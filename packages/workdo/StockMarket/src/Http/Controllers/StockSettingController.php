<?php

namespace Workdo\StockMarket\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

class StockSettingController extends Controller
{
    public function index()
    {
        if (!Auth::user()->isAbleTo('stock setting manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $settings = getCompanyAllSetting();
        return view('stockmarket::settings.index', compact('settings'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('stock setting manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $post = $request->except(['_token']);

        foreach ($post as $key => $value) {
            Setting::updateOrInsert(
                ['key' => $key, 'workspace' => getActiveWorkSpace(), 'created_by' => creatorId()],
                ['value' => $value]
            );
        }

        comapnySettingCacheForget();

        return redirect()->back()->with('success', __('Stock Market settings saved successfully!'));
    }
}
