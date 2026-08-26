<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            if (!Schema::hasColumn('rides', 'pickup_date')) {
                $table->date('pickup_date')->nullable()->after('dropoff_location');
            }
            if (!Schema::hasColumn('rides', 'pickup_time')) {
                $table->string('pickup_time')->nullable()->after('pickup_date');
            }
            if (!Schema::hasColumn('rides', 'total_amount')) {
                $table->decimal('total_amount', 10, 2)->nullable()->after('fare');
            }
            if (!Schema::hasColumn('rides', 'paid_amount')) {
                $table->decimal('paid_amount', 10, 2)->nullable()->after('total_amount');
            }
            if (!Schema::hasColumn('rides', 'remaining_balance')) {
                $table->decimal('remaining_balance', 10, 2)->nullable()->after('paid_amount');
            }
            if (!Schema::hasColumn('rides', 'payment_status')) {
                $table->string('payment_status')->default('unpaid')->after('remaining_balance');
            }
            if (!Schema::hasColumn('rides', 'insurance_accepted')) {
                $table->boolean('insurance_accepted')->default(false)->after('payment_status');
            }
            if (!Schema::hasColumn('rides', 'fuel_policy')) {
                $table->string('fuel_policy')->default('Full-to-Full')->after('insurance_accepted');
            }
            if (!Schema::hasColumn('rides', 'customer_age')) {
                $table->integer('customer_age')->nullable()->after('fuel_policy');
            }
        });

        if (!Schema::hasTable('ride_stops')) {
            Schema::create('ride_stops', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ride_id')->constrained('rides')->onDelete('cascade');
                $table->integer('stop_order')->default(1);
                $table->string('location');
                $table->decimal('lat', 10, 7)->nullable();
                $table->decimal('lng', 10, 7)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ride_stops');

        Schema::table('rides', function (Blueprint $table) {
            $table->dropColumn([
                'pickup_date',
                'pickup_time',
                'total_amount',
                'paid_amount',
                'remaining_balance',
                'payment_status',
                'insurance_accepted',
                'fuel_policy',
                'customer_age',
            ]);
        });
    }
};
