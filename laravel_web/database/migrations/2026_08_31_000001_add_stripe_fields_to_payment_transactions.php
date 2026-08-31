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
        Schema::table('payment_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_transactions', 'stripe_payment_intent_id')) {
                $table->string('stripe_payment_intent_id')->nullable()->index()->after('transaction_ref');
            }
            if (!Schema::hasColumn('payment_transactions', 'stripe_client_secret')) {
                $table->string('stripe_client_secret')->nullable()->after('stripe_payment_intent_id');
            }
            if (!Schema::hasColumn('payment_transactions', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('payment_transactions', 'stripe_payment_intent_id')) {
                $table->dropColumn('stripe_payment_intent_id');
            }
            if (Schema::hasColumn('payment_transactions', 'stripe_client_secret')) {
                $table->dropColumn('stripe_client_secret');
            }
            if (Schema::hasColumn('payment_transactions', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
        });
    }
};
