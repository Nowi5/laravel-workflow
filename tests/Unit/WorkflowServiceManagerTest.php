<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Tests\Fixtures\WorkflowTestJob;
use Tests\Fixtures\TestWorkflow;
use Workflow\Services\WorkflowServiceManager;

final class WorkflowServiceManagerTest extends TestCase
{
    public function testWorkflowServiceManagerCanBeInstantiated(): void
    {
        $testWorkflow = WorkflowServiceManager::createWorkflow(TestWorkflow::class);
        $this->assertInstanceOf(TestWorkflow::class, $testWorkflow);
    }

    public function testGetWorkflow(): void
    {
        $testWorkflow1 = WorkflowServiceManager::createWorkflow(TestWorkflow::class);
        $id = $testWorkflow1->getId();

        $testWorkflow2 = WorkflowServiceManager::getWorkflow(TestWorkflow::class, $id);
        $this->assertInstanceOf(TestWorkflow::class, $testWorkflow2);
    }

}
