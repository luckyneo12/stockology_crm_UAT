<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Workdo\ProductService\Entities\ProductService;
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
    ];

    public function stage()
    {
        return $this->hasOne('Workdo\Lead\Entities\LeadStage', 'id', 'stage_id');
    }
    public function labels()
    {
        if($this->labels)
        {
            return Label::whereIn('id', explode(',', $this->labels))->get();
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
        if($this->products)
        {
            return \Workdo\ProductService\Entities\ProductService::whereIn('id', explode(',', $this->products))->get();
        }

        return [];
    }

    public function sources()
    {
        if($this->sources)
        {
            return Source::whereIn('id', explode(',', $this->sources))->get();
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
        if (!$user) return collect();
        
        $accessibleUserIds = $user->getAccessibleUserIds();
        return $this->reminders->whereIn('user_id', $accessibleUserIds);
    }

    public function getTodayRemindersCount($user = null)
    {
        return $this->getFilteredReminders($user)->filter(function($r) {
            return date('Y-m-d', strtotime($r->remind_at)) == date('Y-m-d');
        })->count();
    }

    public function complete_tasks()
    {
        return $this->hasMany('Workdo\Lead\Entities\LeadTask', 'lead_id', 'id')->where('status', '=', 1);
    }

    public function isAccessible($user = null)
    {
        $user = $user ?? \Auth::user();
        
        if ($this->workspace_id != getActiveWorkSpace()) {
            return false;
        }

        if ($user->type == 'company' || $user->type == 'client' || $user->visibility_level == 'all') {
            return true;
        }

        $accessibleUserIds = $user->getAccessibleUserIds();
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
}
