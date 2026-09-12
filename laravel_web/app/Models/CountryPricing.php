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

        $normalized = \App\Services\CountryService::normalizeToCode($country);
        $code = $normalized ? strtoupper($normalized) : strtoupper(trim($country));
        $raw = trim($country);

        try {
            $pricing = static::where('is_active', true)
                ->where(function ($q) use ($code, $raw) {
                    $q->where('country_code', $code)
                      ->orWhere('country_name', 'LIKE', $raw)
                      ->orWhere('country_name', 'LIKE', $code)
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

    /**
     * Category tiers for this country (e.g. Ghana multi-tier vehicle pricing).
     */
    public function rideCategoryPricings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        CountryRideCategoryPricing::ensureTableExists();
        return $this->hasMany(CountryRideCategoryPricing::class, 'country_code', 'country_code');
    }

    /**
     * Strategic Cost Matrix for Ghana (Accra Market Disruption Architecture).
     * Primary Source of Truth: Database (country_ride_category_pricings).
     * Fallback: Ghana-specific hardcoded architectural values.
     */
    public static function getGhanaPricingMatrix(): array
    {
        try {
            CountryRideCategoryPricing::ensureTableExists();
            $dbTiers = CountryRideCategoryPricing::forCountry('GHA');
            if ($dbTiers->isNotEmpty()) {
                $matrix = [];
                foreach ($dbTiers as $tier) {
                    $item = [
                        'id' => $tier->id,
                        'name' => $tier->category_name,
                        'slug' => $tier->category_key,
                        'category_key' => $tier->category_key,
                        'icon' => $tier->icon ?: '🚗',
                        'minimum_fare' => (float) $tier->minimum_fare,
                        'base_fare' => (float) $tier->base_fare,
                        'per_km_rate' => (float) $tier->per_km_rate,
                        'per_minute_rate' => (float) ($tier->per_minute_rate ?: 0.30),
                        'multiplier' => (float) ($tier->multiplier ?: 1.0),
                        'capacity' => $tier->capacity ?: '1–4 seats',
                        'luggage' => match ($tier->category_key) {
                            'economy' => '2 Bags',
                            'standard' => '3 Bags',
                            'luxury' => '5 Bags',
                            'van_xl' => '6 Bags',
                            'vip_chauffeur' => '3 Bags',
                            'group_bus' => '10 Bags',
                            default => '2 Bags',
                        },
                        'base' => (float) $tier->base_fare,
                        'perKm' => (float) $tier->per_km_rate,
                        'perMin' => (float) ($tier->per_minute_rate ?: 0.30),
                        'min' => (float) $tier->minimum_fare,
                        'seats' => $tier->capacity ?: '1–4 seats',
                        'desc' => $tier->description ?: '',
                        'target' => $tier->target_vehicle ?: $tier->category_name,
                        'description' => $tier->description ?: '',
                    ];

                    // Map canonical primary key (exactly 6 tiers)
                    $matrix[$tier->category_key] = $item;
                }

                if (!empty($matrix)) {
                    return $matrix;
                }
            }
        } catch (\Throwable $e) {
            // Safe fallback if database is unavailable
        }

        // Safe fallback matching exact required Ghana pricing architecture (6 canonical tiers)
        return [
            'economy' => [
                'name' => 'Economy',
                'slug' => 'economy',
                'category_key' => 'economy',
                'icon' => '🚗',
                'minimum_fare' => 8.50,
                'base_fare' => 4.50,
                'per_km_rate' => 1.10,
                'per_minute_rate' => 0.20,
                'multiplier' => 1.0,
                'base' => 4.50,
                'perKm' => 1.10,
                'perMin' => 0.20,
                'min' => 8.50,
                'seats' => '1–4 seats',
                'desc' => 'Small hatchbacks for affordable, high-efficiency daily commuting in Accra',
                'capacity' => '1–4 seats',
                'luggage' => '2 Bags',
                'target' => 'Small hatchbacks (e.g., Kia Picanto, Hyundai i10)',
                'description' => 'Small hatchbacks for affordable, high-efficiency daily commuting in Accra',
            ],
            'standard' => [
                'name' => 'Standard / Comfort',
                'slug' => 'standard',
                'category_key' => 'standard',
                'icon' => '🚘',
                'minimum_fare' => 23.50,
                'base_fare' => 7.00,
                'per_km_rate' => 1.80,
                'per_minute_rate' => 0.30,
                'multiplier' => 1.0,
                'base' => 7.00,
                'perKm' => 1.80,
                'perMin' => 0.30,
                'min' => 23.50,
                'seats' => '1–4 seats',
                'desc' => 'Clean climate-controlled sedans with top-rated vetted drivers',
                'capacity' => '1–4 seats',
                'luggage' => '3 Bags',
                'target' => 'Clean sedans with high-functioning A/C (e.g., Toyota Corolla)',
                'description' => 'Clean climate-controlled sedans with top-rated vetted drivers',
            ],
            'luxury' => [
                'name' => 'Luxury SUV',
                'slug' => 'luxury',
                'category_key' => 'luxury',
                'icon' => '🚙',
                'minimum_fare' => 35.20,
                'base_fare' => 12.00,
                'per_km_rate' => 3.00,
                'per_minute_rate' => 0.50,
                'multiplier' => 1.0,
                'base' => 12.00,
                'perKm' => 3.00,
                'perMin' => 0.50,
                'min' => 35.20,
                'seats' => '1–6 seats',
                'desc' => 'High-ride premium SUVs tailored for business travelers and airport runs',
                'capacity' => '1–6 seats',
                'luggage' => '5 Bags',
                'target' => 'Premium SUVs for business travelers (e.g., Toyota Prado, Ford Explorer)',
                'description' => 'High-ride premium SUVs tailored for business travelers and airport runs',
            ],
            'van_xl' => [
                'name' => 'Van XL',
                'slug' => 'van_xl',
                'category_key' => 'van_xl',
                'icon' => '🚐',
                'minimum_fare' => 50.20,
                'base_fare' => 15.00,
                'per_km_rate' => 4.50,
                'per_minute_rate' => 0.75,
                'multiplier' => 1.0,
                'base' => 15.00,
                'perKm' => 4.50,
                'perMin' => 0.75,
                'min' => 50.20,
                'seats' => '1–7 seats',
                'desc' => 'High-capacity vans for large groups, delegations & heavy luggage',
                'capacity' => '1–7 seats',
                'luggage' => '6 Bags',
                'target' => 'Multi-passenger vehicles for airport runs or large families (e.g., Hyundai H1)',
                'description' => 'High-capacity vans for large groups, delegations & heavy luggage',
            ],
            'vip_chauffeur' => [
                'name' => 'VIP Chauffeurs',
                'slug' => 'vip_chauffeur',
                'category_key' => 'vip_chauffeur',
                'icon' => '👑',
                'minimum_fare' => 109.50,
                'base_fare' => 30.00,
                'per_km_rate' => 6.50,
                'per_minute_rate' => 1.00,
                'multiplier' => 1.0,
                'base' => 30.00,
                'perKm' => 6.50,
                'perMin' => 1.00,
                'min' => 109.50,
                'seats' => '1–4 seats',
                'desc' => 'Executive flagship luxury sedans with professional suited chauffeurs',
                'capacity' => '1–4 seats',
                'luggage' => '3 Bags',
                'target' => 'High-end luxury executive sedans (e.g., Mercedes-Benz E-Class, BMW 5 Series)',
                'description' => 'Executive flagship luxury sedans with professional suited chauffeurs',
            ],
            'group_bus' => [
                'name' => 'Group Bus (7–14)',
                'slug' => 'group_bus',
                'category_key' => 'group_bus',
                'icon' => '🚌',
                'minimum_fare' => 150.90,
                'base_fare' => 45.00,
                'per_km_rate' => 8.00,
                'per_minute_rate' => 1.50,
                'multiplier' => 1.0,
                'base' => 45.00,
                'perKm' => 8.00,
                'perMin' => 1.50,
                'min' => 150.90,
                'seats' => '7–14 seats',
                'desc' => 'Microbuses for event transportation, family gatherings & corporate teams',
                'capacity' => '7–14 seats',
                'luggage' => '10 Bags',
                'target' => 'Microbuses for event transport or corporate teams (e.g., Toyota HiAce)',
                'description' => 'Microbuses for event transportation, family gatherings & corporate teams',
            ],
        ];
    }

    /**
     * Unified, robust Pricing Matrix for ANY country in the database.
     * Single Source of Truth for frontend (/ride, /pricing), API, and backend calculations.
     * 1. If Ghana, loads official PDF matrix.
     * 2. If custom tiers exist in `country_ride_category_pricings` for the country, uses them.
     * 3. Otherwise, dynamically calculates the standard 5 categories directly from the country's
     *    native base_fare, per_km_rate, per_minute_rate, and minimum_fare in `country_pricings`.
     */
    public static function getPricingMatrixForCountry(?string $countryCode): array
    {
        try {
            CountryRideCategoryPricing::ensureTableExists();
            $countryPricing = static::forCountry($countryCode);
            $code = strtoupper(trim($countryPricing->country_code ?? 'USA'));

            // 1. Ghana specific PDF matrix
            if ($code === 'GHA' || strtoupper(trim($countryPricing->currency_code ?? '')) === 'GHS') {
                return static::getGhanaPricingMatrix();
            }

            // 2. Custom category rows in database for this specific country
            $dbTiers = CountryRideCategoryPricing::forCountry($code);
            if ($dbTiers->isNotEmpty()) {
                $matrix = [];
                foreach ($dbTiers as $tier) {
                    $item = [
                        'id' => $tier->id,
                        'name' => $tier->category_name,
                        'slug' => $tier->category_key,
                        'category_key' => $tier->category_key,
                        'icon' => $tier->icon ?: '🚗',
                        'minimum_fare' => (float) $tier->minimum_fare,
                        'base_fare' => (float) $tier->base_fare,
                        'per_km_rate' => (float) $tier->per_km_rate,
                        'per_minute_rate' => (float) ($tier->per_minute_rate ?: 0.30),
                        'multiplier' => (float) ($tier->multiplier ?: 1.0),
                        'capacity' => $tier->capacity ?: '1–4 seats',
                        'luggage' => match ($tier->category_key) {
                            'economy' => '2 Bags',
                            'comfort', 'standard' => '3 Bags',
                            'suv', 'luxury' => '5 Bags',
                            'van_xl', 'xl' => '6 Bags',
                            'vip_chauffeur' => '3 Bags',
                            'group_bus' => '10 Bags',
                            default => '3 Bags',
                        },
                        'target' => $tier->target_vehicle ?: $tier->category_name,
                        'description' => $tier->description ?: '',
                        'base' => (float) $tier->base_fare,
                        'perKm' => (float) $tier->per_km_rate,
                        'perMin' => (float) ($tier->per_minute_rate ?: 0.30),
                        'min' => (float) $tier->minimum_fare,
                        'seats' => $tier->capacity ?: '1–4 seats',
                        'desc' => $tier->description ?: '',
                    ];
                    $matrix[$tier->category_key] = $item;
                }
                return $matrix;
            }

            // 3. Dynamic Native Tiers calculated from country_pricings base and km rates
            $baseF = (float) ($countryPricing->ride_base_fare ?: 5.00);
            $perKmF = (float) ($countryPricing->ride_per_km_rate ?: 1.50);
            $perMinF = (float) ($countryPricing->ride_per_minute_rate ?: 0.25);
            $minF = (float) ($countryPricing->ride_minimum_fare ?: 10.00);

            $tierDefinitions = [
                'economy' => [
                    'name' => 'Economy',
                    'slug' => 'economy',
                    'icon' => '🚗',
                    'capacity' => '1–4 seats',
                    'base_mult' => 1.00,
                    'km_mult' => 1.00,
                    'minute_mult' => 1.00,
                    'min_mult' => 1.00,
                    'luggage' => '2 Bags',
                    'description' => 'Affordable everyday rides',
                    'target' => 'Everyday city economy vehicles',
                ],
                'comfort' => [
                    'name' => 'Comfort',
                    'slug' => 'comfort',
                    'icon' => '✨',
                    'capacity' => '1–4 seats',
                    'base_mult' => 1.20,
                    'km_mult' => 1.20,
                    'minute_mult' => 1.20,
                    'min_mult' => 1.20,
                    'luggage' => '3 Bags',
                    'description' => 'Newer cars with extra legroom & quiet rides',
                    'target' => 'Clean executive sedans with climate control',
                ],
                'suv' => [
                    'name' => 'SUV',
                    'slug' => 'suv',
                    'icon' => '🚙',
                    'capacity' => '1–6 seats',
                    'base_mult' => 1.40,
                    'km_mult' => 1.40,
                    'minute_mult' => 1.40,
                    'min_mult' => 1.40,
                    'luggage' => '5 Bags',
                    'description' => 'Spacious rides for up to 6 people',
                    'target' => 'Premium high-ride SUVs',
                ],
                'van_xl' => [
                    'name' => 'XL Van',
                    'slug' => 'van_xl',
                    'icon' => '🚐',
                    'capacity' => '1–7 seats',
                    'base_mult' => 1.60,
                    'km_mult' => 1.60,
                    'minute_mult' => 1.60,
                    'min_mult' => 1.60,
                    'luggage' => '6 Bags',
                    'description' => 'Large vans for groups and extra luggage',
                    'target' => 'High-capacity passenger vans',
                ],
                'vip_chauffeur' => [
                    'name' => 'VIP Chauffeurs',
                    'slug' => 'vip_chauffeur',
                    'icon' => '👑',
                    'capacity' => '1–4 seats',
                    'base_mult' => 2.00,
                    'km_mult' => 2.00,
                    'minute_mult' => 2.00,
                    'min_mult' => 2.00,
                    'luggage' => '3 Bags',
                    'description' => 'Top-tier luxury with executive suited chauffeur',
                    'target' => 'Flagship luxury sedans',
                ],
            ];

            $matrix = [];
            foreach ($tierDefinitions as $slug => $cfg) {
                $tierBase = round($baseF * $cfg['base_mult'], 2);
                $tierPerKm = round($perKmF * $cfg['km_mult'], 2);
                $tierPerMin = round($perMinF * $cfg['minute_mult'], 2);
                $tierMin = round($minF * $cfg['min_mult'], 2);

                $matrix[$slug] = [
                    'id' => $slug,
                    'name' => $cfg['name'],
                    'slug' => $cfg['slug'],
                    'category_key' => $slug,
                    'icon' => $cfg['icon'],
                    'capacity' => $cfg['capacity'],
                    'base_fare' => $tierBase,
                    'per_km_rate' => $tierPerKm,
                    'per_minute_rate' => $tierPerMin,
                    'minimum_fare' => $tierMin,
                    'multiplier' => 1.0,
                    'luggage' => $cfg['luggage'],
                    'target' => $cfg['target'],
                    'description' => $cfg['description'],
                    'base' => $tierBase,
                    'perKm' => $tierPerKm,
                    'perMin' => $tierPerMin,
                    'min' => $tierMin,
                    'seats' => $cfg['capacity'],
                    'desc' => $cfg['description'],
                ];
            }

            return $matrix;
        } catch (\Throwable $e) {
            return static::getGhanaPricingMatrix();
        }
    }
}
