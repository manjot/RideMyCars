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
    ];
}
