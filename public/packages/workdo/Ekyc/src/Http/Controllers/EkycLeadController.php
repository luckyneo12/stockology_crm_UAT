<?php

namespace Workdo\Ekyc\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\Ekyc\Entities\EkycLead;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EkycLeadController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->isAbleTo('ekyc manage')) {
            $query = \Workdo\Ekyc\Entities\EkycSubmission::with(['user', 'pipeline', 'stage'])
                ->orderBy('created_at', 'desc');

            // Search by mobile, email or pan
            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('mobile_number', 'like', '%'.$request->search.'%')
                      ->orWhere('email', 'like', '%'.$request->search.'%')
                      ->orWhere('pan_number', 'like', '%'.$request->search.'%')
                      ->orWhere('pan_name', 'like', '%'.$request->search.'%');
                });
            }

            // Filter by status
            if ($request->has('status') && $request->status != '') {
                $query->where('status', $request->status);
            }

            // Filter by date range
            if ($request->has('start_date') && $request->start_date != '') {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->has('end_date') && $request->end_date != '') {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            $submissions = $query->paginate(20);

            return view('ekyc::submissions.index', compact('submissions'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (Auth::user()->isAbleTo('ekyc manage')) {
            $users = User::where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
            return view('ekyc::leads.create', compact('users'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('ekyc manage')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $lead = new EkycLead();
            $lead->name = $request->name;
            $lead->email = $request->email;
            $lead->phone = $request->phone;
            $lead->assigned_user = $request->assigned_user;
            $lead->workspace_id = getActiveWorkSpace();
            $lead->created_by = Auth::user()->id;
            $lead->save();

            return redirect()->route('ekyc-leads.index')->with('success', __('Lead created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(EkycLead $ekycLead)
    {
        if (Auth::user()->can('ekyc manage') && $ekycLead->isAccessible()) {
            $users = User::where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
            return view('ekyc::leads.edit', compact('ekycLead', 'users'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, EkycLead $ekycLead)
    {
        if (Auth::user()->can('ekyc manage') && $ekycLead->isAccessible()) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $ekycLead->name = $request->name;
            $ekycLead->email = $request->email;
            $ekycLead->phone = $request->phone;
            $ekycLead->assigned_user = $request->assigned_user;
            $ekycLead->save();

            return redirect()->route('ekyc-leads.index')->with('success', __('Lead updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(EkycLead $ekycLead)
    {
        if (Auth::user()->can('ekyc manage') && $ekycLead->isAccessible()) {
            $ekycLead->delete();
            return redirect()->route('ekyc-leads.index')->with('success', __('Lead deleted successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
