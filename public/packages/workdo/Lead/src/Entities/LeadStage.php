<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;
use Workdo\Lead\Entities\Lead;
use Workdo\Lead\Entities\LeadStagePermission;
use Illuminate\Support\Facades\Auth;

class LeadStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'pipeline_id',
        'created_by',
        'workspace_id',
        'order',
    ];

    public function lead(Request $request = null, $limit = null, $offset = 0)
    {
        $user = Auth::user();
        $query = Lead::where('leads.stage_id', '=', $this->id)
                     ->where('leads.workspace_id', '=', getActiveWorkSpace())
                     ->with(['users', 'tasks', 'complete_tasks', 'stage', 'reminders']);

        if ($user->type == 'client') {
            $query->join('client_leads', 'client_leads.lead_id', '=', 'leads.id')
                  ->where('client_leads.client_id', '=', $user->id);
        } elseif ($user->type != 'company' && $user->visibility_level != 'all') {
            $accessibleUserIds = $user->getAccessibleUserIds();
            $query->where(function($q) use ($accessibleUserIds) {
                $q->whereIn('leads.user_id', $accessibleUserIds)
                  ->orWhereHas('users', function($subQ) use ($accessibleUserIds) {
                      $subQ->whereIn('users.id', $accessibleUserIds);
                  });
            });
        }

        // Apply Custom Filters if request is provided
        if ($request) {
            if ($request->has('responsible_person') && !empty($request->responsible_person)) {
                $respIds = (array)$request->responsible_person;
                $query->where(function($q) use ($respIds) {
                    $q->whereIn('leads.user_id', $respIds)
                      ->orWhereHas('users', function($subQ) use ($respIds) {
                          $subQ->whereIn('users.id', $respIds);
                      });
                });
            }
            if ($request->has('source_id') && !empty($request->source_id)) {
                $query->where(function($q) use ($request) {
                    foreach ((array)$request->source_id as $source) {
                        $q->orWhereRaw('FIND_IN_SET(?, leads.sources)', [$source]);
                    }
                });
            }
            if ($request->has('start_date') && !empty($request->start_date)) {
                $query->where('leads.created_at', '>=', $request->start_date . ' 00:00:00');
            }
            if ($request->has('end_date') && !empty($request->end_date)) {
                $query->where('leads.created_at', '<=', $request->end_date . ' 23:59:59');
            }
            if ($request->has('created_by') && !empty($request->created_by)) {
                $query->whereIn('leads.created_by', (array)$request->created_by);
            }
            if ($request->has('modified_by') && !empty($request->modified_by)) {
                $query->whereIn('leads.updated_by', (array)$request->modified_by);
            }
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('leads.name', 'like', "%$search%")
                      ->orWhere('leads.subject', 'like', "%$search%");
                });
            }
            // Custom Fields Filter
            if ($request->has('custom_fields') && !empty($request->custom_fields)) {
                foreach ($request->custom_fields as $fieldId => $value) {
                    if (!empty($value)) {
                        $query->whereHas('customFieldValues', function($q) use ($fieldId, $value) {
                            $q->where('field_id', $fieldId)
                              ->where('value', 'like', "%$value%");
                        });
                    }
                }
            }
            
            // Department & Designation Filters (HRM Integration)
            if (module_is_active('Hrm')) {
                if ($request->has('department_id') && !empty($request->department_id)) {
                    $departmentIds = (array)$request->department_id;
                    $employeeUserIds = \Workdo\Hrm\Entities\Employee::whereIn('department_id', $departmentIds)->where('workspace', getActiveWorkSpace())->pluck('user_id')->toArray();
                    
                    $query->where(function($q) use ($employeeUserIds) {
                        $q->whereIn('leads.user_id', $employeeUserIds)
                          ->orWhereHas('users', function($subQ) use ($employeeUserIds) {
                              $subQ->whereIn('users.id', $employeeUserIds);
                          });
                    });
                }

                if ($request->has('designation_id') && !empty($request->designation_id)) {
                    $designationIds = (array)$request->designation_id;
                    $employeeUserIds = \Workdo\Hrm\Entities\Employee::whereIn('designation_id', $designationIds)->where('workspace', getActiveWorkSpace())->pluck('user_id')->toArray();

                    $query->where(function($q) use ($employeeUserIds) {
                        $q->whereIn('leads.user_id', $employeeUserIds)
                          ->orWhereHas('users', function($subQ) use ($employeeUserIds) {
                              $subQ->whereIn('users.id', $employeeUserIds);
                          });
                    });
                }
            }
        }

        $query->orderBy('leads.created_at', 'DESC');

        if ($limit !== null) {
            $query->skip($offset)->take($limit);
        }

        return $query->get();
    }

    public function leadCount(Request $request = null)
    {
        $user = Auth::user();
        $query = Lead::where('leads.stage_id', '=', $this->id)
                     ->where('leads.workspace_id', '=', getActiveWorkSpace());

        if ($user->type == 'client') {
            $query->join('client_leads', 'client_leads.lead_id', '=', 'leads.id')
                  ->where('client_leads.client_id', '=', $user->id);
        } elseif ($user->type != 'company' && $user->visibility_level != 'all') {
            $accessibleUserIds = $user->getAccessibleUserIds();
            $query->where(function($q) use ($accessibleUserIds) {
                $q->whereIn('leads.user_id', $accessibleUserIds)
                  ->orWhereHas('users', function($subQ) use ($accessibleUserIds) {
                      $subQ->whereIn('users.id', $accessibleUserIds);
                  });
            });
        }

        if ($request) {
            // Apply same filters as lead() method
            if ($request->has('responsible_person') && !empty($request->responsible_person)) {
                $respIds = (array)$request->responsible_person;
                $query->where(function($q) use ($respIds) {
                    $q->whereIn('leads.user_id', $respIds)
                      ->orWhereHas('users', function($subQ) use ($respIds) {
                          $subQ->whereIn('users.id', $respIds);
                      });
                });
            }
            if ($request->has('source_id') && !empty($request->source_id)) {
                $query->where(function($q) use ($request) {
                    foreach ((array)$request->source_id as $source) {
                        $q->orWhereRaw('FIND_IN_SET(?, leads.sources)', [$source]);
                    }
                });
            }
            if ($request->has('start_date') && !empty($request->start_date)) {
                $query->where('leads.created_at', '>=', $request->start_date . ' 00:00:00');
            }
            if ($request->has('end_date') && !empty($request->end_date)) {
                $query->where('leads.created_at', '<=', $request->end_date . ' 23:59:59');
            }
            if ($request->has('created_by') && !empty($request->created_by)) {
                $query->whereIn('leads.created_by', (array)$request->created_by);
            }
            if ($request->has('modified_by') && !empty($request->modified_by)) {
                $query->whereIn('leads.updated_by', (array)$request->modified_by);
            }
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('leads.name', 'like', "%$search%")
                      ->orWhere('leads.subject', 'like', "%$search%");
                });
            }
            // Custom Fields Filter
            if ($request->has('custom_fields') && !empty($request->custom_fields)) {
                foreach ($request->custom_fields as $fieldId => $value) {
                    if (!empty($value)) {
                        $query->whereHas('customFieldValues', function($q) use ($fieldId, $value) {
                            $q->where('field_id', $fieldId)
                              ->where('value', 'like', "%$value%");
                        });
                    }
                }
            }
            
            // Department & Designation Filters (HRM Integration)
            if (module_is_active('Hrm')) {
                if ($request->has('department_id') && !empty($request->department_id)) {
                    $departmentIds = (array)$request->department_id;
                    $employeeUserIds = \Workdo\Hrm\Entities\Employee::whereIn('department_id', $departmentIds)->where('workspace', getActiveWorkSpace())->pluck('user_id')->toArray();
                    
                    $query->where(function($q) use ($employeeUserIds) {
                        $q->whereIn('leads.user_id', $employeeUserIds)
                          ->orWhereHas('users', function($subQ) use ($employeeUserIds) {
                              $subQ->whereIn('users.id', $employeeUserIds);
                          });
                    });
                }

                if ($request->has('designation_id') && !empty($request->designation_id)) {
                    $designationIds = (array)$request->designation_id;
                    $employeeUserIds = \Workdo\Hrm\Entities\Employee::whereIn('designation_id', $designationIds)->where('workspace', getActiveWorkSpace())->pluck('user_id')->toArray();

                    $query->where(function($q) use ($employeeUserIds) {
                        $q->whereIn('leads.user_id', $employeeUserIds)
                          ->orWhereHas('users', function($subQ) use ($employeeUserIds) {
                              $subQ->whereIn('users.id', $employeeUserIds);
                          });
                    });
                }
            }
        }

        return $query->count();
    }

    public function permissions($user = null)
    {
        $user = $user ?? Auth::user();
        if ($user->type == 'company') {
            return (object)['can_view' => true, 'can_move' => true];
        }

        // Check user-level override
        $userPerm = LeadStagePermission::where('stage_id', $this->id)->where('user_id', $user->id)->first();
        if ($userPerm) {
            return $userPerm;
        }

        // Check role-level default
        $roleId = $user->roles->first()?->id;
        if ($roleId) {
            $rolePerm = LeadStagePermission::where('stage_id', $this->id)->where('role_id', $roleId)->first();
            if ($rolePerm) {
                return $rolePerm;
            }
        }

        // Default permission if nothing defined
        return (object)['can_view' => true, 'can_move' => true];
    }

    public function pipeline()
    {
        return $this->hasOne('Workdo\Lead\Entities\Pipeline', 'id', 'pipeline_id');
    }

}
