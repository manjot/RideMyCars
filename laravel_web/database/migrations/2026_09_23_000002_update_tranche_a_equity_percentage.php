<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('investment_plans')) {
            DB::table('investment_plans')
                ->where('tranche_code', 'A')
                ->update([
                    'equity_percentage' => 7.00,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('investment_plans')) {
            DB::table('investment_plans')
                ->where('tranche_code', 'A')
                ->update([
                    'equity_percentage' => 10.00,
                    'updated_at' => now(),
                ]);
        }
    }
};
