<?php

namespace Workdo\Lead\Http\Controllers;

use App\Models\User;
use App\Models\WorkSpace;
use App\Models\UserNotification;
use App\Models\EmailTemplate;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\Lead\Entities\ClientDeal;
use Workdo\Lead\Entities\Deal;
use Workdo\Lead\Entities\DealCall;
use Workdo\Lead\Entities\DealDiscussion;
use Workdo\Lead\Entities\DealEmail;
use Workdo\Lead\Entities\DealFile;
use Workdo\Lead\Entities\DealStage;
use Workdo\Lead\Entities\DealTask;
use Workdo\Lead\Entities\Label;
use Workdo\Lead\Entities\Lead;
use Workdo\Lead\Entities\LeadActivityLog;
use Workdo\Lead\Entities\LeadCall;
use Workdo\Lead\Entities\LeadDiscussion;
use Workdo\Lead\Entities\LeadEmail;
use Workdo\Lead\Entities\LeadFile;
use Workdo\Lead\Entities\LeadStage;
use Workdo\Lead\Entities\Pipeline;
use Workdo\Lead\Entities\Source;
use Workdo\Lead\Entities\User as EntitiesUser;
use Workdo\Lead\Entities\UserDeal;
use Workdo\Lead\Entities\UserLead;
use Workdo\Lead\Entities\LeadUtility;
use Workdo\Lead\Entities\Reminder;
use App\Models\Role;
use Illuminate\Support\Facades\Mail;
use Workdo\Lead\DataTables\LeadDataTable;
use Workdo\Lead\Entities\LeadTask;
use Workdo\Lead\Events\CreateLead;
use Workdo\Lead\Events\CreateLeadTask;
use Workdo\Lead\Events\DestroyLead;
use Workdo\Lead\Events\DestroyLeadCall;
use Workdo\Lead\Events\DestroyLeadFile;
use Workdo\Lead\Events\DestroyLeadProduct;
use Workdo\Lead\Events\DestroyLeadSource;
use Workdo\Lead\Events\DestroyLeadTask;
use Workdo\Lead\Events\DestroyLeadUser;
use Workdo\Lead\Events\LeadAddCall;
use Workdo\Lead\Events\LeadAddDiscussion;
use Workdo\Lead\Events\LeadAddEmail;
use Workdo\Lead\Events\LeadAddNote;
use Workdo\Lead\Events\LeadAddProduct;
use Workdo\Lead\Events\LeadAddUser;
use Workdo\Lead\Events\LeadConvertDeal;
use Workdo\Lead\Events\LeadMoved;
use Workdo\Lead\Events\LeadSourceUpdate;
use Workdo\Lead\Events\LeadUpdateCall;
use Workdo\Lead\Events\LeadUploadFile;
use Workdo\Lead\Events\StatusChangeLeadTask;
use Workdo\Lead\Events\UpdateLead;
use Workdo\Lead\Events\UpdateLeadTask;
use Workdo\Lead\Entities\PipelineStageAutomation;
use Workdo\Lead\Entities\StageCustomField;
use Workdo\Hrm\Entities\Department;
use Workdo\Hrm\Entities\Designation;
use Workdo\Lead\Entities\LeadSection;

class LeadController extends Controller
{
    public static $isCopying = false;
    public static $cachedWorkflowData = null;
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function __construct()
    {

        if (module_is_active('GoogleAuthentication')) {
            $this->middleware('2fa');
        }
    }

    public function dashboard()
    {
        if (Auth::user()->isAbleTo('crm dashboard manage')) {
            return $this->crmDashboard();
        } else {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }

    public function crmDashboard()
    {
        $user = Auth::user();
        $workspace = getActiveWorkSpace();

        // 1. My Tasks (Pending & Overdue) - Optimized to reduce memory usage and query time
        $tasks_query = \Workdo\Lead\Entities\LeadTask::where('user_id', $user->id)
            ->where('workspace', $workspace)
            ->whereIn('status', ['pending', 'in_progress', 'overdue'])
            ->orderBy('date', 'asc');

        // Only apply heavy accessibility filter if not a company/admin (for whom it's always true)
        if ($user->type != 'company' && $user->type != 'super admin' && $user->visibility_level != 'all') {
            $tasks_query->whereHas('lead', function ($q) use ($user, $workspace) {
                $q->where('workspace_id', $workspace);
                // The relationship and accessibility is already partially handled by LeadTask::user_id
                // Adding a basic check to ensure the lead is still in the correct workspace
            });
        }
        $tasks = $tasks_query->get();

        // 2. My Reminders (Today & Upcoming) - Optimized
        $reminders_query = \Workdo\Lead\Entities\Reminder::where('user_id', $user->id)
            ->where('workspace_id', $workspace)
            ->where('is_sent', 0)
            ->orderBy('remind_at', 'asc');

        if ($user->type != 'company' && $user->type != 'super admin' && $user->visibility_level != 'all') {
            $reminders_query->where(function ($q) use ($workspace) {
                $q->where('remindable_type', '!=', 'Workdo\Lead\Entities\Lead')
                    ->orWhereHasMorph('remindable', '*', function ($inner) use ($workspace) {
                        $inner->where('workspace_id', $workspace);
                    });
            });
        }
        $reminders = $reminders_query->get();

        // 3. Performance Metrics
        $totalTasks = \Workdo\Lead\Entities\LeadTask::where('user_id', $user->id)->where('workspace', $workspace)->count();
        $completedTasks = \Workdo\Lead\Entities\LeadTask::where('user_id', $user->id)->where('workspace', $workspace)->where('status', 'done')->count();
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        return view('lead::crm.dashboard', compact('tasks', 'reminders', 'completionRate', 'totalTasks', 'completedTasks'));
    }

    public function old_dashboard()
    {
        if (Auth::user()->isAbleTo('crm dashboard manage')) {
            $creatorId = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $transdate = date('Y-m-d', time());

            $calenderTasks = [];
            $chartData = [];
            $chartcall = [];
            $dealdata = [];
            $stagedata = [];
            $arrCount = [];
            $arrErr = [];
            $m = date("m");
            $de = date("d");
            $y = date("Y");
            $format = 'Y-m-d';
            $user = Auth::user();

            $usr = EntitiesUser::find($user->id);
            if ($user->hasRole('company')) {
                //Handle Custom Error for System Setting

                foreach ($usr->deals as $deal) {
                    foreach ($deal->tasks as $task) {
                        $task = DealTask::where('id', $task->id)->where('workspace', $getActiveWorkSpace)->first();
                        if (!empty($task)) {

                            $calenderTasks[] = [
                                'title' => $task->name,
                                'start' => $task->date,
                                'url' => route(
                                    'deals.tasks.show',
                                    [
                                        $deal->id,
                                        $task->id,
                                    ]
                                ),
                                'className' => ($task->status) ? 'event-success border-success' : 'event-warning border-warning',
                            ];
                        } else {
                            $calenderTasks[] = [];
                        }
                    }
                }

                $arrCount['client'] = User::where('type', '=', 'client')->where('created_by', '=', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->count();
                $arrCount['user'] = User::where('type', '!=', 'client')->where('created_by', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->count();
                $arrCount['deal'] = Deal::where('created_by', '=', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->count();
                $arryTemp = [];
                for ($i = 0; $i <= 7 - 1; $i++) {
                    $date = date($format, mktime(0, 0, 0, $m, ($de - $i), $y));
                    $arryTemp['date'][] = __(date('d-M', strtotime($date)));
                    $arryTemp['dealcall'][] = DealCall::whereDate('created_at', $date)->where('user_id', $creatorId)->count();
                }
                $chartcall = $arryTemp;
                $chartcall['user'] = $arrCount['user'];
                $chartcall['deal'] = $arrCount['deal'];
            } elseif ($user->hasRole('client')) {
                $temp = [];
                for ($i = 0; $i <= 7 - 1; $i++) {
                    $date = date($format, mktime(0, 0, 0, $m, ($de - $i), $y));
                    $temp['date'][] = __(date('d-M', strtotime($date)));
                    $temp['deal'][] = Deal::whereDate('created_at', $date)->where('created_by', $creatorId)->count();
                }
                $dealdata = $temp;
                $dealdata['user'] = User::where('type', '!   =', 'client')->where('created_by', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->count();
                foreach ($usr->clientDeals as $deal) {
                    foreach ($deal->tasks as $task) {
                        $calenderTasks[] = [
                            'title' => $task->name,
                            'start' => $task->date,
                            'url' => route(
                                'deals.tasks.show',
                                [
                                    $deal->id,
                                    $task->id,
                                ]
                            ),
                            'className' => ($task->status) ? 'event-success border-success' : 'event-warning border-warning',
                        ];
                    }

                    $calenderTasks[] = [
                        'title' => $deal->name,
                        'start' => $deal->created_at->format('Y-m-d'),
                        'url' => route('deals.show', [$deal->id]),
                        'className' => 'deal event-primary border-primary',
                    ];
                }

                $client_deal = $usr->clientDeals->pluck('id');
                $arrCount['deal'] = $usr->clientDeals->count();
                if (!empty($client_deal->first())) {
                    $arrCount['task'] = DealTask::whereIn('deal_id', $client_deal)->count();
                } else {
                    $arrCount['task'] = 0;
                }
            } else {
                $arrTemp = [];

                $chartData = $arrTemp;
                foreach ($usr->deals as $deal) {
                    foreach ($deal->tasks as $task) {
                        $calenderTasks[] = [
                            'title' => $task->name,
                            'start' => $task->date,
                            'url' => route(
                                'deals.tasks.show',
                                [
                                    $deal->id,
                                    $task->id,
                                ]
                            ),
                            'className' => ($task->status) ? 'event-success border-success' : 'event-warning border-warning',
                        ];
                    }

                    $calenderTasks[] = [
                        'title' => $deal->name,
                        'start' => $deal->created_at->format('Y-m-d'),
                        'url' => route('deals.show', [$deal->id]),
                        'className' => 'deal bg-primary border-primary',
                    ];
                }
                $user_deal = $usr->deals->pluck('id');

                $arrCount['deal'] = $usr->deals()->count();
                if (!empty($user_deal->first())) {
                    $arrCount['task'] = DealTask::whereIn('deal_id', $user_deal)->count();
                } else {
                    $arrCount['task'] = 0;
                }
            }
            $user->save();
            if ($user->type == 'client') {
                $deals = Deal::select('deals.*')
                    ->join('client_deals', 'client_deals.deal_id', '=', 'deals.id')
                    ->where('client_deals.client_id', '=', $user->id)
                    ->where('deals.workspace_id', '=', getActiveWorkSpace())
                    ->orderBy('deals.created_at', 'desc')
                    ->take(5)->with('stage')->get();

                $modifiedDeals = Deal::select('deals.*')
                    ->join('client_deals', 'client_deals.deal_id', '=', 'deals.id')
                    ->where('client_deals.client_id', '=', $user->id)
                    ->where('deals.workspace_id', '=', getActiveWorkSpace())
                    ->orderBy('deals.updated_at', 'desc')
                    ->take(5)->with('stage')->get();
            } else {
                $deals = Deal::select('deals.*')
                    ->join('user_deals', 'user_deals.deal_id', '=', 'deals.id')
                    ->where('user_deals.user_id', '=', $user->id)
                    ->where('deals.workspace_id', '=', getActiveWorkSpace())
                    ->orderBy('deals.created_at', 'desc')
                    ->take(5)->with('stage')->get();

                $modifiedDeals = Deal::select('deals.*')
                    ->join('user_deals', 'user_deals.deal_id', '=', 'deals.id')
                    ->where('user_deals.user_id', '=', $user->id)
                    ->where('deals.workspace_id', '=', getActiveWorkSpace())
                    ->orderBy('deals.updated_at', 'desc')
                    ->take(5)->with('stage')->get();
            }

            $user = Auth::user()->name;
            $workspace = WorkSpace::where('id', $getActiveWorkSpace)->first();

            $deal_stage = DealStage::where('created_by', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->orderBy('order', 'ASC')->get();

            $dealStageName = [];
            $dealStageData = [];
            foreach ($deal_stage as $deal_stage_data) {
                $deal_stage = Deal::where('created_by', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->where('stage_id', $deal_stage_data->id)->orderBy('order', 'ASC')->count();
                $dealStageName[] = $deal_stage_data->name;
                $dealStageData[] = $deal_stage;
            }

            return view('lead::index', compact('calenderTasks', 'transdate', 'arrErr', 'arrCount', 'chartData', 'chartcall', 'deals', 'dealdata', 'dealStageName', 'dealStageData', 'workspace', 'modifiedDeals'));
        } else {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }

    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('lead manage')) {

            $creatorId = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            if (Auth::user()->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->where('id', '=', Auth::user()->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
            }
            if (Auth::user()->type == 'company' || Auth::user()->type == 'super admin') {
                $pipelines = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
            } else {
                $accessibleUserIds = Auth::user()->getAccessibleUserIds();
                $pipelines = Pipeline::where('created_by', '=', $creatorId)
                    ->where('workspace_id', $getActiveWorkSpace)
                    ->whereIn('id', function ($query) use ($accessibleUserIds) {
                        $query->select('pipeline_id')
                            ->from('leads')
                            ->where(
                                function ($q) use ($accessibleUserIds) {
                                    $q->whereIn('user_id', $accessibleUserIds)
                                        ->orWhereIn(
                                            'id',
                                            function ($sub) use ($accessibleUserIds) {
                                                $sub->select('lead_id')
                                                    ->from('user_leads')
                                                    ->whereIn('user_id', $accessibleUserIds);
                                            }
                                        );
                                }
                            );
                    })
                    ->get()
                    ->pluck('name', 'id');
            }

            // Filter Options
            $accessibleUserIds = Auth::user()->getAccessibleUserIds();
            $stages = $pipeline ? LeadStage::where('pipeline_id', $pipeline->id)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id') : collect();
            $sources = Source::where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
            // Scoping User Filters based on user type & assistance permissions (New Request)
            if (Auth::user()->type == 'super admin') {
                $base_user_query = User::where('workspace_id', $getActiveWorkSpace)->orWhere('id', Auth::user()->id);
            } elseif (Auth::user()->type == 'company') {
                $base_user_query = User::where('workspace_id', $getActiveWorkSpace)->orWhere('id', Auth::user()->id);
            } else {
                // For regular users, only show themselves + subordinates (as defined in getAccessibleUserIds)
                $base_user_query = User::whereIn('id', $accessibleUserIds);
            }

            $filtered_users = $base_user_query->where('type', '!=', 'client')->get();

            $users = $filtered_users->pluck('name', 'id');
            $creators = $filtered_users->pluck('name', 'id');
            $modifiers = $filtered_users->pluck('name', 'id');

            $saved_filters = \Workdo\Lead\Entities\LeadFilter::where('user_id', Auth::user()->id)
                ->where('workspace_id', $getActiveWorkSpace)
                ->get();

            $departments = [];
            $teams = [];
            if (module_is_active('Hrm')) {
                if (Auth::user()->type != 'company' && Auth::user()->type != 'super admin' && Auth::user()->visibility_level != 'all') {
                    $accessibleUserIds = Auth::user()->getAccessibleUserIds();
                    $employeeDeptsAndTeams = \Workdo\Hrm\Entities\Employee::whereIn('user_id', $accessibleUserIds)
                        ->where('workspace', $getActiveWorkSpace)
                        ->pluck('department_id')
                        ->filter()
                        ->unique()
                        ->toArray();

                    $allDeptAndTeamIds = $employeeDeptsAndTeams;
                    if (!empty($employeeDeptsAndTeams)) {
                        $deptsAndTeams = \Workdo\Hrm\Entities\Department::whereIn('id', $employeeDeptsAndTeams)->get();
                        foreach ($deptsAndTeams as $item) {
                            if ($item->type == 'team' && $item->parent_id) {
                                $allDeptAndTeamIds[] = $item->parent_id;
                            } elseif ($item->type == 'department') {
                                $childTeamIds = \Workdo\Hrm\Entities\Department::where('parent_id', $item->id)->where('type', 'team')->pluck('id')->toArray();
                                $allDeptAndTeamIds = array_merge($allDeptAndTeamIds, $childTeamIds);
                            }
                        }
                    }
                    $allDeptAndTeamIds = array_unique($allDeptAndTeamIds);

                    $departments = \Workdo\Hrm\Entities\Department::whereIn('id', $allDeptAndTeamIds)->where('workspace', $getActiveWorkSpace)->where('type', 'department')->pluck('name', 'id');
                    $teams = \Workdo\Hrm\Entities\Department::whereIn('id', $allDeptAndTeamIds)->where('workspace', $getActiveWorkSpace)->where('type', 'team')->pluck('name', 'id');
                } else {
                    $departments = \Workdo\Hrm\Entities\Department::where('workspace', $getActiveWorkSpace)->where('type', 'department')->pluck('name', 'id');
                    $teams = \Workdo\Hrm\Entities\Department::where('workspace', $getActiveWorkSpace)->where('type', 'team')->pluck('name', 'id');
                }
            }

            return view('lead::leads.index', compact('pipelines', 'pipeline', 'stages', 'sources', 'users', 'creators', 'modifiers', 'saved_filters', 'departments', 'teams'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function jsonDesignation(Request $request)
    {
        if ($request->has('get_parent') && $request->has('designation_id')) {
            $ids = is_array($request->designation_id) ? $request->designation_id : explode(',', $request->designation_id);
            if (module_is_active('Hrm')) {
                // designation_id from payload is actually a Team ID (department id where type is team)
                $deptIds = \Workdo\Hrm\Entities\Department::whereIn('id', $ids)->pluck('parent_id')->unique()->filter()->toArray();
                return response()->json(['department_ids' => array_values($deptIds)]);
            }
        }

        $teams = [];
        if (module_is_active('Hrm')) {
            $department_ids = $request->department_id;
            if (!empty($department_ids)) {
                if (!is_array($department_ids)) {
                    $department_ids = explode(',', $department_ids);
                }
                $department_ids = array_filter($department_ids);
            }

            $query = \Workdo\Hrm\Entities\Department::where('type', 'team')->where('workspace', getActiveWorkSpace());
            if (!empty($department_ids)) {
                $query->whereIn('parent_id', $department_ids);
            }

            $user = Auth::user();
            if ($user->type != 'company' && $user->type != 'super admin' && $user->visibility_level != 'all') {
                $accessibleUserIds = $user->getAccessibleUserIds();
                $employeeDeptsAndTeams = \Workdo\Hrm\Entities\Employee::whereIn('user_id', $accessibleUserIds)
                    ->where('workspace', getActiveWorkSpace())
                    ->pluck('department_id')
                    ->filter()
                    ->unique()
                    ->toArray();
                $query->whereIn('id', $employeeDeptsAndTeams);
            }

            $teams = $query->pluck('name', 'id');
        }
        return response()->json($teams);
    }

    public function jsonUser(Request $request)
    {
        $users = [];
        if (module_is_active('Hrm')) {
            $team_ids = $request->designation_id;
            if (!empty($team_ids)) {
                if (!is_array($team_ids)) {
                    $team_ids = explode(',', $team_ids);
                }
                $team_ids = array_filter($team_ids);
            }

            $department_ids = $request->department_id;
            if (!empty($department_ids)) {
                if (!is_array($department_ids)) {
                    $department_ids = explode(',', $department_ids);
                }
                $department_ids = array_filter($department_ids);
            }

            $accessibleUserIds = Auth::user()->getAccessibleUserIds();

            if (!empty($team_ids)) {
                // designation_id payload is a Team ID, filter by department_id since teams store their IDs in employee.department_id
                $employee_ids = \Workdo\Hrm\Entities\Employee::whereIn('department_id', $team_ids)->where('workspace', getActiveWorkSpace())->pluck('user_id')->toArray();
                $employee_ids = array_intersect($employee_ids, $accessibleUserIds);
                $users = User::whereIn('id', $employee_ids)->where('type', '!=', 'client')->pluck('name', 'id');
            } elseif (!empty($department_ids)) {
                $employee_ids = \Workdo\Hrm\Entities\Employee::whereIn('department_id', $department_ids)->where('workspace', getActiveWorkSpace())->pluck('user_id')->toArray();
                $employee_ids = array_intersect($employee_ids, $accessibleUserIds);
                $users = User::whereIn('id', $employee_ids)->where('type', '!=', 'client')->pluck('name', 'id');
            } else {
                $users = User::whereIn('id', $accessibleUserIds)->where('type', '!=', 'client')->pluck('name', 'id');
            }
        } else {
            // Fallback if HRM is not active, just return all non-client users as usual
            $accessibleUserIds = Auth::user()->getAccessibleUserIds();
            $users = User::whereIn('id', $accessibleUserIds)->where('type', '!=', 'client')->pluck('name', 'id');
        }
        return response()->json($users);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('lead create')) {

            $creatorId = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            $accessibleUsers = Auth::user()->getAccessibleUserIds();
            $users = User::whereIn('id', $accessibleUsers)->where('type', '!=', 'client')->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');

            if (count($users) != 0) {
                $users->prepend(__('Select Responsible Person'), '');
            }

            // Determine Default Stage
            if (Auth::user()->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->where('id', '=', Auth::user()->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
            }
            $stage = null;
            if (!empty($pipeline)) {
                $stage = LeadStage::where('pipeline_id', '=', $pipeline->id)->where('workspace_id', $getActiveWorkSpace)->first();
            }

            if (module_is_active('CustomField')) {
                $customFields = \Workdo\CustomField\Entities\CustomField::where('workspace_id', $getActiveWorkSpace)->where('module', '=', 'lead')->where('sub_module', 'lead')->get();

                // Initial load: don't filter server-side so they all exist in the DOM 
                // and can be dynamically shown/hidden by Javascript.
            } else {
                $customFields = null;
            }

            // Dedicated Lead Custom Fields
            $pipelinesInWorkspace = \Workdo\Lead\Entities\Pipeline::where('workspace_id', $getActiveWorkSpace)->pluck('id');
            foreach ($pipelinesInWorkspace as $pId) {
                \Workdo\Lead\Entities\LeadSection::ensurePipelineLayout($pId, $getActiveWorkSpace);
            }
            $leadCustomFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', $getActiveWorkSpace)->orderBy('order')->get();

            $user = Auth::user();
            $isResponsiblePersonEditable = $user->type == 'company' || in_array($user->visibility_level, ['team', 'department', 'all']);

            // Get all pipelines for selection
            $pipelines = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->pluck('name', 'id');

            // Get stages for default pipeline - Filtered by move permission
            $stages = [];
            if (!empty($pipeline)) {
                $all_stages = LeadStage::where('pipeline_id', '=', $pipeline->id)->where('workspace_id', $getActiveWorkSpace)->orderBy('order')->get();
                foreach ($all_stages as $s) {
                    if ($s->permissions()->can_edit) {
                        $stages[$s->id] = $s->name;
                    }
                }
            }

            $duplicateFields = company_setting('duplicate_fields') ? json_decode(company_setting('duplicate_fields'), true) : [];

            return view('lead::leads.create', compact('users', 'customFields', 'leadCustomFields', 'isResponsiblePersonEditable', 'pipelines', 'stages', 'pipeline', 'stage', 'duplicateFields'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if ($request->has('phone')) {
            $request->merge(['phone' => str_replace(' ', '', $request->phone)]);
        }

        $usr = Auth::user();
        if ($usr->isAbleTo('lead create')) {

            $creatorId = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            $validator = \Validator::make(
                $request->all(),
                [
                    'subject' => 'nullable|string|max:255',
                    'name' => 'nullable|string|max:255',
                    'email' => 'nullable|email|max:255',
                    'follow_up_date' => 'nullable|date',
                    'phone' => 'required',
                ]
            );

            // Stage ID for validation - use request stage or fallback to default
            $stageId = $request->stage_id;
            if (!$stageId) {
                $pipelineId = $request->pipeline_id ?? $usr->default_pipeline;
                if (!$pipelineId) {
                    $p = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                    $pipelineId = $p ? $p->id : null;
                }
                if ($pipelineId) {
                    $stage = LeadStage::where('pipeline_id', '=', $pipelineId)->where('workspace_id', $getActiveWorkSpace)->first();
                    $stageId = $stage ? $stage->id : null;
                }
            }

            // Global Duplicate Check (Enforce Settings)
            $dupeSettings = company_setting('duplicate_fields') ? json_decode(company_setting('duplicate_fields'), true) : [];
            foreach ($dupeSettings as $dfield) {
                $dvalue = null;
                if (in_array($dfield, ['name', 'email', 'phone', 'subject'])) {
                    $dvalue = $request->{$dfield};
                } elseif (str_starts_with($dfield, 'custom_')) {
                    $cfId = str_replace('custom_', '', $dfield);
                    $dvalue = $request->leadCustomField[$cfId] ?? null;
                }

                if ($dvalue) {
                    if (str_starts_with($dfield, 'custom_')) {
                        $cfId = str_replace('custom_', '', $dfield);
                        $exists = \Workdo\Lead\Entities\LeadCustomFieldValue::where('field_id', $cfId)
                            ->where('value', $dvalue)
                            ->whereHas('lead', function ($q) use ($getActiveWorkSpace) {
                                $q->where('workspace_id', $getActiveWorkSpace);
                            })->exists();
                    } else {
                        $exists = Lead::where('workspace_id', $getActiveWorkSpace)->where($dfield, $dvalue)->exists();
                    }

                    if ($exists) {
                        $validator->after(function ($validator) use ($dfield) {
                            $validator->errors()->add($dfield, __('Duplicate detected: ') . $dfield . __(' already exists.'));
                        });
                    }
                }
            }

            // Dynamic Validation for Custom Fields (Module CustomField)
            if ($request->has('customField') && $stageId) {
                $requiredFields = StageCustomField::where('stage_id', $stageId)
                    ->where('entity_type', 'lead')
                    ->where('is_required', 1)
                    ->pluck('custom_field_id')
                    ->toArray();
                foreach ($request->customField as $id => $value) {
                    if (in_array($id, $requiredFields) && empty($value)) {
                        $validator->after(function ($validator) use ($id) {
                            $validator->errors()->add('customField.' . $id, __('This custom field is required.'));
                        });
                    }
                }
            }

            // Validation for Dedicated Lead Custom Fields
            if ($request->has('leadCustomField') && isset($pipeline)) {
                $leadRequiredFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', $getActiveWorkSpace)
                    ->where('pipeline_id', $pipeline->id)
                    ->get();
                foreach ($leadRequiredFields as $field) {
                    $isVisible = false;
                    $isRequired = false;

                    if (!empty($field->required_stages) && $stageId && in_array($stageId, $field->required_stages)) {
                        $isRequired = true;
                        $isVisible = true;
                    }

                    // Role Check
                    if (!empty($field->visible_roles)) {
                        $userRoleIds = Auth::user()->roles->pluck('id')->toArray();
                        if (empty(array_intersect($userRoleIds, $field->visible_roles))) {
                            $isVisible = false;
                        }
                    }

                    // Only validate if visible and value is truly missing (not just blank)
                    if ($isVisible && $isRequired) {
                        $value = $request->leadCustomField[$field->id] ?? null;
                        // For required fields, check if value is null, empty string, or empty array
                        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                            $validator->after(function ($validator) use ($field) {
                                $validator->errors()->add('leadCustomField.' . $field->id, __($field->name . ' is required.'));
                            });
                        }
                    }

                    // Enforce Minimum Value if Number Field
                    if ($field->type === 'number' && !empty($field->stage_min_values) && is_array($field->stage_min_values) && isset($field->stage_min_values[$stageId])) {
                        $minVal = (float)$field->stage_min_values[$stageId];
                        $value = $request->leadCustomField[$field->id] ?? null;
                        if ($value !== null && $value !== '') {
                            if ((float)$value < $minVal) {
                                $validator->after(function ($validator) use ($field, $minVal) {
                                    $validator->errors()->add('leadCustomField.' . $field->id, __($field->name . ' must be at least ' . $minVal . ' for this stage.'));
                                });
                            }
                        } else {
                            $validator->after(function ($validator) use ($field, $minVal) {
                                $validator->errors()->add('leadCustomField.' . $field->id, __($field->name . ' must be at least ' . $minVal . ' for this stage.'));
                            });
                        }
                    }
                }
            }
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            // Use selected pipeline and stage from form
            if ($request->has('pipeline_id') && $request->has('stage_id')) {
                $pipeline = Pipeline::find($request->pipeline_id);
                $stage = LeadStage::find($request->stage_id);
            } else {
                // Fallback to default logic
                if ($usr->default_pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->where('id', '=', $usr->default_pipeline)->first();
                    if (!$pipeline) {
                        $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                    }
                } else {
                    $pipeline = Pipeline::find($request->global_pipeline);
                    if (empty($pipeline)) {
                        $pipeline = Pipeline::where('created_by', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                    }
                }

                if (!empty($pipeline)) {
                    $stage = LeadStage::find($request->global_stage);
                    if (empty($stage)) {
                        $stage = LeadStage::where('pipeline_id', $pipeline->id)->where('created_by', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                    }
                } else {
                    return redirect()->back()->with('error', __('Please create pipeline.'));
                }
            }

            if (empty($pipeline)) {
                return redirect()->back()->with('error', __('Please create pipeline.'));
            }

            if (empty($stage)) {
                return redirect()->back()->with('error', __('Please create stage for this pipeline.'));
            } else {
                // Check stage permissions
                if (!$stage->permissions()->can_edit) {
                    return redirect()->back()->with('error', __('Aapko is stage me lead create karne ka permission nahi hai.'));
                }

                if (empty($request->name)) {
                    $request->merge(['name' => $request->phone]);
                }
                if (empty($request->email)) {
                    $request->merge(['email' => 'null@gmail.com']);
                }

                $lead = new Lead();
                $lead->name = $request->name;
                $lead->email = $request->email;
                $lead->subject = $request->subject ?? 'New Lead';
                $lead->user_id = !empty($request->user_id) ? $request->user_id : $usr->id;
                $lead->pipeline_id = $pipeline->id;
                $lead->stage_id = $stage->id;
                $lead->phone = $request->phone;
                $lead->created_by = $creatorId;
                $lead->workspace_id = $getActiveWorkSpace;
                $lead->date = date('Y-m-d');
                $lead->follow_up_date = $request->follow_up_date;
                $lead->pan_number = $request->pan_number;
                $lead->aadhar_number = $request->aadhar_number;
                $lead->updated_by = $usr->id;
                $lead->save();

                self::triggerWorkflow($lead, $lead->stage_id);

                if (module_is_active('CustomField')) {
                    \Workdo\CustomField\Entities\CustomField::saveData($lead, $request->customField);
                }

                // Save Dedicated Lead Custom Fields
                $requestCustomFields = $request->all()['leadCustomField'] ?? [];
                if (!empty($requestCustomFields)) {
                    foreach ($requestCustomFields as $fieldId => $value) {
                        if ($request->hasFile("leadCustomField.$fieldId")) {
                            $file = $request->file("leadCustomField.$fieldId");
                            $fileName = time() . "_" . str_replace(' ', '_', $file->getClientOriginalName());
                            $file->move(storage_path('app/public/uploads/custom_fields'), $fileName);
                            $value = $fileName;
                        }
                        \Workdo\Lead\Entities\LeadCustomFieldValue::updateOrCreate(
                            ['lead_id' => $lead->id, 'field_id' => $fieldId],
                            ['value' => is_array($value) ? implode(',', array_map('trim', $value)) : trim($value)]
                        );
                    }
                }

                if (Auth::user()->hasRole('company')) {
                    $usrLeads = [
                        $usr->id,
                        $request->user_id,
                    ];
                } else {
                    $usrLeads = [
                        $creatorId,
                        $request->user_id,
                    ];
                }
                $usrLeads = array_filter(array_unique($usrLeads));

                // UserLead creation for single ownership enforcement
                if ($lead->user_id) {
                    \Workdo\Lead\Entities\UserLead::create([
                        'user_id' => $lead->user_id,
                        'lead_id' => $lead->id,
                    ]);
                }

                foreach ($usrLeads as $usrLead) {

                    // Create Notification
                    \App\Models\UserNotification::create([
                        'user_id' => $usrLead,
                        'type' => 'lead_assigned',
                        'data' => json_encode(['message' => 'New Lead Assigned: ' . $lead->name, 'id' => $lead->id]),
                        'is_read' => 0,
                        'workspace_id' => getActiveWorkSpace()
                    ]);
                }

                $leadArr = [
                    'lead_id' => $lead->id,
                    'name' => $lead->name,
                    'updated_by' => $usr->id,
                ];
                if (!empty(company_setting('Lead Assigned')) && company_setting('Lead Assigned') == true) {
                    $lArr = [
                        'lead_name' => $lead->name,
                        'lead_email' => $lead->email,
                        'lead_pipeline' => $pipeline->name,
                        'lead_stage' => $stage->name,
                    ];
                    $usrEmail = User::find($request->user_id);

                    // Send Email
                    $resp = EmailTemplate::sendEmailTemplate('Lead Assigned', [$usrEmail->id => $usrEmail->email], $lArr);
                }

                event(new CreateLead($request, $lead));

                $lead->activities()->create([
                    'user_id' => $usr->id,
                    'log_type' => 'Lead Created',
                    'remark' => json_encode(['title' => 'Lead Created', 'message' => __('Lead created by ') . $usr->name . __(' and assigned to ') . User::find($lead->user_id)->name])
                ]);

                $resp = null;
                $resp['is_success'] = true;
                return redirect()->back()->with('success', __('The lead has been created successfully.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Lead $lead)
    {
        if (Auth::user()->isAbleTo('lead show')) {
            if ($lead->is_active && $lead->isAccessible()) {

                $calenderTasks = [];
                $deal = Deal::where('id', '=', $lead->is_converted)->first();
                $stageCnt = LeadStage::where('pipeline_id', '=', $lead->pipeline_id)->where('created_by', '=', $lead->created_by)->get();
                $i = 0;
                foreach ($stageCnt as $stage) {
                    $i++;
                    if ($stage->id == $lead->stage_id) {
                        break;
                    }
                }
                $percentage = number_format(($i * 100) / count($stageCnt));

                if (module_is_active('CustomField')) {
                    $lead->customField = \Workdo\CustomField\Entities\CustomField::getData($lead, 'lead', 'lead');
                    $customFields = \Workdo\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'lead')->where('sub_module', 'lead')->get();
                } else {
                    $customFields = null;
                }

                // Fetch Lead Documents
                $leadDocuments = \Workdo\Lead\Entities\LeadDocument::where('workspace_id', getActiveWorkSpace())->get();
                $currentStageOrder = $lead->stage?->order ?? 0;
                $filteredDocuments = $leadDocuments->filter(function ($doc) use ($currentStageOrder) {
                    if (!$doc->stage_id)
                        return true;
                    $docStage = \Workdo\Lead\Entities\LeadStage::find($doc->stage_id);
                    return $docStage && $currentStageOrder >= $docStage->order;
                });
                $leadDocuments = $filteredDocuments;
                $uploadedFiles = \Workdo\Lead\Entities\LeadDocumentFile::where('lead_id', $lead->id)->get()->keyBy('document_id');

                // Fetch Dedicated Lead Custom Fields and Values
                \Workdo\Lead\Entities\LeadSection::ensurePipelineLayout($lead->pipeline_id, getActiveWorkSpace());

                $leadSections = \Workdo\Lead\Entities\LeadSection::where('workspace_id', getActiveWorkSpace())
                    ->where('pipeline_id', $lead->pipeline_id)
                    ->with([
                        'fields' => function ($q) use ($lead) {
                            $q->where('pipeline_id', $lead->pipeline_id)->orderBy('order');
                        }
                    ])
                    ->orderBy('order')
                    ->get();
                $leadCustomFieldValues = \Workdo\Lead\Entities\LeadCustomFieldValue::where('lead_id', $lead->id)->pluck('value', 'field_id')->toArray();

                // Fetch Tasks and Reminders with Visibility Scopes
                $accessibleUserIds = Auth::user()->getAccessibleUserIds();
                $tasks = $lead->tasks()->whereIn('user_id', $accessibleUserIds)->get();
                $reminders = $lead->getFilteredReminders();

                $overdueTasksCount = $tasks->where('status', 'overdue')->count();
                $todayRemindersCount = $lead->getTodayRemindersCount();

                return view('lead::leads.show', compact('lead', 'calenderTasks', 'deal', 'percentage', 'customFields', 'leadDocuments', 'uploadedFiles', 'leadSections', 'leadCustomFieldValues', 'tasks', 'reminders', 'overdueTasksCount', 'todayRemindersCount'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Lead $lead)
    {
        try {
            if (Auth::user()->isAbleTo('lead edit') && $lead->isAccessible()) {
                if (!$lead->stagePermissions()->can_edit) {
                    return redirect()->back()->with('error', __('You do not have permission to edit leads in this stage.'));
                }

                $creatorId = creatorId();
                $getActiveWorkSpace = getActiveWorkSpace();

                $pipelines = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id')->toArray();
                $pipelines = ['' => __('Select Pipeline')] + $pipelines;
                $sources = Source::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
                $products = [];
                if (module_is_active('ProductService')) {
                    $products = ProductService::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
                }
                $accessibleUsers = Auth::user()->getAccessibleUserIds();
                $filtered_users = User::whereIn('id', $accessibleUsers)->where('type', '!=', 'client')->where('workspace_id', $getActiveWorkSpace)->get();

                // Ensure current lead owner is in the list
                if ($lead->user_id && !$filtered_users->contains('id', $lead->user_id)) {
                    $lead_owner = User::find($lead->user_id);
                    if ($lead_owner) {
                        $filtered_users->push($lead_owner);
                    }
                }
                $users = $filtered_users->pluck('name', 'id')->toArray();

                if (count($users) != 0) {
                    $users = ['' => __('Select Responsible Person')] + $users;
                }

                $lead->sources = explode(',', $lead->sources);
                $lead->products = explode(',', $lead->products);

                if (module_is_active('CustomField')) {
                    $lead->customField = \Workdo\CustomField\Entities\CustomField::getData($lead, 'lead', 'lead');
                    $customFields = \Workdo\CustomField\Entities\CustomField::where('workspace_id', '=', $getActiveWorkSpace)->where('module', '=', 'lead')->where('sub_module', 'lead')->get();

                    // Filter by Stage Visibility
                    $stageCustomFields = StageCustomField::where('stage_id', $lead->stage_id)->pluck('custom_field_id')->toArray();
                    if (!empty($stageCustomFields)) {
                        $customFields = $customFields->filter(function ($field) use ($stageCustomFields) {
                            return in_array($field->id, $stageCustomFields);
                        });
                    }
                } else {
                    $customFields = null;
                }

                // Dedicated Lead Custom Fields
                $pipelinesInWorkspace = \Workdo\Lead\Entities\Pipeline::where('workspace_id', $getActiveWorkSpace)->pluck('id');
                foreach ($pipelinesInWorkspace as $pId) {
                    \Workdo\Lead\Entities\LeadSection::ensurePipelineLayout($pId, $getActiveWorkSpace);
                }

                $leadSections = \Workdo\Lead\Entities\LeadSection::where('workspace_id', $getActiveWorkSpace)
                    ->with([
                        'fields' => function ($q) {
                            $q->orderBy('order');
                        }
                    ])
                    ->orderBy('order')
                    ->get();
                $leadCustomFieldValues = \Workdo\Lead\Entities\LeadCustomFieldValue::where('lead_id', $lead->id)->pluck('value', 'field_id')->toArray();

                // Ensure current pipeline is in the list
                if ($lead->pipeline_id && !isset($pipelines[$lead->pipeline_id])) {
                    $curr_pipeline = Pipeline::find($lead->pipeline_id);
                    if ($curr_pipeline) {
                        $pipelines[$curr_pipeline->id] = $curr_pipeline->name;
                    }
                }

                $stages = LeadStage::where('pipeline_id', '=', $lead->pipeline_id)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id')->toArray();
                // Ensure current stage is in the list
                if ($lead->stage_id && !isset($stages[$lead->stage_id])) {
                    $curr_stage = LeadStage::find($lead->stage_id);
                    if ($curr_stage) {
                        $stages[$curr_stage->id] = $curr_stage->name;
                    }
                }

                $user = Auth::user();
                $isResponsiblePersonEditable = $user->type == 'company' || in_array($user->visibility_level, ['team', 'department', 'all']);

                return view('lead::leads.edit', compact('lead', 'pipelines', 'sources', 'products', 'users', 'customFields', 'isResponsiblePersonEditable', 'leadSections', 'leadCustomFieldValues', 'stages'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } catch (\Exception $e) {
            \Log::error('Lead Edit Modal Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return '<div class="alert alert-danger m-3">' . __('Error: ') . e($e->getMessage()) . '<br><small>' . e($e->getFile()) . ':' . $e->getLine() . '</small></div>';
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, Lead $lead)
    {
        if ($request->has('phone')) {
            $request->merge(['phone' => str_replace(' ', '', $request->phone)]);
        }

        \Log::debug('Lead Update Request Data: ' . json_encode($request->all()));
        if (Auth::user()->isAbleTo('lead edit') && $lead->isAccessible()) {
            if (!$lead->stagePermissions()->can_edit) {
                return redirect()->back()->with('error', __('You do not have permission to edit leads in this stage.'));
            }
            $creatorId = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            $validator = \Validator::make(
                $request->all(),
                [
                    'subject' => 'nullable|string|max:255',
                    'name' => 'required|string|max:255',
                    'email' => 'nullable|email|max:255',
                    'pipeline_id' => 'required|integer|exists:pipelines,id',
                    'user_id' => 'nullable|integer|exists:users,id',
                    'stage_id' => 'required|integer|exists:lead_stages,id',
                    'phone' => 'required',
                    'sources' => 'nullable|array',
                    'sources.*' => 'integer|exists:sources,id',
                    'products' => 'nullable|array',
                    'products.*' => 'integer|exists:product_services,id',
                    'follow_up_date' => 'nullable|date',
                ]
            );

            // Dynamic Validation for Custom Fields (Update)
            if ($request->has('customField')) {
                $requiredFields = StageCustomField::where('stage_id', $request->stage_id)->where('is_required', 1)->pluck('custom_field_id')->toArray();
                foreach ($request->customField as $id => $value) {
                    if (in_array($id, $requiredFields) && empty($value)) {
                        $validator->after(function ($validator) use ($id) {
                            $validator->errors()->add('customField.' . $id, __('This custom field is required.'));
                        });
                    }
                }
            }

            // Validation for Lead Custom Fields
            $customFieldErrors = $this->validateLeadCustomFields($lead, $request->stage_id, $request->all());
            if (!empty($customFieldErrors)) {
                return redirect()->back()->with('error', $customFieldErrors[0]);
            }

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $lead->name = $request->name;
            $lead->email = $request->email;
            $lead->subject = $request->subject ?? 'New Lead';
            if (!empty($request->user_id)) {
                if ($lead->user_id != $request->user_id) {
                    $oldUserIdComp = $lead->user_id;
                    $oldUserNameComp = $oldUserIdComp ? User::find($oldUserIdComp)->name : __('Unknown');
                    $lead->user_id = $request->user_id;
                    $newUserNameComp = User::find($request->user_id)->name;

                    // Message for transfer
                    $transferMsg = __('Lead responsibility transferred from ') . $oldUserNameComp . __(' to ') . $newUserNameComp . ' (' . __('at stage') . ': ' . ($lead->stage?->name ?? '-') . ')';

                    // Update UserLead: Add new user
                    UserLead::where('lead_id', $lead->id)->delete();
                    UserLead::firstOrCreate([
                        'lead_id' => $lead->id,
                        'user_id' => $request->user_id
                    ]);

                    // Notification for Lead Transfer
                    UserNotification::create([
                        'user_id' => $request->user_id,
                        'type' => 'lead_transfer',
                        'data' => [
                            'lead_id' => $lead->id,
                            'lead_name' => $lead->name,
                            'old_user_id' => $oldUserIdComp,
                            'transferred_by_name' => Auth::user()->name,
                        ],
                        'workspace_id' => getActiveWorkSpace(),
                    ]);
                }
            }
            $lead->pipeline_id = $request->pipeline_id;

            // Automation Logic (Centralized)
            $stageChanged = false;
            $oldStageForLog = null;
            $newStageForLog = null;
            if ($lead->stage_id != $request->stage_id) {
                $stageChanged = true;
                $oldStageForLog = LeadStage::find($lead->stage_id);
                $newStageForLog = LeadStage::find($request->stage_id);

                // Check CAN MOVE permission for the target stage
                if (!$newStageForLog->permissions()->can_move) {
                    return redirect()->back()->with('error', __('Aapko is stage me lead move karne ka permission nahi hai.'));
                }

                PipelineStageAutomation::run($lead, $request->stage_id);

                // Move to top logic
                Lead::where('stage_id', $request->stage_id)->increment('order');
                $lead->order = 0;

                // Notify department head about stage change
                $this->notifyDepartmentHead(
                    $lead,
                    __('Lead "') . $lead->name . __('" moved from ') . ($oldStageForLog ? $oldStageForLog->name : '?') . __(' to ') . ($newStageForLog ? $newStageForLog->name : '?')
                );
            }

            $lead->stage_id = $request->stage_id;
            $lead->sources = isset($request->sources) && !empty($request->sources) ? implode(",", array_filter($request->sources)) : null;
            $lead->products = isset($request->products) && !empty($request->products) ? implode(",", array_filter($request->products)) : null;
            $lead->notes = $request->notes;
            $lead->phone = $request->phone;
            $lead->follow_up_date = $request->follow_up_date;
            $lead->pan_number = $request->pan_number;
            $lead->aadhar_number = $request->aadhar_number;
            $lead->updated_by = Auth::user()->id;
            $lead->save();

            if ($stageChanged) {
                $this->triggerCustomFieldApis($lead, $request->stage_id);
                self::triggerWorkflow($lead, $request->stage_id);
            }

            // Consolidated Activity Log
            $logRemark = [
                'title' => $lead->name,
                'message' => Auth::user()->name . ' ' . __('updated lead details')
            ];

            if (isset($transferMsg)) {
                $logRemark['transfer_msg'] = $transferMsg;
                $logRemark['message'] = $transferMsg; // Primary message if only transfer
            }

            if ($stageChanged) {
                $logType = 'Move';
                $logRemark['old_status'] = $oldStageForLog ? $oldStageForLog->name : 'Unknown';
                $logRemark['new_status'] = $newStageForLog ? $newStageForLog->name : 'Unknown';
                $logRemark['old_stage_id'] = $oldStageForLog ? $oldStageForLog->id : null;
                $logRemark['new_stage_id'] = $newStageForLog ? $newStageForLog->id : null;
            } else {
                $logType = isset($transferMsg) ? 'Lead Transferred' : 'Lead Updated';
            }

            LeadActivityLog::create([
                'user_id' => Auth::user()->id,
                'lead_id' => $lead->id,
                'log_type' => $logType,
                'remark' => json_encode($logRemark),
            ]);

            if (module_is_active('CustomField')) {
                \Workdo\CustomField\Entities\CustomField::saveData($lead, $request->customField);
            }

            // Save Dedicated Lead Custom Fields
            $leadCustomFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', $getActiveWorkSpace)
                ->where('pipeline_id', $lead->pipeline_id)
                ->get();
            $requestCustomFields = $request->all()['leadCustomField'] ?? [];

            foreach ($leadCustomFields as $field) {
                if (array_key_exists($field->id, $requestCustomFields)) {
                    $value = $requestCustomFields[$field->id];

                    if ($request->hasFile("leadCustomField.$field->id")) {
                        $file = $request->file("leadCustomField.$field->id");
                        $fileName = time() . "_" . str_replace(' ', '_', $file->getClientOriginalName());
                        $file->move(storage_path('app/public/uploads/custom_fields'), $fileName);
                        $value = $fileName;
                    }

                    \Workdo\Lead\Entities\LeadCustomFieldValue::updateOrCreate(
                        ['lead_id' => $lead->id, 'field_id' => $field->id],
                        ['value' => is_array($value) ? implode(',', array_map('trim', $value)) : trim($value)]
                    );
                } else {
                    // For non-file fields, if it's missing from request, it means it's cleared (especially for multi-select)
                    // We only do this if the field was visible/applicable
                    $isVisible = true;
                    if (!empty($field->visible_stages) && !in_array($request->stage_id, $field->visible_stages)) {
                        $isVisible = false;
                    }
                    if (!empty($field->visible_roles)) {
                        $userRoleIds = Auth::user()->roles->pluck('id')->toArray();
                        if (empty(array_intersect($userRoleIds, $field->visible_roles))) {
                            $isVisible = false;
                        }
                    }

                    if ($isVisible && $field->type != 'file') {
                        \Workdo\Lead\Entities\LeadCustomFieldValue::updateOrCreate(
                            ['lead_id' => $lead->id, 'field_id' => $field->id],
                            ['value' => '']
                        );
                    }
                }
            }

            // KYC Comment Handling
            if ($request->has('kyc_comment') && !empty($request->kyc_comment) && \Auth::user()->isAbleTo('lead kyc comment')) {
                $discussion = new LeadDiscussion();
                $discussion->comment = $request->kyc_comment;
                $discussion->lead_id = $lead->id;
                $discussion->created_by = Auth::user()->id;
                $discussion->is_kyc = 1;
                $discussion->save();

                // Notify responsible persons about KYC comment
                $responsiblePersonIds = $lead->users->pluck('id')->toArray();
                $responsiblePersonIds[] = $lead->user_id; // Owner
                $responsiblePersonIds = array_unique(array_filter($responsiblePersonIds));

                foreach ($responsiblePersonIds as $recipientId) {
                    if ($recipientId != Auth::user()->id) {
                        UserNotification::create([
                            'user_id' => $recipientId,
                            'type' => 'kyc_comment',
                            'data' => [
                                'lead_id' => $lead->id,
                                'lead_name' => $lead->name,
                                'comment' => $request->kyc_comment,
                                'created_by_name' => Auth::user()->name,
                            ],
                            'workspace_id' => getActiveWorkSpace(),
                        ]);
                    }
                }
            }
            event(new UpdateLead($request, $lead));

            return redirect()->back()->with('success', __('The lead deatails are updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function inlineUpdate(Request $request, $id)
    {
        $lead = Lead::find($id);
        if (!$lead || !Auth::user()->isAbleTo('lead edit') || !$lead->isAccessible()) {
            return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 403);
        }

        if (!$lead->stagePermissions()->can_edit) {
            return response()->json(['is_success' => false, 'error' => __('You do not have permission to edit leads in this stage.')], 403);
        }

        $fieldName = $request->input('field_name');
        $fieldValue = $request->input('field_value');
        $isSystem = $request->input('is_system', 0);

        if ($isSystem) {
            if (in_array($fieldName, ['email', 'phone', 'pan_number', 'aadhar_number'])) {
                if ($fieldName == 'phone') {
                    $fieldValue = str_replace(' ', '', $fieldValue);
                }
                $lead->$fieldName = $fieldValue;
                $lead->save();

                LeadActivityLog::create([
                    'user_id' => Auth::user()->id,
                    'lead_id' => $lead->id,
                    'log_type' => 'Lead Updated',
                    'remark' => json_encode([
                        'title' => $lead->name,
                        'message' => Auth::user()->name . ' ' . __('updated system field ') . $fieldName
                    ]),
                ]);

                return response()->json(['is_success' => true, 'message' => __('Field updated successfully.')]);
            }
            return response()->json(['is_success' => false, 'error' => __('Invalid field.')], 400);
        } else {
            $fieldId = (int)$fieldName;
            $customField = \Workdo\Lead\Entities\LeadCustomField::find($fieldId);
            if (!$customField) {
                return response()->json(['is_success' => false, 'error' => __('Custom field not found.')], 404);
            }

            if ($customField->type == 'file' && $request->hasFile('field_value')) {
                $file = $request->file('field_value');
                $fileName = time() . "_" . str_replace(' ', '_', $file->getClientOriginalName());
                $file->move(storage_path('app/public/uploads/custom_fields'), $fileName);
                $fieldValue = $fileName;
            }

            \Workdo\Lead\Entities\LeadCustomFieldValue::updateOrCreate(
                ['lead_id' => $lead->id, 'field_id' => $fieldId],
                ['value' => is_array($fieldValue) ? implode(',', array_map('trim', $fieldValue)) : trim($fieldValue)]
            );

            LeadActivityLog::create([
                'user_id' => Auth::user()->id,
                'lead_id' => $lead->id,
                'log_type' => 'Lead Updated',
                'remark' => json_encode([
                    'title' => $lead->name,
                    'message' => Auth::user()->name . ' ' . __('updated custom field ') . $customField->name
                ]),
            ]);

            return response()->json([
                'is_success' => true, 
                'message' => __('Field updated successfully.'),
                'value' => $fieldValue,
                'type' => $customField->type
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Lead $lead)
    {
        if (Auth::user()->isAbleTo('lead delete') && $lead->isAccessible()) {

            LeadDiscussion::where('lead_id', '=', $lead->id)->delete();
            UserLead::where('lead_id', '=', $lead->id)->delete();
            $leadfiles = LeadFile::where('lead_id', '=', $lead->id)->get();
            foreach ($leadfiles as $leadfile) {

                delete_file($leadfile->file_path);
                $leadfile->delete();
            }
            LeadActivityLog::where('lead_id', '=', $lead->id)->delete();
            if (module_is_active('CustomField')) {
                $customFields = \Workdo\CustomField\Entities\CustomField::where('module', 'lead')->where('sub_module', 'lead')->get();
                foreach ($customFields as $customField) {
                    $value = \Workdo\CustomField\Entities\CustomFieldValue::where('record_id', '=', $lead->id)->where('field_id', $customField->id)->first();
                    if (!empty($value)) {
                        $value->delete();
                    }
                }
            }
            event(new DestroyLead($lead));

            $lead->delete();

            return redirect()->back()->with('success', __('The lead has been deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function lead_list(LeadDataTable $dataTable)
    {
        $usr = Auth::user();

        if ($usr->isAbleTo('lead manage')) {
            $creatorId = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            if ($usr->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('id', '=', $usr->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
            }

            $pipelines = Pipeline::where('created_by', '=', $creatorId)->get()->pluck('name', 'id');

            // Filter Options
            $accessibleUserIds = $usr->getAccessibleUserIds();
            $stages = LeadStage::where('pipeline_id', $pipeline->id)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
            $sources = Source::where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
            $users = User::whereIn('id', $accessibleUserIds)->get()->pluck('name', 'id');
            $creators = User::where('workspace_id', $getActiveWorkSpace)->where('type', '!=', 'client')->get()->pluck('name', 'id');
            $modifiers = User::where('workspace_id', $getActiveWorkSpace)->where('type', '!=', 'client')->get()->pluck('name', 'id');

            $departments = [];
            $teams = [];
            if (module_is_active('Hrm')) {
                if ($usr->type != 'company' && $usr->type != 'super admin' && $usr->visibility_level != 'all') {
                    $employeeDeptsAndTeams = \Workdo\Hrm\Entities\Employee::whereIn('user_id', $accessibleUserIds)
                        ->where('workspace', $getActiveWorkSpace)
                        ->pluck('department_id')
                        ->filter()
                        ->unique()
                        ->toArray();

                    $allDeptAndTeamIds = $employeeDeptsAndTeams;
                    if (!empty($employeeDeptsAndTeams)) {
                        $deptsAndTeams = \Workdo\Hrm\Entities\Department::whereIn('id', $employeeDeptsAndTeams)->get();
                        foreach ($deptsAndTeams as $item) {
                            if ($item->type == 'team' && $item->parent_id) {
                                $allDeptAndTeamIds[] = $item->parent_id;
                            } elseif ($item->type == 'department') {
                                $childTeamIds = \Workdo\Hrm\Entities\Department::where('parent_id', $item->id)->where('type', 'team')->pluck('id')->toArray();
                                $allDeptAndTeamIds = array_merge($allDeptAndTeamIds, $childTeamIds);
                            }
                        }
                    }
                    $allDeptAndTeamIds = array_unique($allDeptAndTeamIds);

                    $departments = \Workdo\Hrm\Entities\Department::whereIn('id', $allDeptAndTeamIds)->where('workspace', $getActiveWorkSpace)->where('type', 'department')->pluck('name', 'id');
                    $teams = \Workdo\Hrm\Entities\Department::whereIn('id', $allDeptAndTeamIds)->where('workspace', $getActiveWorkSpace)->where('type', 'team')->pluck('name', 'id');
                } else {
                    $departments = \Workdo\Hrm\Entities\Department::where('workspace', $getActiveWorkSpace)->where('type', 'department')->pluck('name', 'id');
                    $teams = \Workdo\Hrm\Entities\Department::where('workspace', $getActiveWorkSpace)->where('type', 'team')->pluck('name', 'id');
                }
            }

            // Initialize saved_filters (can be extended later for filter persistence)
            $saved_filters = [];

            return $dataTable->render('lead::leads.list', compact('pipelines', 'pipeline', 'stages', 'sources', 'users', 'creators', 'modifiers', 'saved_filters', 'departments', 'teams'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function json(Request $request)
    {
        $lead_stages = new LeadStage();
        if ($request->pipeline_id && !empty($request->pipeline_id)) {
            $lead_stages = $lead_stages->where('pipeline_id', '=', $request->pipeline_id);
            $lead_stages = $lead_stages->get()->pluck('name', 'id');
        } else {
            $lead_stages = [];
        }

        return response()->json($lead_stages);
    }

    public function fileUpload($id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {

                $file_name = $request->file->getClientOriginalName();
                $file_path = $request->lead_id . "_" . md5(time()) . "_" . $request->file->getClientOriginalName();

                $url = upload_file($request, 'file', $file_name, 'leads', []);
                if (isset($url['flag']) && $url['flag'] == 1) {
                    $file = LeadFile::create(
                        [
                            'lead_id' => $request->lead_id,
                            'file_name' => $file_name,
                            'file_path' => $url['url'],
                        ]
                    );
                    $return = [];
                    $return['is_success'] = true;
                    $return['download'] = get_file($url['url']);
                    $return['delete'] = route(
                        'leads.file.delete',
                        [
                            $lead->id,
                            $file->id,
                        ]
                    );

                    LeadActivityLog::create(
                        [
                            'user_id' => Auth::user()->id,
                            'lead_id' => $lead->id,
                            'log_type' => 'Upload File',
                            'remark' => json_encode(['file_name' => $file_name]),
                        ]
                    );

                    event(new LeadUploadFile($request, $lead));
                    $lead->touch();

                    return response()->json($return);
                } else {
                    return response()->json(
                        [
                            'is_success' => false,
                            'error' => $url['msg'],
                        ],
                        401
                    );
                }
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function fileDownload($id, $file_id)
    {
        if (Auth::user()->isAbleTo('lead show')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $file = LeadFile::find($file_id);
                if ($file) {
                    $file_path = get_base_file($file->file_path);
                    $filename = $file->file_name;

                    return \Response::download(
                        $file_path,
                        $filename,
                        [
                            'Content-Length: ' . get_size($file_path),
                        ]
                    );
                } else {
                    return redirect()->back()->with('error', __('The file does not exist.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function fileDelete($id, $file_id)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $file = LeadFile::find($file_id);
                if ($file) {
                    delete_file($file->file_path);
                    $file->delete();
                    $lead->touch();

                    event(new DestroyLeadFile($lead));

                    return response()->json(['is_success' => true, 'success' => __('The file has been deleted.')], 200);
                } else {
                    return response()->json(
                        [
                            'is_success' => false,
                            'error' => __('The file does not exist.'),
                        ],
                        200
                    );
                }
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function noteStore($id, Request $request)
    {
        $lead = Lead::find($id);

        if ($lead && $lead->isAccessible()) {
            $lead->notes = $request->notes;
            $lead->save();
            $lead->touch();

            event(new LeadAddNote($request, $lead));

            LeadActivityLog::create([
                'user_id' => Auth::user()->id,
                'lead_id' => $lead->id,
                'log_type' => 'Note Updated',
                'remark' => json_encode([
                    'title' => $lead->name,
                    'message' => Auth::user()->name . ' ' . __('updated the lead note'),
                    'old_stage_id' => $lead->stage_id // Include for real-time sync logic in changesSince
                ]),
            ]);

            return response()->json(
                [
                    'is_success' => true,
                    'success' => __('The note has been saved successfully.'),
                ],
                200
            );
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function labels($id)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            if (!$lead->stagePermissions()->can_edit) {
                return response()->json(['error' => __('You do not have permission to edit leads in this stage.')]);
            }
            if ($lead && $lead->isAccessible()) {
                $labels = Label::where('pipeline_id', '=', $lead->pipeline_id)->get();
                $selected = $lead->labels();
                if ($selected) {
                    $selected = $selected->pluck('name', 'id')->toArray();
                } else {
                    $selected = [];
                }

                return view('lead::leads.labels', compact('lead', 'labels', 'selected'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function labelStore($id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $leads = Lead::find($id);
            if (!$leads->stagePermissions()->can_edit) {
                return response()->json(['error' => __('You do not have permission to edit leads in this stage.')]);
            }
            if ($leads && $leads->isAccessible()) {
                if ($request->labels) {
                    $leads->labels = implode(',', $request->labels);
                } else {
                    $leads->labels = $request->labels;
                }
                $leads->save();
                $leads->touch();

                return redirect()->back()->with('success', __('The label details are updated successfully.'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function userEdit($id)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            if (!$lead->stagePermissions()->can_edit) {
                return response()->json(['error' => __('You do not have permission to edit leads in this stage.')]);
            }
            if ($lead && $lead->isAccessible()) {
                $creatorId = creatorId();
                $getActiveWorkSpace = getActiveWorkSpace();
                $users = User::where('active_workspace', '=', $getActiveWorkSpace)->where('created_by', '=', $creatorId)->where('type', '!=', 'client')->get();

                $users = $users->pluck('name', 'id');
                return view('lead::leads.users', compact('lead', 'users'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function userUpdate($id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $usr = Auth::user();
            $lead = Lead::find($id);
            if (!$lead->stagePermissions()->can_edit) {
                return response()->json(['error' => __('You do not have permission to edit leads in this stage.')]);
            }
            if ($lead && $lead->isAccessible()) {
                if (!empty($request->users)) {
                    $userId = $request->users;
                    // Handle if it comes as array or string (blade change makes it string, but robustness helps)
                    if (is_array($userId)) {
                        $userId = reset($userId);
                    }

                    $lead->user_id = $userId;
                    $lead->save();

                    // Clear existing shared users as we are enforcing single ownership
                    UserLead::where('lead_id', '=', $lead->id)->delete();

                    $lead->touch();
                }

                event(new LeadAddUser($request, $lead));

                if (!empty($request->users)) {
                    return redirect()->back()->with('success', __('Responsibile person updated successfully.'));
                } else {
                    return redirect()->back()->with('error', __('Please select valid user.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function userDestroy($id, $user_id)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                UserLead::where('lead_id', '=', $lead->id)->where('user_id', '=', $user_id)->delete();
                $lead->touch();

                event(new DestroyLeadUser($lead));

                return redirect()->back()->with('success', __('The user has been deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function productEdit($id)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $creatorId = creatorId();
                $getActiveWorkSpace = getActiveWorkSpace();
                $products = [];
                if (module_is_active('ProductService')) {
                    $products = \Workdo\ProductService\Entities\ProductService::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->whereNOTIn('id', explode(',', $lead->products))->get()->pluck('name', 'id');
                }
                return view('lead::leads.products', compact('lead', 'products'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function productUpdate($id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $usr = Auth::user();
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                if (!empty($request->products)) {
                    $products = array_filter($request->products);
                    $old_products = explode(',', $lead->products);
                    $lead->products = implode(',', array_merge($old_products, $products));
                    $lead->save();
                    $lead->touch();

                    $objProduct = [];
                    if (module_is_active('ProductService')) {
                        $objProduct = \Workdo\ProductService\Entities\ProductService::whereIN('id', $products)->get()->pluck('name', 'id')->toArray();
                    }

                    LeadActivityLog::create(
                        [
                            'user_id' => $usr->id,
                            'lead_id' => $lead->id,
                            'log_type' => 'Add Product',
                            'remark' => json_encode(['title' => implode(",", $objProduct)]),
                        ]
                    );

                    $productArr = [
                        'lead_id' => $lead->id,
                        'name' => $lead->name,
                        'updated_by' => $usr->id,
                    ];
                }

                event(new LeadAddProduct($request, $lead));

                if (!empty($products) && !empty($request->products)) {
                    return redirect()->back()->with('success', __('Products have been updated successfully.'))->with('status', 'products');
                } else {
                    return redirect()->back()->with('error', __('Please select valid product.'))->with('status', 'general');
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
        }
    }

    public function productDestroy($id, $product_id)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $products = explode(',', $lead->products);
                foreach ($products as $key => $product) {
                    if ($product_id == $product) {
                        unset($products[$key]);
                    }
                }
                $lead->products = implode(',', $products);
                $lead->save();
                $lead->touch();

                event(new DestroyLeadProduct($lead));

                return redirect()->back()->with('success', __('The product has been deleted.'))->with('status', 'products');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
        }
    }
    public function sourceEdit($id)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $creatorId = creatorId();
                $getActiveWorkSpace = getActiveWorkSpace();
                $sources = Source::where('created_by', '=', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->get();

                $selected = $lead->sources();
                if ($selected) {
                    $selected = $selected->pluck('name', 'id')->toArray();
                }

                return view('lead::leads.sources', compact('lead', 'sources', 'selected'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function sourceUpdate($id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $usr = Auth::user();
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                if (!empty($request->sources) && count($request->sources) > 0) {
                    $lead->sources = implode(',', $request->sources);
                } else {
                    $lead->sources = "";
                }

                $lead->save();
                $lead->touch();

                LeadActivityLog::create(
                    [
                        'user_id' => $usr->id,
                        'lead_id' => $lead->id,
                        'log_type' => 'Update Sources',
                        'remark' => json_encode(['title' => 'Update Sources']),
                    ]
                );

                $leadArr = [
                    'lead_id' => $lead->id,
                    'name' => $lead->name,
                    'updated_by' => $usr->id,
                ];

                event(new LeadSourceUpdate($request, $lead));

                return redirect()->back()->with('success', __('The sources has been changes successfully'))->with('status', 'sources');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
        }
    }

    public function sourceDestroy($id, $source_id)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $sources = explode(',', $lead->sources);
                foreach ($sources as $key => $source) {
                    if ($source_id == $source) {
                        unset($sources[$key]);
                    }
                }
                $lead->sources = implode(',', $sources);
                $lead->save();

                event(new DestroyLeadSource($lead));

                return redirect()->back()->with('success', __('The source has been deleted.'))->with('status', 'sources');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
        }
    }

    public function discussionCreate($id, Request $request)
    {
        $lead = Lead::find($id);
        if ($lead && $lead->isAccessible()) {
            $is_kyc = $request->get('is_kyc', 0);
            $discussions = $lead->discussions;
            if ($is_kyc == 1) {
                $discussions = $discussions->where('is_kyc', 1);
            }
            return view('lead::leads.discussions', compact('lead', 'is_kyc', 'discussions'));
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function discussionStore($id, Request $request)
    {
        $usr = Auth::user();
        $lead = Lead::find($id);
        if ($lead && $lead->isAccessible()) {

            $is_kyc = $request->get('is_kyc', 0);

            // If it's a KYC comment, verify the user has the 'lead kyc comment' permission
            if ($is_kyc && !\Auth::user()->isAbleTo('lead kyc comment')) {
                return redirect()->back()->with('error', __('You do not have permission to post KYC comments.'))->with('status', 'discussion');
            }

            $discussion = new LeadDiscussion();
            $discussion->comment = $request->comment;
            $discussion->lead_id = $lead->id;
            $discussion->created_by = $usr->id;
            $discussion->is_kyc = $is_kyc;
            $discussion->save();
            $lead->touch();

            $leadArr = [
                'lead_id' => $lead->id,
                'name' => $lead->name,
                'updated_by' => $usr->id,
            ];

            event(new LeadAddDiscussion($request, $lead));

            LeadActivityLog::create([
                'user_id' => $usr->id,
                'lead_id' => $lead->id,
                'log_type' => 'Discussion',
                'remark' => json_encode([
                    'title' => $lead->name,
                    'message' => $usr->name . ' ' . ($is_kyc ? __('posted a KYC comment') : __('posted a comment')),
                    'old_stage_id' => $lead->stage_id // Include for real-time sync logic in changesSince
                ]),
            ]);

            if ($is_kyc) {
                $responsiblePersonIds = $lead->users->pluck('id')->toArray();
                $responsiblePersonIds[] = $lead->user_id; // Owner
                $responsiblePersonIds = array_unique(array_filter($responsiblePersonIds));

                foreach ($responsiblePersonIds as $recipientId) {
                    if ($recipientId != $usr->id) {
                        UserNotification::create([
                            'user_id' => $recipientId,
                            'type' => 'kyc_comment',
                            'data' => [
                                'lead_id' => $lead->id,
                                'lead_name' => $lead->name,
                                'comment' => $request->comment,
                                'created_by_name' => $usr->name,
                            ],
                            'workspace_id' => getActiveWorkSpace(),
                        ]);
                    }
                }
            } else {
                // General Discussion Notification
                $responsiblePersonIds = $lead->users->pluck('id')->toArray();
                $responsiblePersonIds[] = $lead->user_id; // Owner
                $responsiblePersonIds = array_unique(array_filter($responsiblePersonIds));

                foreach ($responsiblePersonIds as $recipientId) {
                    if ($recipientId != $usr->id) {
                        UserNotification::create([
                            'user_id' => $recipientId,
                            'type' => 'new_comment',
                            'data' => json_encode(['message' => 'New Comment on Lead: ' . $lead->name, 'id' => $lead->id]),
                            'is_read' => 0,
                            'workspace_id' => getActiveWorkSpace(),
                        ]);
                    }
                }
            }

            return redirect()->back()->with('success', __('The message has been added successfully.'))->with('status', $is_kyc ? 'kyc-discussion' : 'discussion');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'discussion');
        }
    }

    public function discussionDestroy($id, $discussion_id)
    {
        $usr = Auth::user();
        $lead = Lead::find($id);
        if ($lead && $lead->isAccessible()) {
            $discussion = LeadDiscussion::find($discussion_id);
            if ($discussion) {
                if ($usr->type == 'company' || $discussion->created_by == $usr->id || $usr->isAbleTo('lead delete')) {
                    $discussion->delete();
                    $lead->touch();
                    return redirect()->back()->with('success', __('The message has been deleted.'));
                } else {
                    return redirect()->back()->with('error', __('Permission Denied.'));
                }
            } else {
                return redirect()->back()->with('error', __('The message does not exist.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function order(Request $request)
    {
        try {
            if (Auth::user()->isAbleTo('lead move')) {
                $usr = Auth::user();
                $post = $request->all();
                $lead = Lead::find($post['lead_id']);

                if (!$lead || !$lead->isAccessible()) {
                    return response()->json(['error' => __('Permission Denied.')]);
                }

                if (!$lead->stagePermissions()->can_edit) {
                    return response()->json(['error' => __('You do not have permission to edit leads in this stage.')]);
                }

                $lead_users = $lead->users->pluck('email', 'id')->toArray();

                $oldStageId = $lead->stage_id;
                $newStageId = $post['stage_id'];
                $hasStageChanged = ($oldStageId != $newStageId);

                // If stage changed, run logs/automation BEFORE we update $lead object state
                if ($hasStageChanged) {
                    $oldStage = $lead->stage;
                    $newStage = LeadStage::find($newStageId);

                    if ($newStage) {
                        $customFieldErrors = $this->validateLeadCustomFields($lead, $newStageId);
                        if (!empty($customFieldErrors)) {
                            return response()->json(['error' => $customFieldErrors[0]]);
                        }

                        if ($oldStage && !$oldStage->permissions($usr)->can_move) {
                            return response()->json(['error' => __('You do not have permission to move leads out of this stage.')]);
                        }

                        if (!$newStage->permissions()->can_move) {
                            return response()->json(['error' => __('Aapko is stage tak lead move karne ka access nahi hai.')]);
                        }

                        // Create Move Activity Log
                        LeadActivityLog::create([
                            'user_id' => $usr->id,
                            'lead_id' => $lead->id,
                            'log_type' => 'Move',
                            'remark' => json_encode([
                                'title' => $lead->name,
                                'old_status' => $oldStage ? $oldStage->name : 'Unknown',
                                'new_status' => $newStage->name,
                                'old_stage_id' => $oldStageId,
                                'new_stage_id' => $newStageId,
                            ]),
                        ]);

                        if (!empty(company_setting('Lead Moved')) && company_setting('Lead Moved') == true) {
                            $lArr = [
                                'lead_name' => $lead->name,
                                'lead_email' => $lead->email,
                                'lead_pipeline' => $lead->pipeline->name,
                                'lead_stage' => $newStage->name,
                                'lead_old_stage' => $oldStage ? $oldStage->name : 'Unknown',
                                'lead_new_stage' => $newStage->name,
                            ];
                            EmailTemplate::sendEmailTemplate('Lead Moved', $lead_users, $lArr);
                        }

                        PipelineStageAutomation::run($lead, $newStageId);

                        $this->notifyDepartmentHead(
                            $lead,
                            __('Lead "') . $lead->name . __('" moved to ') . $newStage->name
                        );
                    }
                }

                // NOW explicitly save the moved lead to ensure persistence
                $lead->stage_id = $newStageId;
                $lead->save();

                self::triggerWorkflow($lead, $newStageId);

                // Process Conditional Custom Field API integrations
                $this->triggerCustomFieldApis($lead, $newStageId);

                event(new LeadMoved($request, $lead));

                // Parse and update order for ALL leads in the target column
                $orderRaw = $request->order ?? [];
                $orderArr = is_array($orderRaw) ? $orderRaw : explode(',', (string) $orderRaw);
                $orderArr = array_filter($orderArr);

                \Log::debug("Kanban Persistence Final - Lead: {$lead->id} -> Stage: {$newStageId} | Col Size: " . count($orderArr));

                foreach ($orderArr as $key => $item) {
                    Lead::where('id', $item)->update([
                        'order' => $key,
                        'stage_id' => $newStageId
                    ]);
                }
                $old_status = !empty($post['old_status']) ? $post['old_status'] : 0;
                $pipeline_id = !empty($post['pipeline_id']) ? $post['pipeline_id'] : 0;

                // Use filter-aware count so badge reflects applied filters
                $oldStageObj = LeadStage::find($old_status);
                $newStageObj = LeadStage::find($post['stage_id']);
                $old_stage_count = $oldStageObj ? $oldStageObj->leadCount($request) : 0;
                $new_stage_count = $newStageObj ? $newStageObj->leadCount($request) : 0;

                return response()->json([
                    'success' => __('Lead moved successfully.'),
                    'old_stage_count' => $old_stage_count,
                    'new_stage_count' => $new_stage_count,
                ]);
            } else {
                return response()->json(['error' => __('Permission denied.')]);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    /**
     * Returns leads whose stage_id changed after the given Unix timestamp.
     * Used by Kanban board JS to do real-time polling.
     * Returns: [{id, name, stage_id, old_stage_id, new_count, old_count}]
     */
    public function changesSince(Request $request)
    {
        try {
            $since = $request->get('ts');
            $pipelineId = $request->get('pipeline_id');

            if (!$since || !$pipelineId) {
                return response()->json([]);
            }

            $sinceDate = \Carbon\Carbon::createFromTimestamp((int) $since);
            $workspace = getActiveWorkSpace();

            // Get recent logs (All types now tracked for real-time sync)
            $logs = \Workdo\Lead\Entities\LeadActivityLog::where('created_at', '>', $sinceDate)
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('lead_id');

            if ($logs->isEmpty()) {
                return response()->json(['changes' => [], 'counts' => []]);
            }

            $changes = [];
            $allLeadIds = $logs->keys();

            $baseQuery = Lead::whereIn('id', $allLeadIds)
                ->where('pipeline_id', $pipelineId)
                ->where('workspace_id', $workspace);

            // Apply all filters (browser filters + HRM filters)
            $baseQuery = $this->applyLeadFilters($baseQuery, $request);

            $leadsFetched = $baseQuery->get()->keyBy('id');

            // Collect all affected stage IDs for count refresh
            $affectedStageIds = collect();

            foreach ($logs as $leadId => $leadLogs) {
                $lead = $leadsFetched->get($leadId);

                if (!$lead || !$lead->isAccessible()) {
                    continue;
                }

                $latestLog = $leadLogs->first();
                // Parse Log to find old stage ID
                $remark = json_decode($latestLog->remark, true);
                $oldStageId = $remark['old_stage_id'] ?? null;

                // Fallback to name search if ID not in old logs
                if (!$oldStageId && !empty($remark->old_status)) {
                    $os = LeadStage::where('name', $remark->old_status)->where('pipeline_id', $pipelineId)->first();
                    if ($os)
                        $oldStageId = $os->id;
                }

                $affectedStageIds->push($lead->stage_id);
                if ($oldStageId)
                    $affectedStageIds->push($oldStageId);

                $changes[] = [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'stage_id' => $lead->stage_id,
                    'old_stage_id' => $oldStageId,
                ];
            }

            if (empty($changes)) {
                return response()->json(['changes' => [], 'counts' => []]);
            }

            // Compute server-side filter-aware counts for all affected stages in a SINGLE query
            $counts = [];
            $uniqueStageIds = $affectedStageIds->unique()->toArray();

            if (!empty($uniqueStageIds)) {
                // We use a dummy stage object to leverage the leadCount query logic but modify it for batching
                $dummyStage = new LeadStage();
                $baseCountQuery = Lead::whereIn('leads.stage_id', $uniqueStageIds)
                    ->where('leads.workspace_id', '=', $workspace);

                // Replicate the filtering logic from leadCount but with groupBy
                $user = Auth::user();
                if ($user->type == 'client') {
                    $baseCountQuery->join('client_leads', 'client_leads.lead_id', '=', 'leads.id')
                        ->where('client_leads.client_id', '=', $user->id);
                } elseif ($user->type != 'company' && $user->visibility_level != 'all') {
                    $accessibleUserIds = $user->getAccessibleUserIds();
                    $baseCountQuery->where(function ($q) use ($accessibleUserIds) {
                        $q->whereIn('leads.user_id', $accessibleUserIds)
                            ->orWhereHas('users', function ($subQ) use ($accessibleUserIds) {
                                $subQ->whereIn('users.id', $accessibleUserIds);
                            });
                    });
                }

                // Apply browser filters to counts as well
                $baseCountQuery = $this->applyLeadFilters($baseCountQuery, $request);

                $rawCounts = $baseCountQuery->groupBy('stage_id')
                    ->selectRaw('stage_id, count(*) as total')
                    ->pluck('total', 'stage_id')
                    ->toArray();

                foreach ($uniqueStageIds as $sid) {
                    $counts[$sid] = $rawCounts[$sid] ?? 0;
                }
            }

            $nowTs = time();

            return response()->json([
                'changes' => $changes,
                'counts' => $counts,
                'now_ts' => $nowTs
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function applyLeadFilters($query, Request $request)
    {
        if ($request->has('responsible_person') && !empty($request->responsible_person)) {
            $respIds = (array) $request->responsible_person;
            $query->where(function ($q) use ($respIds) {
                $q->whereIn('leads.user_id', $respIds)
                    ->orWhereHas('users', function ($subQ) use ($respIds) {
                        $subQ->whereIn('users.id', $respIds);
                    });
            });
        }
        if ($request->has('source_id') && !empty($request->source_id)) {
            $query->where(function ($q) use ($request) {
                foreach ((array) $request->source_id as $source) {
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
            $query->whereIn('leads.created_by', (array) $request->created_by);
        }
        if ($request->has('modified_by') && !empty($request->modified_by)) {
            $query->whereIn('leads.updated_by', (array) $request->modified_by);
        }
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $user = Auth::user();
            $searchFields = !empty($user->search_settings) ? $user->search_settings : ['name', 'subject'];

            $query->where(function ($q) use ($search, $searchFields) {
                foreach ($searchFields as $field) {
                    if (str_starts_with($field, 'custom_')) {
                        $customFieldId = str_replace('custom_', '', $field);
                        $q->orWhereHas('customFieldValues', function ($subQ) use ($customFieldId, $search) {
                            $subQ->where('field_id', $customFieldId)
                                ->where('value', 'like', "%$search%");
                        });
                    } else {
                        // System fields (name, subject, email, phone, etc.)
                        $q->orWhere('leads.' . $field, 'like', "%$search%");
                    }
                }
            });
        }
        // Custom Fields Filter
        if ($request->has('custom_fields') && !empty($request->custom_fields)) {
            $customFields = is_array($request->custom_fields) ? $request->custom_fields : [];
            foreach ($customFields as $fieldId => $value) {
                if (!empty($value)) {
                    $query->whereHas('customFieldValues', function ($q) use ($fieldId, $value) {
                        $q->where('field_id', $fieldId)
                            ->where('value', 'like', "%$value%");
                    });
                }
            }
        }

        // Department & Team Filters (HRM Integration)
        if (module_is_active('Hrm')) {
            $departmentIdsToFilter = [];

            if ($request->has('department_id') && !empty($request->department_id)) {
                $departmentIdsToFilter = array_merge($departmentIdsToFilter, (array) $request->department_id);
            }

            if ($request->has('team_id') && !empty($request->team_id)) {
                $departmentIdsToFilter = array_merge($departmentIdsToFilter, (array) $request->team_id);
            }

            if (!empty($departmentIdsToFilter)) {
                $employeeUserIds = \Workdo\Hrm\Entities\Employee::whereIn('department_id', $departmentIdsToFilter)
                    ->where('workspace', getActiveWorkSpace())
                    ->pluck('user_id')
                    ->toArray();

                $query->where(function ($q) use ($employeeUserIds) {
                    $q->whereIn('leads.user_id', $employeeUserIds)
                        ->orWhereHas('users', function ($subQ) use ($employeeUserIds) {
                            $subQ->whereIn('users.id', $employeeUserIds);
                        });
                });
            }
        }

        return $query;
    }

    public function showConvertToDeal($id)
    {
        $lead = Lead::findOrFail($id);
        if ($lead && $lead->isAccessible()) {
            $creatorId = creatorId();
            $exist_client = User::where('type', '=', 'client')->where('email', '=', $lead->email)->where('created_by', '=', $creatorId)->first();
            $clients = User::where('type', '=', 'client')->where('created_by', '=', $creatorId)->get();

            return view('lead::leads.convert', compact('lead', 'exist_client', 'clients'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function convertToDeal($id, Request $request)
    {
        $lead = Lead::findOrFail($id);
        if ($lead && $lead->isAccessible()) {
            $usr = Auth::user();
            $creatorId = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            if ($request->client_check == 'exist') {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'clients' => 'required|email|exists:users,email',
                        'price' => 'numeric|min:0',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $client = User::where('type', '=', 'client')->where('email', '=', $request->clients)->where('created_by', '=', $creatorId)->first();

                if (empty($client)) {
                    return redirect()->back()->with('error', 'The client is not available now.');
                }
            } else {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'client_name' => 'required|string|max:255',
                        'client_email' => 'required|email|unique:users,email',
                        'client_password' => 'required',
                        'price' => 'min:0',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $role = Role::where('name', 'client')->where('created_by', '=', $creatorId)->first();
                $client = User::create(
                    [
                        'name' => $request->client_name,
                        'email' => $request->client_email,
                        'password' => \Hash::make($request->client_password),
                        'email_verified_at' => date('Y-m-d h:i:s'),
                        'type' => 'client',
                        'lang' => 'en',
                        'created_by' => $creatorId,
                        'workspace_id' => $getActiveWorkSpace,
                        'active_workspace' => $getActiveWorkSpace,
                    ]
                );
                $client->addRole($role);

                $cArr = [
                    'email' => $request->client_email,
                    'password' => $request->client_password,
                ];

                // Send Email to client if they are new created.
                EmailTemplate::sendEmailTemplate('New User', [$client->id => $client->email], $cArr);
            }

            // Create Deal
            $stage = \Workdo\Lead\Entities\DealStage::where('pipeline_id', '=', $lead->pipeline_id)->first();
            if (empty($stage)) {
                return redirect()->back()->with('error', __('Please create stage for this pipeline.'));
            }

            $deal = new \Workdo\Lead\Entities\Deal();
            $deal->name = $request->name;
            $deal->price = empty($request->price) ? 0 : $request->price;
            $deal->pipeline_id = $lead->pipeline_id;
            $deal->stage_id = $stage->id;
            $deal->sources = in_array('sources', $request->is_transfer) ? $lead->sources : '';
            $deal->products = in_array('products', $request->is_transfer) ? $lead->products : '';
            $deal->notes = in_array('notes', $request->is_transfer) ? $lead->notes : '';
            $deal->labels = $lead->labels;
            $deal->status = 'Active';
            $deal->workspace_id = $getActiveWorkSpace;
            $deal->created_by = $lead->created_by;
            $deal->save();
            // end create deal

            // Make entry in ClientDeal Table
            \Workdo\Lead\Entities\ClientDeal::create(
                [
                    'deal_id' => $deal->id,
                    'client_id' => $client->id,
                ]
            );

            $leadTasks = LeadTask::where('lead_id', '=', $lead->id)->get();

            foreach ($leadTasks as $leadTask) {
                \Workdo\Lead\Entities\DealTask::create(
                    [
                        'deal_id' => $deal->id,
                        'name' => $leadTask->name,
                        'date' => $leadTask->date,
                        'time' => $leadTask->time,
                        'priority' => $leadTask->priority,
                        'status' => $leadTask->status,
                        'workspace' => $leadTask->workspace,
                    ]
                );
            }

            // end

            if (!empty(company_setting('Deal Assigned')) && company_setting('Deal Assigned') == true) {
                $dealArr = [
                    'deal_id' => $deal->id,
                    'name' => $deal->name,
                    'updated_by' => $usr->id,
                ];

                // Send Mail
                $pipeline = Pipeline::find($lead->pipeline_id);
                $dArr = [
                    'deal_name' => $deal->name,
                    'deal_pipeline' => $pipeline->name,
                    'deal_stage' => $stage->name,
                    'deal_status' => $deal->status,
                    'deal_price' => currency_format_with_sym($deal->price),
                ];
                EmailTemplate::sendEmailTemplate('Deal Assigned', [$client->id => $client->email], $dArr);
            }
            // Make Entry in UserDeal Table
            $leadUsers = UserLead::where('lead_id', '=', $lead->id)->get();
            foreach ($leadUsers as $leadUser) {
                \Workdo\Lead\Entities\UserDeal::create(
                    [
                        'user_id' => $leadUser->user_id,
                        'deal_id' => $deal->id,
                    ]
                );
            }
            // end

            //Transfer Lead Discussion to Deal
            if (in_array('discussion', $request->is_transfer)) {
                $discussions = LeadDiscussion::where('lead_id', '=', $lead->id)->where('created_by', '=', $creatorId)->get();
                if (!empty($discussions)) {
                    foreach ($discussions as $discussion) {
                        \Workdo\Lead\Entities\DealDiscussion::create(
                            [
                                'deal_id' => $deal->id,
                                'comment' => $discussion->comment,
                                'created_by' => $discussion->created_by,
                            ]
                        );
                    }
                }
            }
            // end Transfer Discussion

            // Transfer Lead Files to Deal
            if (in_array('files', $request->is_transfer)) {
                $files = LeadFile::where('lead_id', '=', $lead->id)->get();
                if (!empty($files)) {
                    foreach ($files as $file) {
                        $location = base_path() . '/' . $file->file_path;
                        $new_location = base_path() . '/' . $file->file_path;
                        $copied = copy($location, $new_location);

                        if ($copied) {
                            \Workdo\Lead\Entities\DealFile::create(
                                [
                                    'deal_id' => $deal->id,
                                    'file_name' => $file->file_name,
                                    'file_path' => $file->file_path,
                                ]
                            );
                        }
                    }
                }
            }
            // end Transfer Files

            // Transfer Lead Calls to Deal
            if (in_array('calls', $request->is_transfer)) {
                $calls = LeadCall::where('lead_id', '=', $lead->id)->get();
                if (!empty($calls)) {
                    foreach ($calls as $call) {
                        \Workdo\Lead\Entities\DealCall::create(
                            [
                                'deal_id' => $deal->id,
                                'subject' => $call->subject,
                                'call_type' => $call->call_type,
                                'duration' => $call->duration,
                                'user_id' => $call->user_id,
                                'description' => $call->description,
                                'call_result' => $call->call_result,
                            ]
                        );
                    }
                }
            }
            //end

            // Transfer Lead Emails to Deal
            if (in_array('emails', $request->is_transfer)) {
                $emails = LeadEmail::where('lead_id', '=', $lead->id)->get();
                if (!empty($emails)) {
                    foreach ($emails as $email) {
                        \Workdo\Lead\Entities\DealEmail::create(
                            [
                                'deal_id' => $deal->id,
                                'to' => $email->to,
                                'subject' => $email->subject,
                                'description' => $email->description,
                            ]
                        );
                    }
                }
            }

            // Update is_converted field as deal_id
            $lead->is_converted = $deal->id;
            $lead->save();

            event(new LeadConvertDeal($request, $lead));

            return redirect()->back()->with('success', __('The lead has been converted into a deal successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    // Lead Calls
    public function callCreate($id)
    {
        if (Auth::user()->isAbleTo('lead call create')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $users = UserLead::where('lead_id', '=', $lead->id)->get();

                return view('lead::leads.calls', compact('lead', 'users'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function callStore($id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead call create')) {
            $usr = Auth::user();
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'subject' => 'required|string|max:255',
                        'call_type' => 'required|in:outbound,inbound',
                        'user_id' => 'required|integer|exists:users,id',
                        'duration' => [
                            'required',
                            'regex:/^\d{2}:\d{2}:\d{2}$/',
                        ],
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $leadCall = LeadCall::create(
                    [
                        'lead_id' => $lead->id,
                        'subject' => $request->subject,
                        'call_type' => $request->call_type,
                        'duration' => $request->duration,
                        'user_id' => $request->user_id,
                        'description' => $request->description,
                        'call_result' => $request->call_result,
                    ]
                );

                LeadActivityLog::create(
                    [
                        'user_id' => $usr->id,
                        'lead_id' => $lead->id,
                        'log_type' => 'Create Lead Call',
                        'remark' => json_encode(['title' => 'Create new Lead Call']),
                    ]
                );

                $leadArr = [
                    'lead_id' => $lead->id,
                    'name' => $lead->name,
                    'updated_by' => $usr->id,
                ];

                event(new LeadAddCall($request, $lead));
                $lead->touch();

                return redirect()->back()->with('success', __('The call has been created successfully.'))->with('status', 'calls');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
        }
    }

    public function callEdit($id, $call_id)
    {
        if (Auth::user()->isAbleTo('lead call edit')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $call = LeadCall::find($call_id);
                $users = UserLead::where('lead_id', '=', $lead->id)->get();

                return view('lead::leads.calls', compact('call', 'lead', 'users'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function callUpdate($id, $call_id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead call edit')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'subject' => 'required|string|max:255',
                        'call_type' => 'required|in:outbound,inbound',
                        'user_id' => 'required|integer|exists:users,id',
                        'duration' => [
                            'required',
                            'regex:/^\d{2}:\d{2}:\d{2}$/',
                        ],
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $call = LeadCall::find($call_id);

                $call->update(
                    [
                        'subject' => $request->subject,
                        'call_type' => $request->call_type,
                        'duration' => $request->duration,
                        'user_id' => $request->user_id,
                        'description' => $request->description,
                        'call_result' => $request->call_result,
                    ]
                );

                event(new LeadUpdateCall($request, $lead));

                return redirect()->back()->with('success', __('The call details are updated successfully.'))->with('status', 'calls');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
        }
    }

    public function callDestroy($id, $call_id)
    {
        if (Auth::user()->isAbleTo('lead call delete')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $task = LeadCall::find($call_id);
                $task->delete();

                event(new DestroyLeadCall($lead));

                return redirect()->back()->with('success', __('The call has been deleted.'))->with('status', 'calls');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
        }
    }

    public function reminderCreate($id)
    {
        if (Auth::user()->isAbleTo('lead manage')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $types = Reminder::$types;
                $users = User::where('created_by', '=', creatorId())->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
                return view('lead::leads.reminders', compact('lead', 'types', 'users'));
            }
        }
        return response()->json(['error' => __('Permission Denied.')], 401);
    }

    public function reminderStore($id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead manage')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {

                // Team Scope Validation
                $usr = \Auth::user();
                if ($usr->type == 'company' || $usr->type == 'client' || $usr->can('crm manage')) {
                    if ($request->user_id && $request->user_id != $usr->id) {
                        $accessibleUserIds = $usr->getAccessibleUserIds();
                        if (!in_array($request->user_id, $accessibleUserIds)) {
                            return redirect()->back()->with('error', __('You can only assign to your team members.'));
                        }
                    }
                } else {
                    $request->merge(['user_id' => $usr->id]);
                }
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'title' => 'required|string|max:255',
                        'remind_at' => 'required|date_format:Y-m-d\TH:i',
                        'user_id' => 'required|exists:users,id',
                        'type' => 'required|in:call,message,follow_up',
                    ]
                );

                if ($validator->fails()) {
                    return redirect()->back()->with('error', $validator->errors()->first());
                }

                Reminder::create([
                    'user_id' => $request->user_id,
                    'remindable_id' => $lead->id,
                    'remindable_type' => Lead::class,
                    'title' => $request->title,
                    'description' => $request->description,
                    'remind_at' => $request->remind_at,
                    'type' => $request->type,
                    'workspace_id' => getActiveWorkSpace(),
                    'created_by' => creatorId(),
                ]);

                // Create Notification
                if ($request->user_id != \Auth::user()->id) {
                    \App\Models\UserNotification::create([
                        'user_id' => $request->user_id,
                        'type' => 'reminder',
                        'data' => json_encode(['message' => 'New Reminder: ' . $request->title, 'id' => $lead->id]),
                        'is_read' => 0,
                        'workspace_id' => getActiveWorkSpace()
                    ]);
                }

                $lead->touch();

                return redirect()->back()->with('success', __('Reminder created successfully.'))->with('status', 'reminders');
            }
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function reminderEdit($id, $reminder_id)
    {
        if (Auth::user()->isAbleTo('lead manage')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $reminder = Reminder::find($reminder_id);
                $types = Reminder::$types;
                $users = User::where('created_by', '=', creatorId())->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
                return view('lead::leads.reminders', compact('lead', 'reminder', 'types', 'users'));
            }
        }
        return response()->json(['error' => __('Permission Denied.')], 401);
    }

    public function reminderUpdate($id, $reminder_id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead manage')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'title' => 'required|string|max:255',
                        'remind_at' => 'required|date_format:Y-m-d\TH:i',
                        'user_id' => 'required|exists:users,id',
                        'type' => 'required|in:call,message,follow_up',
                    ]
                );

                if ($validator->fails()) {
                    return redirect()->back()->with('error', $validator->errors()->first());
                }

                $reminder = Reminder::find($reminder_id);
                $reminder->update([
                    'user_id' => $request->user_id,
                    'title' => $request->title,
                    'description' => $request->description,
                    'remind_at' => $request->remind_at,
                    'type' => $request->type,
                ]);

                return redirect()->back()->with('success', __('Reminder updated successfully.'))->with('status', 'reminders');
            }
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function reminderDestroy($id, $reminder_id)
    {
        if (Auth::user()->isAbleTo('lead manage')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $reminder = Reminder::find($reminder_id);
                $reminder->delete();
                return redirect()->back()->with('success', __('Reminder deleted successfully.'))->with('status', 'reminders');
            }
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    // Lead email
    public function emailCreate($id)
    {
        if (Auth::user()->isAbleTo('lead email create')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                return view('lead::leads.emails', compact('lead'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function emailStore($id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead email create')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'to' => 'required|email|max:255',
                        'subject' => 'required|string|max:255',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $leadEmail = LeadEmail::create(
                    [
                        'lead_id' => $lead->id,
                        'to' => $request->to,
                        'subject' => $request->subject,
                        'description' => $request->description,
                    ]
                );

                LeadActivityLog::create(
                    [
                        'user_id' => Auth::user()->id,
                        'lead_id' => $lead->id,
                        'log_type' => 'Create Lead Email',
                        'remark' => json_encode(['title' => 'Create new Deal Email']),
                    ]
                );

                event(new LeadAddEmail($request, $lead));
                $lead->touch();

                if (!empty(company_setting('Lead Emails')) && company_setting('Lead Emails') == true) {
                    $lead_users[] = $request->to;
                    $lArr = [
                        'lead_name' => $lead->name,
                        'lead_email_subject' => $request->subject,
                        'lead_email_description' => $request->description,
                    ];

                    // Send Email
                    $resp = EmailTemplate::sendEmailTemplate('Lead Emails', $lead_users, $lArr);
                }

                return redirect()->back()->with('success', __('The email has been created successfully.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'emails');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'emails');
        }
    }

    public function fileImportExport()
    {
        if (Auth::user()->isAbleTo('lead import')) {
            $user = Auth::user();
            $creatorId = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            if ($user->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->where('id', '=', $user->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
            }
            if (!empty($pipeline)) {
                $stage = LeadStage::where('pipeline_id', '=', $pipeline->id)->where('workspace_id', $getActiveWorkSpace)->first();
                if (empty($stage)) {
                    return response()->json(['error' => __('Please create stage for this pipeline.')], 401);
                }
            } else {
                return response()->json(['error' => __('Please create pipeline.')], 401);
            }
            return view('lead::leads.import');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function fileImport(Request $request)
    {
        if (Auth::user()->isAbleTo('lead import')) {

            $error = '';

            $html = '';

            if ($request->hasFile('file') && $request->file('file')->getClientOriginalName() != '') {
                $file = $request->file('file');
                $file_array = explode(".", $file->getClientOriginalName());

                $extension = end($file_array);
                if ($extension == 'csv') {
                    $fileName = 'import_leads_' . Auth::user()->id . '_' . time() . '.csv';
                    $filePath = storage_path('framework/cache/' . $fileName);
                    $file->move(storage_path('framework/cache/'), $fileName);

                    $file_data = fopen($filePath, 'r');
                    $file_header = fgetcsv($file_data);
                    fclose($file_data);

                    session()->put('import_file_path', $filePath);
                    session()->put('file_header', $this->cleanUtf8($file_header));
                    // For progress bar calculation, we need total rows. Let's count quickly and safely.
                    $total_rows = 0;
                    $handle = fopen($filePath, "r");
                    if ($handle) {
                        while (!feof($handle)) {
                            $line = fgets($handle);
                            if ($line !== false) {
                                $total_rows++;
                            }
                        }
                        fclose($handle);
                        $total_rows = max(0, $total_rows - 1); // Subtract 1 for header
                    }
                    session()->put('import_total_rows', $total_rows);
                    session()->save();
                } else {
                    $error = 'Only <b>.csv</b> file allowed';
                }
            } else {

                $error = __('Please select CSV file');
            }
            $output = array(
                'error' => $error,
                'output' => $html,
            );

            return response()->json($output);
        } else {
            return redirect()->back()->with('error', __('permission Denied'));
        }
    }

    public function fileImportModal()
    {
        if (Auth::user()->isAbleTo('lead import')) {
            if (Auth::user()->type == "company") {
                $users = User::where('created_by', '=', creatorId())->where('type', '!=', 'client')->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
            } else {
                $users = User::where('id', '=', Auth::user()->id)->where('type', '!=', 'client')->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
            }

            $pipelines = Pipeline::where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
            $sources = \Workdo\Lead\Entities\Source::where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
            $file_data = [];
            $filePath = session()->get('import_file_path') ?? null;
            if ($filePath && file_exists($filePath)) {
                $file_handle = fopen($filePath, 'r');
                fgetcsv($file_handle); // Skip header
                $lines = 0;
                while (($row = fgetcsv($file_handle)) !== false && $lines < 10) {
                    $file_data[] = $row;
                    $lines++;
                }
                fclose($file_handle);
            }
            $file_header = session()->get('file_header') ?? [];

            return view('lead::leads.import_modal', compact('users', 'pipelines', 'file_data', 'file_header', 'sources'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function getStages(Request $request)
    {
        $stages = LeadStage::where('pipeline_id', '=', $request->pipeline_id)->get()->pluck('name', 'id');

        return response()->json($stages);
    }

    public function leadImportdata(Request $request)
    {
        try {
            if (Auth::user()->isAbleTo('lead import')) {
                $creatorId = creatorId();
                $getActiveWorkSpace = getActiveWorkSpace();
                $filePath = session()->get('import_file_path') ?? null;
                $file_header = session()->get('file_header') ?? [];
                $total_items = session()->get('import_total_rows') ?? 0;

                if (!$filePath || !file_exists($filePath)) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Import file not found or expired. Please re-upload.'),
                    ]);
                }

                $is_chunk = $request->input('is_chunk', false);
                $chunk_index = $request->input('chunk_index', 0);
                $chunk_size = $request->input('chunk_size', 50);

                $process_data = [];
                $file_handle = fopen($filePath, 'r');
                fgetcsv($file_handle); // Skip header

                $current_row = 0;
                while (($row = fgetcsv($file_handle)) !== false) {
                    if ($current_row >= $chunk_index && $current_row < ($chunk_index + $chunk_size)) {
                        $temp_row = [];
                        foreach ($file_header as $i => $header) {
                            $temp_row[$i] = $row[$i] ?? '';
                        }
                        $process_data[] = $temp_row;
                    }
                    $current_row++;
                    if ($current_row >= ($chunk_index + $chunk_size))
                        break;
                }
                fclose($file_handle);

                // Initialize or retrieve error HTML from session
                if (!$is_chunk || $chunk_index == 0) {
                    session()->put('import_error_html', '<h3 class="text-danger text-center">Below data is not inserted</h3></br>
                    <table class="table table-bordered"><tr>
                        <th>' . __('Subject') . '</th>
                        <th>' . __('Name') . '</th>
                        <th>' . __('Email') . '</th>
                        <th>' . __('Phone') . '</th>
                    </tr>');
                    session()->put('import_error_flag', 0);
                    session()->put('duplicate_leads', []); // Also reset duplicates on first chunk
                }

                foreach ($process_data as $validationKey => $value) {
                    $validator = \Validator::make([
                        'name' => $value[$request->name] ?? null,
                        'email' => $value[$request->email] ?? null,
                        'phone' => $value[$request->phone] ?? null,
                    ], [
                        'name' => 'nullable|string|max:255',
                        'email' => 'nullable|email|max:255',
                        'phone' => 'required'
                    ]);

                    if ($validator->fails()) {
                        return response()->json([
                            'success' => false,
                            'message' => $validator->errors()->first() . ' at row ' . ($chunk_index + $validationKey + 1),
                        ]);
                    }
                }

                $pipeline = null;
                if ($request->has('global_pipeline') && !empty($request->global_pipeline)) {
                    $pipeline = Pipeline::where('id', $request->global_pipeline)
                        ->where('workspace_id', $getActiveWorkSpace)
                        ->first();
                }

                if (empty($pipeline)) {
                    $user = Auth::user();
                    if ($user->default_pipeline) {
                        $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->where('id', '=', $user->default_pipeline)->first();
                    }
                    if (empty($pipeline)) {
                        $pipeline = Pipeline::where('created_by', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                    }
                }

                if (empty($pipeline)) {
                    return response()->json(['success' => false, 'message' => __('Please create pipeline.')]);
                }

                $stage = null;
                if ($request->has('global_stage') && !empty($request->global_stage)) {
                    $stage = LeadStage::where('id', $request->global_stage)
                        ->where('pipeline_id', $pipeline->id)
                        ->where('workspace_id', $getActiveWorkSpace)
                        ->first();
                }

                if (empty($stage)) {
                    $stage = LeadStage::where('pipeline_id', $pipeline->id)->where('workspace_id', $getActiveWorkSpace)->orderBy('order')->first();
                }

                if (empty($stage)) {
                    return response()->json(['success' => false, 'message' => __('Please create stage for this pipeline.')]);
                }

                $duplicate_count = count(session()->get('duplicate_leads') ?? []);
                $duplicate_leads_in_chunk = [];

                // Performance Optimization: Pre-fetch all emails and phones for duplicate check
                $emails_in_chunk = [];
                $phones_in_chunk = [];
                foreach ($process_data as $row) {
                    if (isset($request->email) && isset($row[$request->email]) && !empty($row[$request->email])) {
                        $emails_in_chunk[] = $row[$request->email];
                    }
                    if (isset($request->phone) && isset($row[$request->phone]) && !empty($row[$request->phone])) {
                        $phones_in_chunk[] = $row[$request->phone];
                    }
                }

                $existing_leads = Lead::where('workspace_id', $getActiveWorkSpace)
                    ->where(function ($query) use ($emails_in_chunk, $phones_in_chunk) {
                        if (!empty($emails_in_chunk)) {
                            $query->orWhereIn('email', $emails_in_chunk);
                        }
                        if (!empty($phones_in_chunk)) {
                            $query->orWhereIn('phone', $phones_in_chunk);
                        }
                    })->get()->keyBy(function ($item) {
                        return ($item->email ?? '') . '|' . ($item->phone ?? '');
                    });

                $existing_emails = $existing_leads->pluck('email')->filter()->flip()->toArray();
                $existing_phones = $existing_leads->pluck('phone')->filter()->flip()->toArray();

                foreach ($process_data as $key => $row) {
                    $user_id = $creatorId;
                    if ($request->has('global_user') && !empty($request->global_user)) {
                        $user_id = $request->global_user;
                    }

                    $email = (isset($request->email) && $request->email != '' && isset($row[$request->email])) ? $row[$request->email] : '';
                    $name = (isset($request->name) && $request->name != '' && isset($row[$request->name])) ? $row[$request->name] : '';
                    $phone = (isset($request->phone) && $request->phone != '' && isset($row[$request->phone])) ? $row[$request->phone] : '';
                    $subject = 'Lead from Import ' . date('Y-m-d');

                    if (empty($name)) {
                        $name = $phone;
                    }
                    if (empty($name)) {
                        $name = $subject;
                    }

                    $is_duplicate = false;
                    if (!empty($email) && isset($existing_emails[$email])) {
                        $is_duplicate = true;
                    }
                    if (!$is_duplicate && !empty($phone) && isset($existing_phones[$phone])) {
                        $is_duplicate = true;
                    }

                    if ($is_duplicate) {
                        $dup_row = [
                            'row' => $chunk_index + $key + 1,
                            'name' => $name,
                            'email' => $email,
                            'phone' => $phone,
                            'reason' => 'Already exists in Database'
                        ];
                        $duplicate_leads = session()->get('duplicate_leads') ?? [];
                        $duplicate_leads[] = $dup_row;
                        session()->put('duplicate_leads', $duplicate_leads);
                        $duplicate_leads_in_chunk[] = $dup_row;
                        $duplicate_count++;
                        continue;
                    }

                    try {
                        $user_to_assign = $user_id;
                        if (!empty($request->user) && isset($request->user[$key])) {
                            $usr = User::find($request->user[$key]);
                            if ($usr)
                                $user_to_assign = $usr->id;
                        }

                        // Check stage permissions
                        if (!$stage->permissions()->can_edit) {
                            throw new \Exception('Aapko is stage me lead create karne ka permission nahi hai.');
                        }

                        $lead = Lead::create([
                            'subject' => $subject,
                            'name' => $name,
                            'user_id' => $user_to_assign,
                            'email' => $email,
                            'phone' => $phone,
                            'pipeline_id' => $pipeline->id,
                            'stage_id' => $stage->id,
                            'sources' => $request->global_source ?? null,
                            'created_by' => $creatorId,
                            'workspace_id' => $getActiveWorkSpace,
                            'date' => date('Y-m-d'),
                        ]);

                        $usrLeads = [$user_to_assign];
                        foreach ($usrLeads as $usrLead) {
                            UserLead::firstOrCreate(['user_id' => $usrLead, 'lead_id' => $lead->id]);
                        }

                        $lead->activities()->create([
                            'user_id' => Auth::user()->id,
                            'log_type' => 'Lead Imported',
                            'remark' => json_encode(['message' => __('Lead imported via CSV by ') . Auth::user()->name]),
                        ]);

                    } catch (\Exception $e) {
                        session()->put('import_error_flag', 1);
                        $error_html = session()->get('import_error_html') . '<tr><td>' . $subject . '</td><td>' . $name . '</td><td>' . (empty($email) ? '-' : $email) . '</td><td>' . (empty($phone) ? '-' : $phone) . '</td></tr>';
                        session()->put('import_error_html', $error_html);
                        \Log::error("Lead Import Creation Error: " . $e->getMessage());
                    }
                }

                $current_count = $chunk_index + count($process_data);
                $is_finished = ($current_count >= $total_items);

                if ($is_finished) {
                    $html = session()->get('import_error_html');
                    $flag = session()->get('import_error_flag');
                    session()->forget('import_error_html');
                    session()->forget('import_error_flag');
                } else {
                    $flag = 0;
                    $html = '';
                }

                if ($is_finished && !empty($filePath) && file_exists($filePath)) {
                    @unlink($filePath);
                    session()->forget('import_file_path');
                }

                $log_data = [
                    'success' => true,
                    'current' => $current_count,
                    'total' => $total_items,
                    'is_finished' => $is_finished,
                    'html' => ($is_finished && $flag == 1),
                    'response' => $is_finished ? ($flag == 1 ? $html . '</table><br />' : __('Data has been imported.')) : __('Processing...'),
                    'duplicates_count' => $duplicate_count,
                    'chunk_duplicates' => $duplicate_leads_in_chunk,
                    'latest_duplicates' => array_slice(session()->get('duplicate_leads') ?? [], -5, 5)
                ];

                return response()->json($this->cleanUtf8($log_data));

            } else {
                return response()->json(['success' => false, 'message' => __('Permission Denied')]);
            }
        } catch (\Exception $e) {
            \Log::error("CRITICAL LEAD IMPORT ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Critical Error: ' . $this->cleanUtf8($e->getMessage())
            ], 500);
        }
    }

    private function cleanUtf8($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->cleanUtf8($value);
            }
        } elseif (is_string($data)) {
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        }
        return $data;
    }


    public function downloadDuplicateLeads()
    {
        session_start();
        $duplicates = $_SESSION['duplicate_leads'] ?? [];

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=duplicate_leads.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($duplicates) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Row Number', 'Name', 'Email', 'Phone', 'Reason']);

            foreach ($duplicates as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function taskCreate($id)
    {
        if (Auth::user()->isAbleTo('lead task create')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $priorities = LeadTask::$priorities;
                $status = LeadTask::$status;
                $users = User::where('created_by', '=', creatorId())->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
                return view('lead::leads.tasks', compact('lead', 'priorities', 'status', 'users'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function taskStore($id, Request $request)
    {
        $usr = Auth::user();
        if ($usr->isAbleTo('lead task create')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {

                // Team Scope Validation
                if ($usr->type == 'company' || $usr->type == 'client' || $usr->can('crm manage')) {
                    if ($request->user_id && $request->user_id != $usr->id) {
                        $accessibleUserIds = $usr->getAccessibleUserIds();
                        if (!in_array($request->user_id, $accessibleUserIds)) {
                            return redirect()->back()->with('error', __('You can only assign to your team members.'));
                        }
                    }
                } else {
                    $request->merge(['user_id' => $usr->id]);
                }
                $getActiveWorkSpace = getActiveWorkSpace();
                $lead_users = $lead->users->pluck('id')->toArray();
                $usrs = User::whereIN('id', $lead_users)->get()->pluck('email', 'id')->toArray();

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required|string|max:255',
                        'date' => 'required|date',
                        'time' => 'required|date_format:H:i',
                        'priority' => 'required|in:1,2,3',
                        'status' => 'required|in:pending,in_progress,done,overdue',
                        'user_id' => 'required|exists:users,id',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $leadTask = LeadTask::create(
                    [
                        'lead_id' => $lead->id,
                        'user_id' => $request->user_id,
                        'name' => $request->name,
                        'description' => $request->description,
                        'date' => $request->date,
                        'time' => date('H:i:s', strtotime($request->date . ' ' . $request->time)),
                        'priority' => $request->priority,
                        'status' => $request->status,
                        'workspace' => $getActiveWorkSpace,
                    ]
                );

                LeadActivityLog::create(
                    [
                        'user_id' => $usr->id,
                        'lead_id' => $lead->id,
                        'log_type' => 'Create Task',
                        'remark' => json_encode(['title' => $leadTask->name]),
                    ]
                );

                $taskArr = [
                    'lead_id' => $lead->id,
                    'name' => $lead->name,
                    'updated_by' => $usr->id,
                ];

                $lead->touch();
                if (!empty(company_setting('New Task')) && company_setting('New Task') == true) {
                    $tArr = [
                        'lead_name' => $lead->name,
                        'lead_pipeline' => $lead->pipeline->name,
                        'lead_stage' => $lead->stage?->name ?? '-',
                        'lead_status' => $lead->status,
                        'lead_price' => currency_format_with_sym($lead->price),
                        'task_name' => $leadTask->name,
                        'task_priority' => LeadTask::$priorities[$leadTask->priority],
                        'task_status' => LeadTask::$status[$leadTask->status],
                    ];

                    // Send Email
                    $resp = EmailTemplate::sendEmailTemplate('New Task', $usrs, $tArr);
                }

                event(new CreateLeadTask($request, $leadTask, $lead));
                return redirect()->back()->with('success', __('The task has been created successfully.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
        }
    }

    public function taskEdit($id, $task_id)
    {
        if (Auth::user()->isAbleTo('lead task edit')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $priorities = LeadTask::$priorities;
                $status = LeadTask::$status;
                $task = LeadTask::find($task_id);
                $users = User::where('created_by', '=', creatorId())->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');

                return view('lead::leads.tasks', compact('task', 'lead', 'priorities', 'status', 'users'));
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function taskUpdate($id, $task_id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead task edit')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required|string|max:255',
                        'date' => 'required|date',
                        'time' => 'required',
                        'priority' => 'required|in:1,2,3',
                        'status' => 'required|in:pending,in_progress,done,overdue',
                        'user_id' => 'required|exists:users,id',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $task = LeadTask::find($task_id);

                $task->update(
                    [
                        'name' => $request->name,
                        'description' => $request->description,
                        'user_id' => $request->user_id,
                        'date' => $request->date,
                        'time' => date('H:i:s', strtotime($request->date . ' ' . $request->time)),
                        'priority' => $request->priority,
                        'status' => $request->status,
                    ]
                );

                event(new \Workdo\Lead\Events\UpdateLeadTask($request, $lead, $task));

                $lead->touch();

                return redirect()->back()->with('success', __('The task details are updated successfully.'))->with('status', 'tasks');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
        }
    }

    public function taskUpdateStatus($id, $task_id, Request $request)
    {
        if (Auth::user()->isAbleTo('lead task edit')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'status' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return response()->json(
                        [
                            'is_success' => false,
                            'error' => $messages->first(),
                        ],
                        401
                    );
                }

                $task = LeadTask::find($task_id);
                // Status is either 'done' or 'pending'/'overdue'
                if ($request->status == 'done') {
                    // Task was done (checked), clicking it means make it pending
                    $task->status = 'pending';
                    if ($task->date && strtotime($task->date) < strtotime(date('Y-m-d'))) {
                        $task->status = 'overdue';
                    }
                } else {
                    // Task was not done, clicking it means make it done
                    $task->status = 'done';
                }
                $task->save();
                $lead->touch();

                event(new StatusChangeLeadTask($request, $lead, $task));

                return response()->json(
                    [
                        'is_success' => true,
                        'success' => __('The task status has been changes successfully.'),
                        'status' => $task->status,
                        'status_label' => __(LeadTask::$status[$task->status]),
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function taskDestroy($id, $task_id)
    {
        if (Auth::user()->isAbleTo('lead task delete')) {
            $lead = Lead::find($id);
            if ($lead->isAccessible()) {
                $task = LeadTask::find($task_id);
                if ($task) {
                    $task->delete();

                    event(new DestroyLeadTask($task));

                    return redirect()->back()->with('success', __('Lead task successfully deleted.'));
                } else {
                    return redirect()->back()->with('error', __('Task not found.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function saveFilter(Request $request)
    {
        $usr = Auth::user();
        if ($usr->isAbleTo('lead manage')) {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'filters' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
            }

            $filter = \Workdo\Lead\Entities\LeadFilter::create([
                'name' => $request->name,
                'user_id' => $usr->id,
                'filters' => $request->filters,
                'workspace_id' => getActiveWorkSpace(),
            ]);

            return response()->json(['success' => true, 'message' => __('Filter saved successfully.'), 'data' => $filter]);
        }
        return response()->json(['success' => false, 'message' => __('Permission Denied.')], 403);
    }

    public function deleteFilter($id)
    {
        $usr = Auth::user();
        $filter = \Workdo\Lead\Entities\LeadFilter::where('id', $id)
            ->where('user_id', $usr->id)
            ->where('workspace_id', getActiveWorkSpace())
            ->first();

        if ($filter) {
            $filter->delete();
            return response()->json(['success' => true, 'message' => __('Filter deleted successfully.')]);
        }
        return response()->json(['success' => false, 'message' => __('Filter not found.')], 404);
    }

    public function bulkAction(Request $request)
    {
        $usr = Auth::user();
        if ($usr->isAbleTo('lead manage')) {
            $ids = $request->ids;
            $action = $request->action;
            $value = $request->value;

            $ids = $request->ids;
            if (is_string($ids) && $ids !== 'all') {
                $ids = explode(',', $ids);
            }

            if ($request->action == 'get_ids') {
                $dataTable = new \Workdo\Lead\DataTables\LeadDataTable();
                $allIds = $dataTable->query(new Lead())->pluck('leads.id')->toArray();
                return response()->json(['success' => true, 'ids' => $allIds]);
            }

            if ($ids === 'all') {
                return response()->json(['success' => false, 'message' => __('Selecting all records via this legacy endpoint is disabled to prevent memory exhaustion.')]);
            } else {
                if (!is_array($ids))
                    $ids = (array) $ids;
                $leads = Lead::with(['owner', 'users', 'pipeline', 'stage', 'createdBy', 'updatedBy', 'employee.department', 'customFieldValues'])
                    ->whereIn('id', $ids)
                    ->where('workspace_id', getActiveWorkSpace())
                    ->get();
            }

            // Build export column config
            $exportColumns = $request->input('export_columns', []);
            if (!is_array($exportColumns)) {
                $exportColumns = (array) $exportColumns;
            }
            // Default columns if none selected
            $defaultExportColumns = ['id', 'name', 'email', 'phone', 'pipeline', 'stage_id', 'user_id', 'created_at'];
            if (empty($exportColumns)) {
                $exportColumns = $defaultExportColumns;
            }

            // Pre-load custom fields for this workspace (for export)
            $customFieldsMap = [];
            if ($action == 'export') {
                $customFieldIds = [];
                foreach ($exportColumns as $col) {
                    if (strpos($col, 'custom_') === 0) {
                        $customFieldIds[] = (int) str_replace('custom_', '', $col);
                    }
                }
                if (!empty($customFieldIds)) {
                    $cfObjects = \Workdo\Lead\Entities\LeadCustomField::whereIn('id', $customFieldIds)
                        ->where('workspace_id', getActiveWorkSpace())
                        ->get()
                        ->keyBy('id');
                    foreach ($cfObjects as $id => $cf) {
                        $customFieldsMap[$id] = $cf->name;
                    }
                }
            }

            // Column label map for headers
            $columnLabelMap = [
                'id'            => __('Lead ID'),
                'name'          => __('Name'),
                'email'         => __('Email'),
                'phone'         => __('Phone'),
                'pipeline'      => __('Pipeline'),
                'stage_id'      => __('Stage'),
                'user_id'       => __('Responsible Person'),
                'created_at'    => __('Created At'),
                'updated_at'    => __('Modified At'),
                'subject'       => __('Subject'),
                'follow_up_date'=> __('Follow Up Date'),
                'sources'       => __('Sources'),
                'created_by'    => __('Created By'),
                'updated_by'    => __('Modified By'),
                'team'          => __('Team / Department'),
            ];

            $exportFile = null;
            if ($action == 'export' && $usr->isAbleTo('lead manage') && !empty($request->export_id)) {
                $fileName = $request->export_id . '.csv';
                $exportDir = storage_path('app/public/exports');
                if (!file_exists($exportDir)) {
                    mkdir($exportDir, 0777, true);
                }
                $filePath = $exportDir . '/' . $fileName;
                $isFirstChunk = !file_exists($filePath);
                $exportFile = fopen($filePath, 'a');

                if ($isFirstChunk) {
                    fputs($exportFile, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM
                    // Build dynamic header
                    $headers = [];
                    foreach ($exportColumns as $col) {
                        if (strpos($col, 'custom_') === 0) {
                            $cfId = (int) str_replace('custom_', '', $col);
                            $headers[] = $customFieldsMap[$cfId] ?? $col;
                        } else {
                            $headers[] = $columnLabelMap[$col] ?? $col;
                        }
                    }
                    fputcsv($exportFile, $headers);
                }
            }

            // Pre-load lead with customFieldValues if needed
            $needsCustomFields = !empty($customFieldsMap);

            // Pre-load all Sources for name resolution
            $allSources = \Workdo\Lead\Entities\Source::where('workspace_id', getActiveWorkSpace())
                ->get()
                ->keyBy('id');

            $successDetails = [];
            $skippedDetails = [];
            $skippedCount = 0;

            // Track any IDs not found in the workspace or database
            $foundLeadIds = $leads->pluck('id')->toArray();
            $missingIds = array_diff($ids, $foundLeadIds);
            foreach ($missingIds as $missingId) {
                $skippedCount++;
                $skippedDetails[] = [
                    'id' => $missingId,
                    'name' => __('Unknown / Not Found'),
                    'stage' => '-',
                    'reason' => __('Not Found: Lead does not exist or belongs to another workspace.')
                ];
            }

            foreach ($leads as $lead) {
                // Ensure the user has access to these leads
                if (!$lead->isAccessible()) {
                    $skippedCount++;
                    $skippedDetails[] = [
                        'id' => $lead->id,
                        'name' => $lead->name,
                        'stage' => $lead->stage?->name ?? __('Unknown'),
                        'reason' => __('Access Denied: You do not have permission to view this lead or it belongs to another workspace.')
                    ];
                    continue;
                }

                if ($action == 'delete') {
                    if ($usr->isAbleTo('lead delete')) {
                        $lead->delete();
                    }
                } elseif ($action == 'change_stage') {
                    if ($usr->isAbleTo('lead edit')) {
                        if (!$lead->stagePermissions()->can_edit) {
                            $skippedCount++;
                            $skippedDetails[] = [
                                'id' => $lead->id,
                                'name' => $lead->name,
                                'stage' => $lead->stage?->name ?? __('Unknown'),
                                'reason' => __('Stage Locked: You do not have permission to edit leads in this stage.')
                            ];
                            continue;
                        }
                        // During bulk operations, skip leads with missing custom fields instead of blocking
                        $customFieldErrors = $this->validateLeadCustomFields($lead, $value);
                        if (!empty($customFieldErrors)) {
                            $skippedCount++;
                            $skippedDetails[] = [
                                'id' => $lead->id,
                                'name' => $lead->name,
                                'stage' => $lead->stage?->name ?? __('Unknown'),
                                'reason' => __('Missing required custom fields: ') . implode(', ', $customFieldErrors)
                            ];
                            continue; // Skip this lead, process the rest
                        }
                        $oldStage = LeadStage::find($lead->stage_id);
                        $newStage = LeadStage::find($value);
                        $lead->stage_id = $value;
                        $lead->save();

                        // Activity log for bulk stage change
                        $lead->activities()->create([
                            'user_id' => $usr->id,
                            'log_type' => 'Move',
                            'remark' => json_encode([
                                'title' => $lead->name,
                                'old_status' => $oldStage ? $oldStage->name : 'Unknown',
                                'new_status' => $newStage ? $newStage->name : 'Unknown',
                                'old_stage_id' => $oldStage ? $oldStage->id : null,
                                'new_stage_id' => $newStage ? $newStage->id : null,
                            ]),
                        ]);

                        // Notify department head
                        $this->notifyDepartmentHead(
                            $lead,
                            __('Lead "') . $lead->name . __('" stage changed to ') . ($newStage ? $newStage->name : 'Unknown')
                        );

                        $successDetails[] = [
                            'id' => $lead->id,
                            'name' => $lead->name,
                            'stage' => $oldStage ? $oldStage->name : __('Unknown'),
                            'target_stage' => $newStage ? $newStage->name : __('Unknown')
                        ];
                    }
                } elseif ($action == 'export') {
                    if ($exportFile) {
                        $ownerName = $lead->owner ? $lead->owner->name : ($lead->users->first() ? $lead->users->first()->name : '');

                        // Build dynamic row
                        $row = [];
                        foreach ($exportColumns as $col) {
                            if (strpos($col, 'custom_') === 0) {
                                $cfId = (int) str_replace('custom_', '', $col);
                                if ($needsCustomFields) {
                                    $cfVal = $lead->customFieldValues->firstWhere('field_id', $cfId);
                                    $row[] = $cfVal ? $cfVal->value : '';
                                } else {
                                    $row[] = '';
                                }
                            } else {
                                switch ($col) {
                                    case 'id':            $row[] = $lead->id; break;
                                    case 'name':          $row[] = $lead->name; break;
                                    case 'email':         $row[] = $lead->email; break;
                                    case 'phone':         $row[] = $lead->phone; break;
                                    case 'pipeline':      $row[] = $lead->pipeline ? $lead->pipeline->name : ''; break;
                                    case 'stage_id':      $row[] = $lead->stage ? $lead->stage->name : ''; break;
                                    case 'user_id':       $row[] = $ownerName; break;
                                    case 'created_at':    $row[] = $lead->created_at ? $lead->created_at->format('Y-m-d H:i:s') : ''; break;
                                    case 'updated_at':    $row[] = $lead->updated_at ? $lead->updated_at->format('Y-m-d H:i:s') : ''; break;
                                    case 'subject':       $row[] = $lead->subject ?? ''; break;
                                    case 'follow_up_date':$row[] = $lead->follow_up_date ? \Carbon\Carbon::parse($lead->follow_up_date)->format('Y-m-d') : ''; break;
                                    case 'sources':
                                        if ($lead->sources) {
                                            $sourceIds = explode(',', $lead->sources);
                                            $sourceNames = array_map(function($sid) use ($allSources) {
                                                $s = $allSources->get((int)trim($sid));
                                                return $s ? $s->name : trim($sid);
                                            }, $sourceIds);
                                            $row[] = implode(', ', $sourceNames);
                                        } else {
                                            $row[] = '';
                                        }
                                        break;
                                    case 'created_by':    $row[] = $lead->createdBy ? $lead->createdBy->name : ''; break;
                                    case 'updated_by':    $row[] = $lead->updatedBy ? $lead->updatedBy->name : ''; break;
                                    case 'team':
                                        $teamName = '';
                                        if (module_is_active('Hrm') && $lead->employee && $lead->employee->department) {
                                            $teamName = $lead->employee->department->name;
                                        }
                                        $row[] = $teamName;
                                        break;
                                    default:              $row[] = ''; break;
                                }
                            }
                        }
                        fputcsv($exportFile, $row);
                    }
                } elseif ($action == 'change_owner') {
                    if ($usr->isAbleTo('lead edit')) {
                        if (!$lead->stagePermissions()->can_edit) {
                            $skippedCount++;
                            $skippedDetails[] = [
                                'id' => $lead->id,
                                'name' => $lead->name,
                                'stage' => $lead->stage?->name ?? __('Unknown'),
                                'reason' => __('Stage Locked: You do not have permission to edit leads in this stage.')
                            ];
                            continue;
                        }
                        // Reassign leads to another user
                        $oldUserId = $lead->user_id;
                        $oldUser = $oldUserId ? User::find($oldUserId) : null;
                        $oldUserName = $oldUser ? $oldUser->name : __('Unknown');
                        $newUser = User::find($value);
                        $newUserName = $newUser ? $newUser->name : __('Unknown');
                        
                        $lead->user_id = $value;
                        $lead->save();

                        // Sync UserLead: Remove all and add new user
                        $lead->users()->sync([$value]);

                        // Activity Log for Transfer
                        $lead->activities()->create([
                            'user_id' => $usr->id,
                            'log_type' => 'Lead Transferred',
                            'remark' => json_encode([
                                'title' => 'Lead Transferred',
                                'message' => __('Lead responsibility transferred from ') . $oldUserName . __(' to ') . $newUserName . __(' by ') . $usr->name
                            ])
                        ]);

                        // Notification for Lead Transfer
                        UserNotification::create([
                            'user_id' => $value,
                            'type' => 'lead_transfer',
                            'data' => [
                                'lead_id' => $lead->id,
                                'lead_name' => $lead->name,
                                'old_user_id' => $oldUserId,
                                'transferred_by_name' => $usr->name,
                            ],
                            'workspace_id' => getActiveWorkSpace(),
                        ]);

                        if (!empty(company_setting('Lead Assigned')) && company_setting('Lead Assigned') == true) {
                            $lArr = [
                                'lead_name' => $lead->name,
                                'lead_email' => $lead->email,
                                'lead_pipeline' => $lead->pipeline->name,
                                'lead_stage' => $lead->stage?->name ?? '-',
                            ];
                            $usrEmail = User::find($value);
                            // Send Email
                            if ($usrEmail) {
                                EmailTemplate::sendEmailTemplate('Lead Assigned', [$usrEmail->id => $usrEmail->email], $lArr);
                            }
                        }

                        $successDetails[] = [
                            'id' => $lead->id,
                            'name' => $lead->name,
                            'stage' => $lead->stage?->name ?? __('Unknown'),
                            'target_owner' => $newUserName
                        ];
                    }
                }
            }

            if ($exportFile) {
                fclose($exportFile);
            }

            $message = __('Bulk action completed successfully.');
            if ($skippedCount > 0) {
                if (count($ids) == $skippedCount) {
                    $message = __('Bulk action completed, but all leads were skipped due to lack of permission or workspace mismatch.');
                } else {
                    $message = __('Bulk action completed, but ') . $skippedCount . __(' lead(s) were skipped.');
                }
            }

            $report = [
                'action' => $action,
                'success_count' => count($successDetails),
                'skipped_count' => $skippedCount,
                'success_details' => $successDetails,
                'skipped_details' => $skippedDetails
            ];

            return response()->json([
                'success' => true,
                'message' => $message,
                'report' => $report
            ]);
        }
        return response()->json(['success' => false, 'message' => __('Permission Denied.')], 403);
    }

    public function duplicateList()
    {
        // This method can return a view or data for duplicate identification
        // For now, we'll focus on the 'duplicates' filter in LeadDataTable
        return redirect()->route('leads.list', ['duplicates' => 1]);
    }

    public function bulkTaskReminderCreate(Request $request)
    {
        if (\Auth::user()->isAbleTo('lead edit')) {
            $ids = $request->ids;
            if (empty($ids)) {
                return response()->json(['error' => __('Please select at least one lead.')]);
            }
            $idsString = is_array($ids) ? implode(',', $ids) : $ids;

            $getActiveWorkSpace = getActiveWorkSpace();
            $users = User::where('created_by', creatorId())->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');

            return view('lead::leads.bulk_create', compact('ids', 'idsString', 'users'));
        }
        return response()->json(['error' => __('Permission Denied.')]);
    }

    public function bulkTaskReminderStore(Request $request)
    {
        if (\Auth::user()->isAbleTo('lead edit')) {
            $getActiveWorkSpace = getActiveWorkSpace();
            $ids = $request->ids;

            if ($ids === 'all') {
                return $request->ajax() ? response()->json(['success' => false, 'message' => __('Selecting all records via this legacy endpoint is disabled to prevent memory exhaustion.')]) : redirect()->back()->with('error', __('Bulk processing legacy error.'));
            } else {
                if (is_string($ids)) {
                    $ids = explode(',', $ids);
                }
                if (empty($ids) || (count($ids) == 1 && $ids[0] == "")) {
                    return $request->ajax() ? response()->json(['success' => false, 'message' => __('Please select at least one lead.')]) : redirect()->back()->with('error', __('Please select at least one lead.'));
                }
                $leads = Lead::whereIn('id', $ids)->where('workspace_id', $getActiveWorkSpace)->get();
            }
            $count = 0;
            $skippedCount = 0;

            if ($request->type == 'task') {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'task_subject' => 'required',
                        'task_date' => 'required',
                        'task_priority' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    return redirect()->back()->with('error', $validator->errors()->first());
                }

                foreach ($leads as $lead) {
                    if (!$lead->stagePermissions()->can_edit) {
                        $skippedCount++;
                        continue;
                    }
                    $userId = ($request->task_user_id == 'lead_owner') ? $lead->user_id : $request->task_user_id;

                    if (!$userId)
                        $userId = \Auth::user()->id; // Fallback

                    \Workdo\Lead\Entities\LeadTask::create([
                        'lead_id' => $lead->id,
                        'name' => $request->task_subject,
                        'date' => $request->task_date,
                        'time' => date('H:i'),
                        'priority' => $request->task_priority,
                        'status' => 'pending',
                        'workspace' => $getActiveWorkSpace,
                        'user_id' => $userId,
                    ]);

                    LeadActivityLog::create([
                        'user_id' => \Auth::user()->id,
                        'lead_id' => $lead->id,
                        'log_type' => 'Create Task',
                        'remark' => json_encode(['title' => $request->task_subject]),
                    ]);
                    $count++;
                }
                $message = __('Tasks created successfully for ' . $count . ' leads.');
                if ($skippedCount > 0) {
                    if ($count == 0) {
                        $message = __('Tasks could not be created. You do not have permission to edit these leads.');
                        return $request->ajax() ? response()->json(['success' => false, 'message' => $message]) : redirect()->back()->with('error', $message);
                    }
                    $message .= ' ' . __($skippedCount . ' leads were skipped due to lack of permission.');
                }
                if ($request->ajax()) {
                    return response()->json(['success' => true, 'message' => $message]);
                }
                return redirect()->back()->with('success', $message);

            } else {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'reminder_date' => 'required',
                        'reminder_description' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    return $request->ajax() ? response()->json(['success' => false, 'message' => $validator->errors()->first()]) : redirect()->back()->with('error', $validator->errors()->first());
                }

                foreach ($leads as $lead) {
                    if (!$lead->stagePermissions()->can_edit) {
                        $skippedCount++;
                        continue;
                    }
                    $userId = ($request->reminder_user_id == 'lead_owner') ? $lead->user_id : $request->reminder_user_id;
                    if (!$userId)
                        $userId = \Auth::user()->id; // Fallback

                    \Workdo\Lead\Entities\Reminder::create([
                        'created_by' => \Auth::user()->id,
                        'user_id' => $userId,
                        'title' => 'Bulk Reminder',
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
                        'remark' => json_encode(['title' => 'Bulk Reminder']),
                    ]);

                    $count++;
                }
                $message = __('Reminders created successfully for ' . $count . ' leads.');
                if ($skippedCount > 0) {
                    if ($count == 0) {
                        $message = __('Reminders could not be created. You do not have permission to edit these leads.');
                        return $request->ajax() ? response()->json(['success' => false, 'message' => $message]) : redirect()->back()->with('error', $message);
                    }
                    $message .= ' ' . __($skippedCount . ' leads were skipped due to lack of permission.');
                }
                if ($request->ajax()) {
                    return response()->json(['success' => true, 'message' => $message]);
                }
                return redirect()->back()->with('success', $message);
            }
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }
    public function kanbanBatch(Request $request)
    {
        try {
            $stage_id = $request->get('stage_id');
            if (is_array($stage_id)) {
                $stage_id = $stage_id[0];
            }

            $offset = $request->offset ?? 0;
            $limit = $request->limit ?? 50;

            $stage = LeadStage::find($stage_id);
            if (!$stage) {
                return response()->json(['success' => false, 'message' => 'Stage not found.']);
            }

            // Create a new request object with isolated parameters to avoid conflicts
            $isolatedRequest = new Request($request->all());
            $isolatedRequest->merge(['stage_id' => $stage_id]);

            $leads = $stage->lead($isolatedRequest, $limit, $offset);
            $html = '';
            $permissions = $stage->permissions();

            foreach ($leads as $lead) {
                $html .= view('lead::leads.card', compact('lead', 'permissions'))->render();
            }

            return response()->json([
                'success' => true,
                'html' => $html,
                'count' => count($leads),
                'has_more' => count($leads) == $limit
            ]);
        } catch (\Exception $e) {
            \Log::error('Kanban Batch Error for Stage ' . ($stage_id ?? 'unknown') . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => true,
                'message' => 'Error: ' . $e->getMessage(),
                'html' => '<div class="text-danger p-2 m-2" style="font-size: 11px; word-break: break-all;">Error: ' . e($e->getMessage()) . ' in ' . e(basename($e->getFile())) . ':' . $e->getLine() . '</div>',
                'count' => 0,
                'has_more' => false
            ]);
        }
    }

    private function validateLeadCustomFields(Lead $lead, $targetStageId, $inputData = [])
    {
        $getActiveWorkSpace = getActiveWorkSpace();
        $user = Auth::user();

        $targetStage = \Workdo\Lead\Entities\LeadStage::find($targetStageId);
        if (!$targetStage) {
            return [];
        }

        // Get all stages in the same pipeline that come before or are the target stage
        $relevantStageIds = \Workdo\Lead\Entities\LeadStage::where('pipeline_id', $targetStage->pipeline_id)
            ->where('workspace_id', $getActiveWorkSpace)
            ->where(function ($query) use ($targetStage) {
                $query->where('order', '<', $targetStage->order)
                      ->orWhere(function ($q) use ($targetStage) {
                          $q->where('order', '=', $targetStage->order)
                            ->where('id', '<=', $targetStage->id);
                      });
            })
            ->pluck('id')
            ->toArray();

        $fields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', $getActiveWorkSpace)
            ->where('pipeline_id', $targetStage->pipeline_id)
            ->get();
        $errors = [];

        foreach ($fields as $field) {
            $isRequired = false;

            // Check if it's required for the target stage or ANY previous stage in the sequence
            if (!empty($field->required_stages) && !empty(array_intersect($relevantStageIds, $field->required_stages))) {
                $isRequired = true;
            }

            // Check if it's visible for the target stage and user's role if it's globally required
            if (!$isRequired && $field->is_required == 1) {
                $isVisible = true;
                if (!empty($field->visible_stages) && !in_array($targetStageId, $field->visible_stages)) {
                    $isVisible = false;
                }
                if ($isVisible && !empty($field->visible_roles)) {
                    $userRoleIds = $user->roles->pluck('id')->toArray();
                    if (empty(array_intersect($userRoleIds, $field->visible_roles))) {
                        $isVisible = false;
                    }
                }
                if ($isVisible) {
                    $isRequired = true;
                }
            }

            if ($isRequired) {
                $value = $inputData['leadCustomField'][$field->id] ?? null;

                // If not in input, check DB
                if ($value === null) {
                    $value = \Workdo\Lead\Entities\LeadCustomFieldValue::where('lead_id', $lead->id)->where('field_id', $field->id)->value('value');
                }

                if ($field->type == 'file') {
                    if (empty($value) && (!isset($inputData['leadCustomField']) || !request()->hasFile("leadCustomField.{$field->id}"))) {
                        $errors[] = __($field->name . ' is required.');
                    }
                } else {
                    if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                        $errors[] = __($field->name . ' is required.');
                    }
                }
            }

            // Enforce Minimum Value if Number Field
            if ($field->type === 'number' && !empty($field->stage_min_values) && is_array($field->stage_min_values) && isset($field->stage_min_values[$targetStageId])) {
                $minVal = (float)$field->stage_min_values[$targetStageId];
                $value = $inputData['leadCustomField'][$field->id] ?? null;
                if ($value === null) {
                    $value = \Workdo\Lead\Entities\LeadCustomFieldValue::where('lead_id', $lead->id)->where('field_id', $field->id)->value('value');
                }
                if ($value === null || $value === '' || (float)$value < $minVal) {
                    $errors[] = __($field->name . ' must be at least ' . $minVal . ' for this stage.');
                }
            }
        }

        return $errors;
    }

    public function myTasks()
    {
        $user = Auth::user();
        $workspace = getActiveWorkSpace();
        $tasks = \Workdo\Lead\Entities\LeadTask::where('user_id', $user->id)
            ->where('workspace', $workspace)
            ->orderBy('date', 'asc')
            ->get();
        return view('lead::crm.my_tasks', compact('tasks'));
    }

    public function myReminders()
    {
        $user = Auth::user();
        $workspace = getActiveWorkSpace();
        $reminders = \Workdo\Lead\Entities\Reminder::where('user_id', $user->id)
            ->where('workspace_id', $workspace)
            ->orderBy('remind_at', 'asc')
            ->get();
        return view('lead::crm.my_reminders', compact('reminders'));
    }

    public function visibilitySettings()
    {
        if (Auth::user()->isAbleTo('crm manage') && (Auth::user()->type == 'super admin' || Auth::user()->type == 'company')) {
            $workspace = getActiveWorkSpace();
            $roles = Role::where('created_by', creatorId())->get();
            $pipelines = Pipeline::where('created_by', creatorId())->where('workspace_id', $workspace)->get();
            $visibilities = \Workdo\Lead\Entities\LeadFieldVisibility::where('workspace_id', $workspace)->get();

            // Standard Fields
            $fields = [
                'name' => 'Name',
                'email' => 'Email',
                'phone' => 'Phone',
                'subject' => 'Subject',
            ];

            // Add Custom Fields
            $customFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', $workspace)->get();
            foreach ($customFields as $field) {
                $fields['custom_' . $field->id] = $field->name . ' (Custom)';
            }

            return view('lead::crm.visibility_settings', compact('roles', 'pipelines', 'visibilities', 'fields'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function getStagesByPipeline(Request $request)
    {
        $pipelineId = $request->pipeline_id;
        $stages = LeadStage::where('pipeline_id', $pipelineId)->orderBy('order')->get(['id', 'name']);

        return response()->json(['stages' => $stages]);
    }

    public function saveSearchSettings(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->search_settings = $request->fields;
            $user->save();
            return response()->json(['success' => __('Search settings saved successfully.')]);
        }
        return response()->json(['error' => __('Failed to save search settings.')], 400);
    }

    public function saveStatsConfig(Request $request)
    {
        if (Auth::user()->isAbleTo('lead manage')) {
            $cards = $request->cards ?? [];
            
            \App\Models\Setting::updateOrCreate(
                [
                    'key' => 'leads_stats_cards_config',
                    'workspace' => getActiveWorkSpace(),
                ],
                [
                    'value' => json_encode($cards),
                    'created_by' => creatorId(),
                ]
            );
            
            // Clear cache to ensure settings are fresh
            comapnySettingCacheForget();
            
            return response()->json(['success' => true, 'message' => __('Lead statistics configuration saved successfully.')]);
        }
        return response()->json(['success' => false, 'message' => __('Permission Denied.')], 403);
    }

    public function crmSettings()
    {
        if (Auth::user()->isAbleTo('crm manage')) {
            $fields = [
                'name' => 'Name',
                'email' => 'Email',
                'phone' => 'Phone',
                'subject' => 'Subject',
            ];

            $customFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', getActiveWorkSpace())->get();
            foreach ($customFields as $field) {
                $fields['custom_' . $field->id] = $field->name . ' (Custom)';
            }

            $duplicateFields = company_setting('duplicate_fields') ? json_decode(company_setting('duplicate_fields'), true) : [];

            $pipelines = \Workdo\Lead\Entities\Pipeline::where('workspace_id', getActiveWorkSpace())->with('leadStages')->get();
            $workflows = company_setting('lead_workflow_settings') ? json_decode(company_setting('lead_workflow_settings'), true) : [];

            \Illuminate\Support\Facades\Cache::forget('sidebar_menu_v2_' . Auth::user()->id);

            return view('lead::settings.crm_settings', compact('fields', 'duplicateFields', 'pipelines', 'workflows'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function saveCrmSettings(Request $request)
    {
        if (Auth::user()->isAbleTo('crm manage')) {
            $duplicateFields = $request->duplicate_fields ?? [];

            \App\Models\Setting::updateOrCreate(
                [
                    'key' => 'duplicate_fields',
                    'workspace' => getActiveWorkSpace(),
                ],
                [
                    'value' => json_encode($duplicateFields),
                    'created_by' => creatorId(),
                ]
            );

            // Parse and save workflows
            $workflows = [];
            if ($request->has('workflows')) {
                foreach ($request->workflows as $wf) {
                    if (!empty($wf['from_pipeline_id']) && !empty($wf['from_stage_id']) && !empty($wf['to_pipeline_id']) && !empty($wf['to_stage_id'])) {
                        $workflows[] = [
                            'from_pipeline_id' => $wf['from_pipeline_id'],
                            'from_stage_id' => $wf['from_stage_id'],
                            'to_pipeline_id' => $wf['to_pipeline_id'],
                            'to_stage_id' => $wf['to_stage_id'],
                        ];
                    }
                }
            }

            \App\Models\Setting::updateOrCreate(
                [
                    'key' => 'lead_workflow_settings',
                    'workspace' => getActiveWorkSpace(),
                ],
                [
                    'value' => json_encode($workflows),
                    'created_by' => creatorId(),
                ]
            );

            // Clear cache to ensure settings are fresh
            comapnySettingCacheForget();
            \Illuminate\Support\Facades\Cache::forget('sidebar_menu_v2_' . Auth::user()->id);

            return redirect()->back()->with('success', __('CRM settings saved successfully.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function checkDuplicate(Request $request)
    {
        $field = $request->field;
        $value = $request->value;
        $workspace = getActiveWorkSpace();

        if (empty($value)) {
            return response()->json(['exists' => false]);
        }

        $query = Lead::where('workspace_id', $workspace);

        if (str_starts_with($field, 'custom_')) {
            $fieldId = str_replace('custom_', '', $field);
            $exists = \Workdo\Lead\Entities\LeadCustomFieldValue::where('field_id', $fieldId)
                ->where('value', $value)
                ->whereHas('lead', function ($q) use ($workspace) {
                    $q->where('workspace_id', $workspace);
                })->exists();
        } else {
            $exists = $query->where($field, $value)->exists();
        }

        return response()->json(['exists' => $exists]);
    }

    public function getStageRequirements(Request $request)
    {
        $stageId = $request->stage_id;
        $workspace = getActiveWorkSpace();

        $requiredCustomFields = \Workdo\Lead\Entities\StageCustomField::where('stage_id', $stageId)
            ->where('entity_type', 'lead')
            ->where('is_required', 1)
            ->pluck('custom_field_id')
            ->toArray();

        $visibleCustomFields = \Workdo\Lead\Entities\StageCustomField::where('stage_id', $stageId)
            ->where('entity_type', 'lead')
            ->pluck('custom_field_id')
            ->toArray();

        // Find all custom fields that are mapped to ANY stage for leads
        $allLeadMappedCustomFields = \Workdo\Lead\Entities\StageCustomField::where('entity_type', 'lead')
            ->pluck('custom_field_id')
            ->unique()
            ->toArray();

        $hiddenCustom = [];
        foreach ($allLeadMappedCustomFields as $mappedCfId) {
            if (!in_array($mappedCfId, $visibleCustomFields)) {
                $hiddenCustom[] = $mappedCfId;
            }
        }

        $stage = \Workdo\Lead\Entities\LeadStage::find($stageId);
        $pipelineId = $stage ? $stage->pipeline_id : null;

        $relevantStageIds = [];
        if ($stage) {
            $relevantStageIds = \Workdo\Lead\Entities\LeadStage::where('pipeline_id', $stage->pipeline_id)
                ->where('workspace_id', $workspace)
                ->where(function ($query) use ($stage) {
                    $query->where('order', '<', $stage->order)
                          ->orWhere(function ($q) use ($stage) {
                              $q->where('order', '=', $stage->order)
                                ->where('id', '<=', $stage->id);
                          });
                })
                ->pluck('id')
                ->toArray();
        }

        $leadFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', $workspace)->get();
        $dedicatedRequired = [];
        $dedicatedHidden = [];

        foreach ($leadFields as $field) {
            // If field is not for this pipeline, hide it immediately
            if ($pipelineId && $field->pipeline_id != $pipelineId) {
                $dedicatedHidden[] = (string) $field->id;
                continue;
            }
            $visibleStages = $field->visible_stages ?? [];
            $requiredStages = $field->required_stages ?? [];

            $isHidden = false;
            $isRequired = false;

            if ($stageId) {
                // Determine visibility
                if (!empty($visibleStages)) {
                    if (!in_array($stageId, $visibleStages)) {
                        $isHidden = true;
                    }
                } else {
                    if (!empty($requiredStages)) {
                        $intersect = array_intersect($relevantStageIds, $requiredStages);
                        if (empty($intersect)) {
                            $isHidden = true;
                        }
                    }
                }

                // Determine requirement: required if target stage or any previous stage requires it
                if (!empty($requiredStages) && !empty(array_intersect($relevantStageIds, $requiredStages))) {
                    $isRequired = true;
                }

                // Or globally required (if it's not hidden in the current stage)
                if (!$isRequired && $field->is_required) {
                    if (!$isHidden) {
                        $isRequired = true;
                    }
                }
            } else {
                if ($field->is_required) {
                    $isRequired = true;
                }
            }

            if ($isHidden) {
                $dedicatedHidden[] = (string) $field->id;
            } else {
                if ($isRequired) {
                    $dedicatedRequired[] = (string) $field->id;
                }
            }
        }

        return response()->json([
            'required_custom' => $requiredCustomFields,
            'visible_custom' => $visibleCustomFields,
            'hidden_custom' => $hiddenCustom,
            'required_lead' => $dedicatedRequired,
            'hidden_lead' => $dedicatedHidden
        ]);
    }

    /**
     * Notify department head (or company creator) about a lead stage change.
     * Uses HRM module to find the owner's department manager.
     */
    private function notifyDepartmentHead(Lead $lead, string $message): void
    {
        try {
            $owner = User::find($lead->user_id);
            if (!$owner)
                return;

            $departmentHeadId = null;

            if (module_is_active('Hrm')) {
                $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $owner->id)->first();
                if ($employee && $employee->department_id) {
                    $dept = \Workdo\Hrm\Entities\Department::find($employee->department_id);
                    if ($dept && !empty($dept->manager_id)) {
                        $departmentHeadId = $dept->manager_id;
                    }
                }
            }

            // Fallback: notify the company/workspace creator
            if (!$departmentHeadId) {
                $departmentHeadId = $lead->created_by;
            }

            // Don't notify yourself
            if ($departmentHeadId == Auth::id())
                return;

            // Don't notify if same as lead owner
            if ($departmentHeadId == $lead->user_id)
                return;

            UserNotification::create([
                'user_id' => $departmentHeadId,
                'type' => 'lead_stage_change',
                'data' => [
                    'lead_id' => $lead->id,
                    'lead_name' => $lead->name,
                    'message' => $message,
                    'changed_by' => Auth::user()->name,
                    'url' => route('leads.show', $lead->id),
                    'icon' => 'ti-arrows-right-left',
                    'color' => 'primary',
                ],
                'is_read' => false,
                'workspace_id' => getActiveWorkSpace(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('notifyDepartmentHead failed: ' . $e->getMessage());
        }
    }

    public function bulkExportDownload(Request $request)
    {
        if (Auth::user()->isAbleTo('lead manage')) {
            $exportId = $request->export_id;
            if (!$exportId)
                return redirect()->back()->with('error', __('Invalid export request.'));

            $filePath = storage_path('app/public/exports/' . $exportId . '.csv');
            if (file_exists($filePath)) {
                return response()->download($filePath, 'leads_export_' . date('Y_m_d_H_i_s') . '.csv')->deleteFileAfterSend(true);
            }
            return redirect()->back()->with('error', __('Export file not found.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function bulkImportView(Request $request)
    {
        if (Auth::user()->isAbleTo('lead import')) {
            $creatorId = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            $accessibleUsers = Auth::user()->getAccessibleUserIds();
            $users = User::whereIn('id', $accessibleUsers)->where('type', '!=', 'client')->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');

            $pipelines = Pipeline::where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
            $sources = \Workdo\Lead\Entities\Source::where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');

            $custom_fields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', $getActiveWorkSpace)
                ->where(function ($q) {
                    $q->whereNull('is_system')->orWhere('is_system', 0);
                })->get();

            return view('lead::leads.bulk_import', compact('users', 'pipelines', 'sources', 'custom_fields'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function bulkImportSample()
    {
        if (Auth::user()->isAbleTo('lead import')) {
            $headers = [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=leads_bulk_import_sample.csv",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];

            $columns = [
                'Name',
                'Email',
                'Phone',
                'Created Date',
                'Source',
                'Responsible Person',
                'Team',
                'Created By',
                'PAN Number',
                'Aadhar Number',
                'DP ID'
            ];

            $sample_row = [
                'John Doe',
                'john.doe@example.com',
                '9876543210',
                '2026-05-28',
                'Google',
                'Responsible User Name or Email or ID',
                'Sales Team A',
                'Created By User Name or Email or ID',
                'ABCDE1234F',
                '123456789012',
                'IN300001'
            ];

            $callback = function () use ($columns, $sample_row) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);
                fputcsv($file, $sample_row);
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function bulkImportUpload(Request $request)
    {
        if (Auth::user()->isAbleTo('lead import')) {
            $error = '';
            $file_header = [];
            $file_data = [];
            $total_rows = 0;

            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $file = $request->file('file');
                $file_array = explode(".", $file->getClientOriginalName());
                $extension = end($file_array);

                if (strtolower($extension) == 'csv') {
                    $fileName = 'bulk_leads_' . Auth::user()->id . '_' . time() . '.csv';
                    $filePath = storage_path('framework/cache/' . $fileName);
                    $file->move(storage_path('framework/cache/'), $fileName);

                    $file_handle = fopen($filePath, 'r');
                    $file_header = fgetcsv($file_handle);

                    // Read first 100 rows for preview
                    $lines = 0;
                    while (($row = fgetcsv($file_handle)) !== false) {
                        if ($lines < 100) {
                            $file_data[] = $row;
                        }
                        $total_rows++;
                        $lines++;
                    }
                    fclose($file_handle);

                    // Save to session
                    session()->put('bulk_import_file_path', $filePath);
                    session()->put('bulk_import_file_header', $this->cleanUtf8($file_header));
                    session()->put('bulk_import_total_rows', $total_rows);
                    session()->save();
                } else {
                    $error = __('Only .csv files are allowed.');
                }
            } else {
                $error = __('Please select a valid CSV file.');
            }

            return response()->json([
                'success' => empty($error),
                'error' => $error,
                'file_header' => $this->cleanUtf8($file_header),
                'file_data' => $this->cleanUtf8($file_data),
                'total_rows' => $total_rows
            ]);
        }
        return response()->json(['success' => false, 'error' => __('Permission Denied.')], 401);
    }

    public function bulkImportProcess(Request $request)
    {
        file_put_contents(base_path('debug_import.json'), json_encode([
            'request' => $request->all(),
            'session' => [
                'bulk_import_file_path' => session()->get('bulk_import_file_path'),
                'bulk_import_file_header' => session()->get('bulk_import_file_header'),
                'bulk_import_total_rows' => session()->get('bulk_import_total_rows'),
            ]
        ], JSON_PRETTY_PRINT));
        \Log::info('bulkImportProcess request payload: ' . json_encode($request->all()));
        try {
            if (Auth::user()->isAbleTo('lead import')) {
                $creatorId = creatorId();
                $getActiveWorkSpace = getActiveWorkSpace();
                $filePath = session()->get('bulk_import_file_path') ?? null;
                $file_header = session()->get('bulk_import_file_header') ?? [];
                $total_items = session()->get('bulk_import_total_rows') ?? 0;

                $accessibleUserIds = Auth::user()->getAccessibleUserIds();
                $all_users = \App\Models\User::whereIn('id', $accessibleUserIds)
                    ->where('type', '!=', 'client')
                    ->get();

                $all_teams = collect();
                $all_employees = collect();
                if (module_is_active('Hrm')) {
                    $all_teams = \Workdo\Hrm\Entities\Department::where('workspace', $getActiveWorkSpace)->get();
                    $all_employees = \Workdo\Hrm\Entities\Employee::where('workspace', $getActiveWorkSpace)->get();
                }

                if (!$filePath || !file_exists($filePath)) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Import file not found or expired. Please re-upload.'),
                    ]);
                }

                $chunk_index = intval($request->input('chunk_index', 0));
                $chunk_size = intval($request->input('chunk_size', 50));

                $process_data = [];
                $file_handle = fopen($filePath, 'r');
                fgetcsv($file_handle); // Skip header

                $current_row = 0;
                while (($row = fgetcsv($file_handle)) !== false) {
                    if ($current_row >= $chunk_index && $current_row < ($chunk_index + $chunk_size)) {
                        $temp_row = [];
                        foreach ($file_header as $i => $header) {
                            $temp_row[$i] = $row[$i] ?? '';
                        }
                        $process_data[] = $temp_row;
                    }
                    $current_row++;
                    if ($current_row >= ($chunk_index + $chunk_size)) {
                        break;
                    }
                }
                fclose($file_handle);

                // Initialize session logs on first chunk
                if ($chunk_index == 0) {
                    session()->put('bulk_imported_phones', []);
                    session()->put('bulk_imported_emails', []);
                    session()->put('bulk_failed_leads', []);
                }

                $imported_phones = session()->get('bulk_imported_phones', []);
                $imported_emails = session()->get('bulk_imported_emails', []);
                $failed_leads = session()->get('bulk_failed_leads', []);

                // Get mapping configurations (column indices in the CSV row)
                $map_name = ($request->input('mapping_name') !== null && $request->input('mapping_name') !== '') ? intval($request->input('mapping_name')) : null;
                $map_email = ($request->input('mapping_email') !== null && $request->input('mapping_email') !== '') ? intval($request->input('mapping_email')) : null;
                $map_phone = ($request->input('mapping_phone') !== null && $request->input('mapping_phone') !== '') ? intval($request->input('mapping_phone')) : null;
                $map_date = ($request->input('mapping_date') !== null && $request->input('mapping_date') !== '') ? intval($request->input('mapping_date')) : null;
                $map_sources = ($request->input('mapping_sources') !== null && $request->input('mapping_sources') !== '') ? intval($request->input('mapping_sources')) : null;
                $map_user_id = ($request->input('mapping_user_id') !== null && $request->input('mapping_user_id') !== '') ? intval($request->input('mapping_user_id')) : null;
                $map_team = ($request->input('mapping_team') !== null && $request->input('mapping_team') !== '') ? intval($request->input('mapping_team')) : null;
                $map_created_by = ($request->input('mapping_created_by') !== null && $request->input('mapping_created_by') !== '') ? intval($request->input('mapping_created_by')) : null;
                $map_pan_number = ($request->input('mapping_pan_number') !== null && $request->input('mapping_pan_number') !== '') ? intval($request->input('mapping_pan_number')) : null;
                $map_aadhar_number = ($request->input('mapping_aadhar_number') !== null && $request->input('mapping_aadhar_number') !== '') ? intval($request->input('mapping_aadhar_number')) : null;
                $map_dp_id = ($request->input('mapping_dp_id') !== null && $request->input('mapping_dp_id') !== '') ? intval($request->input('mapping_dp_id')) : null;

                $custom_fields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', $getActiveWorkSpace)
                    ->where(function ($q) {
                        $q->whereNull('is_system')->orWhere('is_system', 0);
                    })->get();

                $map_custom_fields = [];
                foreach ($custom_fields as $cf) {
                    $val = $request->input('mapping_custom_field_' . $cf->id);
                    if ($val !== null && $val !== '') {
                        $map_custom_fields[$cf->id] = intval($val);
                    }
                }

                // Global configurations
                $global_pipeline = $request->input('global_pipeline');
                $global_stage = $request->input('global_stage');
                $global_source = $request->input('global_source');
                $global_user = $request->input('global_user');

                // Resolve global pipeline
                $pipeline = null;
                if (!empty($global_pipeline)) {
                    $pipeline = \Workdo\Lead\Entities\Pipeline::where('id', $global_pipeline)
                        ->where('workspace_id', $getActiveWorkSpace)
                        ->first();
                }
                if (empty($pipeline)) {
                    $user = Auth::user();
                    if ($user->default_pipeline) {
                        $pipeline = \Workdo\Lead\Entities\Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->where('id', '=', $user->default_pipeline)->first();
                    }
                    if (empty($pipeline)) {
                        $pipeline = \Workdo\Lead\Entities\Pipeline::where('created_by', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                    }
                }

                if (empty($pipeline)) {
                    return response()->json(['success' => false, 'message' => __('Please create pipeline.')]);
                }

                // Resolve global stage
                $stage = null;
                if (!empty($global_stage)) {
                    $stage = \Workdo\Lead\Entities\LeadStage::where('id', $global_stage)
                        ->where('pipeline_id', $pipeline->id)
                        ->where('workspace_id', $getActiveWorkSpace)
                        ->first();
                }
                if (empty($stage)) {
                    $stage = \Workdo\Lead\Entities\LeadStage::where('pipeline_id', $pipeline->id)->where('workspace_id', $getActiveWorkSpace)->orderBy('order')->first();
                }

                if (empty($stage)) {
                    return response()->json(['success' => false, 'message' => __('Please create stage for this pipeline.')]);
                }

                $create_fail_log = function ($row_number, $name, $email, $phone, $reason, $row) {
                    return [
                        'row' => $row_number,
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'reason' => $reason,
                        'original_row' => $row
                    ];
                };

                $chunk_failures = [];

                foreach ($process_data as $key => $row) {
                    $row_number = $chunk_index + $key + 1;

                    // Extract values using mapping
                    $name = ($map_name !== null && $map_name !== '' && isset($row[$map_name])) ? trim($row[$map_name]) : '';
                    $email = ($map_email !== null && $map_email !== '' && isset($row[$map_email])) ? trim($row[$map_email]) : '';
                    $phone = ($map_phone !== null && $map_phone !== '' && isset($row[$map_phone])) ? trim($row[$map_phone]) : '';
                    $csv_date = ($map_date !== null && $map_date !== '' && isset($row[$map_date])) ? trim($row[$map_date]) : '';
                    $csv_source = ($map_sources !== null && $map_sources !== '' && isset($row[$map_sources])) ? trim($row[$map_sources]) : '';
                    $csv_user = ($map_user_id !== null && $map_user_id !== '' && isset($row[$map_user_id])) ? trim($row[$map_user_id]) : '';
                    $csv_team = ($map_team !== null && $map_team !== '' && isset($row[$map_team])) ? trim($row[$map_team]) : '';
                    $csv_creator = ($map_created_by !== null && $map_created_by !== '' && isset($row[$map_created_by])) ? trim($row[$map_created_by]) : '';
                    $pan_number = ($map_pan_number !== null && $map_pan_number !== '' && isset($row[$map_pan_number])) ? trim($row[$map_pan_number]) : '';
                    $aadhar_number = ($map_aadhar_number !== null && $map_aadhar_number !== '' && isset($row[$map_aadhar_number])) ? trim($row[$map_aadhar_number]) : '';
                    $dp_id = ($map_dp_id !== null && $map_dp_id !== '' && isset($row[$map_dp_id])) ? trim($row[$map_dp_id]) : '';

                    // Subject
                    $subject = 'Lead from Bulk Import ' . date('Y-m-d');

                    if (empty($name)) {
                        $name = !empty($phone) ? $phone : (!empty($email) ? $email : $subject);
                    }

                    // Validation 1: Phone is required
                    if (empty($phone)) {
                        $fail_log = $create_fail_log($row_number, $name, $email, $phone, __('Phone number is required.'), $row);
                        $failed_leads[] = $fail_log;
                        $chunk_failures[] = $fail_log;
                        continue;
                    }

                    // Validation 2: Email format
                    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $fail_log = $create_fail_log($row_number, $name, $email, $phone, __('Invalid email format.'), $row);
                        $failed_leads[] = $fail_log;
                        $chunk_failures[] = $fail_log;
                        continue;
                    }

                    // Duplicate Check
                    $is_duplicate = false;
                    $dup_reason = '';

                    // Check Phone duplicates
                    if (in_array($phone, $imported_phones)) {
                        $is_duplicate = true;
                        $dup_reason = __('Duplicate phone number in CSV file.');
                    } else {
                        $exists = \Workdo\Lead\Entities\Lead::where('phone', $phone)->where('workspace_id', $getActiveWorkSpace)->exists();
                        if ($exists) {
                            $is_duplicate = true;
                            $dup_reason = __('Phone number already exists in database.');
                        }
                    }



                    if ($is_duplicate) {
                        $fail_log = $create_fail_log($row_number, $name, $email, $phone, $dup_reason, $row);
                        $failed_leads[] = $fail_log;
                        $chunk_failures[] = $fail_log;
                        continue;
                    }

                    // Resolve Dynamic Fields
                    // 1. Responsible Person (user_id)
                    $resolved_user_id = null;
                    $matched_user_record = null;
                    if (!empty($csv_user)) {
                        $csv_user_clean = strtolower(trim(preg_replace('/\s+/u', ' ', $csv_user)));

                        // Normalize "Lever / Levers / Leaver / Leavers" suffix variations
                        $csv_user_normalized = preg_replace('/\s+(levers|leavers|lever|leaver)$/i', '', $csv_user_clean);

                        if (is_numeric($csv_user_clean)) {
                            $usr = $all_users->firstWhere('id', intval($csv_user_clean));
                            if ($usr)
                                $matched_user_record = $usr;
                        }
                        // Exact email match
                        if (!$matched_user_record) {
                            $usr = $all_users->first(function ($u) use ($csv_user_clean) {
                                return strtolower(trim($u->email)) === $csv_user_clean;
                            });
                            if ($usr)
                                $matched_user_record = $usr;
                        }
                        // Exact name match
                        if (!$matched_user_record) {
                            $usr = $all_users->first(function ($u) use ($csv_user_clean) {
                                return strtolower(trim($u->name)) === $csv_user_clean;
                            });
                            if ($usr)
                                $matched_user_record = $usr;
                        }
                        // Partial/contains name match
                        if (!$matched_user_record) {
                            $usr = $all_users->first(function ($u) use ($csv_user_clean) {
                                $db_name = strtolower(trim($u->name));
                                return str_contains($db_name, $csv_user_clean) || str_contains($csv_user_clean, $db_name);
                            });
                            if ($usr)
                                $matched_user_record = $usr;
                        }
                        // ── Lever/Levers/Leaver/Leavers normalization match ──
                        // Strip suffix from CSV value and match against DB name prefix
                        // e.g. "legend leavers" → strip → "legend" matches "legend leaver"
                        if (!$matched_user_record && $csv_user_normalized !== $csv_user_clean) {
                            $usr = $all_users->first(function ($u) use ($csv_user_normalized) {
                                $db_name_normalized = preg_replace('/\s+(levers|leavers|lever|leaver)$/i', '', strtolower(trim($u->name)));
                                return $db_name_normalized === $csv_user_normalized
                                    || str_contains($db_name_normalized, $csv_user_normalized)
                                    || str_contains($csv_user_normalized, $db_name_normalized);
                            });
                            if ($usr)
                                $matched_user_record = $usr;
                        }

                        if ($matched_user_record) {
                            // Allow Lever/Leaver system accounts (is_enable_login=0) to be responsible persons
                            // They are internal pool accounts, not real login users
                            $isLeverAccount = preg_match('/\s+(lever|leaver)$/i', $matched_user_record->name);
                            if ($matched_user_record->is_disable == 1) {
                                $fail_log = $create_fail_log($row_number, $name, $email, $phone, sprintf(__('Responsible Person "%s" is inactive/disabled.'), $matched_user_record->name), $row);
                                $failed_leads[] = $fail_log;
                                $chunk_failures[] = $fail_log;
                                continue;
                            }
                            if (!$isLeverAccount && $matched_user_record->is_enable_login != 1) {
                                $fail_log = $create_fail_log($row_number, $name, $email, $phone, sprintf(__('Responsible Person "%s" is inactive/disabled.'), $matched_user_record->name), $row);
                                $failed_leads[] = $fail_log;
                                $chunk_failures[] = $fail_log;
                                continue;
                            }
                            $resolved_user_id = $matched_user_record->id;
                        } else {
                            $fail_log = $create_fail_log($row_number, $name, $email, $phone, sprintf(__('Responsible Person "%s" not found in the workspace.'), $csv_user), $row);
                            $failed_leads[] = $fail_log;
                            $chunk_failures[] = $fail_log;
                            continue;
                        }
                    }

                    if (!$resolved_user_id) {
                        $resolved_user_id = !empty($global_user) ? $global_user : $creatorId;
                        $matched_user_record = $all_users->firstWhere('id', $resolved_user_id);
                    }

                    // 1.1. Team Verification
                    if ($map_team !== null && $map_team !== '' && !empty($csv_team)) {
                        $resolved_team = null;
                        $csv_team_clean = strtolower(trim(preg_replace('/\s+/u', ' ', $csv_team)));
                        $csv_team_nos = (str_ends_with($csv_team_clean, 's') && !str_ends_with($csv_team_clean, 'ss')) ? substr($csv_team_clean, 0, -1) : $csv_team_clean;

                        if (is_numeric($csv_team_clean)) {
                            $resolved_team = $all_teams->firstWhere('id', intval($csv_team_clean));
                        }
                        if (!$resolved_team) {
                            $resolved_team = $all_teams->first(function ($t) use ($csv_team_clean) {
                                return strtolower(trim($t->name)) === $csv_team_clean;
                            });
                        }
                        if (!$resolved_team) {
                            // Exact singular/plural match (e.g. FIGHTER vs FIGHTERs)
                            $resolved_team = $all_teams->first(function ($t) use ($csv_team_clean, $csv_team_nos) {
                                $t_name = strtolower(trim($t->name));
                                $t_name_nos = (str_ends_with($t_name, 's') && !str_ends_with($t_name, 'ss')) ? substr($t_name, 0, -1) : $t_name;
                                return $t_name === $csv_team_nos || $t_name_nos === $csv_team_clean || $t_name_nos === $csv_team_nos;
                            });
                        }
                        if (!$resolved_team) {
                            $resolved_team = $all_teams->first(function ($t) use ($csv_team_clean) {
                                $t_name = strtolower(trim($t->name));
                                return str_contains($t_name, $csv_team_clean) || str_contains($csv_team_clean, $t_name);
                            });
                        }

                        if (!$resolved_team) {
                            $fail_log = $create_fail_log($row_number, $name, $email, $phone, sprintf(__('Team "%s" not found in the workspace.'), $csv_team), $row);
                            $failed_leads[] = $fail_log;
                            $chunk_failures[] = $fail_log;
                            continue;
                        }

                        // Validate if the resolved user belongs to the team
                        if ($resolved_user_id) {
                            $emp = $all_employees->firstWhere('user_id', $resolved_user_id);
                            if (!$emp || $emp->department_id != $resolved_team->id) {
                                $fail_log = $create_fail_log($row_number, $name, $email, $phone, sprintf(__('Responsible Person "%s" does not belong to Team "%s".'), $matched_user_record ? $matched_user_record->name : $csv_user, $resolved_team->name), $row);
                                $failed_leads[] = $fail_log;
                                $chunk_failures[] = $fail_log;
                                continue;
                            }
                        }
                    }

                    // 2. Created By (created_by)
                    $resolved_created_by = null;
                    if (!empty($csv_creator)) {
                        $csv_creator_clean = strtolower(trim(preg_replace('/\s+/u', ' ', $csv_creator)));
                        $matched_creator_record = null;
                        if (is_numeric($csv_creator_clean)) {
                            $usr = $all_users->firstWhere('id', intval($csv_creator_clean));
                            if ($usr)
                                $matched_creator_record = $usr;
                        }
                        if (!$matched_creator_record) {
                            $usr = $all_users->first(function ($u) use ($csv_creator_clean) {
                                return strtolower(trim($u->email)) === $csv_creator_clean;
                            });
                            if ($usr)
                                $matched_creator_record = $usr;
                        }
                        if (!$matched_creator_record) {
                            $usr = $all_users->first(function ($u) use ($csv_creator_clean) {
                                return strtolower(trim($u->name)) === $csv_creator_clean;
                            });
                            if ($usr)
                                $matched_creator_record = $usr;
                        }
                        if (!$matched_creator_record) {
                            $usr = $all_users->first(function ($u) use ($csv_creator_clean) {
                                $db_name = strtolower(trim($u->name));
                                return str_contains($db_name, $csv_creator_clean) || str_contains($csv_creator_clean, $db_name);
                            });
                            if ($usr)
                                $matched_creator_record = $usr;
                        }

                        if ($matched_creator_record) {
                            if ($matched_creator_record->is_disable == 1 || $matched_creator_record->is_enable_login != 1) {
                                $fail_log = $create_fail_log($row_number, $name, $email, $phone, sprintf(__('Created By User "%s" is inactive/disabled.'), $matched_creator_record->name), $row);
                                $failed_leads[] = $fail_log;
                                $chunk_failures[] = $fail_log;
                                continue;
                            }
                            $resolved_created_by = $matched_creator_record->id;
                        } else {
                            $fail_log = $create_fail_log($row_number, $name, $email, $phone, sprintf(__('Created By User "%s" not found.'), $csv_creator), $row);
                            $failed_leads[] = $fail_log;
                            $chunk_failures[] = $fail_log;
                            continue;
                        }
                    }
                    if (!$resolved_created_by) {
                        $resolved_created_by = $creatorId;
                    }

                    // 3. Source (sources)
                    $resolved_source_id = null;
                    if (!empty($csv_source)) {
                        if (is_numeric($csv_source)) {
                            $src = \Workdo\Lead\Entities\Source::where('id', $csv_source)->where('workspace_id', $getActiveWorkSpace)->first();
                            if ($src)
                                $resolved_source_id = $src->id;
                        } else {
                            $src = \Workdo\Lead\Entities\Source::where('name', 'like', '%' . $csv_source . '%')->where('workspace_id', $getActiveWorkSpace)->first();
                            if ($src) {
                                $resolved_source_id = $src->id;
                            } else {
                                $src = \Workdo\Lead\Entities\Source::create([
                                    'name' => $csv_source,
                                    'workspace_id' => $getActiveWorkSpace,
                                    'created_by' => $creatorId
                                ]);
                                $resolved_source_id = $src->id;
                            }
                        }
                    }
                    if (!$resolved_source_id) {
                        $resolved_source_id = $global_source ?? null;
                    }

                    // 4. Created Date (date)
                    $resolved_date = date('Y-m-d');
                    if (!empty($csv_date)) {
                        $time = strtotime($csv_date);
                        if ($time) {
                            $resolved_date = date('Y-m-d', $time);
                        }
                    }

                    try {
                        // Check stage permissions
                        if ($stage && !$stage->permissions()->can_edit) {
                            throw new \Exception(__('You do not have permission to create leads in this stage.'));
                        }

                        $lead = \Workdo\Lead\Entities\Lead::create([
                            'subject' => $subject,
                            'name' => $name,
                            'user_id' => $resolved_user_id,
                            'email' => $email,
                            'phone' => $phone,
                            'pipeline_id' => $pipeline->id,
                            'stage_id' => $stage->id,
                            'sources' => $resolved_source_id,
                            'created_by' => $resolved_created_by,
                            'workspace_id' => $getActiveWorkSpace,
                            'date' => $resolved_date,
                            'pan_number' => $pan_number,
                            'aadhar_number' => $aadhar_number,
                        ]);

                        \Workdo\Lead\Entities\UserLead::firstOrCreate([
                            'user_id' => $resolved_user_id,
                            'lead_id' => $lead->id
                        ]);

                        $lead->activities()->create([
                            'user_id' => Auth::user()->id,
                            'log_type' => 'Lead Imported',
                            'remark' => json_encode(['message' => __('Lead imported via Bulk CSV by ') . Auth::user()->name]),
                        ]);

                        foreach ($map_custom_fields as $cf_id => $col_idx) {
                            $cf_val = isset($row[$col_idx]) ? trim($row[$col_idx]) : '';
                            if ($cf_val !== '') {
                                \Workdo\Lead\Entities\LeadCustomFieldValue::updateOrCreate(
                                    ['lead_id' => $lead->id, 'field_id' => $cf_id],
                                    ['value' => $cf_val]
                                );
                            }
                        }

                        // Successfully processed, track to avoid duplicates
                        $imported_phones[] = $phone;
                        if (!empty($email)) {
                            $imported_emails[] = $email;
                        }

                    } catch (\Exception $e) {
                        $fail_log = $create_fail_log($row_number, $name, $email, $phone, $e->getMessage(), $row);
                        $failed_leads[] = $fail_log;
                        $chunk_failures[] = $fail_log;
                    }
                }

                // Save lists to session
                session()->put('bulk_imported_phones', $imported_phones);
                session()->put('bulk_imported_emails', $imported_emails);
                session()->put('bulk_failed_leads', $failed_leads);
                session()->save();

                $processed_count = $chunk_index + count($process_data);
                $is_finished = ($processed_count >= $total_items);

                // Clean up file if finished
                if ($is_finished && !empty($filePath) && file_exists($filePath)) {
                    @unlink($filePath);
                    session()->forget('bulk_import_file_path');
                }

                return response()->json($this->cleanUtf8([
                    'success' => true,
                    'current' => $processed_count,
                    'total' => $total_items,
                    'is_finished' => $is_finished,
                    'failed_count' => count($failed_leads),
                    'chunk_failures' => $chunk_failures,
                    'all_failed_leads' => $is_finished ? $failed_leads : []
                ]));

            } else {
                return response()->json(['success' => false, 'message' => __('Permission Denied')]);
            }
        } catch (\Exception $e) {
            \Log::error("CRITICAL BULK IMPORT PROCESS ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Critical error: ' . $this->cleanUtf8($e->getMessage())
            ], 500);
        }
    }

    public function syncSectionApi(Request $request)
    {
        $leadId = $request->lead_id;
        $sectionId = $request->section_id;

        $lead = Lead::find($leadId);
        if (!$lead) {
            return response()->json(['error' => __('Lead not found')], 404);
        }

        $section = \Workdo\Lead\Entities\LeadSection::find($sectionId);
        if (!$section) {
            return response()->json(['error' => __('Section not found')], 404);
        }

        // Check if there is an API configured on the section level
        if (!empty($section->api_url)) {
            $updatedValues = $this->executeSectionApi($lead, $section);
            return response()->json([
                'success' => __('Section API synced successfully.'),
                'values' => $updatedValues
            ]);
        }

        $apiFields = $section->fields()->whereNotNull('api_url')->get();
        if ($apiFields->isEmpty()) {
            return response()->json(['error' => __('No API configured for this section')], 400);
        }

        $updatedValues = [];
        foreach ($apiFields as $cf) {
            $updatedVal = $this->executeSingleFieldApi($lead, $cf);
            if ($updatedVal !== null) {
                $updatedValues[$cf->id] = $updatedVal;
            }
        }

        return response()->json([
            'success' => __('Section fields synced successfully.'),
            'values' => $updatedValues
        ]);
    }

    private function triggerCustomFieldApis($lead, $stageId)
    {
        // 1. Process Section level APIs
        $sections = \Workdo\Lead\Entities\LeadSection::where('api_trigger_stage_id', $stageId)
            ->whereNotNull('api_url')
            ->where('workspace_id', getActiveWorkSpace())
            ->get();
            
        foreach ($sections as $section) {
            $this->executeSectionApi($lead, $section, $stageId);
        }

        // 2. Process Custom Field level APIs
        $apiFields = \Workdo\Lead\Entities\LeadCustomField::where('api_trigger_stage_id', $stageId)
            ->whereNotNull('api_url')
            ->where('workspace_id', getActiveWorkSpace())
            ->get();

        foreach ($apiFields as $cf) {
            $this->executeSingleFieldApi($lead, $cf, $stageId);
        }
    }

    private function executeSectionApi($lead, $section, $stageId = null)
    {
        $updatedValues = [];
        try {
            $url = $section->api_url;
            $method = strtoupper($section->api_method ?? 'GET');
            $payload = [
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'lead_email' => $lead->email,
                'lead_phone' => $lead->phone,
                'stage_id' => $stageId ?? $lead->stage_id,
            ];

            $response = null;
            if ($method === 'POST') {
                $response = \Illuminate\Support\Facades\Http::post($url, $payload);
            } else {
                $response = \Illuminate\Support\Facades\Http::get($url, $payload);
            }

            if ($response && $response->successful()) {
                $resData = $response->json();
                $mappingStr = $section->api_response_mapping;
                if (!empty($mappingStr)) {
                    $mappings = json_decode($mappingStr, true);
                    if (is_array($mappings)) {
                        foreach ($mappings as $resKey => $targetField) {
                            $valueToSave = data_get($resData, $resKey);
                            if ($valueToSave !== null) {
                                $valStr = is_array($valueToSave) ? json_encode($valueToSave) : (string)$valueToSave;
                                
                                // Check if it is a system field
                                if (in_array($targetField, ['name', 'email', 'phone', 'pan_number', 'aadhar_number', 'notes'])) {
                                    $lead->{$targetField} = $valStr;
                                    $lead->save();
                                    $updatedValues[$targetField] = $valStr;
                                } else {
                                    // Custom field
                                    \Workdo\Lead\Entities\LeadCustomFieldValue::updateOrCreate(
                                        [
                                            'lead_id' => $lead->id,
                                            'field_id' => $targetField
                                        ],
                                        [
                                            'value' => $valStr
                                        ]
                                    );
                                    $updatedValues[$targetField] = $valStr;
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $ex) {
            \Log::warning("Lead Section API trigger failed for section ID {$section->id}: " . $ex->getMessage());
        }
        return $updatedValues;
    }

    private function executeSingleFieldApi($lead, $cf, $stageId = null)
    {
        try {
            $url = $cf->api_url;
            $method = strtoupper($cf->api_method ?? 'GET');
            $payload = [
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'lead_email' => $lead->email,
                'lead_phone' => $lead->phone,
                'stage_id' => $stageId ?? $lead->stage_id,
            ];

            $response = null;
            if ($method === 'POST') {
                $response = \Illuminate\Support\Facades\Http::post($url, $payload);
            } else {
                $response = \Illuminate\Support\Facades\Http::get($url, $payload);
            }

            if ($response && $response->successful()) {
                $resData = $response->json();
                $valueToSave = null;
                if (!empty($cf->api_response_key)) {
                    $valueToSave = data_get($resData, $cf->api_response_key);
                } else {
                    $valueToSave = $response->body();
                }

                if ($valueToSave !== null) {
                    $valStr = is_array($valueToSave) ? json_encode($valueToSave) : $valueToSave;
                    \Workdo\Lead\Entities\LeadCustomFieldValue::updateOrCreate(
                        [
                            'lead_id' => $lead->id,
                            'field_id' => $cf->id
                        ],
                        [
                            'value' => $valStr
                        ]
                    );
                    return $valStr;
                }
            }
        } catch (\Exception $ex) {
            \Log::warning("Lead Custom Field API trigger failed for field ID {$cf->id}: " . $ex->getMessage());
        }
        return null;
    }

    public static function triggerWorkflow($lead, $newStageId)
    {
        if (self::$isCopying) {
            return;
        }
        self::$isCopying = true;
        try {
            $workspaceId = $lead->workspace_id;
            
            if (self::$cachedWorkflowData === null || !isset(self::$cachedWorkflowData[$workspaceId])) {
                $settings = \App\Models\Setting::where('key', 'lead_workflow_settings')
                    ->where('workspace', $workspaceId)
                    ->first();
                if (!$settings) {
                    self::$cachedWorkflowData[$workspaceId] = [];
                } else {
                    self::$cachedWorkflowData[$workspaceId] = json_decode($settings->value, true) ?: [];
                }
            }

            $workflowData = self::$cachedWorkflowData[$workspaceId];
            if (!is_array($workflowData)) {
                return;
            }

            // Support both old flat rules format and new nested rules format
            $rules = isset($workflowData['rules']) && is_array($workflowData['rules']) ? $workflowData['rules'] : $workflowData;

            foreach ($rules as $rule) {
                if ($rule['from_pipeline_id'] == $lead->pipeline_id && $rule['from_stage_id'] == $newStageId) {
                    $actionType = $rule['action'] ?? 'copy';

                    if ($actionType === 'move') {
                        // Move the lead itself directly to target pipeline and stage
                        $lead->pipeline_id = $rule['to_pipeline_id'];
                        $lead->stage_id = $rule['to_stage_id'];
                        $lead->save();

                        // Add Activity Log
                        \Workdo\Lead\Entities\LeadActivityLog::create([
                            'user_id' => \Auth::user() ? \Auth::user()->id : 1,
                            'lead_id' => $lead->id,
                            'log_type' => 'Workflow Move Triggered',
                            'remark' => json_encode([
                                'title' => $lead->name,
                                'message' => __('Lead moved directly to target pipeline via automation workflow.')
                            ])
                        ]);
                    } else {
                        // Check if this lead was already processed by the workflow to prevent duplicate triggers
                        $alreadyTriggered = \Workdo\Lead\Entities\LeadActivityLog::where('lead_id', $lead->id)
                            ->where('log_type', 'Workflow Triggered')
                            ->exists();
                        if ($alreadyTriggered) {
                            continue;
                        }

                        // Precise check: check if a copy already exists in target stage with same name and phone
                        $exists = \Workdo\Lead\Entities\Lead::where('stage_id', $rule['to_stage_id'])
                            ->where('name', $lead->name)
                            ->where('workspace_id', $workspaceId)
                            ->when($lead->phone, function($query) use ($lead) {
                                return $query->where('phone', $lead->phone);
                            })
                            ->exists();
                        if ($exists) {
                            continue;
                        }

                        // We have a match! Let's duplicate the lead.
                        $newLead = new Lead();
                        $newLead->name = $lead->name;
                        $newLead->email = $lead->email;
                        $newLead->subject = $lead->subject;
                        $newLead->user_id = $lead->user_id;
                        $newLead->pipeline_id = $rule['to_pipeline_id'];
                        $newLead->stage_id = $rule['to_stage_id'];
                        $newLead->phone = $lead->phone;
                        $newLead->created_by = $lead->created_by;
                        $newLead->workspace_id = $lead->workspace_id;
                        $newLead->date = date('Y-m-d');
                        $newLead->follow_up_date = $lead->follow_up_date;
                        $newLead->pan_number = $lead->pan_number;
                        $newLead->aadhar_number = $lead->aadhar_number;
                        $newLead->updated_by = \Auth::user() ? \Auth::user()->id : $lead->updated_by;
                        
                        // Copy products/sources relation fields (comma-separated strings in DB)
                        $newLead->sources = $lead->sources;
                        $newLead->products = $lead->products;
                        $newLead->notes = $lead->notes;
                        $newLead->labels = $lead->labels;
                        $newLead->save();

                        // Assign users to user_lead relation (copy all assigned users from original lead)
                        $oldUserLeads = \Workdo\Lead\Entities\UserLead::where('lead_id', $lead->id)->get();
                        if ($oldUserLeads->count() > 0) {
                            foreach ($oldUserLeads as $oldUL) {
                                \Workdo\Lead\Entities\UserLead::firstOrCreate([
                                    'lead_id' => $newLead->id,
                                    'user_id' => $oldUL->user_id
                                ]);
                            }
                        } else {
                            \Workdo\Lead\Entities\UserLead::firstOrCreate([
                                'lead_id' => $newLead->id,
                                'user_id' => $newLead->user_id
                            ]);
                        }

                        // Copy custom field values (where names match)
                        $oldCustomFieldValues = \Workdo\Lead\Entities\LeadCustomFieldValue::where('lead_id', $lead->id)->get();
                        if ($oldCustomFieldValues->count() > 0) {
                            $oldFields = \Workdo\Lead\Entities\LeadCustomField::whereIn('id', $oldCustomFieldValues->pluck('field_id'))->get()->keyBy('id');
                            $newFields = \Workdo\Lead\Entities\LeadCustomField::where('pipeline_id', $rule['to_pipeline_id'])
                                ->where('workspace_id', $workspaceId)
                                ->get()
                                ->keyBy('name');

                            foreach ($oldCustomFieldValues as $oldVal) {
                                $oldField = $oldFields->get($oldVal->field_id);
                                if ($oldField && $newFields->has($oldField->name)) {
                                    $targetField = $newFields->get($oldField->name);
                                    \Workdo\Lead\Entities\LeadCustomFieldValue::create([
                                        'lead_id' => $newLead->id,
                                        'field_id' => $targetField->id,
                                        'value' => $oldVal->value
                                    ]);
                                }
                            }
                        }

                        // Add Activity Log on old lead
                        \Workdo\Lead\Entities\LeadActivityLog::create([
                            'user_id' => \Auth::user() ? \Auth::user()->id : 1,
                            'lead_id' => $lead->id,
                            'log_type' => 'Workflow Triggered',
                            'remark' => json_encode([
                                'title' => $lead->name,
                                'message' => __('Lead duplicated to target pipeline via automation workflow.')
                            ])
                        ]);

                        // Add Activity Log on new lead
                        $fromPipeline = \Workdo\Lead\Entities\Pipeline::find($lead->pipeline_id);
                        \Workdo\Lead\Entities\LeadActivityLog::create([
                            'user_id' => \Auth::user() ? \Auth::user()->id : 1,
                            'lead_id' => $newLead->id,
                            'log_type' => 'Workflow Copy Created',
                            'remark' => json_encode([
                                'title' => $newLead->name,
                                'message' => __('Lead created as a workflow copy from pipeline: ') . ($fromPipeline ? $fromPipeline->name : 'Unknown')
                            ])
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Lead copy workflow failed: ' . $e->getMessage());
        } finally {
            self::$isCopying = false;
        }
    }

    public function automationsIndex()
    {
        if (Auth::user()->isAbleTo('crm manage')) {
            $pipelines = \Workdo\Lead\Entities\Pipeline::where('workspace_id', getActiveWorkSpace())
                ->with('leadStages')
                ->get();
                
            $settings = \App\Models\Setting::where('key', 'lead_workflow_settings')
                ->where('workspace', getActiveWorkSpace())
                ->first();
                
            $workflowData = $settings ? json_decode($settings->value, true) : [];
            
            \Illuminate\Support\Facades\Cache::forget('sidebar_menu_v2_' . Auth::user()->id);
            
            return view('lead::settings.automations_graph', compact('pipelines', 'workflowData'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function saveAutomations(Request $request)
    {
        if (Auth::user()->isAbleTo('crm manage')) {
            // Set large execution time and memory limit to handle copying large datasets (e.g. 7k+ leads)
            @set_time_limit(600);
            @ini_set('memory_limit', '1024M');

            $rules = $request->input('rules', []);
            $positions = $request->input('positions', []);

            $workflowData = [
                'rules' => $rules,
                'positions' => $positions
            ];

            \App\Models\Setting::updateOrCreate(
                [
                    'key' => 'lead_workflow_settings',
                    'workspace' => getActiveWorkSpace(),
                ],
                [
                    'value' => json_encode($workflowData),
                    'created_by' => creatorId(),
                ]
            );

            // Clear static cache to ensure newly saved rules are loaded
            self::$cachedWorkflowData = null;

            // Retroactively trigger workflow for existing leads in chunks to prevent timeout
            $workspaceId = getActiveWorkSpace();
            foreach ($rules as $rule) {
                $fromStageId = $rule['from_stage_id'];
                
                \Workdo\Lead\Entities\Lead::where('stage_id', $fromStageId)
                    ->where('workspace_id', $workspaceId)
                    ->chunk(100, function ($leads) use ($fromStageId) {
                        foreach ($leads as $lead) {
                            self::triggerWorkflow($lead, $fromStageId);
                        }
                    });
            }

            comapnySettingCacheForget();
            \Illuminate\Support\Facades\Cache::forget('sidebar_menu_v2_' . Auth::user()->id);

            return response()->json(['success' => true, 'message' => __('Automations saved successfully and applied to existing leads.')]);
        }
        return response()->json(['success' => false, 'message' => __('Permission Denied.')], 403);
    }
}