<?php

namespace Tests\Fixtures;
use Workflow\Jobs\WorkflowJob;

final class WorkflowTestJob extends WorkflowJob
{
    //public $tries = 3;
    //public $timeout = 120;
    // public $retryAfter = 10;
    //public $queue = 'custom-queue';
    //public $connection = 'sqs';

    public $name = "Test Step Name";

    public $version = "1.0.0";

    public function execute(): array
    {
        $randomNumber = random_int(0, 100);
        $content = "Hello Custom Workflow, " . now() . "\n";
        return ['content' => $content, 'randomNumber' => $randomNumber, 'something' => 'else'];
    }

}
