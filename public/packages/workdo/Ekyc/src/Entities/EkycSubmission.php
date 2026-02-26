<?php

namespace Workdo\Ekyc\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class EkycSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'session_id',
        'current_step',
        'mobile_number',
        'mobile_verified_at',
        'email',
        'email_verified_at',
        'rm_pp_code',
        'relation',
        'pan_number',
        'pan_name',
        'pan_dob',
        'pan_verified_at',
        'pan_response',
        'aadhaar_number',
        'aadhaar_verified_at',
        'aadhaar_xml_path',
        'aadhaar_data',
        'selfie_path',
        'face_match_score',
        'face_verified_at',
        'bank_account_number',
        'bank_ifsc',
        'bank_account_holder_name',
        'bank_verified_at',
        'bank_response',
        'segments_completed_at',
        'details_completed_at',
        'compliance_completed_at',
        'nominee_completed_at',
        'documents_completed_at',
        'trading_segments',
        'brokerage_plan',
        'father_name',
        'mother_name',
        'marital_status',
        'occupation',
        'annual_income',
        'education',
        'trading_experience',
        'networth',
        'networth_date',
        'is_pep',
        'settlement_frequency',
        'ddpi_consent',
        'running_account_auth',
        'receive_credits',
        'pledge_instruction',
        'nominee_statement_type',
        'statement_requirement',
        'electronic_statement',
        'share_email_rta',
        'annual_report_media',
        'receive_dividend_directly',
        'dis_booklet',
        'has_nominee',
        'nominee_data',
        'video_kyc_scheduled_at',
        'video_kyc_completed_at',
        'video_kyc_officer_id',
        'video_kyc_notes',
        'status',
        'pipeline_id',
        'stage_id',
        'completed_at',
        'rejection_reason',
        'ip_address',
        'user_agent',
        'additional_data',
    ];

    protected $casts = [
        'mobile_verified_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'pan_dob' => 'date',
        'pan_verified_at' => 'datetime',
        'pan_response' => 'array',
        'aadhaar_verified_at' => 'datetime',
        'aadhaar_data' => 'array',
        'face_verified_at' => 'datetime',
        'face_match_score' => 'decimal:2',
        'bank_verified_at' => 'datetime',
        'bank_response' => 'array',
        'video_kyc_scheduled_at' => 'datetime',
        'video_kyc_completed_at' => 'datetime',
        'completed_at' => 'datetime',
        'networth_date' => 'date',
        'additional_data' => 'array',
    ];

    /**
     * Get the user that owns the submission
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the pipeline
     */
    public function pipeline()
    {
        return $this->belongsTo(EkycPipeline::class);
    }

    /**
     * Get the stage
     */
    public function stage()
    {
        return $this->belongsTo(EkycStage::class);
    }

    /**
     * Get OTP logs for this submission
     */
    public function otpLogs()
    {
        return $this->hasMany(EkycOtpLog::class, 'submission_id');
    }

    /**
     * Get the video KYC officer
     */
    public function videoKycOfficer()
    {
        return $this->belongsTo(User::class, 'video_kyc_officer_id');
    }

    /**
     * Check if a specific step is completed
     */
    public function isStepCompleted($step)
    {
        $enabledSteps = self::getEnabledSteps();
        $stepName = $enabledSteps[$step]['name'] ?? '';

        switch ($stepName) {
            case 'Mobile Verification':
                return $this->mobile_verified_at !== null;
            case 'Email Verification':
                return $this->email_verified_at !== null;
            case 'PAN Verification':
                return $this->pan_verified_at !== null;
            case 'Aadhaar Verification':
                return $this->aadhaar_verified_at !== null;
            case 'Bank Account Verification':
                return $this->bank_verified_at !== null;
            case 'Trading Segments':
                return $this->segments_completed_at !== null;
            case 'Personal Details':
                return $this->details_completed_at !== null;
            case 'Compliance Declarations':
                return $this->compliance_completed_at !== null;
            case 'Nominee Details':
                return $this->nominee_completed_at !== null;
            case 'Document Upload':
                return $this->documents_completed_at !== null;
            case 'Video KYC (IPV)':
                return $this->video_kyc_completed_at !== null;
            default:
                return false;
        }
    }

    /**
     * Get enabled KYC steps from settings
     */
    public static function getEnabledSteps()
    {
        $settings = getCompanyAllSetting();
        $steps = [];

        // Step 1 is always Mobile Verification
        $steps[1] = ['name' => 'Mobile Verification', 'enabled' => true];

        $currentIdx = 2;

        // Step 2 is Email if enabled
        if (!empty($settings['ekyc_verify_email']) && $settings['ekyc_verify_email'] == 'on') {
            $steps[$currentIdx++] = ['name' => 'Email Verification', 'enabled' => true];
        }

        // Other steps
        if (!empty($settings['ekyc_pan']) && $settings['ekyc_pan'] == 'on') {
            $steps[$currentIdx++] = ['name' => 'PAN Verification', 'enabled' => true];
        }

        if (!empty($settings['ekyc_aadhaar']) && $settings['ekyc_aadhaar'] == 'on') {
            $steps[$currentIdx++] = ['name' => 'Aadhaar Verification', 'enabled' => true];
        }

        if (!empty($settings['ekyc_bank']) && $settings['ekyc_bank'] == 'on') {
            $steps[$currentIdx++] = ['name' => 'Bank Account Verification', 'enabled' => true];
        }

        // CUSTOM STEPS FROM MANUAL
        if (!empty($settings['ekyc_segments']) && $settings['ekyc_segments'] == 'on') {
            $steps[$currentIdx++] = ['name' => 'Trading Segments', 'enabled' => true];
        }

        if (!empty($settings['ekyc_personal_details']) && $settings['ekyc_personal_details'] == 'on') {
            $steps[$currentIdx++] = ['name' => 'Personal Details', 'enabled' => true];
            $steps[$currentIdx++] = ['name' => 'Compliance Declarations', 'enabled' => true];
        }

        if (!empty($settings['ekyc_nominee']) && $settings['ekyc_nominee'] == 'on') {
            $steps[$currentIdx++] = ['name' => 'Nominee Details', 'enabled' => true];
        }

        if (!empty($settings['ekyc_documents']) && $settings['ekyc_documents'] == 'on') {
            $steps[$currentIdx++] = ['name' => 'Document Upload', 'enabled' => true];
        }

        /* 
        if (!empty($settings['ekyc_video']) && $settings['ekyc_video'] == 'on') {
            $steps[$currentIdx++] = ['name' => 'Video KYC (IPV)', 'enabled' => true];
        }
        */

        return $steps;
    }

    /**
     * Get next enabled step
     */
    public function getNextStep()
    {
        $enabledSteps = self::getEnabledSteps();

        foreach ($enabledSteps as $stepNumber => $stepInfo) {
            if (!$this->isStepCompleted($stepNumber)) {
                return $stepNumber;
            }
        }

        return null; // All steps completed
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for completed submissions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending submissions
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }
}
