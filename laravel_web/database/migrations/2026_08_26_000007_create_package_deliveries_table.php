<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_code')->unique();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('courier_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('courier_profile_id')->nullable()->constrained('driver_profiles')->onDelete('set null');

            // Routing
            $table->string('pickup_location');
            $table->decimal('pickup_lat', 10, 7)->nullable();
            $table->decimal('pickup_lng', 10, 7)->nullable();
            $table->string('dropoff_location');
            $table->decimal('dropoff_lat', 10, 7)->nullable();
            $table->decimal('dropoff_lng', 10, 7)->nullable();

            // Service & Schedule
            $table->string('delivery_type')->default('Instant'); // Instant, Same Day, Express, Scheduled, Hyperlocal
            $table->string('schedule_mode')->default('now'); // now, later
            $table->date('pickup_date')->nullable();
            $table->time('pickup_time')->nullable();

            // Sender & Recipient
            $table->string('sender_name');
            $table->string('sender_phone');
            $table->string('sender_address')->nullable();
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->string('recipient_address')->nullable();
            $table->text('delivery_instructions')->nullable();

            // Package Details
            $table->string('package_category')->default('Documents'); // Documents, Clothing, Electronics, Household items, Office supplies, Personal belongings, Other
            $table->string('package_description')->nullable();
            $table->string('package_size')->default('Small'); // Small, Medium, Large
            $table->decimal('package_weight_kg', 8, 2)->default(1.00);
            $table->integer('quantity')->default(1);
            $table->decimal('declared_value', 10, 2)->default(0.00);
            $table->text('special_handling')->nullable(); // JSON string e.g. ["signature_required", "climate_control"]

            // OTP Verification
            $table->string('delivery_otp', 6)->nullable();

            // Status & Pricing
            $table->string('delivery_status')->default('pending'); // pending, searching, courier_assigned, courier_accepted, going_to_pickup, arrived_at_pickup, parcel_picked_up, in_transit, arrived_at_destination, delivered, cancelled
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('service_fee', 10, 2)->default(0.00);
            $table->decimal('tax', 10, 2)->default(0.00);
            $table->decimal('total_price', 10, 2)->default(0.00);
            $table->string('currency', 10)->default('USD');
            $table->string('payment_method')->default('stripe');
            $table->string('payment_status')->default('pending');

            // Proof of Delivery
            $table->string('pod_photo_url')->nullable();
            $table->string('pod_signature_url')->nullable();
            $table->timestamp('pod_timestamp')->nullable();
            $table->string('pod_status')->default('pending');

            // Timestamps
            $table->timestamp('arrived_at_pickup_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('arrived_at_destination_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::table('ride_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('ride_assignments', 'package_delivery_id')) {
                $table->foreignId('package_delivery_id')->nullable()->after('driver_booking_id')->constrained('package_deliveries')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ride_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('ride_assignments', 'package_delivery_id')) {
                $table->dropForeign(['package_delivery_id']);
                $table->dropColumn('package_delivery_id');
            }
        });

        Schema::dropIfExists('package_deliveries');
    }
};
