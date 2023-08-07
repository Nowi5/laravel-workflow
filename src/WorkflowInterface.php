<?php

namespace Workflow;

interface WorkflowInterface
{

    public function execute();

    public function addStep(string $stepidentifier, string $activityclass, array $input);
}
