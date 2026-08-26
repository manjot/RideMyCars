<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_transactions', 'package_delivery_id')) {
                $table->foreignId('package_delivery_id')->nullable()->after('driver_booking_id')->constrained('package_deliveries')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('payment_transactions', 'package_delivery_id')) {
                $table->dropForeign(['package_delivery_id']);
                $table->dropColumn('package_delivery_id');
            }
        });
    }
};
