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
                        $col = $table->string('verification_status')->default('pending_verification');
                        if (Schema::hasColumn($tableName, 'payment_status')) {
                            $col->after('payment_status');
                        } elseif (Schema::hasColumn($tableName, 'status')) {
                            $col->after('status');
                        }
                    }
                    if (!Schema::hasColumn($tableName, 'verified_by_driver_id')) {
                        $table->unsignedBigInteger('verified_by_driver_id')->nullable();
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
