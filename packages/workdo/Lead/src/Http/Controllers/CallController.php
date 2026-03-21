<?php

namespace Workdo\Lead\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Workdo\Hrm\Entities\Employee;

class CallController extends Controller
{
    /**

     * Save user extension from modal.
     */
    public function saveExtension(Request $request)
    {
        $user = Auth::user();

        // 24 Hour Restriction Check
        if ($user->last_extension_edit) {
            $lastEdit = new \DateTime($user->last_extension_edit);
            $now = new \DateTime();
            $diff = $now->diff($lastEdit);
            $hours = $diff->h + ($diff->days * 24);

            if ($hours < 24) {
                return response()->json([
                    'status' => 'error',
                    'message' => __("You can only edit your extension details once every 24 hours. Please try again later."),
                ], 403);
            }
        }

        $request->validate([
            'extension_1' => 'required|string|max:50',
            'extension_2' => 'nullable|string|max:50',
        ]);

        $user->extension_1 = $request->extension_1;
        $user->extension_2 = $request->extension_2;
        // Keep the old 'extension' column in sync with the active one for backward compatibility if needed, 
        // or just use extension_1 as default.
        $user->extension = ($user->active_extension == 2 && !empty($user->extension_2)) ? $user->extension_2 : $user->extension_1;
        $user->last_extension_edit = date('Y-m-d H:i:s');
        $user->save();

        // Save API Mapping to settings
        $settingsToSave = [
            'user_ext_1_api_id_' . $user->id => $request->api_ext_1 ?? '',
            'user_ext_2_api_id_' . $user->id => $request->api_ext_2 ?? '',
        ];

        foreach ($settingsToSave as $key => $value) {
            \DB::table('settings')->updateOrInsert(
                [
                    'key' => $key,
                    'created_by' => creatorId(),
                    'workspace' => getActiveWorkSpace(),
                ],
                [
                    'value' => $value,
                ]
            );
        }
        companySettingCacheForget();

        return response()->json([
            'status' => 'success',
            'message' => __('Extensions and API Selection saved successfully.'),
        ]);
    }

    /**
     * Switch the active extension for the user.
     */
    public function switchActiveExtension(Request $request)
    {
        $user = Auth::user();
        $target = $request->input('active_index'); // 1 or 2

        if (!in_array($target, [1, 2])) {
            return response()->json(['status' => 'error', 'message' => __('Invalid extension index.')], 400);
        }

        $extValue = ($target == 1) ? $user->extension_1 : $user->extension_2;

        if (empty($extValue)) {
            return response()->json(['status' => 'error', 'message' => __('Selected extension is empty.')], 400);
        }

        $user->active_extension = $target;
        // Update deprecated column for safety
        $user->extension = $extValue;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Active extension switched to Ext ') . $target . ' (' . $extValue . ')',
            'active_extension' => $target,
            'extension_value' => $extValue
        ]);
    }

    /**
     * Switch the active API for the user.
     */
    public function switchActiveApi(Request $request)
    {
        $user = Auth::user();
        $targetApiId = $request->input('active_api_id'); 
        $activeExtIdx = $user->active_extension == 2 ? 2 : 1;

        \DB::table('settings')->updateOrInsert(
            [
                'key' => 'user_ext_' . $activeExtIdx . '_api_id_' . $user->id,
                'created_by' => creatorId(),
                'workspace' => getActiveWorkSpace(),
            ],
            [
                'value' => $targetApiId,
            ]
        );
        companySettingCacheForget();

        return response()->json([
            'status' => 'success',
            'message' => __('API for Ext ') . $activeExtIdx . __(' switched successfully.'),
            'active_api_id' => $targetApiId
        ]);
    }

    /**
     * Handle the Click to Call request.
     */
    public function makeCall(Request $request)
    {
        $user = Auth::user();
        $phoneNumber = $request->input('phone_number');

        if (!$phoneNumber) {
            return response()->json([
                'status' => 'error',
                'message' => __('Phone number is required.'),
            ], 400);
        }

        $extension = ($user->active_extension == 2) ? $user->extension_2 : $user->extension_1;

        if (empty($extension)) {
            return response()->json([
                'status' => 'error',
                'code' => 'MISSING_EXTENSION',
                'message' => __('Please set your extension in your profile before making a call.'),
            ], 400);
        }

        if (!$this->isCallingEnabled($user)) {
            return response()->json([
                'status' => 'copy',
                'phone_number' => $phoneNumber,
                'message' => __('No API URL configured. Call button behaves as copy-to-clipboard.'),
            ], 200);
        }

        $callingUrl = $this->getCallingUrl($user);

        if (empty($callingUrl)) {
            return response()->json([
                'status' => 'copy',
                'phone_number' => $phoneNumber,
                'message' => __('No URL configured. Call button behaves as copy-to-clipboard.'),
            ], 200);
        }

        try {
            // Check if it's an HTTP Gateway or a Protocol Handler
            if (strpos($callingUrl, 'http') === 0) {
                // Check if user has provided placeholders for custom parameters
                // Supported: {ext}, {exten}, {num}, {number}
                if (strpos($callingUrl, '{ext}') !== false || strpos($callingUrl, '{exten}') !== false || 
                    strpos($callingUrl, '{num}') !== false || strpos($callingUrl, '{number}') !== false) {
                    
                    $finalUrl = str_replace(['{ext}', '{exten}'], $extension, $callingUrl);
                    $finalUrl = str_replace(['{num}', '{number}'], $phoneNumber, $finalUrl);
                } else {
                    // Fallback: HTTP/HTTPS Gateway URL - Append default query parameters
                    $urlParts = parse_url($callingUrl);
                    $separator = isset($urlParts['query']) ? '&' : '?';
                    $finalUrl = $callingUrl . $separator . http_build_query([
                        'ext' => $extension,
                        'num' => $phoneNumber,
                    ]);
                }
            } else {
                // Protocol Handler (zoiper:, tel:, etc.)
                // Usually these expect the number directly. If the URL already ends with a separator, 
                // we just append the number. Otherwise, we assume it's like "zoiper:" and append the number.
                $finalUrl = $callingUrl . $phoneNumber;

                // If the user included some placeholder or specific separator in their base URL, 
                // we should respect that, but for simple "zoiper:" or "tel:", this is standard.
            }

            \Log::info('Click to Call URL generated', [
                'user_id' => $user->id,
                'url' => $finalUrl
            ]);

            // Return the URL to the frontend so the browser can "hit" it.
            // This is necessary if the calling API is on a local network (192.168.x.x)
            // that the server cannot reach directly.
            return response()->json([
                'status' => 'success',
                'message' => __('Call URL generated.'),
                'url' => $finalUrl
            ]);

        } catch (\Exception $e) {
            \Log::error('Click to Call Exception', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => __('An error occurred: ') . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the appropriate calling URL based on user's priority or fallback.
     */
    private function getCallingUrl($user)
    {
        $settings = getCompanyAllSetting($user->id, $user->workspace_id);
        $activeExtIdx = $user->active_extension == 2 ? 2 : 1;
        
        // Get the API ID mapped to this extension (e.g., 'user_1', 'dept_1', 'global_2')
        $apiId = $settings['user_ext_'.$activeExtIdx.'_api_id_'.$user->id] ?? '';

        if (!empty($apiId)) {
            $parts = explode('_', $apiId);
            $type = $parts[0]; // user, dept, global
            $index = $parts[1]; // 1, 2, 3

            if ($type == 'user') {
                $key = 'user_api_'.$index.'_url_'.$user->id;
                if (!empty($settings[$key])) return $settings[$key];
            } elseif ($type == 'dept') {
                if (module_is_active('Hrm', $user->workspace_id)) {
                    $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $user->id)->first();
                    if ($employee && $employee->department_id) {
                        $key = 'dept_api_'.$index.'_url_'.$employee->department_id;
                        if (!empty($settings[$key])) return $settings[$key];
                    }
                }
            } elseif ($type == 'global') {
                $key = 'global_calling_api_'.$index.'_url';
                if (!empty($settings[$key])) return $settings[$key];
            }
        }

        // --- OLD FALLBACK LOGIC (for safety or if no mapping selected) ---
        
        // 1. Check for individual user-specific URL (legacy key)
        $userKey = 'click_to_call_url_user_' . $user->id;
        if (!empty($settings[$userKey])) {
            return $settings[$userKey];
        }

        // 2. Try getting department specific URL if HRM is active (legacy key)
        if (module_is_active('Hrm', $user->workspace_id)) {
            $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $user->id)->first();
            if ($employee && $employee->department_id) {
                $deptKey = 'dept_calling_url_' . $employee->department_id;
                if (!empty($settings[$deptKey])) {
                    return $settings[$deptKey];
                }
            }
        }

        // 3. Fallback to default Lead Calling URL (legacy key)
        if (!empty($settings['lead_default_calling_url'])) {
            return $settings['lead_default_calling_url'];
        }

        // 4. Fallback to Global API 1 if everything else fails
        return !empty($settings['global_calling_api_1_url']) ? $settings['global_calling_api_1_url'] : null;
    }

    /**
     * Click to Call Manager View
     */
    public function manager()
    {
        if (\Auth::user()->isAbleTo('crm manage')) {
            $user = \Auth::user();
            $settings = getCompanyAllSetting($user->id, getActiveWorkSpace());
            echo "USER ID: " . $user->id . "\n";
            echo "WORKSPACE: " . getActiveWorkSpace() . "\n";
            die(print_r($settings, true));



            $departments = [];
            if (module_is_active('Hrm', getActiveWorkSpace())) {
                $departments = \Workdo\Hrm\Entities\Department::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();
            }

            $users = \App\Models\User::where('workspace_id', getActiveWorkSpace())
                ->where('type', '!=', 'client')
                ->where('id', '!=', creatorId())
                ->get();

            return view('lead::call.manager', compact('settings', 'departments', 'users'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Save Manager Settings
     */
    public function saveManagerSettings(Request $request)
    {
        if (\Auth::user()->isAbleTo('crm manage')) {
            $post = $request->all();
            unset($post['_token']);

            foreach ($post as $key => $value) {
                // We use the creatorId/workspace_id to save settings globally for the company
                \DB::table('settings')->updateOrInsert(
                    [
                        'key' => $key,
                        'created_by' => creatorId(),
                        'workspace' => getActiveWorkSpace(),
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }

            // Explicitly handle unchecked department toggles which are not sent in $request
            $current_workspace = getActiveWorkSpace();
            if (module_is_active('Hrm', $current_workspace)) {
                $departments = \Workdo\Hrm\Entities\Department::where('created_by', creatorId())->where('workspace', $current_workspace)->get();
                foreach ($departments as $dept) {
                    $key = 'click_to_call_enabled_dept_' . $dept->id;
                    if (!isset($post[$key])) {
                        \DB::table('settings')->updateOrInsert(
                            [
                                'key' => $key,
                                'created_by' => creatorId(),
                                'workspace' => $current_workspace,
                            ],
                            [
                                'value' => 'off',
                            ]
                        );
                    }
                }
            }

            // Forget cache to apply changes immediately
            companySettingCacheForget();

            return redirect()->back()->with('success', __('Settings saved successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Check if click to call is enabled for the user's department.
     */
    private function isCallingEnabled($user)
    {
        $settings = getCompanyAllSetting($user->id, getActiveWorkSpace());

        // If individual URL is set, we consider it enabled for that user
        $userKey = 'click_to_call_url_user_' . $user->id;
        if (!empty($settings[$userKey])) {
            return true;
        }

        $current_workspace = getActiveWorkSpace();
        if (module_is_active('Hrm', $current_workspace)) {
            $emp = \Workdo\Hrm\Entities\Employee::where('user_id', $user->id)->where('workspace', $current_workspace)->first();
            if ($emp && $emp->department) {
                return (isset($settings['click_to_call_enabled_dept_' . $emp->department->id]) && $settings['click_to_call_enabled_dept_' . $emp->department->id] == 'on');
            }
            // non-owner users without a department should be denied if HRM is active
            if ($user->type !== 'company' && $user->type !== 'super admin') {
                return false;
            }
        }

        // Allowed by default for company owners, or if HRM is not active
        return true;
    }
}
