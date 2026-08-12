<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_ref')->unique();
            $table->foreignId('driver_booking_id')->nullable()->constrained('driver_bookings')->onDelete('cascade');
            $table->foreignId('ride_id')->nullable()->constrained('rides')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('country')->default('USA');
            $table->string('currency')->default('USD');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // card, paypal, cashapp, applepay, momo, bank_transfer, cash
            $table->string('provider')->nullable();
            $table->string('status')->default('pending'); // pending, processing, paid, failed, cancelled, refunded
            $table->json('gateway_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
