<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Str;
use Workdo\Hrm\Entities\Branch;
use Workdo\Hrm\Entities\Department;
use Illuminate\Support\Facades\Auth;

class UserManagementHubController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('user manage')) {
            $activeTab = $request->get('tab', 'users');

            // Data for Users Tab
            $roles = Role::where('created_by', creatorId())->pluck('name', 'id')->map(function ($name) {
                return ucfirst($name);
            });
            $users = User::where('created_by', creatorId());

            // Advanced Filter Logic
            if ($request->filled('role')) {
                $users->whereHas('roles', function ($q) use ($request) {
                    $q->where('id', $request->role);
                });
            }

            if ($request->filled('status')) {
                if ($request->status == 'active') {
                    $users->where('is_disable', 0);
                }
                elseif ($request->status == 'inactive') {
                    $users->where('is_disable', 1);
                }
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $users->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if (Auth::user()->isAbleTo('workspace manage')) {
                $users->where('workspace_id', getActiveWorkSpace());
            }

            $users = $users->paginate(50)->appends($request->all());

            // Data for Branches Tab
            $branches = [];
            if (module_is_active('Hrm')) {
                $branches = Branch::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();
            }

            // Data for Departments Tab
            $departments = [];
            $teams = [];
            if (module_is_active('Hrm')) {
                $allDepts = Department::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->with('branch', 'parent')->get();
                $departments = $allDepts->where('type', 'department');
                $teams = $allDepts->where('type', 'team');

                // Auto-heal missing Levers Accounts for existing teams
                $healed = false;
                foreach ($teams as $team) {
                    if (!$team->levers_user_id) {
                        $leversUser = $this->createTeamLeversUser($team);
                        if ($leversUser) {
                            $team->levers_user_id = $leversUser->id;
                            $team->save();
                            $healed = true;
                        }
                    }
                }

                if ($healed) {
                    $allDepts = Department::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->with('branch', 'parent')->get();
                    $departments = $allDepts->where('type', 'department');
                    $teams = $allDepts->where('type', 'team');
                }
            }

            // Data for Org Chart
            $rootDepartments = [];
            if (module_is_active('Hrm')) {
                $rootDepartments = Department::where('created_by', creatorId())
                    ->where('workspace', getActiveWorkSpace())
                    ->whereNull('parent_id')
                    ->with(['children', 'manager.user', 'employees.user'])
                    ->get();
            }

            $companyUser = User::find(creatorId());

            return view('users.management.index', compact('users', 'roles', 'branches', 'departments', 'teams', 'rootDepartments', 'activeTab', 'companyUser'));
        }
        else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function convertToTeam(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('department edit')) {
            $department = Department::find($id);
            if ($department && $department->created_by == creatorId()) {
                $department->type = 'team';
                $department->save();

                // Auto-create the Team Levers Account if not already created
                if (!$department->levers_user_id) {
                    $leversUser = $this->createTeamLeversUser($department);
                    if ($leversUser) {
                        $department->levers_user_id = $leversUser->id;
                        $department->save();
                    }
                }

                return response()->json(['success' => true, 'message' => __('Department converted to Team successfully.')]);
            }
            return response()->json(['success' => false, 'message' => __('Department not found or access denied.')], 404);
        }
        return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
    }

    /**
     * Creates a dedicated "Team Levers" user account for a team.
     * This account receives all leads when any team member is inactivated or deleted.
     */
    public function createTeamLeversUser(Department $department)
    {
        $teamName = $department->name;
        $leversName = $teamName . ' Leaver';  // e.g. "JAGUAR Leaver"
        $leversEmail = strtolower(str_replace(' ', '.', $teamName)) . '.leaver@teamaccount.local';

        // Check if levers user already exists for this team name
        $existing = \App\Models\User::where('email', $leversEmail)
            ->where('created_by', $department->created_by)
            ->first();

        if ($existing) {
            return $existing;
        }

        $creatorUser = \App\Models\User::find($department->created_by);
        if (!$creatorUser) {
            return null;
        }

        // Prefer "Sales Executive" role, fallback to first non-company role
        $role = \App\Models\Role::where('created_by', $department->created_by)
            ->where('name', 'Sales Executive')
            ->first();

        if (!$role) {
            $role = \App\Models\Role::where('created_by', $department->created_by)
                ->where('name', '!=', 'company')
                ->first();
        }

        if (!$role) {
            return null;
        }

        $user = \App\Models\User::create([
            'name'             => $leversName,
            'email'            => $leversEmail,
            'password'         => \Hash::make(\Str::random(32)), // Random unguessable password
            'type'             => $role->name,
            'created_by'       => $department->created_by,
            'workspace_id'     => $department->workspace,
            'active_workspace'  => $department->workspace,
            'is_disable'       => 0, // Active so leads can be assigned
            'is_enable_login'  => 0, // Cannot login
            'lang'             => 'en',
        ]);

        $user->addRole($role);

        // Create an Employee record and assign to this team
        $employee = new \Workdo\Hrm\Entities\Employee();
        $employee->user_id      = $user->id;
        $employee->name         = $leversName;
        $employee->email        = $leversEmail;
        $employee->workspace    = $department->workspace;
        $employee->created_by   = $department->created_by;
        $employee->department_id = $department->id;
        $employee->save();

        return $user;
    }

    public function updateVisibility(Request $request)
    {
        if (Auth::user()->isAbleTo('user edit')) {
            $user = User::find($request->user_id);
            if ($user && $user->created_by == creatorId()) {
                $user->visibility_level = $request->visibility_level;
                $user->save();
                return response()->json(['success' => true, 'message' => __('Visibility updated successfully.')]);
            }
            return response()->json(['success' => false, 'message' => __('User not found or access denied.')], 404);
        }
        return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
    }

    public function departmentUsers($id)
    {
        if (Auth::user()->isAbleTo('user manage') || Auth::user()->isAbleTo('department manage')) {
            $department = Department::find($id);

            if (!$department) {
                return response()->json(['error' => __('Department not found.')], 404);
            }

            // Get users who ARE in this department (via Employee)
            $employees = \Workdo\Hrm\Entities\Employee::where('department_id', $id)
                ->where('created_by', creatorId())
                ->where('workspace', getActiveWorkSpace())
                ->with('user')
                ->get();

            // Get all users who are NOT in this department's employee list
            $existingEmpUserIds = \Workdo\Hrm\Entities\Employee::where('department_id', $id)->pluck('user_id')->toArray();

            $availableUsers = User::where('created_by', creatorId())
                ->where('workspace_id', getActiveWorkSpace())
                ->whereNotIn('id', $existingEmpUserIds)
                ->get();

            return view('users.management.dept_users', compact('department', 'employees', 'availableUsers'));
        }
        return response()->json(['error' => __('Permission denied.')], 401);
    }

    public function addDepartmentUser(Request $request)
    {
        if (Auth::user()->isAbleTo('user edit')) {
            $user = User::find($request->user_id);
            if ($user) {
                $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $user->id)->first();
                if (!$employee) {
                    // Create basic employee record if it doesn't exist
                    $employee = new \Workdo\Hrm\Entities\Employee();
                    $employee->user_id = $user->id;
                    $employee->name = $user->name;
                    $employee->email = $user->email;
                    $employee->workspace = getActiveWorkSpace();
                    $employee->created_by = creatorId();
                }
                $employee->department_id = $request->department_id;
                $employee->save();
                return response()->json(['success' => true, 'message' => __('User added to department successfully.')]);
            }
            return response()->json(['success' => false, 'message' => __('User not found.')], 404);
        }
        return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
    }

    public function removeDepartmentUser(Request $request)
    {
        if (Auth::user()->isAbleTo('department edit')) {
            $employee = \Workdo\Hrm\Entities\Employee::find($request->employee_id);
            if ($employee && $employee->department_id == $request->department_id) {
                $employee->department_id = null;
                $employee->save();
                return response()->json(['success' => true, 'message' => __('User removed from department successfully.')]);
            }
            return response()->json(['success' => false, 'message' => __('Employee not found in this department.')], 404);
        }
        return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
    }
}
