<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Fixtures\WorkflowTestJob;
use Tests\Fixtures\TestWorkflow;

final class WorkflowTest extends TestCase
{
    public function testWorkflowExecution(): void
    {
        $testWorkflow = new TestWorkflow();
        $testWorkflow->start(["Hello Test"]);
        $testWorkflow->getJson();
        $this->assertInstanceOf(TestWorkflow::class, $testWorkflow);
    }

}
