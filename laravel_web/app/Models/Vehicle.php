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
        'assigned_driver_id',
        'security_deposit_amount',
        'daily_mileage_limit',
        'overage_fee_per_km',
        'transmission',
        'fuel_type',
        'seats',
        'luggage',
        'doors',
        'mileage_policy',
        'fuel_policy',
        'min_driver_age',
        'category',
        'insurance_policy_number',
        'insurance_expiry',
        'roadworthiness_expiry',
        'approval_status',
        'approval_notes',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'daily_rate' => 'float',
        'security_deposit_amount' => 'float',
        'seats' => 'integer',
        'luggage' => 'integer',
        'doors' => 'integer',
        'min_driver_age' => 'integer',
    ];

    protected $appends = ['image_src'];

    public function getImageSrcAttribute(): string
    {
        if (empty($this->image_url)) {
            return '/images/hero-rent.png';
        }
        if (str_starts_with($this->image_url, 'http://') || str_starts_with($this->image_url, 'https://') || str_starts_with($this->image_url, '/images/')) {
            return $this->image_url;
        }
        if (str_starts_with($this->image_url, '/storage/')) {
            return $this->image_url;
        }
        return \Illuminate\Support\Facades\Storage::url($this->image_url);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function assignedDriver()
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }
}
