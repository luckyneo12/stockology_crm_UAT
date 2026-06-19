<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'subject',
        'user_id',
        'pipeline_id',
        'stage_id',
        'sources',
        'products',
        'notes',
        'labels',
        'order',
        'phone',
        'created_by',
        'updated_by',
        'is_active',
        'date',
        'workspace_id',
        'pan_number',
        'aadhar_number',
        'dp_id',
    ];

    public function stage()
    {
        return $this->hasOne('Workdo\Lead\Entities\LeadStage', 'id', 'stage_id');
    }
    public function labels()
    {
        static $labelsCache = null;
        if ($labelsCache === null) {
            $labelsCache = Label::where('workspace_id', getActiveWorkSpace())->get()->keyBy('id');
        }

        if ($this->labels) {
            $ids = explode(',', $this->labels);
            $result = collect();
            $missingIds = [];
            foreach ($ids as $id) {
                if ($labelsCache->has($id)) {
                    $result->push($labelsCache->get($id));
                } else {
                    $missingIds[] = $id;
                }
            }
            if (!empty($missingIds)) {
                $missingLabels = Label::whereIn('id', $missingIds)->get();
                foreach ($missingLabels as $lbl) {
                    $labelsCache->put($lbl->id, $lbl);
                    $result->push($lbl);
                }
            }
            return $result;
        }

        return false;
    }


    public function files()
    {
        return $this->hasMany('Workdo\Lead\Entities\LeadFile', 'lead_id', 'id');
    }

    public function pipeline()
    {
        return $this->hasOne('Workdo\Lead\Entities\Pipeline', 'id', 'pipeline_id');
    }

    public function products()
    {
        return collect();
    }

    public function sources()
    {
        static $sourcesCache = null;
        if ($sourcesCache === null) {
            $sourcesCache = Source::where('workspace_id', getActiveWorkSpace())->get()->keyBy('id');
        }

        if ($this->sources) {
            $ids = explode(',', $this->sources);
            $result = collect();
            $missingIds = [];
            foreach ($ids as $id) {
                if ($sourcesCache->has($id)) {
                    $result->push($sourcesCache->get($id));
                } else {
                    $missingIds[] = $id;
                }
            }
            if (!empty($missingIds)) {
                $missingSources = Source::whereIn('id', $missingIds)->get();
                foreach ($missingSources as $src) {
                    $sourcesCache->put($src->id, $src);
                    $result->push($src);
                }
            }
            return $result;
        }

        return [];
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'user_leads', 'lead_id', 'user_id');
    }

    public function activities()
    {
        return $this->hasMany('Workdo\Lead\Entities\LeadActivityLog', 'lead_id', 'id')->orderBy('id', 'desc');
    }

    public function discussions()
    {
        return $this->hasMany('Workdo\Lead\Entities\LeadDiscussion', 'lead_id', 'id')->orderBy('id', 'desc');
    }

    public function calls()
    {
        return $this->hasMany('Workdo\Lead\Entities\LeadCall', 'lead_id', 'id');
    }

    public function emails()
    {
        return $this->hasMany('Workdo\Lead\Entities\LeadEmail', 'lead_id', 'id')->orderByDesc('id');
    }

    public function tasks()
    {
        return $this->hasMany('Workdo\Lead\Entities\LeadTask', 'lead_id', 'id');
    }

    public function reminders()
    {
        return $this->morphMany('Workdo\Lead\Entities\Reminder', 'remindable');
    }

    public function getFilteredReminders($user = null)
    {
        $user = $user ?? Auth::user();
        if (!$user)
            return collect();

        $accessibleUserIds = $user->getAccessibleUserIds();
        return $this->reminders->whereIn('user_id', $accessibleUserIds);
    }

    public function getTodayRemindersCount($user = null)
    {
        return $this->getFilteredReminders($user)->filter(function ($r) {
            return date('Y-m-d', strtotime($r->remind_at)) == date('Y-m-d');
        })->count();
    }

    public function complete_tasks()
    {
        return $this->hasMany('Workdo\Lead\Entities\LeadTask', 'lead_id', 'id')->whereIn('status', ['done', 1]);
    }

    public function employee()
    {
        if (module_is_active('Hrm')) {
            return $this->hasOne('\Workdo\Hrm\Entities\Employee', 'user_id', 'user_id');
        }
        return $this->hasOne('\App\Models\User', 'id', 'user_id'); // Fallback
    }

    public function stagePermissions($user = null)
    {
        $user = $user ?? Auth::user();
        $userId = $user ? $user->id : 0;
        
        static $permissionsCache = [];
        
        $stageId = $this->stage_id;
        if (!$stageId) {
            return (object) ['can_view' => true, 'can_move' => true, 'can_edit' => true];
        }
        
        $cacheKey = "{$stageId}_{$userId}";
        if (!isset($permissionsCache[$cacheKey])) {
            if ($this->stage) {
                $permissionsCache[$cacheKey] = $this->stage->permissions($user);
            } else {
                $permissionsCache[$cacheKey] = (object) ['can_view' => true, 'can_move' => true, 'can_edit' => true];
            }
        }
        
        return $permissionsCache[$cacheKey];
    }

    public function isAccessible($user = null)
    {
        $user = $user ?? Auth::user();

        if ($this->workspace_id != getActiveWorkSpace()) {
            return false;
        }

        // Enforce stage-based visibility permissions
        if (!$this->stagePermissions($user)->can_view) {
            return false;
        }

        if ($user->type == 'company' || $user->type == 'client' || $user->visibility_level == 'all') {
            return true;
        }

        $accessibleUserIds = $user->getAccessibleUserIds();

        // Check if user has access to the Responsible Person (Owner)
        if (in_array($this->user_id, $accessibleUserIds)) {
            return true;
        }

        $leadUserIds = $this->users->pluck('id')->toArray();

        return !empty(array_intersect($accessibleUserIds, $leadUserIds));
    }

    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo('App\Models\User', 'updated_by', 'id');
    }

    public function customFieldValues()
    {
        return $this->hasMany('Workdo\Lead\Entities\LeadCustomFieldValue', 'lead_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function isResponsible($user = null)
    {
        $user = $user ?? \Auth::user();
        if (!$user) {
            return false;
        }

        if ($user->type == 'company') {
            return true;
        }

        // Broaden access if user has KYC comment permission
        if ($user->isAbleTo('lead kyc comment')) {
            return true;
        }

        $responsibleIds = $this->users->pluck('id')->toArray();
        $responsibleIds[] = $this->user_id; // Owner
        $responsibleIds[] = $this->created_by; // Creator

        return in_array($user->id, $responsibleIds);
    }
}
