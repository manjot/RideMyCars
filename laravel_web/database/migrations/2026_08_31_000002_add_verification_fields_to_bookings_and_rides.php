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
        $tables = ['driver_bookings', 'rides', 'package_deliveries', 'payment_transactions'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'verification_status')) {
                        $table->string('verification_status')->default('pending_verification')->after('payment_status');
                    }
                    if (!Schema::hasColumn($tableName, 'verified_by_driver_id')) {
                        $table->foreignId('verified_by_driver_id')->nullable()->constrained('users')->onDelete('set null');
                    }
                    if (!Schema::hasColumn($tableName, 'verified_at')) {
                        $table->timestamp('verified_at')->nullable();
                    }
                    if (!Schema::hasColumn($tableName, 'rejection_reason')) {
                        $table->text('rejection_reason')->nullable();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['driver_bookings', 'rides', 'package_deliveries', 'payment_transactions'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'verification_status')) {
                        $table->dropColumn(['verification_status', 'verified_by_driver_id', 'verified_at', 'rejection_reason']);
                    }
                });
            }
        }
    }
};
