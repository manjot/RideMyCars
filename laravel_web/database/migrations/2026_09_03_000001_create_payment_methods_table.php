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
        if (!Schema::hasTable('payment_methods')) {
            Schema::create('payment_methods', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('provider')->default('stripe'); // stripe, paypal, etc.
                $table->string('provider_customer_id')->nullable();
                $table->string('provider_payment_method_id')->nullable()->index();
                $table->string('card_brand')->default('visa'); // visa, mastercard, amex, discover
                $table->string('card_last4', 4)->default('4242');
                $table->integer('expiry_month')->default(12);
                $table->integer('expiry_year')->default(2030);
                $table->string('cardholder_name')->nullable();
                $table->boolean('is_default')->default(false);
                $table->string('status')->default('active'); // active, inactive
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
