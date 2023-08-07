<?php

namespace Workflow\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
final class WorkflowActivityProcessedEvent
{
    use Dispatchable;

    use SerializesModels;

    public function __construct(public $activity)
    {
    }
}
