<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;

class LeadStagePermission extends Model
{
    protected $fillable = [
        'stage_id',
        'role_id',
        'user_id',
        'can_view',
        'can_move',
        'workspace_id',
    ];

    public function stage()
    {
        return $this->belongsTo(LeadStage::class, 'stage_id');
    }
}
