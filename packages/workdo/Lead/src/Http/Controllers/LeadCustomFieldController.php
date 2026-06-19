<?php

namespace Workdo\Lead\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Lead\Entities\LeadCustomField;
use Illuminate\Support\Facades\Auth;

class LeadCustomFieldController extends Controller
{
    public function index()
    {
        if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
        {
            return redirect()->route('lead-builder.index');
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function create(Request $request)
    {
        if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
        {
            $types = LeadCustomField::$fieldTypes;
            $pipelineId = $request->pipeline_id;
            
            $pipelines = \Workdo\Lead\Entities\Pipeline::where('workspace_id', getActiveWorkSpace());
            if ($pipelineId) {
                $pipelines = $pipelines->where('id', $pipelineId);
            }
            $pipelines = $pipelines->with('leadStages')->get();
            $roles = \App\Models\Role::pluck('name', 'id');
            
            return view('lead::custom_fields.create', compact('types', 'pipelines', 'roles', 'pipelineId'));
        }
    }

    public function store(Request $request)
    {
         if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
        {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'type' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $customField = new LeadCustomField();
            $customField->name = $request->name;
            $customField->type = $request->type;
            $customField->options = $request->options;
            $customField->order = 0;
            $customField->is_required = $request->has('is_required') ? 1 : 0;
            $customField->workspace_id = getActiveWorkSpace();
            $customField->created_by = Auth::user()->id;
            $customField->pipeline_id = $request->pipeline_id;
            
            // Process stage configuration
            $stageConfig = $request->stage_config ?? [];
            $visibleStages = [];
            $requiredStages = [];
            
            foreach ($stageConfig as $stageId => $config) {
                if ($config === 'visible' || $config === 'required') {
                    $visibleStages[] = (string)$stageId;
                }
                if ($config === 'required') {
                    $requiredStages[] = (string)$stageId;
                }
            }
            
            $customField->visible_stages = !empty($visibleStages) ? $visibleStages : null;
            $customField->required_stages = !empty($requiredStages) ? $requiredStages : null;

            // Process stage-wise minimum values for number fields
            $stageMinValues = [];
            if ($request->type === 'number' && is_array($request->stage_min_values)) {
                foreach ($request->stage_min_values as $stageId => $val) {
                    if ($val !== null && $val !== '') {
                        $stageMinValues[(string)$stageId] = (float)$val;
                    }
                }
            }
            $customField->stage_min_values = !empty($stageMinValues) ? $stageMinValues : null;

            $customField->visible_roles = $request->visible_roles;   // Array
            $customField->is_filterable = $request->has('is_filterable') ? 1 : 0;
            $customField->icon          = $request->icon;
            
            // API Integration fields
            $customField->api_url = $request->api_url;
            $customField->api_method = $request->api_method;
            $customField->api_trigger_stage_id = $request->api_trigger_stage_id;
            $customField->api_response_key = $request->api_response_key;

            $customField->save();

            return redirect()->route('lead-builder.index', ['pipeline_id' => $request->pipeline_id])->with('success', __('Custom Field successfully created.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function edit(Request $request, $id)
    {
        if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
        {
            $customField = LeadCustomField::find($id);
            
            if (!$customField) {
                return redirect()->route('lead-custom-fields.index')->with('error', __('Custom Field not found.'));
            }
            
            $types = LeadCustomField::$fieldTypes;
            $pipelineId = $customField->pipeline_id;
            
            $pipelines = \Workdo\Lead\Entities\Pipeline::where('workspace_id', getActiveWorkSpace());
            if ($pipelineId) {
                $pipelines = $pipelines->where('id', $pipelineId);
            }
            $pipelines = $pipelines->with('leadStages')->get();
            $roles = \App\Models\Role::pluck('name', 'id');
            
            return view('lead::custom_fields.edit', compact('customField', 'types', 'pipelines', 'roles', 'pipelineId'));
        }
        
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function update(Request $request, $id)
    {
         if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
        {
            $customField = LeadCustomField::find($id);
            
            if (!$customField) {
                return redirect()->route('lead-custom-fields.index')->with('error', __('Custom Field not found.'));
            }
            
            $customField->name = $request->name;
            $customField->type = $request->type;
            $customField->options = $request->options;
            $customField->is_required = $request->has('is_required') ? 1 : 0;
            
            // Process stage configuration
            $stageConfig = $request->stage_config ?? [];
            $visibleStages = [];
            $requiredStages = [];
            
            foreach ($stageConfig as $stageId => $config) {
                if ($config === 'visible' || $config === 'required') {
                    $visibleStages[] = (string)$stageId;
                }
                if ($config === 'required') {
                    $requiredStages[] = (string)$stageId;
                }
            }
            
            $customField->visible_stages = !empty($visibleStages) ? $visibleStages : null;
            $customField->required_stages = !empty($requiredStages) ? $requiredStages : null;

            // Process stage-wise minimum values for number fields
            $stageMinValues = [];
            if ($request->type === 'number' && is_array($request->stage_min_values)) {
                foreach ($request->stage_min_values as $stageId => $val) {
                    if ($val !== null && $val !== '') {
                        $stageMinValues[(string)$stageId] = (float)$val;
                    }
                }
            }
            $customField->stage_min_values = !empty($stageMinValues) ? $stageMinValues : null;

            $customField->visible_roles = $request->visible_roles;
            $customField->is_filterable = $request->has('is_filterable') ? 1 : 0;
            $customField->icon          = $request->icon;

            // API Integration fields
            $customField->api_url = $request->api_url;
            $customField->api_method = $request->api_method;
            $customField->api_trigger_stage_id = $request->api_trigger_stage_id;
            $customField->api_response_key = $request->api_response_key;

            $customField->save();
            return redirect()->route('lead-builder.index', ['pipeline_id' => $customField->pipeline_id])->with('success', __('Custom Field successfully updated.'));
        }
        
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function destroy($id)
    {
         if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
        {
            $customField = LeadCustomField::find($id);
            
            if (!$customField) {
                return redirect()->route('lead-builder.index')->with('error', __('Custom Field not found.'));
            }
            
            $pipelineId = $customField->pipeline_id;
            $customField->delete();
            return redirect()->route('lead-builder.index', ['pipeline_id' => $pipelineId])->with('success', __('Custom Field successfully deleted.'));
        }
        
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function duplicate($id)
    {
        if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
        {
            $originalField = LeadCustomField::find($id);
            
            if (!$originalField) {
                return redirect()->route('lead-custom-fields.index')->with('error', __('Custom Field not found.'));
            }
            
            // Don't allow duplicating system fields
            if ($originalField->is_system) {
                return redirect()->back()->with('error', __('Cannot duplicate system fields.'));
            }
            
            // Create duplicate
            $newField = $originalField->replicate();
            $newField->name = $originalField->name . ' (Copy)';
            $newField->created_at = now();
            $newField->updated_at = now();
            $newField->save();
            
            return redirect()->back()->with('success', __('Custom Field duplicated successfully.'));
        }
        
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function builder(Request $request)
    {
        if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
        {
            $pipelines = \Workdo\Lead\Entities\Pipeline::where('workspace_id', getActiveWorkSpace())->with('leadStages')->get();
            $pipelineId = $request->pipeline_id ?? ($pipelines->first()?->id);
            $selectedPipeline = $pipelines->where('id', $pipelineId)->first() ?? $pipelines->first();

            if ($selectedPipeline) {
                \Workdo\Lead\Entities\LeadSection::ensurePipelineLayout($selectedPipeline->id, getActiveWorkSpace());
                
                $sections = \Workdo\Lead\Entities\LeadSection::where('workspace_id', getActiveWorkSpace())
                    ->where('pipeline_id', $selectedPipeline->id)
                    ->with(['fields' => function($q) use ($selectedPipeline) {
                        $q->where('pipeline_id', $selectedPipeline->id)->orderBy('order');
                    }])
                    ->orderBy('order')
                    ->get();
            } else {
                $sections = collect();
            }
            
            return view('lead::custom_fields.builder', compact('sections', 'pipelines', 'selectedPipeline'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function saveBuilder(Request $request)
    {
        if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
        {
            $sections = $request->sections; // Array of section objects
            
            foreach ($sections as $secIndex => $sectionData) {
                // Update Section Order
                $section = \Workdo\Lead\Entities\LeadSection::find($sectionData['id']);
                if ($section) {
                    $section->order = $secIndex;
                    $section->save();

                    // Update Fields in this section
                    if (isset($sectionData['fields'])) {
                        foreach ($sectionData['fields'] as $fieldIndex => $fieldData) {
                            $field = LeadCustomField::find($fieldData['id']);
                            if ($field) {
                                $field->section_id = $section->id;
                                $field->order = $fieldIndex;
                                $field->save();
                            }
                        }
                    }
                }
            }
            return response()->json(['success' => __('Layout saved successfully.')]);
        }
        return response()->json(['error' => __('Permission Denied.')], 403);
    }

    public function sectionStore(Request $request)
    {
        if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
        {
            $section = new \Workdo\Lead\Entities\LeadSection();
            $section->name = $request->name;
            $section->columns = $request->columns ?? 3;
            $section->layout_type = $request->layout_type ?? 'section';
            $section->order = 100; // Will be sorted by builder later
            $section->workspace_id = getActiveWorkSpace();
            $section->pipeline_id = $request->pipeline_id;
            
            // API Integrations
            $section->api_url = $request->api_url;
            $section->api_method = $request->api_method ?? 'GET';
            $section->api_trigger_stage_id = $request->api_trigger_stage_id;
            $section->api_response_mapping = $request->api_response_mapping;

            // Stage Visibility
            $section->visible_stages = !empty($request->visible_stages) ? $request->visible_stages : null;

            $section->save();

            return redirect()->back()->with('success', __('Section created successfully.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function sectionUpdate(Request $request, $id)
    {
        if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
        {
            $section = \Workdo\Lead\Entities\LeadSection::find($id);
            $section->name = $request->name;
            $section->columns = $request->columns;
            $section->layout_type = $request->layout_type ?? 'section';
            
            // API Integrations
            $section->api_url = $request->api_url;
            $section->api_method = $request->api_method ?? 'GET';
            $section->api_trigger_stage_id = $request->api_trigger_stage_id;
            $section->api_response_mapping = $request->api_response_mapping;

            // Stage Visibility
            $section->visible_stages = !empty($request->visible_stages) ? $request->visible_stages : null;

            $section->save();

            return redirect()->back()->with('success', __('Section updated successfully.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function sectionDestroy($id)
    {
        if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
        {
            $section = \Workdo\Lead\Entities\LeadSection::find($id);
            // Move fields to a default section or nullify needed? 
            // For now, let's forbid deleting system sections.
            if ($section->is_system) {
                return redirect()->back()->with('error', __('Cannot delete system sections.'));
            }
            // Move fields to General or make them orphaned (so they show up in "Unassigned" bin in builder)
            LeadCustomField::where('section_id', $id)->update(['section_id' => null]);
            $section->delete();

            return redirect()->back()->with('success', __('Section deleted successfully. Any fields were moved to Unassigned.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function sectionCopy(Request $request, $id)
    {
        if (Auth::user()->type == 'company' || Auth::user()->type == 'super admin') {
            $section = \Workdo\Lead\Entities\LeadSection::find($id);
            if (!$section) {
                return redirect()->back()->with('error', __('Section not found.'));
            }

            $targetPipelineId = $request->target_pipeline_id;
            if (!$targetPipelineId) {
                return redirect()->back()->with('error', __('Please select a target pipeline.'));
            }

            // Create target section
            $newSection = new \Workdo\Lead\Entities\LeadSection();
            $newSection->name = $section->name;
            $newSection->pipeline_id = $targetPipelineId;
            $newSection->workspace_id = getActiveWorkSpace();
            $newSection->columns = $section->columns;
            $newSection->layout_type = $section->layout_type;
            $newSection->api_url = $section->api_url;
            $newSection->api_method = $section->api_method;
            $newSection->api_trigger_stage_id = null; // trigger stage belongs to different pipeline, so set to null initially
            $newSection->api_response_mapping = $section->api_response_mapping;
            $newSection->order = $section->order;
            $newSection->is_system = 0; // Copied sections are custom
            $newSection->visible_stages = $section->visible_stages; // Copy stage visibility
            $newSection->save();

            // Copy all custom fields under this section using replicate()
            $fields = \Workdo\Lead\Entities\LeadCustomField::where('section_id', $id)->get();
            foreach ($fields as $field) {
                $newField = $field->replicate();
                $newField->section_id = $newSection->id;
                $newField->pipeline_id = $targetPipelineId;
                $newField->workspace_id = getActiveWorkSpace();
                $newField->is_system = 0;
                $newField->save();
            }

            return redirect()->back()->with('success', __('Section and fields copied successfully.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }
}
