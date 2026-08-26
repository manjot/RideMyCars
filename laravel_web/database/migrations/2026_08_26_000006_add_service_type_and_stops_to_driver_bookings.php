<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_bookings', 'service_type')) {
                $table->string('service_type')->default('Hire Driver')->after('service_category');
            }
            if (!Schema::hasColumn('driver_bookings', 'additional_stops')) {
                $table->text('additional_stops')->nullable()->after('dropoff_location');
            }
            if (!Schema::hasColumn('driver_bookings', 'preferred_gender')) {
                $table->string('preferred_gender')->nullable()->after('transmission');
            }
            if (!Schema::hasColumn('driver_bookings', 'preferred_language')) {
                $table->string('preferred_language')->nullable()->after('preferred_gender');
            }
        });
    }

    public function down(): void
    {
        Schema::table('driver_bookings', function (Blueprint $table) {
            $table->dropColumn(['service_type', 'additional_stops', 'preferred_gender', 'preferred_language']);
        });
    }
};
