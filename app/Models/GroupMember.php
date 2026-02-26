<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    use HasFactory;

    protected $table = 'messenger_group_members';

    protected $fillable = [
        'group_id',
        'user_id',
        'role',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(ChatGroup::class , 'group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class , 'user_id');
    }
}
