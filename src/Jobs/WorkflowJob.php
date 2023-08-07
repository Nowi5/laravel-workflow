<?php

namespace Workflow\Jobs;
use Workflow\Events\WorkflowActivityProcessedEvent;
use Workflow\Models\WorkflowActivityModel;
use Workflow\Jobs\WorkflowJobInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Workflow\States\WorkflowCreatedStatus as CreatedStatus;
use Workflow\States\WorkflowRunningStatus as RunningStatus;
use Workflow\States\WorkflowCompletedStatus as CompletedStatus;
use Workflow\States\WorkflowFailedStatus as FailedStatus;

abstract class WorkflowJob implements ShouldQueue, WorkflowJobInterface
{
    //use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $baseRetryAfter = 15;

    protected $workflowActivity;

    public $name;

    public $version = "1.0.0";

    /**
     * @var int
     */
    public $id;

    public function __construct(int $id = 0)
    {
        $this->id = $id;
    }

    public function handle()
    {
        try {
            $this->workflowActivityModel = WorkflowActivityModel::find($this->id);
            $this->workflowActivityModel->start_at = now();
            $this->workflowActivityModel->state->transitionTo(RunningStatus::class);
            $this->workflowActivityModel->save();

            $result = $this->execute();

            $this->workflowActivityModel->output = $result;
            $this->workflowActivityModel->state->transitionTo(CompletedStatus::class);
            $this->workflowActivityModel->stop_at = now();
            $this->workflowActivityModel->save();

            event(new WorkflowActivityProcessedEvent($this));

        } catch (\Exception $exception) {

            $this->workflowActivityModel = WorkflowActivityModel::find($this->id);
            $this->workflowActivityModel->output = $exception->getMessage();
            $this->workflowActivityModel->state->transitionTo(FailedStatus::class);
            $this->workflowActivityModel->stop_at = now();
            $this->workflowActivityModel->save();

            // event(new WorkflowActivityFailedEvent($this));

            throw $exception;
        }

    }

    public function __get($name) {
        // Check if the property exists locally first
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        // If property doesn't exist in both, you can throw an exception, return null or handle it another way
        return $this->workflowActivityModel->arguments[$name] ?? null;
    }

    abstract public function execute();

    public function retryAfter()
    {
        // Get the current attempt. This starts at 1 for the first attempt.
        $attempts = $this->attempts();

        // Double the delay for each subsequent attempt
        $delayInSeconds = $this->baseRetryAfter * 2 ** ($attempts - 1);

        return now()->addSeconds($delayInSeconds);
    }
}
