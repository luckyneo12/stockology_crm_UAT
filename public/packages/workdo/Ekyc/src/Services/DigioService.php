<?php

namespace Workdo\Ekyc\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DigioService
{
    protected $clientId;
    protected $clientSecret;
    protected $baseUrl;
    protected $environment;

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function __construct()
    {
        $settings = getCompanyAllSetting();
        $this->clientId = !empty($settings['digio_client_id']) ? $settings['digio_client_id'] : '';
        $this->clientSecret = !empty($settings['digio_client_secret']) ? $settings['digio_client_secret'] : '';
        $this->environment = !empty($settings['digio_environment']) ? $settings['digio_environment'] : 'sandbox';
        
        Log::info('Digio Environment: ' . $this->environment);

        $this->baseUrl = $this->environment === 'production' 
            ? 'https://api.digio.in' 
            : 'https://ext.digio.in';
    }

    /**
     * Get Basic Auth Header
     */
    protected function getAuth()
    {
        return base64_encode($this->clientId . ':' . $this->clientSecret);
    }

    /**
     * Verify PAN Card Details
     */
    public function verifyPan($panNumber, $fullName, $dob)
    {
        try {
            // Digio expects DD/MM/YYYY for most PAN APIs
            $formattedDob = date('d/m/Y', strtotime($dob));
            $url = $this->baseUrl . '/v3/client/kyc/fetch_id_data/PAN';
            
            Log::info('Digio PAN Request URL: ' . $url);
            Log::info('Digio PAN Auth (Base64): ' . substr($this->getAuth(), 0, 10) . '...');

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $this->getAuth(),
                'Content-Type' => 'application/json',
            ])->post($url, [
                'id_no' => strtoupper($panNumber),
                'name' => $fullName,
                'dob' => $formattedDob,
                'unique_request_id' => 'PAN_' . time() . '_' . rand(1000, 9999), 
            ]);

            Log::info('Digio PAN Status: ' . $response->status());
            Log::info('Digio PAN Response Body: ' . $response->body());

            if ($response->successful()) {
                $data = $response->json();
                
                // Explicitly check for matches if provided by API
                if (isset($data['name_as_per_pan_match']) && $data['name_as_per_pan_match'] === false) {
                    return [
                        'success' => false,
                        'message' => 'Name mismatch: The name provided does not match the name on the PAN card.',
                        'data' => $data
                    ];
                }

                if (isset($data['date_of_birth_match']) && $data['date_of_birth_match'] === false) {
                    return [
                        'success' => false,
                        'message' => 'DOB mismatch: The date of birth provided does not match our records.',
                        'data' => $data
                    ];
                }

                if (isset($data['status']) && strtolower($data['status']) !== 'valid') {
                    return [
                        'success' => false,
                        'message' => 'This PAN card is currently marked as ' . ($data['status'] ?? 'invalid') . '.',
                        'data' => $data
                    ];
                }

                return [
                    'success' => true,
                    'data' => $data,
                ];
            }

            $errorMsg = $response->json('message') ?? 'PAN Verification failed at Digio';
            
            // Helpful message for the user based on status
            if ($response->status() == 404) {
                $errorMsg = "Digio endpoint not found. Please verify the API version.";
            } elseif ($response->status() == 401) {
                $errorMsg = "Digio Authentication failed. Please check your Client ID/Secret.";
            }

            return [
                'success' => false,
                'message' => $errorMsg . ' (Status: ' . $response->status() . ')',
            ];

        } catch (\Exception $e) {
            Log::error('Digio PAN Verification Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Internal error connecting to Digio',
            ];
        }
    }

    /**
     * Check Request Status
     */
    public function getRequestStatus($requestId)
    {
        try {
            $url = $this->baseUrl . '/v3/client/kyc/request/' . $requestId;
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $this->getAuth(),
            ])->get($url);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to fetch status',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch completed KYC request data
     */
    public function getKycRequestData($requestId)
    {
        try {
            // Digio KYC v2 uses POST /client/kyc/v2/{id}/response 
            $url = $this->baseUrl . '/client/kyc/v2/' . $requestId . '/response';
            
            Log::info('Digio Fetch KYC URL: ' . $url);

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $this->getAuth(),
                'Content-Type' => 'application/json',
            ])->post($url);

            Log::info('Digio Fetch KYC Status: ' . $response->status());

            // Fallback for v1 or different flows
            if ($response->status() == 404) {
                Log::info('Digio Fetch KYC 404 on /response, trying GET fallback...');
                $urlFallback = $this->baseUrl . '/client/kyc/v2/request/' . $requestId;
                $response = Http::withHeaders([
                    'Authorization' => 'Basic ' . $this->getAuth(),
                ])->get($urlFallback);
                Log::info('Digio Fetch KYC Fallback Status: ' . $response->status());
            }

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Digio Fetch KYC Failed. Status: ' . $response->status() . ' Body: ' . $response->body());

            return [
                'success' => false,
                'message' => 'Failed to fetch KYC data from Digio',
            ];

        } catch (\Exception $e) {
            Log::error('Digio Fetch KYC Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Initialize Aadhaar Verification (Kyc Request)
     */
    public function initializeAadhaar($identifier, $type = 'mobile', $aadhaarNumber = null)
    {
        try {
            $url = $this->baseUrl . '/client/kyc/v2/request';
            Log::info('Digio Aadhaar Init URL: ' . $url);

            $payload = [
                'customer_identifier' => (string) $identifier,
                'is_partner_verified' => true, // Attempt to skip Digio's own OTP verification
                'actions' => [
                    [
                        'type' => 'digilocker', 
                        'title' => 'Aadhaar Verification',
                        'description' => 'Verify via Aadhaar OTP',
                        'document_types' => ['AADHAAR'],
                        'access_mode' => 'direct',
                        'preferred_auth_mode' => 'meripehchaan', // Force MeriPehchaan/JanParichay SSO
                    ]
                ],
                'notify_customer' => false,
                'generate_access_token' => true,
            ];

            // Pre-fill Aadhaar Number if provided to speed up process (Single OTP experience)
            if ($aadhaarNumber) {
                $payload['input_values'] = [
                    'aadhaar_number' => $aadhaarNumber
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $this->getAuth(),
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            Log::info('Digio Aadhaar Init Status: ' . $response->status());
            Log::info('Digio Aadhaar Init Response Body: ' . $response->body());

            if ($response->successful()) {
                return [
                    'success' => true,
                    'request_id' => $response->json('id'),
                    'access_token' => $response->json('access_token'),
                ];
            }

            $errorData = $response->json();
            $errorMsg = $errorData['message'] ?? 'Aadhaar initialization failed';
            if (isset($errorData['details'])) {
                $errorMsg .= ' - Details: ' . $errorData['details'];
            }

            return [
                'success' => false,
                'message' => $errorMsg . ' (Status: ' . $response->status() . ')',
            ];

        } catch (\Exception $e) {
            Log::error('Digio Aadhaar Init Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Internal error during Aadhaar initialization: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify Bank Account (Penny Drop)
     */
    public function verifyBank($customerIdentifier, $accountNumber, $ifsc, $referenceName)
    {
        try {
            // Using Direct Bank Verification API v4
            $url = $this->baseUrl . '/v4/client/verify/bank_account';
            
            Log::info('Digio Bank Verify v4 URL: ' . $url);

            $payload = [
                'beneficiary_account_no' => (string) $accountNumber,
                'beneficiary_ifsc' => (string) $ifsc,
                'beneficiary_name' => $referenceName, // Pass reference name for fuzzy matching
                'unique_request_id' => 'BANK_' . time() . '_' . rand(1000, 9999),
                'validation_mode' => 'PENNY_DROP'
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $this->getAuth(),
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            Log::info('Digio Bank Verify v4 Status: ' . $response->status());
            Log::info('Digio Bank Verify v4 Response: ' . $response->body());

            $data = $response->json();

            if ($response->successful()) {
                // v4 direct API structure usually returns name in beneficiary_name_with_bank
                $beneficiaryName = $data['beneficiary_name_with_bank'] ?? ($data['beneficiary_name'] ?? '');
                
                // For direct verify, 'verified' or 'success' might be the status
                // If it's the Penny Drop API, it usually completes synchronously
                $status = $data['status'] ?? '';
                if (strtolower($status) === 'failed' || (isset($data['verified']) && $data['verified'] === false)) {
                    $errorDetails = $data['message'] ?? 'could not verify';
                     return [
                        'success' => false,
                        'message' => 'Bank verification failed: ' . $errorDetails,
                        'data' => $data
                    ];
                }

                // Fuzzy Match with reference Name (v4 might already provide name_match_score)
                $percent = $data['name_match_score'] ?? 0;
                if (!isset($data['name_match_score'])) {
                    similar_text(strtoupper($beneficiaryName), strtoupper($referenceName), $percent);
                }
                
                $isMatch = $percent > 60; // 60% threshold for now

                return [
                    'success' => true,
                    'verified_name' => $beneficiaryName,
                    'is_name_match' => $isMatch,
                    'match_score' => $percent,
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'message' => 'Digio API Error: ' . ($data['message'] ?? 'Unknown error'),
                'data' => $data
            ];

        } catch (\Exception $e) {
             Log::error('Digio Bank Verify v4 Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Internal error during Bank verification: ' . $e->getMessage(),
            ];
        }
    }
}
