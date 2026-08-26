<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RideAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ride_id',
        'driver_booking_id',
        'package_delivery_id',
        'driver_id',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function driverBooking()
    {
        return $this->belongsTo(DriverBooking::class, 'driver_booking_id');
    }

    public function packageDelivery()
    {
        return $this->belongsTo(PackageDelivery::class, 'package_delivery_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
