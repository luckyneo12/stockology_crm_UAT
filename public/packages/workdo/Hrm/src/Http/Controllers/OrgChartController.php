<?php

namespace Workdo\Hrm\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OrgChartController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'super admin' || \Auth::user()->isAbleTo('orgchart manage')) {
            $user = \Auth::user();
            
            // If Admin/Owners, get Top Level Nodes (where parent_id is NULL)
            if ($user->type == 'company' || $user->type == 'super admin') {
                $employees = \Workdo\Hrm\Entities\Employee::where('created_by', creatorId())
                    ->where('workspace', getActiveWorkSpace())
                    ->whereNull('parent_id')
                    ->with(['designation', 'subordinates']) // Eager load
                    ->get();    
            } else {
                // If Manager/Employee, get their own node as root
                $employees = \Workdo\Hrm\Entities\Employee::where('user_id', $user->id)
                    ->where('workspace', getActiveWorkSpace())
                    ->with(['designation', 'subordinates'])
                    ->get();
            }

            return view('hrm::org_chart.index', compact('employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function updateHierarchy(Request $request)
    {
        if (!(\Auth::user()->type == 'company' || \Auth::user()->type == 'super admin' || \Auth::user()->isAbleTo('orgchart edit'))) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $validator = \Validator::make($request->all(), [
            'employee_id' => 'required',
            'parent_id' => 'nullable', // Null means root/direct to admin
        ]);

        if ($validator->fails()) {
             return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        $employee = \Workdo\Hrm\Entities\Employee::find($request->employee_id);
        
        if (!$employee) {
            return response()->json(['success' => false, 'message' => __('Employee not found.')], 404);
        }
        
        // Prevent setting self as parent
        if ($request->parent_id && $request->employee_id == $request->parent_id) {
             return response()->json(['success' => false, 'message' => __('Cannot set self as manager.')], 400);
        }
        
        // Circular reference check (Basic: Check if new parent is currently a child of the employee)
        if ($request->parent_id && $request->parent_id != 'root') {
            $newManager = \Workdo\Hrm\Entities\Employee::find($request->parent_id);
            // This is a simple check. For deep trees, we might need a recursive check, 
            // but for now, we ensure the new manager isn't a direct subordinate.
             if ($newManager->parent_id == $employee->id) {
                 return response()->json(['success' => false, 'message' => __('Circular reference detected! The selected manager is currently reporting to this employee.')], 400);
             }
        }

        // Handle 'root' string or valid ID
        $parentId = ($request->parent_id === 'root' || empty($request->parent_id)) ? null : $request->parent_id;
        $employee->parent_id = $parentId;
        $employee->save();

        return response()->json(['success' => true, 'message' => __('Hierarchy updated successfully.')]);
    }

    public function removeFromHierarchy(Request $request)
    {
        if (!(\Auth::user()->type == 'company' || \Auth::user()->type == 'super admin' || \Auth::user()->isAbleTo('orgchart edit'))) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $validator = \Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        $employee = \Workdo\Hrm\Entities\Employee::find($request->employee_id);
        
        if (!$employee) {
            return response()->json(['success' => false, 'message' => __('Employee not found.')], 404);
        }

        // Set parent_id to null to remove from hierarchy
        $employee->parent_id = null;
        $employee->save();

        return response()->json(['success' => true, 'message' => __('Employee removed from hierarchy successfully.')]);
    }
}
