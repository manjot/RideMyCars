<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'transaction_ref',
        'driver_booking_id',
        'ride_id',
        'user_id',
        'country',
        'currency',
        'amount',
        'payment_method',
        'provider',
        'status',
        'gateway_response',
    ];

    protected $casts = [
        'gateway_response' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driverBooking()
    {
        return $this->belongsTo(DriverBooking::class, 'driver_booking_id');
    }

    public function ride()
    {
        return $this->belongsTo(Ride::class, 'ride_id');
    }
}
