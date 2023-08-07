<?php
namespace Workflow\Listeners;

use Workflow\Events\WorkflowActivityProcessedEvent;
use Workflow\Services\WorkflowServiceManager;
use Workflow\Models\WorkflowModel as WorkflowModel;

use Illuminate\Contracts\Queue\ShouldQueue;

// implements ShouldQueue
final class WorkflowActivityProcessedListener
{
    public function handle(WorkflowActivityProcessedEvent $workflowActivityProcessedEvent): void
    {
        $activity           = $workflowActivityProcessedEvent->activity;
        $workflowId         = $activity->workflowActivityModel->workflow_id;
        $workflowModel      = WorkflowModel::find($workflowId);
        $workflow           = WorkflowServiceManager::getWorkflow($workflowModel->class, $workflowId);
        $workflow->continue();

    }
}
