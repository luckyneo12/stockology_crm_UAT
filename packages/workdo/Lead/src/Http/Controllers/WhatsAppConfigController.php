<?php

namespace Workdo\Lead\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Workdo\Lead\Entities\WhatsAppConfig;
use Workdo\Lead\Entities\Pipeline;
use Workdo\Lead\Entities\LeadStage;
use Illuminate\Support\Facades\Auth;

class WhatsAppConfigController extends Controller
{
    private function isCompany()
    {
        $user = Auth::user();
        return $user->type == 'company' || $user->type == 'super admin';
    }

    public function index()
    {
        if (!$this->isCompany()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $configs = WhatsAppConfig::where('workspace_id', getActiveWorkSpace())->get();
        return view('lead::whatsapp.config_index', compact('configs'));
    }

    public function create()
    {
        if (!$this->isCompany()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $pipelines = Pipeline::where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
        $stages = [];
        $firstPipeline = $pipelines->keys()->first();
        if ($firstPipeline) {
            $stages = LeadStage::where('pipeline_id', $firstPipeline)
                ->where('workspace_id', getActiveWorkSpace())
                ->get()
                ->pluck('name', 'id')
                ->toArray();
        }

        $departments = [];
        if (module_is_active('Hrm') && class_exists('\Workdo\Hrm\Entities\Department')) {
            $departments = \Workdo\Hrm\Entities\Department::where('workspace', getActiveWorkSpace())
                ->get()
                ->pluck('name', 'id')
                ->toArray();
        }

        return view('lead::whatsapp.config_create', compact('pipelines', 'stages', 'departments'));
    }

    public function store(Request $request)
    {
        if (!$this->isCompany()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'connection_type' => 'required|in:meta_cloud,qr_session',
            'phone_number_id' => 'required_if:connection_type,meta_cloud|nullable|string|max:255',
            'business_account_id' => 'required_if:connection_type,meta_cloud|nullable|string|max:255',
            'access_token' => 'required_if:connection_type,meta_cloud|nullable|string',
            'verify_token' => 'required_if:connection_type,meta_cloud|nullable|string|max:255',
            'pipeline_id' => 'required|integer',
            'stage_id' => 'required|integer',
            'department_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        WhatsAppConfig::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'connection_type' => $request->connection_type,
            'phone_number_id' => $request->phone_number_id,
            'business_account_id' => $request->business_account_id,
            'access_token' => $request->access_token,
            'verify_token' => $request->verify_token,
            'department_id' => $request->department_id,
            'pipeline_id' => $request->pipeline_id,
            'stage_id' => $request->stage_id,
            'workspace_id' => getActiveWorkSpace(),
            'created_by' => Auth::user()->id,
        ]);

        return redirect()->route('whatsapp-config.index')->with('success', __('WhatsApp configuration created successfully.'));
    }

    public function edit($id)
    {
        if (!$this->isCompany()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $config = WhatsAppConfig::where('workspace_id', getActiveWorkSpace())->findOrFail($id);
        $pipelines = Pipeline::where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
        $stages = LeadStage::where('pipeline_id', $config->pipeline_id)
            ->where('workspace_id', getActiveWorkSpace())
            ->get()
            ->pluck('name', 'id')
            ->toArray();

        $departments = [];
        if (module_is_active('Hrm') && class_exists('\Workdo\Hrm\Entities\Department')) {
            $departments = \Workdo\Hrm\Entities\Department::where('workspace', getActiveWorkSpace())
                ->get()
                ->pluck('name', 'id')
                ->toArray();
        }

        return view('lead::whatsapp.config_edit', compact('config', 'pipelines', 'stages', 'departments'));
    }

    public function update(Request $request, $id)
    {
        if (!$this->isCompany()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $config = WhatsAppConfig::where('workspace_id', getActiveWorkSpace())->findOrFail($id);

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'connection_type' => 'required|in:meta_cloud,qr_session',
            'phone_number_id' => 'required_if:connection_type,meta_cloud|nullable|string|max:255',
            'business_account_id' => 'required_if:connection_type,meta_cloud|nullable|string|max:255',
            'access_token' => 'required_if:connection_type,meta_cloud|nullable|string',
            'verify_token' => 'required_if:connection_type,meta_cloud|nullable|string|max:255',
            'pipeline_id' => 'required|integer',
            'stage_id' => 'required|integer',
            'department_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $config->update([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'connection_type' => $request->connection_type,
            'phone_number_id' => $request->phone_number_id,
            'business_account_id' => $request->business_account_id,
            'access_token' => $request->access_token,
            'verify_token' => $request->verify_token,
            'department_id' => $request->department_id,
            'pipeline_id' => $request->pipeline_id,
            'stage_id' => $request->stage_id,
        ]);

        return redirect()->route('whatsapp-config.index')->with('success', __('WhatsApp configuration updated successfully.'));
    }

    public function destroy($id)
    {
        if (!$this->isCompany()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $config = WhatsAppConfig::where('workspace_id', getActiveWorkSpace())->findOrFail($id);
        $config->delete();

        return redirect()->route('whatsapp-config.index')->with('success', __('WhatsApp configuration deleted successfully.'));
    }

    public function getStages(Request $request)
    {
        $stages = LeadStage::where('pipeline_id', $request->pipeline_id)
            ->where('workspace_id', getActiveWorkSpace())
            ->get()
            ->pluck('name', 'id');

        return response()->json($stages);
    }

    public function updateStage(Request $request)
    {
        if (!$this->isCompany()) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $config = WhatsAppConfig::where('workspace_id', getActiveWorkSpace())->find($request->id);
        if (!$config) {
            return response()->json(['success' => false, 'message' => __('WhatsApp configuration not found.')], 404);
        }

        $config->update([
            'pipeline_id' => $request->pipeline_id,
            'stage_id' => $request->stage_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('WhatsApp configuration stage mapping updated successfully.'),
        ]);
    }
}
