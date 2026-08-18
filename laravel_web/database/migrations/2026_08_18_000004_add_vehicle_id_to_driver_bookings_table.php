<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_bookings', 'vehicle_id')) {
                $table->foreignId('vehicle_id')->nullable()->after('id')->constrained('vehicles')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('driver_bookings', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn('vehicle_id');
        });
    }
};
