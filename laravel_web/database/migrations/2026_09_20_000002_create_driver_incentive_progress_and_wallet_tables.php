<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure driver_profiles has state, city, zone, and vehicle_type
        if (Schema::hasTable('driver_profiles')) {
            Schema::table('driver_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('driver_profiles', 'state')) {
                    $table->string('state')->nullable()->after('country');
                }
                if (!Schema::hasColumn('driver_profiles', 'city')) {
                    $table->string('city')->nullable()->after('state');
                }
                if (!Schema::hasColumn('driver_profiles', 'zone')) {
                    $table->string('zone')->nullable()->after('city');
                }
                if (!Schema::hasColumn('driver_profiles', 'vehicle_type')) {
                    $table->string('vehicle_type')->nullable()->after('zone');
                }
            });
        }

        // 2. Driver Incentive Progress
        if (!Schema::hasTable('driver_incentive_progress')) {
            Schema::create('driver_incentive_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('incentive_id')->constrained('incentives')->onDelete('cascade');
                $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
                $table->string('period_key')->index(); // e.g. '2026-09-19', '2026-W38', '2026-09'
                $table->integer('completed_rides_count')->default(0);
                $table->json('achieved_milestones')->nullable(); // e.g. [5, 10, 20]
                $table->decimal('total_reward_earned', 10, 2)->default(0.00);
                $table->enum('status', ['in_progress', 'completed', 'missed'])->default('in_progress')->index();
                $table->foreignId('last_ride_id')->nullable()->constrained('rides')->nullOnDelete();
                $table->timestamps();

                $table->unique(['incentive_id', 'driver_id', 'period_key'], 'uniq_incentive_driver_period');
            });
        }

        // 3. Driver Wallet Transactions
        if (!Schema::hasTable('driver_wallet_transactions')) {
            Schema::create('driver_wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('incentive_id')->nullable()->constrained('incentives')->nullOnDelete();
                $table->string('transaction_ref')->unique();
                $table->integer('milestone_rides')->nullable();
                $table->string('type')->default('incentive_bonus');
                $table->decimal('amount', 10, 2);
                $table->string('currency')->default('₹');
                $table->string('description');
                $table->string('status')->default('credited');
                $table->timestamp('credited_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_wallet_transactions');
        Schema::dropIfExists('driver_incentive_progress');

        if (Schema::hasTable('driver_profiles')) {
            Schema::table('driver_profiles', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('driver_profiles', 'state')) $columns[] = 'state';
                if (Schema::hasColumn('driver_profiles', 'city')) $columns[] = 'city';
                if (Schema::hasColumn('driver_profiles', 'zone')) $columns[] = 'zone';
                if (Schema::hasColumn('driver_profiles', 'vehicle_type')) $columns[] = 'vehicle_type';
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
