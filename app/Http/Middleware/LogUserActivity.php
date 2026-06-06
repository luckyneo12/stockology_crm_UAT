<?php

namespace App\Http\Middleware;

use App\Models\UserActivityLog;
use App\Services\IPGeolocationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use WhichBrowser\Parser;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $responseTime = round(($endTime - $startTime) * 1000); // Convert to milliseconds
        
        // Skip logging for certain requests
        if ($this->shouldSkipLogging($request)) {
            return $response;
        }
        
        // Only log if user is authenticated
        if (Auth::check()) {
            $this->logActivity($request, $response, $responseTime);
        }
        
        return $response;
    }

    /**
     * Determine if the request should be skipped from logging
     */
    private function shouldSkipLogging(Request $request): bool
    {
        $skipPatterns = [
            'assets/*',
            'css/*',
            'js/*',
            'images/*',
            'fonts/*',
            'vendor/*',
            'storage/*',
            'telescope/*',
            'horizon/*',
            '_debugbar/*',
            'api/heartbeat',
            'ws/*',
            'livewire/*',
        ];

        $path = $request->path();
        
        foreach ($skipPatterns as $pattern) {
            if ($this->pathMatches($path, $pattern)) {
                return true;
            }
        }

        // Skip AJAX requests that don't modify data
        if ($request->ajax() && in_array($request->method(), ['GET', 'HEAD'])) {
            return true;
        }

        return false;
    }

    /**
     * Check if path matches pattern
     */
    private function pathMatches($path, $pattern): bool
    {
        $pattern = str_replace('*', '.*', preg_quote($pattern, '/'));
        return preg_match("/^{$pattern}$/", $path);
    }

    /**
     * Log the user activity
     */
    private function logActivity(Request $request, $response, $responseTime): void
    {
        try {
            $user = Auth::user();
            $startTime = microtime(true);
            
            // Get device and browser information
            $parser = new Parser($request->userAgent());
            
            // Get location information using IPGeolocationService
            $realIP = IPGeolocationService::getRealIP($request);
            $locationData = IPGeolocationService::getLocationData($realIP);
            
            // Determine activity type and module
            $activityData = $this->determineActivity($request);
            
            // Prepare request data (sanitize sensitive information)
            $requestData = $this->sanitizeRequestData($request);
            
            // Prepare response data (limit size)
            $responseData = null;
            if ($response && method_exists($response, 'getContent')) {
                $content = $response->getContent();
                if (strlen($content) < 1000) { // Only store small responses
                    $responseData = [
                        'status_code' => $response->getStatusCode(),
                        'content' => $content
                    ];
                } else {
                    $responseData = [
                        'status_code' => $response->getStatusCode(),
                        'size' => strlen($content)
                    ];
                }
            }
            
            UserActivityLog::create([
                'user_id' => $user->id,
                'user_type' => get_class($user),
                'activity_type' => $activityData['type'],
                'module' => $activityData['module'],
                'description' => $activityData['description'],
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip_address' => $realIP,
                'user_agent' => $request->userAgent(),
                'browser' => $parser->browser->name ?? null,
                'browser_version' => $parser->browser->version->value ?? null,
                'os' => $parser->os->name ?? null,
                'os_version' => $parser->os->version->value ?? null,
                'device_type' => $this->getDeviceType($request->userAgent()),
                'country' => $locationData['country'],
                'city' => $locationData['city'],
                'latitude' => $locationData['latitude'],
                'longitude' => $locationData['longitude'],
                'request_data' => $requestData,
                'response_data' => $responseData,
                'response_time_ms' => $responseTime,
                'session_id' => session()->getId(),
                'workspace' => getActiveWorkSpace() ?? 0,
                'created_by' => $user->id
            ]);
            
        } catch (\Exception $e) {
            // Don't break the application if logging fails
            Log::error('User activity logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Determine activity type and module from request
     */
    private function determineActivity(Request $request): array
    {
        $path = $request->path();
        $method = $request->method();
        
        // Default values
        $activityType = 'view';
        $module = 'general';
        $description = null;
        
        // Determine activity type from HTTP method
        switch ($method) {
            case 'POST':
                $activityType = 'create';
                break;
            case 'PUT':
            case 'PATCH':
                $activityType = 'edit';
                break;
            case 'DELETE':
                $activityType = 'delete';
                break;
            case 'GET':
                $activityType = 'view';
                break;
        }
        
        // Determine module from URL path
        $pathParts = explode('/', $path);
        if (count($pathParts) > 0) {
            $firstPart = $pathParts[0];
            
            // Map common paths to modules
            $moduleMap = [
                'users' => 'users',
                'leads' => 'leads',
                'projects' => 'projects',
                'tasks' => 'tasks',
                'invoices' => 'invoices',
                'proposals' => 'proposals',
                'contracts' => 'contracts',
                'reports' => 'reports',
                'dashboard' => 'dashboard',
                'profile' => 'profile',
                'settings' => 'settings',
                'companies' => 'companies',
                'customers' => 'customers',
                'vendors' => 'vendors',
                'products' => 'products',
                'services' => 'services',
                'categories' => 'categories',
                'accounts' => 'accounts',
                'bank-accounts' => 'bank_accounts',
                'taxes' => 'taxes',
                'payments' => 'payments',
                'expenses' => 'expenses',
            ];
            
            $module = $moduleMap[$firstPart] ?? 'general';
            
            // Special handling for specific actions
            if (str_contains($path, 'login')) {
                $activityType = 'login';
                $module = 'auth';
            } elseif (str_contains($path, 'logout')) {
                $activityType = 'logout';
                $module = 'auth';
            } elseif (str_contains($path, 'register')) {
                $activityType = 'register';
                $module = 'auth';
            }
        }
        
        // Create description
        $description = $this->generateDescription($request, $activityType, $module);
        
        return [
            'type' => $activityType,
            'module' => $module,
            'description' => $description
        ];
    }

    /**
     * Generate human-readable description
     */
    private function generateDescription(Request $request, string $activityType, string $module): string
    {
        $user = Auth::user();
        $userName = $user ? $user->name : 'Unknown User';
        
        // Get specific resource name if available
        $resourceName = $this->getResourceName($request);
        
        $descriptions = [
            'login' => "{$userName} logged in to the system",
            'logout' => "{$userName} logged out from the system",
            'create' => $resourceName ? 
                "{$userName} created new {$module}: {$resourceName}" : 
                "{$userName} created new {$module}",
            'edit' => $resourceName ? 
                "{$userName} edited {$module}: {$resourceName}" : 
                "{$userName} edited {$module}",
            'delete' => $resourceName ? 
                "{$userName} deleted {$module}: {$resourceName}" : 
                "{$userName} deleted {$module}",
            'view' => $resourceName ? 
                "{$userName} viewed {$module}: {$resourceName}" : 
                "{$userName} viewed {$module}",
        ];
        
        return $descriptions[$activityType] ?? "{$userName} performed {$activityType} on {$module}";
    }

    /**
     * Extract resource name from request
     */
    private function getResourceName(Request $request): ?string
    {
        // Try to get resource name from request parameters
        $id = $request->route('id');
        if ($id) {
            // Try to find the resource and get its name
            try {
                $module = $this->determineActivity($request)['module'];
                $modelClass = $this->getModelForModule($module);
                
                if ($modelClass && class_exists($modelClass)) {
                    $resource = $modelClass::find($id);
                    if ($resource && method_exists($resource, 'getName')) {
                        return $resource->getName();
                    } elseif ($resource && isset($resource->name)) {
                        return $resource->name;
                    } elseif ($resource && isset($resource->title)) {
                        return $resource->title;
                    }
                }
            } catch (\Exception $e) {
                // Ignore errors and continue
            }
        }
        
        // Try to get from form data
        if ($request->has('name')) {
            return $request->get('name');
        }
        if ($request->has('title')) {
            return $request->get('title');
        }
        
        return null;
    }

    /**
     * Get model class for module
     */
    private function getModelForModule(string $module): ?string
    {
        $modelMap = [
            'users' => 'App\Models\User',
            'leads' => 'Workdo\Lead\Entities\Lead',
            'projects' => 'Workdo\Taskly\Entities\Project',
            'tasks' => 'Workdo\Taskly\Entities\Task',
            'invoices' => 'Workdo\Account\Entities\Invoice',
            // Add more mappings as needed
        ];
        
        return $modelMap[$module] ?? null;
    }

    
    /**
     * Get device type from user agent
     */
    private function getDeviceType(string $userAgent): string
    {
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod/', $userAgent)) {
            if (preg_match('/iPad/', $userAgent)) {
                return 'tablet';
            }
            return 'mobile';
        }
        
        return 'desktop';
    }

    /**
     * Sanitize request data to remove sensitive information
     */
    private function sanitizeRequestData(Request $request): ?array
    {
        $data = $request->all();
        
        // Remove sensitive fields
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            'api_key',
            'secret',
            'token',
            'csrf_token',
            '_token',
            'card_number',
            'cvv',
            'expiry'
        ];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***REDACTED***';
            }
        }
        
        // Limit data size
        $serialized = serialize($data);
        if (strlen($serialized) > 5000) {
            return ['data_too_large' => true, 'keys' => array_keys($data)];
        }
        
        return $data;
    }
}
