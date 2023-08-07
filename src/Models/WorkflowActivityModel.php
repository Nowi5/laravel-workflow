<?php

namespace Workflow\Models;

use Workflow\Models\Basemodel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\HasStates;
use Workflow\States\WorkflowStatus;
final class WorkflowActivityModel extends Basemodel
{
    protected $table = 'workflow_activities';

    use HasStates;
    protected $fillable = [
        'workflow_id',
        'name',
        'index',
        'stepidentifier',
        'version',
        'class',
        'arguments',
        'output',
        'state',
        'status',
        'start_at',
        'stop_at',
        'duration'
    ];

    protected $casts = [
        'arguments'     => 'array',
        'output'        => 'array',
        'state'        => WorkflowStatus::class,
    ];

    public function workflow()
    {
        return $this->belongsTo(config('workflows.workflow_model', WorkflowModel::class), 'workflow_id');
    }
}
