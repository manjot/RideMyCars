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
        Schema::table('rides', function (Blueprint $table) {
            if (!Schema::hasColumn('rides', 'start_pin')) {
                $table->string('start_pin', 6)->nullable()->after('status');
            }
        });

        // Backfill existing active rides with a 4-digit PIN if missing
        try {
            \App\Models\Ride::whereNull('start_pin')
                ->chunkById(100, function ($rides) {
                    foreach ($rides as $ride) {
                        $pin = str_pad((string) rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                        $ride->update(['start_pin' => $pin]);
                    }
                });
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            if (Schema::hasColumn('rides', 'start_pin')) {
                $table->dropColumn('start_pin');
            }
        });
    }
};
