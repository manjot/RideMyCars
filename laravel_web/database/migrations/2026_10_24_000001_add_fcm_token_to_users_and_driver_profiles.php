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
        if (Schema::hasTable('driver_profiles')) {
            Schema::table('driver_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('driver_profiles', 'fcm_token')) {
                    $table->string('fcm_token', 512)->nullable()->after('is_live');
                }
                if (!Schema::hasColumn('driver_profiles', 'device_type')) {
                    $table->string('device_type', 32)->nullable()->after('fcm_token'); // android, ios, web
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'fcm_token')) {
                    $table->string('fcm_token', 512)->nullable()->after('remember_token');
                }
                if (!Schema::hasColumn('users', 'device_type')) {
                    $table->string('device_type', 32)->nullable()->after('fcm_token');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('driver_profiles')) {
            Schema::table('driver_profiles', function (Blueprint $table) {
                if (Schema::hasColumn('driver_profiles', 'device_type')) {
                    $table->dropColumn('device_type');
                }
                if (Schema::hasColumn('driver_profiles', 'fcm_token')) {
                    $table->dropColumn('fcm_token');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'device_type')) {
                    $table->dropColumn('device_type');
                }
                if (Schema::hasColumn('users', 'fcm_token')) {
                    $table->dropColumn('fcm_token');
                }
            });
        }
    }
};
