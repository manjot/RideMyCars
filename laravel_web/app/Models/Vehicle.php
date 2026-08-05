<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'make',
        'model',
        'year',
        'license_plate',
        'type',
        'daily_rate',
        'is_available',
        'owner_id',
        'image_url',
    ];
}
