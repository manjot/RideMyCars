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
            // If marked as default, unset previous default
            if ($model->is_default) {
                static::where('id', '!=', $model->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Get default country pricing (USA / USD fallback).
     */
    public static function defaultPricing(): self
    {
        try {
            $record = static::where('is_default', true)->where('is_active', true)->first()
                ?? static::where('country_code', 'USA')->first()
                ?? static::query()->first();

            if ($record instanceof self) {
                return $record;
            }
        } catch (\Throwable $e) {
            // database might not be migrated yet or connection error
        }

        return static::fallbackUsdInstance();
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

        try {
            $pricing = static::where('is_active', true)
                ->where(function ($q) use ($code, $country) {
                    $q->where('country_code', $code)
                      ->orWhere('country_name', 'LIKE', $country)
                      ->orWhere('currency_code', $code);
                })
                ->first();

            if ($pricing instanceof self) {
                return $pricing;
            }
        } catch (\Throwable $e) {
            // fallback
        }

        // Check if country metadata is known (e.g. IND / India)
        $meta = \App\Services\CountryService::getCountryMetaByIso($code);
        if ($meta && !empty($meta['name'])) {
            return static::createUnsupportedInstance($meta['code_3'], $meta['name']);
        }

        return static::defaultPricing();
    }

    /**
     * Create an in-memory CountryPricing instance for a visitor's location that isn't configured in admin yet.
     * All rates fallback to USD ($), with unsupported indicator.
     */
    public static function createUnsupportedInstance(string $code, ?string $name = null): self
    {
        $instance = new self();
        $instance->id = 0;
        $instance->country_code = strtoupper($code);
        $instance->country_name = $name ?? strtoupper($code);
        $instance->currency_code = 'USD';
        $instance->currency_symbol = '$';
        $instance->exchange_rate = 1.0;
        $instance->is_default = false;
        $instance->is_active = true;

        // Fares in USD ($)
        $instance->ride_base_fare = 5.00;
        $instance->ride_per_km_rate = 1.50;
        $instance->ride_per_minute_rate = 0.35;
        $instance->ride_minimum_fare = 8.00;
        $instance->ride_additional_stop_fee = 3.00;

        $instance->delivery_base_fare = 4.50;
        $instance->delivery_per_km_rate = 1.20;
        $instance->delivery_instant_addon = 3.50;
        $instance->delivery_express_addon = 2.00;
        $instance->delivery_same_day_addon = 0.00;
        $instance->delivery_scheduled_addon = 0.00;
        $instance->delivery_per_kg_rate = 0.50;

        $instance->driver_hourly_rate = 15.00;
        $instance->driver_daily_rate = 90.00;
        $instance->driver_weekly_rate = 450.00;

        $instance->rental_price_multiplier = 1.00;
        $instance->rental_protection_daily_rate = 15.00;
        $instance->rental_additional_driver_rate = 10.00;
        $instance->rental_child_seat_rate = 7.00;
        $instance->rental_gps_rate = 5.00;

        $instance->setAttribute('is_unsupported_region', true);

        return $instance;
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
