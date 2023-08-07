<?php

namespace Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Workflow\Models\Basemodel;
use Workflow\Models\WorkflowActivityModel;
use Spatie\ModelStates\HasStates;
use Workflow\States\WorkflowStatus;

final class WorkflowModel extends Basemodel
{
    protected $table = 'workflows';

    use HasStates;

    protected $fillable = [
        'name',
        'version',
        'class',
        'arguments',
        'steps',
        'logics',
        'output',
        'state',
        'status',
        'start_at',
        'stop_at',
        'duration'
    ];

    protected $casts = [
        'arguments'     => 'array',
        'steps'         => 'array',
        'logics'        => 'array',
        'output'        => 'array',
        'state'        => WorkflowStatus::class,
    ];

    public function activities()
    {
        return $this->hasMany(config('workflows.workflow_activity_model',WorkflowActivityModel::class), 'workflow_id');
    }
}
