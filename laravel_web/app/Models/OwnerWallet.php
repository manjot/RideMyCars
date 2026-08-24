<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerWallet extends Model
{
    protected $fillable = [
        'user_id',
        'ride_hailing_balance',
        'driver_hiring_balance',
        'vehicle_rental_balance',
        'pending_payout_balance',
        'total_withdrawn',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
