<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class WhatsAppChat extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_chats';

    protected $fillable = [
        'whatsapp_config_id',
        'customer_phone',
        'customer_name',
        'lead_id',
        'assigned_user_id',
        'workspace_id',
        'last_message_at',
    ];

    public function messages()
    {
        return $this->hasMany(WhatsAppMessage::class, 'whatsapp_chat_id')->orderBy('id', 'asc');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function config()
    {
        return $this->belongsTo(WhatsAppConfig::class, 'whatsapp_config_id');
    }

    public function assignee()
    {
        return $this->belongsTo('\App\Models\User', 'assigned_user_id');
    }

    public function isAccessible($user = null)
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return false;
        }

        if ($this->workspace_id != getActiveWorkSpace()) {
            return false;
        }

        // 1. Company owner / admin has full access
        if ($user->type == 'company' || $user->visibility_level == 'all') {
            return true;
        }

        $config = $this->config;
        if (!$config) {
            return false;
        }

        // 2. Department Manager check (if department is set on config)
        if ($config->department_id) {
            if (module_is_active('Hrm') && class_exists('\Workdo\Hrm\Entities\Department') && class_exists('\Workdo\Hrm\Entities\Employee')) {
                $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $user->id)->first();
                if ($employee) {
                    $isManager = \Workdo\Hrm\Entities\Department::where('id', $config->department_id)
                        ->where('manager_id', $employee->id)
                        ->exists();
                    if ($isManager) {
                        return true;
                    }
                }
            }
        }

        // 3. Team-based Access Control
        $teams = WhatsAppTeam::where('whatsapp_config_id', $config->id)->get();
        if ($teams->isNotEmpty()) {
            $isHeadOfAnyTeam = false;
            $isMemberOfAnyTeam = false;

            foreach ($teams as $team) {
                if ($team->isHead($user->id)) {
                    $isHeadOfAnyTeam = true;
                }
                if ($team->hasMember($user->id)) {
                    $isMemberOfAnyTeam = true;
                }
            }

            // A. Team Head can see all chats for this WhatsApp number
            if ($isHeadOfAnyTeam) {
                return true;
            }

            // B. Team Member can only see their own chats or unassigned chats
            if ($isMemberOfAnyTeam) {
                // Own assigned chat
                if ($this->assigned_user_id == $user->id) {
                    return true;
                }
                // Unassigned or assigned to company admin
                if (in_array($this->assigned_user_id, [null, 0, 1])) {
                    return true;
                }
                // Under any other circumstances (assigned to other members), access is denied
                return false;
            }
        }

        // 4. Default fallback: matching Lead accessibility or accessible user IDs
        $accessibleUserIds = $user->getAccessibleUserIds();
        if (in_array($this->assigned_user_id, $accessibleUserIds)) {
            return true;
        }

        if ($this->lead && $this->lead->isAccessible($user)) {
            return true;
        }

        return false;
    }
}

