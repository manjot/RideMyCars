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
        'accepted_at',
    ];

    protected $casts = [
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
}

