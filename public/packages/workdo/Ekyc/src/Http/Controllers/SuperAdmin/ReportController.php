<?php

namespace Workdo\Ekyc\Http\Controllers\SuperAdmin;

use Illuminate\Routing\Controller;
use Workdo\Ekyc\Entities\EkycLead;
use App\Models\User;

class ReportController extends Controller
{
    public function index()
    {
        $companies = User::where('type', 'company')->get();
        $reportData = [];

        foreach ($companies as $company) {
            $total = EkycLead::where('created_by', $company->id)->count();
            $verified = EkycLead::where('created_by', $company->id)->where('status', 'verified')->count();
            $pending = EkycLead::where('created_by', $company->id)->where('status', 'pending')->count();
            
            $reportData[] = [
                'company_name' => $company->name,
                'email' => $company->email,
                'total_kyc' => $total,
                'verified_kyc' => $verified,
                'pending_kyc' => $pending,
                'success_rate' => $total > 0 ? round(($verified / $total) * 100, 2) : 0
            ];
        }

        return view('ekyc::superadmin.reports', compact('reportData'));
    }
}
