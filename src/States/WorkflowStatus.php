<?php

declare(strict_types=1);

namespace Workflow\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class WorkflowStatus extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(WorkflowCreatedStatus::class)
            ->allowTransition(WorkflowCreatedStatus::class, WorkflowCreatedStatus::class)
            ->allowTransition(WorkflowCreatedStatus::class, WorkflowRunningStatus::class)
            ->allowTransition(WorkflowCreatedStatus::class, WorkflowWaitingStatus::class)

            ->allowTransition(WorkflowRunningStatus::class, WorkflowRunningStatus::class)
            ->allowTransition(WorkflowRunningStatus::class, WorkflowCompletedStatus::class)
            ->allowTransition(WorkflowRunningStatus::class, WorkflowFailedStatus::class)
            ->allowTransition(WorkflowRunningStatus::class, WorkflowWaitingStatus::class)

            ->allowTransition(WorkflowWaitingStatus::class, WorkflowFailedStatus::class)
            ->allowTransition(WorkflowWaitingStatus::class, WorkflowRunningStatus::class)
            ->allowTransition(WorkflowWaitingStatus::class, WorkflowCompletedStatus::class)
        ;
    }
}
