<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add wallet_balance to users table if not exists
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'wallet_balance')) {
                    $table->decimal('wallet_balance', 10, 2)->default(0.00)->after('email');
                }
            });
        }

        // 2. User Saved Payout Methods (Bank Accounts & Mobile Money)
        if (!Schema::hasTable('user_payout_methods')) {
            Schema::create('user_payout_methods', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('type')->default('bank_account'); // bank_account, momo
                $table->boolean('is_default')->default(false);
                // Bank fields
                $table->string('bank_name')->nullable();
                $table->string('account_holder_name')->nullable();
                $table->string('account_number')->nullable();
                $table->string('routing_code')->nullable(); // IFSC, Routing Number, Sort Code, SWIFT
                $table->string('branch_name')->nullable();
                // MoMo fields
                $table->string('momo_network')->nullable(); // MTN, Telecel, AirtelTigo, M-Pesa, etc.
                $table->string('momo_phone')->nullable();
                $table->string('momo_account_name')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'type']);
            });
        }

        // 3. Wallet Withdrawals Table
        if (!Schema::hasTable('wallet_withdrawals')) {
            Schema::create('wallet_withdrawals', function (Blueprint $table) {
                $table->id();
                $table->string('withdrawal_ref', 32)->unique(); // e.g. WTH-20260928-1A2B3C
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('user_type')->default('driver'); // driver, rider, owner, customer
                $table->decimal('amount', 10, 2);
                $table->string('currency', 8)->default('₹');
                $table->decimal('fee', 10, 2)->default(0.00);
                $table->decimal('net_amount', 10, 2);
                $table->string('payout_method')->default('bank_account'); // bank_account, momo
                $table->json('payout_details'); // complete snapshot of account details at request time
                $table->string('status')->default('pending'); // pending, approved, rejected
                $table->text('admin_notes')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->string('transaction_reference')->nullable(); // bank UTR, MoMo ID, reference #
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index('status');
                $table->index('created_at');
            });
        }

        // 4. Wallet Transactions Ledger Table (Universal credit/debit history)
        if (!Schema::hasTable('wallet_transactions')) {
            Schema::create('wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('transaction_ref', 32)->unique(); // e.g. WTX-20260928-XYZ89
                $table->string('type'); // withdrawal, incentive_bonus, ride_earning, top_up, refund, admin_adjustment
                $table->decimal('amount', 10, 2);
                $table->string('currency', 8)->default('₹');
                $table->decimal('balance_before', 10, 2)->default(0.00);
                $table->decimal('balance_after', 10, 2)->default(0.00);
                $table->string('description');
                $table->string('status')->default('completed'); // completed, pending, failed, cancelled
                $table->unsignedBigInteger('reference_id')->nullable(); // withdrawal id, ride id, etc.
                $table->string('reference_type')->nullable(); // WalletWithdrawal, Ride, etc.
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'type']);
                $table->index(['user_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallet_withdrawals');
        Schema::dropIfExists('user_payout_methods');

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'wallet_balance')) {
                    $table->dropColumn('wallet_balance');
                }
            });
        }
    }
};
