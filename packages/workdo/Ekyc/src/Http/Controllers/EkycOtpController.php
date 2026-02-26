<?php

namespace Workdo\Ekyc\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Workdo\Ekyc\Services\OtpService;
use Workdo\Ekyc\Entities\EkycSubmission;

class EkycOtpController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Verify OTP
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|min:4|max:8',
            'relation' => 'nullable|string|max:50',
        ]);

        $pendingVerification = $request->session()->get('ekyc_pending_verification');

        if (!$pendingVerification) {
            return response()->json([
                'success' => false,
                'message' => 'No pending verification found',
            ], 400);
        }

        $identifier = $pendingVerification['identifier'];
        $type = $pendingVerification['type'];

        // Normalize mobile number if type is mobile (last 10 digits)
        if ($type === 'mobile') {
            $identifier = substr(preg_replace('/[^0-9]/', '', $identifier), -10);
        }

        // Verify OTP
        $result = $this->otpService->verify($identifier, $type, $request->otp);

        if ($result['success']) {
            // Get current session submission
            $sessionId = $request->session()->get('ekyc_session_id');
            $currentSubmission = EkycSubmission::where('session_id', $sessionId)->first();

            // 1. Check if there is ANY completed submission for this identifier
            $completedSubmission = EkycSubmission::where($type === 'email' ? 'email' : 'mobile_number', 'LIKE', '%' . $identifier)
                ->where('id', '!=', $currentSubmission?->id)
                ->where('status', 'completed')
                ->first();

            if ($completedSubmission) {
                // Redirect to completion page if already done
                $request->session()->put('ekyc_session_id', $completedSubmission->session_id);
                
                if ($currentSubmission && $currentSubmission->id !== $completedSubmission->id && $currentSubmission->current_step <= 1) {
                    $currentSubmission->delete();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Your KYC process for this number is already completed.',
                    'redirect' => route('ekyc.form.complete'),
                ]);
            }

            // 2. Otherwise, check if there's an in-progress submission to resume
            $existingSubmission = EkycSubmission::where($type === 'email' ? 'email' : 'mobile_number', 'LIKE', '%' . $identifier)
                ->where('id', '!=', $currentSubmission?->id)
                ->whereIn('status', ['pending', 'in_progress', 'on_hold'])
                ->latest()
                ->first();

            if ($existingSubmission) {
                // Resume existing submission
                $submission = $existingSubmission;
                $request->session()->put('ekyc_session_id', $existingSubmission->session_id);
                
                if ($currentSubmission && $currentSubmission->id !== $existingSubmission->id && $currentSubmission->current_step <= 1) {
                    $currentSubmission->delete();
                }
            } else {
                // ... same as before ...
                $submission = $currentSubmission ?? EkycSubmission::create([
                    'session_id' => $sessionId,
                    'current_step' => 1,
                    'status' => 'pending',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            $rm_pp_code = $pendingVerification['rm_pp_code'] ?? null;

            // Update submission based on verification type
            if ($type === 'email') {
                $submission->email = $identifier;
                $submission->email_verified_at = now();
                $submission->relation = $request->relation;
                $submission->rm_pp_code = $rm_pp_code ?? $submission->rm_pp_code;
                $submission->status = 'in_progress';

                // Save initial Full Name if provided
                if (!empty($pendingVerification['full_name'])) {
                    $addData = $submission->additional_data ?? [];
                    $addData['full_name'] = $pendingVerification['full_name'];
                    $submission->additional_data = $addData;
                }
            } else {
                $submission->mobile_number = $identifier;
                $submission->mobile_verified_at = now();
                $submission->relation = $request->relation;
                $submission->rm_pp_code = $rm_pp_code ?? $submission->rm_pp_code;
                $submission->status = 'in_progress';
            }
            $submission->save();
            $submission->refresh();

            // Clear pending verification
            $request->session()->forget('ekyc_pending_verification');

            // Get next step
            $nextStep = $submission->getNextStep();
            
            if ($nextStep) {
                $submission->current_step = $nextStep;
                $submission->save();
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'redirect' => $nextStep ? route('ekyc.form.step', ['step' => $nextStep]) : route('ekyc.form.complete'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
            'remaining_attempts' => $result['remaining_attempts'] ?? 0,
        ], 400);
    }

    /**
     * Resend OTP
     */
    public function resend(Request $request)
    {
        $pendingVerification = $request->session()->get('ekyc_pending_verification');

        if (!$pendingVerification) {
            return response()->json([
                'success' => false,
                'message' => 'No pending verification found',
            ], 400);
        }

        $identifier = $pendingVerification['identifier'];
        $type = $pendingVerification['type'];

        // Get submission ID
        $sessionId = $request->session()->get('ekyc_session_id');
        $submission = EkycSubmission::where('session_id', $sessionId)->first();

        // Resend OTP
        $result = $this->otpService->resend($identifier, $type, $submission?->id);

        if ($result['success']) {
            // Update session with new expiry time
            $pendingVerification['expires_in'] = $result['expires_in'];
            $request->session()->put('ekyc_pending_verification', $pendingVerification);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'expires_in' => $result['expires_in'],
                'is_testing_mode' => $result['is_testing_mode'] ?? false,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
            'wait_time' => $result['wait_time'] ?? 0,
        ], 400);
    }
}
