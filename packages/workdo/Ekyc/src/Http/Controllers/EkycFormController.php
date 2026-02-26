<?php

namespace Workdo\Ekyc\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Workdo\Ekyc\Entities\EkycSubmission;
use Workdo\Ekyc\Entities\EkycUiTemplate;
use Workdo\Ekyc\Services\OtpService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;


class EkycFormController extends Controller
{
    protected $otpService;
    protected $digioService;

    public function __construct(OtpService $otpService, \Workdo\Ekyc\Services\DigioService $digioService)
    {
        $this->otpService = $otpService;
        $this->digioService = $digioService;
    }

    /**
     * Get the company ID for the current journey context
     */
    private function getCompanyId($submission = null)
    {
        // 1. Try from submission user_id
        if ($submission && $submission->user_id) {
            return $submission->user_id;
        }

        // 2. Try from active UI template
        $template = EkycUiTemplate::getActive();
        if ($template && $template->created_by != 1) { // 1 is super admin
            return $template->created_by;
        }

        // 3. Fallback to auth or first company
        return auth()->check() ? creatorId() : 1;
    }

    /**
     * Show the initial KYC form (start page)
     */
    public function start(Request $request)
    {
        // Get or create session
        $sessionId = $request->session()->get('ekyc_session_id');

        if (!$sessionId) {
            $sessionId = Str::uuid()->toString();
            $request->session()->put('ekyc_session_id', $sessionId);
        }

        // Get or create submission
        $submission = EkycSubmission::withTrashed()->where('session_id', $sessionId)->first();

        if ($submission) {
            if ($submission->trashed()) {
                $submission->restore();
                // Reset to step 1 and pending status if it was deleted
                $submission->current_step = 1;
                $submission->status = 'pending';
                $submission->ip_address = $request->ip();
                $submission->user_agent = $request->userAgent();
                $submission->save();
            }

            // Capture company ID from URL if provided and not already set
            if ($request->has('c') && empty($submission->user_id)) {
                $submission->user_id = $request->c;
                $submission->save();
            }
        }
        else {
            $submission = EkycSubmission::create([
                'session_id' => $sessionId,
                'user_id' => $request->get('c'), // Capture from URL
                'current_step' => 1,
                'status' => 'pending',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // Get active UI template
        $template = EkycUiTemplate::getActive();

        return view('ekyc::form.start', compact('submission', 'template'));
    }

    /**
     * Verify contact (email or mobile)
     */
    public function verifyContact(Request $request)
    {
        $request->validate([
            'verification_type' => 'required|in:email,mobile',
            'identifier' => 'required',
            'full_name' => 'nullable|string|max:255',
            'rm_pp_code' => 'nullable|string|max:50',
        ]);

        $sessionId = $request->session()->get('ekyc_session_id');
        $submission = EkycSubmission::where('session_id', $sessionId)->first();

        if (!$submission) {
            return response()->json(['success' => false, 'message' => 'Session expired or submission not found. Please restart.'], 404);
        }

        $type = $request->verification_type;
        $identifier = $request->identifier;

        // Normalization for mobile (ensure 10 digits)
        if ($type === 'mobile') {
            $identifier = substr(preg_replace('/[^0-9]/', '', $identifier), -10);
        }

        // Additional validation based on type
        if ($type === 'email') {
            $request->validate(['identifier' => 'email']);
        }
        elseif ($type === 'mobile') {
            $request->validate(['identifier' => 'numeric|digits:10']);
        }

        // Generate and send OTP
        $result = $this->otpService->generateAndSend($identifier, $type, $submission->id);

        if ($result['success']) {
            // DO NOT update submission here. Wait for successful OTP verification.
            // This prevents overwriting existing session data if a new number is entered.

            // Store in session for OTP verification page
            $request->session()->put('ekyc_pending_verification', [
                'type' => $type,
                'identifier' => $identifier,
                'full_name' => $request->full_name,
                'expires_in' => $result['expires_in'],
                'rm_pp_code' => $request->rm_pp_code, // Store for later use
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'is_testing_mode' => $result['is_testing_mode'],
                'redirect' => route('ekyc.form.otp-verify'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Show OTP verification page
     */
    public function showOtpVerify(Request $request)
    {
        $pendingVerification = $request->session()->get('ekyc_pending_verification');

        if (!$pendingVerification) {
            return redirect()->route('ekyc.form.start');
        }

        $template = EkycUiTemplate::getActive();
        $sessionId = $request->session()->get('ekyc_session_id');
        $submission = EkycSubmission::where('session_id', $sessionId)->first();

        if (!$submission) {
            return redirect()->route('ekyc.form.start');
        }

        return view('ekyc::form.otp-verify', compact('pendingVerification', 'template', 'submission'));
    }

    /**
     * Show specific KYC step
     */
    public function showStep(Request $request, $step)
    {
        $sessionId = $request->session()->get('ekyc_session_id');

        if (!$sessionId) {
            return redirect()->route('ekyc.form.start');
        }

        if ($step == 1) {
            return redirect()->route('ekyc.form.start');
        }

        $submission = EkycSubmission::where('session_id', $sessionId)->first();

        if (!$submission) {
            return redirect()->route('ekyc.form.start');
        }

        // Check if contact is started (mobile verified is common first step)
        if (!$submission->mobile_verified_at && $step > 1) {
            return redirect()->route('ekyc.form.start');
        }

        // Get enabled steps
        $enabledSteps = EkycSubmission::getEnabledSteps($this->getCompanyId($submission));

        // Check if step is valid and enabled
        if (!isset($enabledSteps[$step]) || !$enabledSteps[$step]['enabled']) {
            return redirect()->route('ekyc.form.step', ['step' => $submission->current_step]);
        }

        $template = EkycUiTemplate::getActive();

        // Load appropriate view based on step
        $viewName = $this->getStepViewName($step, $submission);
        $pendingVerification = $request->session()->get('ekyc_pending_verification');

        // Safety: If OTP view is requested but no data in session, redirect to start
        // This usually happens during Step 1 session initialization
        if ($viewName === 'otp-verify' && !$pendingVerification && $step == 1) {
            return redirect()->route('ekyc.form.start');
        }

        $digio_environment = $this->digioService->getEnvironment();

        // Extract Aadhaar photo for face-api.js comparison
        $aadhaar_photo = null;
        $additionalData = $submission->additional_data ?? [];
        $photoPath = $additionalData['profile_photo_path'] ?? ($additionalData['id_proof_path'] ?? null);

        if ($photoPath && Storage::disk('public')->exists($photoPath)) {
            $photoContent = Storage::disk('public')->get($photoPath);
            $aadhaar_photo = base64_encode($photoContent);
        }
        elseif (!empty($submission->aadhaar_data)) {
            $aadhaarData = $submission->aadhaar_data;
            if (isset($aadhaarData['actions'])) {
                foreach ($aadhaarData['actions'] as $action) {
                    $potentialPhoto = $action['details']['image'] ?? ($action['details']['photo'] ?? null);
                    if ($potentialPhoto) {
                        $aadhaar_photo = $potentialPhoto;
                        break;
                    }
                }
            }
        }

        // Clean up: face-api.js needs pure base64 if we add prefix in JS, 
        // or we handle prefix detection in JS. Let's strip it here for consistency.
        if ($aadhaar_photo && str_contains($aadhaar_photo, ',')) {
            $aadhaar_photo = explode(',', $aadhaar_photo)[1];
        }

        $pdfTemplates = [];
        if ($viewName === 'esign') {
            $settings = getCompanyAllSetting($this->getCompanyId($submission));
            $pdfTemplates = !empty($settings['ekyc_pdf_templates']) ? json_decode($settings['ekyc_pdf_templates'], true) : [];
        }

        return view("ekyc::form.{$viewName}", compact('submission', 'template', 'step', 'enabledSteps', 'pendingVerification', 'digio_environment', 'aadhaar_photo', 'pdfTemplates'));
    }

    /**
     * Submit a specific step
     */
    public function submitStep(Request $request, $step)
    {
        $sessionId = $request->session()->get('ekyc_session_id');
        $submission = EkycSubmission::where('session_id', $sessionId)->first();

        if (!$submission) {
            return response()->json(['success' => false, 'message' => 'Session expired']);
        }

        $enabledSteps = EkycSubmission::getEnabledSteps($this->getCompanyId($submission));
        $stepName = $enabledSteps[$step]['name'] ?? '';

        switch ($stepName) {
            case 'Mobile Verification':
            case 'Email Verification':
                // Handled in EkycOtpController
                break;
            case 'PAN Verification':
                return $this->processPanStep($request, $submission);
            case 'Aadhaar Verification':
                return $this->processAadhaarStep($request, $submission);
            case 'Selfie Liveness Capture':
                return $this->processSelfieStep($request, $submission);
            case 'Bank Account Verification':
                return $this->processBankStep($request, $submission);
            case 'Trading Segments':
                return $this->processSegmentsStep($request, $submission);
            case 'Personal Details':
                return $this->processPersonalDetailsStep($request, $submission);
            case 'Compliance Declarations':
                return $this->processComplianceStep($request, $submission);
            case 'Nominee Details':
                return $this->processNomineeStep($request, $submission);
            case 'Document Upload':
                return $this->processDocumentsStep($request, $submission);
            case 'Video KYC (IPV)':
                return $this->processVideoKycStep($request, $submission);
            default:
                return response()->json(['success' => false, 'message' => 'Invalid step'], 400);
        }

        return response()->json(['success' => false, 'message' => 'Invalid step']);
    }

    /**
     * Process PAN verification step
     */
    private function processPanStep(Request $request, EkycSubmission $submission)
    {
        $request->validate([
            'pan_number' => 'required|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'pan_name' => 'required|string|max:255',
            'pan_dob' => 'required|date',
            'email' => 'nullable|email',
            'rm_pp_code' => 'nullable|string|max:50',
        ]);

        // Real Digio API Call
        $this->digioService->loadSettings($this->getCompanyId($submission));
        $result = $this->digioService->verifyPan(
            $request->pan_number,
            $request->pan_name,
            $request->pan_dob
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'PAN Verification Failed: ' . ($result['message'] ?? 'Could not verify with tax authorities.'),
            ], 400);
        }

        // Update submission with verified data
        $submission->email = $request->email ?? $submission->email;
        $submission->rm_pp_code = $request->rm_pp_code;
        $submission->pan_number = strtoupper($request->pan_number);
        $submission->pan_name = $request->pan_name;
        $submission->pan_dob = $request->pan_dob;
        $submission->pan_verified_at = now();
        $submission->pan_response = $result['data'];
        $submission->save();
        $submission->refresh();

        $nextStep = $submission->getNextStep($this->getCompanyId($submission)) ?? $submission->current_step;
        $submission->update(['current_step' => $nextStep]);

        return response()->json([
            'success' => true,
            'message' => 'PAN verified successfully via Digio',
            'next_step' => $nextStep,
            'redirect' => $nextStep ? route('ekyc.form.step', ['step' => $nextStep]) : route('ekyc.form.complete'),
        ]);
    }

    /**
     * Process Aadhaar verification step
     */
    private function processAadhaarStep(Request $request, EkycSubmission $submission)
    {
        // Use Mobile Number with +91 prefix as identifier (Required for partner verified skip)
        $mobile = $submission->mobile_number;
        $identifier = (strpos($mobile, '+91') === 0) ? $mobile : '+91' . $mobile;

        $this->digioService->loadSettings($this->getCompanyId($submission));
        $result = $this->digioService->initializeAadhaar($identifier, 'mobile'); // No 3rd arg

        if ($result['success']) {
            $submission->update([
                'additional_data' => array_merge($submission->additional_data ?? [], [
                    'aadhaar_request_id' => $result['request_id'],
                    'aadhaar_access_token' => $result['access_token']
                ])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Aadhaar session initialized',
                'digio_request_id' => $result['request_id'],
                'digio_access_token' => $result['access_token'],
                'digio_identifier' => $identifier, // Pass the mobile number
                'is_digio' => true,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Aadhaar Initialization Failed: ' . ($result['message'] ?? 'Check Digio logs.'),
        ], 400);
    }

    /**
     * Process Selfie Liveness Capture step
     */
    /**
     * Process Selfie Liveness Capture step
     */
    public function initializeSelfieSession(Request $request)
    {
        $sessionId = $request->session()->get('ekyc_session_id');
        $submission = EkycSubmission::where('session_id', $sessionId)->first();

        if (!$submission) {
            return response()->json(['success' => false, 'message' => 'Session expired'], 404);
        }

        // Use mobile as identifier
        $identifier = $submission->mobile_number;
        if (!str_starts_with($identifier, '+91')) {
            $identifier = '+91' . $identifier;
        }

        $this->digioService->loadSettings($this->getCompanyId($submission));
        $backendEnv = $this->digioService->getEnvironment();
        $jsEnv = ($backendEnv === 'production') ? 'production' : 'stage';

        // 1. Session Token Reuse Check
        $additionalData = $submission->additional_data ?? [];
        if (!empty($additionalData['digio_access_token']) && !empty($additionalData['digio_token_created_at'])) {
            $createdAt = \Carbon\Carbon::parse($additionalData['digio_token_created_at']);
            // Reuse if less than 25 minutes old (Digio tokens usually 30 min)
            if ($createdAt->diffInMinutes(now()) < 25) {
                // Verify status of the existing request to ensure it's not Cancelled
                if (!empty($additionalData['digio_request_id'])) {
                    $statusCheck = $this->digioService->getKycRequestData($additionalData['digio_request_id']);
                    // If check succeeds and status is valid (e.g. 'requested', 'partially_completed')
                    // 'CANCELLED' or 'REJECTED' or 'COMPLETED' (if we want to allow retry) might require new session
                    $currentStatus = $statusCheck['data']['status'] ?? '';

                    if ($statusCheck['success'] && !in_array($currentStatus, ['CANCELLED', 'REJECTED', 'FAILED'])) {
                        return response()->json([
                            'success' => true,
                            'is_digio' => true,
                            'request_id' => $additionalData['digio_request_id'],
                            'access_token' => $additionalData['digio_access_token'],
                            'identifier' => $identifier,
                            'environment' => $jsEnv,
                            'message' => 'Session reused (Status: ' . $currentStatus . ')'
                        ]);
                    }
                    // Else: Status is bad, fall through to create new session
                    Log::info("Digio Session Reuse Skipped: Status was " . $currentStatus);
                }
            }
        }

        $result = $this->digioService->initializeSelfie($identifier);

        if ($result['success']) {
            // Cache the new token
            $additionalData['digio_access_token'] = $result['access_token'];
            $additionalData['digio_request_id'] = $result['request_id'];
            $additionalData['digio_token_created_at'] = now()->toIso8601String();
            $submission->additional_data = $additionalData;
            $submission->save();

            return response()->json([
                'success' => true,
                'is_digio' => true,
                'request_id' => $result['request_id'],
                'access_token' => $result['access_token'],
                'identifier' => $identifier,
                'environment' => $jsEnv
            ]);
        }

        return response()->json(['success' => false, 'message' => $result['message'] ?? 'Failed to init Digio'], 400);
    }

    private function processSelfieStep(Request $request, EkycSubmission $submission)
    {
        $request->validate([
            'selfie_data' => 'required|string', // Base64 image
        ]);

        $additionalData = $submission->additional_data ?? [];
        $idProofPath = $additionalData['id_proof_path'] ?? null;

        Log::info("processSelfieStep: checking id_proof_path: " . ($idProofPath ?? 'NULL'));

        // LAZY REPAIR: If photo path is missing but we have Aadhaar data, try to extract it now
        if (!$idProofPath || !Storage::disk('public')->exists($idProofPath)) {
            Log::info("id_proof_path missing or file not found. Attempting repair from aadhaar_data...");
            if (!empty($submission->aadhaar_data)) {
                $idProofPath = $this->extractAndSaveAadhaarPhoto($submission);
                if ($idProofPath) {
                    $additionalData['id_proof_path'] = $idProofPath;
                    $additionalData['profile_photo_path'] = $idProofPath;
                    $submission->additional_data = $additionalData;
                    $submission->save();
                    Log::info("Repair successful! New path: " . $idProofPath);
                }
            }
        }

        if (!$idProofPath || !Storage::disk('public')->exists($idProofPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Aadhaar photo not found. <b>Note:</b> DigiLocker verification often does not provide a photo. Please <a href="' . route('ekyc.form.step', ['step' => 2]) . '" style="color: #ef4444; text-decoration: underline; font-weight: bold;">click here to go back</a> and use the <b>Aadhaar OTP (UIDAI)</b> method instead.',
                'retry_aadhaar' => true
            ], 400);
        }

        // 1. Process and Save Selfie
        $selfiePath = '';
        try {
            $imageData = $request->selfie_data;
            if (str_contains($imageData, ',')) {
                $imageData = explode(',', $imageData)[1];
            }
            $decodedImage = base64_decode($imageData);

            $fileName = 'custom_selfie_' . $submission->id . '_' . time() . '.jpg';
            $selfiePath = 'ekyc/selfies/' . $fileName;
            Storage::disk('public')->put($selfiePath, $decodedImage);

            $submission->selfie_path = $selfiePath;
            $submission->face_verified_at = now();
        }
        catch (\Exception $e) {
            Log::error('Selfie Save Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not save selfie image.'], 500);
        }

        // 2. Server-side Verification via Digio Face Match
        $score = 0;
        try {
            $this->digioService->loadSettings($this->getCompanyId($submission));

            // Paths for Digio Face Match
            $fullIdProofPath = Storage::disk('public')->path($idProofPath);
            $fullSelfiePath = Storage::disk('public')->path($selfiePath);

            Log::info("Initiating server-side Face Match for Submission {$submission->id}...");
            $matchResult = $this->digioService->faceMatch($fullIdProofPath, $fullSelfiePath);

            if ($matchResult['success']) {
                $score = $matchResult['match_score'] ?? 0;
                Log::info("Digio Face Match Score: {$score}%");
            }
            else {
                Log::error("Digio Face Match API returned error: " . $matchResult['message']);
                // Fallback to browser score if provided, but prioritize server side
                $score = $request->match_score ?? 0;
            }
        }
        catch (\Exception $e) {
            Log::error("Digio Face Match Integration Error: " . $e->getMessage());
            $score = $request->match_score ?? 0; // Fallback to browser-side score if API fails
        }

        $latitude = $request->lat ?? null;
        $longitude = $request->lng ?? null;
        $address = $request->address ?? null;

        $submission->face_match_score = $score;
        $additionalData['biometrics']['verification_method'] = 'Digio Server Match';
        $additionalData['biometrics']['capture_location'] = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => $address,
        ];
        $submission->additional_data = $additionalData;

        // Determination logic (60+ is verified, 35+ is manual review)
        if ($score >= 60) {
            $submission->status = 'verified';
            $additionalData['biometrics']['status_reason'] = 'Server Match High Confidence (>=60)';
        }
        elseif ($score >= 35) {
            $submission->status = 'manual_review';
            $additionalData['biometrics']['status_reason'] = 'Server Match Borderline (35-59)';
        }
        else {
            $additionalData['biometrics']['status_reason'] = 'Face match score too low (' . $score . '%)';
            $submission->additional_data = $additionalData;
            $submission->status = 'rejected';
            $submission->save();

            return response()->json([
                'success' => false,
                'message' => 'Face match failed (Score: ' . $score . '%). The selfie does not significantly match the ID document photo. Please ensure better lighting and try again.',
                'score' => $score,
                'status' => 'failed'
            ], 422);
        }

        $submission->additional_data = $additionalData;

        // 3. Finalize
        $submission->save();
        $submission->refresh();

        $nextStep = $submission->getNextStep($this->getCompanyId($submission)) ?? $submission->current_step;
        $submission->update(['current_step' => $nextStep]);

        return response()->json([
            'success' => true,
            'message' => 'Selfie Verified Successfully!',
            'score' => $score,
            'status' => $submission->status,
            'next_step' => $nextStep,
            'redirect' => $nextStep ? route('ekyc.form.step', ['step' => $nextStep]) : route('ekyc.form.complete'),
        ]);
    }

    /**
     * Process Bank verification step
     */
    private function processBankStep(Request $request, EkycSubmission $submission)
    {
        $request->validate([
            'bank_account_number' => 'required|string|max:50',
            'bank_ifsc' => 'required|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
            'bank_account_holder_name' => 'required|string|max:255',
        ]);

        $mobile = $submission->mobile_number;
        $identifier = (strpos($mobile, '+91') === 0) ? $mobile : '+91' . $mobile;

        $this->digioService->loadSettings($this->getCompanyId($submission));
        $result = $this->digioService->verifyBank(
            $identifier,
            $request->bank_account_number,
            $request->bank_ifsc,
            $request->bank_account_holder_name
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Bank Verification Failed: ' . ($result['message'] ?? 'Could not verify account.'),
            ], 400);
        }

        // Check if name matches (Optional strictness)
        if (isset($result['is_name_match']) && !$result['is_name_match']) {
            return response()->json([
                'success' => false,
                'message' => "Name Mismatch: Bank account name '" . ($result['verified_name'] ?? 'Unknown') . "' does not match your entered name '" . $request->bank_account_holder_name . "'.",
            ], 400);
        }

        $submission->bank_account_number = $request->bank_account_number;
        $submission->bank_ifsc = strtoupper($request->bank_ifsc);
        $submission->bank_account_holder_name = $result['verified_name'] ?? $request->bank_account_holder_name;
        $submission->bank_verified_at = now();
        $submission->bank_response = $result['data'] ?? [];
        $submission->save();
        $submission->refresh();

        $nextStep = $submission->getNextStep($this->getCompanyId($submission)) ?? $submission->current_step;
        $submission->update(['current_step' => $nextStep]);

        return response()->json([
            'success' => true,
            'message' => 'Bank account verified successfully',
            'next_step' => $nextStep,
            'redirect' => $nextStep ? route('ekyc.form.step', ['step' => $nextStep]) : route('ekyc.form.complete'),
        ]);
    }

    private function processSegmentsStep(Request $request, EkycSubmission $submission)
    {
        $request->validate([
            'segments' => 'required|array',
            'brokerage_plan' => 'required|string',
        ]);

        $submission->trading_segments = json_encode($request->segments);
        $submission->brokerage_plan = $request->brokerage_plan;
        $submission->segments_completed_at = now();
        $submission->save();
        $submission->refresh();

        $nextStep = $submission->getNextStep($this->getCompanyId($submission)) ?? $submission->current_step;
        $submission->update(['current_step' => $nextStep]);

        return response()->json([
            'success' => true,
            'message' => 'Trading segments updated successfully',
            'next_step' => $nextStep,
            'redirect' => $nextStep ? route('ekyc.form.step', ['step' => $nextStep]) : route('ekyc.form.complete'),
        ]);
    }

    private function processPersonalDetailsStep(Request $request, EkycSubmission $submission)
    {
        $request->validate([
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'marital_status' => 'required|string',
            'education' => 'required|string',
            'occupation' => 'required|string',
            'annual_income' => 'required|string',
            'trading_experience' => 'required|string',
            'is_pep' => 'required|string', // 0 or 1
            'networth' => 'required|numeric',
            'networth_date' => 'required|date',
        ]);

        $submission->father_name = $request->father_name;
        $submission->mother_name = $request->mother_name;
        $submission->marital_status = $request->marital_status;
        $submission->education = $request->education;
        $submission->occupation = $request->occupation;
        $submission->annual_income = $request->annual_income;
        $submission->trading_experience = $request->trading_experience;
        $submission->networth = $request->networth;
        $submission->networth_date = $request->networth_date;
        $submission->is_pep = (bool)$request->is_pep;
        $submission->details_completed_at = now();
        $submission->save();
        $submission->refresh();

        $nextStep = $submission->getNextStep($this->getCompanyId($submission)) ?? $submission->current_step;
        $submission->update(['current_step' => $nextStep]);

        return response()->json([
            'success' => true,
            'message' => 'Personal details updated successfully',
            'next_step' => $nextStep,
            'redirect' => $nextStep ? route('ekyc.form.step', ['step' => $nextStep]) : route('ekyc.form.complete'),
        ]);
    }

    private function processComplianceStep(Request $request, EkycSubmission $submission)
    {
        $request->validate([
            'ddpi_consent' => 'required',
            'running_account_auth' => 'required|string',
            'receive_credits' => 'required',
            'pledge_instruction' => 'required',
            'nominee_statement_type' => 'required|string',
            'statement_requirement' => 'required|string',
            'electronic_statement' => 'required',
            'share_email_rta' => 'required',
            'annual_report_media' => 'required|string',
            'receive_dividend_directly' => 'required',
            'dis_booklet' => 'required',
        ]);

        $submission->ddpi_consent = (bool)$request->ddpi_consent;
        $submission->running_account_auth = $request->running_account_auth;
        $submission->receive_credits = (bool)$request->receive_credits;
        $submission->pledge_instruction = (bool)$request->pledge_instruction;
        $submission->nominee_statement_type = $request->nominee_statement_type;
        $submission->statement_requirement = $request->statement_requirement;
        $submission->electronic_statement = (bool)$request->electronic_statement;
        $submission->share_email_rta = (bool)$request->share_email_rta;
        $submission->annual_report_media = $request->annual_report_media;
        $submission->receive_dividend_directly = (bool)$request->receive_dividend_directly;
        $submission->dis_booklet = (bool)$request->dis_booklet;
        $submission->compliance_completed_at = now();
        $submission->save();
        $submission->refresh();

        $nextStep = $submission->getNextStep($this->getCompanyId($submission)) ?? $submission->current_step;
        $submission->update(['current_step' => $nextStep]);
        Log::info("Compliance Step Completed. Next calculated step: " . ($nextStep ?? 'DONE'));

        return response()->json([
            'success' => true,
            'message' => 'Compliance declarations updated successfully',
            'next_step' => $nextStep,
            'redirect' => $nextStep ? route('ekyc.form.step', ['step' => $nextStep]) : route('ekyc.form.complete'),
        ]);
    }

    private function processNomineeStep(Request $request, EkycSubmission $submission)
    {
        $rules = [
            'has_nominee' => 'required|in:0,1',
        ];

        if ($request->has_nominee == '1') {
            $rules['nominees'] = 'required|array|min:1';
            $rules['nominees.*.name'] = 'required|string|max:255';
            $rules['nominees.*.doc_type'] = 'required|string|max:255';
            $rules['nominees.*.doc_id'] = 'required|string|max:255';
            $rules['nominees.*.dob'] = 'required|date';
            $rules['nominees.*.mobile'] = 'required|string|max:20';
            $rules['nominees.*.email'] = 'required|email|max:255';
            $rules['nominees.*.share'] = 'required|numeric|min:1|max:100';
            $rules['nominees.*.address1'] = 'required_without:nominees.*.same_address|nullable|string|max:255';
            $rules['nominees.*.address2'] = 'required_without:nominees.*.same_address|nullable|string|max:255';
            $rules['nominees.*.pincode'] = 'required_without:nominees.*.same_address|nullable|string|max:10';
            $rules['nominees.*.city'] = 'required_without:nominees.*.same_address|nullable|string|max:255';
            $rules['nominees.*.state'] = 'required_without:nominees.*.same_address|nullable|string|max:255';
        }

        $request->validate($rules);

        if ($request->has_nominee == '1') {
            $totalShare = collect($request->nominees)->sum('share');
            if ($totalShare != 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total share across all nominees must be exactly 100%. Current total: ' . $totalShare . '%',
                ], 422);
            }
        }

        $submission->has_nominee = (bool)$request->has_nominee;
        $submission->nominee_data = $request->has_nominee == '1' ? $request->nominees : null;
        $submission->nominee_completed_at = now();
        $submission->save();
        $submission->refresh();

        $nextStep = $submission->getNextStep($this->getCompanyId($submission)) ?? $submission->current_step;
        $submission->update(['current_step' => $nextStep]);

        return response()->json([
            'success' => true,
            'message' => 'Nominee details updated successfully',
            'next_step' => $nextStep,
            'redirect' => $nextStep ? route('ekyc.form.step', ['step' => $nextStep]) : route('ekyc.form.complete'),
        ]);
    }

    private function processDocumentsStep(Request $request, EkycSubmission $submission)
    {
        $request->validate([
            'signature_data' => 'required|string',
            'income_proof' => 'nullable|file|max:5120',
        ]);

        $additionalData = $submission->additional_data ?? [];

        // Handle Signature (already formatted as base64 from capture)
        if (str_starts_with($request->signature_data, 'data:image')) {
            $additionalData['signature'] = $request->signature_data;
        }

        // Handle Income Proof (Optional file upload)
        if ($request->hasFile('income_proof')) {
            $path = $request->file('income_proof')->store('ekyc/documents', 'public');
            $additionalData['document_paths']['income_proof'] = $path;
        }

        $submission->additional_data = $additionalData;
        $submission->documents_completed_at = now();
        $submission->save();
        $submission->refresh();

        Log::info("Documents Step Completed. Time: " . $submission->documents_completed_at);

        $companyId = $this->getCompanyId($submission);
        Log::info("Company ID: " . $companyId);

        $settings = getCompanyAllSetting($companyId);
        Log::info("e-Sign Enabled Setting: " . ($settings['ekyc_esign'] ?? 'OFF'));
        Log::info("PDF Templates: " . ($settings['ekyc_pdf_templates'] ?? 'NONE'));

        $enabledSteps = EkycSubmission::getEnabledSteps($companyId);
        Log::info("Enabled Steps: " . json_encode($enabledSteps));

        // Debug isStepCompleted
        foreach ($enabledSteps as $idx => $step) {
            $isComplete = $submission->isStepCompleted($idx, $companyId);
            Log::info("Step $idx ({$step['name']}): " . ($isComplete ? 'COMPLETED' : 'PENDING'));
        }

        $nextStep = $submission->getNextStep($companyId) ?? $submission->current_step;
        Log::info("Calculated Next Step: " . $nextStep);

        $submission->update(['current_step' => $nextStep]);

        return response()->json([
            'success' => true,
            'message' => 'Documents and Identity verified successfully!',
            'next_step' => $nextStep,
            'redirect' => $nextStep ? route('ekyc.form.step', ['step' => $nextStep]) : route('ekyc.form.complete'),
        ]);
    }

    /**
     * Initialize Digio Selfie session
     */
    public function initializeDigioSelfie(Request $request)
    {
        $submission = EkycSubmission::where('session_id', session('ekyc_session_id'))->first();
        if (!$submission) {
            return response()->json(['success' => false, 'message' => 'No active submission found'], 404);
        }

        $mobile = $submission->mobile_number;
        $identifier = (strpos($mobile, '+91') === 0) ? $mobile : '+91' . $mobile;

        Log::info('Initializing Digio Selfie for Identifier: ' . $identifier);

        $this->digioService->loadSettings($this->getCompanyId($submission));
        $result = $this->digioService->initializeSelfie($identifier);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'digio_request_id' => $result['request_id'],
                'digio_access_token' => $result['access_token'],
                'digio_identifier' => $identifier
            ]);
        }

        return response()->json(['success' => false, 'message' => $result['message']], 400);
    }

    /**
     * Confirm Digio Selfie and fetch image
     */
    public function confirmSelfie(Request $request)
    {
        $request->validate([
            'digio_request_id' => 'required|string',
        ]);

        $submission = EkycSubmission::where('session_id', session('ekyc_session_id'))->first();
        if (!$submission) {
            return response()->json(['success' => false, 'message' => 'No active submission found'], 404);
        }

        $this->digioService->loadSettings($this->getCompanyId($submission));
        $result = $this->digioService->getKycRequestData($request->digio_request_id);

        if ($result['success']) {
            $data = $result['data'];

            // Log for debugging
            Log::info('Digio Selfie Confirmation Data: ' . json_encode($data));

            // Find liveness action and image
            $actions = $data['actions'] ?? [];
            $selfiePath = null;

            foreach ($actions as $action) {
                if (in_array($action['type'], ['liveness', 'selfie']) && !empty($action['details']['facematch']['id_data']['image'])) {
                    $imageData = $action['details']['facematch']['id_data']['image'];
                    $imageData = base64_decode($imageData);
                    $fileName = 'aadhaar_photo_' . time() . '_' . $submission->id . '.jpg';
                    $path = 'ekyc/documents/' . $fileName;
                    Storage::disk('public')->put($path, $imageData);
                    $selfiePath = $path; // Temp variable, will be stored in additional_data below
                    break;
                }
            }

            if ($selfiePath) {
                $submission->selfie_path = $selfiePath;
                $submission->face_verified_at = now();
                $submission->face_match_score = 100.00;

                // Save geolocation if provided
                if ($request->has('latitude') && $request->has('longitude')) {
                    $additionalData = $submission->additional_data ?? [];
                    $additionalData['geolocation'] = [
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                        'timestamp' => now()->toDateTimeString()
                    ];
                    $submission->additional_data = $additionalData;
                }

                $submission->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Selfie verified successfully',
                    'selfie_url' => Storage::url($selfiePath)
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Selfie image not found in Digio response'], 400);
        }

        return response()->json(['success' => false, 'message' => $result['message']], 400);
    }

    /**
     * Process Video KYC step
     */
    private function processVideoKycStep(Request $request, EkycSubmission $submission)
    {
        $request->validate([
            'preferred_date' => 'required|date|after:today',
        ]);

        $submission->update([
            'video_kyc_scheduled_at' => $request->preferred_date,
            'current_step' => $submission->getNextStep($this->getCompanyId($submission)) ?? $submission->current_step,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Video KYC scheduled successfully',
            'redirect' => route('ekyc.form.complete'),
        ]);
    }

    /**
     * Show completion page
     */
    public function complete(Request $request)
    {
        $sessionId = $request->session()->get('ekyc_session_id');

        if (!$sessionId) {
            return redirect()->route('ekyc.form.start');
        }

        $submission = EkycSubmission::where('session_id', $sessionId)->first();

        if (!$submission) {
            return redirect()->route('ekyc.form.start');
        }

        $companyId = $this->getCompanyId($submission);
        $settings = getCompanyAllSetting($companyId);
        $enabledSteps = EkycSubmission::getEnabledSteps($companyId);

        Log::info("Completion Page Reached. Submission: " . $submission->id . ", Company: " . $companyId);
        Log::info("e-Sign Enabled: " . ($settings['ekyc_esign'] ?? 'OFF'));
        Log::info("Enabled Steps Map: " . json_encode(array_column($enabledSteps, 'name')));

        foreach ($enabledSteps as $idx => $step) {
            Log::info("Step $idx ({$step['name']}) Status: " . ($submission->isStepCompleted($idx, $companyId) ? 'DONE' : 'PENDING'));
        }

        // Mark as completed ONLY if all required steps are done
        $pendingSteps = $submission->getPendingVerificationSteps($this->getCompanyId($submission));
        if (!empty($pendingSteps)) {
            $nextStep = $submission->getNextStep($this->getCompanyId($submission));
            return redirect()->route('ekyc.form.step', ['step' => $nextStep]);
        }

        if (!$submission->completed_at) {
            $submission->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        $template = EkycUiTemplate::getActive();

        return view('ekyc::form.complete', compact('submission', 'template'));
    }

    /**
     * Get view name for step
     */
    /**
     * Confirm Aadhaar and check name match with PAN
     */
    public function confirmAadhaar(Request $request)
    {
        Log::info('Confirm Aadhaar Hit. Request Data: ' . json_encode($request->all()));

        $request->validate([
            'digio_request_id' => 'required',
        ]);

        $sessionId = $request->session()->get('ekyc_session_id');
        $submission = EkycSubmission::where('session_id', $sessionId)->first();

        if (!$submission) {
            return response()->json(['success' => false, 'message' => 'Session expired']);
        }

        $this->digioService->loadSettings($this->getCompanyId($submission));
        $result = $this->digioService->getKycRequestData($request->digio_request_id);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => 'Verification failed on Digio: ' . ($result['message'] ?? 'Unknown error')]);
        }

        $aadhaarData = $result['data'];
        Log::info('=== AADHAAR CONFIRMATION DEBUG START ===');
        Log::info('Full Digio Response: ' . json_encode($aadhaarData));

        if (is_array($aadhaarData)) {
            Log::info('Top-level keys: ' . json_encode(array_keys($aadhaarData)));

            if (isset($aadhaarData['actions']) && is_array($aadhaarData['actions'])) {
                Log::info('Actions count: ' . count($aadhaarData['actions']));
                foreach ($aadhaarData['actions'] as $idx => $action) {
                    Log::info("Action $idx type: " . ($action['type'] ?? 'N/A'));
                    if (isset($action['details']) && is_array($action['details'])) {
                        Log::info("Action $idx details keys: " . json_encode(array_keys($action['details'])));
                    }
                }
            }

            if (isset($aadhaarData['details']) && is_array($aadhaarData['details'])) {
                Log::info('Top-level details keys: ' . json_encode(array_keys($aadhaarData['details'])));
            }

            if (isset($aadhaarData['entities']) && is_array($aadhaarData['entities'])) {
                Log::info('Entities count: ' . count($aadhaarData['entities']));
            }
        }
        Log::info('=== AADHAAR CONFIRMATION DEBUG END ===');

        $aadhaarName = '';

        // Strategy 1: Check in actions (standard KYC response)
        if (isset($aadhaarData['actions']) && is_array($aadhaarData['actions'])) {
            foreach ($aadhaarData['actions'] as $action) {
                if (isset($action['details']['name'])) {
                    $aadhaarName = $action['details']['name'];
                    break;
                }
                // Sometimes it's in a different structure within details
                if (isset($action['details']['aadhaar']['name'])) {
                    $aadhaarName = $action['details']['aadhaar']['name'];
                    break;
                }
            }
        }

        // Strategy 2: Check top level details
        if (!$aadhaarName && isset($aadhaarData['details']['name'])) {
            $aadhaarName = $aadhaarData['details']['name'];
        }

        // Strategy 3: Check entities (if Digio parses it differently)
        if (!$aadhaarName && isset($aadhaarData['entities'][0]['details']['name'])) {
            $aadhaarName = $aadhaarData['entities'][0]['details']['name'];
        }

        if (!$aadhaarName) {
            Log::error('Aadhaar Name Not Found in Digio Response. Structure: ' . json_encode($aadhaarData));
            return response()->json([
                'success' => false,
                'message' => 'Could not retrieve name from Aadhaar details. Please check logs or try again.'
            ]);
        }

        $panName = strtoupper(trim($submission->pan_name));
        $aadhaarNameUpper = strtoupper(trim($aadhaarName));

        // Log the comparison for debugging
        Log::info("Comparing PAN Name: '{$panName}' with Aadhaar Name: '{$aadhaarNameUpper}'");

        // Basic comparison. In production, you might want more fuzzy matching.
        $percent = 0;
        similar_text($panName, $aadhaarNameUpper, $percent);

        if ($percent < 60) { // Using 60% match which we used for Bank/PAN
            return response()->json([
                'success' => false,
                'is_mismatch' => true,
                'pan_name' => $panName,
                'aadhaar_name' => $aadhaarNameUpper,
                'message' => "Name Mismatch: Your PAN name is '{$panName}', but Aadhaar shows '{$aadhaarNameUpper}'. Both must match."
            ]);
        }

        // Update verification time first
        $submission->aadhaar_verified_at = now();
        $submission->aadhaar_data = $aadhaarData;

        // --- DETAILED DATA EXTRACTION ---
        $details = $aadhaarData['details'] ?? [];
        $address = $details['address'] ?? [];

        // Try to get data from actions if top-level details are sparse
        if (isset($aadhaarData['actions'])) {
            foreach ($aadhaarData['actions'] as $action) {
                if (!empty($action['details']['aadhaar'])) {
                    $details = array_merge($details, $action['details']['aadhaar']);
                    if (!empty($action['details']['aadhaar']['address'])) {
                        $address = array_merge($address, $action['details']['aadhaar']['address']);
                    }
                }
            }
        }

        $aadhaarNumber = $details['aadhaar_number'] ?? ($details['id_number'] ?? null);
        $dob = $details['dob'] ?? null;
        $gender = $details['gender'] ?? null;

        if ($aadhaarNumber) {
            $submission->aadhaar_number = (string)$aadhaarNumber;
        }

        // CRITICAL: Save aadhaar_data FIRST so extraction can use it
        $submission->aadhaar_data = $aadhaarData;
        $submission->aadhaar_verified_at = now();
        $submission->save();

        // SAVE AADHAAR PHOTO & DOCUMENT IF AVAILABLE
        Log::info('Attempting Aadhaar photo extraction...');
        $idProofPath = $this->extractAndSaveAadhaarPhoto($submission);
        $aadhaarDocPath = null;

        // Still need to download the document if it's in actions
        if (isset($aadhaarData['actions'])) {
            foreach ($aadhaarData['actions'] as $action) {
                $fileId = $action['file_id'] ?? null;
                if ($fileId && !$aadhaarDocPath) {
                    $fileRes = $this->digioService->downloadFile($fileId);
                    if ($fileRes['success']) {
                        $ext = str_contains($fileRes['mime_type'] ?? '', 'pdf') ? 'pdf' : 'xml';
                        $docName = 'aadhaar_doc_' . time() . '_' . $submission->id . '.' . $ext;
                        $docPath = 'ekyc/documents/' . $docName;
                        Storage::disk('public')->put($docPath, $fileRes['content']);
                        $aadhaarDocPath = $docPath;
                        Log::info("Aadhaar document saved: {$aadhaarDocPath}");
                    }
                }
            }
        }

        // Store all extracted data
        $submission->additional_data = array_merge($submission->additional_data ?? [], [
            'aadhaar_linked_mobile' => $details['mobile_number'] ?? null,
            'aadhaar_id' => $aadhaarNumber,
            'unique_request_id' => $request->digio_request_id,
            'aadhaar_name' => $aadhaarNameUpper,
            'aadhaar_dob' => $dob,
            'aadhaar_gender' => $gender,
            'aadhaar_address' => [
                'house' => $address['house'] ?? ($address['hno'] ?? null),
                'street' => $address['street'] ?? ($address['loc'] ?? null),
                'locality' => $address['locality'] ?? null,
                'district' => $address['district'] ?? ($address['dist'] ?? null),
                'state' => $address['state'] ?? null,
                'pincode' => $address['pincode'] ?? ($address['pc'] ?? null),
                'full_address' => $details['full_address'] ?? null,
            ],
            'id_proof_path' => $idProofPath,
            'aadhaar_document_path' => $aadhaarDocPath,
            'profile_photo_path' => $idProofPath // Save as profile photo permanently
        ]);
        $submission->save();
        $submission->refresh();

        // Now calculate next step on the updated instance
        $nextStep = $submission->getNextStep($this->getCompanyId($submission)) ?? $submission->current_step;
        $submission->update(['current_step' => $nextStep]);

        return response()->json([
            'success' => true,
            'message' => 'Aadhaar verified and name matched successfully!',
            'redirect' => route('ekyc.form.step', ['step' => $nextStep])
        ]);
    }

    /**
     * Initialize e-Sign process for a specific template
     */
    public function initializeEsign(Request $request)
    {
        $request->validate([
            'template_id' => 'required|string'
        ]);

        $sessionId = session('ekyc_session_id');
        $submission = EkycSubmission::where('session_id', $sessionId)->first();
        if (!$submission) {
            return response()->json(['success' => false, 'message' => 'No active submission found'], 404);
        }

        $templateId = $request->template_id;
        $additionalData = $submission->additional_data ?? [];
        $esignDocs = $additionalData['esign_docs'] ?? [];

        // 1. Check if we already have a document registered for this template
        if (!empty($esignDocs[$templateId]['document_id']) && empty($esignDocs[$templateId]['signed_at'])) {
            return response()->json([
                'success' => true,
                'document_id' => $esignDocs[$templateId]['document_id'],
                'access_token' => $esignDocs[$templateId]['access_token'] ?? null
            ]);
        }

        // 2. Generate PDF for this specific template
        $pdfResult = $this->generateEsignPdf($submission, $templateId);
        if (!$pdfResult['success']) {
            return response()->json(['success' => false, 'message' => $pdfResult['message']], 500);
        }

        $filePath = $pdfResult['file_path'];

        // 3. Register with Digio
        $mobile = $submission->mobile_number;
        $identifier = (strpos($mobile, '+91') === 0) ? $mobile : '+91' . $mobile;

        $signers = [
            [
                'identifier' => $identifier,
                'name' => $submission->pan_name,
                'reason' => 'E-KYC Application Signing'
            ]
        ];

        $this->digioService->loadSettings($this->getCompanyId($submission));
        $result = $this->digioService->createEsignRequest($filePath, $signers);

        if ($result['success']) {
            $esignDocs[$templateId] = [
                'document_id' => $result['document_id'],
                'access_token' => $result['access_token'],
                'status' => 'pending',
                'name' => $pdfResult['template_name'] ?? 'Document'
            ];

            $additionalData['esign_docs'] = $esignDocs;
            $submission->additional_data = $additionalData;
            $submission->save();

            return response()->json([
                'success' => true,
                'document_id' => $result['document_id'],
                'access_token' => $result['access_token']
            ]);
        }
        return response()->json(['success' => false, 'message' => $result['message']], 400);
    }

    /**
     * Confirm e-Sign completion for a specific document
     */
    public function confirmEsign(Request $request)
    {
        $request->validate(['document_id' => 'required|string']);

        $submission = EkycSubmission::where('session_id', session('ekyc_session_id'))->first();
        if (!$submission) {
            return response()->json(['success' => false, 'message' => 'Session expired'], 404);
        }

        // Identify which template this document belongs to
        $additionalData = $submission->additional_data ?? [];
        $esignDocs = $additionalData['esign_docs'] ?? [];
        $templateId = null;

        foreach ($esignDocs as $tid => $docInfo) {
            if (($docInfo['document_id'] ?? '') === $request->document_id) {
                $templateId = $tid;
                break;
            }
        }

        if (!$templateId) {
            return response()->json(['success' => false, 'message' => 'Document ID not found in current session'], 404);
        }

        // Download signed document
        $this->digioService->loadSettings($this->getCompanyId($submission));
        $result = $this->digioService->downloadSignedDocument($request->document_id);

        if ($result['success']) {
            $fileName = 'signed_' . $templateId . '_' . time() . '_' . $submission->id . '.pdf';
            $path = 'ekyc/documents/' . $fileName;
            Storage::disk('public')->put($path, $result['content']);

            $esignDocs[$templateId]['status'] = 'signed';
            $esignDocs[$templateId]['signed_at'] = now()->toDateTimeString();
            $esignDocs[$templateId]['signed_path'] = $path;

            $additionalData['esign_docs'] = $esignDocs;

            // Legacy support: if this is the only or primary doc, keep root fields updated
            $additionalData['esign_completed_at'] = now()->toDateTimeString();
            $additionalData['signed_document_path'] = $path;

            $submission->additional_data = $additionalData;
            $submission->save();

            // Check if ALL required documents are signed
            $settings = getCompanyAllSetting($this->getCompanyId($submission));
            $templates = !empty($settings['ekyc_pdf_templates']) ? json_decode($settings['ekyc_pdf_templates'], true) : [];
            $allSigned = true;
            foreach ($templates as $t) {
                if (($t['is_enabled'] ?? 'off') === 'on' && ($esignDocs[$t['id']]['status'] ?? '') !== 'signed') {
                    $allSigned = false;
                    break;
                }
            }

            $nextStep = null;
            if ($allSigned) {
                $nextStep = $submission->getNextStep($this->getCompanyId($submission));
                $submission->update(['current_step' => $nextStep]);
            }

            return response()->json([
                'success' => true,
                'message' => 'E-Sign completed successfully',
                'all_signed' => $allSigned,
                'redirect' => $allSigned ? ($nextStep ? route('ekyc.form.step', ['step' => $nextStep]) : route('ekyc.form.complete')) : null
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Failed to verify e-sign with Digio'], 400);
    }

    /**
     * Preview the generated PDF for a specific template
     */
    /**
     * Preview e-Sign document (PDF or HTML)
     */
    public function viewEsignPdf(Request $request, $templateId)
    {
        $sessionId = session('ekyc_session_id');
        $submission = null;

        if ($request->has('submission_id') && auth()->check()) {
            $submission = EkycSubmission::find($request->submission_id);
        }
        else {
            $submission = EkycSubmission::where('session_id', $sessionId)->first();
        }

        if (!$submission) {
            abort(404, 'Submission Not Found or Session Expired');
        }

        $format = $request->query('format', 'pdf');

        if ($format === 'html') {
            $htmlResult = $this->generateEsignHtml($submission, $templateId);
            if (!$htmlResult['success']) {
                abort(500, $htmlResult['message']);
            }
            return response($htmlResult['html'])->header('Content-Type', 'text/html');
        }

        $pdfResult = $this->generateEsignPdf($submission, $templateId);
        if (!$pdfResult['success']) {
            // Fallback to HTML if PDF fails due to missing library (user friendly)
            if (strpos($pdfResult['message'] ?? '', 'dompdf.wrapper') !== false) {
                $htmlResult = $this->generateEsignHtml($submission, $templateId);
                if ($htmlResult['success']) {
                    return response($htmlResult['html'])->header('Content-Type', 'text/html');
                }
            }
            abort(500, $pdfResult['message']);
        }

        return response()->file($pdfResult['file_path'], [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($pdfResult['file_path']) . '"'
        ]);
    }

    /**
     * Generate HTML for e-Sign document
     */
    private function generateEsignHtml(EkycSubmission $submission, $specificTemplateId = null)
    {
        try {
            $settings = getCompanyAllSetting($this->getCompanyId($submission));
            $templates = !empty($settings['ekyc_pdf_templates']) ? json_decode($settings['ekyc_pdf_templates'], true) : [];

            $activeTemplates = array_filter($templates, function ($t) use ($specificTemplateId) {
                if ($specificTemplateId && $specificTemplateId !== 'combined') {
                    return ($t['id'] ?? '') === $specificTemplateId;
                }
                return ($t['is_enabled'] ?? 'off') === 'on';
            });

            if (empty($activeTemplates)) {
                return [
                    'success' => false,
                    'message' => 'No active templates configured.'
                ];
            }

            // Prepare Placeholder Data
            $placeholders = $this->getPlaceholderData($submission);

            $combinedHtml = '<style>
                body { font-family: sans-serif; font-size: 11px; line-height: 1.4; color: #333; padding: 20px; }
                .page-break { page-break-after: always; margin: 20px 0; border-bottom: 2px dashed #ccc; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
                table, th, td { border: 1px solid #ddd; }
                th, td { padding: 8px; text-align: left; }
                .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #10b981; padding-bottom: 10px; }
                .section-title { background: #f4f4f4; padding: 5px 10px; font-weight: bold; margin-top: 20px; }
                img.signature { max-height: 60px; }
                img.selfie { max-height: 120px; border: 1px solid #ccc; }
                img.company-logo { max-height: 50px; }
                .document-block { background: #fff; padding: 30px; border: 1px solid #eee; margin-bottom: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
            </style>';

            $templateName = 'Document';
            foreach ($activeTemplates as $index => $template) {
                $content = $template['content'] ?? '';
                $templateName = $template['name'] ?? 'Document';

                // Replace placeholders
                foreach ($placeholders as $key => $value) {
                    $content = str_replace('{' . $key . '}', $value, $content);
                }

                $combinedHtml .= '<div class="document-block">';
                $combinedHtml .= '<h3>' . $templateName . '</h3>';
                $combinedHtml .= $content;
                $combinedHtml .= '</div>';

                if ($index < count($activeTemplates) - 1) {
                    $combinedHtml .= '<div class="page-break"></div>';
                }
            }

            return [
                'success' => true,
                'html' => $combinedHtml,
                'template_name' => $templateName
            ];
        }
        catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'HTML generation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate PDF based on active templates or a specific template
     */
    private function generateEsignPdf(EkycSubmission $submission, $specificTemplateId = null)
    {
        try {
            $htmlResult = $this->generateEsignHtml($submission, $specificTemplateId);
            if (!$htmlResult['success']) {
                return $htmlResult;
            }

            $combinedHtml = $htmlResult['html'];
            $templateName = $htmlResult['template_name'];

            $tempDir = storage_path('app/public/ekyc/temp');
            if (!file_exists($tempDir))
                mkdir($tempDir, 0777, true);

            $idSuffix = $specificTemplateId ?: 'combined';
            $fileName = 'app_form_' . $submission->id . '_' . $idSuffix . '.pdf';
            $filePath = $tempDir . '/' . $fileName;

            // Check if Dompdf exists
            try {
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadHTML($combinedHtml);
                $pdf->save($filePath);
            }
            catch (\ReflectionException $e) {
                return [
                    'success' => false,
                    'message' => 'PDF Library (dompdf) not found. Please contact administrator (ReflectionException).'
                ];
            }
            catch (\Illuminate\Contracts\Container\BindingResolutionException $e) {
                return [
                    'success' => false,
                    'message' => 'PDF Library (dompdf) binding not found. Please contact administrator (BindingResolutionException).'
                ];
            }

            return [
                'success' => true,
                'file_path' => $filePath,
                'template_name' => $templateName
            ];

        }
        catch (\Exception $e) {
            Log::error('Dynamic PDF Generation Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'PDF generation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Map submission data to placeholders
     */
    private function getPlaceholderData(EkycSubmission $submission)
    {
        $aadhaar_photo = null;
        if (!empty($submission->aadhaar_data)) {
            $aadhaarData = $submission->aadhaar_data;
            if (isset($aadhaarData['actions'])) {
                foreach ($aadhaarData['actions'] as $action) {
                    if (isset($action['details']['image'])) {
                        $aadhaar_photo = $action['details']['image'];
                        break;
                    }
                }
            }
        }

        $signatureData = $submission->additional_data['signature'] ?? '';
        $selfiePath = $submission->selfie_path ? storage_path('app/public/' . $submission->selfie_path) : null;
        $selfieData = '';
        if ($selfiePath && file_exists($selfiePath)) {
            $type = pathinfo($selfiePath, PATHINFO_EXTENSION);
            $data = file_get_contents($selfiePath);
            $selfieData = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $settings = getCompanyAllSetting($this->getCompanyId($submission));
        $companyLogo = '';
        if (!empty($settings['ekyc_company_logo'])) {
            $logoPath = public_path($settings['ekyc_company_logo']);
            if (file_exists($logoPath)) {
                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                $data = file_get_contents($logoPath);
                $companyLogo = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        $authSign = '';
        if (!empty($settings['ekyc_company_auth_sign'])) {
            $signPath = public_path($settings['ekyc_company_auth_sign']);
            if (file_exists($signPath)) {
                $type = pathinfo($signPath, PATHINFO_EXTENSION);
                $data = file_get_contents($signPath);
                $authSign = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        $aadhaar_address = '';
        if (!empty($submission->aadhaar_data['details']['address'])) {
            $addr = $submission->aadhaar_data['details']['address'];
            $aadhaar_address = implode(', ', array_filter([
                $addr['house'] ?? '',
                $addr['street'] ?? '',
                $addr['loc'] ?? '',
                $addr['dist'] ?? '',
                $addr['state'] ?? '',
                $addr['pc'] ?? ''
            ]));
        }

        return [
            'full_name' => $submission->pan_name ?? $submission->additional_data['full_name'] ?? 'N/A',
            'pan_number' => $submission->pan_number,
            'aadhaar_number' => $submission->aadhaar_number ?? data_get($submission, 'additional_data.aadhaar_id', 'N/A'),
            'dob' => $submission->pan_dob ? $submission->pan_dob->format('d-m-Y') : 'N/A',
            'mobile_number' => $submission->mobile_number,
            'email' => $submission->email ?? 'N/A',
            'bank_account_number' => $submission->bank_account_number,
            'bank_ifsc' => $submission->bank_ifsc,
            'bank_name' => data_get($submission, 'additional_data.bank_name', 'N/A'),
            'father_name' => $submission->father_name ?? 'N/A',
            'mother_name' => $submission->mother_name ?? 'N/A',
            'gender' => data_get($submission, 'additional_data.gender', data_get($submission, 'aadhaar_data.details.gender', 'N/A')),
            'marital_status' => $submission->marital_status ?? 'N/A',
            'occupation' => $submission->occupation ?? 'N/A',
            'annual_income' => $submission->annual_income ?? 'N/A',
            'trading_experience' => $submission->trading_experience ?? '0',
            'networth' => $submission->networth ?? 'N/A',
            'networth_date' => $submission->networth_date ? $submission->networth_date->format('d-m-Y') : 'N/A',
            'user_address' => $aadhaar_address ?: data_get($submission, 'additional_data.address', 'N/A'),
            'city' => data_get($submission, 'additional_data.city', data_get($submission, 'aadhaar_data.details.address.dist', 'N/A')),
            'state' => data_get($submission, 'additional_data.state', data_get($submission, 'aadhaar_data.details.address.state', 'N/A')),
            'pin_code' => data_get($submission, 'additional_data.pin_code', data_get($submission, 'aadhaar_data.details.address.pc', 'N/A')),
            'application_no' => $submission->id,
            'client_code' => data_get($submission, 'additional_data.client_code', 'HO' . str_pad($submission->id, 5, '0', STR_PAD_LEFT)),
            'boid' => data_get($submission, 'additional_data.boid', 'N/A'),
            'client_id' => data_get($submission, 'additional_data.client_id', $submission->id),
            'dp_id' => data_get($submission, 'additional_data.dp_id', 'N/A'),
            'introducer_name' => $submission->rm_pp_code ?? 'HO',
            'signature' => $signatureData ? '<img src="' . $signatureData . '" class="signature">' : '[Signature Missing]',
            'selfie' => $selfieData ? '<img src="' . $selfieData . '" class="selfie">' : ($aadhaar_photo ? '<img src="data:image/jpeg;base64,' . $aadhaar_photo . '" class="selfie">' : '[Selfie Missing]'),
            'company_name' => $settings['ekyc_company_name'] ?? '',
            'company_address' => $settings['ekyc_company_address'] ?? '',
            'company_logo' => $companyLogo ? '<img src="' . $companyLogo . '" class="company-logo" style="max-width: 150px;">' : '',
            'auth_sign' => $authSign ? '<img src="' . $authSign . '" class="auth-sign" style="max-width: 150px;">' : '',
            'current_date' => now()->format('d-m-Y')
        ];
    }

    private function getStepViewName($step, $submission = null)
    {
        $enabledSteps = EkycSubmission::getEnabledSteps($this->getCompanyId($submission));
        $stepName = $enabledSteps[$step]['name'] ?? '';

        $viewMap = [
            'Mobile Verification' => 'otp-verify', // Though Step 1 redirects to start
            'Email Verification' => 'email-entry', // FIXED: Point to entry screen, not OTP
            'PAN Verification' => 'pan',
            'Aadhaar Verification' => 'aadhaar',
            'Bank Account Verification' => 'bank',
            'Trading Segments' => 'segments',
            'Personal Details' => 'personal-details',
            'Compliance Declarations' => 'compliance',
            'Nominee Details' => 'nominee',
            'Selfie Liveness Capture' => 'selfie',
            'Document Upload' => 'documents',
            'e-Sign Verification' => 'esign',
            'Video KYC (IPV)' => 'video-kyc',
        ];

        return $viewMap[$stepName] ?? 'pan';
    }

    private function saveBase64Image($base64Data, $prefix, $id)
    {
        try {
            if (str_contains($base64Data, ',')) {
                $base64Data = explode(',', $base64Data)[1];
            }
            $decoded = base64_decode($base64Data);
            if (!$decoded)
                return null;

            $fileName = $prefix . time() . '_' . $id . '.jpg';
            $path = 'ekyc/documents/' . $fileName;
            Storage::disk('public')->put($path, $decoded);
            return $path;
        }
        catch (\Exception $e) {
            Log::error("Error saving base64 image ({$prefix}): " . $e->getMessage());
            return null;
        }
    }

    private function extractAndSaveAadhaarPhoto($submission)
    {
        $aadhaarData = $submission->aadhaar_data;
        if (empty($aadhaarData)) {
            Log::warning("extractAndSaveAadhaarPhoto: No aadhaar_data found for submission {$submission->id}");
            return null;
        }

        Log::info("=== EXTRACT AADHAAR PHOTO DEBUG START (Submission {$submission->id}) ===");
        Log::info("Aadhaar data top-level keys: " . json_encode(array_keys($aadhaarData)));

        $idProofPath = null;

        // 1. Try to find photo in actions
        if (isset($aadhaarData['actions'])) {
            Log::info("Checking actions array, count: " . count($aadhaarData['actions']));
            foreach ($aadhaarData['actions'] as $idx => $action) {
                Log::info("Action $idx - type: " . ($action['type'] ?? 'N/A'));
                Log::info("Action $idx - details keys: " . json_encode(array_keys($action['details'] ?? [])));

                // Prioritize 'image' and 'photo' keys as seen in production logs, including nested 'aadhaar' versions
                $imageData = $action['details']['image']
                    ?? ($action['details']['photo']
                    ?? ($action['details']['aadhaar']['image']
                    ?? ($action['details']['aadhaar']['photo']
                    ?? ($action['details']['facematch']['id_data']['image']
                    ?? ($action['details']['id_data']['image'] ?? null)))));

                if ($imageData) {
                    Log::info("Found image in action $idx! Attempting to save...");
                    $idProofPath = $this->saveBase64Image($imageData, 'aadhaar_photo_', $submission->id);
                    if ($idProofPath) {
                        Log::info("Successfully saved photo from actions: $idProofPath");
                        break;
                    }
                    else {
                        Log::error("Failed to save image from action $idx");
                    }
                }
                else {
                    Log::info("No image found in action $idx");
                }
            }
        }
        else {
            Log::info("No actions array in aadhaar_data");
        }

        // 2. Fallback: Search in top-level details or entities
        if (!$idProofPath) {
            Log::info("Photo not found in actions, trying fallback locations...");

            $imageData = $aadhaarData['details']['photo']
                ?? ($aadhaarData['details']['image']
                ?? ($aadhaarData['details']['id_data']['image'] ?? null));

            if ($imageData) {
                Log::info("Found image in top-level details!");
            }

            if (!$imageData && isset($aadhaarData['entities'])) {
                Log::info("Checking entities array, count: " . count($aadhaarData['entities']));
                foreach ($aadhaarData['entities'] as $idx => $entity) {
                    $imageData = $entity['details']['photo']
                        ?? ($entity['details']['image']
                        ?? ($entity['details']['id_data']['image'] ?? null));
                    if ($imageData) {
                        Log::info("Found image in entity $idx!");
                        break;
                    }
                }
            }

            if ($imageData) {
                $idProofPath = $this->saveBase64Image($imageData, 'aadhaar_photo_fb_', $submission->id);
                if ($idProofPath) {
                    Log::info("Successfully saved photo from fallback: $idProofPath");
                }
                else {
                    Log::error("Failed to save image from fallback location");
                }
            }
            else {
                Log::warning("No image data found in any fallback location");
            }
        }

        if (!$idProofPath) {
            Log::error("=== PHOTO EXTRACTION FAILED - No photo found anywhere ===");
        }
        else {
            Log::info("=== PHOTO EXTRACTION SUCCESS: $idProofPath ===");
        }

        return $idProofPath;
    }

    /**
     * Reverse Geocode Proxy to avoid CORS/403
     */
    public function reverseGeocode(Request $request)
    {
        $lat = $request->lat;
        $lng = $request->lng;

        if (!$lat || !$lng) {
            return response()->json(['success' => false, 'message' => 'Coordinates missing'], 400);
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Workdo-Ekyc-Proxy/1.0 (' . config('app.url') . ')'
            ])->timeout(10)->get("https://nominatim.openstreetmap.org/reverse", [
                'format' => 'json',
                'lat' => $lat,
                'lon' => $lng,
                'zoom' => 18,
                'addressdetails' => 1
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'address' => $data['display_name'] ?? 'Address not available'
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Geocoding service unavailable'], 503);
        }
        catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
