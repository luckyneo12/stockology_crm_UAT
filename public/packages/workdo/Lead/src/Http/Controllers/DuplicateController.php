<?php

namespace Workdo\Lead\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Workdo\Lead\Entities\Lead;
use Illuminate\Support\Facades\DB;

class DuplicateController extends Controller
{
    public function index()
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->can('lead import')) {
            $creatorId = creatorId();
            $workspaceId = getActiveWorkSpace();

            // Find duplicate emails
            $duplicateEmails = Lead::select('email', DB::raw('COUNT(*) as count'))
                ->where('created_by', $creatorId)
                ->where('workspace_id', $workspaceId)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->groupBy('email')
                ->having('count', '>', 1)
                ->pluck('email');

            $leadsByEmail = Lead::whereIn('email', $duplicateEmails)
                ->where('created_by', $creatorId)
                ->where('workspace_id', $workspaceId)
                ->orderBy('email')
                ->get()
                ->groupBy('email');

            // Find duplicate phones
            $duplicatePhones = Lead::select('phone', DB::raw('COUNT(*) as count'))
                ->where('created_by', $creatorId)
                ->where('workspace_id', $workspaceId)
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->groupBy('phone')
                ->having('count', '>', 1)
                ->pluck('phone');

            $leadsByPhone = Lead::whereIn('phone', $duplicatePhones)
                ->where('created_by', $creatorId)
                ->where('workspace_id', $workspaceId)
                ->orderBy('phone')
                ->get()
                ->groupBy('phone');

            return view('lead::duplicates.index', compact('leadsByEmail', 'leadsByPhone'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function destroy($id)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->can('lead delete') || \Auth::user()->can('lead import')) {
            $lead = Lead::find($id);
            if ($lead) {
                $lead->delete();
                return redirect()->back()->with('success', __('Lead deleted successfully.'));
            }
            return redirect()->back()->with('error', __('Lead not found.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
