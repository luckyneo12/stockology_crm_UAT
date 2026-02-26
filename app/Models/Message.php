<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_messages';

    protected $fillable = [
        'from_id',
        'to_id',
        'group_id',
        'body',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'read_at',
        'workspace_id',
        'deleted_by'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function fromUser()
    {
        return $this->belongsTo(User::class , 'from_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class , 'to_id');
    }

    public function group()
    {
        return $this->belongsTo(MessengerGroup::class , 'group_id');
    }

    public function replyMessage()
    {
        return $this->belongsTo(Message::class , 'reply_to');
    }

    public function replies()
    {
        return $this->hasMany(Message::class , 'reply_to');
    }

    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileIconAttribute()
    {
        if (!$this->file_type)
            return null;

        $type = strtolower($this->file_type);

        if (str_contains($type, 'pdf'))
            return 'ti ti-file-text';
        if (str_contains($type, 'csv') || str_contains($type, 'excel') || str_contains($type, 'spreadsheet'))
            return 'ti ti-file-spreadsheet';
        if (str_contains($type, 'image') || str_contains($type, 'jpg') || str_contains($type, 'png') || str_contains($type, 'gif'))
            return 'ti ti-photo';
        if (str_contains($type, 'video'))
            return 'ti ti-video';

        return 'ti ti-file';
    }
}
