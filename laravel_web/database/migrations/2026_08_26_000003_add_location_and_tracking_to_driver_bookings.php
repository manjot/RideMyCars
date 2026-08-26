<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_bookings', 'pickup_lat')) {
                $table->decimal('pickup_lat', 10, 7)->nullable()->after('pickup_location');
            }
            if (!Schema::hasColumn('driver_bookings', 'pickup_lng')) {
                $table->decimal('pickup_lng', 10, 7)->nullable()->after('pickup_lat');
            }
            if (!Schema::hasColumn('driver_bookings', 'dropoff_lat')) {
                $table->decimal('dropoff_lat', 10, 7)->nullable()->after('dropoff_location');
            }
            if (!Schema::hasColumn('driver_bookings', 'dropoff_lng')) {
                $table->decimal('dropoff_lng', 10, 7)->nullable()->after('dropoff_lat');
            }
            if (!Schema::hasColumn('driver_bookings', 'actual_distance_km')) {
                $table->decimal('actual_distance_km', 8, 2)->nullable()->after('total_price');
            }
            if (!Schema::hasColumn('driver_bookings', 'actual_duration_minutes')) {
                $table->integer('actual_duration_minutes')->nullable()->after('actual_distance_km');
            }
            if (!Schema::hasColumn('driver_bookings', 'final_fare')) {
                $table->decimal('final_fare', 10, 2)->nullable()->after('actual_duration_minutes');
            }
            if (!Schema::hasColumn('driver_bookings', 'arrived_at')) {
                $table->timestamp('arrived_at')->nullable()->after('booking_status');
            }
            if (!Schema::hasColumn('driver_bookings', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('arrived_at');
            }
            if (!Schema::hasColumn('driver_bookings', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }
            if (!Schema::hasColumn('driver_bookings', 'cancellation_reason')) {
                $table->string('cancellation_reason')->nullable()->after('completed_at');
            }
        });

        Schema::table('ride_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('ride_assignments', 'driver_booking_id')) {
                $table->foreignId('driver_booking_id')->nullable()->after('ride_id')->constrained('driver_bookings')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('driver_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'pickup_lat',
                'pickup_lng',
                'dropoff_lat',
                'dropoff_lng',
                'actual_distance_km',
                'actual_duration_minutes',
                'final_fare',
                'arrived_at',
                'started_at',
                'completed_at',
                'cancellation_reason',
            ]);
        });

        Schema::table('ride_assignments', function (Blueprint $table) {
            $table->dropForeign(['driver_booking_id']);
            $table->dropColumn('driver_booking_id');
        });
    }
};
