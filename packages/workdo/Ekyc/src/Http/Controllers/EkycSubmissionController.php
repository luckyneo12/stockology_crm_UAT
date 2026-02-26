<?php

namespace Workdo\Ekyc\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Workdo\Ekyc\Entities\EkycSubmission;

class EkycSubmissionController extends Controller
{
    /**
     * Display all submissions
     */
    public function index(Request $request)
    {
        $query = EkycSubmission::with(['user', 'pipeline', 'stage'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date != '') {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date != '') {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $submissions = $query->paginate(20);

        return view('ekyc::submissions.index', compact('submissions'));
    }

    /**
     * Show submission details
     */
    public function show($id)
    {
        $submission = EkycSubmission::with(['user', 'pipeline', 'stage', 'otpLogs'])
            ->findOrFail($id);

        return view('ekyc::submissions.show', compact('submission'));
    }

    /**
     * Approve submission
     */
    public function approve(Request $request, $id)
    {
        $submission = EkycSubmission::findOrFail($id);

        if ($request->isMethod('GET')) {
            return view('ekyc::submissions.approve', compact('submission'));
        }

        $submission->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'admin_notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Submission approved successfully',
        ]);
    }

    /**
     * Reject submission
     */
    public function reject(Request $request, $id)
    {
        $submission = EkycSubmission::findOrFail($id);

        if ($request->isMethod('GET')) {
            return view('ekyc::submissions.reject', compact('submission'));
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $submission->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
            'rejection_reason' => $request->reason,
            'admin_notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Submission rejected successfully',
        ]);
    }

    /**
     * Serve captured images (base64 from DB)
     */
    public function getImage($id, $field)
    {
        $submission = EkycSubmission::findOrFail($id);
        
        // Handle special case for Aadhaar Photo path in additional_data
        if ($field === 'aadhaar_photo') {
            $path = $submission->additional_data['id_proof_path'] ?? null;
            if ($path && \Storage::disk('public')->exists($path)) {
                return \Storage::disk('public')->response($path);
            }
            abort(404);
        }

        $data = $submission->getAttribute($field);

        // If it's a file path already (like selfie_path)
        if ($data && !str_contains($data, ',') && \Storage::disk('public')->exists($data)) {
            return \Storage::disk('public')->response($data);
        }

        if (!$data || !str_contains($data, ',')) {
            abort(404);
        }

        @list($type, $data) = explode(';', $data);
        @list(, $data) = explode(',', $data);
        $imageData = base64_decode($data);

        $mime = str_replace('data:', '', $type);

        return Response::make($imageData, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
    /**
     * Delete submission
     */
    public function destroy($id)
    {
        if (Auth::user()->can('ekyc manage') || Auth::user()->type == 'company') {
            Log::info("EkycSubmission delete request (Soft) for ID: " . $id . " by User: " . Auth::user()->name);
            $submission = EkycSubmission::findOrFail($id);
            $submission->delete();

            return redirect()->back()->with('success', __('Submission deleted successfully (Moved to Trash).'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function forceDestroy($id)
    {
        if (Auth::user()->can('ekyc manage') || Auth::user()->type == 'company') {
            Log::warning("EkycSubmission PERMANENT delete request for ID: " . $id . " by User: " . Auth::user()->name);
            $submission = EkycSubmission::withTrashed()->findOrFail($id);
            $submission->forceDelete();

            return redirect()->back()->with('success', __('Submission permanently deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
