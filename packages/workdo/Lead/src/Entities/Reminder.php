<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'remindable_id',
        'remindable_type',
        'title',
        'description',
        'remind_at',
        'type',
        'is_sent',
        'workspace_id',
        'created_by',
    ];

    public function remindable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public static $types = [
        'call' => 'Call',
        'message' => 'Message',
        'follow_up' => 'Follow-up',
    ];
}
