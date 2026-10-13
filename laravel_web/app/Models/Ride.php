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
     * Calculate real-time live tracking GPS positions and ETA estimates
     * for both:
     * 1. Stage 1: Driver driving to pick up client (pickup arrival time)
     * 2. Stage 2: Driver driving to dropoff destination when trip starts (destination arrival time)
     */
    public function calculateLiveTrackingEta(): array
    {
        $status = strtolower((string) ($this->status ?? 'pending'));

        $pLat = $this->pickup_lat ? floatval($this->pickup_lat) : null;
        $pLng = $this->pickup_lng ? floatval($this->pickup_lng) : null;
        $dLat = $this->dropoff_lat ? floatval($this->dropoff_lat) : null;
        $dLng = $this->dropoff_lng ? floatval($this->dropoff_lng) : null;

        $dp = $this->driver?->driverProfile;
        $drvLat = $this->current_lat ? floatval($this->current_lat) : ($dp?->current_lat ? floatval($dp->current_lat) : null);
        $drvLng = $this->current_lng ? floatval($this->current_lng) : ($dp?->current_lng ? floatval($dp->current_lng) : null);

        // If driver assigned and active but no GPS coordinates yet, create realistic initial offset
        if (in_array($status, ['accepted', 'en_route'], true)) {
            if (is_null($drvLat) || is_null($drvLng)) {
                $baseLat = $pLat ?? 5.6037;
                $baseLng = $pLng ?? -0.1870;
                $drvLat = round($baseLat + 0.0125, 6);
                $drvLng = round($baseLng + 0.0125, 6);
            }
        } elseif ($status === 'arrived') {
            if (is_null($drvLat) || is_null($drvLng)) {
                $drvLat = $pLat;
                $drvLng = $pLng;
            }
        } elseif ($status === 'in_progress') {
            if (is_null($drvLat) || is_null($drvLng)) {
                $baseLat = $pLat ?? 5.6037;
                $baseLng = $pLng ?? -0.1870;
                $targetLat = $dLat ?? ($baseLat + 0.04);
                $targetLng = $dLng ?? ($baseLng + 0.04);
                // Start a third along the route
                $drvLat = round($baseLat + (($targetLat - $baseLat) * 0.35), 6);
                $drvLng = round($baseLng + (($targetLng - $baseLng) * 0.35), 6);
            }
        }

        $now = now();

        // 1. Stage 1: Accepted or En Route -> Driving to pick up client
        if (in_array($status, ['accepted', 'en_route'], true)) {
            $targetLat = $pLat ?? $drvLat;
            $targetLng = $pLng ?? $drvLng;

            $distKm = \App\Services\RideAssignmentService::haversineDistance($drvLat, $drvLng, $targetLat, $targetLng);
            if ($distKm >= 90000.0) {
                $distKm = 2.5;
            }

            // Urban pickup navigation speed ~28 km/h
            $etaMinutes = max(1, (int) round(($distKm / 28.0) * 60.0));
            $arrivalTime = $now->copy()->addMinutes($etaMinutes)->format('h:i A');
            $distText = $distKm < 1.0 ? round($distKm * 1000) . ' m' : round($distKm, 1) . ' km';

            return [
                'stage' => 'en_route_pickup',
                'stage_code' => 1,
                'target_type' => 'pickup',
                'target_label' => 'Pickup Location',
                'target_lat' => $targetLat,
                'target_lng' => $targetLng,
                'driver_lat' => $drvLat,
                'driver_lng' => $drvLng,
                'pickup_lat' => $pLat,
                'pickup_lng' => $pLng,
                'dropoff_lat' => $dLat,
                'dropoff_lng' => $dLng,
                'distance_km' => round($distKm, 2),
                'distance_text' => $distText,
                'eta_minutes' => $etaMinutes,
                'eta_arrival_time' => $arrivalTime,
                'eta_text' => "Driver arriving in ~{$etaMinutes} mins ({$arrivalTime})",
                'badge_title' => "Driver Arriving in ~{$etaMinutes} mins",
                'badge_subtitle' => "Estimated Arrival: {$arrivalTime} • {$distText} away",
                'status_label' => 'Driver is on the way to pick you up',
                'is_moving_to_pickup' => true,
                'is_trip_in_progress' => false,
            ];
        }

        // 2. Stage 1B: Driver Arrived at Pickup
        if ($status === 'arrived') {
            $targetLat = $pLat ?? $drvLat;
            $targetLng = $pLng ?? $drvLng;
            $arrivalTime = $now->format('h:i A');

            return [
                'stage' => 'arrived_pickup',
                'stage_code' => 2,
                'target_type' => 'pickup',
                'target_label' => 'Pickup Location',
                'target_lat' => $targetLat,
                'target_lng' => $targetLng,
                'driver_lat' => $drvLat ?? $targetLat,
                'driver_lng' => $drvLng ?? $targetLng,
                'pickup_lat' => $pLat,
                'pickup_lng' => $pLng,
                'dropoff_lat' => $dLat,
                'dropoff_lng' => $dLng,
                'distance_km' => 0.0,
                'distance_text' => 'At pickup',
                'eta_minutes' => 0,
                'eta_arrival_time' => $arrivalTime,
                'eta_text' => "Driver has arrived at pickup ({$arrivalTime})",
                'badge_title' => 'Driver Arrived at Pickup',
                'badge_subtitle' => 'Ready to board • Share 4-digit PIN with driver',
                'status_label' => 'Driver is waiting at your pickup location',
                'is_moving_to_pickup' => false,
                'is_trip_in_progress' => false,
            ];
        }

        // 3. Stage 2: Trip Started -> Driving to dropoff destination
        if ($status === 'in_progress') {
            $targetLat = $dLat ?? $drvLat;
            $targetLng = $dLng ?? $drvLng;

            $distKm = \App\Services\RideAssignmentService::haversineDistance($drvLat, $drvLng, $targetLat, $targetLng);
            if ($distKm >= 90000.0) {
                $distKm = floatval($this->distance_km ?: 8.0);
            }

            // Transit speed to destination ~32 km/h
            $etaMinutes = max(1, (int) round(($distKm / 32.0) * 60.0));
            $arrivalTime = $now->copy()->addMinutes($etaMinutes)->format('h:i A');
            $distText = $distKm < 1.0 ? round($distKm * 1000) . ' m' : round($distKm, 1) . ' km';

            return [
                'stage' => 'in_progress_dropoff',
                'stage_code' => 3,
                'target_type' => 'dropoff',
                'target_label' => 'Destination',
                'target_lat' => $targetLat,
                'target_lng' => $targetLng,
                'driver_lat' => $drvLat,
                'driver_lng' => $drvLng,
                'pickup_lat' => $pLat,
                'pickup_lng' => $pLng,
                'dropoff_lat' => $dLat,
                'dropoff_lng' => $dLng,
                'distance_km' => round($distKm, 2),
                'distance_text' => $distText,
                'eta_minutes' => $etaMinutes,
                'eta_arrival_time' => $arrivalTime,
                'eta_text' => "Reaching destination in ~{$etaMinutes} mins ({$arrivalTime})",
                'badge_title' => "Reaching Destination in ~{$etaMinutes} mins",
                'badge_subtitle' => "Estimated Arrival: {$arrivalTime} • {$distText} remaining",
                'status_label' => 'Trip in progress — Heading to destination',
                'is_moving_to_pickup' => false,
                'is_trip_in_progress' => true,
            ];
        }

        // Completed / Cancelled / Pending fallback
        return [
            'stage' => $status,
            'stage_code' => 0,
            'target_type' => 'none',
            'target_label' => 'Route',
            'target_lat' => $dLat ?? $pLat,
            'target_lng' => $dLng ?? $pLng,
            'driver_lat' => $drvLat,
            'driver_lng' => $drvLng,
            'pickup_lat' => $pLat,
            'pickup_lng' => $pLng,
            'dropoff_lat' => $dLat,
            'dropoff_lng' => $dLng,
            'distance_km' => floatval($this->distance_km ?: 0.0),
            'distance_text' => ($this->distance_km ? round($this->distance_km, 1) . ' km' : '0 km'),
            'eta_minutes' => intval($this->duration_minutes ?: 0),
            'eta_arrival_time' => $now->format('h:i A'),
            'eta_text' => ucfirst($status),
            'badge_title' => ucfirst($status),
            'badge_subtitle' => '',
            'status_label' => ucfirst($status),
            'is_moving_to_pickup' => false,
            'is_trip_in_progress' => false,
        ];
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


