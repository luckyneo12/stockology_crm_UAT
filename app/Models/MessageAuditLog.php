<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageAuditLog extends Model
{
    use HasFactory;

    protected $table = 'message_audit_logs';

    protected $fillable = [
        'message_id',
        'action',
        'performed_by',
        'message_content_snapshot',
        'file_path_snapshot',
    ];

    public function performer()
    {
        return $this->belongsTo(User::class , 'performed_by');
    }

    public function message()
    {
        return $this->belongsTo(Message::class , 'message_id')->withTrashed();
    }
}
