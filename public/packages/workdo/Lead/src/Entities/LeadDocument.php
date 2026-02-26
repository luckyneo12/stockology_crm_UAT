<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;

class LeadDocument extends Model
{
    protected $fillable = [
        'name',
        'stage_id', 
        'is_required',
        'workspace_id',
        'created_by'
    ];
    
    public function stage() {
        return $this->belongsTo(LeadStage::class, 'stage_id');
    }
}
