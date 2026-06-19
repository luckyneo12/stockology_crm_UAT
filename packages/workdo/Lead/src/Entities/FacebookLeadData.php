<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FacebookLeadData extends Model
{
    use HasFactory;

    protected $table = 'facebook_lead_data';

    protected $fillable = [
        'rule_id',
        'leadgen_id',
        'page_id',
        'form_id',
        'payload',
        'status',
        'error_reason',
        'assigned_user_id',
        'workspace_id',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function assignee()
    {
        return $this->hasOne(\App\Models\User::class, 'id', 'assigned_user_id');
    }
}
