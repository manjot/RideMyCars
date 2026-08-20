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
        'notes',
        'signature_required',
        'climate_control',
        'discreet_packaging',
        'digital_receipt_code',
        'arrived_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'arrived_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

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

