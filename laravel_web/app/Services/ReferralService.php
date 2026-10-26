<?php

namespace App\Services;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    /**
     * Process referral reward bonus when a new user signs up.
     */
    public static function rewardReferrerOnSignup(User $newReferee, ?User $referrer = null): bool
    {
        try {
            if (!$referrer) {
                if ($newReferee->referrer_id) {
                    $referrer = User::find($newReferee->referrer_id);
                } elseif (!empty($newReferee->referred_by)) {
                    $referrer = User::where('referral_code', strtoupper(trim($newReferee->referred_by)))->first();
                }
            }

            if (!$referrer || $referrer->id === $newReferee->id) {
                return false;
            }

            // Ensure we don't reward duplicate bonuses for the same referee
            $alreadyRewarded = WalletTransaction::where('user_id', $referrer->id)
                ->where('type', 'referral_reward')
                ->whereJsonContains('metadata->referee_id', $newReferee->id)
                ->exists();

            if ($alreadyRewarded) {
                return false;
            }

            // Get reward amount from settings or default to $10.00
            $rewardAmount = (float) SettingService::get('referral_reward_amount', 10.00);
            if ($rewardAmount <= 0) {
                $rewardAmount = 10.00;
            }

            $currency = WalletService::getUserCurrency($referrer);
            $trxRef = 'REF-' . strtoupper(\Illuminate\Support\Str::random(8));

            // Credit the referrer's wallet
            WalletService::creditWallet(
                $referrer,
                $rewardAmount,
                'referral_reward',
                $trxRef,
                [
                    'referee_id' => $newReferee->id,
                    'referee_name' => $newReferee->name,
                    'referee_email' => $newReferee->email,
                    'referral_code' => $referrer->referral_code,
                    'reward_type' => 'signup_referral_bonus',
                ]
            );

            // Send notification to the referrer
            NotificationService::send(
                $referrer->id,
                'referral_reward',
                '🎉 Referral Bonus Credited!',
                "Congratulations! {$newReferee->name} joined RideMyCars with your referral code. {$currency}{$rewardAmount} has been credited directly to your wallet.",
                null,
                '/account?tab=referral',
                [
                    'amount' => $rewardAmount,
                    'currency' => $currency,
                    'referee_name' => $newReferee->name,
                    'icon' => 'gift',
                    'color' => 'amber',
                ]
            );

            Log::info("Referral reward of {$currency}{$rewardAmount} credited to referrer #{$referrer->id} for new user #{$newReferee->id}");
            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to process referral reward: " . $e->getMessage());
            return false;
        }
    }
}
