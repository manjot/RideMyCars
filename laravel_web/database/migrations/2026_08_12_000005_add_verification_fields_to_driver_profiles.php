<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->integer('experience_years')->default(1)->after('hourly_rate');
            $table->decimal('daily_rate', 8, 2)->nullable()->after('experience_years');
            $table->decimal('weekly_rate', 8, 2)->nullable()->after('daily_rate');
            $table->string('country')->default('USA')->after('weekly_rate');
            $table->string('service_area')->nullable()->after('country');
            
            // License Verification
            $table->string('license_country')->nullable()->after('license_number');
            $table->date('license_expiry')->nullable()->after('license_country');
            $table->string('license_front_image')->nullable()->after('license_expiry');
            $table->string('license_back_image')->nullable()->after('license_front_image');
            $table->string('verification_status')->default('not_submitted')->after('license_back_image'); // not_submitted, submitted, under_review, verified, rejected, expired
            $table->text('verification_notes')->nullable()->after('verification_status');
            $table->integer('total_trips')->default(0)->after('rating');
        });
    }

    public function down(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'experience_years', 'daily_rate', 'weekly_rate', 'country', 'service_area',
                'license_country', 'license_expiry', 'license_front_image', 'license_back_image',
                'verification_status', 'verification_notes', 'total_trips'
            ]);
        });
    }
};
