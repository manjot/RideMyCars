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
        Schema::table('rides', function (Blueprint $table) {
            if (!Schema::hasColumn('rides', 'driver_search_started_at')) {
                $table->timestamp('driver_search_started_at')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('rides', 'payment_held_at')) {
                $table->timestamp('payment_held_at')->nullable()->after('driver_search_started_at');
            }
            if (!Schema::hasColumn('rides', 'hold_payment_intent_id')) {
                $table->string('hold_payment_intent_id')->nullable()->after('payment_held_at');
            }
            if (!Schema::hasColumn('rides', 'hold_authorization_code')) {
                $table->string('hold_authorization_code')->nullable()->after('hold_payment_intent_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('rides', 'driver_search_started_at')) {
                $columnsToDrop[] = 'driver_search_started_at';
            }
            if (Schema::hasColumn('rides', 'payment_held_at')) {
                $columnsToDrop[] = 'payment_held_at';
            }
            if (Schema::hasColumn('rides', 'hold_payment_intent_id')) {
                $columnsToDrop[] = 'hold_payment_intent_id';
            }
            if (Schema::hasColumn('rides', 'hold_authorization_code')) {
                $columnsToDrop[] = 'hold_authorization_code';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
