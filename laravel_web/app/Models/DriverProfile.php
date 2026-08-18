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
        'photo_formality_status',
        'license_verified_at',
        'verification_provider',
        'background_check_status',
        'background_check_provider',
        'background_check_id',
        'background_checked_at',
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

    /**
     * Check if driver satisfies all required verification criteria:
     * 1. Driver License Verified
     * 2. Background Check Clear/Verified
     * 3. Formal Profile Photo Verified
     */
    public function getIsFullyVerifiedAttribute(): bool
    {
        $licenseOk = ($this->verification_status === 'verified');
        $backgroundOk = in_array($this->background_check_status, ['clear', 'verified', 'approved']);
        $photoOk = ($this->photo_formality_status === 'verified');

        return $licenseOk && $backgroundOk && $photoOk;
    }

    /**
     * Check if driver has any booking conflict for the given date, start time, and duration.
     */
    public function hasBookingConflict(string $startDate, string $startTime, string $durationType, int $durationCount, ?int $ignoreBookingId = null): bool
    {
        if (!$this->is_available) {
            return true;
        }

        try {
            $reqStart = \Carbon\Carbon::parse("{$startDate} {$startTime}");
        } catch (\Exception $e) {
            return false;
        }

        $reqEnd = (clone $reqStart);
        if ($durationType === 'weekly') {
            $reqEnd->addWeeks(max(1, $durationCount));
        } elseif ($durationType === 'daily') {
            $reqEnd->addDays(max(1, $durationCount));
        } else {
            // hourly
            $reqEnd->addHours(max(1, $durationCount));
        }

        $existingBookings = DriverBooking::where('driver_profile_id', $this->id)
            ->whereIn('booking_status', ['pending', 'accepted', 'in_progress'])
            ->when($ignoreBookingId, function ($q) use ($ignoreBookingId) {
                $q->where('id', '!=', $ignoreBookingId);
            })
            ->get();

        foreach ($existingBookings as $b) {
            try {
                $bStart = \Carbon\Carbon::parse("{$b->start_date} {$b->start_time}");
            } catch (\Exception $e) {
                continue;
            }

            $bEnd = (clone $bStart);
            if ($b->duration_type === 'weekly') {
                $bEnd->addWeeks(max(1, (int) $b->duration_count));
            } elseif ($b->duration_type === 'daily') {
                $bEnd->addDays(max(1, (int) $b->duration_count));
            } else {
                $bEnd->addHours(max(1, (int) $b->duration_count));
            }

            // Overlap condition: reqStart < bEnd AND reqEnd > bStart
            if ($reqStart->lt($bEnd) && $reqEnd->gt($bStart)) {
                return true;
            }
        }

        return false;
    }
}
