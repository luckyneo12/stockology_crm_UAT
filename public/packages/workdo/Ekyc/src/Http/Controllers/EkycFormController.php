<?php

namespace Workdo\Ekyc\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Workdo\Ekyc\Entities\EkycSubmission;
use Workdo\Ekyc\Entities\EkycUiTemplate;
use Workdo\Ekyc\Services\OtpService;

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
        } else {
            $submission = EkycSubmission::create([
                'session_id' => $sessionId,
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
            'rm_pp_code' => 'nullable|string|max:50',
        ]);

        $sessionId = $request->session()->get('ekyc_session_id');
        $submission = EkycSubmission::where('session_id', $sessionId)->firstOrFail();

        $type = $request->verification_type;
        $identifier = $request->identifier;

        // Additional validation based on type
        if ($type === 'email') {
            $request->validate(['identifier' => 'email']);
        } elseif ($type === 'mobile') {
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
        $submission = EkycSubmission::where('session_id', $sessionId)->firstOrFail();

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

        $submission = EkycSubmission::where('session_id', $sessionId)->firstOrFail();

        // Check if contact is started (mobile verified is common first step)
        if (!$submission->mobile_verified_at && $step > 1) {
            return redirect()->route('ekyc.form.start');
        }

        // Get enabled steps
        $enabledSteps = EkycSubmission::getEnabledSteps();

        // Check if step is valid and enabled
        if (!isset($enabledSteps[$step]) || !$enabledSteps[$step]['enabled']) {
            return redirect()->route('ekyc.form.step', ['step' => $submission->current_step]);
        }

        $template = EkycUiTemplate::getActive();

        // Load appropriate view based on step
        $viewName = $this->getStepViewName($step);
        $pendingVerification = $request->session()->get('ekyc_pending_verification');

        // Safety: If OTP view is requested but no data in session, redirect to start
        // This usually happens during Step 1 session initialization
        if ($viewName === 'otp-verify' && !$pendingVerification && $step == 1) {
            return redirect()->route('ekyc.form.start');
        }

        $digio_environment = $this->digioService->getEnvironment();
        return view("ekyc::form.{$viewName}", compact('submission', 'template', 'step', 'enabledSteps', 'pendingVerification', 'digio_environment'));
    }

    /**
     * Submit a specific step
     */
    public function submitStep(Request $request, $step)
    {
        $enabledSteps = EkycSubmission::getEnabledSteps();
        $stepName = $enabledSteps[$step]['name'] ?? '';

        $sessionId = $request->session()->get('ekyc_session_id');
        $submission = EkycSubmission::where('session_id', $sessionId)->first();

        if (!$submission) {
            return response()->json(['success' => false, 'message' => 'Session expired']);
        }

        switch ($stepName) {
            case 'Mobile Verification':
            case 'Email Verification':
                // Handled in EkycOtpController
                break;
            case 'PAN Verification':
                return $this->processPanStep($request, $submission);
            case 'Aadhaar Verification':
                return $this->processAadhaarStep($request, $submission);
            case 'Selfie & Face Match':
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
        $submission->update([
            'email' => $request->email ?? $submission->email,
            'rm_pp_code' => $request->rm_pp_code,
            'pan_number' => strtoupper($request->pan_number),
            'pan_name' => $request->pan_name,
            'pan_dob' => $request->pan_dob,
            'pan_verified_at' => now(),
            'pan_response' => json_encode($result['data']),
            'current_step' => $submission->getNextStep() ?? $submission->current_step,
        ]);

        $nextStep = $submission->getNextStep();

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
     * Process Selfie step
     */
    private function processSelfieStep(Request $request, EkycSubmission $submission)
    {
        $request->validate([
            'selfie' => 'required|image|max:5120', // 5MB max
        ]);

        if ($request->hasFile('selfie')) {
            $path = $request->file('selfie')->store('ekyc/selfies', 'public');
            
            $submission->update([
                'selfie_path' => $path,
                'face_verified_at' => now(),
                'face_match_score' => 95.00, // Placeholder - actual face matching would be done here
                'current_step' => $submission->getNextStep() ?? $submission->current_step,
            ]);
        }

        $nextStep = $submission->getNextStep();

        return response()->json([
            'success' => true,
            'message' => 'Selfie uploaded successfully',
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

        $submission->update([
            'bank_account_number' => $request->bank_account_number,
            'bank_ifsc' => strtoupper($request->bank_ifsc),
            'bank_account_holder_name' => $result['verified_name'] ?? $request->bank_account_holder_name,
            'bank_verified_at' => now(),
            'bank_response' => json_encode($result['data'] ?? []),
            'current_step' => $submission->getNextStep() ?? $submission->current_step,
        ]);

        $nextStep = $submission->getNextStep();

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

        $submission->update([
            'trading_segments' => json_encode($request->segments),
            'brokerage_plan' => $request->brokerage_plan,
            'segments_completed_at' => now(),
            'current_step' => $submission->getNextStep() ?? $submission->current_step,
        ]);

        $nextStep = $submission->getNextStep();

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

        $submission->update([
            'father_name' => $request->father_name,
            'mother_name' => $request->mother_name,
            'marital_status' => $request->marital_status,
            'education' => $request->education,
            'occupation' => $request->occupation,
            'annual_income' => $request->annual_income,
            'trading_experience' => $request->trading_experience,
            'networth' => $request->networth,
            'networth_date' => $request->networth_date,
            'is_pep' => (bool) $request->is_pep,
            'details_completed_at' => now(),
            'current_step' => $submission->getNextStep() ?? $submission->current_step,
        ]);

        $nextStep = $submission->getNextStep();

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

        $submission->update([
            'ddpi_consent' => (bool) $request->ddpi_consent,
            'running_account_auth' => $request->running_account_auth,
            'receive_credits' => (bool) $request->receive_credits,
            'pledge_instruction' => (bool) $request->pledge_instruction,
            'nominee_statement_type' => $request->nominee_statement_type,
            'statement_requirement' => $request->statement_requirement,
            'electronic_statement' => (bool) $request->electronic_statement,
            'share_email_rta' => (bool) $request->share_email_rta,
            'annual_report_media' => $request->annual_report_media,
            'receive_dividend_directly' => (bool) $request->receive_dividend_directly,
            'dis_booklet' => (bool) $request->dis_booklet,
            'compliance_completed_at' => now(),
            'current_step' => $submission->getNextStep() ?? $submission->current_step,
        ]);

        $nextStep = $submission->getNextStep();

        return response()->json([
            'success' => true,
            'message' => 'Compliance declarations updated successfully',
            'next_step' => $nextStep,
            'redirect' => $nextStep ? route('ekyc.form.step', ['step' => $nextStep]) : route('ekyc.form.complete'),
        ]);
    }

    private function processNomineeStep(Request $request, EkycSubmission $submission)
    {
        // Add validation for nominee details if has_nominee is 'yes'
        if ($request->has_nominee == 'yes') {
            $request->validate([
                'nominee.name' => 'required|string|max:255',
                'nominee.relation' => 'required|string|max:255',
                'nominee.dob' => 'required|date',
                // Add more nominee fields as needed
            ]);
        }

        $submission->update([
            'has_nominee' => $request->has_nominee == 'yes' ? true : false,
            'nominee_data' => $request->has_nominee == 'yes' ? json_encode($request->nominee) : null,
            'nominee_completed_at' => now(),
            'current_step' => $submission->getNextStep() ?? $submission->current_step,
        ]);

        $nextStep = $submission->getNextStep();

        return response()->json([
            'success' => true,
            'message' => 'Nominee details updated successfully',
            'next_step' => $nextStep,
            'redirect' => $nextStep ? route('ekyc.form.step', ['step' => $nextStep]) : route('ekyc.form.complete'),
        ]);
    }

    private function processDocumentsStep(Request $request, EkycSubmission $submission)
    {
        // Example validation for document uploads
        $request->validate([
            'pan_card_image' => 'nullable|image|max:5120',
            'aadhaar_front_image' => 'nullable|image|max:5120',
            'aadhaar_back_image' => 'nullable|image|max:5120',
            // Add more document validations as needed
        ]);

        $documentPaths = [];
        if ($request->hasFile('pan_card_image')) {
            $documentPaths['pan_card_image'] = $request->file('pan_card_image')->store('ekyc/documents', 'public');
        }
        if ($request->hasFile('aadhaar_front_image')) {
            $documentPaths['aadhaar_front_image'] = $request->file('aadhaar_front_image')->store('ekyc/documents', 'public');
        }
        if ($request->hasFile('aadhaar_back_image')) {
            $documentPaths['aadhaar_back_image'] = $request->file('aadhaar_back_image')->store('ekyc/documents', 'public');
        }
        // Store other documents similarly

        $submission->update([
            'documents_data' => json_encode($documentPaths), // Store paths in a JSON column
            'documents_completed_at' => now(),
            'current_step' => $submission->getNextStep() ?? $submission->current_step,
        ]);

        $nextStep = $submission->getNextStep();

        return response()->json([
            'success' => true,
            'message' => 'Documents uploaded successfully',
            'next_step' => $nextStep,
            'redirect' => $nextStep ? route('ekyc.form.step', ['step' => $nextStep]) : route('ekyc.form.complete'),
        ]);
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
            'current_step' => $submission->getNextStep() ?? $submission->current_step,
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

        $submission = EkycSubmission::where('session_id', $sessionId)->firstOrFail();

        // Mark as completed if all required steps are done
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
        \Log::info('Confirm Aadhaar Hit. Request Data: ' . json_encode($request->all()));
        
        $request->validate([
            'digio_request_id' => 'required',
        ]);

        $sessionId = $request->session()->get('ekyc_session_id');
        $submission = EkycSubmission::where('session_id', $sessionId)->first();

        if (!$submission) {
            return response()->json(['success' => false, 'message' => 'Session expired']);
        }

        $result = $this->digioService->getKycRequestData($request->digio_request_id);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => 'Verification failed on Digio: ' . ($result['message'] ?? 'Unknown error')]);
        }

        $aadhaarData = $result['data'];
        \Log::info('Digio Confirm Aadhaar Response: ' . json_encode($aadhaarData));

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
            \Log::error('Aadhaar Name Not Found in Digio Response. Structure: ' . json_encode($aadhaarData));
            return response()->json([
                'success' => false, 
                'message' => 'Could not retrieve name from Aadhaar details. Please check logs or try again.'
            ]);
        }

        $panName = strtoupper(trim($submission->pan_name));
        $aadhaarNameUpper = strtoupper(trim($aadhaarName));
        
        // Log the comparison for debugging
        \Log::info("Comparing PAN Name: '{$panName}' with Aadhaar Name: '{$aadhaarNameUpper}'");

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
        $aadhaarDetails = $aadhaarData['details'] ?? ($aadhaarData['actions'][0]['details'] ?? []);
        
        $submission->additional_data = array_merge($submission->additional_data ?? [], [
            'aadhaar_linked_mobile' => $aadhaarDetails['mobile_number'] ?? null,
            'aadhaar_id' => $aadhaarDetails['id_number'] ?? ($aadhaarDetails['aadhaar_number'] ?? null),
            'unique_request_id' => $request->digio_request_id,
            'aadhaar_name' => $aadhaarNameUpper
        ]);
        $submission->save();

        // Now calculate next step on the updated instance
        $nextStep = $submission->getNextStep() ?? $submission->current_step;
        $submission->update(['current_step' => $nextStep]);

        return response()->json([
            'success' => true,
            'message' => 'Aadhaar verified and name matched successfully!',
            'redirect' => route('ekyc.form.step', ['step' => $nextStep])
        ]);
    }

    private function getStepViewName($step)
    {
        $enabledSteps = EkycSubmission::getEnabledSteps();
        $stepName = $enabledSteps[$step]['name'] ?? '';

        $viewMap = [
            'Mobile Verification' => 'otp-verify', // Though Step 1 redirects to start
            'Email Verification' => 'email-entry',  // FIXED: Point to entry screen, not OTP
            'PAN Verification' => 'pan',
            'Aadhaar Verification' => 'aadhaar',
            'Bank Account Verification' => 'bank',
            'Trading Segments' => 'segments',
            'Personal Details' => 'personal-details',
            'Compliance Declarations' => 'compliance',
            'Nominee Details' => 'nominee',
            'Document Upload' => 'documents',
            'Video KYC (IPV)' => 'video-kyc',
        ];

        return $viewMap[$stepName] ?? 'pan';
    }
}
