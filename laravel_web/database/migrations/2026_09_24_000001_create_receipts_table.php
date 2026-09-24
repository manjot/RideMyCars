<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('receipts')) {
            Schema::create('receipts', function (Blueprint $table) {
                $table->id();
                $table->string('receipt_number')->unique();
                $table->string('booking_type'); // 'ride', 'rental', 'driver_booking', 'delivery'
                $table->unsignedBigInteger('booking_id');
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
                
                // Financials
                $table->string('currency', 10)->default('USD');
                $table->decimal('subtotal', 10, 2)->default(0.00);
                $table->decimal('discount_amount', 10, 2)->default(0.00);
                $table->decimal('tax_amount', 10, 2)->default(0.00);
                $table->decimal('fee_amount', 10, 2)->default(0.00);
                $table->decimal('total_amount', 10, 2)->default(0.00);
                $table->string('payment_method')->default('Cash');
                $table->string('payment_status')->default('paid');

                // PDF storage
                $table->string('pdf_path')->nullable();
                $table->string('verification_token', 64)->unique();

                // Email dispatch
                $table->string('sent_to_email')->nullable();
                $table->timestamp('emailed_at')->nullable();
                $table->string('email_status')->default('pending'); // pending, sent, failed

                // Rich snapshot data
                $table->json('snapshot_data')->nullable();

                $table->timestamps();

                $table->index(['booking_type', 'booking_id']);
                $table->index(['user_id', 'created_at']);
            });
        }

        if (Schema::hasTable('rides') && !Schema::hasColumn('rides', 'receipt_id')) {
            Schema::table('rides', function (Blueprint $table) {
                $table->foreignId('receipt_id')->nullable()->after('payment_status')->constrained('receipts')->nullOnDelete();
            });
        }

        if (Schema::hasTable('driver_bookings') && !Schema::hasColumn('driver_bookings', 'receipt_id')) {
            Schema::table('driver_bookings', function (Blueprint $table) {
                $table->foreignId('receipt_id')->nullable()->after('payment_status')->constrained('receipts')->nullOnDelete();
            });
        }

        if (Schema::hasTable('package_deliveries') && !Schema::hasColumn('package_deliveries', 'receipt_id')) {
            Schema::table('package_deliveries', function (Blueprint $table) {
                $table->foreignId('receipt_id')->nullable()->after('payment_status')->constrained('receipts')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rides') && Schema::hasColumn('rides', 'receipt_id')) {
            Schema::table('rides', function (Blueprint $table) {
                $table->dropForeign(['receipt_id']);
                $table->dropColumn('receipt_id');
            });
        }

        if (Schema::hasTable('driver_bookings') && Schema::hasColumn('driver_bookings', 'receipt_id')) {
            Schema::table('driver_bookings', function (Blueprint $table) {
                $table->dropForeign(['receipt_id']);
                $table->dropColumn('receipt_id');
            });
        }

        if (Schema::hasTable('package_deliveries') && Schema::hasColumn('package_deliveries', 'receipt_id')) {
            Schema::table('package_deliveries', function (Blueprint $table) {
                $table->dropForeign(['receipt_id']);
                $table->dropColumn('receipt_id');
            });
        }

        Schema::dropIfExists('receipts');
    }
};
