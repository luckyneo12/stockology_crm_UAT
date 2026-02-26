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

        // Verify OTP
        $result = $this->otpService->verify($identifier, $type, $request->otp);

        if ($result['success']) {
            // Get current session submission
            $sessionId = $request->session()->get('ekyc_session_id');
            $currentSubmission = EkycSubmission::where('session_id', $sessionId)->first();

            // Check if there's an existing submission for this identifier (mobile/email)
            $existingSubmission = EkycSubmission::where($type === 'email' ? 'email' : 'mobile_number', $identifier)
                ->where('id', '!=', $currentSubmission?->id)
                ->where('status', '!=', 'completed')
                ->latest()
                ->first();

            if ($existingSubmission) {
                // Resume existing submission
                $submission = $existingSubmission;
                // Update the session to use the existing submission's session ID
                // IMPORTANT: We do this instead of updating the record's session_id to avoid unique constraint violation
                $request->session()->put('ekyc_session_id', $existingSubmission->session_id);
                
                // Delete the blank/initial submission created at start if it exists
                if ($currentSubmission && $currentSubmission->id !== $existingSubmission->id && $currentSubmission->current_step <= 1) {
                    $currentSubmission->delete();
                }
            } else {
                // Check if current submission belongs to a DIFFERENT number/email
                $isDifferentIdentifier = $currentSubmission && 
                    (($type === 'mobile' && $currentSubmission->mobile_number && $currentSubmission->mobile_number !== $identifier) ||
                     ($type === 'email' && $currentSubmission->email && $currentSubmission->email !== $identifier));

                if ($isDifferentIdentifier) {
                    // Start a completely NEW session for this different person/number
                    $sessionId = \Illuminate\Support\Str::uuid()->toString();
                    $request->session()->put('ekyc_session_id', $sessionId);
                    
                    $submission = EkycSubmission::create([
                        'session_id' => $sessionId,
                        'current_step' => 1,
                        'status' => 'pending',
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);
                } else {
                    $submission = $currentSubmission ?? EkycSubmission::create([
                        'session_id' => $sessionId,
                        'current_step' => 1,
                        'status' => 'pending',
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);
                }
            }

            $rm_pp_code = $pendingVerification['rm_pp_code'] ?? null;

            // Update submission based on verification type
            if ($type === 'email') {
                $submission->email = $identifier;
                $submission->email_verified_at = now();
                $submission->relation = $request->relation;
                $submission->rm_pp_code = $rm_pp_code ?? $submission->rm_pp_code;
                $submission->status = 'in_progress';
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
