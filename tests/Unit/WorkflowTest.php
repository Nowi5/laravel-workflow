<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Tests\Fixtures\WorkflowTestJob;
use Tests\Fixtures\TestWorkflow;

final class WorkflowTest extends TestCase
{
    public function testWorkflowCanBeInstantiated(): void
    {
        $testWorkflow = new TestWorkflow();
        $this->assertInstanceOf(TestWorkflow::class, $testWorkflow);
    }

    public function testWorkflowSetStepJson(): void
    {
        $testWorkflow = new TestWorkflow();
        $testWorkflow->addStepsFromJson('[{"activityclass": "App\\\Jobs\\\WorkflowStepJob","stepidentifier": "step1","input": {"param1": "some value","param2": "another value"}}]');
        $workflowJson = $testWorkflow->getJson();
        $workflowArray = json_decode($workflowJson, true);
        $this->assertArrayHasKey('steps', $workflowArray);
    }

    public function testWorkflowSetLogiocsJson(): void
    {
        $testWorkflow = new TestWorkflow();
        $testWorkflow->addLogicsFromJson('[{"initial_step":"step1","next_step":"step2"},{"initial_step":"step2","next_step":"step3"},{"initial_step":"step3","conditions":[{"evaluate":"%step2.randomNumber%","comparison":"<50","next":"step5"},{"evaluate":"%step2.randomNumber%","comparison":">=50","next":"step4"}]}]');
        $workflowJson = $testWorkflow->getJson();
        $workflowArray = json_decode($workflowJson, true);
        $this->assertArrayHasKey('logics', $workflowArray);
    }
}
