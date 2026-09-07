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
        if (!Schema::hasTable('ride_categories')) {
            Schema::create('ride_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('icon')->nullable(); // Emoji like '🚗' or asset URL/path
                $table->string('capacity')->default('1–4 seats');
                $table->decimal('base_fare', 10, 2)->default(5.00);
                $table->decimal('per_km_rate', 10, 2)->default(1.50);
                $table->decimal('per_minute_rate', 10, 2)->default(0.25);
                $table->decimal('minimum_fare', 10, 2)->default(10.00);
                $table->decimal('multiplier', 5, 2)->default(1.00);
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_categories');
    }
};
