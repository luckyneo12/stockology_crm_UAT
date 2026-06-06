<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IPGeolocationService
{
    /**
     * Get location data for IP address
     */
    public static function getLocationData(string $ip): array
    {
        // Default location data
        $locationData = [
            'country' => null,
            'city' => null,
            'latitude' => null,
            'longitude' => null,
            'timezone' => null,
            'isp' => null,
            'asn' => null
        ];
        
        // Skip for localhost
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return $locationData;
        }
        
        // Try to get from cache first
        $cacheKey = "ip_location_{$ip}";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // Try different geolocation services
        $locationData = self::getFromIPApi($ip);
        
        if (empty($locationData['country'])) {
            $locationData = self::getFromIPInfo($ip);
        }
        
        if (empty($locationData['country'])) {
            $locationData = self::getFromFreeGeoIP($ip);
        }
        
        // Cache the result for 24 hours
        if (!empty($locationData['country'])) {
            Cache::put($cacheKey, $locationData, 24 * 60 * 60);
        }
        
        return $locationData;
    }
    
    /**
     * Get location data from ip-api.com
     */
    private static function getFromIPApi(string $ip): array
    {
        try {
            $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'status,message,country,regionName,city,lat,lon,timezone,isp,as,query'
            ]);
            
            if ($response->successful() && $response->json('status') === 'success') {
                $data = $response->json();
                
                return [
                    'country' => $data['country'] ?? null,
                    'city' => $data['city'] ?? null,
                    'latitude' => $data['lat'] ?? null,
                    'longitude' => $data['lon'] ?? null,
                    'timezone' => $data['timezone'] ?? null,
                    'isp' => $data['isp'] ?? null,
                    'asn' => $data['as'] ?? null
                ];
            }
        } catch (\Exception $e) {
            Log::warning('IP API service failed: ' . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * Get location data from ipinfo.io
     */
    private static function getFromIPInfo(string $ip): array
    {
        try {
            // Note: This requires an API token for production use
            $response = Http::timeout(5)->get("https://ipinfo.io/{$ip}/json");
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Parse coordinates if available
                $coordinates = $data['loc'] ?? null;
                $latitude = null;
                $longitude = null;
                
                if ($coordinates) {
                    [$latitude, $longitude] = explode(',', $coordinates);
                }
                
                return [
                    'country' => $data['country'] ?? null,
                    'city' => $data['city'] ?? null,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'timezone' => $data['timezone'] ?? null,
                    'isp' => $data['org'] ?? null,
                    'asn' => $data['org'] ?? null
                ];
            }
        } catch (\Exception $e) {
            Log::warning('IPInfo service failed: ' . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * Get location data from freegeoip.app
     */
    private static function getFromFreeGeoIP(string $ip): array
    {
        try {
            $response = Http::timeout(5)->get("https://freegeoip.app/json/{$ip}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'country' => $data['country_name'] ?? $data['country_code'] ?? null,
                    'city' => $data['city'] ?? null,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'timezone' => $data['time_zone'] ?? null,
                    'isp' => null,
                    'asn' => null
                ];
            }
        } catch (\Exception $e) {
            Log::warning('FreeGeoIP service failed: ' . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * Validate IP address
     */
    public static function isValidIP(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }
    
    /**
     * Get IP from request (handles proxy cases)
     */
    public static function getRealIP($request): string
    {
        $ip = $request->ip();
        
        // Check for proxy headers
        $proxyHeaders = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($proxyHeaders as $header) {
            if ($request->server($header)) {
                $ips = explode(',', $request->server($header));
                $ip = trim($ips[0]);
                
                if (self::isValidIP($ip)) {
                    break;
                }
            }
        }
        
        return $ip;
    }
    
    /**
     * Check if IP is from private network
     */
    public static function isPrivateIP(string $ip): bool
    {
        $privateRanges = [
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
            '127.0.0.0/8',
            '::1/128',
            'fc00::/7',
            'fe80::/10'
        ];
        
        foreach ($privateRanges as $range) {
            if ($this->ip_in_range($ip, $range)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if IP is in given range
     */
    private static function ip_in_range(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }
        
        [$subnet, $bits] = explode('/', $range);
        
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return self::ipv6_in_range($ip, $subnet, $bits);
        } else {
            return self::ipv4_in_range($ip, $subnet, $bits);
        }
    }
    
    /**
     * Check IPv4 in range
     */
    private static function ipv4_in_range(string $ip, string $subnet, int $bits): bool
    {
        $ip_long = ip2long($ip);
        $subnet_long = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        
        return ($ip_long & $mask) === ($subnet_long & $mask);
    }
    
    /**
     * Check IPv6 in range
     */
    private static function ipv6_in_range(string $ip, string $subnet, int $bits): bool
    {
        // Simplified IPv6 range check
        $ip_bin = inet_pton($ip);
        $subnet_bin = inet_pton($subnet);
        
        if ($ip_bin === false || $subnet_bin === false) {
            return false;
        }
        
        $mask = str_repeat("\xFF", $bits >> 3);
        if ($bits % 8) {
            $mask .= chr(0xFF << (8 - ($bits % 8)));
        }
        
        return ($ip_bin & $mask) === ($subnet_bin & $mask);
    }
    
    /**
     * Get ISP information
     */
    public static function getISPInfo(string $ip): array
    {
        $locationData = self::getLocationData($ip);
        
        return [
            'isp' => $locationData['isp'],
            'asn' => $locationData['asn'],
            'is_proxy' => self::isProxyIP($ip),
            'is_datacenter' => self::isDatacenterIP($ip)
        ];
    }
    
    /**
     * Check if IP is likely a proxy/VPN
     */
    public static function isProxyIP(string $ip): bool
    {
        // This is a simplified check - in production you might want to use
        // a dedicated proxy detection service
        
        $locationData = self::getLocationData($ip);
        
        // Common hosting/cloud provider patterns
        $hostingProviders = [
            'Amazon', 'Google', 'Microsoft', 'DigitalOcean', 
            'Vultr', 'Linode', 'OVH', 'Cloudflare'
        ];
        
        if ($locationData['isp']) {
            foreach ($hostingProviders as $provider) {
                if (stripos($locationData['isp'], $provider) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check if IP is from a datacenter
     */
    public static function isDatacenterIP(string $ip): bool
    {
        return self::isProxyIP($ip);
    }
}
