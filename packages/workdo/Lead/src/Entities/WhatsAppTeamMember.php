<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsAppTeamMember extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_team_members';

    protected $fillable = [
        'team_id',
        'user_id',
        'role', // 'head' or 'member'
    ];

    public function team()
    {
        return $this->belongsTo(WhatsAppTeam::class, 'team_id');
    }

    public function user()
    {
        return $this->belongsTo('\App\Models\User', 'user_id');
    }

    public function isHead(): bool
    {
        return $this->role === 'head';
    }
}
