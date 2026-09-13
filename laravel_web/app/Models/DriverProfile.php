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
        'current_lat',
        'current_lng',
        'last_location_update',
        'vehicle_insurance_image',
        'vehicle_insurance_status',
        'vehicle_insurance_expiry',
        'vehicle_insurance_rejection_reason',
        'vehicle_fitness_image',
        'vehicle_fitness_status',
        'vehicle_fitness_expiry',
        'vehicle_fitness_rejection_reason',
        'is_live',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'is_live' => 'boolean',
        'vehicle_insurance_expiry' => 'date',
        'vehicle_fitness_expiry' => 'date',
        'last_location_update' => 'datetime',
        'current_lat' => 'float',
        'current_lng' => 'float',
    ];

    protected $appends = [
        'photo_url',
        'masked_license',
        'is_verified',
        'insurance_certificate_url',
        'fitness_certificate_url',
        'is_certificates_approved',
    ];

    /**
     * Get full driver photo URL or an avatar placeholder if no photo was uploaded.
     */
    public function getPhotoUrlAttribute(): string
    {
        if (!empty($this->image_url)) {
            if (str_starts_with($this->image_url, 'http://') || str_starts_with($this->image_url, 'https://')) {
                return $this->image_url;
            }
            return asset('storage/' . ltrim($this->image_url, '/'));
        }

        $name = $this->user ? $this->user->name : 'Driver';
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=0F172A&color=FFFFFF&size=256&bold=true';
    }

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
     * Get URL for Vehicle Insurance certificate picture scan.
     */
    public function getInsuranceCertificateUrlAttribute(): ?string
    {
        if (empty($this->vehicle_insurance_image)) {
            return null;
        }
        if (str_starts_with($this->vehicle_insurance_image, 'http://') || str_starts_with($this->vehicle_insurance_image, 'https://')) {
            return $this->vehicle_insurance_image;
        }
        return asset('storage/' . ltrim($this->vehicle_insurance_image, '/'));
    }

    /**
     * Get URL for Vehicle Fitness (Roadworthy) certificate picture scan.
     */
    public function getFitnessCertificateUrlAttribute(): ?string
    {
        if (empty($this->vehicle_fitness_image)) {
            return null;
        }
        if (str_starts_with($this->vehicle_fitness_image, 'http://') || str_starts_with($this->vehicle_fitness_image, 'https://')) {
            return $this->vehicle_fitness_image;
        }
        return asset('storage/' . ltrim($this->vehicle_fitness_image, '/'));
    }

    /**
     * Check if both Vehicle Insurance and Fitness certificates are approved.
     */
    public function getIsCertificatesApprovedAttribute(): bool
    {
        return $this->vehicle_insurance_status === 'approved' && $this->vehicle_fitness_status === 'approved';
    }

    /**
     * Check if driver is eligible to be made live by admin.
     */
    public function getCanGoLiveAttribute(): bool
    {
        return $this->is_certificates_approved;
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
