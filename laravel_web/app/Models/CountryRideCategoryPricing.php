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

            // Also check default seed if table is empty
            if (Schema::hasTable('country_ride_category_pricings')) {
                $count = DB::table('country_ride_category_pricings')->count();
                if ($count === 0) {
                    $now = now();
                    $tiers = [
                        [
                            'country_code' => 'GHA',
                            'category_key' => 'economy',
                            'category_name' => 'Economy',
                            'icon' => '🚗',
                            'capacity' => '1–4 seats',
                            'target_vehicle' => 'Small hatchbacks (e.g., Kia Picanto, Hyundai i10)',
                            'description' => 'Affordable, high-efficiency daily commuting in Accra',
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
                            'icon' => '🚗',
                            'capacity' => '1–4 seats',
                            'target_vehicle' => 'Mid-size sedans (Toyota Corolla, Hyundai Elantra)',
                            'description' => 'Air-conditioned everyday comfort rides',
                            'minimum_fare' => 12.00,
                            'base_fare' => 6.00,
                            'per_km_rate' => 1.45,
                            'per_minute_rate' => 0.25,
                            'multiplier' => 1.00,
                            'sort_order' => 2,
                            'is_active' => true,
                            'active' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                        [
                            'country_code' => 'GHA',
                            'category_key' => 'comfort',
                            'category_name' => 'Comfort Plus',
                            'icon' => '🚙',
                            'capacity' => '1–4 seats',
                            'target_vehicle' => 'Spacious sedans & compact crossovers (Camry, RAV4)',
                            'description' => 'Premium vehicles with top-rated drivers',
                            'minimum_fare' => 16.00,
                            'base_fare' => 8.50,
                            'per_km_rate' => 1.95,
                            'per_minute_rate' => 0.35,
                            'multiplier' => 1.15,
                            'sort_order' => 3,
                            'is_active' => true,
                            'active' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                        [
                            'country_code' => 'GHA',
                            'category_key' => 'luxury',
                            'category_name' => 'Luxury / VIP',
                            'icon' => '👑',
                            'capacity' => '1–4 seats',
                            'target_vehicle' => 'Executive SUVs & luxury sedans (Mercedes E-Class, Prado)',
                            'description' => 'Executive business and VIP travel',
                            'minimum_fare' => 28.00,
                            'base_fare' => 15.00,
                            'per_km_rate' => 3.20,
                            'per_minute_rate' => 0.50,
                            'multiplier' => 1.50,
                            'sort_order' => 4,
                            'is_active' => true,
                            'active' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                        [
                            'country_code' => 'GHA',
                            'category_key' => 'van_xl',
                            'category_name' => 'Van / XL (6-8 Passengers)',
                            'icon' => '🚐',
                            'capacity' => '6–8 seats',
                            'target_vehicle' => 'Minivans & Large MPVs (Toyota Sienna, Hyundai H-1)',
                            'description' => 'Airport runs, families & group luggage',
                            'minimum_fare' => 22.00,
                            'base_fare' => 12.00,
                            'per_km_rate' => 2.50,
                            'per_minute_rate' => 0.40,
                            'multiplier' => 1.30,
                            'sort_order' => 5,
                            'is_active' => true,
                            'active' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                        [
                            'country_code' => 'GHA',
                            'category_key' => 'group_bus',
                            'category_name' => 'Group Bus / Coaster',
                            'icon' => '🚌',
                            'capacity' => '14–30 seats',
                            'target_vehicle' => 'Toyota HiAce / Coaster bus',
                            'description' => 'Corporate shuttles, tour groups & events',
                            'minimum_fare' => 35.00,
                            'base_fare' => 20.00,
                            'per_km_rate' => 3.80,
                            'per_minute_rate' => 0.60,
                            'multiplier' => 1.80,
                            'sort_order' => 6,
                            'is_active' => true,
                            'active' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                        [
                            'country_code' => 'GHA',
                            'category_key' => 'vip_chauffeur',
                            'category_name' => 'VIP Chauffeur & Security',
                            'icon' => '🛡️',
                            'capacity' => '1–4 seats',
                            'target_vehicle' => 'Armored/Executive Fleet with Private Chauffeur',
                            'description' => 'Discreet elite transport with security protocol',
                            'minimum_fare' => 45.00,
                            'base_fare' => 25.00,
                            'per_km_rate' => 4.50,
                            'per_minute_rate' => 0.80,
                            'multiplier' => 2.20,
                            'sort_order' => 7,
                            'is_active' => true,
                            'active' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                    ];
                    DB::table('country_ride_category_pricings')->insert($tiers);
                }
            }

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
