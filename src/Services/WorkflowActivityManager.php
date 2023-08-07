<?php

namespace Workflow\Services;

use Workflow\Jobs\WorkflowJobInterface;
use Workflow\Models\WorkflowModel;
use Workflow\Models\WorkflowActivityModel;
use Workflow\States\WorkflowCreatedStatus as CreatedStatus;

final class WorkflowActivityManager
{
    public static function executeActivity($activity): void
    {
        try {
            dispatch($activity);
        } catch (\Exception $exception) {
            \Log::error('Failed to dispatch activity: ' . $exception->getMessage());
        }
    }

    public static function getLastActivityModel($id): ?WorkflowActivityModel
    {
        return WorkflowActivityModel::where('workflow_id', ($id))
            ->orderBy('index', 'desc')->first();
    }

    public static function createActivity(string $activityClass, $workflowId, $index, $stepIdentifier, $input, $artefacts): WorkflowJobInterface
    {
        $input = self::resolveValueFromInputValues($input);

        if (!class_exists($activityClass)) {
            throw new \Exception(sprintf('Activity class %s does not exist.', $activityClass));
        }

        $activity = new $activityClass();
        if (!$activity instanceof WorkflowJobInterface) {
            throw new \Exception(sprintf('Activity class %s does not implement WorkflowActivityInterface.', $activityClass));
        }

        $activityModel = WorkflowActivityModel::create([
            'workflow_id'       => $workflowId,
            'name'              => $activity->name,
            'version'           => $activity->version,
            'stepidentifier'    => $stepIdentifier,
            'index'             => $index,
            'class'             => $activityClass,
            'arguments'         => $input
        ]);
        $activityModel->state->transitionTo(CreatedStatus::class);

        $activity->id = $activityModel->id;
        $activity->model = $activityModel;

        return $activity;
    }

    private static function resolveValueFromInputValues($inputs, $artefacts = [])
    {
        foreach ($inputs as &$input) {
            // Check if the value matches the pattern
            if (preg_match('/^%([\w\.]+)%$/', (string) $input)) {
                try {

                    // Check if the expression matches the pattern
                    if (preg_match('/^%([\w\.]+)%$/', (string) $input, $matches)) {
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
                    throw new \Exception(sprintf('Invalid format in expression: %s', $input));

                } catch (\Exception) {
                    // If an exception is thrown, you can choose to handle it here.
                    // For this example, we simply leave the original value unchanged.
                }
            }
        }

        return $inputs;
    }

}
