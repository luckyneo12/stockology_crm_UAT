<?php

namespace Workdo\Hrm\Http\Controllers;

use App\Models\Setting;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\Entities\Branch;
use Workdo\Hrm\Entities\Department;
use Workdo\Hrm\Entities\Designation;
use Workdo\Hrm\Entities\Employee;
use Workdo\Hrm\Events\CreateDepartment;
use Workdo\Hrm\Events\DestroyDepartment;
use Workdo\Hrm\Events\UpdateDepartment;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('department manage')) {
            $departments = Department::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->with('branch')->get();
            return view('hrm::department.index', compact('departments'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        if (Auth::user()->isAbleTo('department create')) {
            $branch = Branch::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $parents = Department::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $parents->prepend(__('Select Parent Department'), '');
            $parent_id = $request->parent_id ?? null;
            $employees = \Workdo\Hrm\Entities\Employee::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->whereHas('user', function ($q) {
                $q->where('is_enable_login', 1);
            })->get()->pluck('name', 'id');
            $employees->prepend(__('Select Department Head'), '');

            return view('hrm::department.create', compact('branch', 'parents', 'parent_id', 'employees'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('department create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'branch_id' => 'required',
                    'name' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $department             = new Department();
            $department->branch_id  = $request->branch_id;
            $department->parent_id  = $request->parent_id ?: null;
            $department->manager_id = $request->manager_id ?: null;
            $department->name       = $request->name;
            $department->workspace  = getActiveWorkSpace();
            $department->created_by = creatorId();
            $department->save();

            event(new CreateDepartment($request, $department));

            return redirect()->back()->with('success', __('The department has been created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Department $department)
    {
        if (Auth::user()->isAbleTo('department manage')) {
            if ($department->created_by == creatorId() && $department->workspace == getActiveWorkSpace()) {
                $employees = Employee::where('department_id', $department->id)
                    ->where('created_by', creatorId())
                    ->where('workspace', getActiveWorkSpace())
                    ->with('user')
                    ->get();
                
                return view('hrm::department.show', compact('department', 'employees'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Department $department)
    {
        if (Auth::user()->isAbleTo('department edit')) {
            if ($department->created_by == creatorId() &&  $department->workspace  == getActiveWorkSpace()) {
                $branch = Branch::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
                $parents = Department::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->where('id', '!=', $department->id)->get()->pluck('name', 'id');
                $parents->prepend(__('Select Parent Department'), '');
                $employees = \Workdo\Hrm\Entities\Employee::where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->whereHas('user', function ($q) {
                    $q->where('is_enable_login', 1);
                })->get()->pluck('name', 'id');
                $employees->prepend(__('Select Department Head'), '');

                return view('hrm::department.edit', compact('department', 'branch', 'parents', 'employees'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, Department $department)
    {
        if (Auth::user()->isAbleTo('department edit')) {
            if ($department->created_by == creatorId() &&  $department->workspace  == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'branch_id' => 'required',
                        'name' => 'required|max:20',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                // update Designation branch id
                Designation::where('department_id', $department->id)->where('created_by', '=', creatorId())->where('workspace', getActiveWorkSpace())->update(['branch_id' => $request->branch_id]);

                $department->branch_id = $request->branch_id;
                $department->parent_id = $request->parent_id ?: null;
                $department->manager_id = $request->manager_id ?: null;
                $department->name      = $request->name;
                $department->save();

                event(new UpdateDepartment($request, $department));

                return redirect()->back()->with('success', __('The department details are updated successfully.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Department $department)
    {
        if (Auth::user()->isAbleTo('department delete')) {
            if ($department->created_by == creatorId() &&  $department->workspace  == getActiveWorkSpace()) {
                $employee     = Employee::where('department_id', $department->id)->where('workspace', getActiveWorkSpace())->get();
                if (count($employee) == 0) {
                    Designation::where('department_id', $department->id)->delete();

                    event(new DestroyDepartment($department));

                    $department->delete();
                } else {
                    return redirect()->back()->with('error', __('This department has employees. Please remove the employee from this department.'));
                }
                return redirect()->back()->with('success', __('The department has been deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function DepartmentNameEdit()
    {
        if (Auth::user()->isAbleTo('department name edit')) {
            return view('hrm::department.departmentnameedit');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function saveDepartmentName(Request $request)
    {
        if (Auth::user()->isAbleTo('department name edit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'hrm_department_name' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            } else {
                $post = $request->all();
                unset($post['_token']);

                foreach ($post as $key => $value) {
                    // Define the data to be updated or inserted
                    $data = [
                        'key' => $key,
                        'workspace' => getActiveWorkSpace(),
                        'created_by' => creatorId(),
                    ];
                    // Check if the record exists, and update or insert accordingly
                    Setting::updateOrInsert($data, ['value' => $value]);
                }
                // Settings Cache forget
                comapnySettingCacheForget();
                return redirect()->route('department.index')->with('success', __('The department name are updated successfully.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function addEmployee(Department $department)
    {
        if (Auth::user()->isAbleTo('department edit')) {
            if ($department->created_by == creatorId() && $department->workspace == getActiveWorkSpace()) {
                $employees = Employee::where('created_by', '=', creatorId())
                    ->where('workspace', getActiveWorkSpace())
                    ->whereNull('department_id')
                    ->orWhere('department_id', '!=', $department->id)
                    ->get()
                    ->pluck('name', 'id');
                
                return view('hrm::department.add_employee', compact('department', 'employees'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function storeEmployee(Request $request, Department $department)
    {
        if (Auth::user()->isAbleTo('department edit')) {
            if ($department->created_by == creatorId() && $department->workspace == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'employee_id' => 'required|exists:employees,id',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $employee = Employee::find($request->employee_id);
                if ($employee && $employee->created_by == creatorId() && $employee->workspace == getActiveWorkSpace()) {
                    $employee->department_id = $department->id;
                    $employee->save();

                    return redirect()->back()->with('success', __('Employee added to department successfully.'));
                }

                return redirect()->back()->with('error', __('Employee not found.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function removeEmployee(Department $department, Employee $employee)
    {
        if (Auth::user()->isAbleTo('department edit')) {
            if ($department->created_by == creatorId() && $department->workspace == getActiveWorkSpace()) {
                if ($employee->department_id == $department->id && $employee->created_by == creatorId() && $employee->workspace == getActiveWorkSpace()) {
                    $employee->department_id = null;
                    $employee->save();

                    return redirect()->back()->with('success', __('Employee removed from department successfully.'));
                }

                return redirect()->back()->with('error', __('Employee not found in this department.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
