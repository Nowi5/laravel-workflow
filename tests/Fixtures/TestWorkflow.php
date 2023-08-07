<?php

namespace  Tests\Fixtures;

use Tests\Fixtures\WorkflowTestJob;
use Workflow\Workflow;

final class TestWorkflow extends Workflow{

    public $name = "TestWorkflow";

    public $version = "1.0.0";

    public function execute(): void{
        // create steps
        //$this->addActivity("step2", TestWorkflowActivity::class);

        // Adding activities to the workflow
        $this->addStep("step1", WorkflowTestJob::class, ["param1" => "some value", "param2" => "another value"]);
        $this->addStep("step2", WorkflowTestJob::class, ["param1" => "%step1.randomNumber%"]);
        $this->addStep("step3", WorkflowTestJob::class, ["param1" => "%step2.randomNumber%"]);
        $this->addStep("step4", WorkflowTestJob::class, ["param1" => "%step3.randomNumber%"]);
        $this->addStep("step5", WorkflowTestJob::class, ["param1" => "%step4.randomNumber%"]);
        $this->addStep("step6", WorkflowTestJob::class, ["param1" => "%step5.randomNumber%"]);

        // Setting the logic for the workflow
        $this->addLogic("step1", "step2");
        $this->addLogic("step2", "step3");
        $this->addLogic("step3", null, [
            ["evaluate" => "%step2.randomNumber%", "comparison" => "<50", "next" => "step5"],
            ["evaluate" => "%step2.randomNumber%", "comparison" => ">=50", "next" => "step4"]
        ]);
        $this->addLogic("step4", "step6");
    }

}
