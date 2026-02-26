<?php

namespace Workdo\Ekyc\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class EkycController extends Controller
{
    public function index()
    {
        return view('ekyc::index');
    }

    public function setting()
    {
        return view('ekyc::settings');
    }

    public function pdfEditor()
    {
        $company_settings = getCompanyAllSetting();
        return view('ekyc::pdf_editor', compact('company_settings'));
    }

    public function saveSettings(Request $request)
    {
        $all_inputs = $request->all();
        unset($all_inputs['_token']);

        $company_settings = getCompanyAllSetting();

        // Handle Company Logo Upload
        if ($request->hasFile('ekyc_company_logo')) {
            $logo_name = 'ekyc_company_logo_' . time() . '.' . $request->ekyc_company_logo->getClientOriginalExtension();
            $upload = upload_file($request, 'ekyc_company_logo', $logo_name, 'ekyc/logo');
            if ($upload['flag'] == 1) {
                $all_inputs['ekyc_company_logo'] = $upload['url'];
                
                // Delete old logo
                $old_logo = isset($company_settings['ekyc_company_logo']) ? $company_settings['ekyc_company_logo'] : '';
                if(!empty($old_logo) && check_file($old_logo)) {
                    delete_file($old_logo);
                }
            } else {
                return redirect()->back()->with('error', $upload['msg']);
            }
        }

        // Handle Authorized Signature Upload
        if ($request->hasFile('ekyc_company_auth_sign')) {
            $sign_name = 'ekyc_company_auth_sign_' . time() . '.' . $request->ekyc_company_auth_sign->getClientOriginalExtension();
            $upload = upload_file($request, 'ekyc_company_auth_sign', $sign_name, 'ekyc/signature');
            if ($upload['flag'] == 1) {
                $all_inputs['ekyc_company_auth_sign'] = $upload['url'];
                
                // Delete old signature
                $old_sign = isset($company_settings['ekyc_company_auth_sign']) ? $company_settings['ekyc_company_auth_sign'] : '';
                if(!empty($old_sign) && check_file($old_sign)) {
                    delete_file($old_sign);
                }
            } else {
                return redirect()->back()->with('error', $upload['msg']);
            }
        }

        foreach ($all_inputs as $key => $value) {
            // Only process our module's settings
            if (
                strpos($key, 'ekyc_') === 0 || 
                strpos($key, 'otp_') === 0 || 
                strpos($key, 'digio_') === 0
            ) {
                // Skip raw file objects
                if ($request->hasFile($key)) continue;

                Setting::updateOrInsert(
                    [
                        'key' => $key,
                        'workspace' => getActiveWorkSpace(),
                        'created_by' => creatorId(),
                    ],
                    ['value' => $value]
                );
            }
        }

        if (function_exists('comapnySettingCacheForget')) {
            comapnySettingCacheForget();
        }

        return redirect()->back()->with('success', __('eKYC Settings saved successfully.'));
    }

    private function getNextStep($currentStep, $settings)
    {
        $steps = [
            'pan' => 'ekyc_pan',
            'aadhaar' => 'ekyc_aadhaar',
            'selfie' => 'ekyc_selfie',
            'bank' => 'ekyc_bank',
            'video' => 'ekyc_video'
        ];

        $keys = array_keys($steps);
        $currentIndex = array_search($currentStep, $keys);

        // If current step is completed (or we are just starting), find next active
        if ($currentIndex === false) {
             $currentIndex = -1; // Start from beginning
        }

        for ($i = $currentIndex + 1; $i < count($keys); $i++) {
            $stepKey = $keys[$i];
            $settingKey = $steps[$stepKey];
            
            // If setting is 'on' or not set (default on), return this step
            if (!isset($settings[$settingKey]) || $settings[$settingKey] == 'on') {
                return $stepKey;
            }
        }

        return 'completed';
    }

    public function clientKycJourney($id)
    {
        $settings = getCompanyAllSetting();
        $requestedStep = request()->get('step');
        
        // If no step requested, find the first active step
        if (!$requestedStep) {
            $requestedStep = $this->getNextStep('start', $settings);
             return redirect()->route('client.kyc.journey', ['id' => $id, 'step' => $requestedStep]);
        }
        
        // If specific step requested but disabled, move to next allowed
        $settingMap = [
            'pan' => 'ekyc_pan',
            'aadhaar' => 'ekyc_aadhaar',
            'selfie' => 'ekyc_selfie',
            'bank' => 'ekyc_bank',
            'video' => 'ekyc_video'
        ];
        
        if (isset($settingMap[$requestedStep]) && isset($settings[$settingMap[$requestedStep]]) && $settings[$settingMap[$requestedStep]] == 'off') {
             $next = $this->getNextStep($requestedStep, $settings);
             return redirect()->route('client.kyc.journey', ['id' => $id, 'step' => $next]);
        }


        if ($requestedStep == 'pan') {
            return view('ekyc::kyc_flow.pan_verify', compact('id'));
        } elseif ($requestedStep == 'aadhaar') {
            return view('ekyc::kyc_flow.aadhaar_verify', compact('id'));
        } elseif ($requestedStep == 'selfie') {
            return view('ekyc::kyc_flow.selfie_match', compact('id'));
        } elseif ($requestedStep == 'bank') {
            return view('ekyc::kyc_flow.bank_verify', compact('id'));
        } elseif ($requestedStep == 'video') {
            return view('ekyc::kyc_flow.video_kyc', compact('id'));
        } elseif ($requestedStep == 'completed') {
            return view('ekyc::kyc_flow.status', compact('id'));
        }
        
        return view('ekyc::kyc_flow.status', compact('id'));
    }

    public function verifyPan(Request $request)
    {
        $settings = getCompanyAllSetting();
        $next = $this->getNextStep('pan', $settings);
        return redirect()->route('client.kyc.journey', ['id' => 1, 'step' => $next]);
    }

    public function verifyAadhaar(Request $request)
    {
        $settings = getCompanyAllSetting();
        $next = $this->getNextStep('aadhaar', $settings);
        return redirect()->route('client.kyc.journey', ['id' => 1, 'step' => $next]);
    }

    public function selfieMatch(Request $request)
    {
        $settings = getCompanyAllSetting();
        $next = $this->getNextStep('selfie', $settings);
        return redirect()->route('client.kyc.journey', ['id' => 1, 'step' => $next]);
    }

    public function bankVerify(Request $request)
    {
        $settings = getCompanyAllSetting();
        $next = $this->getNextStep('bank', $settings);
        return redirect()->route('client.kyc.journey', ['id' => 1, 'step' => $next]);
    }

    public function videoKyc(Request $request)
    {
        // Video is usually last, so go to completed
        return redirect()->route('client.kyc.journey', ['id' => 1, 'step' => 'completed']);
    }

    public function webhook(Request $request)
    {
        return response()->json(['status' => 'success']);
    }
}
