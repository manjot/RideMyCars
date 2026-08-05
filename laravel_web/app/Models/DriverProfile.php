<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverProfile extends Model
{
    protected $fillable = [
        'user_id',
        'license_number',
        'hourly_rate',
        'is_available',
        'rating',
        'image_url',
        'bio',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
