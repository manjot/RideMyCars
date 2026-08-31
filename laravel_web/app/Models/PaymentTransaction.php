<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'transaction_ref',
        'driver_booking_id',
        'package_delivery_id',
        'ride_id',
        'user_id',
        'vehicle_id',
        'country',
        'currency',
        'amount',
        'gross_amount',
        'platform_fee',
        'maintenance_fee',
        'gateway_fee',
        'owner_share',
        'net_payout',
        'payment_method',
        'provider',
        'status',
        'payout_status',
        'escrow_status',
        'escrow_amount',
        'escrow_refunded_amount',
        'escrow_deducted_amount',
        'gateway_fee_absorber',
        'payout_failed_reason',
        'payout_retry_count',
        'stripe_payment_intent_id',
        'stripe_client_secret',
        'paid_at',
        'service_vertical',
        'gateway_response',
        'cancellation_fee',
        'penalty_amount',
        'return_fee',
        'eligible_refund_amount',
        'refund_amount',
        'refund_status',
        'refund_reference',
        'refunded_at',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driverBooking()
    {
        return $this->belongsTo(DriverBooking::class, 'driver_booking_id');
    }

    public function packageDelivery()
    {
        return $this->belongsTo(PackageDelivery::class, 'package_delivery_id');
    }

    public function ride()
    {
        return $this->belongsTo(Ride::class, 'ride_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
