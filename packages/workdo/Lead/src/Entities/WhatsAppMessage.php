<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsAppMessage extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'whatsapp_chat_id',
        'direction',
        'message_type',
        'body',
        'media_url',
        'message_sid',
        'status',
        'sender_id',
    ];

    public function chat()
    {
        return $this->belongsTo(WhatsAppChat::class, 'whatsapp_chat_id');
    }

    public function sender()
    {
        return $this->belongsTo('\App\Models\User', 'sender_id');
    }
}
