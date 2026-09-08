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
        'country_fares',
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
        'country_fares' => 'array',
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
     * Get all active categories safely.
     */
    public static function getActiveCategories()
    {
        try {
            return static::active()->ordered()->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    /**
     * Get localized rates for a specific country (e.g. GHA), falling back to multiplier conversion.
     */
    public function getRatesForCountry(?string $country = null, float $fallbackMultiplier = 1.0): array
    {
        $code = $country ? strtoupper(trim($country)) : 'USA';
        if (strlen($code) === 2) {
            $meta = \App\Services\CountryService::getCountryMetaByIso($code);
            if (!empty($meta['code_3'])) {
                $code = strtoupper($meta['code_3']);
            }
        }

        $cf = $this->country_fares;
        if (is_array($cf)) {
            // Direct 3-letter match (e.g. GHA)
            if (isset($cf[$code]) && is_array($cf[$code])) {
                $regional = $cf[$code];
                return [
                    'base_fare' => (float) ($regional['base_fare'] ?? $this->base_fare),
                    'per_km_rate' => (float) ($regional['per_km_rate'] ?? $this->per_km_rate),
                    'per_minute_rate' => (float) ($regional['per_minute_rate'] ?? $this->per_minute_rate),
                    'minimum_fare' => (float) ($regional['minimum_fare'] ?? $this->minimum_fare),
                    'multiplier' => (float) ($regional['multiplier'] ?? $this->multiplier),
                ];
            }

            // Ghana fallback match if name or GH
            if (in_array($code, ['GH', 'GHA', 'GHANA']) && isset($cf['GHA']) && is_array($cf['GHA'])) {
                $regional = $cf['GHA'];
                return [
                    'base_fare' => (float) ($regional['base_fare'] ?? $this->base_fare),
                    'per_km_rate' => (float) ($regional['per_km_rate'] ?? $this->per_km_rate),
                    'per_minute_rate' => (float) ($regional['per_minute_rate'] ?? $this->per_minute_rate),
                    'minimum_fare' => (float) ($regional['minimum_fare'] ?? $this->minimum_fare),
                    'multiplier' => (float) ($regional['multiplier'] ?? $this->multiplier),
                ];
            }
        }

        // Standard scaling for other countries
        return [
            'base_fare' => round($this->base_fare * $fallbackMultiplier, 2),
            'per_km_rate' => round($this->per_km_rate * $fallbackMultiplier, 2),
            'per_minute_rate' => round($this->per_minute_rate * $fallbackMultiplier, 2),
            'minimum_fare' => round($this->minimum_fare * $fallbackMultiplier, 2),
            'multiplier' => (float) $this->multiplier,
        ];
    }

    /**
     * Estimate fare based on distance and duration.
     */
    public function calculateEstimatedFare(float $distanceKm, float $durationMinutes, ?string $country = null, float $countryMultiplier = 1.0): float
    {
        $rates = $this->getRatesForCountry($country, $countryMultiplier);
        $fare = $rates['base_fare'] + ($distanceKm * $rates['per_km_rate']) + ($durationMinutes * $rates['per_minute_rate']);
        $fare = $fare * ($rates['multiplier'] ?: 1.0);
        return round(max($fare, $rates['minimum_fare']), 2);
    }
}
