<?php

namespace Workdo\Lead\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Lead\DataTables\LeadTaskDataTable;
use App\Models\User;
use Workdo\Lead\Entities\Lead;
use Workdo\Lead\Entities\LeadStage;
use Workdo\Lead\Entities\LeadTask;
use Workdo\Lead\Entities\Reminder;
use Workdo\Lead\Entities\LeadActivityLog;
use App\Models\UserNotification;

class LeadTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(LeadTaskDataTable $dataTable)
    {
        if (\Auth::user()->isAbleTo('lead manage')) {
            $getActiveWorkSpace = getActiveWorkSpace();
            // Pre-fetch users for filter
            $users = User::where('created_by', creatorId())
                ->where('workspace_id', $getActiveWorkSpace)
                ->get()
                ->pluck('name', 'id');

            // Pre-fetch Leads for filter (limit to accessible)
            $leads = Lead::where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');

            return $dataTable->render('lead::tasks.index', compact('users', 'leads'));
        }
        else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->isAbleTo('lead edit')) {
            $getActiveWorkSpace = getActiveWorkSpace();
            $users = User::where('created_by', creatorId())
                ->where('workspace_id', $getActiveWorkSpace)
                ->get()
                ->pluck('name', 'id');

            // Get Stages for filter
            $stages = LeadStage::where('created_by', creatorId())
                ->where('workspace_id', $getActiveWorkSpace)
                ->orderBy('order')
                ->get()
                ->pluck('name', 'id');

            // Get Leads for single select
            $leads = Lead::where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');


            return view('lead::tasks.create', compact('users', 'stages', 'leads'));
        }
        return response()->json(['error' => __('Permission Denied.')]);
    }

    public function store(Request $request)
    {
        if (\Auth::user()->isAbleTo('lead edit')) {
            $getActiveWorkSpace = getActiveWorkSpace();

            // Validation
            $rules = [
                'target_type' => 'required|in:single,filter',
                'create_task' => 'required_without:create_reminder',
                'create_reminder' => 'required_without:create_task',
            ];

            if ($request->target_type == 'single') {
                $rules['lead_id'] = 'required';
            }

            if ($request->has('create_task')) {
                $rules['task_subject'] = 'required|string|max:255';
                $rules['task_date'] = 'required|date';
                $rules['task_priority'] = 'required';
            }

            if ($request->has('create_reminder')) {
                $rules['reminder_date'] = 'required|date';
                $rules['reminder_time'] = 'required';
                $rules['reminder_description'] = 'required|string';
            }

            $request->validate($rules);

            $targetLeads = collect();

            // 1. Identify Target Leads
            if ($request->target_type == 'single') {
                if (!$request->lead_id)
                    return redirect()->back()->with('error', __('Please select a lead.'));
                $targetLeads = Lead::where('id', $request->lead_id)->where('workspace_id', $getActiveWorkSpace)->get();
            }
            else {
                // Bulk Filter
                $query = Lead::where('workspace_id', $getActiveWorkSpace);

                if ($request->filter_stage_id) {
                    $query->whereIn('stage_id', $request->filter_stage_id);
                }
                if ($request->filter_user_id) {
                    $query->whereIn('user_id', $request->filter_user_id);
                }
                if ($request->filter_date_start) {
                    $query->whereDate('created_at', '>=', $request->filter_date_start);
                }
                if ($request->filter_date_end) {
                    $query->whereDate('created_at', '<=', $request->filter_date_end);
                }
                $targetLeads = $query->get();
            }

            if ($targetLeads->isEmpty()) {
                return redirect()->back()->with('error', __('No leads found matching the criteria.'));
            }

            $countTask = 0;
            $countReminder = 0;

            foreach ($targetLeads as $lead) {
                // Determine Assignee (Responsible Person)
                // If "Assign to Owner" is checked, use lead's user_id.
                // Else use the selected user_id from form (if 'single' or 'bulk' override).
                // But form might have "assign_to" dropdown? 
                // Plan said: "Assign to Lead Owner" checkbox (checked by default).
                // If unchecked, we need a user select? Or just default to Auth user?
                // Let's assume there is an 'assigned_user_id' field in form, which is used if checkbox unchecked.

                $assignedUserId = \Auth::user()->id; // Default fallback
                if ($request->has('assign_to_owner') && $request->assign_to_owner == 1) {
                    $assignedUserId = $lead->user_id;
                }
                elseif ($request->assigned_user_id) {
                    $assignedUserId = $request->assigned_user_id;
                }

                // Create Task
                if ($request->has('create_task') && $request->create_task == 1) {
                    $task = LeadTask::create([
                        'lead_id' => $lead->id,
                        'name' => $request->task_subject,
                        'date' => $request->task_date,
                        'time' => date('H:i'),
                        'priority' => $request->task_priority,
                        'status' => 'pending',
                        'workspace' => $getActiveWorkSpace,
                        'user_id' => $assignedUserId,
                    ]);

                    // Notification for Task Assignment
                    if ($assignedUserId != \Auth::user()->id) {
                        UserNotification::create([
                            'user_id' => $assignedUserId,
                            'type' => 'task_assignment',
                            'data' => [
                                'task_id' => $task->id,
                                'task_name' => $task->name,
                                'lead_id' => $lead->id,
                                'lead_name' => $lead->name,
                                'assigned_by_name' => \Auth::user()->name,
                            ],
                            'workspace_id' => getActiveWorkSpace(),
                        ]);
                    }

                    LeadActivityLog::create([
                        'user_id' => \Auth::user()->id,
                        'lead_id' => $lead->id,
                        'log_type' => 'Create Task',
                        'remark' => json_encode(['title' => $request->task_subject]),
                    ]);
                    $countTask++;
                }

                // Create Reminder
                if ($request->has('create_reminder') && $request->create_reminder == 1) {
                    Reminder::create([
                        'created_by' => \Auth::user()->id,
                        'user_id' => $assignedUserId,
                        'title' => 'Global Reminder',
                        'description' => $request->reminder_description,
                        'remind_at' => $request->reminder_date . ' ' . ($request->reminder_time ?? '09:00'),
                        'remindable_type' => 'Workdo\Lead\Entities\Lead',
                        'remindable_id' => $lead->id,
                        'is_sent' => 0,
                    ]);

                    LeadActivityLog::create([
                        'user_id' => \Auth::user()->id,
                        'lead_id' => $lead->id,
                        'log_type' => 'Create Reminder',
                        'remark' => json_encode(['title' => 'Global Reminder']),
                    ]);
                    $countReminder++;
                }
            }

            return redirect()->back()->with('success', __('Processed: ' . $countTask . ' Tasks and ' . $countReminder . ' Reminders created.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function destroy($id)
    {
        if (\Auth::user()->isAbleTo('lead task delete')) {
            $task = LeadTask::find($id);
            if ($task) {
                $task->delete();
                return redirect()->back()->with('success', __('Lead task successfully deleted.'));
            }
            else {
                return redirect()->back()->with('error', __('Task not found.'));
            }
        }
        else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function bulkDestroy(Request $request)
    {
        if (\Auth::user()->isAbleTo('lead task delete')) {
            $ids = $request->ids;
            if (empty($ids)) {
                return response()->json(['success' => false, 'message' => __('No tasks selected.')]);
            }

            LeadTask::whereIn('id', $ids)->where('workspace', getActiveWorkSpace())->delete();

            return response()->json(['success' => true, 'message' => __('Selected tasks deleted successfully.')]);
        }
        return response()->json(['success' => false, 'message' => __('Permission Denied.')], 403);
    }
}
