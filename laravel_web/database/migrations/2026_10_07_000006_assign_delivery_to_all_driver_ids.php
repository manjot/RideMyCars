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
            $del = \App\Models\PackageDelivery::where('tracking_number', 'DEL-FJMSOPDG')
                ->orWhere('delivery_code', 'DEL-FJMSOPDG')
                ->orWhere('id', 8)
                ->first();

            if (!$del) {
                $del = \App\Models\PackageDelivery::whereIn('delivery_status', ['pending', 'created', 'searching'])
                    ->whereNull('courier_id')
                    ->latest()
                    ->first();
            }

            if (!$del) return;

            $del->update([
                'delivery_status' => 'pending',
                'courier_id' => null,
                'currency' => 'INR',
            ]);

            // Candidate driver IDs for the active device
            $targetDriverIds = [259, 258, 22];

            $shachishUser = \App\Models\User::where('email', 'like', '%shachisheh%')->orWhere('name', 'like', '%Shachish%')->get();
            foreach ($shachishUser as $u) {
                $targetDriverIds[] = $u->id;
            }
            $targetDriverIds = array_unique($targetDriverIds);

            foreach ($targetDriverIds as $dId) {
                \App\Models\RideAssignment::updateOrCreate(
                    [
                        'package_delivery_id' => $del->id,
                        'driver_id' => $dId,
                    ],
                    [
                        'ride_id' => null,
                        'driver_booking_id' => null,
                        'status' => 'pending',
                        'assignment_type' => 'primary',
                        'expires_at' => now()->addHours(3),
                    ]
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Migration 6 error: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
