<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsAppTeam extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_teams';

    protected $fillable = [
        'name',
        'description',
        'whatsapp_config_id',
        'workspace_id',
        'created_by',
    ];

    /**
     * The WhatsApp number assigned to this team.
     */
    public function config()
    {
        return $this->belongsTo(WhatsAppConfig::class, 'whatsapp_config_id');
    }

    /**
     * All members of this team (including heads).
     */
    public function members()
    {
        return $this->hasMany(WhatsAppTeamMember::class, 'team_id');
    }

    /**
     * Only the team head(s).
     */
    public function heads()
    {
        return $this->hasMany(WhatsAppTeamMember::class, 'team_id')->where('role', 'head');
    }

    /**
     * Get all user IDs that belong to this team (heads + members).
     */
    public function getMemberUserIds(): array
    {
        return $this->members()->pluck('user_id')->toArray();
    }

    /**
     * Check if a user is the head of this team.
     */
    public function isHead(int $userId): bool
    {
        return $this->members()->where('user_id', $userId)->where('role', 'head')->exists();
    }

    /**
     * Check if a user belongs to this team.
     */
    public function hasMember(int $userId): bool
    {
        return $this->members()->where('user_id', $userId)->exists();
    }
}
