<?php

namespace Workdo\Lead\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Workdo\Lead\Entities\WebhookEndpoint;
use Workdo\Lead\Entities\WebhookData;

class WebhookReceiverController extends Controller
{
    public function store(Request $request, $url)
    {
        $endpoint = WebhookEndpoint::where('url', $url)->first();

        if (!$endpoint) {
            return response()->json([
                'status' => 'error',
                'message' => 'Webhook URL not found.'
            ], 404);
        }

        $payload = $request->all();

        WebhookData::create([
            'webhook_endpoint_id' => $endpoint->id,
            'payload' => $payload,
            'status' => 'pending',
            'assigned_user_id' => null, // Will be unassigned initially wait, no, the requirement is we might assign it right away? Actually standard is let the user see it and then they assign, OR we use the default assign_to but the requirement says `current_user_id` for tracking who is handling it. Let's make it null.
            'workspace_id' => $endpoint->workspace_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data received successfully.'
        ], 200);
    }
}
