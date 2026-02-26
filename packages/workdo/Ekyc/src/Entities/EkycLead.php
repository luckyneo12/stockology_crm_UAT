<?php

namespace Workdo\Ekyc\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class EkycLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
        'assigned_user',
        'workspace_id',
        'created_by',
    ];

    public function assignedUser()
    {
        return $this->belongsTo('App\Models\User', 'assigned_user', 'id');
    }

    public function creator()
    {
        return $this->belongsTo('App\Models\User', 'created_by', 'id');
    }

    public function requests()
    {
        return $this->hasMany('Workdo\Ekyc\Entities\EkycRequest', 'lead_id', 'id');
    }

    public function isAccessible($user = null)
    {
        $user = $user ?? Auth::user();
        
        if ($this->workspace_id != getActiveWorkSpace()) {
            return false;
        }

        if ($user->type == 'company' || $user->visibility_level == 'all') {
            return true;
        }

        $accessibleUserIds = $user->getAccessibleUserIds();
        
        return in_array($this->assigned_user, $accessibleUserIds) || $this->created_by == $user->id;
    }

    public static function scopeAccessible($query)
    {
        $user = Auth::user();
        $query->where('workspace_id', getActiveWorkSpace());

        if ($user->type != 'company' && $user->visibility_level != 'all') {
            $accessibleUserIds = $user->getAccessibleUserIds();
            $query->whereIn('assigned_user', $accessibleUserIds)
                  ->orWhere('created_by', $user->id);
        }

        return $query;
    }
}
