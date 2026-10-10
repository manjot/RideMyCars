<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_code',
        'customer_id',
        'courier_id',
        'courier_profile_id',
        'pickup_location',
        'pickup_lat',
        'pickup_lng',
        'dropoff_location',
        'dropoff_lat',
        'dropoff_lng',
        'delivery_type',
        'schedule_mode',
        'pickup_date',
        'pickup_time',
        'sender_name',
        'sender_phone',
        'sender_address',
        'recipient_name',
        'recipient_phone',
        'recipient_address',
        'delivery_instructions',
        'package_category',
        'has_prescription',
        'package_description',
        'package_size',
        'package_weight_kg',
        'quantity',
        'declared_value',
        'special_handling',
        'delivery_otp',
        'delivery_status',
        'subtotal',
        'service_fee',
        'tax',
        'total_price',
        'currency',
        'payment_method',
        'payment_status',
        'receipt_id',
        'verification_status',
        'verified_by_driver_id',
        'verified_at',
        'rejection_reason',
        'pod_photo_url',
        'pod_signature_url',
        'pod_timestamp',
        'pod_status',
        'prohibited_items_acknowledged',
        'inspection_status',
        'inspection_notes',
        'inspection_photo_url',
        'arrived_at_pickup_at',
        'picked_up_at',
        'arrived_at_destination_at',
        'delivered_at',
        'cancellation_fee',
        'penalty_amount',
        'return_fee',
        'eligible_refund_amount',
        'refund_amount',
        'refund_status',
        'refund_reference',
        'refunded_at',
        'accepted_at',
        'expires_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
        'dropoff_lat' => 'float',
        'dropoff_lng' => 'float',
        'package_weight_kg' => 'float',
        'declared_value' => 'float',
        'subtotal' => 'float',
        'service_fee' => 'float',
        'tax' => 'float',
        'total_price' => 'float',
        'special_handling' => 'array',
        'has_prescription' => 'boolean',
        'prohibited_items_acknowledged' => 'boolean',
        'pickup_date' => 'date',
        'pod_timestamp' => 'datetime',
        'arrived_at_pickup_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'arrived_at_destination_at' => 'datetime',
        'delivered_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function courierProfile()
    {
        return $this->belongsTo(DriverProfile::class, 'courier_profile_id');
    }

    public function assignments()
    {
        return $this->hasMany(RideAssignment::class, 'package_delivery_id');
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class, 'receipt_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(PackagePrescription::class, 'package_delivery_id');
    }

    /**
     * Check if this delivery request has exceeded its waiting time window.
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
     * Ensure expires_at and cancellation_reason columns exist in database.
     */
    public static function ensureColumnsExist(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('package_deliveries')) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('package_deliveries', 'expires_at')) {
                    \Illuminate\Support\Facades\Schema::table('package_deliveries', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->timestamp('expires_at')->nullable()->after('delivery_status')->index();
                    });
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('package_deliveries', 'cancellation_reason')) {
                    \Illuminate\Support\Facades\Schema::table('package_deliveries', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->text('cancellation_reason')->nullable()->after('delivery_status');
                    });
                }
            }
        } catch (\Throwable $e) {}
    }
}

