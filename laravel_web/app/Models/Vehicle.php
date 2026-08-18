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
}
