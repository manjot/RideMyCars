<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalInspection extends Model
{
    protected $fillable = [
        'driver_booking_id',
        'vehicle_id',
        'inspection_type',
        'front_photo_url',
        'back_photo_url',
        'left_photo_url',
        'right_photo_url',
        'dashboard_photo_url',
        'fuel_gauge_photo_url',
        'odometer_reading',
        'fuel_level',
        'notes',
        'inspected_by_user_id',
        'inspected_at',
    ];

    protected $casts = [
        'inspected_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(DriverBooking::class, 'driver_booking_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspected_by_user_id');
    }
}
