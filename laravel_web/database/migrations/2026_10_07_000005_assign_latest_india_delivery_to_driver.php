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
        try {
            $driver = \App\Models\User::where('email', 'shachisheh@gmail.com')->first();
            if (!$driver) return;

            // Find all pending package deliveries in India or without courier
            $deliveries = \App\Models\PackageDelivery::whereIn('delivery_status', ['pending', 'created', 'searching'])
                ->whereNull('courier_id')
                ->latest()
                ->take(5)
                ->get();

            foreach ($deliveries as $del) {
                // Ensure currency is INR if not set
                if (empty($del->currency) || $del->currency === 'USD') {
                    $del->update(['currency' => 'INR']);
                }

                \App\Models\RideAssignment::updateOrCreate(
                    [
                        'package_delivery_id' => $del->id,
                        'driver_id' => $driver->id,
                    ],
                    [
                        'ride_id' => null,
                        'driver_booking_id' => null,
                        'status' => 'pending',
                        'assignment_type' => 'primary',
                        'expires_at' => now()->addHours(2),
                    ]
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Migration assign delivery error: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
