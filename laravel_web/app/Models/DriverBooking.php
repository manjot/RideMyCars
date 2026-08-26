<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverBooking extends Model
{
    protected $fillable = [
        'vehicle_id',
        'booking_code',
        'client_id',
        'driver_id',
        'driver_profile_id',
        'service_category',
        'service_type',
        'country',
        'car_type',
        'car_make_model',
        'manufacturing_year',
        'registration_number',
        'transmission',
        'preferred_gender',
        'preferred_language',
        'commercial_service_type',
        'cargo_details',
        'pickup_location',
        'dropoff_location',
        'additional_stops',
        'start_date',
        'start_time',
        'duration_type',
        'duration_count',
        'hourly_rate',
        'daily_rate',
        'weekly_rate',
        'subtotal',
        'service_fee',
        'tax',
        'total_price',
        'currency',
        'payment_method',
        'payment_status',
        'booking_status',
        'notes',
        'vehicle_source',
        'end_date',
        'end_time',
        'buffer_end_time',
        'escrow_deposit_amount',
        'escrow_status',
        'escrow_damage_claim_notes',
        'start_odometer',
        'end_odometer',
        'overage_mileage_fee',
        'pickup_lat',
        'pickup_lng',
        'dropoff_lat',
        'dropoff_lng',
        'actual_distance_km',
        'actual_duration_minutes',
        'final_fare',
        'arrived_at',
        'started_at',
        'completed_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'arrived_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'start_date' => 'date',
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
        'dropoff_lat' => 'float',
        'dropoff_lng' => 'float',
        'actual_distance_km' => 'float',
        'actual_duration_minutes' => 'integer',
        'final_fare' => 'float',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function driverProfile()
    {
        return $this->belongsTo(DriverProfile::class, 'driver_profile_id');
    }

    public function review()
    {
        return $this->hasOne(DriverReview::class, 'driver_booking_id');
    }

    public function paymentTransaction()
    {
        return $this->hasOne(PaymentTransaction::class, 'driver_booking_id');
    }
}
