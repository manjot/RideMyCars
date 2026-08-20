<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update any existing rides where fare is 0 or null with realistic calculated amounts
        $rides = DB::table('rides')->whereNull('fare')->orWhere('fare', '<=', 0)->get();

        foreach ($rides as $ride) {
            $pickup = strtolower($ride->pickup_location ?? '');
            $dropoff = strtolower($ride->dropoff_location ?? '');

            if (str_contains($pickup, 'agra') && str_contains($dropoff, 'varanasi')) {
                $fare = 559.40;
            } elseif (str_contains($pickup, 'delhi') && str_contains($dropoff, 'bengaluru')) {
                $fare = 480.00;
            } elseif (str_contains($pickup, 'karol bagh') && str_contains($dropoff, 'dwarka')) {
                $fare = 24.50;
            } elseif (str_contains($pickup, 'dwaraka') || str_contains($dropoff, 'dwaraka')) {
                $fare = 38.00;
            } else {
                $fare = 35.00;
            }

            DB::table('rides')->where('id', $ride->id)->update(['fare' => $fare]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
