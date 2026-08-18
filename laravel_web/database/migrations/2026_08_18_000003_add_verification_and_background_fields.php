<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_profiles', 'license_verified_at')) {
                $table->timestamp('license_verified_at')->nullable();
            }
            if (!Schema::hasColumn('driver_profiles', 'verification_provider')) {
                $table->string('verification_provider')->default('veriff');
            }
            if (!Schema::hasColumn('driver_profiles', 'background_check_status')) {
                $table->string('background_check_status')->default('pending');
            }
            if (!Schema::hasColumn('driver_profiles', 'background_check_provider')) {
                $table->string('background_check_provider')->default('checkr');
            }
            if (!Schema::hasColumn('driver_profiles', 'background_check_id')) {
                $table->string('background_check_id')->nullable();
            }
            if (!Schema::hasColumn('driver_profiles', 'background_checked_at')) {
                $table->timestamp('background_checked_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'license_verified_at',
                'verification_provider',
                'background_check_status',
                'background_check_provider',
                'background_check_id',
                'background_checked_at',
            ]);
        });
    }
};
