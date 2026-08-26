<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            if (!Schema::hasColumn('rides', 'pickup_lat')) {
                $table->decimal('pickup_lat', 10, 7)->nullable()->after('pickup_location');
            }
            if (!Schema::hasColumn('rides', 'pickup_lng')) {
                $table->decimal('pickup_lng', 10, 7)->nullable()->after('pickup_lat');
            }
            if (!Schema::hasColumn('rides', 'dropoff_lat')) {
                $table->decimal('dropoff_lat', 10, 7)->nullable()->after('dropoff_location');
            }
            if (!Schema::hasColumn('rides', 'dropoff_lng')) {
                $table->decimal('dropoff_lng', 10, 7)->nullable()->after('dropoff_lat');
            }
            if (!Schema::hasColumn('rides', 'distance_km')) {
                $table->decimal('distance_km', 8, 2)->nullable()->after('fare');
            }
            if (!Schema::hasColumn('rides', 'duration_minutes')) {
                $table->integer('duration_minutes')->nullable()->after('distance_km');
            }
            if (!Schema::hasColumn('rides', 'cancellation_reason')) {
                $table->string('cancellation_reason')->nullable()->after('status');
            }
        });

        Schema::table('driver_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_profiles', 'current_lat')) {
                $table->decimal('current_lat', 10, 7)->nullable()->after('service_area');
            }
            if (!Schema::hasColumn('driver_profiles', 'current_lng')) {
                $table->decimal('current_lng', 10, 7)->nullable()->after('current_lat');
            }
            if (!Schema::hasColumn('driver_profiles', 'last_location_update')) {
                $table->timestamp('last_location_update')->nullable()->after('current_lng');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dropColumn([
                'pickup_lat',
                'pickup_lng',
                'dropoff_lat',
                'dropoff_lng',
                'distance_km',
                'duration_minutes',
                'cancellation_reason',
            ]);
        });

        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'current_lat',
                'current_lng',
                'last_location_update',
            ]);
        });
    }
};
