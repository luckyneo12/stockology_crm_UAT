<?php

namespace Workdo\Lead\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Lead\Entities\Deal;
use Workdo\Lead\Entities\DealStage;
use Workdo\Lead\Entities\Pipeline;
use Workdo\Lead\Events\CreateDealStage;
use Workdo\Lead\Events\DealStageChange;
use Workdo\Lead\Events\DestroyDealStage;
use Workdo\Lead\Events\UpdateDealStage;
use Illuminate\Support\Facades\Auth;
use Workdo\Hrm\Entities\Department;
use Workdo\Lead\Entities\StageCustomField;
use Workdo\Lead\Entities\PipelineStageAutomation;

class DealStageController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (Auth::user()->isAbleTo('dealstages manage')) {
            $stages    = DealStage::select('deal_stages.*', 'pipelines.name as pipeline')
                ->join('pipelines', 'pipelines.id', '=', 'deal_stages.pipeline_id')
                ->where('pipelines.created_by', '=', creatorId())
                ->where('deal_stages.created_by', '=', creatorId())
                ->orderBy('deal_stages.pipeline_id')->where('deal_stages.workspace_id', getActiveWorkSpace())
                ->orderBy('deal_stages.order')
                ->get();
            $pipelines = [];

            foreach ($stages as $stage) {
                if (!array_key_exists($stage->pipeline_id, $pipelines)) {
                    $pipelines[$stage->pipeline_id]           = [];
                    $pipelines[$stage->pipeline_id]['name']   = $stage['pipeline'];
                    $pipelines[$stage->pipeline_id]['stages'] = [];
                }
                $pipelines[$stage->pipeline_id]['stages'][] = $stage;
            }

            return view('lead::deal_stages.index')->with('pipelines', $pipelines);
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
        if (Auth::user()->isAbleTo('dealstages create')) {
            $pipelines = Pipeline::where('created_by', '=', creatorId())->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');

            return view('lead::deal_stages.create')->with('pipelines', $pipelines);
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
        if (Auth::user()->isAbleTo('dealstages create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name'          => 'required|string|max:30',
                    'pipeline_id'   => 'required|integer|exists:pipelines,id',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('stages.index')->with('error', $messages->first());
            }
            $stage              = new DealStage();
            $stage->name        = $request->name;
            $stage->pipeline_id = $request->pipeline_id;
            $stage->created_by  = creatorId();
            $stage->workspace_id = getActiveWorkSpace();
            $stage->save();

            event(new CreateDealStage($request, $stage));

            return redirect()->route('deal-stages.index')->with('success', __('The deal stage has been created successfully.'));
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
    public function edit(DealStage $dealStage)
    {

        if (Auth::user()->isAbleTo('dealstages edit')) {
            if ($dealStage->created_by == creatorId() && $dealStage->workspace_id == getActiveWorkSpace()) {
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
                $stageCustomFields = StageCustomField::where('stage_id', $dealStage->id)->where('entity_type', 'deal')->get()->pluck('is_required', 'custom_field_id')->toArray();
                $automation = PipelineStageAutomation::where('stage_id', $dealStage->id)->where('entity_type', 'deal')->first();

                return view('lead::deal_stages.edit', compact('dealStage', 'pipelines', 'departments', 'customFields', 'stageCustomFields', 'automation'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
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
    public function update(Request $request, DealStage $dealStage)
    {
        if (Auth::user()->isAbleTo('dealstages edit')) {

            if ($dealStage->created_by == creatorId() && $dealStage->workspace_id == getActiveWorkSpace()) {

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name'          => 'required|string|max:30',
                        'pipeline_id'   => 'required|integer|exists:pipelines,id',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('deal-stages.index')->with('error', $messages->first());
                }

                $dealStage->name        = $request->name;
                $dealStage->pipeline_id = $request->pipeline_id;
                $dealStage->save();

                // Save Custom Field Visibility
                StageCustomField::where('stage_id', $dealStage->id)->where('entity_type', 'deal')->delete();
                if($request->has('custom_fields')){
                    foreach($request->custom_fields as $cf_id){
                        $isRequired = isset($request->custom_fields_required[$cf_id]) ? 1 : 0;
                        StageCustomField::create([
                            'stage_id' => $dealStage->id,
                            'custom_field_id' => $cf_id,
                            'entity_type' => 'deal',
                            'is_required' => $isRequired,
                            'created_by' => creatorId(),
                            'workspace_id' => getActiveWorkSpace(),
                        ]);
                    }
                }

                // Save Automation
                PipelineStageAutomation::where('stage_id', $dealStage->id)->where('entity_type', 'deal')->delete();
                if($request->filled('target_department_id') || $request->is_auto_task == 1 || $request->is_auto_reminder == 1){
                    PipelineStageAutomation::create([
                        'pipeline_id' => $dealStage->pipeline_id,
                        'stage_id' => $dealStage->id,
                        'entity_type' => 'deal',
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

                event(new UpdateDealStage($request, $dealStage));

                return redirect()->route('deal-stages.index')->with('success', __('The deal stage details are updated successfully.'));
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
    public function destroy($id)
    {
        if (Auth::user()->isAbleTo('dealstages delete')) {
            $stage = DealStage::find($id);
            if ($stage->created_by == creatorId() && $stage->workspace_id == getActiveWorkSpace()) {
                $deals = Deal::where('stage_id', '=', $stage->id)->where('created_by', '=', $stage->created_by)->count();

                if ($deals == 0) {
                    $stage->delete();

                    event(new DestroyDealStage($stage));

                    return redirect()->route('deal-stages.index')->with('success', __('The deal stage has been deleted.'));
                } else {
                    return redirect()->route('deal-stages.index')->with('error', __('There are some deals on stage, please remove it first.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
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
                $stage        = DealStage::where('id', '=', $item)->first();
                $stage->order = $key;
                $stage->save();
    
                event(new DealStageChange($post, $stage));
            }
            return response()->json(['success' => __('Deal stage moved successfully.')]);
        } catch (\Throwable $th) {
            return response()->json(['error' => __('Something went wrong.')]);
        }
    }

    public function json(Request $request)
    {
        $stage = new DealStage();
        if ($request->pipeline_id) {
            $stage = $stage->where('pipeline_id', '=', $request->pipeline_id);
            $stage = $stage->get()->pluck('name', 'id');
        } else {
            $stage = [];
        }

        return response()->json($stage);
    }
}
