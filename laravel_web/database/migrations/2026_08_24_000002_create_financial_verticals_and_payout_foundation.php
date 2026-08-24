<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add fields to payment_transactions
        Schema::table('payment_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_transactions', 'service_vertical')) {
                $table->string('service_vertical')->default('RIDE_HAILING'); // RIDE_HAILING, DRIVER_HIRING, VEHICLE_RENTAL
            }
            if (!Schema::hasColumn('payment_transactions', 'vehicle_id')) {
                $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            }
            if (!Schema::hasColumn('payment_transactions', 'gross_amount')) {
                $table->decimal('gross_amount', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('payment_transactions', 'platform_fee')) {
                $table->decimal('platform_fee', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('payment_transactions', 'maintenance_fee')) {
                $table->decimal('maintenance_fee', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('payment_transactions', 'gateway_fee')) {
                $table->decimal('gateway_fee', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('payment_transactions', 'owner_share')) {
                $table->decimal('owner_share', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('payment_transactions', 'net_payout')) {
                $table->decimal('net_payout', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('payment_transactions', 'payout_status')) {
                $table->string('payout_status')->default('pending'); // pending, completed, failed, on_hold
            }
            if (!Schema::hasColumn('payment_transactions', 'escrow_status')) {
                $table->string('escrow_status')->default('none'); // none, held, released, partially_deducted, fully_deducted, refunded
            }
            if (!Schema::hasColumn('payment_transactions', 'escrow_amount')) {
                $table->decimal('escrow_amount', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('payment_transactions', 'escrow_refunded_amount')) {
                $table->decimal('escrow_refunded_amount', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('payment_transactions', 'escrow_deducted_amount')) {
                $table->decimal('escrow_deducted_amount', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('payment_transactions', 'gateway_fee_absorber')) {
                $table->string('gateway_fee_absorber')->default('fleet_owner'); // passenger, platform, fleet_owner
            }
            if (!Schema::hasColumn('payment_transactions', 'payout_failed_reason')) {
                $table->text('payout_failed_reason')->nullable();
            }
            if (!Schema::hasColumn('payment_transactions', 'payout_retry_count')) {
                $table->integer('payout_retry_count')->default(0);
            }
        });

        // 2. Add KYC and Target fields to driver_profiles
        Schema::table('driver_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_profiles', 'ghana_card_front_url')) {
                $table->string('ghana_card_front_url')->nullable();
            }
            if (!Schema::hasColumn('driver_profiles', 'ghana_card_back_url')) {
                $table->string('ghana_card_back_url')->nullable();
            }
            if (!Schema::hasColumn('driver_profiles', 'selfie_verification_status')) {
                $table->string('selfie_verification_status')->default('pending'); // pending, verified, rejected
            }
            if (!Schema::hasColumn('driver_profiles', 'daily_revenue_target')) {
                $table->decimal('daily_revenue_target', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('driver_profiles', 'consecutive_target_misses')) {
                $table->integer('consecutive_target_misses')->default(0);
            }
            if (!Schema::hasColumn('driver_profiles', 'is_banned')) {
                $table->boolean('is_banned')->default(false);
            }
        });

        // 3. Add buffer and mileage fields to driver_bookings & vehicles
        Schema::table('driver_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_bookings', 'escrow_deposit_amount')) {
                $table->decimal('escrow_deposit_amount', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('driver_bookings', 'escrow_status')) {
                $table->string('escrow_status')->default('none'); // none, held, released, partially_deducted, fully_deducted, refunded
            }
            if (!Schema::hasColumn('driver_bookings', 'escrow_damage_claim_notes')) {
                $table->text('escrow_damage_claim_notes')->nullable();
            }
            if (!Schema::hasColumn('driver_bookings', 'buffer_end_time')) {
                $table->timestamp('buffer_end_time')->nullable();
            }
            if (!Schema::hasColumn('driver_bookings', 'start_odometer')) {
                $table->integer('start_odometer')->nullable();
            }
            if (!Schema::hasColumn('driver_bookings', 'end_odometer')) {
                $table->integer('end_odometer')->nullable();
            }
            if (!Schema::hasColumn('driver_bookings', 'overage_mileage_fee')) {
                $table->decimal('overage_mileage_fee', 10, 2)->default(0.00);
            }
        });

        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'assigned_driver_id')) {
                $table->foreignId('assigned_driver_id')->nullable()->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('vehicles', 'security_deposit_amount')) {
                $table->decimal('security_deposit_amount', 10, 2)->default(200.00);
            }
            if (!Schema::hasColumn('vehicles', 'daily_mileage_limit')) {
                $table->integer('daily_mileage_limit')->default(200); // 200 km/miles per day
            }
            if (!Schema::hasColumn('vehicles', 'overage_fee_per_km')) {
                $table->decimal('overage_fee_per_km', 8, 2)->default(1.50);
            }
            if (!Schema::hasColumn('vehicles', 'status')) {
                $table->string('status')->default('active'); // active, booked, in_maintenance, idle, frozen
            }
        });

        // 4. Create owner_wallets table
        if (!Schema::hasTable('owner_wallets')) {
            Schema::create('owner_wallets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
                $table->decimal('ride_hailing_balance', 10, 2)->default(0.00);
                $table->decimal('driver_hiring_balance', 10, 2)->default(0.00);
                $table->decimal('vehicle_rental_balance', 10, 2)->default(0.00);
                $table->decimal('pending_payout_balance', 10, 2)->default(0.00);
                $table->decimal('total_withdrawn', 10, 2)->default(0.00);
                $table->timestamps();
            });
        }

        // 5. Create payout_ledgers table
        if (!Schema::hasTable('payout_ledgers')) {
            Schema::create('payout_ledgers', function (Blueprint $table) {
                $table->id();
                $table->string('payout_ref')->unique();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('payment_transaction_id')->nullable()->constrained('payment_transactions')->onDelete('set null');
                $table->string('service_vertical')->default('RIDE_HAILING');
                $table->decimal('gross_amount', 10, 2);
                $table->decimal('platform_fee', 10, 2)->default(0.00);
                $table->decimal('maintenance_fee', 10, 2)->default(0.00);
                $table->decimal('net_payout', 10, 2);
                $table->string('payout_method')->default('momo'); // momo, bank_transfer, expresspay
                $table->string('account_details')->nullable();
                $table->string('status')->default('pending'); // pending, completed, failed
                $table->text('failure_reason')->nullable();
                $table->integer('retry_count')->default(0);
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }

        // 6. Create guarantor_verifications table
        if (!Schema::hasTable('guarantor_verifications')) {
            Schema::create('guarantor_verifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('driver_profile_id')->constrained('driver_profiles')->onDelete('cascade');
                $table->string('full_name');
                $table->string('ghana_card_number')->nullable();
                $table->date('dob')->nullable();
                $table->string('relationship')->nullable();
                $table->string('primary_phone');
                $table->string('alt_phone')->nullable();
                $table->string('digital_address')->nullable();
                $table->string('physical_address')->nullable();
                $table->string('employer_business')->nullable();
                $table->string('job_title')->nullable();
                $table->string('workplace_address')->nullable();
                $table->string('ghana_card_front_url')->nullable();
                $table->string('ghana_card_back_url')->nullable();
                $table->string('signed_liability_agreement_url')->nullable();
                $table->string('status')->default('pending_additional_proof'); // pending_additional_proof, approved, rejected
                $table->text('admin_notes')->nullable();
                $table->timestamps();
            });
        }

        // 7. Create rental_inspections table
        if (!Schema::hasTable('rental_inspections')) {
            Schema::create('rental_inspections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('driver_booking_id')->constrained('driver_bookings')->onDelete('cascade');
                $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
                $table->string('inspection_type')->default('pre_rental'); // pre_rental, post_rental
                $table->string('front_photo_url')->nullable();
                $table->string('back_photo_url')->nullable();
                $table->string('left_photo_url')->nullable();
                $table->string('right_photo_url')->nullable();
                $table->string('dashboard_photo_url')->nullable();
                $table->string('fuel_gauge_photo_url')->nullable();
                $table->integer('odometer_reading')->nullable();
                $table->string('fuel_level')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('inspected_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('inspected_at')->nullable();
                $table->timestamps();
            });
        }

        // 8. Create driver_job_postings and applications tables
        if (!Schema::hasTable('driver_job_postings')) {
            Schema::create('driver_job_postings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('location')->default('Accra, Ghana');
                $table->string('shift_type')->default('Full-Time'); // Full-Time, Part-Time, Night Shift
                $table->decimal('daily_target_amount', 10, 2)->default(500.00);
                $table->string('status')->default('open'); // open, filled, closed
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('driver_job_applications')) {
            Schema::create('driver_job_applications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('job_posting_id')->constrained('driver_job_postings')->onDelete('cascade');
                $table->foreignId('driver_user_id')->constrained('users')->onDelete('cascade');
                $table->string('status')->default('applied'); // applied, interviewing, hired, rejected
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 9. Create rental_adjustments table
        if (!Schema::hasTable('rental_adjustments')) {
            Schema::create('rental_adjustments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('driver_booking_id')->constrained('driver_bookings')->onDelete('cascade');
                $table->foreignId('admin_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->date('original_end_date')->nullable();
                $table->date('new_end_date')->nullable();
                $table->decimal('original_total_price', 10, 2);
                $table->decimal('new_total_price', 10, 2);
                $table->decimal('original_platform_fee', 10, 2);
                $table->decimal('new_platform_fee', 10, 2);
                $table->decimal('original_owner_payout', 10, 2);
                $table->decimal('new_owner_payout', 10, 2);
                $table->text('adjustment_reason');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_adjustments');
        Schema::dropIfExists('driver_job_applications');
        Schema::dropIfExists('driver_job_postings');
        Schema::dropIfExists('rental_inspections');
        Schema::dropIfExists('guarantor_verifications');
        Schema::dropIfExists('payout_ledgers');
        Schema::dropIfExists('owner_wallets');
    }
};
