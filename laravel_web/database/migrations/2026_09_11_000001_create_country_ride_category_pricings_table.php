<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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

        // Idempotent initial seed of Ghana pricing tiers
        $now = now();
        $ghanaTiers = [
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
                'description' => 'High-capacity vans for large groups, delegations & heavy luggage',
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
                'description' => 'Executive flagship luxury sedans with professional suited chauffeurs',
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
                'description' => 'Microbuses for event transportation, family gatherings & corporate teams',
                'minimum_fare' => 150.90,
                'base_fare' => 45.00,
                'per_km_rate' => 8.00,
                'per_minute_rate' => 1.50,
                'multiplier' => 1.00,
                'sort_order' => 6,
                'is_active' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($ghanaTiers as $tier) {
            $exists = DB::table('country_ride_category_pricings')
                ->where('country_code', $tier['country_code'])
                ->where('category_key', $tier['category_key'])
                ->exists();

            if (!$exists) {
                DB::table('country_ride_category_pricings')->insert($tier);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_ride_category_pricings');
    }
};
