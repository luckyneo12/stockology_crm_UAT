<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsAppConfig extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_configs';

    protected $fillable = [
        'name',
        'phone_number',
        'phone_number_id',
        'business_account_id',
        'access_token',
        'verify_token',
        'department_id',
        'pipeline_id',
        'stage_id',
        'workspace_id',
        'created_by',
        // QR Session fields
        'session_status',
        'session_id',
        'connection_type',
    ];

    protected $hidden = ['access_token'];

    /**
     * Department relationship (HRM module optional).
     */
    public function department()
    {
        if (module_is_active('Hrm') && class_exists('\Workdo\Hrm\Entities\Department')) {
            return $this->belongsTo('\Workdo\Hrm\Entities\Department', 'department_id');
        }
        return null;
    }

    public function pipeline()
    {
        return $this->belongsTo('\Workdo\Lead\Entities\Pipeline', 'pipeline_id');
    }

    public function stage()
    {
        return $this->belongsTo('\Workdo\Lead\Entities\LeadStage', 'stage_id');
    }

    /**
     * Teams that use this WhatsApp number.
     */
    public function teams()
    {
        return $this->hasMany(WhatsAppTeam::class, 'whatsapp_config_id');
    }

    /**
     * All chats for this config.
     */
    public function chats()
    {
        return $this->hasMany(WhatsAppChat::class, 'whatsapp_config_id');
    }

    // ── Session Status Helpers ───────────────────────────────────────────────

    public function isConnected(): bool
    {
        return $this->session_status === 'connected';
    }

    public function isQrBased(): bool
    {
        return $this->connection_type === 'qr_session';
    }

    public function getSessionStatusLabelAttribute(): string
    {
        $labels = [
            'disconnected'  => 'Disconnected',
            'connecting'    => 'Connecting...',
            'qr_pending'    => 'Scan QR Code',
            'authenticated' => 'Authenticated',
            'connected'     => 'Connected',
            'blocked'       => 'Blocked',
        ];
        return $labels[$this->session_status] ?? ucfirst($this->session_status);
    }

    public function getSessionStatusColorAttribute(): string
    {
        $colors = [
            'disconnected'  => 'secondary',
            'connecting'    => 'warning',
            'qr_pending'    => 'info',
            'authenticated' => 'info',
            'connected'     => 'success',
            'blocked'       => 'danger',
        ];
        return $colors[$this->session_status] ?? 'secondary';
    }

    /**
     * Check if a specific user has access to this configuration.
     */
    public function isAccessible($user = null): bool
    {
        $user = $user ?? \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return false;
        }

        if ($this->workspace_id != getActiveWorkSpace()) {
            return false;
        }

        if ($user->type == 'company' || $user->visibility_level == 'all') {
            return true;
        }

        // If the user is the Department Head of the department associated with this WhatsApp number
        if ($this->department_id) {
            if (module_is_active('Hrm') && class_exists('\Workdo\Hrm\Entities\Department') && class_exists('\Workdo\Hrm\Entities\Employee')) {
                $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $user->id)->first();
                if ($employee) {
                    $isManager = \Workdo\Hrm\Entities\Department::where('id', $this->department_id)
                        ->where('manager_id', $employee->id)
                        ->exists();
                    if ($isManager) {
                        return true;
                    }
                }
            }
        }

        // Check if the config is assigned to any team of which the user is a member/head
        $teamIds = $this->teams()->pluck('id')->toArray();
        if (!empty($teamIds)) {
            $isMember = WhatsAppTeamMember::whereIn('team_id', $teamIds)
                ->where('user_id', $user->id)
                ->exists();
            if ($isMember) {
                return true;
            }
        }

        return false;
    }
}

