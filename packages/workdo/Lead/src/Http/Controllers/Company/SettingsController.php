<?php

namespace Workdo\Lead\Http\Controllers\Company;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use Workdo\Hrm\Entities\Department;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($settings)
    {
        if (Auth::check() && module_is_active('Lead')) {
            $departments = [];
            if (module_is_active('Hrm')) {
                $departments = Department::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();
            }
            return view('lead::company.settings.index', compact('settings', 'departments'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('lead manage')) {
            $post = $request->all();
            unset($post['_token']);

            // Expected format: dept_calling_url_{department_id} and click_to_call_enabled_dept_{department_id}

            $departments = [];
            if (module_is_active('Hrm')) {
                $departments = \Workdo\Hrm\Entities\Department::where('created_by', creatorId())
                    ->where('workspace', getActiveWorkSpace())
                    ->get();

                // For checkboxes, if they are unchecked they aren't in $post. Manually set them.
                foreach ($departments as $dept) {
                    $checkboxKey = 'click_to_call_enabled_dept_' . $dept->id;
                    if (!isset($post[$checkboxKey])) {
                        $post[$checkboxKey] = 'off';
                    }
                }
            }

            foreach ($post as $key => $value) {
                if (strpos($key, 'dept_calling_url_') === 0 || strpos($key, 'click_to_call_enabled_dept_') === 0 || $key === 'lead_default_calling_url') {
                    $setting = Setting::updateOrCreate(
                        [
                            'key' => $key,
                            'workspace' => getActiveWorkSpace(),
                            'created_by' => creatorId(),
                        ],
                        [
                            'value' => $value ?? '',
                        ]
                    );
                }
            }

            // clear cache for settings if necessary
            $workspace = \App\Models\WorkSpace::find(getActiveWorkSpace());
            if ($workspace) {
                \Illuminate\Support\Facades\Cache::forget('company_settings_' . $workspace->slug);
            }

            return redirect()->back()->with('success', __('Lead Settings updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
