<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Models\UserPayoutMethod;
use App\Models\WalletTransaction;
use App\Models\WalletWithdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    /**
     * Get settings for withdrawals.
     */
    public static function getSettings(): array
    {
        $enabled = (bool) SettingService::get('wallet.withdrawals_enabled', true);
        $min = (float) SettingService::get('wallet.min_withdrawal_amount', 50.00);
        $max = (float) SettingService::get('wallet.max_withdrawal_amount', 10000.00);
        $feeType = SettingService::get('wallet.withdrawal_fee_type', 'fixed');
        $feeVal = (float) SettingService::get('wallet.withdrawal_fee_value', 0.00);
        $methods = explode(',', SettingService::get('wallet.allowed_payout_methods', 'bank_account,momo,stripe,card,paypal,cashapp,zelle'));

        return [
            'enabled' => $enabled,
            'withdrawals_enabled' => $enabled,
            'min_amount' => $min,
            'min_withdrawal' => $min,
            'max_amount' => $max,
            'max_withdrawal' => $max,
            'fee_type' => $feeType,
            'withdrawal_fee_type' => $feeType,
            'fee_value' => $feeVal,
            'withdrawal_fee' => $feeVal,
            'allowed_methods' => $methods,
            'allowed_payout_methods' => $methods,
        ];
    }

    /**
     * Determine preferred currency symbol for a user.
     */
    public static function getUserCurrency(User $user): string
    {
        $country = strtoupper(trim($user->country ?? ''));
        return match ($country) {
            'IN', 'IND', 'INDIA' => '₹',
            'GH', 'GHA', 'GHANA' => 'GH₵',
            'GB', 'GBR', 'UK' => '£',
            'EU', 'EUR' => '€',
            default => '$',
        };
    }

    /**
     * Reconcile and settle a driver's Cash Commission Wallet and Main Wallet Balance.
     * Ensures deductions take effect in Cash Commission Wallet and balances are whole numbers.
     */
    public static function reconcileDriverCashCommissionWallet(User $user): void
    {
        try {
            $profile = $user->driverProfile;
            $rawMain = (float) ($user->wallet_balance ?? 0.0);
            $rawComm = (float) ($profile?->cash_commission_wallet ?? 0.0);

            if (!$profile) {
                $roundedMain = max(0.0, round($rawMain));
                if (abs($rawMain - $roundedMain) > 0.001) {
                    $user->wallet_balance = $roundedMain;
                    $user->save();
                }
                return;
            }

            // 1. If main wallet went negative under legacy logic, convert negative balance into outstanding cash_commission_wallet
            if ($rawMain < 0) {
                $outstanding = round(abs($rawMain));
                $user->wallet_balance = 0;
                $user->save();
                $profile->update(['cash_commission_wallet' => $outstanding]);
                return;
            }

            // 2. Check if legacy commission_deduction transactions already subtracted the commission from main wallet
            if ($rawComm > 0) {
                $unreconciledTx = WalletTransaction::where('user_id', $user->id)
                    ->where('type', 'commission_deduction')
                    ->get()
                    ->filter(function ($tx) {
                        $meta = is_array($tx->metadata) ? $tx->metadata : [];
                        return empty($meta['reconciled_commission_wallet']);
                    });

                if ($unreconciledTx->isNotEmpty()) {
                    // In legacy code, every commission_deduction already subtracted `amount` from wallet_balance.
                    // Since wallet_balance >= 0 now, those deductions have already been paid by the main wallet!
                    foreach ($unreconciledTx as $tx) {
                        $meta = is_array($tx->metadata) ? $tx->metadata : [];
                        $meta['reconciled_commission_wallet'] = true;
                        $tx->metadata = $meta;
                        $tx->save();
                    }
                    $rawComm = 0.0;
                    $profile->update(['cash_commission_wallet' => 0]);
                }
            }

            // 3. If both main wallet > 0 and cash_commission_wallet > 0, apply main wallet balance to reduce outstanding cash_commission_wallet
            $roundedMain = max(0.0, round($rawMain));
            $roundedComm = max(0.0, round($rawComm));

            if ($roundedMain > 0 && $roundedComm > 0) {
                $settled = min($roundedMain, $roundedComm);
                $roundedMain = max(0.0, round($roundedMain - $settled));
                $roundedComm = max(0.0, round($roundedComm - $settled));
            }

            if (abs($rawMain - $roundedMain) > 0.001) {
                $user->wallet_balance = $roundedMain;
                $user->save();
            }
            if (abs((float) $profile->cash_commission_wallet - $roundedComm) > 0.001) {
                $profile->update(['cash_commission_wallet' => $roundedComm]);
            }

            \App\Models\DriverProfile::syncCommissionStatus($profile->fresh(), false);
        } catch (\Throwable $e) {
            Log::warning('reconcileDriverCashCommissionWallet warning: ' . $e->getMessage());
        }
    }

    /**
     * Get full wallet summary for a user.
     */
    public static function getWalletSummary(User $user): array
    {
        self::reconcileDriverCashCommissionWallet($user);
        $user->refresh();

        $settings = self::getSettings();
        $currency = self::getUserCurrency($user);

        $pendingWithdrawalsSum = (int) round((float) WalletWithdrawal::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount'));

        $approvedWithdrawalsSum = (int) round((float) WalletWithdrawal::where('user_id', $user->id)
            ->where('status', 'approved')
            ->sum('amount'));

        $withdrawalsCount = [
            'pending' => WalletWithdrawal::where('user_id', $user->id)->where('status', 'pending')->count(),
            'approved' => WalletWithdrawal::where('user_id', $user->id)->where('status', 'approved')->count(),
            'rejected' => WalletWithdrawal::where('user_id', $user->id)->where('status', 'rejected')->count(),
            'total' => WalletWithdrawal::where('user_id', $user->id)->count(),
        ];

        // Additional Cash Commission Wallet (whole number)
        $driverProfile = $user->driverProfile?->fresh() ?? $user->driverProfile;
        if ($driverProfile) {
            \App\Models\DriverProfile::syncCommissionStatus($driverProfile, false);
            $driverProfile->refresh();
        }
        $commissionWalletBalance = (int) round((float) ($driverProfile?->cash_commission_wallet ?? 0));
        $mainWalletBalance = (int) round((float) ($user->wallet_balance ?? 0));
        $cashCommissionsCount = 0;
        $recentCashCommissions = [];

        if (class_exists(\App\Models\DriverCashCommission::class)) {
            \App\Models\DriverCashCommission::ensureTableExists();
            $cashCommissionsCount = \App\Models\DriverCashCommission::where('driver_id', $user->id)->count();
            $recentCashCommissions = \App\Models\DriverCashCommission::where('driver_id', $user->id)
                ->latest()
                ->take(15)
                ->get()
                ->map(function ($item) {
                    $item->gross_fare = (int) round((float) $item->gross_fare);
                    $item->commission_amount = (int) round((float) $item->commission_amount);
                    return $item;
                });
        }

        return [
            'balance' => $mainWalletBalance,
            'wallet_balance' => $mainWalletBalance,
            'currency' => $currency,
            'pending_withdrawals_sum' => $pendingWithdrawalsSum,
            'approved_withdrawals_sum' => $approvedWithdrawalsSum,
            'withdrawals_count' => $withdrawalsCount,
            'settings' => $settings,
            'commission_wallet_balance' => $commissionWalletBalance,
            'commission_threshold_reached' => $commissionWalletBalance >= 100,
            'commission_warning_message' => $commissionWalletBalance >= 100
                ? 'Kindly top up your wallet balance within 7 days for Commission Deduction or risk deactivation.'
                : null,
            'is_commission_locked' => (bool) ($driverProfile?->is_commission_locked ?? false),
            'is_available' => (bool) ($driverProfile?->is_available ?? false),
            'commission_threshold_reached_at' => $driverProfile?->commission_threshold_reached_at?->toIso8601String(),
            'commission_deadline_at' => $driverProfile?->commission_deadline_at,
            'commission_days_remaining' => $driverProfile?->commission_days_remaining,
            'commission_wallet' => [
                'balance' => $commissionWalletBalance,
                'currency' => $currency,
                'count' => $cashCommissionsCount,
                'recent' => $recentCashCommissions,
                'threshold_reached' => $commissionWalletBalance >= 100,
                'warning_message' => $commissionWalletBalance >= 100
                    ? 'Kindly top up your wallet balance within 7 days for Commission Deduction or risk deactivation.'
                    : null,
                'is_locked' => (bool) ($driverProfile?->is_commission_locked ?? false),
                'days_remaining' => $driverProfile?->commission_days_remaining,
            ],
        ];
    }

    /**
     * Submit a new withdrawal request.
     * Note: Per platform rules, withdrawals from the main wallet are INSTANT without Admin approval.
     */
    public static function submitWithdrawal(User $user, float $amount, string $payoutMethod, array $payoutDetails, bool $saveAsDefault = false): WalletWithdrawal
    {
        $settings = self::getSettings();

        if (!$settings['enabled']) {
            throw new \InvalidArgumentException('Wallet withdrawals are currently disabled by administration.');
        }

        if ($amount < $settings['min_amount']) {
            throw new \InvalidArgumentException("Withdrawal amount must be at least {$settings['min_amount']}.");
        }

        if ($amount > $settings['max_amount']) {
            throw new \InvalidArgumentException("Withdrawal amount cannot exceed {$settings['max_amount']}.");
        }

        if ($user->wallet_balance < $amount) {
            throw new \InvalidArgumentException('Withdrawal amount cannot exceed your available wallet balance.');
        }

        if ($payoutMethod === 'bank') {
            $payoutMethod = 'bank_account';
        }

        if (!in_array($payoutMethod, ['bank_account', 'momo'], true)) {
            throw new \InvalidArgumentException('Invalid payout method selected. Please choose Bank Account or Mobile Money (MoMo).');
        }

        $targetRole = $payoutDetails['target_role'] ?? ($user->role === 'driver' ? 'driver' : 'rider');

        // Validate and normalize payout details
        if ($payoutMethod === 'bank_account') {
            $bankName = trim($payoutDetails['bank_name'] ?? '');
            $accNumber = trim($payoutDetails['account_number'] ?? '');
            $holderName = trim($payoutDetails['account_holder_name'] ?? ($payoutDetails['holder_name'] ?? $user->name));
            $routing = trim($payoutDetails['routing_code'] ?? ($payoutDetails['routing'] ?? ''));

            if (empty($bankName) || empty($accNumber)) {
                throw new \InvalidArgumentException('Please provide valid bank name and account number.');
            }

            $payoutDetails = [
                'bank_name' => $bankName,
                'account_number' => $accNumber,
                'account_holder_name' => $holderName,
                'holder_name' => $holderName,
                'routing_code' => $routing,
            ];
        } elseif ($payoutMethod === 'momo') {
            $phone = trim($payoutDetails['momo_phone'] ?? ($payoutDetails['phone_number'] ?? ($payoutDetails['phone'] ?? '')));
            $network = trim($payoutDetails['momo_network'] ?? ($payoutDetails['network'] ?? 'MTN'));
            $accName = trim($payoutDetails['momo_account_name'] ?? ($payoutDetails['account_name'] ?? $user->name));

            if (empty($phone) || empty($network)) {
                throw new \InvalidArgumentException('Please provide mobile money provider network and phone number.');
            }

            $payoutDetails = [
                'momo_phone' => $phone,
                'phone_number' => $phone,
                'momo_network' => $network,
                'network' => $network,
                'momo_account_name' => $accName,
                'account_name' => $accName,
            ];
        }

        // Calculate fee
        $fee = 0.00;
        if ($settings['fee_type'] === 'percentage' && $settings['fee_value'] > 0) {
            $fee = round(($amount * $settings['fee_value']) / 100, 2);
        } elseif ($settings['fee_type'] === 'fixed' && $settings['fee_value'] > 0) {
            $fee = round($settings['fee_value'], 2);
        }
        $netAmount = max(0.00, $amount - $fee);

        $currency = self::getUserCurrency($user);

        return DB::transaction(function () use ($user, $amount, $fee, $netAmount, $payoutMethod, $payoutDetails, $currency, $saveAsDefault, $targetRole) {
            // Re-check balance inside transaction
            $freshUser = User::where('id', $user->id)->lockForUpdate()->first();
            if ($freshUser->wallet_balance < $amount) {
                throw new \InvalidArgumentException('Insufficient wallet balance for this withdrawal.');
            }

            // Save or update payout method if requested
            if ($saveAsDefault) {
                self::savePayoutMethod($user, array_merge($payoutDetails, [
                    'type' => $payoutMethod,
                    'is_default' => true,
                ]));
            }

            $balanceBefore = (float) $freshUser->wallet_balance;
            $balanceAfter = max(0.00, round($balanceBefore - $amount, 2));

            // Instant deduction from driver's main wallet (Requirement 2)
            $freshUser->wallet_balance = $balanceAfter;
            $freshUser->save();

            $instantTxRef = 'INSTANT-' . strtoupper(\Illuminate\Support\Str::random(10));

            // Create INSTANT Approved Withdrawal Request (No admin approval required)
            $withdrawal = WalletWithdrawal::create([
                'withdrawal_ref' => WalletWithdrawal::generateUniqueRef(),
                'user_id' => $user->id,
                'user_type' => $user->role ?? 'driver',
                'amount' => $amount,
                'currency' => $currency,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'payout_method' => $payoutMethod,
                'payout_details' => $payoutDetails,
                'status' => 'approved',
                'approved_at' => now(),
                'transaction_reference' => $instantTxRef,
                'admin_notes' => 'Instant withdrawal processed automatically without admin approval requirement.',
            ]);

            // Record transaction ledger entry immediately
            $methodLabel = $payoutMethod === 'momo' ? 'Mobile Money (MoMo)' : 'Bank Transfer';
            WalletTransaction::create([
                'user_id' => $freshUser->id,
                'transaction_ref' => 'WTX-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'type' => 'withdrawal',
                'amount' => $amount,
                'currency' => $currency,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => "Instant Payout via {$methodLabel} [Ref: {$withdrawal->withdrawal_ref}]",
                'status' => 'completed',
                'reference_id' => $withdrawal->id,
                'reference_type' => WalletWithdrawal::class,
                'metadata' => [
                    'is_instant' => true,
                    'payout_method' => $withdrawal->payout_method,
                    'payout_details' => $withdrawal->payout_details,
                    'instant_ref' => $instantTxRef,
                ],
            ]);

            // Send In-App & Push Notification
            NotificationService::send(
                $user->id,
                'withdrawal_approved',
                'Instant Withdrawal Processed! 🎉',
                "Your withdrawal of {$currency}{$amount} via {$methodLabel} was processed instantly without admin approval. Reference: {$withdrawal->withdrawal_ref}",
                null,
                '/wallet',
                [
                    'withdrawal_id' => $withdrawal->id,
                    'ref' => $withdrawal->withdrawal_ref,
                    'amount' => $amount,
                    'currency' => $currency,
                    'status' => 'approved',
                    'is_instant' => true,
                    'target_role' => $targetRole,
                    'recipient_user_id' => $user->id,
                ]
            );

            return $withdrawal;
        });
    }

    /**
     * Admin approves a withdrawal request.
     * Deducts wallet balance and creates transaction ledger entry.
     */
    public static function approveWithdrawal(WalletWithdrawal $withdrawal, User $admin, ?string $transactionRef = null, ?string $adminNotes = null): WalletWithdrawal
    {
        if ($withdrawal->status !== 'pending') {
            throw new \InvalidArgumentException("Withdrawal is already {$withdrawal->status} and cannot be approved.");
        }

        return DB::transaction(function () use ($withdrawal, $admin, $transactionRef, $adminNotes) {
            $user = User::where('id', $withdrawal->user_id)->lockForUpdate()->first();
            if (!$user) {
                throw new \RuntimeException('User account not found.');
            }

            if ($user->wallet_balance < $withdrawal->amount) {
                throw new \InvalidArgumentException("User currently has insufficient balance ({$withdrawal->currency}{$user->wallet_balance}) to process this withdrawal of {$withdrawal->currency}{$withdrawal->amount}.");
            }

            $balanceBefore = (float) $user->wallet_balance;
            $balanceAfter = max(0.00, $balanceBefore - (float) $withdrawal->amount);

            // Deduct balance
            $user->wallet_balance = $balanceAfter;
            $user->save();

            // Record transaction ledger entry
            $methodLabel = $withdrawal->payout_method === 'momo' ? 'Mobile Money (MoMo)' : 'Bank Transfer';
            WalletTransaction::create([
                'user_id' => $user->id,
                'transaction_ref' => 'WTX-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'type' => 'withdrawal',
                'amount' => $withdrawal->amount,
                'currency' => $withdrawal->currency,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => "Payout via {$methodLabel} [Ref: {$withdrawal->withdrawal_ref}]",
                'status' => 'completed',
                'reference_id' => $withdrawal->id,
                'reference_type' => WalletWithdrawal::class,
                'metadata' => [
                    'payout_method' => $withdrawal->payout_method,
                    'payout_details' => $withdrawal->payout_details,
                    'admin_payout_ref' => $transactionRef,
                ],
            ]);

            // Update withdrawal status
            $withdrawal->update([
                'status' => 'approved',
                'transaction_reference' => $transactionRef,
                'admin_notes' => $adminNotes,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            // Send Notifications
            $refText = $transactionRef ? " (Ref: {$transactionRef})" : '';
            NotificationService::send(
                $user->id,
                'withdrawal_approved',
                'Withdrawal Approved! 🎉',
                "Your withdrawal request of {$withdrawal->currency}{$withdrawal->amount} has been approved and disbursed{$refText}.",
                null,
                '/wallet',
                [
                    'withdrawal_id' => $withdrawal->id,
                    'ref' => $withdrawal->withdrawal_ref,
                    'transaction_ref' => $transactionRef,
                    'amount' => $withdrawal->amount,
                    'currency' => $withdrawal->currency,
                    'status' => 'approved',
                ]
            );

            return $withdrawal->fresh();
        });
    }

    /**
     * Admin rejects a withdrawal request with a mandatory reason.
     * Balance remains unchanged.
     */
    public static function rejectWithdrawal(WalletWithdrawal $withdrawal, User $admin, string $reason, ?string $adminNotes = null): WalletWithdrawal
    {
        if ($withdrawal->status !== 'pending') {
            throw new \InvalidArgumentException("Withdrawal is already {$withdrawal->status} and cannot be rejected.");
        }

        $cleanReason = trim($reason);
        if (empty($cleanReason)) {
            throw new \InvalidArgumentException('A clear rejection reason is mandatory when rejecting a withdrawal request.');
        }

        return DB::transaction(function () use ($withdrawal, $admin, $cleanReason, $adminNotes) {
            $withdrawal->update([
                'status' => 'rejected',
                'rejection_reason' => $cleanReason,
                'admin_notes' => $adminNotes,
                'rejected_by' => $admin->id,
                'rejected_at' => now(),
            ]);

            // Send Notifications
            NotificationService::send(
                $withdrawal->user_id,
                'withdrawal_rejected',
                'Withdrawal Request Declined',
                "Your withdrawal request of {$withdrawal->currency}{$withdrawal->amount} was rejected. Reason: {$cleanReason}",
                null,
                '/wallet',
                [
                    'withdrawal_id' => $withdrawal->id,
                    'ref' => $withdrawal->withdrawal_ref,
                    'reason' => $cleanReason,
                    'amount' => $withdrawal->amount,
                    'currency' => $withdrawal->currency,
                    'status' => 'rejected',
                ]
            );

            return $withdrawal->fresh();
        });
    }

    /**
     * Save a user payout method.
     */
    public static function savePayoutMethod(User $user, array $data): UserPayoutMethod
    {
        $type = $data['type'] ?? 'bank_account';
        if ($type === 'bank') {
            $type = 'bank_account';
        }
        $isDefault = (bool) ($data['is_default'] ?? false);

        $existingCount = UserPayoutMethod::where('user_id', $user->id)->count();
        if ($existingCount === 0) {
            $isDefault = true;
        }

        if ($isDefault) {
            UserPayoutMethod::where('user_id', $user->id)->update(['is_default' => false]);
        }

        return UserPayoutMethod::create([
            'user_id' => $user->id,
            'type' => $type,
            'is_default' => $isDefault,
            'bank_name' => $data['bank_name'] ?? null,
            'account_holder_name' => $data['account_holder_name'] ?? ($data['holder_name'] ?? $user->name),
            'account_number' => $data['account_number'] ?? null,
            'routing_code' => $data['routing_code'] ?? null,
            'branch_name' => $data['branch_name'] ?? null,
            'momo_network' => $data['momo_network'] ?? ($data['network'] ?? null),
            'momo_phone' => $data['momo_phone'] ?? ($data['phone_number'] ?? null),
            'momo_account_name' => $data['momo_account_name'] ?? ($data['account_name'] ?? $user->name),
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * Credit funds to a user's wallet (Add Money / Top-Up).
     * Automatically deducts any outstanding Cash Commission Wallet balance first.
     */
    public static function creditWallet(User $user, float $amount, string $paymentMethod = 'momo', ?string $externalRef = null, array $metadata = []): array
    {
        $amount = round($amount);
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Top-up amount must be greater than zero.');
        }

        self::reconcileDriverCashCommissionWallet($user);
        $currency = self::getUserCurrency($user);

        return DB::transaction(function () use ($user, $amount, $paymentMethod, $externalRef, $metadata, $currency) {
            $freshUser = User::where('id', $user->id)->lockForUpdate()->first();
            if (!$freshUser) {
                throw new \RuntimeException('User account not found.');
            }

            $profile = $freshUser->driverProfile;
            $outstandingComm = $profile ? max(0.0, round((float) ($profile->cash_commission_wallet ?? 0))) : 0.0;
            $settledCommission = min($amount, $outstandingComm);
            $newOutstandingComm = max(0.0, round($outstandingComm - $settledCommission));
            $netWalletCredit = max(0.0, round($amount - $settledCommission));

            $driverSwitchUnlocked = false;
            if ($profile && $settledCommission > 0) {
                $wasLockedOrOverThreshold = (bool) $profile->commission_locked
                    || !empty($profile->commission_threshold_reached_at)
                    || $outstandingComm >= 100
                    || !$profile->is_available;

                $profile->update([
                    'cash_commission_wallet' => $newOutstandingComm,
                ]);
                \App\Models\DriverProfile::syncCommissionStatus($profile->fresh(), true);
                $profile->refresh();

                if ($newOutstandingComm < 100 && $wasLockedOrOverThreshold) {
                    $driverSwitchUnlocked = true;
                }
            }

            $balanceBefore = max(0.0, round((float) ($freshUser->wallet_balance ?? 0)));
            $balanceAfter = round($balanceBefore + $netWalletCredit);

            $freshUser->wallet_balance = $balanceAfter;
            $freshUser->save();

            $methodLabel = match (strtolower($paymentMethod)) {
                'momo', 'mobile_money', 'mtn_momo' => 'Mobile Money (MoMo Pay)',
                'stripe', 'card', 'credit_card' => 'Debit/Credit Card (Stripe)',
                default => 'Instant Wallet Deposit',
            };

            $trxRef = 'WTX-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
            $desc = "Wallet Top-Up via {$methodLabel}";
            if ($settledCommission > 0) {
                $desc .= " ({$currency}{$settledCommission} applied to Cash Commission Wallet; Remaining: {$currency}{$newOutstandingComm})";
            }

            $transaction = WalletTransaction::create([
                'user_id' => $freshUser->id,
                'transaction_ref' => $trxRef,
                'type' => 'deposit',
                'amount' => $amount,
                'currency' => $currency,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $desc,
                'status' => 'completed',
                'reference_id' => null,
                'reference_type' => null,
                'metadata' => array_merge([
                    'payment_method' => $paymentMethod,
                    'external_ref' => $externalRef,
                    'settled_cash_commission' => $settledCommission,
                    'remaining_cash_commission' => $newOutstandingComm,
                    'credited_at' => now()->toIso8601String(),
                ], $metadata),
            ]);

            // Send notification
            $notifBody = $settledCommission > 0
                ? "Your deposit of {$currency}{$amount} via {$methodLabel} was received. {$currency}{$settledCommission} was deducted for your Cash Commission Wallet (Remaining: {$currency}{$newOutstandingComm}). Main Wallet Balance: {$currency}{$balanceAfter}"
                : "Your wallet has been credited with {$currency}{$amount} via {$methodLabel}. Current balance: {$currency}{$balanceAfter}";

            $targetRole = $metadata['target_role'] ?? ($freshUser->role === 'driver' ? 'driver' : 'rider');

            NotificationService::send(
                $freshUser->id,
                'wallet_credited',
                'Wallet Credited! 💳',
                $notifBody,
                null,
                '/wallet',
                [
                    'transaction_ref' => $trxRef,
                    'amount' => (int) $amount,
                    'currency' => $currency,
                    'balance' => (int) $balanceAfter,
                    'commission_wallet_balance' => (int) $newOutstandingComm,
                    'target_role' => $targetRole,
                    'recipient_user_id' => $freshUser->id,
                ]
            );

            return [
                'success' => true,
                'balance' => (int) $balanceAfter,
                'wallet_balance' => (int) $balanceAfter,
                'commission_wallet_balance' => (int) $newOutstandingComm,
                'settled_commission' => (int) $settledCommission,
                'driver_switch_unlocked' => $driverSwitchUnlocked,
                'is_available' => (bool) ($profile?->is_available ?? true),
                'is_commission_locked' => (bool) ($profile?->is_commission_locked ?? false),
                'amount_added' => (int) $amount,
                'currency' => $currency,
                'transaction' => $transaction,
                'message' => $settledCommission > 0
                    ? ($driverSwitchUnlocked
                        ? "Successfully added {$currency}{$amount}! {$currency}{$settledCommission} was deducted for your Cash Commission Wallet. Your Driver Switch has been turned on and unlocked — you can now receive ride requests!"
                        : "Successfully added {$currency}{$amount}! {$currency}{$settledCommission} was deducted for your Cash Commission Wallet.")
                    : "Successfully added {$currency}{$amount} to your wallet!",
            ];
        });
    }

    /**
     * Delete a user payout method.
     */
    public static function deletePayoutMethod(User $user, int $methodId): bool
    {
        return (bool) UserPayoutMethod::where('user_id', $user->id)
            ->where('id', $methodId)
            ->delete();
    }
}
