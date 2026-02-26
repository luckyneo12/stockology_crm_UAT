<?php

namespace Workdo\Lead\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Workdo\Lead\Entities\WebhookEndpoint;
use Workdo\Lead\Entities\WebhookData;
use Workdo\Lead\Entities\Lead;
use Workdo\Lead\Entities\UserLead;
use App\Models\User;
use Workdo\Lead\Events\CreateLead;
use Illuminate\Support\Facades\Auth;

class WebhookDataController extends Controller
{
    private function isCompany()
    {
        $user = Auth::user();
        return $user->type == 'company' || $user->type == 'super admin';
    }

    private function canViewSpecific($webhookData)
    {
        if ($this->isCompany()) {
            return true;
        }

        $user = Auth::user();
        if ($webhookData->assigned_user_id == $user->id) {
            return true;
        }
        $endpoint = $webhookData->endpoint;
        if ($endpoint) {
            $view_permissions = $endpoint->view_permissions ?? [];
            $edit_permissions = $endpoint->edit_permissions ?? [];
            if (in_array((string)$user->id, $view_permissions) ||
            in_array((string)$user->id, $edit_permissions) ||
            $endpoint->created_by == $user->id) {
                return true;
            }
        }
        return false;
    }

    public function index()
    {
        $user = Auth::user();

        // Let's verify if they have overall access to at least see the page
        $canSeePage = $this->isCompany();
        if (!$canSeePage) {
            $canSeePage = WebhookEndpoint::whereJsonContains('view_permissions', (string)$user->id)
                ->orWhereJsonContains('edit_permissions', (string)$user->id)
                ->orWhere('created_by', $user->id)->exists();
            if (!$canSeePage) {
                $canSeePage = WebhookData::where('assigned_user_id', $user->id)->exists();
            }
        }

        if ($canSeePage) {
            $query = WebhookData::where('workspace_id', getActiveWorkSpace());

            if (!$this->isCompany()) {
                $query->where(function ($q) use ($user) {
                    $q->where('assigned_user_id', $user->id)
                        ->orWhereHas('endpoint', function ($req) use ($user) {
                        $req->whereJsonContains('view_permissions', (string)$user->id)
                            ->orWhereJsonContains('edit_permissions', (string)$user->id)
                            ->orWhere('created_by', $user->id);
                    }
                    );
                });
            }

            $webhookDataList = $query->orderBy('id', 'DESC')->get();
            return view('lead::webhook_data.index', compact('webhookDataList'));
        }
        else {
            return redirect()->route('dashboard')->with('error', __('Permission completely denied.'));
        }
    }

    public function payload($id)
    {
        $webhookData = WebhookData::find($id);
        if ($webhookData && $webhookData->workspace_id == getActiveWorkSpace() && $this->canViewSpecific($webhookData)) {
            return view('lead::webhook_data.payload', compact('webhookData'));
        }
        else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function transferModal($id)
    {
        $webhookData = WebhookData::find($id);
        if ($webhookData && $webhookData->workspace_id == getActiveWorkSpace() && $this->canViewSpecific($webhookData)) {
            $endpoint = $webhookData->endpoint;
            $users = User::where('workspace_id', getActiveWorkSpace())->where('type', '!=', 'client')->get()->pluck('name', 'id');
            return view('lead::webhook_data.transfer', compact('webhookData', 'users'));
        }
        else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function transfer(Request $request, $id)
    {
        $webhookData = WebhookData::find($id);
        if ($webhookData && $webhookData->workspace_id == getActiveWorkSpace() && $this->canViewSpecific($webhookData)) {
            $webhookData->assigned_user_id = $request->assigned_user_id;
            $webhookData->save();

            // Notification Logic
            $endpoint = $webhookData->endpoint;
            if ($endpoint && $endpoint->view_permissions) {
                $permissions = $endpoint->view_permissions;
                foreach ($permissions as $userId) {
                    if ($userId != Auth::user()->id) {
                        $user = User::find($userId);
                        $assignee = User::find($request->assigned_user_id);

                        $msg = "A webhook data payload from '{$endpoint->name}' has been transferred to " . ($assignee ? $assignee->name : 'Unknown') . ".";

                        \App\Models\Notification::create([
                            'user_id' => $userId,
                            'type' => 'Webhook Data Transferred',
                            'data' => json_encode(['message' => $msg]),
                            'is_read' => 0,
                            'workspace_id' => getActiveWorkSpace(),
                        ]);
                    }
                }
            }

            return redirect()->back()->with('success', __('Data transferred successfully.'));
        }
        else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function convertToLead(Request $request, $id)
    {
        $webhookData = WebhookData::find($id);
        if ($webhookData && $webhookData->workspace_id == getActiveWorkSpace() && $this->canViewSpecific($webhookData)) {
            $endpoint = $webhookData->endpoint;
            $payload = $webhookData->payload;
            $mapping = $endpoint->field_mapping ?? [];

            // Create Lead
            $lead = new Lead();

            // Use mapping for standard fields
            $lead->name = $payload[$mapping['name'] ?? ''] ?? $payload['name'] ?? $payload['title'] ?? 'Lead from Webhook ' . $endpoint->name;
            $lead->email = $payload[$mapping['email'] ?? ''] ?? $payload['email'] ?? '';
            $lead->phone = $payload[$mapping['phone'] ?? ''] ?? $payload['phone'] ?? $payload['mobile'] ?? '';
            $lead->subject = $payload[$mapping['subject'] ?? ''] ?? $payload['subject'] ?? 'Webhook Lead';

            $lead->pipeline_id = $endpoint->pipeline_id;
            $lead->stage_id = $endpoint->stage_id;
            $lead->created_by = Auth::user()->id;
            $lead->date = date('Y-m-d');
            $lead->workspace_id = getActiveWorkSpace();
            $lead->save();

            // Handle Custom Fields Mapping
            if (isset($mapping['custom']) && is_array($mapping['custom'])) {
                foreach ($mapping['custom'] as $fieldId => $jsonKey) {
                    if (!empty($jsonKey) && isset($payload[$jsonKey])) {
                        \Workdo\Lead\Entities\LeadCustomFieldValue::updateOrCreate([
                            'lead_id' => $lead->id,
                            'field_id' => $fieldId,
                        ], [
                            'value' => $payload[$jsonKey],
                        ]);
                    }
                }
            }

            // Assign User to Lead
            $assignToId = $webhookData->assigned_user_id ?? $endpoint->assign_to ?? Auth::user()->id;

            UserLead::create([
                'user_id' => $assignToId,
                'lead_id' => $lead->id,
            ]);

            // Update webhook data status
            $webhookData->status = 'converted';
            $webhookData->save();

            event(new CreateLead($request, $lead));

            return redirect()->back()->with('success', __('Lead successfully created from webhook data!'));
        }
        else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
