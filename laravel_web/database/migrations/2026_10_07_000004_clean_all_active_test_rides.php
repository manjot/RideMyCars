<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rides')) {
            DB::table('rides')
                ->whereIn('status', ['pending', 'accepted', 'en_route', 'arrived', 'in_progress'])
                ->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => 'Clean state for live testing',
                    'updated_at' => now(),
                ]);
        }
        if (Schema::hasTable('ride_assignments')) {
            DB::table('ride_assignments')
                ->whereIn('status', ['pending', 'accepted', 'dispatching'])
                ->update([
                    'status' => 'cancelled',
                    'updated_at' => now(),
                ]);
        }
        if (Schema::hasTable('driver_bookings')) {
            DB::table('driver_bookings')
                ->whereIn('booking_status', ['pending', 'confirmed', 'in_progress'])
                ->update([
                    'booking_status' => 'cancelled',
                    'updated_at' => now(),
                ]);
        }
        if (Schema::hasTable('package_deliveries')) {
            DB::table('package_deliveries')
                ->whereIn('delivery_status', ['pending', 'assigned', 'picked_up', 'in_transit'])
                ->update([
                    'delivery_status' => 'cancelled',
                    'updated_at' => now(),
                ]);
        }
        if (Schema::hasTable('driver_profiles')) {
            DB::table('driver_profiles')->update([
                'is_available' => true,
                'is_live' => true,
            ]);
        }
    }

    public function down(): void
    {
    }
};
