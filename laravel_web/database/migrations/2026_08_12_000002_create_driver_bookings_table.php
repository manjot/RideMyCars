<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('driver_profile_id')->nullable()->constrained('driver_profiles')->onDelete('set null');
            $table->string('service_category')->default('private'); // private, commercial
            $table->string('country')->default('USA');
            
            // Private category vehicle details
            $table->string('car_type')->nullable(); // Sedan, SUV, Luxury, Van, Truck
            $table->string('car_make_model')->nullable();
            $table->string('manufacturing_year')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('transmission')->default('automatic'); // automatic, manual
            
            // Commercial category details
            $table->string('commercial_service_type')->nullable(); // cargo, passenger_shuttle, vip_escort, heavy_vehicle
            $table->text('cargo_details')->nullable();
            
            // Location & Schedule
            $table->string('pickup_location');
            $table->string('dropoff_location')->nullable();
            $table->date('start_date');
            $table->time('start_time');
            $table->string('duration_type')->default('hourly'); // hourly, daily, weekly
            $table->integer('duration_count')->default(1);
            
            // Pricing Breakdown
            $table->decimal('hourly_rate', 8, 2)->default(0.00);
            $table->decimal('daily_rate', 8, 2)->default(0.00);
            $table->decimal('weekly_rate', 8, 2)->default(0.00);
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('service_fee', 10, 2)->default(0.00);
            $table->decimal('tax', 10, 2)->default(0.00);
            $table->decimal('total_price', 10, 2)->default(0.00);
            $table->string('currency')->default('USD');
            
            // Statuses
            $table->string('payment_method')->nullable(); // card, paypal, cashapp, applepay, momo, bank_transfer, cash
            $table->string('payment_status')->default('pending'); // pending, processing, paid, failed, cancelled, refunded
            $table->string('booking_status')->default('pending'); // pending, accepted, in_progress, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_bookings');
    }
};
