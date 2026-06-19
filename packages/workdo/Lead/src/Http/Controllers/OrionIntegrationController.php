<?php

namespace Workdo\Lead\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Workdo\Lead\Entities\OrionLeadLog;
use Workdo\Lead\Entities\Lead;
use Workdo\Lead\Entities\LeadCustomField;
use Workdo\Lead\Entities\LeadCustomFieldValue;
use Workdo\Lead\Entities\UserLead;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrionIntegrationController extends Controller
{
    private function isCompany()
    {
        $user = Auth::user();
        return $user->type == 'company' || $user->type == 'super admin';
    }

    /**
     * Display log entries.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->isAbleTo('crm manage')) {
            return redirect()->route('dashboard')->with('error', __('Permission denied.'));
        }

        $query = OrionLeadLog::where('workspace_id', getActiveWorkSpace());

        if (Auth::user()->type !== 'company' && Auth::user()->type !== 'super admin') {
            $accessibleUserIds = Auth::user()->getAccessibleUserIds();
            $query->whereIn('created_by', $accessibleUserIds);
        }

        // Apply filters
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('api_type')) {
            $query->where('api_type', $request->api_type);
        }

        $logs = $query->orderBy('id', 'DESC')->paginate(15);

        return view('lead::orion_lead_logs.index', compact('logs'));
    }

    /**
     * Show raw payload popup.
     */
    public function payload($id)
    {
        if (!Auth::user()->isAbleTo('crm manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $log = OrionLeadLog::find($id);
        if ($log && $log->workspace_id == getActiveWorkSpace()) {
            if (Auth::user()->type !== 'company' && Auth::user()->type !== 'super admin') {
                $accessibleUserIds = Auth::user()->getAccessibleUserIds();
                if (!in_array($log->created_by, $accessibleUserIds)) {
                    return response()->json(['error' => __('Permission denied.')], 403);
                }
            }
            return view('lead::orion_lead_logs.payload', compact('log'));
        }
        return response()->json(['error' => __('Log entry not found.')], 404);
    }

    /**
     * Retrieve Orion settings.
     */
    public static function getOrionSettings()
    {
        $setting = Setting::where('key', 'orion_lead_integration_settings')
            ->where('workspace', getActiveWorkSpace())
            ->first();
        return $setting ? json_decode($setting->value, true) : [
            'credentials' => [
                'base_url' => 'http://61.247.230.203:15000/api',
                'username' => '',
                'password' => '',
                'firm_id' => '1001',
                'financial_year' => '2022-2023',
            ],
            'rules' => []
        ];
    }

    /**
     * Save EKYC rules & credentials.
     */
    public function saveOrionRules(Request $request)
    {
        if (!Auth::user()->isAbleTo('crm manage')) {
            return response()->json(['success' => false, 'message' => __('Permission Denied.')], 403);
        }

        $credentials = $request->input('credentials', []);
        $rules = $request->input('rules', []);

        // Retrieve existing setting to preserve positions or other metrics if needed
        $existing = self::getOrionSettings();
        $positions = $request->input('positions', $existing['positions'] ?? []);

        $settingsData = [
            'credentials' => [
                'base_url' => $credentials['base_url'] ?? 'http://61.247.230.203:15000/api',
                'username' => $credentials['username'] ?? '',
                'password' => $credentials['password'] ?? '',
                'firm_id' => $credentials['firm_id'] ?? '1001',
                'financial_year' => $credentials['financial_year'] ?? '2022-2023',
            ],
            'rules' => $rules,
            'positions' => $positions
        ];

        Setting::updateOrCreate(
            [
                'key' => 'orion_lead_integration_settings',
                'workspace' => getActiveWorkSpace(),
            ],
            [
                'value' => json_encode($settingsData),
                'created_by' => creatorId(),
            ]
        );

        companySettingCacheForget();

        return response()->json([
            'success' => true,
            'message' => __('Orion EKYC integration settings saved successfully.')
        ]);
    }

    /**
     * Delete Orion rule.
     */
    public function deleteOrionRule(Request $request)
    {
        if (!Auth::user()->isAbleTo('crm manage')) {
            return response()->json(['success' => false, 'message' => __('Permission Denied.')], 403);
        }

        $ruleId = $request->input('rule_id');
        if (empty($ruleId)) {
            return response()->json(['success' => false, 'message' => __('Rule ID missing.')], 400);
        }

        $settings = self::getOrionSettings();
        $rules = $settings['rules'] ?? [];

        $filteredRules = array_values(array_filter($rules, function($r) use ($ruleId) {
            return ($r['id'] ?? '') !== $ruleId;
        }));

        $settings['rules'] = $filteredRules;

        Setting::updateOrCreate(
            [
                'key' => 'orion_lead_integration_settings',
                'workspace' => getActiveWorkSpace(),
            ],
            [
                'value' => json_encode($settings),
                'created_by' => creatorId(),
            ]
        );

        companySettingCacheForget();

        return response()->json([
            'success' => true,
            'message' => __('Orion workflow rule deleted successfully.'),
            'rules' => $filteredRules
        ]);
    }

    /**
     * Test credentials by requesting a bearer token.
     */
    public function testOrionConnection(Request $request)
    {
        if (!Auth::user()->isAbleTo('crm manage')) {
            return response()->json(['success' => false, 'message' => __('Permission Denied.')], 403);
        }

        $baseUrl = $request->input('base_url');
        $username = $request->input('username');
        $password = $request->input('password');

        if (empty($baseUrl)) {
            return response()->json(['success' => false, 'message' => __('Base URL is required.')]);
        }

        $isRivexa = (strpos($baseUrl, 'rivexaflow.space') !== false || strpos($baseUrl, 'api/crm') !== false);

        if ($isRivexa) {
            try {
                $response = Http::get(rtrim($baseUrl, '/') . '/test-connection-probe');
                return response()->json([
                    'success' => true,
                    'message' => __('Connection successful! Target Rivexa EKYC API server is reachable.')
                ]);
            } catch (\Exception $ex) {
                return response()->json(['success' => false, 'message' => __('Rivexa API connection failed: ') . $ex->getMessage()]);
            }
        }

        if (empty($username) || empty($password)) {
            return response()->json(['success' => false, 'message' => __('Username and Password are required for Orion connection.')]);
        }

        try {
            $tokenUrl = rtrim($baseUrl, '/') . '/token';
            $response = Http::asForm()->post($tokenUrl, [
                'UserName' => $username,
                'Password' => $password,
                'Grant_type' => 'password'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['access_token'])) {
                    return response()->json([
                        'success' => true,
                        'message' => __('Connection successful! Valid access token retrieved.')
                    ]);
                }
            }
            
            $errText = $response->body();
            return response()->json(['success' => false, 'message' => __('Authentication failed: ') . $errText]);
        } catch (\Exception $ex) {
            return response()->json(['success' => false, 'message' => __('API connection failed: ') . $ex->getMessage()]);
        }
    }

    /**
     * Obtain access token using credentials.
     */
    private function getAccessToken($credentials)
    {
        $baseUrl = $credentials['base_url'] ?? '';
        $isRivexa = (strpos($baseUrl, 'rivexaflow.space') !== false || strpos($baseUrl, 'api/crm') !== false);

        if ($isRivexa && (empty($credentials['username']) || empty($credentials['password']))) {
            return null; // Rivexa endpoint can work without token if username/password are not provided
        }

        $tokenUrl = rtrim($baseUrl, '/') . '/token';

        $response = Http::asForm()->post($tokenUrl, [
            'UserName' => $credentials['username'] ?? '',
            'Password' => $credentials['password'] ?? '',
            'Grant_type' => 'password'
        ]);

        if ($response->successful()) {
            return $response->json()['access_token'] ?? null;
        }

        throw new \Exception(__('Token retrieval failed: ') . $response->body());
    }

    /**
     * Execute stage entry EKYC workflow.
     */
    public static function triggerStageEntry($lead, $newStageId)
    {
        $settings = self::getOrionSettings();
        $rules = $settings['rules'] ?? [];
        $credentials = $settings['credentials'] ?? [];

        if (empty($credentials['username']) || empty($credentials['password'])) {
            return; // Credentials not configured
        }

        // Find rules matching the target stage
        $matchedRules = array_filter($rules, function($r) use ($newStageId) {
            return ($r['stage_id'] ?? null) == $newStageId;
        });

        if (empty($matchedRules)) {
            return;
        }

        // Create log category instance
        $instance = new self();

        foreach ($matchedRules as $rule) {
            $triggerMode = $rule['trigger_mode'] ?? 'manual_fetch';

            if ($triggerMode === 'auto_fetch') {
                // Determine search parameter (Client Code)
                // We'll search for custom field mapping for 'ClientCode' or check dp_id
                $mapping = $rule['field_mapping'] ?? [];
                $clientCode = $instance->getLeadFieldValue($lead, $mapping, 'ClientCode') ?? $lead->dp_id;

                if (!empty($clientCode)) {
                    try {
                        $instance->fetchAndApplyDetails($lead, $rule, $credentials, $clientCode);
                    } catch (\Exception $ex) {
                        Log::error("Orion Auto Fetch failed for lead ID {$lead->id}: " . $ex->getMessage());
                        throw $ex; // Re-throw to trigger reverting of the stage
                    }
                } else {
                    throw new \Exception(__('Client Code / DP ID is required for auto data fetch, but is missing on this Lead.'));
                }
            } elseif ($triggerMode === 'auto_send_ekyc') {
                try {
                    $instance->sendEkycDetails($lead, $rule, $credentials, false);
                } catch (\Exception $ex) {
                    Log::error("Orion Auto Send EKYC failed for lead ID {$lead->id}: " . $ex->getMessage());
                }
            } elseif ($triggerMode === 'auto_send_modify') {
                try {
                    $instance->sendEkycDetails($lead, $rule, $credentials, true);
                } catch (\Exception $ex) {
                    Log::error("Orion Auto Send Modify failed for lead ID {$lead->id}: " . $ex->getMessage());
                }
            }
        }
    }

    /**
     * Perform a manual fetch from EKYC details.
     */
    public function manualFetch(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('lead edit')) {
            return response()->json(['success' => false, 'message' => __('Permission Denied.')], 403);
        }

        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json(['success' => false, 'message' => __('Lead not found.')], 404);
        }

        $clientCode = $request->input('client_code');
        $ruleId = $request->input('rule_id');

        if (empty($clientCode)) {
            return response()->json(['success' => false, 'message' => __('Client Code is required.')], 400);
        }

        $settings = self::getOrionSettings();
        $credentials = $settings['credentials'] ?? [];
        $rules = $settings['rules'] ?? [];

        $rule = collect($rules)->firstWhere('id', $ruleId);
        if (!$rule) {
            return response()->json(['success' => false, 'message' => __('Integration rule configuration not found.')], 404);
        }

        try {
            $updatedFields = $this->fetchAndApplyDetails($lead, $rule, $credentials, $clientCode);
            return response()->json([
                'success' => true,
                'message' => __('Lead details successfully fetched and updated from Orion!'),
                'updated_fields' => $updatedFields
            ]);
        } catch (\Exception $ex) {
            return response()->json(['success' => false, 'message' => $ex->getMessage()], 500);
        }
    }

    /**
     * Perform EKYC GET request and apply mapped fields to Lead.
     */
    private function fetchAndApplyDetails($lead, $rule, $credentials, $clientCode)
    {
        $logEntry = OrionLeadLog::create([
            'lead_id' => $lead->id,
            'client_code' => $clientCode,
            'api_type' => 'fetch_details',
            'status' => 'pending',
            'workspace_id' => $lead->workspace_id,
            'created_by' => Auth::check() ? Auth::user()->id : $lead->created_by,
        ]);

        try {
            $baseUrl = $credentials['base_url'] ?? '';
            $isRivexa = (strpos($baseUrl, 'rivexaflow.space') !== false || strpos($baseUrl, 'api/crm') !== false);

            $token = $this->getAccessToken($credentials);
            
            $headers = [];
            if ($token) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }
            if (!empty($credentials['firm_id'])) {
                $headers['FIRMID'] = $credentials['firm_id'];
            }

            // Clean client code/mobile: strip non-alphanumeric. If it's 12 digits starting with '91', strip '91'.
            $cleanClientCode = preg_replace('/[^0-9a-zA-Z]/', '', $clientCode);
            if (preg_match('/^[0-9]{12}$/', $cleanClientCode) && strpos($cleanClientCode, '91') === 0) {
                $cleanClientCode = substr($cleanClientCode, 2);
            }

            $resData = null;
            $lastError = null;
            $successfulYear = null;
            $queryUsed = $cleanClientCode;
            $url = '';
            $yearsToTry = [];

            if ($isRivexa) {
                // For Rivexa, client code is appended to URL directly. No query params needed.
                $url = rtrim($baseUrl, '/') . '/' . $cleanClientCode;
                try {
                    $response = Http::withHeaders($headers)->get($url);
                    if ($response->successful()) {
                        $resData = $response->json();
                    } else {
                        $lastError = $response->body();
                    }
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                }

                // Fallback with original clientCode if clean version failed
                if (!$resData && $cleanClientCode !== $clientCode) {
                    $url = rtrim($baseUrl, '/') . '/' . $clientCode;
                    try {
                        $response = Http::withHeaders($headers)->get($url);
                        if ($response->successful()) {
                            $resData = $response->json();
                            $queryUsed = $clientCode;
                        } else {
                            $lastError = $response->body();
                        }
                    } catch (\Exception $e) {
                        $lastError = $e->getMessage();
                    }
                }
            } else {
                $url = rtrim($baseUrl, '/') . '/Masters/GetOrionEKYCDetail/Get';
                // Determine financial years to try as fallbacks
                if (!empty($credentials['financial_year'])) {
                    $yearsToTry[] = $credentials['financial_year'];
                }

                $currentYear = (int)date('Y');
                $currentMonth = (int)date('m');
                if ($currentMonth >= 4) {
                    $currentFY = $currentYear . '-' . ($currentYear + 1);
                    $prevFY = ($currentYear - 1) . '-' . $currentYear;
                } else {
                    $currentFY = ($currentYear - 1) . '-' . $currentYear;
                    $prevFY = ($currentYear - 2) . '-' . ($currentYear - 1);
                }

                if (!in_array($currentFY, $yearsToTry)) {
                    $yearsToTry[] = $currentFY;
                }
                if (!in_array($prevFY, $yearsToTry)) {
                    $yearsToTry[] = $prevFY;
                }
                if (!in_array('2026-2027', $yearsToTry)) {
                    $yearsToTry[] = '2026-2027';
                }
                if (!in_array('2025-2026', $yearsToTry)) {
                    $yearsToTry[] = '2025-2026';
                }

                // Try with cleaned code
                foreach ($yearsToTry as $fy) {
                    try {
                        $response = Http::withHeaders(array_merge($headers, [
                            'FINANCIALYEAR' => $fy,
                        ]))->get($url, [
                            'Code' => $cleanClientCode,
                            'ClientType' => 'A'
                        ]);

                        if ($response->successful()) {
                            $data = $response->json();
                            $hasData = false;
                            if (is_array($data)) {
                                foreach ($data as $k => $v) {
                                    if (is_array($v) && count($v) > 0) {
                                        $hasData = true;
                                        break;
                                    }
                                }
                            }

                            if ($hasData) {
                                $resData = $data;
                                $successfulYear = $fy;
                                $queryUsed = $cleanClientCode;
                                break;
                            }
                        } else {
                            $lastError = $response->body();
                        }
                    } catch (\Exception $e) {
                        $lastError = $e->getMessage();
                    }
                }

                // Fallback: Try with original clientCode if it differs from cleanClientCode and no data found yet
                if (!$resData && $cleanClientCode !== $clientCode) {
                    foreach ($yearsToTry as $fy) {
                        try {
                            $response = Http::withHeaders(array_merge($headers, [
                                'FINANCIALYEAR' => $fy,
                            ]))->get($url, [
                                'Code' => $clientCode,
                                'ClientType' => 'A'
                            ]);

                            if ($response->successful()) {
                                $data = $response->json();
                                $hasData = false;
                                if (is_array($data)) {
                                    foreach ($data as $k => $v) {
                                        if (is_array($v) && count($v) > 0) {
                                            $hasData = true;
                                            break;
                                        }
                                    }
                                }

                                if ($hasData) {
                                    $resData = $data;
                                    $successfulYear = $fy;
                                    $queryUsed = $clientCode;
                                    break;
                                }
                            } else {
                                $lastError = $response->body();
                            }
                        } catch (\Exception $e) {
                            $lastError = $e->getMessage();
                        }
                    }
                }
            }

            $logEntry->request_payload = [
                'url' => $url,
                'query' => $isRivexa ? [] : [
                    'Code' => $queryUsed,
                    'ClientType' => 'A'
                ],
                'tried_financial_years' => $isRivexa ? [] : $yearsToTry,
                'successful_financial_year' => $successfulYear
            ];

            if (!$resData) {
                $errMsg = $isRivexa 
                    ? __('No client details found in Rivexa for Application ID: :code.', ['code' => $clientCode])
                    : __('No client details found in Orion backoffice for Code/Mobile/PAN: :code (tried financial years: :years).', [
                        'code' => $clientCode,
                        'years' => implode(', ', $yearsToTry)
                    ]);
                if ($lastError) {
                    $errMsg .= ' (' . $lastError . ')';
                }
                throw new \Exception($errMsg);
            }

            $logEntry->response_payload = $resData;
            $logEntry->status = 'success';
            $logEntry->save();

            // Perform Field Mapping
            $mapping = $rule['field_mapping'] ?? [];
            $updatedFields = [];

            // Apply standard mapping fields
            foreach ($mapping as $orionKey => $crmKey) {
                if (empty($crmKey)) continue;

                $val = $this->extractOrionField($resData, $orionKey);
                if ($val !== null) {
                    // Strip/Format E.g. Phone number sanitization
                    if ($crmKey === 'phone') {
                        $val = preg_replace('/[^0-9]/', '', $val);
                    }

                    if (in_array($crmKey, ['name', 'email', 'phone', 'subject', 'pan_number', 'aadhar_number', 'dp_id'])) {
                        $lead->{$crmKey} = $val;
                        $updatedFields[$crmKey] = $val;
                    } elseif (strpos($crmKey, 'custom_') === 0) {
                        $cfId = substr($crmKey, 7);
                        LeadCustomFieldValue::updateOrCreate([
                            'lead_id' => $lead->id,
                            'field_id' => $cfId,
                        ], [
                            'value' => $val,
                        ]);
                        $updatedFields[$crmKey] = $val;
                    }
                }
            }

            $lead->save();

            // Save Activity Log
            $lead->activities()->create([
                'user_id' => Auth::check() ? Auth::user()->id : $lead->created_by,
                'log_type' => 'Orion Data Synced',
                'remark' => json_encode(['message' => __('Lead EKYC details updated via Orion API.')]),
            ]);

            return $updatedFields;

        } catch (\Exception $ex) {
            $logEntry->status = 'failed';
            $logEntry->error_reason = $ex->getMessage();
            $logEntry->save();

            throw new \Exception(__('Orion Fetch failed: ') . $ex->getMessage());
        }
    }

    /**
     * Send details to EKYC / Modify service POST endpoint.
     */
    private function sendEkycDetails($lead, $rule, $credentials, $isModify = false)
    {
        $apiType = $isModify ? 'post_modify' : 'post_ekyc';
        $logEntry = OrionLeadLog::create([
            'lead_id' => $lead->id,
            'client_code' => $lead->dp_id ?? 'LEAD' . $lead->id,
            'api_type' => $apiType,
            'status' => 'pending',
            'workspace_id' => $lead->workspace_id,
            'created_by' => Auth::check() ? Auth::user()->id : $lead->created_by,
        ]);

        try {
            $token = $this->getAccessToken($credentials);
            $compiledPayload = $this->compileOrionPayload($lead, $rule);

            // Wrap in Status structure if Modify POST
            $postPayload = $compiledPayload;
            if ($isModify) {
                $postPayload = [
                    'ClientCode' => $compiledPayload['KYCDetail']['ClientCode'] ?? 'LEAD' . $lead->id,
                    'KYCDetail' => [
                        'Status' => 'Y',
                        'KYCDetail' => $compiledPayload['KYCDetail']
                    ],
                    'AddressDetail' => [
                        'Status' => 'Y',
                        'AddressDetail' => $compiledPayload['AddressDetail']
                    ],
                    'ContactDetail' => [
                        'Status' => 'Y',
                        'ContactDetail' => $compiledPayload['ContactDetail']
                    ],
                    'BankDetail' => [
                        'Status' => 'Y',
                        'BankDetail' => $compiledPayload['BankDetail']
                    ],
                    'DepositoryDetail' => $compiledPayload['DepositoryDetail'] ?? null,
                    'NomineeDetail' => [
                        'Status' => 'Y',
                        'NomOptFlag' => $compiledPayload['NomineeDetail']['NomineeOptFlag'] ?? 'Y',
                        'NomineeDetail' => $compiledPayload['NomineeDetail']['NomineeDetail'] ?? []
                    ],
                    'ExchangeDetail' => [
                        'Status' => 'Y',
                        'ExchangeDetail' => $compiledPayload['ExchangeDetail']
                    ],
                    'SegmentDetail' => [
                        'Status' => 'Y',
                        'SegmentDetail' => $compiledPayload['SegmentDetail']
                    ]
                ];
            }

            $endpoint = $isModify ? '/Masters/OrionEKYCModifyService/Post' : '/Masters/OrionEKYCService/Post';
            $url = rtrim($credentials['base_url'], '/') . $endpoint;

            $logEntry->request_payload = $postPayload;

            $response = Http::withHeaders([
                'FIRMID' => $credentials['firm_id'] ?? '1001',
                'FINANCIALYEAR' => $credentials['financial_year'] ?? '2022-2023',
                'Authorization' => 'Bearer ' . $token
            ])->post($url, $postPayload);

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            $resData = $response->json();
            $logEntry->response_payload = $resData;

            if (isset($resData['StatusCode']) && $resData['StatusCode'] !== 'OK') {
                $logEntry->status = 'failed';
                $logEntry->error_reason = implode(', ', $resData['ResponseLog'] ?? [$resData['StatusCode']]);
            } else {
                $logEntry->status = 'success';
            }
            $logEntry->save();

            // Save Activity Log
            $lead->activities()->create([
                'user_id' => Auth::check() ? Auth::user()->id : $lead->created_by,
                'log_type' => $isModify ? 'Orion Modification Posted' : 'Orion EKYC Posted',
                'remark' => json_encode(['message' => $isModify ? __('Lead details modification posted to Orion API.') : __('New Lead EKYC details posted to Orion API.')]),
            ]);

            return $resData;

        } catch (\Exception $ex) {
            $logEntry->status = 'failed';
            $logEntry->error_reason = $ex->getMessage();
            $logEntry->save();

            throw new \Exception(__('Orion Post failed: ') . $ex->getMessage());
        }
    }

    /**
     * Map Orion JSON response fields safely.
     */
    private function extractOrionField($payload, $fieldName)
    {
        if (!$payload || !is_array($payload)) return null;

        // Check if payload is in the new format (has mandatoryData or rawData)
        $isNewFormat = isset($payload['mandatoryData']) || isset($payload['rawData']);

        if ($isNewFormat) {
            $mandatory = $payload['mandatoryData'] ?? [];
            $userInfo = $mandatory['userInfo'] ?? [];
            $bankFinancial = $mandatory['bankFinancial'] ?? [];
            
            $rawData = $payload['rawData'] ?? [];
            $personal = $rawData['personalDetails'] ?? [];
            $address = $rawData['address'] ?? [];
            $bankDetails = $rawData['bankDetails'] ?? [];
            
            // Parse JSON strings
            $identityDetails = [];
            if (!empty($rawData['identityDetails'])) {
                $identityDetails = json_decode($rawData['identityDetails'], true) ?: [];
            }
            $nomineeDetails = [];
            if (!empty($rawData['nomineeDetails'])) {
                $nomineeDetails = json_decode($rawData['nomineeDetails'], true) ?: [];
            }

            switch ($fieldName) {
                case 'ClientCode':
                    return $userInfo['clientCode'] ?? $rawData['applicationId'] ?? null;
                case 'PanNo':
                    $pan = $identityDetails['pan'] ?? $userInfo['panNumber'] ?? null;
                    if (empty($pan) || $pan === 'Uploaded') {
                        $pan = $rawData['ocrData']['pan_verification']['data']['pan'] ?? null;
                    }
                    return $pan;
                case 'ClientName':
                    return $personal['fullName'] ?? $userInfo['fullName'] ?? $identityDetails['pan_name'] ?? null;
                case 'Dob':
                    $dob = $userInfo['dob'] ?? $personal['dob'] ?? null;
                    if ($dob && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dob)) {
                        $parts = explode('/', $dob);
                        $dob = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                    }
                    return $dob;
                case 'Aadhar':
                    return $identityDetails['aadhaar'] ?? $userInfo['aadhaarNumber'] ?? null;
                case 'ContactNo':
                    return $userInfo['mobileNumber'] ?? $personal['mobileNumber'] ?? null;
                case 'ContactEmail':
                    return $userInfo['email'] ?? $personal['email'] ?? null;
                case 'AddressLine1':
                    return $address['line1'] ?? $userInfo['userAddress'] ?? null;
                case 'AddressLine2':
                    return $address['line2'] ?? null;
                case 'AddressLine3':
                    return $address['line3'] ?? null;
                case 'AddressCity':
                    return $address['city'] ?? null;
                case 'AddressPincode':
                    return $address['pincode'] ?? null;
                case 'AddressState':
                    return $address['state'] ?? null;
                case 'BankAccountNumber':
                    return $bankFinancial['bankAccountNumber'] ?? $bankDetails['accountNumber'] ?? null;
                case 'BankIFSC':
                    return $bankFinancial['bankIfsc'] ?? $bankDetails['ifsc'] ?? null;
                case 'BankName':
                    return $bankFinancial['bankName'] ?? $bankDetails['bankName'] ?? null;
                case 'NomineeName':
                    $nominees = $nomineeDetails['nominees'] ?? [];
                    if (is_array($nominees) && isset($nominees[0])) {
                        return $nominees[0]['name'] ?? null;
                    }
                    return null;
                case 'NomineeRelation':
                    $nominees = $nomineeDetails['nominees'] ?? [];
                    if (is_array($nominees) && isset($nominees[0])) {
                        return $nominees[0]['relation'] ?? null;
                    }
                    return null;
                case 'NomineeDOB':
                    $nominees = $nomineeDetails['nominees'] ?? [];
                    $ndob = null;
                    if (is_array($nominees) && isset($nominees[0])) {
                        $ndob = $nominees[0]['dob'] ?? null;
                    }
                    if ($ndob && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $ndob)) {
                        $parts = explode('/', $ndob);
                        $ndob = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                    }
                    return $ndob;
                case 'NomineePAN':
                    $nominees = $nomineeDetails['nominees'] ?? [];
                    if (is_array($nominees) && isset($nominees[0])) {
                        return $nominees[0]['pan'] ?? $nominees[0]['proofNumber'] ?? null;
                    }
                    return null;
                case 'NomineeShare':
                    $nominees = $nomineeDetails['nominees'] ?? [];
                    if (is_array($nominees) && isset($nominees[0])) {
                        return $nominees[0]['sharePercentage'] ?? null;
                    }
                    return null;
                case 'DepositoryClientID':
                    return $personal['clientCode'] ?? $userInfo['clientCode'] ?? null;
                case 'TradingSoftwareType':
                    return $personal['tradingSoftwareType'] ?? null;
                case 'AnnualIncome':
                    return $personal['annualIncome'] ?? $bankFinancial['annualIncome'] ?? null;
                case 'AnnualIncomeDate':
                    return $personal['annualIncomeDate'] ?? null;
                case 'NetWorth':
                    return $personal['networth'] ?? $bankFinancial['networth'] ?? null;
                case 'NetWorthDate':
                    return $personal['networthDate'] ?? $bankFinancial['networthDate'] ?? null;
                case 'PayoutFlag':
                    return $personal['payoutFlag'] ?? null;
                case 'FundMandate':
                    return $personal['fundMandate'] ?? null;
                case 'FatherName':
                    return $personal['fatherName'] ?? null;
                case 'MotherName':
                    return $personal['motherName'] ?? null;
                case 'Gender':
                    return $personal['gender'] ?? $userInfo['gender'] ?? null;
                case 'MaritalStatus':
                    return $personal['maritalStatus'] ?? $userInfo['maritalStatus'] ?? null;
                case 'Occupation':
                    return $personal['occupation'] ?? $bankFinancial['occupation'] ?? null;
                case 'PEP':
                    return $personal['politicallyExposed'] ?? null;
                case 'DepositoryType':
                    return $personal['depositoryType'] ?? null;
                case 'BankAccountType':
                    return $bankFinancial['bankAccountType'] ?? $bankDetails['accountType'] ?? null;
                case 'AddressCountry':
                    return $address['country'] ?? $userInfo['addressCountry'] ?? null;
                case 'OpenDate':
                    $od = $userInfo['openDate'] ?? $personal['openDate'] ?? $rawData['openDate'] ?? null;
                    if ($od && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $od)) {
                        $parts = explode('/', $od);
                        $od = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                    }
                    return $od;
                case 'RiskCategory':
                    return $rawData['riskCategory'] ?? $userInfo['riskCategory'] ?? null;
                case 'ECSLimit':
                    return $bankFinancial['ecsLimit'] ?? $bankDetails['ecsLimit'] ?? null;
                case 'UMRN':
                    return $bankFinancial['umrn'] ?? $bankDetails['umrn'] ?? null;
                default:
                    return null;
            }
        }

        // Safely extract sub-arrays
        $kyc = isset($payload['KYCDetail'][0]) ? $payload['KYCDetail'][0] : ($payload['KYCDetail'] ?? []);
        if (!is_array($kyc)) {
            $kyc = $payload['KYCDetail'] ?? [];
        }
        
        $bank = isset($payload['BankDetail'][0]) ? $payload['BankDetail'][0] : ($payload['BankDetail'] ?? []);
        if (!is_array($bank)) {
            $bank = $payload['BankDetail'] ?? [];
        }
        
        $dep = isset($payload['DepositoryDetail'][0]) ? $payload['DepositoryDetail'][0] : ($payload['DepositoryDetail'] ?? []);
        if (!is_array($dep)) {
            $dep = $payload['DepositoryDetail'] ?? [];
        }
        
        $bo = isset($payload['ClientBackOfficeDetail'][0]) ? $payload['ClientBackOfficeDetail'][0] : ($payload['BackOfficeDetail'][0] ?? ($payload['ClientBackOfficeDetail'] ?? ($payload['BackOfficeDetail'] ?? [])));
        if (!is_array($bo)) {
            $bo = [];
        }

        switch ($fieldName) {
            case 'ClientCode':
                return $kyc['ClientCode'] ?? $payload['ClientCode'] ?? null;
            case 'PanNo':
                return $kyc['PanNo'] ?? null;
            case 'ClientName':
                return $kyc['ClientName'] ?? null;
            case 'Dob':
                $dob = $kyc['DOB'] ?? $kyc['Dob'] ?? null;
                if ($dob && strpos($dob, 'T') !== false) {
                    $dob = explode('T', $dob)[0];
                }
                return $dob;
            case 'Aadhar':
                return $kyc['Aadhar'] ?? null;
            case 'ContactNo':
                $contacts = $payload['ContactDetail'] ?? [];
                foreach ($contacts as $c) {
                    if (($c['ContactType'] ?? '') === 'M') {
                        return $c['ContactNo'] ?? null;
                    }
                }
                return $contacts[0]['ContactNo'] ?? null;
            case 'ContactEmail':
                $contacts = $payload['ContactDetail'] ?? [];
                foreach ($contacts as $c) {
                    if (($c['ContactType'] ?? '') === 'E') {
                        return $c['ContactEmail'] ?? null;
                    }
                }
                return $contacts[0]['ContactEmail'] ?? null;
            case 'AddressLine1':
                $addresses = $payload['AddressDetail'] ?? [];
                foreach ($addresses as $a) {
                    if (($a['AddressType'] ?? '') === 'C') {
                        return $a['AddressLine1'] ?? null;
                    }
                }
                return $addresses[0]['AddressLine1'] ?? null;
            case 'AddressLine2':
                $addresses = $payload['AddressDetail'] ?? [];
                foreach ($addresses as $a) {
                    if (($a['AddressType'] ?? '') === 'C') {
                        return $a['AddressLine2'] ?? null;
                    }
                }
                return $addresses[0]['AddressLine2'] ?? null;
            case 'AddressLine3':
                $addresses = $payload['AddressDetail'] ?? [];
                foreach ($addresses as $a) {
                    if (($a['AddressType'] ?? '') === 'C') {
                        return $a['AddressLine3'] ?? null;
                    }
                }
                return $addresses[0]['AddressLine3'] ?? null;
            case 'AddressCity':
                $addresses = $payload['AddressDetail'] ?? [];
                foreach ($addresses as $a) {
                    if (($a['AddressType'] ?? '') === 'C') {
                        return $a['AddressCity'] ?? null;
                    }
                }
                return $addresses[0]['AddressCity'] ?? null;
            case 'AddressPincode':
                $addresses = $payload['AddressDetail'] ?? [];
                foreach ($addresses as $a) {
                    if (($a['AddressType'] ?? '') === 'C') {
                        return $a['AddressPincode'] ?? null;
                    }
                }
                return $addresses[0]['AddressPincode'] ?? null;
            case 'AddressState':
                $addresses = $payload['AddressDetail'] ?? [];
                foreach ($addresses as $a) {
                    if (($a['AddressType'] ?? '') === 'C') {
                        return $a['AddressState'] ?? null;
                    }
                }
                return $addresses[0]['AddressState'] ?? null;
            case 'BankAccountNumber':
                return $bank['BankAccountNumber'] ?? null;
            case 'BankIFSC':
                return $bank['BankIFSC'] ?? null;
            case 'BankName':
                return $bank['BankName'] ?? null;
            case 'NomineeName':
                $nominees = $payload['NomineeDetail']['NomineeDetail'] ?? $payload['NomineeDetail'] ?? [];
                if (is_array($nominees) && isset($nominees[0])) {
                    return $nominees[0]['Name'] ?? null;
                }
                return $nominees['Name'] ?? null;
            case 'DepositoryClientID':
                return $dep['DepositoryClientID'] ?? null;
            case 'TradingSoftwareType':
                return $bo['TradingSoftwareType'] ?? null;
            case 'AnnualIncome':
                return $kyc['AnnualIncome'] ?? null;
            case 'AnnualIncomeDate':
                $date = $kyc['AnnualIncomeDate'] ?? null;
                if ($date && strpos($date, 'T') !== false) {
                    $date = explode('T', $date)[0];
                }
                return $date;
            case 'NetWorth':
                return $kyc['NetWorth'] ?? null;
            case 'NetWorthDate':
                $date = $kyc['NetWorthDate'] ?? null;
                if ($date && strpos($date, 'T') !== false) {
                    $date = explode('T', $date)[0];
                }
                return $date;
            case 'PayoutFlag':
                return $bo['PayoutFlag'] ?? null;
            case 'FundMandate':
                return $bank['FundMandate'] ?? null;
            case 'FatherName':
                return $kyc['FatherName'] ?? null;
            case 'MotherName':
                return $kyc['MotherName'] ?? null;
            case 'Gender':
                return $kyc['Gender'] ?? null;
            case 'MaritalStatus':
                return $kyc['MaritalStatus'] ?? null;
            case 'Occupation':
                return $kyc['Occupation'] ?? null;
            case 'PEP':
                return $kyc['PEP'] ?? null;
            case 'DepositoryType':
                return $dep['DepositoryType'] ?? null;
            case 'NomineeRelation':
                $nominees = $payload['NomineeDetail']['NomineeDetail'] ?? $payload['NomineeDetail'] ?? [];
                if (is_array($nominees) && isset($nominees[0])) {
                    return $nominees[0]['Relation'] ?? null;
                }
                return $nominees['Relation'] ?? null;
            case 'NomineeDOB':
                $nominees = $payload['NomineeDetail']['NomineeDetail'] ?? $payload['NomineeDetail'] ?? [];
                $dob = null;
                if (is_array($nominees) && isset($nominees[0])) {
                    $dob = $nominees[0]['DOB'] ?? null;
                } else {
                    $dob = $nominees['DOB'] ?? null;
                }
                if ($dob && strpos($dob, 'T') !== false) {
                    $dob = explode('T', $dob)[0];
                }
                return $dob;
            case 'NomineePAN':
                $nominees = $payload['NomineeDetail']['NomineeDetail'] ?? $payload['NomineeDetail'] ?? [];
                if (is_array($nominees) && isset($nominees[0])) {
                    return $nominees[0]['PANNO'] ?? null;
                }
                return $nominees['PANNO'] ?? null;
            case 'NomineeShare':
                $nominees = $payload['NomineeDetail']['NomineeDetail'] ?? $payload['NomineeDetail'] ?? [];
                if (is_array($nominees) && isset($nominees[0])) {
                    return $nominees[0]['SharePercentage'] ?? null;
                }
                return $nominees['SharePercentage'] ?? null;
            case 'BankAccountType':
                return $bank['BankAccountType'] ?? null;
            case 'AddressCountry':
                $addresses = $payload['AddressDetail'] ?? [];
                foreach ($addresses as $a) {
                    if (($a['AddressType'] ?? '') === 'C') {
                        return $a['AddressCountry'] ?? null;
                    }
                }
                return $addresses[0]['AddressCountry'] ?? null;
            case 'OpenDate':
                $od = $kyc['OpenDate'] ?? null;
                if ($od && strpos($od, 'T') !== false) {
                    $od = explode('T', $od)[0];
                }
                return $od;
            case 'RiskCategory':
                return $bo['RiskCategory'] ?? null;
            case 'ECSLimit':
                return $bank['ECSLimit'] ?? null;
            case 'UMRN':
                return $bank['UMRN'] ?? null;
            default:
                return null;
        }
    }

    /**
     * Compile payload for Orion POST.
     */
    private function compileOrionPayload($lead, $rule)
    {
        $mapping = $rule['field_mapping'] ?? [];
        $clientCode = $this->getLeadFieldValue($lead, $mapping, 'ClientCode') ?? 'LEAD' . $lead->id;

        $payload = [
            'KYCDetail' => [
                'ClientCode' => $clientCode,
                'OpenDate' => $this->getLeadFieldValue($lead, $mapping, 'OpenDate') ?? date('Y-m-d'),
                'CloseDate' => null,
                'PanNo' => $this->getLeadFieldValue($lead, $mapping, 'PanNo') ?? $lead->pan_number,
                'ClientType' => 'I',
                'ClientStatus' => '07',
                'ClientName' => $this->getLeadFieldValue($lead, $mapping, 'ClientName') ?? $lead->name,
                'Dob' => $this->getLeadFieldValue($lead, $mapping, 'Dob') ?? '1990-01-01',
                'FatherOrSpouse' => 'F',
                'FatherPrefix' => 'Mr',
                'FatherName' => $this->getLeadFieldValue($lead, $mapping, 'FatherName') ?? ('Father of ' . $lead->name),
                'MotherPrefix' => 'Mrs',
                'MotherName' => $this->getLeadFieldValue($lead, $mapping, 'MotherName') ?? ('Mother of ' . $lead->name),
                'Maidenprefix' => null,
                'MaidenName' => null,
                'Gender' => $this->getLeadFieldValue($lead, $mapping, 'Gender') ?? 'M',
                'MaritalStatus' => $this->getLeadFieldValue($lead, $mapping, 'MaritalStatus') ?? 'U',
                'ResidentialStatus' => 'RI',
                'Citizenship' => 'IN',
                'IDProof' => '05',
                'IDProofRef' => $this->getLeadFieldValue($lead, $mapping, 'Aadhar') ?? $lead->aadhar_number,
                'Aadhar' => $this->getLeadFieldValue($lead, $mapping, 'Aadhar') ?? $lead->aadhar_number,
                'AnnualIncome' => $this->getLeadFieldValue($lead, $mapping, 'AnnualIncome') ?? '03',
                'AnnualIncomeDate' => $this->getLeadFieldValue($lead, $mapping, 'AnnualIncomeDate') ?? date('Y-m-d'),
                'NetWorth' => (float)($this->getLeadFieldValue($lead, $mapping, 'NetWorth') ?? 550000.0),
                'NetWorthDate' => $this->getLeadFieldValue($lead, $mapping, 'NetWorthDate') ?? date('Y-m-d'),
                'PEP' => $this->getLeadFieldValue($lead, $mapping, 'PEP') ?? '01',
                'Occupation' => $this->getLeadFieldValue($lead, $mapping, 'Occupation') ?? '01',
            ],
            'AddressDetail' => [
                [
                    'ClientCode' => $clientCode,
                    'AddressType' => 'C',
                    'AddressLine1' => $this->getLeadFieldValue($lead, $mapping, 'AddressLine1') ?? 'Line 1',
                    'AddressLine2' => $this->getLeadFieldValue($lead, $mapping, 'AddressLine2'),
                    'AddressLine3' => $this->getLeadFieldValue($lead, $mapping, 'AddressLine3'),
                    'AddressCity' => $this->getLeadFieldValue($lead, $mapping, 'AddressCity') ?? 'City',
                    'AddressPincode' => $this->getLeadFieldValue($lead, $mapping, 'AddressPincode') ?? '110001',
                    'AddressState' => $this->getLeadFieldValue($lead, $mapping, 'AddressState') ?? 'DL',
                    'AddressCountry' => $this->getLeadFieldValue($lead, $mapping, 'AddressCountry') ?? 'IN',
                    'AddressPrimary' => 'Y',
                    'AddressProof' => '01',
                ]
            ],
            'ContactDetail' => [
                [
                    'ClientCode' => $clientCode,
                    'ContactType' => 'M',
                    'ContactNo' => $this->getLeadFieldValue($lead, $mapping, 'ContactNo') ?? $lead->phone,
                    'PrimaryFlag' => 'Y',
                    'ActiveFlag' => 'Y',
                    'RelatedTo' => 'S'
                ],
                [
                    'ClientCode' => $clientCode,
                    'ContactType' => 'E',
                    'ContactEmail' => $this->getLeadFieldValue($lead, $mapping, 'ContactEmail') ?? $lead->email,
                    'PrimaryFlag' => 'Y',
                    'ActiveFlag' => 'Y',
                    'RelatedTo' => 'S'
                ]
            ],
            'BankDetail' => [
                'ClientCode' => $clientCode,
                'BankAccountNumber' => $this->getLeadFieldValue($lead, $mapping, 'BankAccountNumber') ?? '0000000000',
                'BankAccountType' => $this->getLeadFieldValue($lead, $mapping, 'BankAccountType') ?? 'S',
                'BankIFSC' => $this->getLeadFieldValue($lead, $mapping, 'BankIFSC') ?? 'IFSC0000001',
                'BankName' => $this->getLeadFieldValue($lead, $mapping, 'BankName') ?? 'Bank',
                'BankAddress1' => 'Address',
                'BankCity' => 'City',
                'BankPincode' => '110001',
                'BankState' => 'DL',
                'BankCountry' => 'IN',
                'PrimaryFlag' => 'Y',
                'FundMandate' => $this->getLeadFieldValue($lead, $mapping, 'FundMandate') ?? null,
                'UMRN' => $this->getLeadFieldValue($lead, $mapping, 'UMRN') ?? null,
                'ECSLimit' => $this->getLeadFieldValue($lead, $mapping, 'ECSLimit') ?? null,
            ],
            'BackOfficeDetail' => [
                'ClientCode' => $clientCode,
                'TradingSoftwareType' => $this->getLeadFieldValue($lead, $mapping, 'TradingSoftwareType') ?? 'NONE',
                'RiskCategory' => $this->getLeadFieldValue($lead, $mapping, 'RiskCategory') ?? 'LOW',
                'PayoutFlag' => $this->getLeadFieldValue($lead, $mapping, 'PayoutFlag') ?? 'Y',
                'KYCMode' => 'NRML'
            ],
            'DepositoryDetail' => [
                [
                    'ClientCode' => $clientCode,
                    'DepositoryType' => $this->getLeadFieldValue($lead, $mapping, 'DepositoryType') ?? 'CDSL',
                    'DepositoryClientID' => $this->getLeadFieldValue($lead, $mapping, 'DepositoryClientID') ?? $lead->dp_id ?? '00000000',
                    'PrimaryFlag' => 'Y'
                ]
            ],
            'NomineeDetail' => [
                'ClientCode' => $clientCode,
                'NomineeOptFlag' => 'Y',
                'NomineeDetail' => [
                    [
                        'ClientCode' => $clientCode,
                        'NomineeType' => 'N',
                        'Name' => $this->getLeadFieldValue($lead, $mapping, 'NomineeName') ?? 'Nominee',
                        'Relation' => $this->getLeadFieldValue($lead, $mapping, 'NomineeRelation') ?? 'S',
                        'DOB' => $this->getLeadFieldValue($lead, $mapping, 'NomineeDOB') ?? '2000-01-01',
                        'PANNO' => $this->getLeadFieldValue($lead, $mapping, 'NomineePAN') ?? 'ASDFR8990Y',
                        'SharePercentage' => (float)($this->getLeadFieldValue($lead, $mapping, 'NomineeShare') ?? 100.0),
                    ]
                ]
            ],
            'ExchangeDetail' => [
                [
                    'ClientCode' => $clientCode,
                    'ExchangeID' => 'NSE',
                    'CategoryCode' => '1',
                    'ActiveFlag' => 'Y'
                ]
            ],
            'SegmentDetail' => [
                [
                    'ClientCode' => $clientCode,
                    'ExchangeID' => 'NSE',
                    'SegmentID' => 'CAP',
                    'TradingAllow' => 'Y'
                ]
            ]
        ];

        return $payload;
    }

    /**
     * Retrieve lead field values mapped from rule.
     */
    private function getLeadFieldValue($lead, $mapping, $orionFieldName)
    {
        $crmKey = $mapping[$orionFieldName] ?? null;
        if (empty($crmKey)) return null;

        if ($crmKey === 'name') return $lead->name;
        if ($crmKey === 'email') return $lead->email;
        if ($crmKey === 'phone') return $lead->phone;
        if ($crmKey === 'subject') return $lead->subject;
        if ($crmKey === 'pan_number') return $lead->pan_number;
        if ($crmKey === 'aadhar_number') return $lead->aadhar_number;
        if ($crmKey === 'dp_id') return $lead->dp_id;

        if (strpos($crmKey, 'custom_') === 0) {
            $cfId = substr($crmKey, 7);
            $val = LeadCustomFieldValue::where('lead_id', $lead->id)
                ->where('field_id', $cfId)
                ->first();
            return $val ? $val->value : null;
        }

        return null;
    }

    /**
     * Trigger EKYC POST request directly (e.g. when E-Sign is complete)
     */
    public static function triggerEkycPostDirectly($lead)
    {
        $settings = self::getOrionSettings();
        $rules = $settings['rules'] ?? [];
        $credentials = $settings['credentials'] ?? [];

        if (empty($credentials['username']) || empty($credentials['password'])) {
            $baseUrl = $credentials['base_url'] ?? '';
            $isRivexa = (strpos($baseUrl, 'rivexaflow.space') !== false || strpos($baseUrl, 'api/crm') !== false);
            if (!$isRivexa) {
                return; // Credentials not configured and not Rivexa
            }
        }

        // Find the first rule that triggers auto_send_ekyc or auto_send_modify, 
        // or just use any rule with mapping configured
        $rule = collect($rules)->first(function($r) {
            return in_array($r['trigger_mode'] ?? '', ['auto_send_ekyc', 'auto_send_modify', 'manual_fetch']);
        }) ?? (collect($rules)->first() ?? null);

        if (!$rule) {
            return; // No mapping rules configured
        }

        $instance = new self();
        $isModify = ($rule['trigger_mode'] ?? '') === 'auto_send_modify';

        try {
            $instance->sendEkycDetails($lead, $rule, $credentials, $isModify);
        } catch (\Exception $ex) {
            \Log::error("Direct Orion/Rivexa EKYC Post failed for lead ID {$lead->id}: " . $ex->getMessage());
        }
    }
}
