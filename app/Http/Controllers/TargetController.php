<?php

namespace App\Http\Controllers;

use App\Models\Target;
use App\Models\TargetTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TargetController extends Controller
{
    private function getSubordinateUserIds($userId)
    {
        if (!module_is_active('Hrm')) {
            return [];
        }
        
        $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $userId)->first();
        if (!$employee) return [];

        $managedDepartments = \Workdo\Hrm\Entities\Department::where('manager_id', $employee->id)->get();
        if ($managedDepartments->isEmpty()) return [];

        $allDeptIds = [];
        foreach ($managedDepartments as $dept) {
            $allDeptIds = array_merge($allDeptIds, $dept->allChildIds());
        }

        return \Workdo\Hrm\Entities\Employee::whereIn('department_id', $allDeptIds)
            ->where('user_id', '!=', $userId)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique()
            ->toArray();
    }

    private function resolveManagerForUser($userId)
    {
        if (!module_is_active('Hrm')) {
            return creatorId();
        }

        $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $userId)->first();
        if (!$employee) {
            return creatorId();
        }

        $dept = \Workdo\Hrm\Entities\Department::find($employee->department_id);
        if (!$dept) {
            return creatorId();
        }

        // Case 1: If user belongs to a team, the team's manager is the responsible person
        if ($dept->type == 'team') {
            if ($dept->manager_id) {
                $mgr = \Workdo\Hrm\Entities\Employee::find($dept->manager_id);
                if ($mgr && $mgr->user_id) {
                    return $mgr->user_id;
                }
            }
            
            // If team doesn't have a manager, check parent department's manager
            if ($dept->parent_id) {
                $parentDept = \Workdo\Hrm\Entities\Department::find($dept->parent_id);
                if ($parentDept && $parentDept->manager_id) {
                    $mgr = \Workdo\Hrm\Entities\Employee::find($parentDept->manager_id);
                    if ($mgr && $mgr->user_id) {
                        return $mgr->user_id;
                    }
                }
            }
        }

        // Case 2: If user belongs to a department, the department's manager is the responsible person
        if ($dept->type == 'department') {
            if ($dept->manager_id) {
                $mgr = \Workdo\Hrm\Entities\Employee::find($dept->manager_id);
                if ($mgr && $mgr->user_id) {
                    return $mgr->user_id;
                }
            }
        }

        // Fallback: Check parent department manager if any
        if ($dept->parent_id) {
            $parentDept = \Workdo\Hrm\Entities\Department::find($dept->parent_id);
            if ($parentDept && $parentDept->manager_id) {
                $mgr = \Workdo\Hrm\Entities\Employee::find($parentDept->manager_id);
                if ($mgr && $mgr->user_id) {
                    return $mgr->user_id;
                }
            }
        }

        return creatorId();
    }

    private function resolveResponsibleUser($assignmentType, $entityId)
    {
        if ($assignmentType == 'company') {
            $companyOwner = User::where('workspace_id', $entityId)->where('type', 'company')->first();
            return $companyOwner ? $companyOwner->id : creatorId();
        }

        if ($assignmentType == 'individual') {
            return $this->resolveManagerForUser($entityId);
        }

        // department or team
        $resolvedManagerId = null;
        if (module_is_active('Hrm') && $entityId) {
            $dept = \Workdo\Hrm\Entities\Department::find($entityId);
            if ($dept && $dept->manager_id) {
                $mgr = \Workdo\Hrm\Entities\Employee::find($dept->manager_id);
                if ($mgr && $mgr->user_id) {
                    $resolvedManagerId = $mgr->user_id;
                }
            }
        }
        return $resolvedManagerId ?: creatorId();
    }

    private function canManageTargets($user, $target = null)
    {
        if ($user->type == 'company' || $user->type == 'super admin') {
            return true;
        }

        // Check if user has subordinates
        if (count($this->getSubordinateUserIds($user->id)) > 0) {
            return true;
        }

        // Check if user is the responsible person for a specific target and has edit rights
        if ($target && $target->responsible_user_id == $user->id && $target->can_edit) {
            return true;
        }

        return false;
    }

    public function index(Request $request)
    {
        $usr = Auth::user();
        $isCompany = ($usr->type == 'company' || $usr->type == 'super admin');
        $visibility = $usr->visibility_level ?? 'self';
        
        $isManager = $this->canManageTargets($usr) || Target::where('responsible_user_id', $usr->id)->exists();
        $subordinateIds = $this->getSubordinateUserIds($usr->id);
        if ($isCompany) {
            $subordinateIds = User::where('workspace_id', getActiveWorkSpace())->where('type', '!=', 'client')->where('id', '!=', $usr->id)->pluck('id')->toArray();
        }

        // ── Hierarchy context variables ───────────────────────────────────────
        $isDeptHead   = false;
        $isTeamLead   = false;
        $myDeptTarget = null;
        $myTeamTarget = null;
        $myDept       = null;
        $myTeam       = null;

        if (!$isCompany && module_is_active('Hrm')) {
            $myEmployee = \Workdo\Hrm\Entities\Employee::where('user_id', $usr->id)->first();
            if ($myEmployee) {
                $myDeptObj = \Workdo\Hrm\Entities\Department::find($myEmployee->department_id);
                if ($myDeptObj) {
                    if ($myDeptObj->type == 'team') {
                        // User belongs to a team — check if they are its manager
                        if ($myDeptObj->manager_id == $myEmployee->id) {
                            $isTeamLead   = true;
                            $myTeam       = $myDeptObj;
                            $myTeamTarget = Target::where('workspace', getActiveWorkSpace())
                                ->where('team_id', $myDeptObj->id)
                                ->latest()->first();
                        }
                        // Also check if the parent dept's manager is this user
                        if ($myDeptObj->parent_id) {
                            $parentDept = \Workdo\Hrm\Entities\Department::find($myDeptObj->parent_id);
                            if ($parentDept && $parentDept->manager_id == $myEmployee->id) {
                                $isDeptHead   = true;
                                $myDept       = $parentDept;
                                $myDeptTarget = Target::where('workspace', getActiveWorkSpace())
                                    ->where('department_id', $parentDept->id)
                                    ->latest()->first();
                            }
                        }
                    } elseif ($myDeptObj->type == 'department') {
                        // User belongs to a department — check if they are its manager
                        if ($myDeptObj->manager_id == $myEmployee->id) {
                            $isDeptHead   = true;
                            $myDept       = $myDeptObj;
                            $myDeptTarget = Target::where('workspace', getActiveWorkSpace())
                                ->where('department_id', $myDeptObj->id)
                                ->latest()->first();
                        }
                    }
                }
            }
        }
        // ──────────────────────────────────────────────────────────────────────

        $query = Target::where('workspace', getActiveWorkSpace());

        // Hierarchy visibility filtering
        if (!$isCompany) {
            if ($visibility == 'all') {
                // Can see everything in the workspace
            } elseif ($visibility == 'department' && module_is_active('Hrm')) {
                $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $usr->id)->first();
                if ($employee) {
                    $myDeptId = $employee->department_id;
                    $dept = \Workdo\Hrm\Entities\Department::find($myDeptId);
                    $allDeptIds = $dept ? array_merge([$myDeptId], $dept->allChildIds()) : [$myDeptId];
                    $deptUserIds = \Workdo\Hrm\Entities\Employee::whereIn('department_id', $allDeptIds)->pluck('user_id')->toArray();
                    
                    $query->where(function($q) use ($usr, $allDeptIds, $deptUserIds) {
                        $q->where('assigned_to', $usr->id)
                          ->orWhere('responsible_user_id', $usr->id)
                          ->orWhere('assigned_by', $usr->id)
                          ->orWhereIn('department_id', $allDeptIds)
                          ->orWhereIn('team_id', $allDeptIds)
                          ->orWhereIn('assigned_to', $deptUserIds);
                    });
                } else {
                    $query->where(function($q) use ($usr) {
                        $q->where('assigned_to', $usr->id)
                          ->orWhere('responsible_user_id', $usr->id);
                    });
                }
            } elseif ($visibility == 'team' && module_is_active('Hrm')) {
                $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $usr->id)->first();
                if ($employee) {
                    $myTeamId = $employee->department_id;
                    $teamUserIds = \Workdo\Hrm\Entities\Employee::where('department_id', $myTeamId)->pluck('user_id')->toArray();
                    
                    $query->where(function($q) use ($usr, $myTeamId, $teamUserIds) {
                        $q->where('assigned_to', $usr->id)
                          ->orWhere('responsible_user_id', $usr->id)
                          ->orWhere('assigned_by', $usr->id)
                          ->orWhere('team_id', $myTeamId)
                          ->orWhereIn('assigned_to', $teamUserIds);
                    });
                } else {
                    $query->where(function($q) use ($usr) {
                        $q->where('assigned_to', $usr->id)
                          ->orWhere('responsible_user_id', $usr->id);
                    });
                }
            } else { // 'self'
                $query->where(function ($q) use ($usr) {
                    $q->where('assigned_to', $usr->id)
                      ->orWhere('responsible_user_id', $usr->id);
                });
            }
        }

        // View mine toggle for managers
        if ($isManager && $request->has('view_mine') && $request->view_mine == 1) {
            $query->where('assigned_to', $usr->id);
        }

        // Advanced Filters
        if ($request->has('assigned_to') && !empty($request->assigned_to)) {
            $query->whereIn('assigned_to', (array) $request->assigned_to);
        }
        if ($request->has('status') && !empty($request->status)) {
            $query->whereIn('status', (array) $request->status);
        }
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('end_date', '<=', $request->end_date);
        }
        if ($request->has('department_id') && !empty($request->department_id)) {
            $query->where(function($q) use ($request) {
                $q->whereIn('targets.department_id', (array)$request->department_id)
                  ->orWhereHas('assignedToUser.employee', function($eq) use ($request) {
                      $eq->whereIn('department_id', (array)$request->department_id);
                  });
            });
        }
        if ($request->has('team_id') && !empty($request->team_id)) {
            $query->where(function($q) use ($request) {
                $q->whereIn('targets.team_id', (array)$request->team_id)
                  ->orWhereHas('assignedToUser.employee', function($eq) use ($request) {
                      $eq->whereIn('department_id', (array)$request->team_id);
                  });
            });
        }

        $targets = $query->orderBy('id', 'desc')->get();

        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        $today = date('Y-m-d');
        
        $last30DaysTargets = Target::where('workspace', getActiveWorkSpace())
            ->where(function($q) use ($thirtyDaysAgo, $today) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', $today);
            })
            ->where(function($q) use ($thirtyDaysAgo) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $thirtyDaysAgo);
            })
            ->get();

        // Dashboard Stats
        $stats = [
            'total' => $targets->count(),
            'completed' => $targets->where('status', 'Completed')->count(),
            'pending' => $targets->where('status', 'Pending')->count(),
            'achieved_total' => $targets->sum('achieved_value'),
            'target_total' => $targets->sum('target_value'),
            'last30_target' => $last30DaysTargets->sum('target_value'),
            'last30_achieved' => $last30DaysTargets->sum('achieved_value'),
            'earned_incentive' => $targets->where('status', 'Completed')->sum('incentive'),
            'pending_incentive' => $targets->where('status', '!=', 'Completed')->sum('incentive'),
        ];

        // Compile Incentive Ledger Breakdown
        $departmentLedger = [];
        $teamLedger = [];
        $memberLedger = [];

        foreach ($targets as $t) {
            $incentive = (float)($t->incentive ?? 0.00);
            if ($incentive <= 0) {
                continue;
            }

            $isCompleted = ($t->status == 'Completed');

            if ($t->department_id > 0) {
                $deptId = $t->department_id;
                if (!isset($departmentLedger[$deptId])) {
                    $departmentLedger[$deptId] = [
                        'name' => $t->department ? $t->department->name : __('Unknown Department'),
                        'manager' => $t->responsibleUser ? $t->responsibleUser->name : __('N/A'),
                        'earned' => 0.00,
                        'pending' => 0.00,
                    ];
                }
                if ($isCompleted) {
                    $departmentLedger[$deptId]['earned'] += $incentive;
                } else {
                    $departmentLedger[$deptId]['pending'] += $incentive;
                }
            } elseif ($t->team_id > 0) {
                $teamId = $t->team_id;
                if (!isset($teamLedger[$teamId])) {
                    $teamLedger[$teamId] = [
                        'name' => $t->team ? $t->team->name : __('Unknown Team'),
                        'manager' => $t->responsibleUser ? $t->responsibleUser->name : __('N/A'),
                        'earned' => 0.00,
                        'pending' => 0.00,
                    ];
                }
                if ($isCompleted) {
                    $teamLedger[$teamId]['earned'] += $incentive;
                } else {
                    $teamLedger[$teamId]['pending'] += $incentive;
                }
            } elseif ($t->assigned_to > 0) {
                $memberId = $t->assigned_to;
                if (!isset($memberLedger[$memberId])) {
                    $memberLedger[$memberId] = [
                        'name' => $t->assignedToUser ? $t->assignedToUser->name : __('Unknown Member'),
                        'earned' => 0.00,
                        'pending' => 0.00,
                    ];
                }
                if ($isCompleted) {
                    $memberLedger[$memberId]['earned'] += $incentive;
                } else {
                    $memberLedger[$memberId]['pending'] += $incentive;
                }
            }
        }

        // Top Performer Individual
        $topIndividual = Target::where('workspace', getActiveWorkSpace())
            ->where('status', 'Completed')
            ->selectRaw('assigned_to, count(*) as count')
            ->groupBy('assigned_to')
            ->orderBy('count', 'desc')
            ->first();
        $topIndividualUser = $topIndividual ? User::find($topIndividual->assigned_to) : null;
        $stats['top_individual'] = $topIndividualUser ? $topIndividualUser->name : 'N/A';

        // Top Performer Team
        $stats['top_team'] = 'N/A';
        if (module_is_active('Hrm')) {
            $topTeamId = Target::where('targets.workspace', getActiveWorkSpace())
                ->where('targets.status', 'Completed')
                ->join('employees', 'targets.assigned_to', '=', 'employees.user_id')
                ->selectRaw('employees.department_id, count(*) as count')
                ->groupBy('employees.department_id')
                ->orderBy('count', 'desc')
                ->first();
            if ($topTeamId) {
                $team = \Workdo\Hrm\Entities\Department::find($topTeamId->department_id);
                $stats['top_team'] = $team ? $team->name : 'N/A';
            }
        }

        // Team Performance Comparison Leaderboard
        $teamPerformance = [];
        if (module_is_active('Hrm')) {
            $allTeams = \Workdo\Hrm\Entities\Department::where('type', 'team')->where('workspace', getActiveWorkSpace())->get();
            foreach ($allTeams as $team) {
                // Get all targets assigned to this team OR to individuals in this team
                $teamTargets = Target::where('workspace', getActiveWorkSpace())
                    ->where(function($q) use ($team) {
                        $q->where('team_id', $team->id)
                          ->orWhereHas('assignedToUser.employee', function($eq) use ($team) {
                              $eq->where('department_id', $team->id);
                          });
                    })->get();
                
                if ($teamTargets->count() > 0) {
                    $tVal = $teamTargets->sum('target_value');
                    $aVal = $teamTargets->sum('achieved_value');
                    $teamPerformance[] = [
                        'id' => $team->id,
                        'name' => $team->name,
                        'progress' => $tVal > 0 ? round(($aVal / $tVal) * 100, 1) : 0,
                        'target' => $tVal,
                        'achieved' => $aVal
                    ];
                }
            }
            usort($teamPerformance, function($a, $b) {
                return $b['progress'] <=> $a['progress'];
            });
        }

        // Monthly Trend
        $monthlyTarget = array_fill(1, 12, 0);
        $monthlyAchieved = array_fill(1, 12, 0);
        
        $monthlyData = Target::where('workspace', getActiveWorkSpace())
            ->whereYear('start_date', date('Y'))
            ->selectRaw('MONTH(start_date) as month, SUM(target_value) as total_target, SUM(achieved_value) as total_achieved')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
            
        foreach ($monthlyData as $data) {
            $monthlyTarget[$data->month] = (int) $data->total_target;
            $monthlyAchieved[$data->month] = (int) $data->total_achieved;
        }
        
        $stats['monthly_target'] = array_values($monthlyTarget);
        $stats['monthly_achieved'] = array_values($monthlyAchieved);
        $stats['monthly_labels'] = [__('Jan'), __('Feb'), __('Mar'), __('Apr'), __('May'), __('Jun'), __('Jul'), __('Aug'), __('Sep'), __('Oct'), __('Nov'), __('Dec')];

        // Aggregated Performance Breakdown
        $unitPerformance = [];
        if (module_is_active('Hrm')) {
            if ($isCompany) {
                // Admin/Company sees all departments (type == 'department') that have targets
                $departments = \Workdo\Hrm\Entities\Department::where('workspace', getActiveWorkSpace())
                    ->where('type', 'department')
                    ->get();
                foreach ($departments as $dept) {
                    $deptTargets = Target::where('workspace', getActiveWorkSpace())
                        ->where(function($q) use ($dept) {
                            $q->where('department_id', $dept->id)
                              ->orWhereHas('assignedToUser.employee', function($eq) use ($dept) {
                                  $eq->where('department_id', $dept->id);
                              });
                        })->get();
                    if ($deptTargets->count() > 0) {
                        $tVal = $deptTargets->sum('target_value');
                        $aVal = $deptTargets->sum('achieved_value');
                        $unitPerformance[] = [
                            'id' => $dept->id,
                            'name' => $dept->name,
                            'type' => $dept->type,
                            'progress' => $tVal > 0 ? round(($aVal / $tVal) * 100, 1) : 0,
                            'target' => $tVal,
                            'achieved' => $aVal
                        ];
                    }
                }
            } elseif ($isDeptHead && $myDept) {
                // Department Head sees only the teams under their department that have targets
                $childTeams = \Workdo\Hrm\Entities\Department::where('workspace', getActiveWorkSpace())
                    ->where('parent_id', $myDept->id)
                    ->where('type', 'team')
                    ->get();
                foreach ($childTeams as $team) {
                    $teamTargets = Target::where('workspace', getActiveWorkSpace())
                        ->where(function($q) use ($team) {
                            $q->where('team_id', $team->id)
                              ->orWhereHas('assignedToUser.employee', function($eq) use ($team) {
                                  $eq->where('department_id', $team->id);
                              });
                        })->get();
                    if ($teamTargets->count() > 0) {
                        $tVal = $teamTargets->sum('target_value');
                        $aVal = $teamTargets->sum('achieved_value');
                        $unitPerformance[] = [
                            'id' => $team->id,
                            'name' => $team->name,
                            'type' => $team->type,
                            'progress' => $tVal > 0 ? round(($aVal / $tVal) * 100, 1) : 0,
                            'target' => $tVal,
                            'achieved' => $aVal
                        ];
                    }
                }
            } elseif ($isTeamLead && $myTeam) {
                // Team Lead sees members of their team that have targets
                $memberUserIds = \Workdo\Hrm\Entities\Employee::where('department_id', $myTeam->id)
                    ->whereNotNull('user_id')
                    ->pluck('user_id')
                    ->toArray();
                $members = User::whereIn('id', $memberUserIds)->get();
                foreach ($members as $member) {
                    $memberTargets = Target::where('workspace', getActiveWorkSpace())
                        ->where('assigned_to', $member->id)
                        ->get();
                    if ($memberTargets->count() > 0) {
                        $tVal = $memberTargets->sum('target_value');
                        $aVal = $memberTargets->sum('achieved_value');
                        $unitPerformance[] = [
                            'id' => $member->id,
                            'name' => $member->name,
                            'type' => 'member',
                            'progress' => $tVal > 0 ? round(($aVal / $tVal) * 100, 1) : 0,
                            'target' => $tVal,
                            'achieved' => $aVal
                        ];
                    }
                }
            } else {
                // Regular Member sees only their own target performance card (if they have targets)
                $myTargets = Target::where('workspace', getActiveWorkSpace())
                    ->where('assigned_to', $usr->id)
                    ->get();
                if ($myTargets->count() > 0) {
                    $tVal = $myTargets->sum('target_value');
                    $aVal = $myTargets->sum('achieved_value');
                    $unitPerformance[] = [
                        'id' => $usr->id,
                        'name' => $usr->name,
                        'type' => 'member',
                        'progress' => $tVal > 0 ? round(($aVal / $tVal) * 100, 1) : 0,
                        'target' => $tVal,
                        'achieved' => $aVal
                    ];
                }
            }
        }

        // Setup Frontend variables
        $departments = [];
        $teams = [];
        if (module_is_active('Hrm')) {
            if (!$isCompany) {
                $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $usr->id)->first();
                if ($employee) {
                    // Start with departments managed by the user
                    $managedDeptIds = \Workdo\Hrm\Entities\Department::where('manager_id', $employee->id)->get()->flatMap(function($d) {
                        return array_merge([$d->id], $d->allChildIds());
                    })->toArray();

                    $myDeptId = $employee->department_id;
                    $dept = \Workdo\Hrm\Entities\Department::find($myDeptId);

                    if ($visibility == 'all') {
                        $allowedIds = \Workdo\Hrm\Entities\Department::where('workspace', getActiveWorkSpace())->pluck('id')->toArray();
                    } elseif ($visibility == 'department') {
                        $allDeptIds = $dept ? array_merge([$myDeptId], $dept->allChildIds()) : [$myDeptId];
                        $allowedIds = array_merge($managedDeptIds, $allDeptIds);
                    } elseif ($visibility == 'team') {
                        $allowedIds = array_merge($managedDeptIds, [$myDeptId]);
                    } else { // 'self'
                        $allowedIds = array_merge($managedDeptIds, [$myDeptId]);
                    }

                    $allowedIds = array_unique(array_filter($allowedIds));

                    $departments = \Workdo\Hrm\Entities\Department::whereIn('id', $allowedIds)->where('type', 'department')->where('workspace', getActiveWorkSpace())->pluck('name', 'id');
                    $teams = \Workdo\Hrm\Entities\Department::whereIn('id', $allowedIds)->where('type', 'team')->where('workspace', getActiveWorkSpace())->pluck('name', 'id');
                }
            } else {
                $departments = \Workdo\Hrm\Entities\Department::where('type', 'department')->where('workspace', getActiveWorkSpace())->pluck('name', 'id');
                $teams = \Workdo\Hrm\Entities\Department::where('type', 'team')->where('workspace', getActiveWorkSpace())->pluck('name', 'id');
            }
        }

        $allowedUserIds = array_unique(array_merge([$usr->id], $subordinateIds));
        $subordinateUsers = User::whereIn('id', $allowedUserIds)->pluck('name', 'id');
        $statuses = ['Pending' => __('Pending'), 'Completed' => __('Completed'), 'Missed' => __('Missed')];

        $employeePerformance = [];
        $allTargets = Target::where('workspace', getActiveWorkSpace())->get();
        $assignedUserIds = $allTargets->where('assigned_to', '>', 0)->pluck('assigned_to')->unique()->toArray();
        $usersWithTargets = User::whereIn('id', $assignedUserIds)->get();
        foreach ($usersWithTargets as $user) {
            $userTargets = $allTargets->where('assigned_to', $user->id);
            $tVal = $userTargets->sum('target_value');
            $aVal = $userTargets->sum('achieved_value');
            
            $deptName = 'N/A';
            if (module_is_active('Hrm') && $user->employee) {
                $deptName = $user->employee->department ? $user->employee->department->name : 'N/A';
            }
            
            $employeePerformance[] = [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ? asset('storage/uploads/avatar/'.$user->avatar) : asset('storage/uploads/avatar/avatar.png'),
                'department' => $deptName,
                'progress' => $tVal > 0 ? round(($aVal / $tVal) * 100, 1) : 0,
                'target' => $tVal,
                'achieved' => $aVal,
                'remaining' => max(0, $tVal - $aVal)
            ];
        }
        usort($employeePerformance, function($a, $b) {
            return $b['progress'] <=> $a['progress'];
        });

        $templates = TargetTemplate::where('workspace', getActiveWorkSpace())->get();
        return view('targets.index', compact(
            'targets', 'isManager', 'subordinateUsers', 'statuses', 'departments', 'teams',
            'stats', 'unitPerformance', 'teamPerformance', 'templates', 'employeePerformance',
            'isDeptHead', 'isTeamLead', 'myDeptTarget', 'myTeamTarget', 'myDept', 'myTeam',
            'departmentLedger', 'teamLedger', 'memberLedger'
        ));
    }

    public function create(Request $request)
    {
        $usr = Auth::user();
        if ($this->canManageTargets($usr)) {
            $subordinateIds = $this->getSubordinateUserIds($usr->id);
            if ($usr->type == 'company' || $usr->type == 'super admin') {
                $subordinateIds = User::where('workspace_id', getActiveWorkSpace())->where('type', '!=', 'client')->where('id', '!=', $usr->id)->pluck('id')->toArray();
            }
            $users = User::whereIn('id', $subordinateIds)->pluck('name', 'id')->toArray();

            $departments = [];
            $teams = [];
            if (module_is_active('Hrm')) {
                $departments = \Workdo\Hrm\Entities\Department::where('type', 'department')->where('workspace', getActiveWorkSpace())->pluck('name', 'id')->toArray();
                $teams = \Workdo\Hrm\Entities\Department::where('type', 'team')->where('workspace', getActiveWorkSpace())->pluck('name', 'id')->toArray();
            }

            $parent_id = $request->get('parent_id', null);
            $parentTarget = null;
            // Context variables for hierarchy-aware form
            $assignmentContext = 'free';  // 'teams_only' | 'members_only' | 'free'
            $contextLabel      = '';       // Human-readable hint
            $restrictedTeams   = [];       // Teams under parent dept
            $restrictedUsers   = [];       // Members of parent team

            if ($parent_id) {
                $parentTarget = Target::find($parent_id);

                if ($parentTarget && module_is_active('Hrm')) {
                    // Parent assigned to a DEPARTMENT → sub-targets must go to teams under that dept
                    if ($parentTarget->department_id > 0) {
                        $assignmentContext = 'teams_only';
                        $parentDept        = \Workdo\Hrm\Entities\Department::find($parentTarget->department_id);
                        if ($parentDept) {
                            $contextLabel    = $parentDept->name;
                            $childDeptIds    = $parentDept->children->pluck('id')->toArray();
                            $restrictedTeams = \Workdo\Hrm\Entities\Department::whereIn('id', $childDeptIds)
                                ->where('type', 'team')
                                ->pluck('name', 'id')
                                ->toArray();
                            // Fallback: if no child teams, show all teams of workspace
                            if (empty($restrictedTeams)) {
                                $restrictedTeams = \Workdo\Hrm\Entities\Department::where('type', 'team')
                                    ->where('workspace', getActiveWorkSpace())
                                    ->pluck('name', 'id')
                                    ->toArray();
                            }
                        }
                    }
                    // Parent assigned to a TEAM → sub-targets must go to members of that team
                    elseif ($parentTarget->team_id > 0) {
                        $assignmentContext = 'members_only';
                        $parentTeam        = \Workdo\Hrm\Entities\Department::find($parentTarget->team_id);
                        if ($parentTeam) {
                            $contextLabel   = $parentTeam->name;
                            $memberUserIds  = \Workdo\Hrm\Entities\Employee::where('department_id', $parentTeam->id)
                                ->whereNotNull('user_id')
                                ->pluck('user_id')
                                ->toArray();
                            $restrictedUsers = User::whereIn('id', $memberUserIds)
                                ->pluck('name', 'id')
                                ->toArray();
                        }
                    }
                }
            }

            // Find all managers for "Responsible" dropdown
            $allManagers = User::where('workspace_id', getActiveWorkSpace())->where('type', '!=', 'client')->get()->filter(function($u){
                return count($this->getSubordinateUserIds($u->id)) > 0 || $u->type == 'company' || $u->type == 'super admin';
            })->pluck('name', 'id')->toArray();

            // Pipelines selection for automated tracking
            $pipelines = [];
            $customDateFields = [];
            if (module_is_active('Lead')) {
                $pipelines = \Workdo\Lead\Entities\Pipeline::where('workspace_id', getActiveWorkSpace())->pluck('name', 'id')->toArray();
                $customDateFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', getActiveWorkSpace())
                    ->where('type', 'date')
                    ->pluck('name', 'id')
                    ->toArray();
            }

            return view('targets.create', compact(
                'users', 'departments', 'teams', 'parentTarget', 'allManagers',
                'pipelines', 'customDateFields',
                'assignmentContext', 'contextLabel', 'restrictedTeams', 'restrictedUsers'
            ));
        }
        return response()->json(['error' => __('Permission Denied.')], 401);
    }

    public function store(Request $request)
    {
        $usr = Auth::user();
        if ($this->canManageTargets($usr)) {
            $validator = \Validator::make($request->all(), [
                'target_name' => 'required|string|max:120'
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            // Extract dynamic allocations and incentives if present
            $entities = [];
            $allocations = [];
            $incentives = [];
            if ($request->has('individual_targets')) {
                foreach ($request->individual_targets as $uid => $val) {
                    if ($val > 0) {
                        $entities[] = $uid;
                        $allocations[$uid] = $val;
                        $incentives[$uid] = isset($request->individual_incentives[$uid]) ? (float)$request->individual_incentives[$uid] : 0.00;
                    }
                }
                $request->merge(['assignment_type' => 'individual']);
            } elseif ($request->has('team_targets')) {
                foreach ($request->team_targets as $tid => $val) {
                    if ($val > 0) {
                        $entities[] = $tid;
                        $allocations[$tid] = $val;
                        $incentives[$tid] = isset($request->team_incentives[$tid]) ? (float)$request->team_incentives[$tid] : 0.00;
                    }
                }
                $request->merge(['assignment_type' => 'team']);
            } elseif ($request->has('department_targets')) {
                foreach ($request->department_targets as $did => $val) {
                    if ($val > 0) {
                        $entities[] = $did;
                        $allocations[$did] = $val;
                        $incentives[$did] = isset($request->department_incentives[$did]) ? (float)$request->department_incentives[$did] : 0.00;
                    }
                }
                $request->merge(['assignment_type' => 'department']);
            } else {
                // Fallback to legacy behavior
                if ($request->assignment_type == 'individual') {
                    $entities = (array) $request->assigned_to;
                } elseif ($request->assignment_type == 'department') {
                    $entities = (array) $request->department_id;
                } elseif ($request->assignment_type == 'team') {
                    $entities = (array) $request->team_id;
                }
                foreach ($entities as $entity_id) {
                    $allocations[$entity_id] = $request->target_value;
                    $incentives[$entity_id] = (float)($request->incentive ?? 0.00);
                }
            }

            if (empty($entities)) {
                return redirect()->back()->with('error', __('Please assign target quantity to at least one individual, department, or team.'));
            }

            if (($usr->type == 'company' || $usr->type == 'super admin') && $request->assignment_type == 'individual' && !$request->parent_id) {
                return redirect()->back()->with('error', __('Admins can only assign targets to Departments or Teams.'));
            }

            if ($request->parent_id) {
                $parentTarget = Target::find($request->parent_id);
                if (!$parentTarget) {
                    return redirect()->back()->with('error', __('Parent target not found.'));
                }
                $isAuthorizedToAssignSub = ($usr->type == 'company' || $usr->type == 'super admin' || $parentTarget->assigned_by == $usr->id || $parentTarget->responsible_user_id == $usr->id);
                if (!$isAuthorizedToAssignSub) {
                    return redirect()->back()->with('error', __('Permission Denied to assign sub-targets under this target.'));
                }

                // ── Hierarchy enforcement ────────────────────────────────────────
                if ($parentTarget->department_id > 0 && $request->assignment_type === 'individual') {
                    return redirect()->back()->with('error', __('Sub-targets of a Department target must be assigned to Teams, not Individuals.'));
                }
                if ($parentTarget->team_id > 0 && $request->assignment_type !== 'individual') {
                    return redirect()->back()->with('error', __('Sub-targets of a Team target must be assigned to Individual members.'));
                }
                // ────────────────────────────────────────────────────────────────

                $currentSubTargetsSum = Target::where('parent_id', $request->parent_id)->sum('target_value');
                $newSubTargetsSum = $currentSubTargetsSum + array_sum($allocations);
                if ($newSubTargetsSum > $parentTarget->target_value) {
                    $available = max(0, $parentTarget->target_value - $currentSubTargetsSum);
                    return redirect()->back()->with('error', __('The total assigned sub-targets value exceeds the parent target value. Available limit to assign: ') . $available);
                }
            }

            // Check for duplicate target of same type in the same month before saving any target
            foreach ($entities as $entity_id) {
                if (empty($entity_id)) continue;

                $startDate = $request->parent_id ? Target::find($request->parent_id)->start_date : $request->start_date;
                $targetType = $request->parent_id ? Target::find($request->parent_id)->target_type : ($request->target_type ?? 'manual');
                $pipelineId = $request->parent_id ? Target::find($request->parent_id)->pipeline_id : $request->pipeline_id;
                $stageId = $request->parent_id ? Target::find($request->parent_id)->stage_id : $request->stage_id;
                $targetName = $request->parent_id ? Target::find($request->parent_id)->target_name : $request->target_name;

                $uniqueQuery = Target::where('workspace', getActiveWorkSpace())
                    ->whereYear('start_date', date('Y', strtotime($startDate)))
                    ->whereMonth('start_date', date('m', strtotime($startDate)));

                if ($request->assignment_type == 'individual') {
                    $uniqueQuery->where('assigned_to', $entity_id);
                } elseif ($request->assignment_type == 'department') {
                    $uniqueQuery->where('department_id', $entity_id);
                } elseif ($request->assignment_type == 'team') {
                    $uniqueQuery->where('team_id', $entity_id);
                }

                if ($targetType == 'lead_stage') {
                    $uniqueQuery->where('target_type', 'lead_stage')
                        ->where('pipeline_id', $pipelineId)
                        ->where('stage_id', $stageId);
                } else {
                    $uniqueQuery->where('target_type', 'manual')
                        ->where('target_name', $targetName);
                }

                if ($uniqueQuery->exists()) {
                    $entityName = '';
                    if ($request->assignment_type == 'individual') {
                        $user = User::find($entity_id);
                        $entityName = $user ? $user->name : __('Member');
                    } elseif ($request->assignment_type == 'department' && module_is_active('Hrm')) {
                        $dept = \Workdo\Hrm\Entities\Department::find($entity_id);
                        $entityName = $dept ? $dept->name : __('Department');
                    } elseif ($request->assignment_type == 'team' && module_is_active('Hrm')) {
                        $team = \Workdo\Hrm\Entities\Department::find($entity_id);
                        $entityName = $team ? $team->name : __('Team');
                    }
                    $msg = __('A target of this type is already assigned to :name for this month.', ['name' => $entityName]);
                    return redirect()->back()->with('error', $msg);
                }
            }

            foreach ($entities as $entity_id) {
                if (empty($entity_id)) continue;
                
                $target = new Target();
                $target->target_name = $request->target_name;
                $target->parent_id = $request->parent_id;
                $target->assigned_to = $request->assignment_type == 'individual' ? $entity_id : 0;
                $target->department_id = $request->assignment_type == 'department' ? $entity_id : 0;
                $target->team_id = $request->assignment_type == 'team' ? $entity_id : 0;
                $target->assigned_by = $usr->id;
                $target->responsible_user_id = $this->resolveResponsibleUser($request->assignment_type, $entity_id);
                $target->can_edit = $request->has('can_edit') ? 1 : 0;
                $target->start_date = $request->start_date;
                $target->end_date = $request->end_date;
                $target->target_value = $allocations[$entity_id];
                $target->incentive = isset($incentives[$entity_id]) ? $incentives[$entity_id] : 0.00;
                $target->achieved_value = 0;
                $target->status = 'Pending';
                $target->workspace = getActiveWorkSpace();
                $target->created_by = creatorId();
                
                $target->target_type = $request->target_type ?? 'manual';
                if ($target->target_type == 'lead_stage') {
                    $target->pipeline_id = $request->pipeline_id;
                    $target->stage_id = $request->stage_id;
                    $target->custom_date_field = $request->custom_date_field ?? 'created_at';
                }
                
                $target->save();

                if ($target->target_type == 'lead_stage') {
                    $target->recalculateAchievedValue();
                }
            }

            return redirect()->route('targets.index')->with('success', __('Targets successfully created.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function edit(Target $target)
    {
        $usr = Auth::user();
        if ($usr->type == 'company' || $usr->type == 'super admin' || $target->assigned_by == $usr->id) {
            $subordinateIds = $this->getSubordinateUserIds($usr->id);
            if ($usr->type == 'company' || $usr->type == 'super admin') {
                $subordinateIds = User::where('workspace_id', getActiveWorkSpace())->where('type', '!=', 'client')->where('id', '!=', $usr->id)->pluck('id')->toArray();
            }
            $users = User::whereIn('id', $subordinateIds)->pluck('name', 'id')->toArray();
            if($target->assigned_to && !isset($users[$target->assigned_to])){
                $assignedTo = User::find($target->assigned_to);
                if($assignedTo) $users[$target->assigned_to] = $assignedTo->name;
            }

            $departments = [];
            $teams = [];
            if (module_is_active('Hrm')) {
                $departments = \Workdo\Hrm\Entities\Department::where('type', 'department')->where('workspace', getActiveWorkSpace())->pluck('name', 'id')->toArray();
                $teams = \Workdo\Hrm\Entities\Department::where('type', 'team')->where('workspace', getActiveWorkSpace())->pluck('name', 'id')->toArray();
            }

            // Determine if current user can change the responsible person
            $canChangeResponsible = ($target->assigned_by == $usr->id || $usr->type == 'company' || $usr->type == 'super admin');

            $allManagers = User::where('workspace_id', getActiveWorkSpace())->where('type', '!=', 'client')->get()->filter(function($u){
                return count($this->getSubordinateUserIds($u->id)) > 0 || $u->type == 'company';
            })->pluck('name', 'id')->toArray();

            // Pipelines & Stages selection for automated tracking
            $pipelines = [];
            $stages = [];
            $customDateFields = [];
            if (module_is_active('Lead')) {
                $pipelines = \Workdo\Lead\Entities\Pipeline::where('workspace_id', getActiveWorkSpace())->pluck('name', 'id')->toArray();
                if ($target->pipeline_id) {
                    $stages = \Workdo\Lead\Entities\LeadStage::where('pipeline_id', $target->pipeline_id)->where('workspace_id', getActiveWorkSpace())->pluck('name', 'id')->toArray();
                }
                $customDateFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', getActiveWorkSpace())
                    ->where('type', 'date')
                    ->pluck('name', 'id')
                    ->toArray();
            }

            return view('targets.edit', compact('target', 'users', 'departments', 'teams', 'canChangeResponsible', 'allManagers', 'pipelines', 'stages', 'customDateFields'));
        }
        return response()->json(['error' => __('Permission Denied.')], 401);
    }

    public function update(Request $request, Target $target)
    {
        $usr = Auth::user();
        if ($usr->type == 'company' || $usr->type == 'super admin' || $target->assigned_by == $usr->id) {
            $validator = \Validator::make($request->all(), [
                'target_name' => 'required|string|max:120',
                'target_value' => 'required|numeric',
                'incentive' => 'nullable|numeric'
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            // Validation 1: If updating a sub-target, ensure sum of other sub-targets + new value <= parent value
            if ($target->parent_id) {
                $parentTarget = Target::find($target->parent_id);
                if ($parentTarget) {
                    $currentSubTargetsSum = Target::where('parent_id', $target->parent_id)->where('id', '!=', $target->id)->sum('target_value');
                    if ($currentSubTargetsSum + $request->target_value > $parentTarget->target_value) {
                        $available = max(0, $parentTarget->target_value - $currentSubTargetsSum);
                        return redirect()->back()->with('error', __('The total assigned sub-targets value exceeds the parent target value. Available limit to assign: ') . $available);
                    }
                }
            }

            // Validation 2: If updating a parent target, ensure its new value is >= sum of its existing sub-targets
            $subTargetsSum = Target::where('parent_id', $target->id)->sum('target_value');
            if ($subTargetsSum > 0 && $request->target_value < $subTargetsSum) {
                return redirect()->back()->with('error', __('The target value cannot be less than the sum of its existing assigned sub-targets: ') . $subTargetsSum);
            }
            // Validation 3: Check for duplicate target of same type in the same month (excluding current target)
            $startDate = $request->start_date ?? $target->start_date;
            $targetType = $request->target_type ?? $target->target_type;
            $pipelineId = $request->pipeline_id ?? $target->pipeline_id;
            $stageId = $request->stage_id ?? $target->stage_id;
            $targetName = $request->target_name ?? $target->target_name;
            
            $assignmentType = $request->assignment_type ?? ($target->assigned_to > 0 ? 'individual' : ($target->department_id > 0 ? 'department' : 'team'));
            $entityId = $target->assigned_to > 0 ? $target->assigned_to : ($target->department_id > 0 ? $target->department_id : $target->team_id);
            if ($request->has('assignment_type')) {
                if ($request->assignment_type == 'individual') {
                    $entityId = $request->assigned_to;
                } elseif ($request->assignment_type == 'department') {
                    $entityId = $request->department_id;
                } elseif ($request->assignment_type == 'team') {
                    $entityId = $request->team_id;
                }
            }

            $uniqueQuery = Target::where('workspace', getActiveWorkSpace())
                ->where('id', '!=', $target->id)
                ->whereYear('start_date', date('Y', strtotime($startDate)))
                ->whereMonth('start_date', date('m', strtotime($startDate)));

            if ($assignmentType == 'individual') {
                $uniqueQuery->where('assigned_to', $entityId);
            } elseif ($assignmentType == 'department') {
                $uniqueQuery->where('department_id', $entityId);
            } elseif ($assignmentType == 'team') {
                $uniqueQuery->where('team_id', $entityId);
            }

            if ($targetType == 'lead_stage') {
                $uniqueQuery->where('target_type', 'lead_stage')
                    ->where('pipeline_id', $pipelineId)
                    ->where('stage_id', $stageId);
            } else {
                $uniqueQuery->where('target_type', 'manual')
                    ->where('target_name', $targetName);
            }

            if ($uniqueQuery->exists()) {
                $entityName = '';
                if ($assignmentType == 'individual') {
                    $user = User::find($entityId);
                    $entityName = $user ? $user->name : __('Member');
                } elseif ($assignmentType == 'department' && module_is_active('Hrm')) {
                    $dept = \Workdo\Hrm\Entities\Department::find($entityId);
                    $entityName = $dept ? $dept->name : __('Department');
                } elseif ($assignmentType == 'team' && module_is_active('Hrm')) {
                    $team = \Workdo\Hrm\Entities\Department::find($entityId);
                    $entityName = $team ? $team->name : __('Team');
                }
                $msg = __('A target of this type is already assigned to :name for this month.', ['name' => $entityName]);
                return redirect()->back()->with('error', $msg);
            }

            $canChangeResponsible = ($target->assigned_by == $usr->id || $usr->type == 'company' || $usr->type == 'super admin');

            if ($canChangeResponsible) {
                if (($usr->type == 'company' || $usr->type == 'super admin') && $request->assignment_type == 'individual' && !$target->parent_id) {
                    return redirect()->back()->with('error', __('Admins can only assign targets to Departments or Teams.'));
                }

                $target->target_name = $request->target_name;
                $target->assigned_to = $request->assignment_type == 'individual' ? $request->assigned_to : 0;
                $target->department_id = $request->assignment_type == 'department' ? $request->department_id : 0;
                $target->team_id = $request->assignment_type == 'team' ? $request->team_id : 0;

                $entityId = 0;
                if ($request->assignment_type == 'individual') {
                    $entityId = $request->assigned_to;
                } elseif ($request->assignment_type == 'department') {
                    $entityId = $request->department_id;
                } elseif ($request->assignment_type == 'team') {
                    $entityId = $request->team_id;
                }
                $target->responsible_user_id = $this->resolveResponsibleUser($request->assignment_type, $entityId);
                $target->can_edit = $request->has('can_edit') ? 1 : 0;
                $target->start_date = $request->start_date;
                $target->end_date = $request->end_date;
                $target->target_value = $request->target_value;
                $target->incentive = (float)($request->incentive ?? 0.00);
                
                $target->target_type = $request->target_type ?? 'manual';
                if ($target->target_type == 'lead_stage') {
                    $target->pipeline_id = $request->pipeline_id;
                    $target->stage_id = $request->stage_id;
                    $target->custom_date_field = $request->custom_date_field ?? 'created_at';
                } else {
                    $target->pipeline_id = null;
                    $target->stage_id = null;
                    $target->custom_date_field = 'created_at';
                    $target->achieved_value = $request->achieved_value;
                }
            } else {
                if ($target->target_type == 'manual') {
                    $target->achieved_value = $request->achieved_value;
                }
            }
            
            $target->status = $request->status;
            $target->save();

            // Recalculate if automated
            if ($target->target_type == 'lead_stage') {
                $target->recalculateAchievedValue();
            }

            return redirect()->route('targets.index')->with('success', __('Target successfully updated.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function progressView($id)
    {
        $target = Target::find($id);
        if ($target && ($target->assigned_to == Auth::user()->id || $target->responsible_user_id == Auth::user()->id || Auth::user()->type == 'company')) {
            return view('targets.progress', compact('target'));
        }
        return response()->json(['error' => __('Permission Denied.')], 401);
    }

    public function updateProgress(Request $request, $id)
    {
        $target = Target::find($id);
        if ($target && ($target->assigned_to == Auth::user()->id || $target->responsible_user_id == Auth::user()->id || Auth::user()->type == 'company')) {
            if ($target->target_type == 'manual') {
                $target->achieved_value = $request->achieved_value;
                if ($target->achieved_value >= $target->target_value) {
                    $target->status = 'Completed';
                } else {
                    $target->status = 'Pending';
                }
                $target->save();
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Progress successfully updated.'),
                    'status' => $target->status,
                    'achieved_value' => $target->achieved_value,
                    'progress' => round($target->aggregateProgress, 1)
                ]);
            }
            return redirect()->back()->with('success', __('Progress successfully updated.'));
        }
        if ($request->ajax()) {
            return response()->json(['success' => false, 'message' => __('Permission Denied.')], 403);
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }
    public function updateStatus(Request $request, $id)
    {
        $target = Target::find($id);
        $usr = Auth::user();
        if ($target && ($usr->type == 'company' || $usr->type == 'super admin' || $target->assigned_by == $usr->id)) {
            $target->status = $request->status;
            if ($request->status == 'Completed' && $target->achieved_value < $target->target_value) {
                $target->achieved_value = $target->target_value;
            }
            $target->save();
            return response()->json([
                'success' => true,
                'message' => __('Status successfully updated.'),
                'status' => $target->status,
                'achieved_value' => $target->achieved_value,
                'progress' => round($target->aggregateProgress, 1)
            ]);
        }
        return response()->json(['success' => false, 'message' => __('Permission Denied.')], 403);
    }

    public function getPipelineStages(Request $request)
    {
        $pipeline_id = $request->pipeline_id;
        if (module_is_active('Lead') && $pipeline_id) {
            $stages = \Workdo\Lead\Entities\LeadStage::where('pipeline_id', $pipeline_id)->where('workspace_id', getActiveWorkSpace())->pluck('name', 'id')->toArray();
            return response()->json($stages);
        }
        return response()->json([]);
    }

    public function destroy(Target $target)
    {
        $usr = Auth::user();
        if ($usr->type == 'company' || $usr->type == 'super admin' || $target->assigned_by == $usr->id) {
            $target->delete();
            return redirect()->route('targets.index')->with('success', __('Target successfully deleted.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function bulkDestroy(Request $request)
    {
        $usr = Auth::user();
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => __('No targets selected.')], 400);
        }

        $targets = Target::whereIn('id', $ids)->get();
        $deletedCount = 0;
        foreach ($targets as $target) {
            if ($usr->type == 'company' || $usr->type == 'super admin' || $target->assigned_by == $usr->id) {
                $target->delete();
                $deletedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => __(':count Targets successfully deleted.', ['count' => $deletedCount])
        ]);
    }

    public function getUnitUsers(Request $request)
    {
        $id = $request->id;
        if (module_is_active('Hrm') && $id) {
            $empUserIds = \Workdo\Hrm\Entities\Employee::where('department_id', $id)->pluck('user_id')->toArray();
            
            // Restrict by hierarchy
            $usr = Auth::user();
            if ($usr->type != 'company' && $usr->type != 'super admin' && $usr->visibility_level != 'all') {
                $subordinateIds = $this->getSubordinateUserIds($usr->id);
                $allowedUserIds = array_merge([$usr->id], $subordinateIds);
                $empUserIds = array_intersect($empUserIds, $allowedUserIds);
            }
            
            $users = User::whereIn('id', $empUserIds)->pluck('name', 'id')->toArray();
            return response()->json($users);
        }
        return response()->json([]);
    }

    public function templateCreate(Request $request)
    {
        $usr = Auth::user();
        if ($usr->type == 'company' || $usr->type == 'super admin' || count($this->getSubordinateUserIds($usr->id)) > 0) {
            $pipelines = [];
            $customDateFields = [];
            if (module_is_active('Lead')) {
                $pipelines = \Workdo\Lead\Entities\Pipeline::where('workspace_id', getActiveWorkSpace())->pluck('name', 'id')->toArray();
                $customDateFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', getActiveWorkSpace())
                    ->where('type', 'date')
                    ->pluck('name', 'id')
                    ->toArray();
            }
            return view('targets.templates.create', compact('pipelines', 'customDateFields'));
        }
        return response()->json(['error' => __('Permission Denied.')], 401);
    }

    public function templateStore(Request $request)
    {
        $usr = Auth::user();
        if ($usr->type == 'company' || $usr->type == 'super admin' || count($this->getSubordinateUserIds($usr->id)) > 0) {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:120',
                'target_type' => 'required|string'
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $template = new TargetTemplate();
            $template->name = $request->name;
            $template->target_type = $request->target_type;
            if ($request->target_type == 'lead_stage') {
                $template->pipeline_id = $request->pipeline_id;
                $template->stage_id = $request->stage_id;
                $template->custom_date_field = $request->custom_date_field ?? 'created_at';
            } else {
                $template->pipeline_id = null;
                $template->stage_id = null;
                $template->custom_date_field = 'created_at';
            }
            $template->workspace = getActiveWorkSpace();
            $template->created_by = creatorId();
            $template->save();

            return redirect()->route('targets.index')->with('success', __('Target Template successfully created.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function templateEdit($id)
    {
        $usr = Auth::user();
        if ($usr->type == 'company' || $usr->type == 'super admin' || count($this->getSubordinateUserIds($usr->id)) > 0) {
            $template = TargetTemplate::find($id);
            if (!$template) {
                return response()->json(['error' => __('Template not found.')], 404);
            }

            $pipelines = [];
            $stages = [];
            $customDateFields = [];
            if (module_is_active('Lead')) {
                $pipelines = \Workdo\Lead\Entities\Pipeline::where('workspace_id', getActiveWorkSpace())->pluck('name', 'id')->toArray();
                if ($template->pipeline_id) {
                    $stages = \Workdo\Lead\Entities\LeadStage::where('pipeline_id', $template->pipeline_id)->where('workspace_id', getActiveWorkSpace())->pluck('name', 'id')->toArray();
                }
                $customDateFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', getActiveWorkSpace())
                    ->where('type', 'date')
                    ->pluck('name', 'id')
                    ->toArray();
            }

            return view('targets.templates.edit', compact('template', 'pipelines', 'stages', 'customDateFields'));
        }
        return response()->json(['error' => __('Permission Denied.')], 401);
    }

    public function templateUpdate(Request $request, $id)
    {
        $usr = Auth::user();
        if ($usr->type == 'company' || $usr->type == 'super admin' || count($this->getSubordinateUserIds($usr->id)) > 0) {
            $template = TargetTemplate::find($id);
            if (!$template) {
                return redirect()->back()->with('error', __('Template not found.'));
            }

            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:120',
                'target_type' => 'required|string'
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $template->name = $request->name;
            $template->target_type = $request->target_type;
            if ($request->target_type == 'lead_stage') {
                $template->pipeline_id = $request->pipeline_id;
                $template->stage_id = $request->stage_id;
                $template->custom_date_field = $request->custom_date_field ?? 'created_at';
            } else {
                $template->pipeline_id = null;
                $template->stage_id = null;
                $template->custom_date_field = 'created_at';
            }
            $template->save();

            return redirect()->route('targets.index')->with('success', __('Target Template successfully updated.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function templateDestroy($id)
    {
        $usr = Auth::user();
        if ($usr->type == 'company' || $usr->type == 'super admin' || count($this->getSubordinateUserIds($usr->id)) > 0) {
            $template = TargetTemplate::find($id);
            if ($template) {
                $template->delete();
                return redirect()->route('targets.index')->with('success', __('Target Template successfully deleted.'));
            }
            return redirect()->back()->with('error', __('Template not found.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function templateAssignView($id)
    {
        $usr = Auth::user();
        if ($usr->type == 'company' || $usr->type == 'super admin' || count($this->getSubordinateUserIds($usr->id)) > 0) {
            $template = TargetTemplate::find($id);
            if (!$template) {
                return redirect()->back()->with('error', __('Template not found.'));
            }

            $subordinateIds = $this->getSubordinateUserIds($usr->id);
            if ($usr->type == 'company' || $usr->type == 'super admin') {
                $subordinateIds = User::where('workspace_id', getActiveWorkSpace())->where('type', '!=', 'client')->where('id', '!=', $usr->id)->pluck('id')->toArray();
            }
            $users = User::whereIn('id', $subordinateIds)->pluck('name', 'id')->toArray();

            $departments = [];
            $teams = [];
            if (module_is_active('Hrm')) {
                $departments = \Workdo\Hrm\Entities\Department::where('type', 'department')->where('workspace', getActiveWorkSpace())->pluck('name', 'id')->toArray();
                $teams = \Workdo\Hrm\Entities\Department::where('type', 'team')->where('workspace', getActiveWorkSpace())->pluck('name', 'id')->toArray();
            }

            $allManagers = User::where('workspace_id', getActiveWorkSpace())->where('type', '!=', 'client')->get()->filter(function($u){
                return count($this->getSubordinateUserIds($u->id)) > 0 || $u->type == 'company' || $u->type == 'super admin';
            })->pluck('name', 'id')->toArray();

            $workspaces = [];
            if ($usr->type == 'super admin') {
                $workspaces = \App\Models\WorkSpace::pluck('name', 'id')->toArray();
            } elseif ($usr->type == 'company') {
                $workspaces = \App\Models\WorkSpace::where('created_by', creatorId())->pluck('name', 'id')->toArray();
            }

            return view('targets.templates.assign', compact('template', 'users', 'departments', 'teams', 'allManagers', 'workspaces'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function templateAssignStore(Request $request, $id)
    {
        $usr = Auth::user();
        if ($usr->type == 'company' || $usr->type == 'super admin' || count($this->getSubordinateUserIds($usr->id)) > 0) {
            $template = TargetTemplate::find($id);
            if (!$template) {
                return redirect()->back()->with('error', __('Template not found.'));
            }

            if (($usr->type == 'company' || $usr->type == 'super admin') && $request->assignment_type == 'individual' && !$request->parent_id) {
                return redirect()->back()->with('error', __('Admins can only assign targets to Departments or Teams.'));
            }

            $entities = [];
            if ($request->assignment_type == 'individual') {
                $entities = (array) $request->assigned_to;
            } elseif ($request->assignment_type == 'department') {
                $entities = (array) $request->department_id;
            } elseif ($request->assignment_type == 'team') {
                $entities = (array) $request->team_id;
            } elseif ($request->assignment_type == 'company') {
                $entities = (array) $request->workspace_id;
            }

            if (empty($entities)) {
                return redirect()->back()->with('error', __('Please select at least one individual, department, team, or company.'));
            }

            $parent_id = $request->get('parent_id', null);
            $parentTarget = null;
            $currentSubTargetsSum = 0;
            if ($parent_id) {
                $parentTarget = Target::find($parent_id);
                if (!$parentTarget) {
                    return redirect()->back()->with('error', __('Parent target not found.'));
                }
                $isAuthorizedToAssignSub = ($usr->type == 'company' || $usr->type == 'super admin' || $parentTarget->assigned_by == $usr->id || $parentTarget->responsible_user_id == $usr->id);
                if (!$isAuthorizedToAssignSub) {
                    return redirect()->back()->with('error', __('Permission Denied to assign sub-targets under this target.'));
                }
                $currentSubTargetsSum = Target::where('parent_id', $parent_id)->sum('target_value');
            }
            $startDate = now()->startOfMonth()->toDateString();
            if ($parentTarget) {
                $startDate = $parentTarget->start_date;
            }

            // Check for duplicate target of same type in the same month before template assignment
            foreach ($entities as $entity_id) {
                if (empty($entity_id)) continue;

                $uniqueQuery = Target::where('workspace', getActiveWorkSpace())
                    ->whereYear('start_date', date('Y', strtotime($startDate)))
                    ->whereMonth('start_date', date('m', strtotime($startDate)));

                if ($request->assignment_type == 'individual') {
                    $uniqueQuery->where('assigned_to', $entity_id);
                } elseif ($request->assignment_type == 'department') {
                    $uniqueQuery->where('department_id', $entity_id);
                } elseif ($request->assignment_type == 'team') {
                    $uniqueQuery->where('team_id', $entity_id);
                } elseif ($request->assignment_type == 'company') {
                    $uniqueQuery->where('workspace', $entity_id)
                        ->where('assigned_to', 0)
                        ->where('department_id', 0)
                        ->where('team_id', 0);
                }

                if ($template->target_type == 'lead_stage') {
                    $uniqueQuery->where('target_type', 'lead_stage')
                        ->where('pipeline_id', $template->pipeline_id)
                        ->where('stage_id', $template->stage_id);
                } else {
                    $uniqueQuery->where('target_type', 'manual')
                        ->where('target_name', $template->name);
                }

                if ($uniqueQuery->exists()) {
                    $entityName = '';
                    if ($request->assignment_type == 'individual') {
                        $user = User::find($entity_id);
                        $entityName = $user ? $user->name : __('Member');
                    } elseif ($request->assignment_type == 'department' && module_is_active('Hrm')) {
                        $dept = \Workdo\Hrm\Entities\Department::find($entity_id);
                        $entityName = $dept ? $dept->name : __('Department');
                    } elseif ($request->assignment_type == 'team' && module_is_active('Hrm')) {
                        $team = \Workdo\Hrm\Entities\Department::find($entity_id);
                        $entityName = $team ? $team->name : __('Team');
                    } elseif ($request->assignment_type == 'company') {
                        $ws = \App\Models\WorkSpace::find($entity_id);
                        $entityName = $ws ? $ws->name : __('Company');
                    }
                    $msg = __('A target of this type is already assigned to :name for this month.', ['name' => $entityName]);
                    return redirect()->back()->with('error', $msg);
                }
            }

            foreach ($entities as $entity_id) {
                if (empty($entity_id)) continue;

                $tValue = isset($request->target_values[$entity_id]) ? (int) $request->target_values[$entity_id] : 0;
                if ($tValue <= 0) {
                    $tValue = (int) $request->target_value;
                }

                if ($tValue <= 0) {
                    return redirect()->back()->with('error', __('Target quantity is required and must be greater than 0 for all assignees.'));
                }

                if ($parentTarget) {
                    if ($currentSubTargetsSum + $tValue > $parentTarget->target_value) {
                        $available = max(0, $parentTarget->target_value - $currentSubTargetsSum);
                        return redirect()->back()->with('error', __('The total assigned sub-targets value exceeds the parent target value. Available limit to assign: ') . $available);
                    }
                    $currentSubTargetsSum += $tValue;
                }

                $target = new Target();
                $target->target_name = $template->name;
                $target->target_type = $template->target_type;
                $target->pipeline_id = $template->pipeline_id;
                $target->stage_id = $template->stage_id;
                $target->custom_date_field = $template->custom_date_field ?? 'created_at';

                $target->parent_id = $request->parent_id;
                
                if ($request->assignment_type == 'company') {
                    $target->workspace = $entity_id;
                    $companyOwner = User::where('workspace_id', $entity_id)->where('type', 'company')->first();
                    $creator_id = $companyOwner ? $companyOwner->id : creatorId();
                    $target->created_by = $creator_id;
                    $target->assigned_by = $creator_id;
                    $target->assigned_to = 0;
                    $target->department_id = 0;
                    $target->team_id = 0;
                    $target->responsible_user_id = $creator_id;
                } else {
                    $target->workspace = getActiveWorkSpace();
                    $target->created_by = creatorId();
                    $target->assigned_by = $usr->id;
                    $target->assigned_to = $request->assignment_type == 'individual' ? $entity_id : 0;
                    $target->department_id = $request->assignment_type == 'department' ? $entity_id : 0;
                    $target->team_id = $request->assignment_type == 'team' ? $entity_id : 0;
                    $target->responsible_user_id = $this->resolveResponsibleUser($request->assignment_type, $entity_id);
                }

                $target->can_edit = $request->has('can_edit') ? 1 : 0;
                $target->start_date = now()->startOfMonth()->toDateString();
                $target->end_date = now()->endOfMonth()->toDateString();
                $target->target_value = $tValue;
                $target->incentive = isset($request->incentives[$entity_id]) && $request->incentives[$entity_id] !== '' ? (float) $request->incentives[$entity_id] : (float) ($request->incentive ?? 0.00);
                $target->achieved_value = 0;
                $target->status = 'Pending';
                
                $target->save();

                if ($target->target_type == 'lead_stage') {
                    $target->recalculateAchievedValue();
                }
            }

            return redirect()->route('targets.index')->with('success', __('Targets successfully assigned in bulk.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function getTeamMembersPerformance(Request $request, $id)
    {
        $usr = Auth::user();
        if (!module_is_active('Hrm')) {
            return response()->json(['error' => __('Hrm module is not active.')], 400);
        }

        $team = \Workdo\Hrm\Entities\Department::find($id);
        if (!$team) {
            return response()->json(['error' => __('Team not found.')], 404);
        }

        $allDeptIds = array_merge([$id], $team->allChildIds());
        $employees = \Workdo\Hrm\Entities\Employee::whereIn('department_id', $allDeptIds)->whereNotNull('user_id')->get();

        $memberData = [];
        foreach ($employees as $emp) {
            $user = User::find($emp->user_id);
            if (!$user) continue;

            $targets = Target::where('workspace', getActiveWorkSpace())
                ->where('assigned_to', $user->id)
                ->get();

            if ($targets->count() == 0) {
                continue;
            }

            $totalTarget = $targets->sum('target_value');
            $totalAchieved = $targets->sum('achieved_value');
            $pct = $totalTarget > 0 ? round(($totalAchieved / $totalTarget) * 100, 1) : 0;

            $memberData[] = [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ? asset('storage/uploads/avatar/'.$user->avatar) : asset('storage/uploads/avatar/avatar.png'),
                'targets_count' => $targets->count(),
                'total_target' => $totalTarget,
                'total_achieved' => $totalAchieved,
                'progress' => $pct,
                'completed' => $targets->where('status', 'Completed')->count(),
                'targets_list' => $targets->map(function($t) {
                    $pipelineName = null;
                    $stageName = null;
                    $customDateFieldName = null;
                    if ($t->target_type == 'lead_stage') {
                        $pipelineName = $t->pipeline ? $t->pipeline->name : __('Unknown Pipeline');
                        $stageName = $t->stage ? $t->stage->name : __('Unknown Stage');
                        if ($t->custom_date_field && $t->custom_date_field !== 'created_at') {
                            $dateField = \DB::table('lead_custom_fields')->where('workspace_id', getActiveWorkSpace())->where('id', $t->custom_date_field)->first();
                            $customDateFieldName = $dateField ? $dateField->name : null;
                        }
                    }
                    return [
                        'name' => $t->target_name,
                        'target' => $t->target_value,
                        'achieved' => $t->achieved_value,
                        'progress' => round($t->aggregateProgress, 1),
                        'status' => $t->status,
                        'type' => $t->target_type == 'lead_stage' ? __('Automated') : __('Manual'),
                        'pipeline_name' => $pipelineName,
                        'stage_name' => $stageName,
                        'custom_date_field_name' => $customDateFieldName
                    ];
                })->toArray()
            ];
        }

        usort($memberData, function($a, $b) {
            return $b['progress'] <=> $a['progress'];
        });

        return view('targets.team_members_performance', compact('team', 'memberData'));
    }

    public function getDepartmentTeamsPerformance(Request $request, $id)
    {
        $usr = Auth::user();
        if (!module_is_active('Hrm')) {
            return response()->json(['error' => __('Hrm module is not active.')], 400);
        }

        $department = \Workdo\Hrm\Entities\Department::find($id);
        if (!$department) {
            return response()->json(['error' => __('Department not found.')], 404);
        }

        $allChildIds = $department->allChildIds();
        $teams = \Workdo\Hrm\Entities\Department::whereIn('id', $allChildIds)
            ->where('id', '!=', $id)
            ->where('type', 'team')
            ->where('workspace', getActiveWorkSpace())
            ->get();

        $teamData = [];
        foreach ($teams as $team) {
            $teamTargets = Target::where('workspace', getActiveWorkSpace())
                ->where(function($q) use ($team) {
                    $q->where('team_id', $team->id)
                      ->orWhereHas('assignedToUser.employee', function($eq) use ($team) {
                          $eq->where('department_id', $team->id);
                      });
                })->get();

            if ($teamTargets->count() == 0) {
                continue;
            }

            $totalTarget = $teamTargets->sum('target_value');
            $totalAchieved = $teamTargets->sum('achieved_value');
            $pct = $totalTarget > 0 ? round(($totalAchieved / $totalTarget) * 100, 1) : 0;

            $teamData[] = [
                'id' => $team->id,
                'name' => $team->name,
                'targets_count' => $teamTargets->count(),
                'total_target' => $totalTarget,
                'total_achieved' => $totalAchieved,
                'progress' => $pct,
                'completed' => $teamTargets->where('status', 'Completed')->count(),
                'targets_list' => $teamTargets->map(function($t) {
                    $pipelineName = null;
                    $stageName = null;
                    $customDateFieldName = null;
                    if ($t->target_type == 'lead_stage') {
                        $pipelineName = $t->pipeline ? $t->pipeline->name : __('Unknown Pipeline');
                        $stageName = $t->stage ? $t->stage->name : __('Unknown Stage');
                        if ($t->custom_date_field && $t->custom_date_field !== 'created_at') {
                            $dateField = \DB::table('lead_custom_fields')->where('workspace_id', getActiveWorkSpace())->where('id', $t->custom_date_field)->first();
                            $customDateFieldName = $dateField ? $dateField->name : null;
                        }
                    }
                    return [
                        'name' => $t->target_name,
                        'target' => $t->target_value,
                        'achieved' => $t->achieved_value,
                        'progress' => round($t->aggregateProgress, 1),
                        'status' => $t->status,
                        'type' => $t->target_type == 'lead_stage' ? __('Automated') : __('Manual'),
                        'pipeline_name' => $pipelineName,
                        'stage_name' => $stageName,
                        'custom_date_field_name' => $customDateFieldName
                    ];
                })->toArray()
            ];
        }

        usort($teamData, function($a, $b) {
            return $b['progress'] <=> $a['progress'];
        });

        return view('targets.department_teams_performance', compact('department', 'teamData'));
    }
}

