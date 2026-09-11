<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryRideCategoryPricing extends Model
{
    protected $table = 'country_ride_category_pricings';

    protected $fillable = [
        'country_code',
        'category_key',
        'category_name',
        'icon',
        'capacity',
        'target_vehicle',
        'description',
        'minimum_fare',
        'base_fare',
        'per_km_rate',
        'per_minute_rate',
        'multiplier',
        'sort_order',
        'is_active',
        'active',
    ];

    protected $casts = [
        'minimum_fare' => 'float',
        'base_fare' => 'float',
        'per_km_rate' => 'float',
        'per_minute_rate' => 'float',
        'multiplier' => 'float',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function ($model) {
            // Keep active and is_active synchronized
            if ($model->isDirty('is_active') && !$model->isDirty('active')) {
                $model->active = $model->is_active;
            } elseif ($model->isDirty('active') && !$model->isDirty('is_active')) {
                $model->is_active = $model->active;
            }
        });
    }

    /**
     * Relationship to parent CountryPricing.
     */
    public function countryPricing(): BelongsTo
    {
        return $this->belongsTo(CountryPricing::class, 'country_code', 'country_code');
    }

    /**
     * Get all active category tiers for a country code.
     */
    public static function forCountry(?string $countryCode): Collection
    {
        $code = strtoupper(trim($countryCode ?? 'USA'));
        return static::where('country_code', $code)
            ->where(function ($q) {
                $q->where('is_active', true)->orWhere('active', true);
            })
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Find category pricing by key or standard vehicle alias.
     */
    public static function findByKeyOrAlias(?string $countryCode, ?string $vehicleType): ?self
    {
        $code = strtoupper(trim($countryCode ?? 'USA'));
        $lower = strtolower(trim($vehicleType ?? 'standard'));

        $mappedKey = 'standard';
        if (str_contains($lower, 'econ') || str_contains($lower, 'hatch') || str_contains($lower, 'picanto') || str_contains($lower, 'i10')) {
            $mappedKey = 'economy';
        } elseif (str_contains($lower, 'bus') || str_contains($lower, 'group') || str_contains($lower, 'hiace') || str_contains($lower, 'microbus')) {
            $mappedKey = 'group_bus';
        } elseif (str_contains($lower, 'chauffeur') || str_contains($lower, 'vip') || str_contains($lower, 'mercedes') || str_contains($lower, 'bmw')) {
            $mappedKey = 'vip_chauffeur';
        } elseif (str_contains($lower, 'van') || str_contains($lower, 'xl') || str_contains($lower, 'h1')) {
            $mappedKey = 'van_xl';
        } elseif (str_contains($lower, 'suv') || str_contains($lower, 'prado') || str_contains($lower, 'explorer') || str_contains($lower, 'luxury suv')) {
            $mappedKey = 'luxury';
        } elseif (str_contains($lower, 'comfort') || str_contains($lower, 'standard') || str_contains($lower, 'corolla') || str_contains($lower, 'sedan')) {
            $mappedKey = 'standard';
        }

        return static::where('country_code', $code)
            ->where(function ($q) use ($mappedKey, $lower) {
                $q->where('category_key', $mappedKey)
                  ->orWhere('category_key', $lower)
                  ->orWhere('category_name', 'LIKE', "%{$lower}%");
            })
            ->where(function ($q) {
                $q->where('is_active', true)->orWhere('active', true);
            })
            ->first();
    }
}
