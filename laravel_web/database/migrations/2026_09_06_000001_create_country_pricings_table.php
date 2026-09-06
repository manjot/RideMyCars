<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('country_pricings', function (Blueprint $table) {
            $table->id();
            
            // Country & Currency identification
            $table->string('country_code', 10)->unique()->index(); // e.g. USA, GHA, ZAF, NGA, GBR, CAN
            $table->string('country_name', 100);                   // e.g. United States, Ghana
            $table->string('currency_code', 10)->default('USD');   // e.g. USD, GHS, ZAR, NGN, GBP
            $table->string('currency_symbol', 10)->default('$');   // e.g. $, GH₵, R, ₦, £
            $table->decimal('exchange_rate', 14, 4)->default(1.0000); // Multiplier relative to USD
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();

            // 1. Ride Hailing Fares
            $table->decimal('ride_base_fare', 10, 2)->default(5.00);
            $table->decimal('ride_per_km_rate', 10, 2)->default(1.50);
            $table->decimal('ride_per_minute_rate', 10, 2)->default(0.25);
            $table->decimal('ride_minimum_fare', 10, 2)->default(10.00);
            $table->decimal('ride_additional_stop_fee', 10, 2)->default(3.50);

            // 2. Package Delivery Fares
            $table->decimal('delivery_base_fare', 10, 2)->default(15.00);
            $table->decimal('delivery_per_km_rate', 10, 2)->default(1.50);
            $table->decimal('delivery_instant_addon', 10, 2)->default(10.00);
            $table->decimal('delivery_express_addon', 10, 2)->default(8.00);
            $table->decimal('delivery_same_day_addon', 10, 2)->default(4.00);
            $table->decimal('delivery_scheduled_addon', 10, 2)->default(2.00);
            $table->decimal('delivery_per_kg_rate', 10, 2)->default(0.75);

            // 3. Driver Hire Fares
            $table->decimal('driver_hourly_rate', 10, 2)->default(25.00);
            $table->decimal('driver_daily_rate', 10, 2)->default(170.00);
            $table->decimal('driver_weekly_rate', 10, 2)->default(1000.00);

            // 4. Car Rental Pricing Multipliers & Extras
            $table->decimal('rental_price_multiplier', 14, 4)->default(1.0000); // scales vehicle daily_rate
            $table->decimal('rental_protection_daily_rate', 10, 2)->default(12.00);
            $table->decimal('rental_additional_driver_rate', 10, 2)->default(10.00);
            $table->decimal('rental_child_seat_rate', 10, 2)->default(8.00);
            $table->decimal('rental_gps_rate', 10, 2)->default(5.00);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_pricings');
    }
};
