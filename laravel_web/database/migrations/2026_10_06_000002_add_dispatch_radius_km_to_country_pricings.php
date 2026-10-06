<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('country_pricings')) {
            if (!Schema::hasColumn('country_pricings', 'dispatch_radius_km')) {
                Schema::table('country_pricings', function (Blueprint $table) {
                    $table->decimal('dispatch_radius_km', 8, 2)->default(10.00)->after('is_active');
                });
            }

            // Set default 10km for all countries in DB
            DB::table('country_pricings')->update(['dispatch_radius_km' => 10.00]);
        }

        // Clean up legacy Ghana rides where driver_country is NULL or incorrectly set to IND
        if (Schema::hasTable('rides')) {
            DB::table('rides')
                ->where(function ($q) {
                    $q->whereNull('driver_country')
                      ->orWhere('driver_country', 'IND');
                })
                ->where(function ($q) {
                    $q->where('pickup_location', 'like', '%Ghana%')
                      ->orWhere('dropoff_location', 'like', '%Ghana%')
                      ->orWhere(function ($sub) {
                          $sub->whereBetween('pickup_lat', [4.0, 12.0])
                              ->whereBetween('pickup_lng', [-4.0, 2.0]);
                      });
                })
                ->update(['driver_country' => 'GHA']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('country_pricings') && Schema::hasColumn('country_pricings', 'dispatch_radius_km')) {
            Schema::table('country_pricings', function (Blueprint $table) {
                $table->dropColumn('dispatch_radius_km');
            });
        }
    }
};
