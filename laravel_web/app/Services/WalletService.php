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
        return [
            'enabled' => (bool) SettingService::get('wallet.withdrawals_enabled', true),
            'min_amount' => (float) SettingService::get('wallet.min_withdrawal_amount', 50.00),
            'max_amount' => (float) SettingService::get('wallet.max_withdrawal_amount', 10000.00),
            'fee_type' => SettingService::get('wallet.withdrawal_fee_type', 'fixed'), // fixed, percentage
            'fee_value' => (float) SettingService::get('wallet.withdrawal_fee_value', 0.00),
            'allowed_methods' => explode(',', SettingService::get('wallet.allowed_payout_methods', 'bank_account,momo')),
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

        if (!in_array($payoutMethod, ['bank_account', 'momo'])) {
            throw new \InvalidArgumentException('Invalid payout method selected. Please choose Bank Account or Mobile Money (MoMo).');
        }

        // Validate payout details
        if ($payoutMethod === 'bank_account') {
            if (empty($payoutDetails['account_number']) || empty($payoutDetails['bank_name'])) {
                throw new \InvalidArgumentException('Please provide valid bank name and account number.');
            }
        } elseif ($payoutMethod === 'momo') {
            if (empty($payoutDetails['momo_phone']) || empty($payoutDetails['momo_network'])) {
                throw new \InvalidArgumentException('Please provide mobile money provider network and phone number.');
            }
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
        $isDefault = (bool) ($data['is_default'] ?? false);

        if ($isDefault) {
            UserPayoutMethod::where('user_id', $user->id)->update(['is_default' => false]);
        }

        return UserPayoutMethod::create([
            'user_id' => $user->id,
            'type' => $type,
            'is_default' => $isDefault,
            'bank_name' => $data['bank_name'] ?? null,
            'account_holder_name' => $data['account_holder_name'] ?? $user->name,
            'account_number' => $data['account_number'] ?? null,
            'routing_code' => $data['routing_code'] ?? null,
            'branch_name' => $data['branch_name'] ?? null,
            'momo_network' => $data['momo_network'] ?? null,
            'momo_phone' => $data['momo_phone'] ?? null,
            'momo_account_name' => $data['momo_account_name'] ?? $user->name,
            'metadata' => $data['metadata'] ?? null,
        ]);
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
