<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Clean up package deliveries in Ghana so they have currency = 'GHS'
        if (Schema::hasTable('package_deliveries')) {
            DB::table('package_deliveries')
                ->where(function ($q) {
                    $q->where('pickup_location', 'like', '%Ghana%')
                      ->orWhere('pickup_location', 'like', '%mallam%')
                      ->orWhere('pickup_location', 'like', '%Weija%')
                      ->orWhere('pickup_location', 'like', '%KB Lodge%')
                      ->orWhere('dropoff_location', 'like', '%Ghana%')
                      ->orWhere('dropoff_location', 'like', '%West Hills%')
                      ->orWhere('dropoff_location', 'like', '%mallam%')
                      ->orWhere(function ($sub) {
                          $sub->whereBetween('pickup_lat', [4.0, 12.0])
                              ->whereBetween('pickup_lng', [-4.0, 2.0]);
                      });
                })
                ->update(['currency' => 'GHS']);
        }

        // 2. Cancel any pending assignments for Ghana deliveries or rides assigned to Indian drivers
        if (Schema::hasTable('ride_assignments')) {
            // Expire / cancel pending assignments for Ghana package deliveries
            $ghanaDeliveryIds = DB::table('package_deliveries')
                ->where('currency', 'GHS')
                ->orWhere('pickup_location', 'like', '%Ghana%')
                ->orWhere('pickup_location', 'like', '%mallam%')
                ->pluck('id')
                ->toArray();

            if (!empty($ghanaDeliveryIds)) {
                DB::table('ride_assignments')
                    ->whereIn('package_delivery_id', $ghanaDeliveryIds)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'cancelled',
                        'expires_at' => now()->subMinute(),
                    ]);
            }

            // Expire / cancel pending assignments for Ghana rides
            $ghanaRideIds = DB::table('rides')
                ->where('driver_country', 'GHA')
                ->orWhere('pickup_location', 'like', '%Ghana%')
                ->pluck('id')
                ->toArray();

            if (!empty($ghanaRideIds)) {
                DB::table('ride_assignments')
                    ->whereIn('ride_id', $ghanaRideIds)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'cancelled',
                        'expires_at' => now()->subMinute(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // No-op for cleanup
    }
};
