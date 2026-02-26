<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    use HasFactory;

    protected $table = 'messenger_groups';

    protected $fillable = [
        'name',
        'description',
        'avatar',
        'created_by',
        'workspace_id',
        'allow_images',
        'allow_files',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function members()
    {
        return $this->hasMany(GroupMember::class , 'group_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class , 'group_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class , 'created_by');
    }

    public function workspace()
    {
        return $this->belongsTo(WorkSpace::class , 'workspace_id');
    }
}
