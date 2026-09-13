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
        Schema::table('driver_profiles', function (Blueprint $table) {
            // Vehicle Insurance picture scan and review fields
            if (!Schema::hasColumn('driver_profiles', 'vehicle_insurance_image')) {
                $table->string('vehicle_insurance_image')->nullable()->after('verification_notes');
            }
            if (!Schema::hasColumn('driver_profiles', 'vehicle_insurance_status')) {
                $table->string('vehicle_insurance_status')->default('not_submitted')->after('vehicle_insurance_image'); // not_submitted, submitted, under_review, approved, rejected
            }
            if (!Schema::hasColumn('driver_profiles', 'vehicle_insurance_expiry')) {
                $table->date('vehicle_insurance_expiry')->nullable()->after('vehicle_insurance_status');
            }
            if (!Schema::hasColumn('driver_profiles', 'vehicle_insurance_rejection_reason')) {
                $table->text('vehicle_insurance_rejection_reason')->nullable()->after('vehicle_insurance_expiry');
            }

            // Vehicle Fitness (Roadworthy) certificate picture scan and review fields
            if (!Schema::hasColumn('driver_profiles', 'vehicle_fitness_image')) {
                $table->string('vehicle_fitness_image')->nullable()->after('vehicle_insurance_rejection_reason');
            }
            if (!Schema::hasColumn('driver_profiles', 'vehicle_fitness_status')) {
                $table->string('vehicle_fitness_status')->default('not_submitted')->after('vehicle_fitness_image'); // not_submitted, submitted, under_review, approved, rejected
            }
            if (!Schema::hasColumn('driver_profiles', 'vehicle_fitness_expiry')) {
                $table->date('vehicle_fitness_expiry')->nullable()->after('vehicle_fitness_status');
            }
            if (!Schema::hasColumn('driver_profiles', 'vehicle_fitness_rejection_reason')) {
                $table->text('vehicle_fitness_rejection_reason')->nullable()->after('vehicle_fitness_expiry');
            }

            // Driver Live / Active status controlled by admin
            if (!Schema::hasColumn('driver_profiles', 'is_live')) {
                $table->boolean('is_live')->default(false)->after('vehicle_fitness_rejection_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_profiles', function (Blueprint $table) {
            $columns = [
                'vehicle_insurance_image',
                'vehicle_insurance_status',
                'vehicle_insurance_expiry',
                'vehicle_insurance_rejection_reason',
                'vehicle_fitness_image',
                'vehicle_fitness_status',
                'vehicle_fitness_expiry',
                'vehicle_fitness_rejection_reason',
                'is_live',
            ];

            $dropList = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('driver_profiles', $column)) {
                    $dropList[] = $column;
                }
            }

            if (!empty($dropList)) {
                $table->dropColumn($dropList);
            }
        });
    }
};
