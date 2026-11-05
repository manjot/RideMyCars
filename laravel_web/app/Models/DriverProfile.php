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
        'is_chauffeur',
        'cash_commission_wallet',
        'commission_threshold_reached_at',
        'commission_warning_notified_at',
        'commission_locked',
        'commission_locked_at',
        'active_chauffeur_online',
        'chauffeur_mode',
        'driver_type',
        'id_card_type',
        'id_card_number',
        'id_card_expiry',
        'id_card_front_image',
        'id_card_back_image',
        'id_card_status',
        'id_card_rejection_reason',
        'fcm_token',
        'device_type',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'is_live' => 'boolean',
        'is_chauffeur' => 'boolean',
        'active_chauffeur_online' => 'boolean',
        'chauffeur_mode' => 'boolean',
        'cash_commission_wallet' => 'float',
        'commission_threshold_reached_at' => 'datetime',
        'commission_warning_notified_at' => 'datetime',
        'commission_locked' => 'boolean',
        'commission_locked_at' => 'datetime',
        'id_card_expiry' => 'date',
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
        'is_chauffeur',
        'is_driver_docs_verified',
        'is_guarantor_verified',
        'professional_badge_label',
        'insurance_certificate_url',
        'fitness_certificate_url',
        'is_certificates_approved',
        'license_front_url',
        'license_back_url',
        'id_card_front_url',
        'id_card_back_url',
        'is_commission_locked',
        'commission_deadline_at',
        'commission_days_remaining',
        'commission_warning_message',
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
     * Determine if driver is qualified / acting as Chauffeur.
     */
    public function getIsChauffeurAttribute(): bool
    {
        if (isset($this->attributes['is_chauffeur']) && (bool)$this->attributes['is_chauffeur']) {
            return true;
        }
        if (isset($this->attributes['driver_type']) && strtolower($this->attributes['driver_type']) === 'chauffeur') {
            return true;
        }
        if ($this->user && in_array(strtolower($this->user->role ?? ''), ['chauffeur', 'private_chauffeur', 'hire_driver'])) {
            return true;
        }
        if (!empty($this->bio) && str_contains(strtolower($this->bio), 'chauffeur')) {
            return true;
        }
        return false;
    }

    /**
     * License front scan URL
     */
    public function getLicenseFrontUrlAttribute(): ?string
    {
        if (empty($this->license_front_image)) return null;
        if (str_starts_with($this->license_front_image, 'http://') || str_starts_with($this->license_front_image, 'https://')) {
            return $this->license_front_image;
        }
        return asset('storage/' . ltrim($this->license_front_image, '/'));
    }

    /**
     * License back scan URL
     */
    public function getLicenseBackUrlAttribute(): ?string
    {
        if (empty($this->license_back_image)) return null;
        if (str_starts_with($this->license_back_image, 'http://') || str_starts_with($this->license_back_image, 'https://')) {
            return $this->license_back_image;
        }
        return asset('storage/' . ltrim($this->license_back_image, '/'));
    }

    /**
     * ID Card front scan URL
     */
    public function getIdCardFrontUrlAttribute(): ?string
    {
        $img = $this->id_card_front_image ?: ($this->ghana_card_front_url ?? null);
        if (empty($img)) return null;
        if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
            return $img;
        }
        return asset('storage/' . ltrim($img, '/'));
    }

    /**
     * ID Card back scan URL
     */
    public function getIdCardBackUrlAttribute(): ?string
    {
        $img = $this->id_card_back_image ?: ($this->ghana_card_back_url ?? null);
        if (empty($img)) return null;
        if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
            return $img;
        }
        return asset('storage/' . ltrim($img, '/'));
    }

    /**
     * Driver Guarantor Verification relationship
     */
    public function guarantor()
    {
        return $this->hasOne(GuarantorVerification::class, 'driver_profile_id')->latestOfMany();
    }

    /**
     * Check if the driver's documents (License, ID card, or Vehicle Certificates) are verified.
     * Only after the Driver's documents are verified should it display "Verified Professional Driver".
     */
    public function getIsDriverDocsVerifiedAttribute(): bool
    {
        $licenseVerified = in_array(strtolower((string) $this->verification_status), ['verified', 'approved']);
        $idVerified = in_array(strtolower((string) $this->id_card_status), ['verified', 'approved']);
        $certsApproved = $this->is_certificates_approved;

        if (!$licenseVerified) {
            return false;
        }

        // If driver uploaded ID card or vehicle certificates, require at least one or both to be approved/verified
        // or if admin explicitly verified the driver's documents (verification_status = verified and id_card_status not pending/rejected)
        if ($certsApproved || $idVerified) {
            return true;
        }

        // If no pending/unverified ID card or certificate rejection exists and license is verified + live/kyc verified
        $idPendingOrRejected = in_array(strtolower((string) $this->id_card_status), ['pending', 'submitted', 'under_review', 'rejected']);
        $insPendingOrRejected = in_array(strtolower((string) $this->vehicle_insurance_status), ['submitted', 'under_review', 'rejected']);
        $fitPendingOrRejected = in_array(strtolower((string) $this->vehicle_fitness_status), ['submitted', 'under_review', 'rejected']);

        if ($idPendingOrRejected || $insPendingOrRejected || $fitPendingOrRejected) {
            return false;
        }

        return !empty($this->license_front_image) || $this->kyc_status === 'verified';
    }

    /**
     * Check if the Chauffeur's Guarantor's documents are verified.
     * Only after the Chauffeur's Guarantor's documents are verified should it display "Verified Professional Chauffeur".
     */
    public function getIsGuarantorVerifiedAttribute(): bool
    {
        $guarantor = $this->relationLoaded('guarantor') ? $this->guarantor : $this->guarantor()->first();
        if (!$guarantor) {
            return false;
        }
        return in_array(strtolower((string) $guarantor->status), ['verified', 'approved']);
    }

    /**
     * Get the exact status label to display on the Driver / Chauffeur profile & dashboard:
     * - Only after Chauffeur's Guarantor's documents are verified -> "Verified Professional Chauffeur"
     * - Only after Driver's documents are verified -> "Verified Professional Driver"
     * - Otherwise -> pending verification label
     */
    public function getProfessionalBadgeLabelAttribute(): string
    {
        if ($this->is_chauffeur) {
            if ($this->is_guarantor_verified) {
                return 'Verified Professional Chauffeur';
            }
            return 'Chauffeur (Guarantor Verification Pending)';
        }

        if ($this->is_driver_docs_verified) {
            return 'Verified Professional Driver';
        }

        return 'Driver (Document Verification Pending)';
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

    /**
     * Ensure commission threshold & lock columns exist on driver_profiles table.
     */
    public static function ensureCommissionColumnsExist(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('driver_profiles')) {
                return;
            }
            \Illuminate\Support\Facades\Schema::table('driver_profiles', function (\Illuminate\Database\Schema\Blueprint $table) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('driver_profiles', 'cash_commission_wallet')) {
                    $table->decimal('cash_commission_wallet', 10, 2)->default(0)->after('is_live');
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('driver_profiles', 'commission_threshold_reached_at')) {
                    $table->timestamp('commission_threshold_reached_at')->nullable()->after('cash_commission_wallet');
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('driver_profiles', 'commission_warning_notified_at')) {
                    $table->timestamp('commission_warning_notified_at')->nullable()->after('commission_threshold_reached_at');
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('driver_profiles', 'commission_locked')) {
                    $table->boolean('commission_locked')->default(false)->after('commission_warning_notified_at');
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('driver_profiles', 'commission_locked_at')) {
                    $table->timestamp('commission_locked_at')->nullable()->after('commission_locked');
                }
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('DriverProfile::ensureCommissionColumnsExist warning: ' . $e->getMessage());
        }
    }

    public function getIsCommissionLockedAttribute(): bool
    {
        $commBalance = round((float) ($this->cash_commission_wallet ?? 0));
        if ($commBalance < 100) {
            return false;
        }
        if (!empty($this->attributes['commission_locked']) && (bool) $this->attributes['commission_locked']) {
            return true;
        }
        if (!empty($this->attributes['commission_threshold_reached_at'])) {
            try {
                $reachedAt = \Carbon\Carbon::parse($this->attributes['commission_threshold_reached_at']);
                if ($reachedAt->lte(now()->subDays(7))) {
                    return true;
                }
            } catch (\Throwable $e) {}
        }
        return false;
    }

    public function getCommissionDeadlineAtAttribute(): ?string
    {
        $commBalance = round((float) ($this->cash_commission_wallet ?? 0));
        if ($commBalance < 100 || empty($this->attributes['commission_threshold_reached_at'])) {
            return null;
        }
        try {
            return \Carbon\Carbon::parse($this->attributes['commission_threshold_reached_at'])->addDays(7)->toIso8601String();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getCommissionDaysRemainingAttribute(): ?int
    {
        $commBalance = round((float) ($this->cash_commission_wallet ?? 0));
        if ($commBalance < 100) {
            return null;
        }
        if (empty($this->attributes['commission_threshold_reached_at'])) {
            return 7;
        }
        try {
            $deadline = \Carbon\Carbon::parse($this->attributes['commission_threshold_reached_at'])->addDays(7);
            if (now()->gte($deadline)) {
                return 0;
            }
            return (int) max(0, ceil(now()->diffInHours($deadline, false) / 24));
        } catch (\Throwable $e) {
            return 7;
        }
    }

    public function getCommissionWarningMessageAttribute(): ?string
    {
        $commBalance = round((float) ($this->cash_commission_wallet ?? 0));
        if ($commBalance >= 100) {
            return 'Kindly top up your wallet balance within 7 days for Commission Deduction or risk deactivation.';
        }
        return null;
    }

    /**
     * Synchronize and enforce the driver's Cash Commission Wallet threshold (100),
     * 7-day payment deadline, automatic Driver Switch lock/off, and automatic unlock/on upon payment.
     */
    public static function syncCommissionStatus(DriverProfile $profile, bool $fromPayment = false): array
    {
        self::ensureCommissionColumnsExist();

        $commBalance = round((float) ($profile->cash_commission_wallet ?? 0));
        $warningText = 'Kindly top up your wallet balance within 7 days for Commission Deduction or risk deactivation.';

        if ($commBalance >= 100) {
            $dirty = false;

            if (empty($profile->commission_threshold_reached_at)) {
                $profile->commission_threshold_reached_at = now();
                $dirty = true;
            }

            if (empty($profile->commission_warning_notified_at)) {
                $profile->commission_warning_notified_at = now();
                $dirty = true;

                try {
                    \App\Services\NotificationService::send(
                        $profile->user_id,
                        'commission_threshold_warning',
                        'Cash Commission Payment Due',
                        $warningText,
                        null,
                        '/driver/earnings',
                        [
                            'commission_wallet_balance' => (int) $commBalance,
                            'deadline_days' => 7,
                        ]
                    );
                } catch (\Throwable $e) {}
            }

            $reachedAt = $profile->commission_threshold_reached_at
                ? \Carbon\Carbon::parse($profile->commission_threshold_reached_at)
                : now();
            $deadlineExceeded = $reachedAt->lte(now()->subDays(7)) || (bool) $profile->commission_locked;

            if ($deadlineExceeded) {
                $justLocked = !(bool) $profile->commission_locked;

                if ($profile->is_available || $profile->active_chauffeur_online || !$profile->commission_locked) {
                    $profile->is_available = false;
                    $profile->active_chauffeur_online = false;
                    $profile->commission_locked = true;
                    if (empty($profile->commission_locked_at)) {
                        $profile->commission_locked_at = now();
                    }
                    $dirty = true;
                }

                if ($justLocked) {
                    try {
                        \App\Services\NotificationService::send(
                            $profile->user_id,
                            'commission_switch_locked',
                            'Driver Switch Turned Off & Locked',
                            'Your Driver Switch has been turned off due to unpaid commission exceeding the 7-day payment deadline. Kindly pay your outstanding commission to unlock your Driver Switch.',
                            null,
                            '/driver/earnings',
                            [
                                'commission_wallet_balance' => (int) $commBalance,
                                'is_commission_locked' => true,
                                'is_available' => false,
                            ]
                        );
                    } catch (\Throwable $e) {}
                }
            }

            if ($dirty) {
                $profile->save();
            }

            return [
                'threshold_reached' => true,
                'is_commission_locked' => (bool) $profile->commission_locked,
                'is_available' => (bool) $profile->is_available,
                'warning_message' => $warningText,
            ];
        }

        // Commission balance is < 100 (paid or below threshold)
        $wasOverdueOrTracked = (bool) $profile->commission_locked
            || !empty($profile->commission_threshold_reached_at)
            || !empty($profile->commission_warning_notified_at)
            || !empty($profile->commission_locked_at);

        if ($wasOverdueOrTracked || $fromPayment) {
            $wasLockedOrThreshold = $wasOverdueOrTracked;
            $profile->commission_threshold_reached_at = null;
            $profile->commission_warning_notified_at = null;
            $profile->commission_locked = false;
            $profile->commission_locked_at = null;

            if ($fromPayment || $wasLockedOrThreshold) {
                $profile->is_available = true;
                $profile->is_live = true;
            }
            $profile->save();

            if ($fromPayment || $wasLockedOrThreshold) {
                try {
                    \App\Services\NotificationService::send(
                        $profile->user_id,
                        'commission_switch_unlocked',
                        'Driver Switch Turned On & Unlocked',
                        'Your Driver Switch has been turned on and unlocked. You can now receive ride requests.',
                        null,
                        '/driver/dashboard',
                        [
                            'commission_wallet_balance' => (int) $commBalance,
                            'is_commission_locked' => false,
                            'is_available' => true,
                        ]
                    );
                } catch (\Throwable $e) {}
            }
        }

        return [
            'threshold_reached' => false,
            'is_commission_locked' => false,
            'is_available' => (bool) $profile->is_available,
            'warning_message' => null,
        ];
    }

    /**
     * Sweep all drivers with cash_commission_wallet >= 100 and enforce threshold/7-day lock.
     */
    public static function enforceAllOverdueCommissionDeadlines(): void
    {
        self::ensureCommissionColumnsExist();
        try {
            $profiles = self::where('cash_commission_wallet', '>=', 100)->get();
            foreach ($profiles as $profile) {
                self::syncCommissionStatus($profile, false);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('enforceAllOverdueCommissionDeadlines warning: ' . $e->getMessage());
        }
    }
}
