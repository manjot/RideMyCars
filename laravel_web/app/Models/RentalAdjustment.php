<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalAdjustment extends Model
{
    protected $fillable = [
        'driver_booking_id',
        'admin_user_id',
        'original_end_date',
        'new_end_date',
        'original_total_price',
        'new_total_price',
        'original_platform_fee',
        'new_platform_fee',
        'original_owner_payout',
        'new_owner_payout',
        'adjustment_reason',
    ];

    protected $casts = [
        'original_end_date' => 'date',
        'new_end_date' => 'date',
    ];

    public function booking()
    {
        return $this->belongsTo(DriverBooking::class, 'driver_booking_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
