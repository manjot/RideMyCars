<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    protected $fillable = [
        'rider_id',
        'driver_id',
        'pickup_location',
        'dropoff_location',
        'fare',
        'status',
        'start_pin',
        'vehicle_type',
        'payment_method',
        'is_for_someone_else',
        'passenger_name',
        'passenger_phone',
        'notes',
        'signature_required',
        'climate_control',
        'discreet_packaging',
        'digital_receipt_code',
        'arrived_at',
        'started_at',
        'completed_at',
        'ride_type',
        'merchant_account',
        'sender_name',
        'sender_address',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'pod_photo_url',
        'pod_signature_url',
        'pod_timestamp',
        'pod_status',
        'current_lat',
        'current_lng',
        'pickup_lat',
        'pickup_lng',
        'dropoff_lat',
        'dropoff_lng',
        'estimated_minutes',
        'is_delayed',
        'pickup_date',
        'pickup_time',
        'total_amount',
        'paid_amount',
        'remaining_balance',
        'payment_status',
        'receipt_id',
        'verification_status',
        'verified_by_driver_id',
        'verified_at',
        'rejection_reason',
        'insurance_accepted',
        'fuel_policy',
        'customer_age',
        'distance_km',
        'duration_minutes',
        'cancellation_reason',
        'vehicle_id',
        'return_date',
        'return_time',
        'different_dropoff',
        'driver_country',
        'driver_email',
        'driver_phone',
        'protection_option',
        'protection_fee',
        'selected_extras',
        'extras_fee',
        'cancellation_fee',
        'penalty_amount',
        'return_fee',
        'eligible_refund_amount',
        'refund_amount',
        'refund_status',
        'refund_reference',
        'refunded_at',
        'driver_search_started_at',
        'payment_held_at',
        'hold_payment_intent_id',
        'hold_authorization_code',
        'accepted_at',
        'backup_chauffeur_enabled',
        'backup_driver_id',
        'backup_status',
        'driver_assignment_type',
        'backup_attempt_count',
        'backup_reserved_at',
        'backup_declined_driver_ids',
        'expires_at',
    ];

    protected $casts = [
        'driver_search_started_at' => 'datetime',
        'payment_held_at' => 'datetime',
        'arrived_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'pod_timestamp' => 'datetime',
        'insurance_accepted' => 'boolean',
        'different_dropoff' => 'boolean',
        'pickup_date' => 'date',
        'return_date' => 'date',
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
        'dropoff_lat' => 'float',
        'dropoff_lng' => 'float',
        'distance_km' => 'float',
        'duration_minutes' => 'integer',
        'protection_fee' => 'float',
        'extras_fee' => 'float',
        'total_amount' => 'float',
        'paid_amount' => 'float',
        'remaining_balance' => 'float',
        'backup_chauffeur_enabled' => 'boolean',
        'backup_reserved_at' => 'datetime',
        'backup_declined_driver_ids' => 'array',
        'expires_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function stops()
    {
        return $this->hasMany(RideStop::class)->orderBy('stop_order');
    }

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function assignments()
    {
        return $this->hasMany(RideAssignment::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function backupDriver()
    {
        return $this->belongsTo(User::class, 'backup_driver_id');
    }

    public function reviews()
    {
        return $this->hasMany(RideReview::class);
    }

    public function riderReview()
    {
        return $this->hasOne(RideReview::class)->where('type', 'rider_to_driver');
    }

    public function driverReview()
    {
        return $this->hasOne(RideReview::class)->where('type', 'driver_to_rider');
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class, 'receipt_id');
    }

    /**
     * Ensure a 4-digit start PIN is always returned for ride verification
     */
    public function getStartPinAttribute($value)
    {
        if (empty($value)) {
            $generated = str_pad((string)(($this->id ? ($this->id % 9000) : rand(1000, 8999)) + 1000), 4, '0', STR_PAD_LEFT);
            if ($this->exists && !empty($this->id)) {
                try {
                    $this->attributes['start_pin'] = $generated;
                    $this->saveQuietly();
                } catch (\Throwable $e) {}
            }
            return $generated;
        }
        return (string) $value;
    }

    /**
     * Check if this ride request has exceeded its driver waiting time window.
     */
    public function isExpired(): bool
    {
        return !empty($this->expires_at) && $this->expires_at->isPast();
    }

    /**
     * Get the remaining waiting time seconds before request expires (0 if expired).
     */
    public function remainingSeconds(): int
    {
        if (empty($this->expires_at)) {
            return 0;
        }
        return (int) max(0, now()->diffInSeconds($this->expires_at, false));
    }

    /**
     * Remaining seconds attribute for API serialization.
     */
    public function getRemainingSecondsAttribute(): int
    {
        return $this->remainingSeconds();
    }

    /**
     * Ensure expires_at column exists in database.
     */
    public static function ensureColumnsExist(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('rides')) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('rides', 'expires_at')) {
                    \Illuminate\Support\Facades\Schema::table('rides', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->timestamp('expires_at')->nullable()->after('status')->index();
                    });
                }
            }
        } catch (\Throwable $e) {}
    }
}


