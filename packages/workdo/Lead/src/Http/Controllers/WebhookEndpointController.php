<?php

namespace Workdo\Lead\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Workdo\Lead\Entities\WebhookEndpoint;
use Workdo\Lead\Entities\Pipeline;
use Workdo\Lead\Entities\LeadStage;
use Workdo\Lead\Entities\LeadCustomField;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WebhookEndpointController extends Controller
{
    private function isCompany()
    {
        $user = \Auth::user();
        return $user->type == 'company' || $user->type == 'super admin';
    }

    private function canEditSpecific($endpoint)
    {
        if ($this->isCompany()) {
            return true;
        }
        $user = \Auth::user();
        if ($endpoint->created_by == $user->id) {
            return true;
        }
        $permissions = $endpoint->edit_permissions ?? [];
        if (in_array((string)$user->id, $permissions)) {
            return true;
        }
        return false;
    }

    public function index()
    {
        if ($this->isCompany()) {
            $webhookEndpoints = WebhookEndpoint::where('workspace_id', getActiveWorkSpace())->get();
            return view('lead::webhook_endpoints.index', compact('webhookEndpoints'));
        }
        else {
            $user = \Auth::user();
            $webhookEndpoints = WebhookEndpoint::where('workspace_id', getActiveWorkSpace())
                ->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereJsonContains('edit_permissions', (string)$user->id);
            })->get();

            if ($webhookEndpoints->count() > 0) {
                return view('lead::webhook_endpoints.index', compact('webhookEndpoints'));
            }
            return redirect()->route('dashboard')->with('error', __('Permission completely denied.'));
        }
    }

    public function create()
    {
        if ($this->isCompany()) {
            $pipelines = Pipeline::where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
            $users = User::where('workspace_id', getActiveWorkSpace())->where('type', '!=', 'client')->get()->pluck('name', 'id');

            // Fetch first pipeline stages by default to populate dropdown
            $stages = [];
            $firstPipeline = $pipelines->keys()->first();
            if ($firstPipeline) {
                $stages = LeadStage::where('pipeline_id', $firstPipeline)->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id')->toArray();
            }

            $customFields = LeadCustomField::where('workspace_id', getActiveWorkSpace())->get();

            return view('lead::webhook_endpoints.create', compact('pipelines', 'users', 'stages', 'customFields'));
        }
        else {
            return response()->json(['error' => __('Permission denined.')], 401);
        }
    }

    public function store(Request $request)
    {
        if ($this->isCompany()) {
            $validator = \Validator::make(
                $request->all(),
            [
                'name' => 'required',
            ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $endpoint = new WebhookEndpoint();
            $endpoint->name = $request->name;
            $endpoint->url = Str::uuid()->toString();
            $endpoint->created_by = \Auth::user()->id;
            $endpoint->assign_to = $request->assign_to;
            $endpoint->pipeline_id = $request->pipeline_id;
            $endpoint->stage_id = $request->stage_id;
            $endpoint->view_permissions = $request->view_permissions ?? [];
            $endpoint->edit_permissions = $request->edit_permissions ?? [];
            $endpoint->field_mapping = $this->processFieldMapping($request);
            $endpoint->workspace_id = getActiveWorkSpace();
            $endpoint->save();

            return redirect()->route('webhook-endpoints.index')->with('success', __('Webhook Endpoint created successfully.'));
        }
        else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        $webhookEndpoint = WebhookEndpoint::find($id);
        if ($this->canEditSpecific($webhookEndpoint)) {
            if ($webhookEndpoint->workspace_id == getActiveWorkSpace()) {
                $pipelines = Pipeline::where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
                $users = User::where('workspace_id', getActiveWorkSpace())->where('type', '!=', 'client')->get()->pluck('name', 'id');
                $stages = LeadStage::where('pipeline_id', $webhookEndpoint->pipeline_id)->where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id')->toArray();
                $customFields = LeadCustomField::where('workspace_id', getActiveWorkSpace())->get();

                return view('lead::webhook_endpoints.edit', compact('webhookEndpoint', 'pipelines', 'users', 'stages', 'customFields'));
            }
            else {
                return response()->json(['error' => __('Permission denined.')], 401);
            }
        }
        else {
            return response()->json(['error' => __('Permission denined.')], 401);
        }
    }

    public function update(Request $request, $id)
    {
        $webhookEndpoint = WebhookEndpoint::find($id);
        if ($this->canEditSpecific($webhookEndpoint)) {
            if ($webhookEndpoint->workspace_id == getActiveWorkSpace()) {
                $validator = \Validator::make(
                    $request->all(),
                [
                    'name' => 'required',
                ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $webhookEndpoint->name = $request->name;
                $webhookEndpoint->assign_to = $request->assign_to;
                $webhookEndpoint->pipeline_id = $request->pipeline_id;
                $webhookEndpoint->stage_id = $request->stage_id;
                $webhookEndpoint->view_permissions = $request->view_permissions ?? [];
                $webhookEndpoint->edit_permissions = $request->edit_permissions ?? [];
                $webhookEndpoint->field_mapping = $this->processFieldMapping($request);
                $webhookEndpoint->save();

                return redirect()->route('webhook-endpoints.index')->with('success', __('Webhook Endpoint updated successfully.'));
            }
            else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        $webhookEndpoint = WebhookEndpoint::find($id);
        if ($this->canEditSpecific($webhookEndpoint)) {
            if ($webhookEndpoint->workspace_id == getActiveWorkSpace()) {
                $webhookEndpoint->delete();
                return redirect()->route('webhook-endpoints.index')->with('success', __('Webhook Endpoint deleted successfully.'));
            }
            else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function testForm($url)
    {
        $endpoint = WebhookEndpoint::where('url', $url)->first();
        if (!$endpoint) {
            abort(404);
        }

        $mapping = $endpoint->field_mapping ?? [];
        $webhookUrl = url('api/Lead/webhook/' . $endpoint->url);

        return view('lead::webhook_endpoints.test_form', compact('endpoint', 'mapping', 'webhookUrl'));
    }

    private function processFieldMapping(Request $request)
    {
        $mapping = $request->field_mapping ?? [];

        if (isset($mapping['new']) && !empty($mapping['new']['labels'])) {
            foreach ($mapping['new']['labels'] as $index => $label) {
                if (!empty($label) && !empty($mapping['new']['keys'][$index])) {
                    // Create New Custom Field
                    $customField = new LeadCustomField();
                    $customField->name = $label;
                    $customField->type = $mapping['new']['types'][$index] ?? 'text';
                    $customField->workspace_id = getActiveWorkSpace();
                    $customField->created_by = Auth::user()->id;
                    $customField->save();

                    // Add to mapping
                    $mapping['custom'][$customField->id] = $mapping['new']['keys'][$index];

                    // Add to in_form mapping if checked
                    if (isset($mapping['new']['in_form'][$index])) {
                        $mapping['in_form']['custom'][$customField->id] = 1;
                    }
                }
            }
        }
        unset($mapping['new']);

        return $mapping;
    }
}
