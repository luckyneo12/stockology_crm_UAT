<?php

namespace Workdo\Ekyc\Http\Controllers\SuperAdmin;

use Illuminate\Routing\Controller;

class SettingsController extends Controller
{
    public function index($settings)
    {
        return view('ekyc::settings', compact('settings'));
    }
}
