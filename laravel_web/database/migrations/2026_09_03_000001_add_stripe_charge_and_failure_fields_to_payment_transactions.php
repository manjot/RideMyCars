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
            if (!Schema::hasColumn('payment_transactions', 'stripe_charge_id')) {
                $table->string('stripe_charge_id')->nullable()->after('stripe_client_secret');
            }
            if (!Schema::hasColumn('payment_transactions', 'failure_code')) {
                $table->string('failure_code')->nullable()->after('status');
            }
            if (!Schema::hasColumn('payment_transactions', 'failure_message')) {
                $table->text('failure_message')->nullable()->after('failure_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('payment_transactions', 'stripe_charge_id')) {
                $table->dropColumn('stripe_charge_id');
            }
            if (Schema::hasColumn('payment_transactions', 'failure_code')) {
                $table->dropColumn('failure_code');
            }
            if (Schema::hasColumn('payment_transactions', 'failure_message')) {
                $table->dropColumn('failure_message');
            }
        });
    }
};
