<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update Ghana rental multiplier in country_pricings table.
     */
    public function up(): void
    {
        if (Schema::hasTable('country_pricings')) {
            DB::table('country_pricings')
                ->where('country_code', 'GHA')
                ->update([
                    'rental_price_multiplier' => 12.5000,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('country_pricings')) {
            DB::table('country_pricings')
                ->where('country_code', 'GHA')
                ->update([
                    'rental_price_multiplier' => 1.0000,
                    'updated_at' => now(),
                ]);
        }
    }
};
