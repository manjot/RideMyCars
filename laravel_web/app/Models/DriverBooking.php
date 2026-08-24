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
        'country',
        'car_type',
        'car_make_model',
        'manufacturing_year',
        'registration_number',
        'transmission',
        'commercial_service_type',
        'cargo_details',
        'pickup_location',
        'dropoff_location',
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
