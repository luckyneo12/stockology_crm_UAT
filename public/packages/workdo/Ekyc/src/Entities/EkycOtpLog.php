<?php

namespace Workdo\Ekyc\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Crypt;

class EkycOtpLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'identifier',
        'identifier_type',
        'otp_code',
        'expires_at',
        'verified_at',
        'attempts',
        'max_attempts',
        'is_testing_mode',
        'provider',
        'provider_response',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_testing_mode' => 'boolean',
        'provider_response' => 'array',
    ];

    protected $hidden = [
        'otp_code',
    ];

    /**
     * Get the submission that owns the OTP log
     */
    public function submission()
    {
        return $this->belongsTo(EkycSubmission::class, 'submission_id');
    }

    /**
     * Set OTP code (encrypted)
     */
    public function setOtpCodeAttribute($value)
    {
        $this->attributes['otp_code'] = Crypt::encryptString($value);
    }

    /**
     * Get OTP code (decrypted)
     */
    public function getOtpCodeAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired()
    {
        return now()->greaterThan($this->expires_at);
    }

    /**
     * Check if OTP is verified
     */
    public function isVerified()
    {
        return $this->verified_at !== null;
    }

    /**
     * Check if max attempts reached
     */
    public function hasReachedMaxAttempts()
    {
        return $this->attempts >= $this->max_attempts;
    }

    /**
     * Increment verification attempts
     */
    public function incrementAttempts()
    {
        $this->increment('attempts');
    }

    /**
     * Mark as verified
     */
    public function markAsVerified()
    {
        $this->update(['verified_at' => now()]);
    }

    /**
     * Scope for active (not expired, not verified) OTPs
     */
    public function scopeActive($query)
    {
        return $query->whereNull('verified_at')
                    ->where('expires_at', '>', now());
    }

    /**
     * Scope for specific identifier
     */
    public function scopeForIdentifier($query, $identifier, $type)
    {
        return $query->where('identifier', $identifier)
                    ->where('identifier_type', $type);
    }

    /**
     * Get the latest active OTP for an identifier
     */
    public static function getLatestActive($identifier, $type)
    {
        return self::forIdentifier($identifier, $type)
                  ->active()
                  ->latest()
                  ->first();
    }
}
