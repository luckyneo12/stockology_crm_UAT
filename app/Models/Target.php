<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    use HasFactory;

    protected $fillable = [
        'target_name',
        'parent_id',
        'assigned_to',
        'department_id',
        'team_id',
        'assigned_by',
        'responsible_user_id',
        'can_edit',
        'start_date',
        'end_date',
        'target_value',
        'incentive',
        'achieved_value',
        'status',
        'workspace',
        'created_by',
        'target_type',
        'pipeline_id',
        'stage_id',
        'custom_date_field'
    ];

    public function subTargets()
    {
        return $this->hasMany(Target::class, 'parent_id');
    }

    public function parentTarget()
    {
        return $this->belongsTo(Target::class, 'parent_id');
    }

    public function department()
    {
        if (module_is_active('Hrm')) {
            return $this->belongsTo(\Workdo\Hrm\Entities\Department::class, 'department_id');
        }
        return null;
    }

    public function team()
    {
        if (module_is_active('Hrm')) {
            return $this->belongsTo(\Workdo\Hrm\Entities\Department::class, 'team_id');
        }
        return null;
    }

    public function pipeline()
    {
        if (module_is_active('Lead')) {
            return $this->belongsTo(\Workdo\Lead\Entities\Pipeline::class, 'pipeline_id');
        }
        return null;
    }

    public function stage()
    {
        if (module_is_active('Lead')) {
            return $this->belongsTo(\Workdo\Lead\Entities\LeadStage::class, 'stage_id');
        }
        return null;
    }

    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedByUser()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function getAggregateProgressAttribute()
    {
        if ($this->subTargets->count() > 0) {
            $totalTarget = $this->subTargets->sum('target_value');
            $totalAchieved = $this->subTargets->sum('achieved_value');
            return $totalTarget > 0 ? ($totalAchieved / $totalTarget) * 100 : 0;
        }
        return $this->target_value > 0 ? ($this->achieved_value / $this->target_value) * 100 : 0;
    }

    public function recalculateAchievedValue()
    {
        if (!in_array($this->target_type, ['lead_stage', 'account', 'ftd', 'revenue'])) {
            return;
        }

        if (!module_is_active('Lead')) {
            return;
        }

        $query = \Workdo\Lead\Entities\Lead::where('workspace_id', $this->workspace);

        if ($this->target_type === 'lead_stage') {
            $query->where('pipeline_id', $this->pipeline_id)
                  ->where('stage_id', $this->stage_id);
        }

        // Check if there is an selected custom date field for tracking
        $customDateFieldId = $this->custom_date_field;
        $activationDateField = null;
        if ($customDateFieldId && $customDateFieldId !== 'created_at') {
            $activationDateField = \DB::table('lead_custom_fields')
                ->where('workspace_id', $this->workspace)
                ->where('id', $customDateFieldId)
                ->first();
        }

        if ($activationDateField) {
            $query->whereExists(function ($subQuery) use ($activationDateField) {
                $subQuery->select(\DB::raw(1))
                    ->from('lead_custom_field_values')
                    ->whereColumn('lead_custom_field_values.lead_id', 'leads.id')
                    ->where('lead_custom_field_values.field_id', $activationDateField->id);

                if ($this->start_date) {
                    $subQuery->where('lead_custom_field_values.value', '>=', $this->start_date);
                }
                if ($this->end_date) {
                    $subQuery->where('lead_custom_field_values.value', '<=', $this->end_date);
                }
            });
        } else {
            if ($this->start_date) {
                $query->where('created_at', '>=', $this->start_date . ' 00:00:00');
            }
            if ($this->end_date) {
                $query->where('created_at', '<=', $this->end_date . ' 23:59:59');
            }
        }

        if ($this->assigned_to > 0) {
            $query->where('user_id', $this->assigned_to);
        } elseif ($this->department_id > 0 && module_is_active('Hrm')) {
            $dept = \Workdo\Hrm\Entities\Department::find($this->department_id);
            $allDeptIds = $dept ? $dept->allChildIds() : [$this->department_id];
            $empUserIds = \Workdo\Hrm\Entities\Employee::whereIn('department_id', $allDeptIds)->pluck('user_id')->toArray();
            $query->whereIn('user_id', $empUserIds);
        } elseif ($this->team_id > 0 && module_is_active('Hrm')) {
            $dept = \Workdo\Hrm\Entities\Department::find($this->team_id);
            $allDeptIds = $dept ? $dept->allChildIds() : [$this->team_id];
            $empUserIds = \Workdo\Hrm\Entities\Employee::whereIn('department_id', $allDeptIds)->pluck('user_id')->toArray();
            $query->whereIn('user_id', $empUserIds);
        }

        if ($this->target_type === 'lead_stage') {
            $count = $query->count();
            $this->achieved_value = $count;
        } elseif ($this->target_type === 'account') {
            // Count leads with non-empty CLIENT CODE custom field
            $clientCodeFieldIds = \DB::table('lead_custom_fields')
                ->where('workspace_id', $this->workspace)
                ->where('name', 'like', '%CLIENT CODE%')
                ->pluck('id')
                ->toArray();

            $query->whereExists(function ($subQuery) use ($clientCodeFieldIds) {
                $subQuery->select(\DB::raw(1))
                    ->from('lead_custom_field_values')
                    ->whereColumn('lead_custom_field_values.lead_id', 'leads.id')
                    ->whereIn('lead_custom_field_values.field_id', $clientCodeFieldIds)
                    ->whereNotNull('lead_custom_field_values.value')
                    ->where('lead_custom_field_values.value', '!=', '');
            });

            $count = $query->count();
            $this->achieved_value = $count;
        } elseif ($this->target_type === 'ftd') {
            // Count leads with FTD > 0
            $ftdFieldIds = \DB::table('lead_custom_fields')
                ->where('workspace_id', $this->workspace)
                ->where('name', 'like', '%ftd%')
                ->pluck('id')
                ->toArray();

            $query->whereExists(function ($subQuery) use ($ftdFieldIds) {
                $subQuery->select(\DB::raw(1))
                    ->from('lead_custom_field_values')
                    ->whereColumn('lead_custom_field_values.lead_id', 'leads.id')
                    ->whereIn('lead_custom_field_values.field_id', $ftdFieldIds)
                    ->whereNotNull('lead_custom_field_values.value')
                    ->where('lead_custom_field_values.value', '!=', '')
                    ->where('lead_custom_field_values.value', '>', 0);
            });

            $count = $query->count();
            $this->achieved_value = $count;
        } elseif ($this->target_type === 'revenue') {
            // Sum of FTD/Revenue values for matching leads
            $revenueFieldIds = \DB::table('lead_custom_fields')
                ->where('workspace_id', $this->workspace)
                ->where('name', 'like', '%revenue%')
                ->pluck('id')
                ->toArray();

            if (empty($revenueFieldIds)) {
                $revenueFieldIds = \DB::table('lead_custom_fields')
                    ->where('workspace_id', $this->workspace)
                    ->where('name', 'like', '%brokerage%')
                    ->pluck('id')
                    ->toArray();
            }

            if (empty($revenueFieldIds)) {
                $revenueFieldIds = \DB::table('lead_custom_fields')
                    ->where('workspace_id', $this->workspace)
                    ->where('name', 'like', '%ftd%')
                    ->pluck('id')
                    ->toArray();
            }

            $leadIds = $query->pluck('id')->toArray();
            $sum = 0;
            if (!empty($leadIds) && !empty($revenueFieldIds)) {
                $sum = \DB::table('lead_custom_field_values')
                    ->whereIn('lead_id', $leadIds)
                    ->whereIn('field_id', $revenueFieldIds)
                    ->whereNotNull('value')
                    ->where('value', '!=', '')
                    ->sum(\DB::raw('CAST(value AS DECIMAL(15,2))'));
            }
            $this->achieved_value = $sum;
        }

        if ($this->achieved_value >= $this->target_value) {
            $this->status = 'Completed';
        } else {
            $this->status = 'Pending';
        }
        $this->save();
    }
}
