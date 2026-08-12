<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverProfile extends Model
{
    protected $fillable = [
        'user_id',
        'license_number',
        'hourly_rate',
        'daily_rate',
        'weekly_rate',
        'experience_years',
        'country',
        'service_area',
        'is_available',
        'rating',
        'total_trips',
        'kyc_status',
        'image_url',
        'bio',
        'license_country',
        'license_expiry',
        'license_front_image',
        'license_back_image',
        'verification_status',
        'verification_notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviews()
    {
        return $this->hasMany(DriverReview::class, 'driver_profile_id');
    }

    public function bookings()
    {
        return $this->hasMany(DriverBooking::class, 'driver_profile_id');
    }

    /**
     * Get masked license number for public display.
     */
    public function getMaskedLicenseAttribute(): string
    {
        if (empty($this->license_number)) {
            return 'NOT SUBMITTED';
        }
        $len = strlen($this->license_number);
        if ($len <= 4) return '****';
        return substr($this->license_number, 0, 2) . str_repeat('*', $len - 4) . substr($this->license_number, -2);
    }

    /**
     * Is driver verified?
     */
    public function getIsVerifiedAttribute(): bool
    {
        return $this->verification_status === 'verified';
    }
}
