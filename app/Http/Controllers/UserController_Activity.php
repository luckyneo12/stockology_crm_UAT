<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class UserController_Activity extends Controller
{
    /**
     * Display comprehensive user activity logs with advanced filtering
     */
    public function UserActivityHistory(Request $request)
    {
        if (!Auth::user()->isAbleTo('user logs history')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            // Check if table exists
            if (!Schema::hasTable('user_activity_logs')) {
                return view('users.activity_simple', [
                    'error' => 'Activity tracking table not found. Please run setup script first.'
                ]);
            }

            // Base query
            $query = UserActivityLog::with('user');
            
            // Super admin can see all, company admin only sees their users
            if (Auth::user()->type != 'super admin') {
                $query->whereHas('user', function($q) {
                    $q->where('created_by', Auth::user()->id);
                });
            }

            // Apply filters
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('module')) {
                $query->where('module', $request->module);
            }

            if ($request->filled('activity_type')) {
                $query->where('activity_type', $request->activity_type);
            }

            if ($request->filled('ip_address')) {
                $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
            }

            if ($request->filled('country')) {
                $query->where('country', 'like', '%' . $request->country . '%');
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('description', 'like', '%' . $request->search . '%')
                      ->orWhere('url', 'like', '%' . $request->search . '%');
                });
            }

            // Get statistics
            $statistics = [
                'total_activities' => $query->count(),
                'active_users' => $query->distinct('user_id')->count('user_id'),
                'modules_used' => $query->distinct('module')->count('module'),
                'avg_response_time' => round($query->avg('response_time_ms') ?? 0)
            ];

            // Get paginated results
            $activities = $query->orderBy('created_at', 'desc')->paginate(50);

            // Get filter options
            $users = User::where('type', '!=', 'super admin')
                        ->when(Auth::user()->type != 'super admin', function($q) {
                            $q->where('created_by', Auth::user()->id);
                        })
                        ->orderBy('name')
                        ->pluck('name', 'id');

            $modules = UserActivityLog::distinct('module')
                        ->when(Auth::user()->type != 'super admin', function($q) {
                            $q->whereHas('user', function($subQ) {
                                $subQ->where('created_by', Auth::user()->id);
                            });
                        })
                        ->pluck('module', 'module');

            return view('users.activity_history_advanced', compact('activities', 'users', 'modules', 'statistics'));

        } catch (\Exception $e) {
            Log::error('Activity History Error: ' . $e->getMessage());
            return view('users.activity_simple', [
                'error' => 'Error loading activities: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * View specific activity details
     */
    public function UserActivityView($id)
    {
        try {
            // Use raw database query to completely avoid model issues
            $activity = \DB::table('user_activity_logs')->where('id', $id)->first();
            
            if (!$activity) {
                return redirect()->back()->with('error', __('Activity not found.'));
            }
            
            // Get user info directly
            $userInfo = \DB::table('users')->select('id', 'name', 'email', 'created_by')->where('id', $activity->user_id)->first();
            
            // Check permissions
            if (Auth::user()->type != 'super admin') {
                if ($userInfo && $userInfo->created_by != Auth::user()->id) {
                    return redirect()->back()->with('error', __('Permission denied.'));
                }
            }
            
            // Convert stdClass to array properly
            $activityArray = (array) $activity;
            $userArray = (array) $userInfo;
            
            // Create simple array with raw data
            $activityData = [
                'id' => $activityArray['id'],
                'user_name' => $userArray['name'] ?? 'Unknown User',
                'user_email' => $userArray['email'] ?? 'Unknown Email',
                'activity_type' => $activityArray['activity_type'],
                'module' => $activityArray['module'],
                'description' => $activityArray['description'],
                'url' => $activityArray['url'],
                'method' => $activityArray['method'],
                'ip_address' => $activityArray['ip_address'],
                'user_agent' => $activityArray['user_agent'],
                'browser' => $activityArray['browser'],
                'browser_version' => $activityArray['browser_version'],
                'os' => $activityArray['os'],
                'os_version' => $activityArray['os_version'],
                'device_type' => $activityArray['device_type'],
                'country' => $activityArray['country'],
                'city' => $activityArray['city'],
                'latitude' => $activityArray['latitude'],
                'longitude' => $activityArray['longitude'],
                'created_at' => $activityArray['created_at'],
                'updated_at' => $activityArray['updated_at']
            ];
            
            return view('users.activity_view', ['activity' => (object) $activityData]);
            
        } catch (\Exception $e) {
            Log::error('Activity View Error: ' . $e->getMessage());
            return redirect()->back()->with('error', __('Activity not found.'));
        }
    }

    /**
     * Delete activity
     */
    public function UserActivityDestroy($id)
    {
        if (Auth::user()->type != 'super admin') {
            return response()->json(['success' => false, 'message' => 'Permission denied']);
        }

        try {
            $activity = UserActivityLog::findOrFail($id);
            $activity->delete();
            
            return response()->json(['success' => true, 'message' => 'Activity deleted successfully']);
            
        } catch (\Exception $e) {
            Log::error('Activity Delete Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error deleting activity']);
        }
    }

    /**
     * Export activities to CSV
     */
    public function UserActivityExport(Request $request)
    {
        if (!Auth::user()->isAbleTo('user logs history')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            // Same query as in UserActivityHistory
            $query = UserActivityLog::with('user');
            
            if (Auth::user()->type != 'super admin') {
                $query->whereHas('user', function($q) {
                    $q->where('created_by', Auth::user()->id);
                });
            }

            // Apply same filters
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            if ($request->filled('module')) {
                $query->where('module', $request->module);
            }
            if ($request->filled('activity_type')) {
                $query->where('activity_type', $request->activity_type);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $activities = $query->orderBy('created_at', 'desc')->get();

            $filename = "user_activities_" . date('Y-m-d_H-i-s') . ".csv";
            $headers = [
                'User', 'Email', 'Activity Type', 'Module', 'Description', 'IP Address', 'Country', 'Device', 'Response Time', 'Date'
            ];

            $callback = function() use ($activities) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $headers);
                
                foreach ($activities as $activity) {
                    fputcsv($file, [
                        $activity->user->name ?? '',
                        $activity->user->email ?? '',
                        $activity->activity_type,
                        $activity->module,
                        $activity->description ?? '',
                        $activity->ip_address,
                        $activity->country ?? '',
                        $activity->device_type ?? '',
                        $activity->response_time_ms ?? '',
                        $activity->created_at
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);

        } catch (\Exception $e) {
            Log::error('Activity Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', __('Error exporting activities.'));
        }
    }
}
