<?php

namespace Workdo\Ekyc\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EkycMaintenanceController extends Controller
{
    /**
     * Show maintenance page
     */
    public function show()
    {
        $settings = getCompanyAllSetting();
        
        $message = $settings['ekyc_maintenance_message'] ?? 'We are currently upgrading our KYC system. Please check back later.';
        $maintenanceStart = $settings['ekyc_maintenance_start'] ?? null;
        $maintenanceEnd = $settings['ekyc_maintenance_end'] ?? null;

        return view('ekyc::maintenance', compact('message', 'maintenanceStart', 'maintenanceEnd'));
    }
}
