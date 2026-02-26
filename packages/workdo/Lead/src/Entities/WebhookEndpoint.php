<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WebhookEndpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'created_by',
        'assign_to',
        'pipeline_id',
        'stage_id',
        'view_permissions',
        'edit_permissions',
        'field_mapping',
        'workspace_id',
    ];

    protected $casts = [
        'view_permissions' => 'array',
        'edit_permissions' => 'array',
        'field_mapping' => 'array',
    ];

    public function creator()
    {
        return $this->hasOne(\App\Models\User::class , 'id', 'created_by');
    }

    public function assignee()
    {
        return $this->hasOne(\App\Models\User::class , 'id', 'assign_to');
    }

    public function pipeline()
    {
        return $this->hasOne(Pipeline::class , 'id', 'pipeline_id');
    }

    public function stage()
    {
        return $this->hasOne(LeadStage::class , 'id', 'stage_id');
    }

    public function webhookData()
    {
        return $this->hasMany(WebhookData::class , 'webhook_endpoint_id', 'id');
    }
}
