<?php

namespace Workflow\Services;

use Exception;
use Workflow\WorkflowInterface;

final class WorkflowServiceManager{

    /**
     * Instantiate a new workflow.
     *
     * @param string $workflowClass The name of the workflow class.
     * @return WorkflowInterface The instantiated workflow.
     */
    public static function createWorkflow(string $workflowClass): WorkflowInterface
    {
        if (!class_exists($workflowClass)) {
            throw new Exception(sprintf('Workflow class %s does not exist.', $workflowClass));
        }
        $workflow = new $workflowClass();

        return $workflow;
    }

    public static function getWorkflow(string $workflowClass, int $id = 0): WorkflowInterface
    {
        if($id === 0){
            return self::createWorkflow($workflowClass);
        }

        if (!class_exists($workflowClass)) {
            throw new Exception(sprintf('Workflow class %s does not exist.', $workflowClass));
        }

        $workflow = new $workflowClass($id);
        if (!$workflow instanceof WorkflowInterface) {
            throw new Exception(sprintf('Workflow class %s does not implement WorkflowInterface.', $workflowClass));
        }

        return $workflow;
    }

}
