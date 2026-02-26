<?php

namespace Workdo\Ekyc\Services;

use Workdo\Ekyc\Entities\EkycOtpLog;
use Workdo\Ekyc\Entities\EkycSubmission;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class OtpService
{
    /**
     * Generate and send OTP
     */
    public function generateAndSend($identifier, $type, $submissionId = null)
    {
        try {
            $settings = getCompanyAllSetting();
            
            // Check if testing mode is enabled
            $isSmsTesting = !empty($settings['otp_testing_mode']) && $settings['otp_testing_mode'] == 'on';
            $isEmailTesting = !empty($settings['otp_email_testing_mode']) && $settings['otp_email_testing_mode'] == 'on';
            $isTestingMode = ($type === 'email') ? $isEmailTesting : $isSmsTesting;
            
            // Generate OTP
            $otpLength = $settings['otp_length'] ?? 6;
            $otp = $isTestingMode && !empty($settings['otp_default_code']) 
                ? $settings['otp_default_code'] 
                : $this->generateOtp($otpLength);
            
            // Get expiry time
            $expirySeconds = $settings['otp_expiry_seconds'] ?? 300;
            $expiresAt = now()->addSeconds($expirySeconds);
            
            // Get max attempts
            $maxAttempts = $settings['otp_max_attempts'] ?? 3;
            
            // Create OTP log
            $otpLog = EkycOtpLog::create([
                'submission_id' => $submissionId,
                'identifier' => $identifier,
                'identifier_type' => $type,
                'otp_code' => $otp,
                'expires_at' => $expiresAt,
                'max_attempts' => $maxAttempts,
                'is_testing_mode' => $isTestingMode,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            // Send OTP if not in testing mode
            if (!$isTestingMode) {
                if ($type === 'email') {
                    $this->sendEmailOtp($identifier, $otp, $expirySeconds);
                } elseif ($type === 'mobile') {
                    $this->sendSmsOtp($identifier, $otp, $expirySeconds);
                }
            } else {
                Log::info("Testing mode enabled. OTP not sent. Default OTP: {$otp}");
            }
            
            return [
                'success' => true,
                'otp_id' => $otpLog->id,
                'expires_in' => $expirySeconds,
                'is_testing_mode' => $isTestingMode,
                'message' => $isTestingMode 
                    ? 'Testing mode: Use default OTP' 
                    : 'OTP sent successfully'
            ];
            
        } catch (Exception $e) {
            Log::error('OTP Generation Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to generate OTP: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verify OTP
     */
    public function verify($identifier, $type, $otp)
    {
        try {
            // Get the latest active OTP for this identifier
            $otpLog = EkycOtpLog::getLatestActive($identifier, $type);
            
            if (!$otpLog) {
                return [
                    'success' => false,
                    'message' => 'No active OTP found or OTP has expired'
                ];
            }
            
            // Check if max attempts reached
            if ($otpLog->hasReachedMaxAttempts()) {
                return [
                    'success' => false,
                    'message' => 'Maximum verification attempts reached. Please request a new OTP'
                ];
            }
            
            // Increment attempts
            $otpLog->incrementAttempts();
            
            // Verify OTP
            if ($otpLog->otp_code === $otp) {
                $otpLog->markAsVerified();
                
                return [
                    'success' => true,
                    'message' => 'OTP verified successfully',
                    'submission_id' => $otpLog->submission_id
                ];
            } else {
                $remainingAttempts = $otpLog->max_attempts - $otpLog->attempts;
                return [
                    'success' => false,
                    'message' => "Invalid OTP. {$remainingAttempts} attempts remaining",
                    'remaining_attempts' => $remainingAttempts
                ];
            }
            
        } catch (Exception $e) {
            Log::error('OTP Verification Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Resend OTP
     */
    public function resend($identifier, $type, $submissionId = null)
    {
        $settings = getCompanyAllSetting();
        $cooldown = $settings['otp_resend_cooldown'] ?? 60;
        
        // Check if cooldown period has passed
        $lastOtp = EkycOtpLog::forIdentifier($identifier, $type)
            ->latest()
            ->first();
        
        if ($lastOtp && $lastOtp->created_at->addSeconds($cooldown)->isFuture()) {
            $waitTime = $lastOtp->created_at->addSeconds($cooldown)->diffInSeconds(now());
            return [
                'success' => false,
                'message' => "Please wait {$waitTime} seconds before requesting a new OTP",
                'wait_time' => $waitTime
            ];
        }
        
        // Generate and send new OTP
        return $this->generateAndSend($identifier, $type, $submissionId);
    }
    
    /**
     * Generate random OTP
     */
    private function generateOtp($length = 6)
    {
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= random_int(0, 9);
        }
        return $otp;
    }
    
    /**
     * Send OTP via Email
     */
    private function sendEmailOtp($email, $otp, $expirySeconds)
    {
        $settings = getCompanyAllSetting();
        $provider = $settings['otp_email_provider'] ?? 'smtp';
        
        try {
            $expiryMinutes = ceil($expirySeconds / 60);
            
            Mail::send('ekyc::emails.otp', [
                'otp' => $otp,
                'expiry_minutes' => $expiryMinutes
            ], function ($message) use ($email, $settings) {
                $fromEmail = $settings['otp_email_from_email'] ?? config('mail.from.address');
                $fromName = $settings['otp_email_from_name'] ?? config('mail.from.name');
                
                $message->from($fromEmail, $fromName);
                $message->to($email);
                $message->subject('Your KYC Verification OTP');
            });
            
            Log::info("Email OTP sent to {$email} via {$provider}");
            return true;
            
        } catch (Exception $e) {
            Log::error("Email OTP sending failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Send OTP via SMS
     */
    private function sendSmsOtp($mobile, $otp, $expirySeconds)
    {
        $settings = getCompanyAllSetting();
        $provider = $settings['otp_sms_provider'] ?? 'twilio';
        
        try {
            $expiryMinutes = ceil($expirySeconds / 60);
            $message = "Your KYC verification OTP is: {$otp}. Valid for {$expiryMinutes} minutes. Do not share this code.";
            
            switch ($provider) {
                case 'twilio':
                    $this->sendViaTwilio($mobile, $message, $settings);
                    break;
                case 'msg91':
                    $this->sendViaMsg91($mobile, $otp, $settings);
                    break;
                case 'fast2sms':
                    $this->sendViaFast2Sms($mobile, $otp, $settings);
                    break;
                case 'custom':
                    $this->sendViaCustomApi($mobile, $otp, $settings);
                    break;
                default:
                    throw new Exception("Unsupported SMS provider: {$provider}");
            }
            
            Log::info("SMS OTP sent to {$mobile} via {$provider}");
            return true;
            
        } catch (Exception $e) {
            Log::error("SMS OTP sending failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Send SMS via Twilio
     */
    private function sendViaTwilio($mobile, $message, $settings)
    {
        $accountSid = $settings['otp_sms_api_key'] ?? '';
        $authToken = $settings['otp_sms_api_secret'] ?? '';
        $from = $settings['otp_sms_sender_id'] ?? '';
        
        // Implement Twilio API call
        // This is a placeholder - actual implementation would use Twilio SDK
        Log::info("Twilio SMS: {$message} to {$mobile}");
    }
    
    /**
     * Send SMS via MSG91
     */
    private function sendViaMsg91($mobile, $otp, $settings)
    {
        $apiKey = $settings['otp_sms_api_key'] ?? '';
        $senderId = $settings['otp_sms_sender_id'] ?? '';
        
        // Implement MSG91 API call
        Log::info("MSG91 SMS: OTP {$otp} to {$mobile}");
    }
    
    /**
     * Send SMS via Fast2SMS
     */
    private function sendViaFast2Sms($mobile, $otp, $settings)
    {
        $apiKey = $settings['otp_sms_api_key'] ?? '';
        
        // Implement Fast2SMS API call
        Log::info("Fast2SMS: OTP {$otp} to {$mobile}");
    }
    
    /**
     * Send SMS via Custom API
     */
    private function sendViaCustomApi($mobile, $otp, $settings)
    {
        $endpoint = $settings['otp_sms_api_endpoint'] ?? '';
        $apiKey = $settings['otp_sms_api_key'] ?? '';
        
        // Implement custom API call
        Log::info("Custom API SMS: OTP {$otp} to {$mobile}");
    }
}
