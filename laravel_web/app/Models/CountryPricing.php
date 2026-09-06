<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CountryPricing extends Model
{
    use HasFactory;

    protected $table = 'country_pricings';

    protected $fillable = [
        'country_code',
        'country_name',
        'currency_code',
        'currency_symbol',
        'exchange_rate',
        'is_default',
        'is_active',
        // Ride
        'ride_base_fare',
        'ride_per_km_rate',
        'ride_per_minute_rate',
        'ride_minimum_fare',
        'ride_additional_stop_fee',
        // Delivery
        'delivery_base_fare',
        'delivery_per_km_rate',
        'delivery_instant_addon',
        'delivery_express_addon',
        'delivery_same_day_addon',
        'delivery_scheduled_addon',
        'delivery_per_kg_rate',
        // Driver
        'driver_hourly_rate',
        'driver_daily_rate',
        'driver_weekly_rate',
        // Rental
        'rental_price_multiplier',
        'rental_protection_daily_rate',
        'rental_additional_driver_rate',
        'rental_child_seat_rate',
        'rental_gps_rate',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'exchange_rate' => 'float',
        'ride_base_fare' => 'float',
        'ride_per_km_rate' => 'float',
        'ride_per_minute_rate' => 'float',
        'ride_minimum_fare' => 'float',
        'ride_additional_stop_fee' => 'float',
        'delivery_base_fare' => 'float',
        'delivery_per_km_rate' => 'float',
        'delivery_instant_addon' => 'float',
        'delivery_express_addon' => 'float',
        'delivery_same_day_addon' => 'float',
        'delivery_scheduled_addon' => 'float',
        'delivery_per_kg_rate' => 'float',
        'driver_hourly_rate' => 'float',
        'driver_daily_rate' => 'float',
        'driver_weekly_rate' => 'float',
        'rental_price_multiplier' => 'float',
        'rental_protection_daily_rate' => 'float',
        'rental_additional_driver_rate' => 'float',
        'rental_child_seat_rate' => 'float',
        'rental_gps_rate' => 'float',
    ];

    protected static function booted()
    {
        static::saved(function ($model) {
            Cache::forget('country_pricings_all');
            Cache::forget('country_pricings_active');
            Cache::forget('country_pricing_default');
            Cache::forget('country_pricing_' . strtoupper($model->country_code));

            // If marked as default, unset previous default
            if ($model->is_default) {
                static::where('id', '!=', $model->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });

        static::deleted(function ($model) {
            Cache::forget('country_pricings_all');
            Cache::forget('country_pricings_active');
            Cache::forget('country_pricing_default');
            Cache::forget('country_pricing_' . strtoupper($model->country_code));
        });
    }

    /**
     * Get default country pricing (USA / USD fallback).
     */
    public static function defaultPricing(): self
    {
        return Cache::remember('country_pricing_default', 3600, function () {
            try {
                $record = static::where('is_default', true)->where('is_active', true)->first()
                    ?? static::where('country_code', 'USA')->first()
                    ?? static::query()->first();

                if ($record) {
                    return $record;
                }
            } catch (\Throwable $e) {
                // database might not be migrated yet or connection error
            }

            return static::fallbackUsdInstance();
        });
    }

    /**
     * Find pricing for a country code or name.
     */
    public static function forCountry(?string $country): self
    {
        if (empty($country)) {
            return static::defaultPricing();
        }

        $code = strtoupper(trim($country));

        return Cache::remember('country_pricing_' . $code, 3600, function () use ($code, $country) {
            try {
                $pricing = static::where('is_active', true)
                    ->where(function ($q) use ($code, $country) {
                        $q->where('country_code', $code)
                          ->orWhere('country_name', 'LIKE', $country)
                          ->orWhere('currency_code', $code);
                    })
                    ->first();

                if ($pricing) {
                    return $pricing;
                }
            } catch (\Throwable $e) {
                // fallback
            }

            return static::defaultPricing();
        });
    }

    /**
     * Fallback in-memory instance if DB is not seeded or unavailable.
     */
    public static function fallbackUsdInstance(): self
    {
        $instance = new self();
        $instance->id = 0;
        $instance->country_code = 'USA';
        $instance->country_name = 'United States';
        $instance->currency_code = 'USD';
        $instance->currency_symbol = '$';
        $instance->exchange_rate = 1.0;
        $instance->is_default = true;
        $instance->is_active = true;

        // Ride
        $instance->ride_base_fare = 5.00;
        $instance->ride_per_km_rate = 1.50;
        $instance->ride_per_minute_rate = 0.25;
        $instance->ride_minimum_fare = 10.00;
        $instance->ride_additional_stop_fee = 3.50;

        // Delivery
        $instance->delivery_base_fare = 15.00;
        $instance->delivery_per_km_rate = 1.50;
        $instance->delivery_instant_addon = 10.00;
        $instance->delivery_express_addon = 8.00;
        $instance->delivery_same_day_addon = 4.00;
        $instance->delivery_scheduled_addon = 2.00;
        $instance->delivery_per_kg_rate = 0.75;

        // Driver
        $instance->driver_hourly_rate = 25.00;
        $instance->driver_daily_rate = 170.00;
        $instance->driver_weekly_rate = 1000.00;

        // Rental
        $instance->rental_price_multiplier = 1.0000;
        $instance->rental_protection_daily_rate = 12.00;
        $instance->rental_additional_driver_rate = 10.00;
        $instance->rental_child_seat_rate = 8.00;
        $instance->rental_gps_rate = 5.00;

        return $instance;
    }
}
