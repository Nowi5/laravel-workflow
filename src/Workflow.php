<?php

namespace Workflow;

use Workflow\Helpers\ModelHelper;
use Workflow\Models\WorkflowModel as WorkflowModel;
use Workflow\Services\WorkflowActivityManager;
use Workflow\States\WorkflowCreatedStatus as CreatedStatus;
use Workflow\States\WorkflowRunningStatus as RunningStatus;
use Workflow\States\WorkflowCompletedStatus as CompletedStatus;
use Workflow\States\WorkflowFailedStatus as FailedStatus;
abstract class Workflow implements WorkflowInterface
{
    private $currentActivity;

    private $workflowModel;

    private $artefacts;

    public function __construct($id = 0)
    {
        if($id !== 0){
            $this->workflowModel = WorkflowModel::with('activities')->find($id);
        }
        else{
            $this->workflowModel = WorkflowModel::create([
                'name'      => $this->name ?? static::class,
                'version'   => $this->version ?? "1.0.0",
                'class'     => static::class
            ]);
            $this->workflowModel->save();
            $this->workflowModel->state->transitionTo(CreatedStatus::class);
        }
    }

    public function start(array $args = []): void{
        $this->execute();
        $this->arguments = $args;
        $this->start_at = now();
        $this->workflowModel->state->transitionTo(RunningStatus::class);
        $this->workflowModel->save();
        $this->continue();
    }

    public function stop(): void{
        $this->stop_at = now();
        $this->workflowModel->state->transitionTo(CompletedStatus::class);
        $this->workflowModel->save();
    }

    public function continue(){
        $this->workflowModel->state->transitionTo(RunningStatus::class);
        $this->workflowModel->save();

        try {
            // determine last acitivity
            $lastAcitivityModel = WorkflowActivityManager::getLastActivityModel($this->workflowModel->id);

            $index = $lastAcitivityModel == null ? 0 : $lastAcitivityModel->index + 1;

            // determine next activity
            $nextActivityListEntry = $this->getNextActivity($lastAcitivityModel);
            if($nextActivityListEntry === null){
                $this->end();
                return;
            }

            // create next activity
            $activity = WorkflowActivityManager::createActivity(
                $nextActivityListEntry['activityclass'],
                $this->workflowModel->id,
                $index,
                $nextActivityListEntry['stepidentifier'],
                $nextActivityListEntry['input'],
                $this->getArtifacts()
            );

            // execute next activity
            WorkflowActivityManager::executeActivity($activity);
        }
        catch(\Exception $exception){
            $this->failed();
            throw $exception;
        }

    }


    public function addStepsFromJson(string $jsonString): void
    {
        // Validate the JSON string for steps
        if (!$this->validateJsonForSteps($jsonString)) {
            throw new \Exception("Invalid JSON structure provided for steps.");
        }

        $data = json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
        if(!isset($this->steps)){
            $this->steps = [];
        }
        $this->steps = array_merge($this->steps, $data);
    }
    private function validateJsonForSteps(string $jsonString): bool
    {
        $data = json_decode(utf8_encode($jsonString), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON decoding error: ' . json_last_error_msg());
        }
        if (!is_array($data)) {
            throw new \Exception('Decoded data is not an array.');
        }
        foreach ($data as $step) {
            // Check if 'stepidentifier' is missing or empty.
            if (empty($step['stepidentifier'])) {
                throw new \Exception('Missing or empty stepidentifier.');
            }

            // Check if 'activityclass' is missing or empty.
            if (empty($step['activityclass'])) {
                throw new \Exception('Missing or empty activityclass.');
            }
        }
        return true;
    }


    public function addLogicsFromJson(string $jsonString): void
    {
        // Validate the JSON string for logics
        if (!$this->validateJsonForLogics($jsonString)) {
            throw new \Exception("Invalid JSON structure provided for logics.");
        }

        $data = json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
        if(!isset($this->logics)){
            $this->logics = [];
        }
        $this->logics = array_merge($this->logics, $data);
    }
    private function validateJsonForLogics(string $jsonString): bool
    {
        $data = json_decode(utf8_encode($jsonString), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON decoding error: ' . json_last_error_msg());
        }
        if (!is_array($data)) {
            throw new \Exception('Decoded data is not an array.');
        }
        foreach ($data as $logic) {
            if (!isset($logic['initial_step'])){
                return false;
            }

            if(!isset($logic['next_step']) && !isset($logic['conditions'])){
                return false;
            }
        }
        return true;
    }

// The addStepsFromJson and addLogicsFromJson methods remain unchanged.


    private function end(): void{
        $this->workflowModel->state->transitionTo(CompletedStatus::class);
        $this->workflowModel->save();
    }

    private function failed(): void
    {
        try {
            $this->workflowModel->state->transitionTo(FailedStatus::class);
            $this->workflowModel->save();
        } catch (\Spatie\ModelStates\Exceptions\TransitionNotFound) {
            return;
        }
    }

    private function resolveValueFromExpression($expression)
    {
        $artefacts = $this->getArtifacts();

        // Check if the expression matches the pattern
        if (preg_match('/^%([\w\.]+)%$/', (string) $expression, $matches)) {
            $path = explode('.', $matches[1]);

            $currentValue = $artefacts;

            foreach ($path as $segment) {
                if (isset($currentValue[$segment])) {
                    $currentValue = $currentValue[$segment];
                } else {
                    // If any segment is not found, throw an exception
                    throw new \Exception(sprintf('Invalid key segment in expression: %s', $segment));
                }
            }

            return $currentValue;
        }

        // If no match is found, return the original expression or throw an exception
        throw new \Exception(sprintf('Invalid format in expression: %s', $expression));
    }






    private function getNextActivityName(?\Workflow\Models\WorkflowActivityModel $workflowActivityModel): string
    {
        $stepKeys = array_keys($this->steps);

        // No last activity
        if (!$workflowActivityModel instanceof \Workflow\Models\WorkflowActivityModel) {
            return $stepKeys[0];
        }

        // If no logic is defined, use the default next step
        if (!$this->logics) {
            $currentIndex = array_search($workflowActivityModel->stepidentifier, $stepKeys, true);
            if ($currentIndex !== false && isset($stepKeys[$currentIndex + 1])) {
                return $stepKeys[$currentIndex + 1]; // Default next step
            }
        }

        // If there is logic, let use it to determine next step
        // Find next step
        $nextStepLogic = null;
        foreach ($this->logics as $logic) {
            if ($logic['initial_step'] === $workflowActivityModel->stepidentifier) {
                $nextStepLogic = $logic;
                break;
            }
        }

        // If there's a direct "next_step", return it.
        if (isset($nextStepLogic['next_step'])) {
            return $nextStepLogic['next_step'];
        }

        // If there are conditions, evaluate each.
        if (isset($nextStepLogic['conditions'])) {
            $artefacts = $this->getArtifacts();
            foreach ($nextStepLogic['conditions'] as $condition) {
                $evaluateExpression = $condition['evaluate'];

                // Replace placeholders in the evaluate expression with actual output values.
                $value = $this->resolveValueFromExpression($evaluateExpression);

                if ($this->evaluateCondition($condition['comparison'], $value)) {
                    return $condition['next'];
                }
            }
        }

        return "";
    }

    private function getNextActivity(?\Workflow\Models\WorkflowActivityModel $workflowActivityModel)
    {
        $nextStepName = $this->getNextActivityName($workflowActivityModel);
        if (!$nextStepName || $nextStepName === "") {
            return null; // Workflow is completed as no next step found.
        }

        if (!isset($this->steps[$nextStepName])) {
            throw new \Exception(sprintf('Next step not found in activity list: %s', $nextStepName));
        }

        return $this->steps[$nextStepName];
    }

    private function evaluateCondition(string $condition, $value): bool
    {
        [, $operator, $threshold] = preg_split('/([<>=]+)/', $condition, -1, PREG_SPLIT_DELIM_CAPTURE);
        $threshold = trim($threshold);

        return match ($operator) {
            '<' => $value < $threshold,
            '>' => $value > $threshold,
            '<=' => $value <= $threshold,
            '>=' => $value >= $threshold,
            '==' => $value == $threshold,
            '!=' => $value != $threshold,
            default => throw new \Exception(sprintf('Invalid operator in condition: %s', $operator)),
        };
    }

    public function addStep(string $stepidentifier, string $activityclass, array $input): void
    {
        $steps = $this->steps;
        $steps[$stepidentifier] = [
            "stepidentifier" => $stepidentifier,
            "activityclass" => $activityclass,
            "input" => $input
        ];
        $this->steps = $steps;
    }

    public function addLogic(string $start, ?string $next = null, ?array $conditions = null): void
    {
        $logics = $this->logics;
        $logicItem = ["initial_step" => $start];

        if ($next) {
            $logicItem["next_step"] = $next;
        } elseif ($conditions) {
            $logicItem["conditions"] = $conditions;
        }

        $logics[] = $logicItem;

        $this->logics = $logics;
    }

    public function getJson(): string
    {
        return json_encode([
            "name" => $this->name,
            "version" => $this->version,
            "steps" => $this->steps,
            "logics" => $this->logics
        ], JSON_THROW_ON_ERROR);

    }

    public function getId(): int
    {
        return $this->workflowModel->id;
    }

    // Magic method to access model's attributes
    public function __get($property)
    {
        // Check if the property exists locally first
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        // If not locally, then check in the model
        if (ModelHelper::attributeExists($this->workflowModel, $property)) {
            return $this->workflowModel->$property;
        }

        // If property doesn't exist in both, you can throw an exception, return null or handle it another way
        return null;
    }

    // Magic method to set properties
    public function __set($property, $value)
    {
        // Check if the property exists locally first
        if (property_exists($this, $property)) {
            $this->$property = $value;
            return;
        }

        // If not locally, then check in the model
        if (ModelHelper::attributeExists($this->workflowModel, $property)) {
            $this->workflowModel->$property = $value;
            return;
        }

        throw new \Exception(sprintf('Property "%s" does not exist.', $property));
    }

    public function getArtifacts()
    {
        $consolidated = [];
        if($this->artefacts !== null){
            return $this->artefacts;
        }

        // Initialize the consolidated output with the Workflow's own arguments.
        $consolidated['input'] = $this->arguments ?? [];

        // Iterate over each associated WorkflowActivity.
        if($this->workflowModel->activities !== null) {
            foreach ($this->workflowModel->activities as $activity) {
                // Decode the activity's output.
                $output = $activity->output ?? [];

                // Format the key as per the requirement.
                $key = $activity->stepidentifier;

                // Store the output using the formatted key.
                $consolidated[$key] = $output;
            }
        }

        $this->artefacts = $consolidated;
        return $consolidated;
    }
}