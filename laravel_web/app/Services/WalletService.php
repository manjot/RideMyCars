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
     * Get full wallet summary for a user.
     */
    public static function getWalletSummary(User $user): array
    {
        $settings = self::getSettings();
        $currency = self::getUserCurrency($user);

        $pendingWithdrawalsSum = (float) WalletWithdrawal::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount');

        $approvedWithdrawalsSum = (float) WalletWithdrawal::where('user_id', $user->id)
            ->where('status', 'approved')
            ->sum('amount');

        $withdrawalsCount = [
            'pending' => WalletWithdrawal::where('user_id', $user->id)->where('status', 'pending')->count(),
            'approved' => WalletWithdrawal::where('user_id', $user->id)->where('status', 'approved')->count(),
            'rejected' => WalletWithdrawal::where('user_id', $user->id)->where('status', 'rejected')->count(),
            'total' => WalletWithdrawal::where('user_id', $user->id)->count(),
        ];

        return [
            'balance' => (float) $user->wallet_balance,
            'wallet_balance' => (float) $user->wallet_balance,
            'currency' => $currency,
            'pending_withdrawals_sum' => $pendingWithdrawalsSum,
            'approved_withdrawals_sum' => $approvedWithdrawalsSum,
            'withdrawals_count' => $withdrawalsCount,
            'settings' => $settings,
        ];
    }

    /**
     * Submit a new withdrawal request.
     * Note: Balance is NOT deducted until request is approved by Admin.
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

        return DB::transaction(function () use ($user, $amount, $fee, $netAmount, $payoutMethod, $payoutDetails, $currency, $saveAsDefault) {
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

            // Create Pending Withdrawal Request (Balance remains untouched!)
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
                'status' => 'pending',
            ]);

            // Send In-App & Push Notification
            $methodLabel = $payoutMethod === 'momo' ? 'Mobile Money' : 'Bank Account';
            NotificationService::send(
                $user->id,
                'withdrawal_submitted',
                'Withdrawal Request Received',
                "Your withdrawal request of {$currency}{$amount} via {$methodLabel} has been submitted. Ref: {$withdrawal->withdrawal_ref}",
                null,
                '/wallet',
                [
                    'withdrawal_id' => $withdrawal->id,
                    'ref' => $withdrawal->withdrawal_ref,
                    'amount' => $amount,
                    'currency' => $currency,
                    'status' => 'pending',
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
     */
    public static function creditWallet(User $user, float $amount, string $paymentMethod = 'momo', ?string $externalRef = null, array $metadata = []): array
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Top-up amount must be greater than zero.');
        }

        $currency = self::getUserCurrency($user);

        return DB::transaction(function () use ($user, $amount, $paymentMethod, $externalRef, $metadata, $currency) {
            $freshUser = User::where('id', $user->id)->lockForUpdate()->first();
            if (!$freshUser) {
                throw new \RuntimeException('User account not found.');
            }

            $balanceBefore = (float) ($freshUser->wallet_balance ?? 0.00);
            $balanceAfter = round($balanceBefore + $amount, 2);

            $freshUser->wallet_balance = $balanceAfter;
            $freshUser->save();

            $methodLabel = match (strtolower($paymentMethod)) {
                'momo', 'mobile_money', 'mtn_momo' => 'Mobile Money (MoMo Pay)',
                'stripe', 'card', 'credit_card' => 'Debit/Credit Card (Stripe)',
                default => 'Instant Wallet Deposit',
            };

            $trxRef = 'WTX-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));

            $transaction = WalletTransaction::create([
                'user_id' => $freshUser->id,
                'transaction_ref' => $trxRef,
                'type' => 'deposit',
                'amount' => $amount,
                'currency' => $currency,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => "Wallet Top-Up via {$methodLabel}",
                'status' => 'completed',
                'reference_id' => null,
                'reference_type' => null,
                'metadata' => array_merge([
                    'payment_method' => $paymentMethod,
                    'external_ref' => $externalRef,
                    'credited_at' => now()->toIso8601String(),
                ], $metadata),
            ]);

            // Send notification
            NotificationService::send(
                $freshUser->id,
                'wallet_credited',
                'Wallet Credited! 💳',
                "Your wallet has been credited with {$currency}{$amount} via {$methodLabel}. Current balance: {$currency}{$balanceAfter}",
                null,
                '/wallet',
                [
                    'transaction_ref' => $trxRef,
                    'amount' => $amount,
                    'currency' => $currency,
                    'balance' => $balanceAfter,
                ]
            );

            return [
                'success' => true,
                'balance' => $balanceAfter,
                'wallet_balance' => $balanceAfter,
                'amount_added' => $amount,
                'currency' => $currency,
                'transaction' => $transaction,
                'message' => "Successfully added {$currency}{$amount} to your wallet!",
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
