<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WebhookData extends Model
{
    use HasFactory;

    protected $fillable = [
        'webhook_endpoint_id',
        'payload',
        'status',
        'assigned_user_id',
        'workspace_id'
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function endpoint()
    {
        return $this->hasOne(WebhookEndpoint::class , 'id', 'webhook_endpoint_id');
    }

    public function assignedUser()
    {
        return $this->hasOne(\App\Models\User::class , 'id', 'assigned_user_id');
    }
}
