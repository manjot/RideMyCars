<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RideCategory extends Model
{
    protected $table = 'ride_categories';

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'capacity',
        'base_fare',
        'per_km_rate',
        'per_minute_rate',
        'minimum_fare',
        'multiplier',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'base_fare' => 'float',
        'per_km_rate' => 'float',
        'per_minute_rate' => 'float',
        'minimum_fare' => 'float',
        'multiplier' => 'float',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });

        static::saved(function () {
            Cache::forget('active_ride_categories');
        });

        static::deleted(function () {
            Cache::forget('active_ride_categories');
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Get all active categories with caching.
     */
    public static function getActiveCategories()
    {
        return Cache::remember('active_ride_categories', 3600, function () {
            return static::active()->ordered()->get();
        });
    }

    /**
     * Estimate fare based on distance and duration.
     */
    public function calculateEstimatedFare(float $distanceKm, float $durationMinutes, float $countryMultiplier = 1.0): float
    {
        $fare = $this->base_fare + ($distanceKm * $this->per_km_rate) + ($durationMinutes * $this->per_minute_rate);
        $fare = $fare * $this->multiplier * $countryMultiplier;
        return round(max($fare, $this->minimum_fare * $countryMultiplier), 2);
    }
}
