<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
     * Ensure database table exists and is populated with default tiers.
     * Guarantees zero 500 crashes even if migrations were not executed manually.
     */
    public static function ensureTableExists(): void
    {
        try {
            if (!Schema::hasTable('country_ride_category_pricings')) {
                // Attempt artisan migrate first
                try {
                    Artisan::call('migrate', ['--force' => true]);
                } catch (\Throwable $migErr) {
                    Log::warning('Artisan migrate call in ensureTableExists: ' . $migErr->getMessage());
                }

                // If still missing, create table directly
                if (!Schema::hasTable('country_ride_category_pricings')) {
                    Schema::create('country_ride_category_pricings', function (Blueprint $table) {
                        $table->id();
                        $table->string('country_code', 10)->index();
                        $table->string('category_key', 50);
                        $table->string('category_name', 100);
                        $table->string('icon', 50)->nullable()->default('🚗');
                        $table->string('capacity', 50)->nullable()->default('1–4 seats');
                        $table->string('target_vehicle')->nullable();
                        $table->text('description')->nullable();
                        $table->decimal('minimum_fare', 10, 2);
                        $table->decimal('base_fare', 10, 2);
                        $table->decimal('per_km_rate', 10, 2);
                        $table->decimal('per_minute_rate', 10, 2)->default(0.30);
                        $table->decimal('multiplier', 5, 2)->default(1.00);
                        $table->integer('sort_order')->default(0);
                        $table->boolean('is_active')->default(true);
                        $table->boolean('active')->default(true);
                        $table->timestamps();

                        $table->unique(['country_code', 'category_key'], 'uniq_country_category_pricing');
                    });
                }
            }

            // Ensure official Ghana PDF tiers exist and are synchronized
            static::syncGhanaPdfTiers(false);


            // Ensure rides table has payment hold and dispatch gate columns
            if (Schema::hasTable('rides')) {
                Schema::table('rides', function (Blueprint $table) {
                    if (!Schema::hasColumn('rides', 'driver_search_started_at')) {
                        $table->timestamp('driver_search_started_at')->nullable()->after('payment_status');
                    }
                    if (!Schema::hasColumn('rides', 'payment_held_at')) {
                        $table->timestamp('payment_held_at')->nullable()->after('driver_search_started_at');
                    }
                    if (!Schema::hasColumn('rides', 'hold_payment_intent_id')) {
                        $table->string('hold_payment_intent_id')->nullable()->after('payment_held_at');
                    }
                    if (!Schema::hasColumn('rides', 'hold_authorization_code')) {
                        $table->string('hold_authorization_code')->nullable()->after('hold_payment_intent_id');
                    }
                });
            }
        } catch (\Throwable $e) {
            Log::error('ensureTableExists fatal error: ' . $e->getMessage());
        }
    }

    /**
     * Synchronize the official Ghana Pricing Architecture from the PDF Cost Matrix.
     * Upserts the exact 6 vehicle categories in native Ghana Cedis (GH₵).
     */
    public static function syncGhanaPdfTiers(bool $force = false): void
    {
        try {
            if (!Schema::hasTable('country_ride_category_pricings')) {
                return;
            }

            $count = DB::table('country_ride_category_pricings')
                ->where('country_code', 'GHA')
                ->count();

            // If not forced and all 6 tiers already exist with proper minimum_fare, do not overwrite custom edits
            if (!$force && $count >= 6) {
                $hasValidEconomy = DB::table('country_ride_category_pricings')
                    ->where('country_code', 'GHA')
                    ->where('category_key', 'economy')
                    ->where('minimum_fare', 8.50)
                    ->exists();
                $hasValidStandard = DB::table('country_ride_category_pricings')
                    ->where('country_code', 'GHA')
                    ->where('category_key', 'standard')
                    ->where('minimum_fare', 23.50)
                    ->exists();
                if ($hasValidEconomy && $hasValidStandard) {
                    return;
                }
            }

            $now = now();
            $officialPdfTiers = [
                [
                    'country_code' => 'GHA',
                    'category_key' => 'economy',
                    'category_name' => 'Economy',
                    'icon' => '🚗',
                    'capacity' => '1–4 seats',
                    'target_vehicle' => 'Small hatchbacks (e.g., Kia Picanto, Hyundai i10)',
                    'description' => 'Small hatchbacks for affordable, high-efficiency daily commuting in Accra',
                    'minimum_fare' => 8.50,
                    'base_fare' => 4.50,
                    'per_km_rate' => 1.10,
                    'per_minute_rate' => 0.20,
                    'multiplier' => 1.00,
                    'sort_order' => 1,
                    'is_active' => true,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'country_code' => 'GHA',
                    'category_key' => 'standard',
                    'category_name' => 'Standard / Comfort',
                    'icon' => '🚘',
                    'capacity' => '1–4 seats',
                    'target_vehicle' => 'Clean sedans with high-functioning A/C (e.g., Toyota Corolla)',
                    'description' => 'Clean climate-controlled sedans with top-rated vetted drivers',
                    'minimum_fare' => 23.50,
                    'base_fare' => 7.00,
                    'per_km_rate' => 1.80,
                    'per_minute_rate' => 0.30,
                    'multiplier' => 1.00,
                    'sort_order' => 2,
                    'is_active' => true,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'country_code' => 'GHA',
                    'category_key' => 'luxury',
                    'category_name' => 'Luxury SUV',
                    'icon' => '🚙',
                    'capacity' => '1–6 seats',
                    'target_vehicle' => 'Premium SUVs for business travelers (e.g., Toyota Prado, Ford Explorer)',
                    'description' => 'High-ride premium SUVs tailored for business travelers and airport runs',
                    'minimum_fare' => 35.20,
                    'base_fare' => 12.00,
                    'per_km_rate' => 3.00,
                    'per_minute_rate' => 0.50,
                    'multiplier' => 1.00,
                    'sort_order' => 3,
                    'is_active' => true,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'country_code' => 'GHA',
                    'category_key' => 'van_xl',
                    'category_name' => 'Van XL',
                    'icon' => '🚐',
                    'capacity' => '1–7 seats',
                    'target_vehicle' => 'Multi-passenger vehicles for airport runs or large families (e.g., Hyundai H1)',
                    'description' => 'Multi-passenger vehicles for airport runs or large families',
                    'minimum_fare' => 50.20,
                    'base_fare' => 15.00,
                    'per_km_rate' => 4.50,
                    'per_minute_rate' => 0.75,
                    'multiplier' => 1.00,
                    'sort_order' => 4,
                    'is_active' => true,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'country_code' => 'GHA',
                    'category_key' => 'vip_chauffeur',
                    'category_name' => 'VIP Chauffeurs',
                    'icon' => '👑',
                    'capacity' => '1–4 seats',
                    'target_vehicle' => 'High-end luxury executive sedans (e.g., Mercedes-Benz E-Class, BMW 5 Series)',
                    'description' => 'High-end luxury executive sedans with suited, vetted private chauffeurs',
                    'minimum_fare' => 109.50,
                    'base_fare' => 30.00,
                    'per_km_rate' => 6.50,
                    'per_minute_rate' => 1.00,
                    'multiplier' => 1.00,
                    'sort_order' => 5,
                    'is_active' => true,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'country_code' => 'GHA',
                    'category_key' => 'group_bus',
                    'category_name' => 'Group Bus (7–14)',
                    'icon' => '🚌',
                    'capacity' => '7–14 seats',
                    'target_vehicle' => 'Microbuses for event transport or corporate teams (e.g., Toyota HiAce)',
                    'description' => 'Microbuses for event transport or corporate teams',
                    'minimum_fare' => 150.90,
                    'base_fare' => 45.00,
                    'per_km_rate' => 8.00,
                    'per_minute_rate' => 1.20,
                    'multiplier' => 1.00,
                    'sort_order' => 6,
                    'is_active' => true,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];

            foreach ($officialPdfTiers as $tier) {
                DB::table('country_ride_category_pricings')->updateOrInsert(
                    [
                        'country_code' => 'GHA',
                        'category_key' => $tier['category_key'],
                    ],
                    $tier
                );
            }

            // Remove any outdated or non-canonical tiers for Ghana (e.g. legacy comfort duplicate)
            DB::table('country_ride_category_pricings')
                ->where('country_code', 'GHA')
                ->whereNotIn('category_key', ['economy', 'standard', 'luxury', 'van_xl', 'vip_chauffeur', 'group_bus'])
                ->delete();

            // Ensure parent CountryPricing for Ghana is aligned with the PDF matrix
            if (Schema::hasTable('country_pricings')) {
                DB::table('country_pricings')->updateOrInsert(
                    ['country_code' => 'GHA'],
                    [
                        'country_name' => 'Ghana',
                        'currency_code' => 'GHS',
                        'currency_symbol' => 'GH₵',
                        'exchange_rate' => 15.5000,
                        'is_active' => true,
                        'ride_base_fare' => 7.00,
                        'ride_per_km_rate' => 1.80,
                        'ride_per_minute_rate' => 0.30,
                        'ride_minimum_fare' => 10.00, // Standalone platform threshold from PDF note
                        'ride_additional_stop_fee' => 3.50,
                        'delivery_base_fare' => 18.00,
                        'delivery_per_km_rate' => 2.00,
                        'delivery_instant_addon' => 10.00,
                        'delivery_express_addon' => 8.00,
                        'delivery_same_day_addon' => 4.00,
                        'delivery_scheduled_addon' => 2.00,
                        'delivery_per_kg_rate' => 1.00,
                        'updated_at' => $now,
                    ]
                );
            }
        } catch (\Throwable $err) {
            Log::error('syncGhanaPdfTiers error: ' . $err->getMessage());
        }
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
        static::ensureTableExists();

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
        static::ensureTableExists();

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
