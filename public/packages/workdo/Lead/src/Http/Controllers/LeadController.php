<?php

namespace Workdo\Lead\Http\Controllers;

use App\Models\User;
use App\Models\WorkSpace;
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
use Workdo\ProductService\Entities\ProductService;
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
use Workdo\Lead\Entities\LeadSection;

class LeadController extends Controller
{
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
        
        // 1. My Tasks (Pending & Overdue)
        $tasks = \Workdo\Lead\Entities\LeadTask::where('user_id', $user->id)
                    ->where('workspace', $workspace)
                    ->whereIn('status', ['pending', 'in_progress', 'overdue'])
                    ->orderBy('date', 'asc')
                    ->get();
                    
        // 2. My Reminders (Today & Upcoming)
        $reminders = \Workdo\Lead\Entities\Reminder::where('user_id', $user->id)
                        ->where('workspace_id', $workspace)
                        ->where('is_sent', 0)
                        ->orderBy('remind_at', 'asc')
                        ->get();
                        
        // 3. Performance Metrics
        $totalTasks = \Workdo\Lead\Entities\LeadTask::where('user_id', $user->id)->where('workspace', $workspace)->count();
        $completedTasks = \Workdo\Lead\Entities\LeadTask::where('user_id', $user->id)->where('workspace', $workspace)->where('status', 'done')->count();
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
        
        return view('lead::crm.dashboard', compact('tasks', 'reminders', 'completionRate', 'totalTasks', 'completedTasks'));
    }

    public function old_dashboard()
    {
        if (Auth::user()->isAbleTo('crm dashboard manage')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            $transdate = date('Y-m-d', time());

            $calenderTasks = [];
            $chartData     = [];
            $chartcall     = [];
            $dealdata      = [];
            $stagedata     = [];
            $arrCount      = [];
            $arrErr        = [];
            $m             = date("m");
            $de            = date("d");
            $y             = date("Y");
            $format        = 'Y-m-d';
            $user          = Auth::user();

            $usr          =  EntitiesUser::find($user->id);
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

                $arrCount['client']  = User::where('type', '=', 'client')->where('created_by', '=', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->count();
                $arrCount['user']    = User::where('type', '!=', 'client')->where('created_by', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->count();
                $arrCount['deal']    = Deal::where('created_by', '=', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->count();
                $arryTemp = [];
                for ($i = 0; $i <= 7 - 1; $i++) {
                    $date                 = date($format, mktime(0, 0, 0, $m, ($de - $i), $y));
                    $arryTemp['date'][]    = __(date('d-M', strtotime($date)));
                    $arryTemp['dealcall'][] = DealCall::whereDate('created_at', $date)->where('user_id', $creatorId)->count();
                }
                $chartcall = $arryTemp;
                $chartcall['user']    = $arrCount['user'];
                $chartcall['deal']    = $arrCount['deal'];
            } elseif ($user->hasRole('client')) {
                $temp = [];
                for ($i = 0; $i <= 7 - 1; $i++) {
                    $date                 = date($format, mktime(0, 0, 0, $m, ($de - $i), $y));
                    $temp['date'][]    = __(date('d-M', strtotime($date)));
                    $temp['deal'][] = Deal::whereDate('created_at', $date)->where('created_by', $creatorId)->count();
                }
                $dealdata = $temp;
                $dealdata['user']    = User::where('type', '!   =', 'client')->where('created_by', $creatorId)->where('workspace_id', '=', $getActiveWorkSpace)->count();
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

                $client_deal         = $usr->clientDeals->pluck('id');
                $arrCount['deal']    = $usr->clientDeals->count();
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
                $user_deal           = $usr->deals->pluck('id');

                $arrCount['deal']    = $usr->deals()->count();
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
            $workspace       = WorkSpace::where('id', $getActiveWorkSpace)->first();

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

            $creatorId          = creatorId();
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
                    ->whereIn('id', function($query) use ($accessibleUserIds) {
                        $query->select('pipeline_id')
                            ->from('leads')
                            ->whereIn('id', function($q) use ($accessibleUserIds) {
                                $q->select('lead_id')
                                    ->from('user_leads')
                                    ->whereIn('user_id', $accessibleUserIds);
                            });
                    })
                    ->get()
                    ->pluck('name', 'id');
            }

            // Filter Options
            $accessibleUserIds = Auth::user()->getAccessibleUserIds();
            $stages = LeadStage::where('pipeline_id', $pipeline->id)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
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

            return view('lead::leads.index', compact('pipelines', 'pipeline', 'stages', 'sources', 'users', 'creators', 'modifiers', 'saved_filters'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('lead create')) {

            $creatorId          = creatorId();
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
                $customFields =  \Workdo\CustomField\Entities\CustomField::where('workspace_id', $getActiveWorkSpace)->where('module', '=', 'lead')->where('sub_module', 'lead')->get();
                
                 // Filter by Stage Visibility
                if($stage){
                    $stageCustomFields = StageCustomField::where('stage_id', $stage->id)->pluck('custom_field_id')->toArray();
                    if(!empty($stageCustomFields)){
                        $customFields = $customFields->filter(function($field) use ($stageCustomFields){
                            return in_array($field->id, $stageCustomFields);
                        });
                    }
                }
            } else {
                $customFields = null;
            }
            
            // Dedicated Lead Custom Fields
            $leadCustomFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', $getActiveWorkSpace)->orderBy('order')->get();

            $user = Auth::user();
            $isResponsiblePersonEditable = $user->type == 'company' || in_array($user->visibility_level, ['team', 'department', 'all']);

            // Get all pipelines for selection
            $pipelines = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->pluck('name', 'id');
            
            // Get stages for default pipeline
            $stages = [];
            if (!empty($pipeline)) {
                $stages = LeadStage::where('pipeline_id', '=', $pipeline->id)->where('workspace_id', $getActiveWorkSpace)->pluck('name', 'id');
            }

            return view('lead::leads.create', compact('users', 'customFields', 'leadCustomFields', 'isResponsiblePersonEditable', 'pipelines', 'stages', 'pipeline', 'stage'));
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
        $usr = Auth::user();
        if ($usr->isAbleTo('lead create')) {

            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            $validator = \Validator::make(
                $request->all(),
                [
                    'subject'           => 'nullable|string|max:255',
                    'name'              => 'nullable|string|max:255',
                    'email'             => 'nullable|email|max:255',
                    'follow_up_date'    => 'nullable|date',
                    'phone'             => 'required',
                ]
            );

            // Dynamic Validation for Custom Fields
            if($request->has('customField')){
                 $pipelineId = $usr->default_pipeline;
                 // (Fallback logic for pipeline as below)
                 if(!$pipelineId){
                     $p = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                     $pipelineId = $p ? $p->id : null;
                 }
                 if($pipelineId){
                     $stage = LeadStage::where('pipeline_id', '=', $pipelineId)->where('workspace_id', $getActiveWorkSpace)->first();
                     if($stage){
                         $requiredFields = StageCustomField::where('stage_id', $stage->id)->where('is_required', 1)->pluck('custom_field_id')->toArray();
                         foreach($request->customField as $id => $value){
                             if(in_array($id, $requiredFields) && empty($value)){
                                 $validator->after(function ($validator) use($id) {
                                     $validator->errors()->add('customField.'.$id, __('This custom field is required.'));
                                 });
                             }
                         }
                     }
                 }
            }
        
            // Validation for Dedicated Lead Custom Fields
            if($request->has('leadCustomField')){
                 // Determine the stage for this new lead
                 $pipelineId = $usr->default_pipeline;
                 if(!$pipelineId){
                     $p = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                     $pipelineId = $p ? $p->id : null;
                 }
                 $stageId = null;
                 if($pipelineId){
                     $stage = LeadStage::where('pipeline_id', '=', $pipelineId)->where('workspace_id', $getActiveWorkSpace)->first();
                     $stageId = $stage ? $stage->id : null;
                 }
                 
                 $leadRequiredFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', $getActiveWorkSpace)->where('is_required', 1)->get();
                 foreach($leadRequiredFields as $field){
                     // Check visibility before enforcing required
                     $isVisible = true;
                     
                     // Stage Check
                     if (!empty($field->visible_stages) && $stageId && !in_array($stageId, $field->visible_stages)) {
                         $isVisible = false;
                     }
                     
                     // Role Check
                     if (!empty($field->visible_roles)) {
                         $userRoleIds = Auth::user()->roles->pluck('id')->toArray();
                         if (empty(array_intersect($userRoleIds, $field->visible_roles))) {
                             $isVisible = false;
                         }
                     }
                     
                     // Only validate if visible and value is truly missing (not just blank)
                     if($isVisible){
                         $value = $request->leadCustomField[$field->id] ?? null;
                         // For required fields, check if value is null, empty string, or empty array
                         if($value === null || $value === '' || (is_array($value) && empty($value))){
                             $validator->after(function ($validator) use($field) {
                                 $validator->errors()->add('leadCustomField.'.$field->id, __($field->name . ' is required.'));
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
            if($request->has('pipeline_id') && $request->has('stage_id')){
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
                if (empty($request->name)) {
                    $request->merge(['name' => $request->phone]);
                }
                if (empty($request->email)) {
                    $request->merge(['email' => 'null@gmail.com']);
                }

                $lead                 = new Lead();
                $lead->name           = $request->name;
                $lead->email          = $request->email;
                $lead->subject        = $request->subject ?? 'New Lead';
                $lead->user_id        = !empty($request->user_id) ? $request->user_id : $usr->id;
                $lead->pipeline_id    = $pipeline->id;
                $lead->stage_id       = $stage->id;
                $lead->phone          = $request->phone;
                $lead->created_by     = $creatorId;
                $lead->workspace_id   = $getActiveWorkSpace;
                $lead->date           = date('Y-m-d');
                $lead->follow_up_date = $request->follow_up_date;
                $lead->pan_number     = $request->pan_number;
                $lead->aadhar_number  = $request->aadhar_number;
                $lead->updated_by     = $usr->id;
                $lead->save();

                if (module_is_active('CustomField')) {
                    \Workdo\CustomField\Entities\CustomField::saveData($lead, $request->customField);
                }

                // Save Dedicated Lead Custom Fields
                $requestCustomFields = $request->all()['leadCustomField'] ?? [];
                if(!empty($requestCustomFields)){
                     foreach($requestCustomFields as $fieldId => $value){
                         if($request->hasFile("leadCustomField.$fieldId")) {
                             $fileName = time() . "_" . str_replace(' ', '_', $value->getClientOriginalName());
                             $value->move(storage_path('app/public/uploads/custom_fields'), $fileName);
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

                foreach ($usrLeads as $usrLead) {
                    UserLead::create(
                        [
                            'user_id' => $usrLead,
                            'lead_id' => $lead->id,
                        ]
                    );
                }

                $leadArr = [
                    'lead_id' => $lead->id,
                    'name' => $lead->name,
                    'updated_by' => $usr->id,
                ];
                if (!empty(company_setting('Lead Assigned')) && company_setting('Lead Assigned')  == true) {
                    $lArr    = [
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
                $deal          = Deal::where('id', '=', $lead->is_converted)->first();
                $stageCnt      = LeadStage::where('pipeline_id', '=', $lead->pipeline_id)->where('created_by', '=', $lead->created_by)->get();
                $i             = 0;
                foreach ($stageCnt as $stage) {
                    $i++;
                    if ($stage->id == $lead->stage_id) {
                        break;
                    }
                }
                $precentage = number_format(($i * 100) / count($stageCnt));

                if (module_is_active('CustomField')) {
                    $lead->customField = \Workdo\CustomField\Entities\CustomField::getData($lead, 'lead', 'lead');
                    $customFields      = \Workdo\CustomField\Entities\CustomField::where('workspace_id', '=', getActiveWorkSpace())->where('module', '=', 'lead')->where('sub_module', 'lead')->get();
                } else {
                    $customFields = null;
                }
                
                // Fetch Lead Documents
                $leadDocuments = \Workdo\Lead\Entities\LeadDocument::where('workspace_id', getActiveWorkSpace())->get();
                $currentStageOrder = $lead->stage->order;
                $filteredDocuments = $leadDocuments->filter(function($doc) use ($currentStageOrder) {
                    if (!$doc->stage_id) return true;
                    $docStage = \Workdo\Lead\Entities\LeadStage::find($doc->stage_id);
                    return $docStage && $currentStageOrder >= $docStage->order;
                });
                $leadDocuments = $filteredDocuments;
                $uploadedFiles = \Workdo\Lead\Entities\LeadDocumentFile::where('lead_id', $lead->id)->get()->keyBy('document_id');

                // Fetch Dedicated Lead Custom Fields and Values
            $leadSections = \Workdo\Lead\Entities\LeadSection::where('workspace_id', getActiveWorkSpace())
                                ->with(['fields' => function($q) {
                                    $q->orderBy('order');
                                }])
                                ->orderBy('order')
                                ->get();
            $leadCustomFieldValues = \Workdo\Lead\Entities\LeadCustomFieldValue::where('lead_id', $lead->id)->pluck('value', 'field_id')->toArray();

                // Fetch Tasks and Reminders with Visibility Scopes
                $accessibleUserIds = Auth::user()->getAccessibleUserIds();
                $tasks = $lead->tasks()->whereIn('user_id', $accessibleUserIds)->get();
                $reminders = $lead->getFilteredReminders();

                $overdueTasksCount = $tasks->where('status', 'overdue')->count();
                $todayRemindersCount = $lead->getTodayRemindersCount();

                return view('lead::leads.show', compact('lead', 'calenderTasks', 'deal', 'precentage', 'customFields', 'leadDocuments', 'uploadedFiles', 'leadSections', 'leadCustomFieldValues', 'tasks', 'reminders', 'overdueTasksCount', 'todayRemindersCount'));
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
        if (Auth::user()->isAbleTo('lead edit') && $lead->isAccessible()) {

            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            $pipelines = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id')->toArray();
            $pipelines = ['' => __('Select Pipeline')] + $pipelines;
            $sources = Source::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
            if (module_is_active('ProductService')) {
                $products = ProductService::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');
            }
            $accessibleUsers = Auth::user()->getAccessibleUserIds();
            $filtered_users = User::whereIn('id', $accessibleUsers)->where('type', '!=', 'client')->where('workspace_id', $getActiveWorkSpace)->get();
            
            // Ensure current lead owner is in the list
            if($lead->user_id && !$filtered_users->contains('id', $lead->user_id)){
                $lead_owner = User::find($lead->user_id);
                if($lead_owner){
                    $filtered_users->push($lead_owner);
                }
            }
            $users = $filtered_users->pluck('name', 'id')->toArray();

            if (count($users) != 0) {
                $users = ['' => __('Select Responsible Person')] + $users;
            }

            $lead->sources  = explode(',', $lead->sources);
            $lead->products = explode(',', $lead->products);

            if (module_is_active('CustomField')) {
                $lead->customField = \Workdo\CustomField\Entities\CustomField::getData($lead, 'lead', 'lead');
                $customFields             = \Workdo\CustomField\Entities\CustomField::where('workspace_id', '=', $getActiveWorkSpace)->where('module', '=', 'lead')->where('sub_module', 'lead')->get();
            
                // Filter by Stage Visibility
                $stageCustomFields = StageCustomField::where('stage_id', $lead->stage_id)->pluck('custom_field_id')->toArray();
                if(!empty($stageCustomFields)){
                    $customFields = $customFields->filter(function($field) use ($stageCustomFields){
                        return in_array($field->id, $stageCustomFields);
                    });
                }
            } else {
                $customFields = null;
            }

            // Dedicated Lead Custom Fields
            $leadSections = \Workdo\Lead\Entities\LeadSection::where('workspace_id', $getActiveWorkSpace)
                                ->with(['fields' => function($q) {
                                    $q->orderBy('order');
                                }])
                                ->orderBy('order')
                                ->get();
            $leadCustomFieldValues = \Workdo\Lead\Entities\LeadCustomFieldValue::where('lead_id', $lead->id)->pluck('value', 'field_id')->toArray();

            // Ensure current pipeline is in the list
            if($lead->pipeline_id && !isset($pipelines[$lead->pipeline_id])){
                $curr_pipeline = Pipeline::find($lead->pipeline_id);
                if($curr_pipeline){
                    $pipelines[$curr_pipeline->id] = $curr_pipeline->name;
                }
            }

            $stages = LeadStage::where('pipeline_id', '=', $lead->pipeline_id)->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id')->toArray();
            // Ensure current stage is in the list
            if($lead->stage_id && !isset($stages[$lead->stage_id])){
                $curr_stage = LeadStage::find($lead->stage_id);
                if($curr_stage){
                    $stages[$curr_stage->id] = $curr_stage->name;
                }
            }

            $user = Auth::user();
            $isResponsiblePersonEditable = $user->type == 'company' || in_array($user->visibility_level, ['team', 'department', 'all']);

            return view('lead::leads.edit', compact('lead', 'pipelines', 'sources', 'products', 'users', 'customFields', 'isResponsiblePersonEditable', 'leadSections', 'leadCustomFieldValues', 'stages'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
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
        \Log::debug('Lead Update Request Data: ' . json_encode($request->all()));
        if (Auth::user()->isAbleTo('lead edit') && $lead->isAccessible()) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            $validator = \Validator::make(
                $request->all(),
                [
                    'subject'       => 'nullable|string|max:255',
                    'name'          => 'required|string|max:255',
                    'email'         => 'nullable|email|max:255',
                    'pipeline_id'   => 'required|integer|exists:pipelines,id',
                    'user_id'       => 'nullable|integer|exists:users,id',
                    'stage_id'      => 'required|integer|exists:lead_stages,id',
                    'phone'         => 'required',
                    'sources'           => 'nullable|array',
                    'sources.*'         => 'integer|exists:sources,id',
                    'products'          => 'nullable|array',
                    'products.*'        => 'integer|exists:product_services,id',
                    'follow_up_date'    => 'nullable|date',
                ]
            );

            // Dynamic Validation for Custom Fields (Update)
            if($request->has('customField')){
                 $requiredFields = StageCustomField::where('stage_id', $request->stage_id)->where('is_required', 1)->pluck('custom_field_id')->toArray();
                 foreach($request->customField as $id => $value){
                     if(in_array($id, $requiredFields) && empty($value)){
                         $validator->after(function ($validator) use($id) {
                             $validator->errors()->add('customField.'.$id, __('This custom field is required.'));
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

            $lead->name           = $request->name;
            $lead->email          = $request->email;
            $lead->subject        = $request->subject ?? 'New Lead';
            if(!empty($request->user_id)) {
                if($lead->user_id != $request->user_id) {
                    $oldUserId = $lead->user_id;
                    $lead->user_id = $request->user_id;

                    // Update UserLead: Remove old user
                    if($oldUserId) {
                        UserLead::where('lead_id', $lead->id)->where('user_id', $oldUserId)->delete();
                    }

                    // Update UserLead: Add new user
                    UserLead::firstOrCreate([
                        'lead_id' => $lead->id,
                        'user_id' => $request->user_id
                    ]);
                }
            }
            $lead->pipeline_id    = $request->pipeline_id;

            // Automation Logic (Centralized)
            if($lead->stage_id != $request->stage_id){
                 PipelineStageAutomation::run($lead, $request->stage_id);
            }

            $lead->stage_id       = $request->stage_id;
            $lead->sources        = isset($request->sources) && !empty($request->sources) ? implode(",", array_filter($request->sources)) : null;
            $lead->products       = isset($request->products) && !empty($request->products) ? implode(",", array_filter($request->products)) : null;
            $lead->notes          = $request->notes;
            $lead->phone          = $request->phone;
            $lead->follow_up_date = $request->follow_up_date;
            $lead->pan_number     = $request->pan_number;
            $lead->aadhar_number  = $request->aadhar_number;
            $lead->updated_by     = Auth::user()->id;
            $lead->save();


            if (module_is_active('CustomField')) {
                \Workdo\CustomField\Entities\CustomField::saveData($lead, $request->customField);
            }
            
            // Save Dedicated Lead Custom Fields
            $leadCustomFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', $getActiveWorkSpace)->get();
            $requestCustomFields = $request->all()['leadCustomField'] ?? [];
            
            foreach($leadCustomFields as $field){
                if(array_key_exists($field->id, $requestCustomFields)){
                    $value = $requestCustomFields[$field->id];
                    
                    if($request->hasFile("leadCustomField.$field->id")) {
                        $fileName = time() . "_" . str_replace(' ', '_', $value->getClientOriginalName());
                        $value->move(storage_path('app/public/uploads/custom_fields'), $fileName);
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

                    if($isVisible && $field->type != 'file'){
                        \Workdo\Lead\Entities\LeadCustomFieldValue::updateOrCreate(
                            ['lead_id' => $lead->id, 'field_id' => $field->id],
                            ['value' => '']
                        );
                    }
                }
            }
            event(new UpdateLead($request, $lead));

            return redirect()->back()->with('success', __('The lead deatails are updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
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
            $creatorId          = creatorId();
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

            $saved_filters = \Workdo\Lead\Entities\LeadFilter::where('user_id', Auth::user()->id)
                ->where('workspace_id', $getActiveWorkSpace)
                ->get();

            return $dataTable->render('lead::leads.list', compact('pipelines', 'pipeline', 'stages', 'sources', 'users', 'creators', 'modifiers', 'saved_filters'));
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
                    $file                 = LeadFile::create(
                        [
                            'lead_id' => $request->lead_id,
                            'file_name' => $file_name,
                            'file_path' => $url['url'],
                        ]
                    );
                    $return               = [];
                    $return['is_success'] = true;
                    $return['download']   =  get_file($url['url']);
                    $return['delete']     = route(
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
                    $filename  = $file->file_name;

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

            event(new LeadAddNote($request, $lead));

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
            if ($lead && $lead->isAccessible()) {
                $labels   = Label::where('pipeline_id', '=', $lead->pipeline_id)->get();
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
            if ($leads && $leads->isAccessible()) {
                if ($request->labels) {
                    $leads->labels = implode(',', $request->labels);
                } else {
                    $leads->labels = $request->labels;
                }
                $leads->save();

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
            if ($lead && $lead->isAccessible()) {
                $creatorId          = creatorId();
                $getActiveWorkSpace = getActiveWorkSpace();
                $users = User::where('active_workspace', '=', $getActiveWorkSpace)->where('created_by', '=', $creatorId)->where('type', '!=', 'client')->whereNOTIn(
                    'id',
                    function ($q) use ($lead) {
                        $q->select('user_id')->from('user_leads')->where('lead_id', '=', $lead->id);
                    }
                )->get();

                // foreach ($users as $key => $user) {
                //     if (!$user->isAbleTo('lead manage')) {
                //         $users->forget($key);
                //     }
                // }
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
            $usr  = Auth::user();
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                if (!empty($request->users)) {
                    $users   = array_filter($request->users);
                    $leadArr = [
                        'lead_id' => $lead->id,
                        'name' => $lead->name,
                        'updated_by' => $usr->id,
                    ];

                    foreach ($users as $user) {
                        UserLead::create(
                            [
                                'lead_id' => $lead->id,
                                'user_id' => $user,
                            ]
                        );
                    }
                }

                event(new LeadAddUser($request, $lead));

                if (!empty($users) && !empty($request->users)) {
                    return redirect()->back()->with('success', __('Users have been updated successfully.'));
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

                event(new DestroyLeadUser($lead));

                return redirect()->back()->with('success', __('The user has been deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function  productEdit($id)
    {
        if (Auth::user()->isAbleTo('lead edit')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $creatorId          = creatorId();
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
            $usr        = Auth::user();
            $lead       = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                if (!empty($request->products)) {
                    $products       = array_filter($request->products);
                    $old_products   = explode(',', $lead->products);
                    $lead->products = implode(',', array_merge($old_products, $products));
                    $lead->save();

                    $objProduct = ProductService::whereIN('id', $products)->get()->pluck('name', 'id')->toArray();

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
                $creatorId          = creatorId();
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
            $usr        = Auth::user();
            $lead       = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                if (!empty($request->sources) && count($request->sources) > 0) {
                    $lead->sources = implode(',', $request->sources);
                } else {
                    $lead->sources = "";
                }

                $lead->save();

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

    public function discussionCreate($id)
    {
        $lead = Lead::find($id);
        if ($lead && $lead->isAccessible()) {
            return view('lead::leads.discussions', compact('lead'));
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function discussionStore($id, Request $request)
    {
        $usr        = Auth::user();
        $lead       = Lead::find($id);
        if ($lead && $lead->isAccessible()) {
            $discussion             = new LeadDiscussion();
            $discussion->comment    = $request->comment;
            $discussion->lead_id    = $lead->id;
            $discussion->created_by = $usr->id;
            $discussion->save();

            $leadArr = [
                'lead_id' => $lead->id,
                'name' => $lead->name,
                'updated_by' => $usr->id,
            ];

            event(new LeadAddDiscussion($request, $lead));

            return redirect()->back()->with('success', __('The message has been added successfully.'))->with('status', 'discussion');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'discussion');
        }
    }

    public function order(Request $request)
    {
        try {
        if (Auth::user()->isAbleTo('lead move')) {
            $usr        = Auth::user();
            $post       = $request->all();
            $lead       = Lead::find($post['lead_id']);

            if (!$lead || !$lead->isAccessible()) {
                return response()->json(['error' => __('Permission Denied.')]);
            }

            $lead_users = $lead->users->pluck('email', 'id')->toArray();

                if ($lead->stage_id != $post['stage_id']) {

                    $newStage = LeadStage::find($post['stage_id']);

                    $customFieldErrors = $this->validateLeadCustomFields($lead, $post['stage_id']);
                    if (!empty($customFieldErrors)) {
                        return response()->json(['error' => $customFieldErrors[0]]);
                    }

                    if (!$newStage->permissions()->can_move) {
                        return response()->json(['error' => __('Aapko is stage tak lead move karne ka access nahi hai.')]);
                    }

                    LeadActivityLog::create(
                        [
                            'user_id' => Auth::user()->id,
                            'lead_id' => $lead->id,
                            'log_type' => 'Move',
                            'remark' => json_encode(
                                [
                                    'title' => $lead->name,
                                    'old_status' => $lead->stage->name,
                                    'new_status' => $newStage->name,
                                ]
                            ),
                        ]
                    );

                    $leadArr = [
                        'lead_id' => $lead->id,
                        'name' => $lead->name,
                        'updated_by' => $usr->id,
                        'old_status' => $lead->stage->name,
                        'new_status' => $newStage->name,
                    ];


                    if (!empty(company_setting('Lead Moved')) && company_setting('Lead Moved')  == true) {

                        $lArr = [
                            'lead_name' => $lead->name,
                            'lead_email' => $lead->email,
                            'lead_pipeline' => $lead->pipeline->name,
                            'lead_stage' => $lead->stage->name,
                            'lead_old_stage' => $lead->stage->name,
                            'lead_new_stage' => $newStage->name,
                        ];

                        // Send Email
                        EmailTemplate::sendEmailTemplate('Lead Moved', $lead_users, $lArr);
                    }

                    // Automation Logic (Centralized)
                    PipelineStageAutomation::run($lead, $post['stage_id']);
                }
                event(new LeadMoved($request, $lead));

                foreach ($post['order'] as $key => $item) {

                    $leads = Lead::where('id', $item)->update(['order' => $key, 'stage_id' => $post['stage_id']]);
                }
                return response()->json(['success' => __('Lead moved successfully.')]);
            } else {
                return response()->json(['error' => __('Permission denied.')]);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => __('Something went wrong.')]);
        }
    }

    public function showConvertToDeal($id)
    {
        $lead         = Lead::findOrFail($id);
        if ($lead && $lead->isAccessible()) {
            $creatorId    = creatorId();
            $exist_client = User::where('type', '=', 'client')->where('email', '=', $lead->email)->where('created_by', '=', $creatorId)->first();
            $clients      = User::where('type', '=', 'client')->where('created_by', '=', $creatorId)->get();

            return view('lead::leads.convert', compact('lead', 'exist_client', 'clients'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function convertToDeal($id, Request $request)
    {
        $lead = Lead::findOrFail($id);
        if ($lead && $lead->isAccessible()) {
            $usr                = Auth::user();
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();

            if ($request->client_check == 'exist') {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'clients'   => 'required|email|exists:users,email',
                        'price'     => 'numeric|min:0',
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
                        'client_name'       => 'required|string|max:255',
                        'client_email'      => 'required|email|unique:users,email',
                        'client_password'   => 'required',
                        'price'             => 'min:0',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $role   = Role::where('name', 'client')->where('created_by', '=', $creatorId)->first();
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

            $deal              = new \Workdo\Lead\Entities\Deal();
            $deal->name        = $request->name;
            $deal->price       = empty($request->price) ? 0 : $request->price;
            $deal->pipeline_id = $lead->pipeline_id;
            $deal->stage_id    = $stage->id;
            $deal->sources     = in_array('sources', $request->is_transfer) ? $lead->sources : '';
            $deal->products    = in_array('products', $request->is_transfer) ? $lead->products : '';
            $deal->notes       = in_array('notes', $request->is_transfer) ? $lead->notes : '';
            $deal->labels      = $lead->labels;
            $deal->status      = 'Active';
            $deal->workspace_id  = $getActiveWorkSpace;
            $deal->created_by  = $lead->created_by;
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

            if (!empty(company_setting('Deal Assigned')) && company_setting('Deal Assigned')  == true) {
                $dealArr = [
                    'deal_id' => $deal->id,
                    'name' => $deal->name,
                    'updated_by' => $usr->id,
                ];

                // Send Mail
                $pipeline = Pipeline::find($lead->pipeline_id);
                $dArr     = [
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
                        $location     = base_path() . '/' . $file->file_path;
                        $new_location = base_path() . '/' . $file->file_path;
                        $copied       = copy($location, $new_location);

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
            $usr  = Auth::user();
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'subject'       => 'required|string|max:255',
                        'call_type'     => 'required|in:outbound,inbound',
                        'user_id'       => 'required|integer|exists:users,id',
                        'duration'      => [
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
                $call  = LeadCall::find($call_id);
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
                        'subject'       => 'required|string|max:255',
                        'call_type'     => 'required|in:outbound,inbound',
                        'user_id'       => 'required|integer|exists:users,id',
                        'duration'      => [
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
                if($usr->type == 'company' || $usr->type == 'client' || $usr->can('crm manage')) {
                    if($request->user_id && $request->user_id != $usr->id) {
                         $accessibleUserIds = $usr->getAccessibleUserIds();
                         if(!in_array($request->user_id, $accessibleUserIds)) {
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
                        'to'        => 'required|email|max:255',
                        'subject'   => 'required|string|max:255',
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

                if (!empty(company_setting('Lead Emails')) && company_setting('Lead Emails')  == true) {
                    $lead_users[] = $request->to;
                    $lArr = [
                        'lead_name' => $lead->name,
                        'lead_email_subject' => $request->subject,
                        'lead_email_description' => $request->description,
                    ];

                    // Send Email
                   $resp = EmailTemplate::sendEmailTemplate('Lead Emails', $lead_users, $lArr);
                }

                return redirect()->back()->with('success', __('The email has been created successfully.') .((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
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
            $user               = Auth::user();
            $creatorId          = creatorId();
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
            session_start();

            $error = '';

            $html = '';

            if ($request->file->getClientOriginalName() != '') {
                $file_array = explode(".", $request->file->getClientOriginalName());

                $extension = end($file_array);
                if ($extension == 'csv') {
                    $file_data = fopen($request->file->getRealPath(), 'r');

                    $file_header = fgetcsv($file_data);
                    $html .= '<table class="table table-hover mb-0"><thead><tr>';

                    for ($count = 0; $count < count($file_header); $count++) {
                        $html .= '
                                <th>
                                    <select name="set_column_data" class="form-select set_column_data" data-column_number="' . $count . '">
                                        <option value="">' . __('Set Column Data') . '</option>
                                        <option value="subject">' . __('Subject') . '</option>
                                        <option value="name">' . __('Name') . '</option>
                                        <option value="email">' . __('Email') . '</option>
                                        <option value="phone">' . __('Phone No') . '</option>
                                    </select>
                                </th>
                                ';
                    }

                    $html .= '
                                <th>
                                        <select name="set_column_data" class="form-select set_column_data user-name" data-column_number="' . $count + 1 . '">
                                            <option value="user">' . __('Responsible Person') . '</option>
                                        </select>
                                </th>
                                ';

                    $html .= '</tr></thead><tbody>';
                    $limit = 0;
                    while (($row = fgetcsv($file_data)) !== false) {
                        $limit++;

                        $html .= '<tr>';

                        for ($count = 0; $count < count($row); $count++) {
                            $html .= '<td>' . $row[$count] . '</td>';
                        }

                        $html .= '<td>
                                    <select name="user" class="form-control user-name-value">
                                        <option value="">' . __('Select Responsible Person') . '</option>';
                        if (Auth::user()->type == "company") {
                            $users = User::where('created_by', '=', creatorId())->where('type', '!=', 'client')->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
                        } else {
                            $users = User::where('id', '=', Auth::user()->id)->where('type', '!=', 'client')->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
                        }
                        foreach ($users as $key => $user) {
                            $html .= ' <option value="' . $key . '">' . $user . '</option>';
                        }
                        $html .= '  </select>
                                </td>';

                        $html .= '</tr>';

                        $temp_data[] = $row;
                    }
                    $html .= '</tbody></table>';
                    $_SESSION['file_data'] = $temp_data;
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

            return json_encode($output);
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

            return view('lead::leads.import_modal', compact('users', 'pipelines'));
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
        if (Auth::user()->isAbleTo('lead import')) {
            $creatorId          = creatorId();
            $getActiveWorkSpace = getActiveWorkSpace();
            session_start();
            $file_data = $_SESSION['file_data'] ?? [];

            $is_chunk = $request->input('is_chunk', false);
            $chunk_index = $request->input('chunk_index', 0);
            $chunk_size = $request->input('chunk_size', 50);

            if ($is_chunk) {
                $total_items = count($file_data);
                $process_data = array_slice($file_data, $chunk_index, $chunk_size);
            } else {
                $process_data = $file_data;
                $total_items = count($file_data);
            }

            // Initialize or retrieve error HTML from session
            if (!$is_chunk || $chunk_index == 0) {
                $_SESSION['import_error_html'] = '<h3 class="text-danger text-center">Below data is not inserted</h3></br>
                <table class="table table-bordered"><tr>
                    <th>' . __('Subject') . '</th>
                    <th>' . __('Name') . '</th>
                    <th>' . __('Email') . '</th>
                    <th>' . __('Phone') . '</th>
                </tr>';
                $_SESSION['import_error_flag'] = 0;
            }

            foreach ($process_data as $validationKey => $value) {
                $validator = \Validator::make([
                    'subject' => $value[$request->subject] ?? null,
                    'name'    => $value[$request->name] ?? null,
                    'email'   => $value[$request->email] ?? null,
                    'phone'   => $value[$request->phone] ?? null,
                ], [
                    'subject' => 'nullable|string|max:255',
                    'name'    => 'nullable|string|max:255',
                    'email'   => 'nullable|email|max:255',
                    'phone'   => 'required'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => $validator->errors()->first() . ' at row ' . ($chunk_index + $validationKey + 1),
                    ]);
                }
            }

            \Log::info("Lead Import Started: Chunk Index: {$chunk_index}, Total Data in Session: " . count($file_data));
            \Log::info("Lead Import Request Params: " . json_encode($request->only(['global_pipeline', 'global_stage', 'global_user', 'chunk_index', 'is_chunk'])));

            $pipeline = null;
            if ($request->has('global_pipeline') && !empty($request->global_pipeline)) {
                $pipeline = Pipeline::where('id', $request->global_pipeline)
                    ->where('workspace_id', $getActiveWorkSpace)
                    ->first();
                
                if (!$pipeline) {
                    \Log::error("Lead Import: Pipeline ID {$request->global_pipeline} not found for workspace {$getActiveWorkSpace}");
                    return response()->json([
                        'success' => false,
                        'message' => __('Selected pipeline not found or access denied.'),
                    ]);
                }
            }

            if (empty($pipeline)) {
                $user = Auth::user();
                if ($user->default_pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('workspace_id', $getActiveWorkSpace)->where('id', '=', $user->default_pipeline)->first();
                }
                if (empty($pipeline)) {
                    $pipeline = Pipeline::where('created_by', $creatorId)->where('workspace_id', $getActiveWorkSpace)->first();
                }
                \Log::info("Lead Import: Falling back to pipeline: " . ($pipeline ? $pipeline->id : 'NONE'));
            }

            if (!empty($pipeline)) {
                $stage = null;
                if ($request->has('global_stage') && !empty($request->global_stage)) {
                    // Try to find the stage strictly by ID and pipeline
                    $stage = LeadStage::where('id', $request->global_stage)
                        ->where('pipeline_id', $pipeline->id)
                        ->where('workspace_id', $getActiveWorkSpace)
                        ->first();
                    
                    if (!$stage) {
                        // Diagnostic check to see WHY it failed
                        $exists_at_all = LeadStage::find($request->global_stage);
                        $msg = "The selected stage ID ({$request->global_stage}) is not valid for pipeline {$pipeline->id}.";
                        if ($exists_at_all) {
                            if ($exists_at_all->pipeline_id != $pipeline->id) {
                                $msg = "The selected stage belongs to a different pipeline ({$exists_at_all->pipeline_id}).";
                            } elseif ($exists_at_all->workspace_id != $getActiveWorkSpace) {
                                $msg = "The selected stage belongs to a different workspace.";
                            }
                        }
                        
                        \Log::error("Lead Import: Stage Failure: " . $msg);
                        
                        return response()->json([
                            'success' => false,
                            'message' => __($msg . ' Please refresh and re-select.'),
                        ]);
                    }
                }

                if (empty($stage)) {
                    // If NO stage was selected, we pick the first one of the pipeline.
                    $stage = LeadStage::where('pipeline_id', $pipeline->id)->where('workspace_id', $getActiveWorkSpace)->orderBy('order')->first();
                    \Log::info("Lead Import Warning: global_stage missing or invalid, using first stage of pipeline: " . ($stage ? $stage->id : 'NONE'));
                }

                if (empty($stage)) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Please create stage for this pipeline.'),
                    ]);
                }
                
                \Log::info("Lead Import Resolved: Pipeline: {$pipeline->id}, Stage: {$stage->id}");

            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('Please create pipeline.'),
                ]);
            }

            foreach ($process_data as $key => $row) {
                $row_index = $chunk_index + $key;
                $email     = (isset($request->email) && $request->email != '' && isset($row[$request->email])) ? $row[$request->email] : '';
                $subject   = (isset($request->subject) && $request->subject != '' && isset($row[$request->subject])) ? $row[$request->subject] : 'New Lead';
                $name      = (isset($request->name) && $request->name != '' && isset($row[$request->name])) ? $row[$request->name] : '';
                $phone     = (isset($request->phone) && $request->phone != '' && isset($row[$request->phone])) ? $row[$request->phone] : '';

                if(empty($name)) {
                    $name = $phone;
                }
                if(empty($name)) {
                    $name = $subject;
                }

                $leads = [];
                if (!empty($email)) {
                    $leads = Lead::where('created_by', $creatorId)
                        ->where('workspace_id', $getActiveWorkSpace)
                        ->where('email', 'like', $email)
                        ->get();
                }

                if (empty($leads) || $leads->isEmpty()) {
                    try {
                        $users = null;
                        if (!empty($request->user) && isset($request->user[$key])) {
                            $users = User::find($request->user[$key]);
                        }

                        if (empty($users)) {
                            $users = User::find($request->global_user);
                        }

                        if (empty($users)) {
                            $users = User::where('created_by', Auth::user()->id)->first();
                        }

                        $lead = Lead::create([
                            'subject'      => $subject,
                            'name'         => $name,
                            'user_id'      => !empty($users) ? $users->id : Auth::user()->id,
                            'email'        => $email,
                            'phone'        => $phone,
                            'pipeline_id'  => $pipeline->id,
                            'stage_id'     => $stage->id,
                            'created_by'   => $creatorId,
                            'workspace_id' => $getActiveWorkSpace,
                            'date'         => date('Y-m-d'),
                        ]);
                        \Log::info("Lead Created: ID {$lead->id}, Subject: {$subject}, User ID: {$lead->user_id}, Stage ID: {$lead->stage_id}");

                        $usrLeads = [$creatorId];
                        if(!empty($users) && $users->id != $creatorId) {
                            $usrLeads[] = $users->id;
                        }

                        foreach ($usrLeads as $usrLead) {
                            UserLead::create([
                                'user_id' => $usrLead,
                                'lead_id' => $lead->id,
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Lead Import Error: ' . $e->getMessage());
                        $_SESSION['import_error_flag'] = 1;
                        $_SESSION['import_error_html'] .= '<tr>';
                        $_SESSION['import_error_html'] .= '<td>' . $subject . '</td>';
                        $_SESSION['import_error_html'] .= '<td>' . $name . '</td>';
                        $_SESSION['import_error_html'] .= '<td>' . (empty($email) ? '-' : $email) . '</td>';
                        $_SESSION['import_error_html'] .= '<td>' . (empty($phone) ? '-' : $phone) . '</td>';
                        $_SESSION['import_error_html'] .= '</tr>';
                    }
                } else {
                    $_SESSION['import_error_flag'] = 1;
                    $_SESSION['import_error_html'] .= '<tr>';
                    $_SESSION['import_error_html'] .= '<td>' . $subject . '</td>';
                    $_SESSION['import_error_html'] .= '<td>' . $name . '</td>';
                    $_SESSION['import_error_html'] .= '<td>' . (empty($email) ? '-' : $email) . '</td>';
                    $_SESSION['import_error_html'] .= '<td>' . (empty($phone) ? '-' : $phone) . '</td>';
                    $_SESSION['import_error_html'] .= '</tr>';
                }
            }

            $current_count = $chunk_index + count($process_data);
            $is_finished = ($current_count >= $total_items);

            if ($is_finished) {
                $_SESSION['import_error_html'] .= '</table><br />';
                $html = $_SESSION['import_error_html'];
                $flag = $_SESSION['import_error_flag'];
                unset($_SESSION['file_data']);
                unset($_SESSION['import_error_html']);
                unset($_SESSION['import_error_flag']);
            }

            return response()->json([
                'success' => true,
                'is_finished' => $is_finished,
                'total' => $total_items,
                'current' => $current_count,
                'html' => ($is_finished && $flag == 1),
                'response' => $is_finished ? ($flag == 1 ? $html : __('Data has been imported.')) : __('Processing...'),
            ]);
        } else {
            return response()->json(['success' => false, 'message' => __('Permission Denied')]);
        }
    }

    public function taskCreate($id)
    {
        if (Auth::user()->isAbleTo('lead task create')) {
            $lead = Lead::find($id);
            if ($lead && $lead->isAccessible()) {
                $priorities = LeadTask::$priorities;
                $status     = LeadTask::$status;
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
                if($usr->type == 'company' || $usr->type == 'client' || $usr->can('crm manage')) {
                    if($request->user_id && $request->user_id != $usr->id) {
                         $accessibleUserIds = $usr->getAccessibleUserIds();
                         if(!in_array($request->user_id, $accessibleUserIds)) {
                              return redirect()->back()->with('error', __('You can only assign to your team members.'));
                         }
                    }
                } else {
                     $request->merge(['user_id' => $usr->id]);
                }
                $getActiveWorkSpace = getActiveWorkSpace();
                $lead_users = $lead->users->pluck('id')->toArray();
                $usrs       = User::whereIN('id', $lead_users)->get()->pluck('email', 'id')->toArray();

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name'      => 'required|string|max:255',
                        'date'      => 'required|date',
                        'time'      => 'required|date_format:H:i',
                        'priority'  => 'required|in:1,2,3',
                        'status'    => 'required|in:pending,in_progress,done,overdue',
                        'user_id'   => 'required|exists:users,id',
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
                if (!empty(company_setting('New Task')) && company_setting('New Task')  == true) {
                    $tArr = [
                        'lead_name' => $lead->name,
                        'lead_pipeline' => $lead->pipeline->name,
                        'lead_stage' => $lead->stage->name,
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
                $status     = LeadTask::$status;
                $task       = LeadTask::find($task_id);
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
                        'name'      => 'required|string|max:255',
                        'date'      => 'required|date',
                        'time'      => 'required',
                        'priority'  => 'required|in:1,2,3',
                        'status'    => 'required|in:pending,in_progress,done,overdue',
                        'user_id'   => 'required|exists:users,id',
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
                    if($task->date && strtotime($task->date) < strtotime(date('Y-m-d'))) {
                        $task->status = 'overdue';
                    }
                } else {
                    // Task was not done, clicking it means make it done
                    $task->status = 'done';
                }
                $task->save();

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

            if (empty($ids)) {
                return response()->json(['success' => false, 'message' => __('No leads selected.')]);
            }

            if (count($ids) > 500) {
                return response()->json(['success' => false, 'message' => __('Maximum 500 leads can be processed at once.')]);
            }

            $leads = Lead::whereIn('id', $ids)->where('workspace_id', getActiveWorkSpace())->get();

            foreach ($leads as $lead) {
                // Ensure the user has access to these leads
                if (!$lead->isAccessible()) continue;

                if ($action == 'delete') {
                    if ($usr->isAbleTo('lead delete')) {
                        $lead->delete();
                    }
                } elseif ($action == 'change_stage') {
                    if ($usr->isAbleTo('lead edit')) {
                        $customFieldErrors = $this->validateLeadCustomFields($lead, $value);
                        if (!empty($customFieldErrors)) {
                            return response()->json(['success' => false, 'message' => $customFieldErrors[0]]);
                        }
                        $lead->stage_id = $value;
                        $lead->save();
                    }
                } elseif ($action == 'change_owner') {
                    if ($usr->isAbleTo('lead edit')) {
                        // Reassign leads to another user
                        $lead->users()->sync([$value]);
                    }
                }
            }

            return response()->json(['success' => true, 'message' => __('Bulk action completed successfully.')]);
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
            $ids = implode(',', $ids);
            
            $getActiveWorkSpace = getActiveWorkSpace();
             $users = User::where('created_by', creatorId())->where('workspace_id', $getActiveWorkSpace)->get()->pluck('name', 'id');

            return view('lead::leads.bulk_create', compact('ids', 'users'));
        }
        return response()->json(['error' => __('Permission Denied.')]);
    }

    public function bulkTaskReminderStore(Request $request)
    {
        if (\Auth::user()->isAbleTo('lead edit')) {
            $ids = explode(',', $request->ids);
            if (empty($ids)) {
                 return redirect()->back()->with('error', __('Please select at least one lead.'));
            }

            $getActiveWorkSpace = getActiveWorkSpace();
            $leads = Lead::whereIn('id', $ids)->where('workspace_id', $getActiveWorkSpace)->get();
            $count = 0;

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
                    $userId = ($request->task_user_id == 'lead_owner') ? $lead->user_id : $request->task_user_id;
                    
                    if(!$userId) $userId = \Auth::user()->id; // Fallback

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
                return redirect()->back()->with('success', __('Tasks created successfully for '.$count.' leads.'));

            } else {
                 $validator = \Validator::make(
                    $request->all(),
                    [
                        'reminder_date' => 'required',
                        'reminder_description' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    return redirect()->back()->with('error', $validator->errors()->first());
                }

                 foreach ($leads as $lead) {
                    $userId = ($request->reminder_user_id == 'lead_owner') ? $lead->user_id : $request->reminder_user_id;
                    if(!$userId) $userId = \Auth::user()->id; // Fallback

                    Reminder::create([
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
                        'remark' => json_encode(['title' => 'Bulk Reminder']), // Description is in Reminder itself
                    ]);

                    $count++;
                }
                 return redirect()->back()->with('success', __('Reminders created successfully for '.$count.' leads.'));
            }
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }
    public function kanbanBatch(Request $request)
    {
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
            ->where('order', '<=', $targetStage->order)
            ->pluck('id')
            ->toArray();

        $fields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', $getActiveWorkSpace)->get();
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
}
