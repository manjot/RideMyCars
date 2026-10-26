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
        // 1. Add cash commission wallet & active chauffeur switch to driver_profiles
        Schema::table('driver_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_profiles', 'cash_commission_wallet')) {
                $table->decimal('cash_commission_wallet', 12, 2)->default(0.00)->after('is_chauffeur');
            }
            if (!Schema::hasColumn('driver_profiles', 'active_chauffeur_online')) {
                $table->boolean('active_chauffeur_online')->default(false)->after('cash_commission_wallet');
            }
            if (!Schema::hasColumn('driver_profiles', 'chauffeur_mode')) {
                $table->boolean('chauffeur_mode')->default(false)->after('active_chauffeur_online');
            }
        });

        // 2. Create driver_cash_commissions table to record platform commissions from trips paid in cash
        if (!Schema::hasTable('driver_cash_commissions')) {
            Schema::create('driver_cash_commissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
                $table->unsignedBigInteger('booking_id')->nullable();
                $table->string('service_type')->default('ride'); // ride, delivery, driver_hire, rental
                $table->string('trip_ref')->nullable();
                $table->decimal('gross_fare', 12, 2)->default(0.00);
                $table->decimal('commission_rate', 5, 2)->default(10.00); // percentage e.g. 10.00%
                $table->decimal('commission_amount', 12, 2)->default(0.00);
                $table->string('currency', 10)->default('₹');
                $table->timestamp('deducted_from_wallet_at')->nullable();
                $table->string('status')->default('deducted'); // deducted, pending, settled
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['driver_id', 'created_at']);
                $table->index('service_type');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_cash_commissions');

        Schema::table('driver_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('driver_profiles', 'cash_commission_wallet')) {
                $table->dropColumn('cash_commission_wallet');
            }
            if (Schema::hasColumn('driver_profiles', 'active_chauffeur_online')) {
                $table->dropColumn('active_chauffeur_online');
            }
            if (Schema::hasColumn('driver_profiles', 'chauffeur_mode')) {
                $table->dropColumn('chauffeur_mode');
            }
        });
    }
};
