<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'transmission')) {
                $table->string('transmission')->default('automatic')->after('type');
            }
            if (!Schema::hasColumn('vehicles', 'fuel_type')) {
                $table->string('fuel_type')->default('petrol')->after('transmission');
            }
            if (!Schema::hasColumn('vehicles', 'seats')) {
                $table->integer('seats')->default(5)->after('fuel_type');
            }
            if (!Schema::hasColumn('vehicles', 'luggage')) {
                $table->integer('luggage')->default(2)->after('seats');
            }
            if (!Schema::hasColumn('vehicles', 'doors')) {
                $table->integer('doors')->default(4)->after('luggage');
            }
            if (!Schema::hasColumn('vehicles', 'mileage_policy')) {
                $table->string('mileage_policy')->default('unlimited')->after('doors');
            }
            if (!Schema::hasColumn('vehicles', 'fuel_policy')) {
                $table->string('fuel_policy')->default('Full-to-Full')->after('mileage_policy');
            }
            if (!Schema::hasColumn('vehicles', 'min_driver_age')) {
                $table->integer('min_driver_age')->default(18)->after('fuel_policy');
            }
            if (!Schema::hasColumn('vehicles', 'category')) {
                $table->string('category')->nullable()->after('min_driver_age');
            }
        });

        Schema::table('rides', function (Blueprint $table) {
            if (!Schema::hasColumn('rides', 'vehicle_id')) {
                $table->foreignId('vehicle_id')->nullable()->after('rider_id')->constrained('vehicles')->nullOnDelete();
            }
            if (!Schema::hasColumn('rides', 'return_date')) {
                $table->date('return_date')->nullable()->after('pickup_time');
            }
            if (!Schema::hasColumn('rides', 'return_time')) {
                $table->string('return_time')->nullable()->after('return_date');
            }
            if (!Schema::hasColumn('rides', 'different_dropoff')) {
                $table->boolean('different_dropoff')->default(false)->after('return_time');
            }
            if (!Schema::hasColumn('rides', 'driver_country')) {
                $table->string('driver_country')->nullable()->after('customer_age');
            }
            if (!Schema::hasColumn('rides', 'driver_email')) {
                $table->string('driver_email')->nullable()->after('driver_country');
            }
            if (!Schema::hasColumn('rides', 'driver_phone')) {
                $table->string('driver_phone')->nullable()->after('driver_email');
            }
            if (!Schema::hasColumn('rides', 'protection_option')) {
                $table->string('protection_option')->default('basic')->after('insurance_accepted');
            }
            if (!Schema::hasColumn('rides', 'protection_fee')) {
                $table->decimal('protection_fee', 10, 2)->default(0.00)->after('protection_option');
            }
            if (!Schema::hasColumn('rides', 'selected_extras')) {
                $table->text('selected_extras')->nullable()->after('protection_fee');
            }
            if (!Schema::hasColumn('rides', 'extras_fee')) {
                $table->decimal('extras_fee', 10, 2)->default(0.00)->after('selected_extras');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'transmission',
                'fuel_type',
                'seats',
                'luggage',
                'doors',
                'mileage_policy',
                'fuel_policy',
                'min_driver_age',
                'category',
            ]);
        });

        Schema::table('rides', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn([
                'vehicle_id',
                'return_date',
                'return_time',
                'different_dropoff',
                'driver_country',
                'driver_email',
                'driver_phone',
                'protection_option',
                'protection_fee',
                'selected_extras',
                'extras_fee',
            ]);
        });
    }
};
