<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add expires_at to rides table
        Schema::table('rides', function (Blueprint $table) {
            if (!Schema::hasColumn('rides', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('status')->index();
            }
        });

        // 2. Add expires_at and cancellation_reason to package_deliveries table
        Schema::table('package_deliveries', function (Blueprint $table) {
            if (!Schema::hasColumn('package_deliveries', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('delivery_status')->index();
            }
            if (!Schema::hasColumn('package_deliveries', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('delivery_status');
            }
        });

        // 3. Seed default dispatch waiting time settings into settings table
        if (Schema::hasTable('settings')) {
            $defaultSettings = [
                [
                    'key' => 'dispatch.ride_waiting_time_minutes',
                    'value' => '5',
                    'group' => 'Dispatch & Waiting Times',
                    'type' => 'number',
                    'label' => 'Ride Request Waiting Time (Minutes)',
                ],
                [
                    'key' => 'dispatch.delivery_waiting_time_minutes',
                    'value' => '5',
                    'group' => 'Dispatch & Waiting Times',
                    'type' => 'number',
                    'label' => 'Delivery Request Waiting Time (Minutes)',
                ],
                [
                    'key' => 'dispatch.auto_cancel_unaccepted',
                    'value' => '1',
                    'group' => 'Dispatch & Waiting Times',
                    'type' => 'boolean',
                    'label' => 'Auto-Cancel Unaccepted Requests When Expired',
                ],
            ];

            foreach ($defaultSettings as $setting) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $setting['key']],
                    array_merge($setting, [
                        'updated_at' => now(),
                        'created_at' => now(),
                    ])
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            if (Schema::hasColumn('rides', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });

        Schema::table('package_deliveries', function (Blueprint $table) {
            if (Schema::hasColumn('package_deliveries', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
            if (Schema::hasColumn('package_deliveries', 'cancellation_reason')) {
                $table->dropColumn('cancellation_reason');
            }
        });

        if (Schema::hasTable('settings')) {
            DB::table('settings')->whereIn('key', [
                'dispatch.ride_waiting_time_minutes',
                'dispatch.delivery_waiting_time_minutes',
                'dispatch.auto_cancel_unaccepted',
            ])->delete();
        }
    }
};
