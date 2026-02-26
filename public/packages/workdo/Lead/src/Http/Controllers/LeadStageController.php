<?php

namespace Workdo\Lead\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Lead\Entities\Lead;
use Workdo\Lead\Entities\LeadStage;
use Workdo\Lead\Entities\Pipeline;
use Workdo\Lead\Events\CreateLeadStage;
use Workdo\Lead\Events\DestroyLeadStage;
use Workdo\Lead\Events\LeadStageChange;
use Workdo\Lead\Events\UpdateLeadStage;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\Entities\Department;
use Workdo\Lead\Entities\StageCustomField;
use Workdo\Lead\Entities\PipelineStageAutomation;

class LeadStageController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('leadstages manage')) {
            $lead_stages = LeadStage::select('lead_stages.*', 'pipelines.name as pipeline')
                ->join('pipelines', 'pipelines.id', '=', 'lead_stages.pipeline_id')
                ->where('pipelines.created_by', '=', creatorId())
                ->where('lead_stages.created_by', '=', creatorId())->where('lead_stages.workspace_id', '=', getActiveWorkSpace())
                ->orderBy('lead_stages.pipeline_id')
                ->orderBy('lead_stages.order')
                ->get();
            $pipelines   = [];

            foreach ($lead_stages as $lead_stage) {
                if (!array_key_exists($lead_stage->pipeline_id, $pipelines)) {
                    $pipelines[$lead_stage->pipeline_id]                = [];
                    $pipelines[$lead_stage->pipeline_id]['name']        = $lead_stage['pipeline'];
                    $pipelines[$lead_stage->pipeline_id]['lead_stages'] = [];
                }
                $pipelines[$lead_stage->pipeline_id]['lead_stages'][] = $lead_stage;
            }

            return view('lead::lead_stages.index')->with('pipelines', $pipelines);
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
        if (Auth::user()->isAbleTo('leadstages create')) {
            $pipelines = Pipeline::where('created_by', '=', creatorId())->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');

            return view('lead::lead_stages.create')->with('pipelines', $pipelines);
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
        if (Auth::user()->isAbleTo('leadstages create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name'          => 'required|string|max:30',
                    'pipeline_id'   => 'required|integer|exists:pipelines,id',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('lead-stages.index')->with('error', $messages->first());
            }
            $lead_stage              = new LeadStage();
            $lead_stage->name        = $request->name;
            $lead_stage->pipeline_id = $request->pipeline_id;
            $lead_stage->created_by  = creatorId();
            $lead_stage->workspace_id  = getActiveWorkSpace();
            $lead_stage->save();

            event(new CreateLeadStage($request, $lead_stage));

            return redirect()->route('lead-stages.index')->with('success', __('The lead stage has been created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('lead::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(LeadStage $leadStage)
    {
        if (Auth::user()->isAbleTo('leadstages edit')) {
            if ($leadStage->created_by == creatorId() && $leadStage->workspace_id == getActiveWorkSpace()) {
                $pipelines = Pipeline::where('created_by', '=', creatorId())->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
                
                // Fetch Departments for Automation
                $departments = [];
                if(module_is_active('Hrm')){
                    $departments = Department::where('created_by', creatorId())->pluck('name', 'id');
                }

                // Fetch Custom Fields for Visibility
                $customFields = [];
                if(module_is_active('CustomField')){
                     $customFields = \Workdo\CustomField\Entities\CustomField::where('module', 'Lead')->where('created_by', creatorId())->where('workspace_id', getActiveWorkSpace())->get();
                }

                // Fetch existing settings
                $stageCustomFields = StageCustomField::where('stage_id', $leadStage->id)->where('entity_type', 'lead')->get()->pluck('is_required', 'custom_field_id')->toArray();
                $automation = PipelineStageAutomation::where('stage_id', $leadStage->id)->where('entity_type', 'lead')->first();

                return view('lead::lead_stages.edit', compact('leadStage', 'pipelines', 'departments', 'customFields', 'stageCustomFields', 'automation'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, LeadStage $leadStage)
    {
        if (Auth::user()->isAbleTo('leadstages edit')) {

            if ($leadStage->created_by == creatorId() && $leadStage->workspace_id == getActiveWorkSpace()) {

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name'          => 'required|string|max:30',
                        'pipeline_id'   => 'required|integer|exists:pipelines,id',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('lead-stages.index')->with('error', $messages->first());
                }

                $leadStage->name        = $request->name;
                $leadStage->pipeline_id = $request->pipeline_id;
                $leadStage->save();

                // Save Custom Field Visibility
                StageCustomField::where('stage_id', $leadStage->id)->where('entity_type', 'lead')->delete();
                if($request->has('custom_fields')){
                    foreach($request->custom_fields as $cf_id){
                        $isRequired = isset($request->custom_fields_required[$cf_id]) ? 1 : 0;
                        StageCustomField::create([
                            'stage_id' => $leadStage->id,
                            'custom_field_id' => $cf_id,
                            'entity_type' => 'lead',
                            'is_required' => $isRequired,
                            'created_by' => creatorId(),
                            'workspace_id' => getActiveWorkSpace(),
                        ]);
                    }
                }

                // Save Automation
                PipelineStageAutomation::where('stage_id', $leadStage->id)->where('entity_type', 'lead')->delete();
                if($request->filled('target_department_id') || $request->is_auto_task == 1 || $request->is_auto_reminder == 1){
                    PipelineStageAutomation::create([
                        'pipeline_id' => $leadStage->pipeline_id,
                        'stage_id' => $leadStage->id,
                        'entity_type' => 'lead',
                        'target_department_id' => $request->target_department_id,
                        'is_auto_task' => $request->is_auto_task ?? 0,
                        'auto_task_name' => $request->auto_task_name,
                        'auto_task_priority' => $request->auto_task_priority,
                        'auto_task_duration' => $request->auto_task_duration,
                        'is_auto_reminder' => $request->is_auto_reminder ?? 0,
                        'auto_reminder_title' => $request->auto_reminder_title,
                        'auto_reminder_duration' => $request->auto_reminder_duration,
                        'created_by' => creatorId(),
                        'workspace_id' => getActiveWorkSpace(),
                    ]);
                }

                event(new UpdateLeadStage($request, $leadStage));

                return redirect()->route('lead-stages.index')->with('success', __('The lead stage details are updated successfully.'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(LeadStage $leadStage)
    {
        if (Auth::user()->isAbleTo('leadstages delete')) {
            $leads = Lead::where('stage_id', '=', $leadStage->id)->count();
            if ($leads == 0) {
                $leadStage->delete();

                event(new DestroyLeadStage($leads));

                return redirect()->route('lead-stages.index')->with('success', __('The lead stage has been deleted.'));
            } else {
                return redirect()->back()->with('error', 'Please remove Lead from stage:' . $leadStage->name);
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function order(Request $request)
    {
        try {
            $post = $request->all();
            foreach ($post['order'] as $key => $item) {
                $lead_stage        = LeadStage::where('id', '=', $item)->first();
                $lead_stage->order = $key;
                $lead_stage->save();

                event(new LeadStageChange($post, $lead_stage));
            }
            return response()->json(['success' => __('Lead stage moved successfully.')]);
        } catch (\Throwable $th) {
            return response()->json(['error' => __('Something went wrong.')]);
        }
    }
}
