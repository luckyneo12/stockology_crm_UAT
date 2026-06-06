<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_type',
        'activity_type',
        'module',
        'description',
        'url',
        'method',
        'ip_address',
        'user_agent',
        'browser',
        'browser_version',
        'os',
        'os_version',
        'device_type',
        'country',
        'city',
        'latitude',
        'longitude',
        'request_data',
        'response_data',
        'response_time_ms',
        'session_id',
        'workspace',
        'created_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user that owns the activity log.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    
    /**
     * Scope to get activities by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get activities by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get activities by module
     */
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope to get activities by activity type
     */
    public function scopeByActivityType($query, $activityType)
    {
        return $query->where('activity_type', $activityType);
    }

    /**
     * Scope to get activities by IP address
     */
    public function scopeByIP($query, $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * Get location details as formatted string
     */
    public function getLocationAttribute()
    {
        $location = [];
        if ($this->city) $location[] = $this->city;
        if ($this->country) $location[] = $this->country;
        return implode(', ', $location) ?: 'Unknown';
    }

    /**
     * Get device details as formatted string
     */
    public function getDeviceDetailsAttribute()
    {
        $details = [];
        if ($this->browser) $details[] = $this->browser . ' ' . $this->browser_version;
        if ($this->os) $details[] = $this->os . ' ' . $this->os_version;
        if ($this->device_type) $details[] = '(' . $this->device_type . ')';
        return implode(' ', $details);
    }
}
