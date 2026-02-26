<?php

namespace Workdo\Ekyc\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckEkycMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Use admin settings for public routes (no auth required)
        // This avoids issues with getCompanyAllSetting() when user is not authenticated
        try {
            $settings = $request->user() ? getCompanyAllSetting() : getAdminAllSetting();
        } catch (\Exception $e) {
            // If settings retrieval fails, allow access (fail open)
            return $next($request);
        }

        // Check if maintenance mode is enabled
        $maintenanceMode = !empty($settings['ekyc_maintenance_mode']) && $settings['ekyc_maintenance_mode'] == 'on';

        if (!$maintenanceMode) {
            return $next($request);
        }

        // Check if scheduled maintenance is active
        $maintenanceStart = $settings['ekyc_maintenance_start'] ?? null;
        $maintenanceEnd = $settings['ekyc_maintenance_end'] ?? null;

        if ($maintenanceStart && $maintenanceEnd) {
            $now = now();
            $start = \Carbon\Carbon::parse($maintenanceStart);
            $end = \Carbon\Carbon::parse($maintenanceEnd);

            // If current time is not within maintenance window, allow access
            if ($now->lt($start) || $now->gt($end)) {
                return $next($request);
            }
        }

        // Check if user is whitelisted
        if ($this->isWhitelisted($request, $settings)) {
            return $next($request);
        }

        // Redirect to maintenance page
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'eKYC system is currently under maintenance',
            ], 503);
        }

        return redirect()->route('ekyc.maintenance');
    }

    /**
     * Check if user/IP is whitelisted
     */
    private function isWhitelisted(Request $request, array $settings)
    {
        // Check if user is admin
        if ($request->user() && $request->user()->type === 'super admin') {
            return true;
        }

        // Check IP whitelist
        $whitelistIps = $settings['ekyc_maintenance_whitelist_ips'] ?? '';
        if ($whitelistIps) {
            $ips = array_map('trim', explode(',', $whitelistIps));
            if (in_array($request->ip(), $ips)) {
                return true;
            }
        }

        // Check user ID whitelist
        if ($request->user()) {
            $whitelistUsers = $settings['ekyc_maintenance_whitelist_users'] ?? '';
            if ($whitelistUsers) {
                $userIds = array_map('trim', explode(',', $whitelistUsers));
                if (in_array($request->user()->id, $userIds)) {
                    return true;
                }
            }
        }

        return false;
    }
}
