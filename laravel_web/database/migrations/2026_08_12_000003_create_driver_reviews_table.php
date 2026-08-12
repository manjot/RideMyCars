<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_booking_id')->constrained('driver_bookings')->onDelete('cascade');
            $table->foreignId('driver_profile_id')->constrained('driver_profiles')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->unsignedTinyInteger('rating'); // 1 to 5
            $table->text('review_text')->nullable();
            $table->timestamps();

            $table->unique('driver_booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_reviews');
    }
};
