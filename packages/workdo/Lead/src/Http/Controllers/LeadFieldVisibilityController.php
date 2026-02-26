<?php

namespace Workdo\Lead\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workdo\Lead\Entities\LeadFieldVisibility;
use Workdo\Lead\Entities\LeadUtility; // To be created/updated

class LeadFieldVisibilityController extends Controller
{
    /**
     * Store or update visibility settings.
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('lead manage') && (Auth::user()->type == 'super admin' || Auth::user()->type == 'company')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'field_name' => 'required|string',
                    'encryption_type' => 'required|in:none,mask,hide',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }

            $condition = [
                'field_name' => $request->field_name,
                'role_id' => $request->role_id,
                'pipeline_id' => $request->pipeline_id,
                'workspace_id' => getActiveWorkSpace(),
            ];
            
            $stageIds = $request->stage_id;
            if(is_array($stageIds)){
                $stageIds = implode(',', $stageIds);
            }
            
            $visibility = LeadFieldVisibility::updateOrCreate(
                $condition,
                [
                    'stage_id' => $stageIds,
                    'encryption_type' => $request->encryption_type,
                    'masking_type' => $request->masking_type,
                    'created_by' => creatorId(),
                ]
            );

            return redirect()->back()->with('success', __('Visibility settings updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function edit($id)
    {
        if (Auth::user()->isAbleTo('lead manage') && (Auth::user()->type == 'super admin' || Auth::user()->type == 'company')) {
            $visibility = LeadFieldVisibility::find($id);
            $roles = \App\Models\Role::where('created_by', creatorId())->get();
            $pipelines = \Workdo\Lead\Entities\Pipeline::where('created_by', creatorId())->where('workspace_id', getActiveWorkSpace())->get();
            
            // Standard Fields
            $fields = [
                'name' => 'Name',
                'email' => 'Email',
                'phone' => 'Phone',
                'subject' => 'Subject',
            ];
            
            // Add Custom Fields
            $customFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', getActiveWorkSpace())->get();
            foreach ($customFields as $field) {
                $fields['custom_' . $field->id] = $field->name . ' (Custom)';
            }

            return view('lead::crm.visibility_edit', compact('visibility', 'roles', 'pipelines', 'fields'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('lead manage') && (Auth::user()->type == 'super admin' || Auth::user()->type == 'company')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'field_name' => 'required|string',
                    'encryption_type' => 'required|in:none,mask,hide',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }

            $visibility = LeadFieldVisibility::find($id);
            if ($visibility) {
                $visibility->field_name = $request->field_name;
                $visibility->role_id = $request->role_id;
                $visibility->pipeline_id = $request->pipeline_id;
                
                $stageIds = $request->stage_id;
                if(is_array($stageIds)){
                    $stageIds = implode(',', $stageIds);
                }
                $visibility->stage_id = $stageIds;
                
                $visibility->encryption_type = $request->encryption_type;
                $visibility->masking_type = $request->masking_type;
                $visibility->save();
                
                return redirect()->route('leads.visibility.settings')->with('success', __('Visibility rule updated successfully.'));
            }
            return redirect()->back()->with('error', __('Rule not found.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    /**
     * Reveal a masked field securely.
     */
    public function revealField(Request $request, $leadId, $fieldName)
    {
        $lead = \Workdo\Lead\Entities\Lead::find($leadId);
        
        if(!$lead || !$lead->isAccessible()){
             return response()->json(['error' => __('Permission Denied'), 'is_success' => false], 403);
        }

        // Log the access to activity
        \Workdo\Lead\Entities\LeadActivityLog::create([
            'user_id' => \Auth::id(),
            'lead_id' => $leadId,
            'log_type' => 'Revealed Protected Field',
            'remark' => json_encode([
                'field' => $fieldName,
                'action' => 'revealed',
                'timestamp' => now()->toDateTimeString()
            ]),
        ]);

        // Return real value
        $value = $lead->{$fieldName} ?? '';
        
        return response()->json(['value' => $value, 'is_success' => true]);
    }
    
    public function destroy($id)
    {
        if (Auth::user()->isAbleTo('lead manage') && (Auth::user()->type == 'super admin' || Auth::user()->type == 'company')) {
            $visibility = LeadFieldVisibility::find($id);
            if ($visibility) {
                $visibility->delete();
                return redirect()->back()->with('success', __('Visibility rule deleted successfully.'));
            }
            return redirect()->back()->with('error', __('Rule not found.'));
        }
        return redirect()->back()->with('error', __('Permission Denied.'));
    }
}
