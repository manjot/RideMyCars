<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('incentives')) {
            Schema::create('incentives', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->enum('type', ['daily', 'weekly', 'monthly'])->default('daily');
                
                // Location targeting
                $table->string('country')->index(); // Required
                $table->string('state')->nullable()->index();
                $table->string('city')->nullable()->index();
                $table->string('zone')->nullable()->index();
                
                // Vehicle type targeting
                // Bike, Auto, Car, SUV, Taxi, All Vehicles
                $table->string('vehicle_type')->default('All Vehicles')->index();
                
                // Targets array: [{"rides": 5, "reward": 100}, {"rides": 10, "reward": 250}, {"rides": 20, "reward": 500}]
                $table->json('targets')->nullable();
                
                // Schedule configuration
                // daily: 'every_day' or 'specific_date'
                // weekly: 'monday_sunday' or 'specific_week'
                // monthly: 'entire_month' or 'specific_month'
                $table->string('schedule_type')->default('every_day');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                
                // Status & Notifications
                $table->enum('status', ['active', 'inactive'])->default('active')->index();
                $table->boolean('notify_on_start')->default(true);
                $table->boolean('notify_on_reward')->default(true);
                
                $table->string('currency')->default('₹');
                
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('incentives');
    }
};
