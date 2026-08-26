<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideStop extends Model
{
    protected $fillable = [
        'ride_id',
        'stop_order',
        'location',
        'lat',
        'lng',
    ];

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }
}
