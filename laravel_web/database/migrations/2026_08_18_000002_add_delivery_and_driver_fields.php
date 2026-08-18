<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_bookings', 'vehicle_source')) {
                $table->string('vehicle_source')->default('rental');
            }
        });

        Schema::table('rides', function (Blueprint $table) {
            if (!Schema::hasColumn('rides', 'signature_required')) {
                $table->boolean('signature_required')->default(false);
            }
            if (!Schema::hasColumn('rides', 'climate_control')) {
                $table->boolean('climate_control')->default(false);
            }
            if (!Schema::hasColumn('rides', 'discreet_packaging')) {
                $table->boolean('discreet_packaging')->default(false);
            }
            if (!Schema::hasColumn('rides', 'digital_receipt_code')) {
                $table->string('digital_receipt_code')->nullable();
            }
        });

        Schema::table('driver_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_profiles', 'photo_formality_status')) {
                $table->string('photo_formality_status')->default('pending');
            }
        });
    }

    public function down(): void
    {
        Schema::table('driver_bookings', function (Blueprint $table) {
            $table->dropColumn('vehicle_source');
        });

        Schema::table('rides', function (Blueprint $table) {
            $table->dropColumn([
                'signature_required',
                'climate_control',
                'discreet_packaging',
                'digital_receipt_code',
            ]);
        });

        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->dropColumn('photo_formality_status');
        });
    }
};
