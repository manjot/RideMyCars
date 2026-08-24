<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            if (!Schema::hasColumn('rides', 'ride_type')) {
                $table->string('ride_type')->default('ride'); // ride, delivery
            }
            if (!Schema::hasColumn('rides', 'merchant_account')) {
                $table->string('merchant_account')->nullable();
            }
            if (!Schema::hasColumn('rides', 'sender_name')) {
                $table->string('sender_name')->nullable();
            }
            if (!Schema::hasColumn('rides', 'sender_address')) {
                $table->string('sender_address')->nullable();
            }
            if (!Schema::hasColumn('rides', 'receiver_name')) {
                $table->string('receiver_name')->nullable();
            }
            if (!Schema::hasColumn('rides', 'receiver_phone')) {
                $table->string('receiver_phone')->nullable();
            }
            if (!Schema::hasColumn('rides', 'receiver_address')) {
                $table->string('receiver_address')->nullable();
            }
            if (!Schema::hasColumn('rides', 'pod_photo_url')) {
                $table->string('pod_photo_url')->nullable();
            }
            if (!Schema::hasColumn('rides', 'pod_signature_url')) {
                $table->string('pod_signature_url')->nullable();
            }
            if (!Schema::hasColumn('rides', 'pod_timestamp')) {
                $table->timestamp('pod_timestamp')->nullable();
            }
            if (!Schema::hasColumn('rides', 'pod_status')) {
                $table->string('pod_status')->default('pending'); // pending, verified, completed, disputed
            }
            if (!Schema::hasColumn('rides', 'current_lat')) {
                $table->decimal('current_lat', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('rides', 'current_lng')) {
                $table->decimal('current_lng', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('rides', 'pickup_lat')) {
                $table->decimal('pickup_lat', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('rides', 'pickup_lng')) {
                $table->decimal('pickup_lng', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('rides', 'dropoff_lat')) {
                $table->decimal('dropoff_lat', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('rides', 'dropoff_lng')) {
                $table->decimal('dropoff_lng', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('rides', 'estimated_minutes')) {
                $table->integer('estimated_minutes')->nullable();
            }
            if (!Schema::hasColumn('rides', 'is_delayed')) {
                $table->boolean('is_delayed')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dropColumn([
                'ride_type',
                'merchant_account',
                'sender_name',
                'sender_address',
                'receiver_name',
                'receiver_phone',
                'receiver_address',
                'pod_photo_url',
                'pod_signature_url',
                'pod_timestamp',
                'pod_status',
                'current_lat',
                'current_lng',
                'pickup_lat',
                'pickup_lng',
                'dropoff_lat',
                'dropoff_lng',
                'estimated_minutes',
                'is_delayed',
            ]);
        });
    }
};
