<?php

namespace App\Services;

use App\Models\DriverIncentiveProgress;
use App\Models\DriverWalletTransaction;
use App\Models\Incentive;
use App\Models\OwnerWallet;
use App\Models\Ride;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IncentiveService
{
    /**
     * Ensure database tables exist before executing queries.
     */
    public static function ensureTables(): void
    {
        Incentive::ensureTableExists();
        DriverIncentiveProgress::ensureTableExists();
        DriverWalletTransaction::ensureTableExists();
    }

    /**
     * Get active incentives matching the driver's location and vehicle.
     */
    public static function getApplicableIncentives(User $driver, ?string $type = null, ?Carbon $date = null): array
    {
        self::ensureTables();

        $d = $date ?: Carbon::now();
        $query = Incentive::query()->where('status', 'active');

        if ($type) {
            $query->where('type', $type);
        }

        $allActive = $query->get();
        $applicable = [];

        foreach ($allActive as $inc) {
            if ($inc->isScheduleActive($d) && $inc->matchesDriver($driver)) {
                $applicable[] = $inc;
            }
        }

        return $applicable;
    }

    /**
     * Count completed rides for a driver in the incentive's schedule period.
     */
    public static function countCompletedRides(Incentive $incentive, User $driver, ?Carbon $date = null): int
    {
        $d = ($date ?: Carbon::now())->copy();

        if ($incentive->type === 'weekly') {
            $periodStart = $d->copy()->startOfWeek(); // Monday 00:00:00
            $periodEnd = $d->copy()->endOfWeek();     // Sunday 23:59:59
        } elseif ($incentive->type === 'monthly') {
            $periodStart = $d->copy()->startOfMonth();
            $periodEnd = $d->copy()->endOfMonth();
        } else {
            // Daily
            $periodStart = $d->copy()->startOfDay();
            $periodEnd = $d->copy()->endOfDay();
        }

        $rideQuery = Ride::where('driver_id', $driver->id)
            ->where('status', 'completed')
            ->where(function ($q) use ($periodStart, $periodEnd) {
                $q->whereBetween('completed_at', [$periodStart, $periodEnd])
                  ->orWhere(function ($sub) use ($periodStart, $periodEnd) {
                      $sub->whereNull('completed_at')
                          ->whereBetween('created_at', [$periodStart, $periodEnd]);
                  });
            });

        // If incentive is vehicle specific
        $vType = trim($incentive->vehicle_type ?? 'All Vehicles');
        if (!empty($vType) && strcasecmp($vType, 'All Vehicles') !== 0 && strcasecmp($vType, 'All') !== 0) {
            $rideQuery->where(function ($q) use ($vType) {
                $q->where('vehicle_type', 'like', "%{$vType}%")
                  ->orWhereHas('vehicle', function ($vSub) use ($vType) {
                      $vSub->where('category', 'like', "%{$vType}%")
                           ->orWhere('type', 'like', "%{$vType}%");
                  });
            });
        }

        return $rideQuery->count();
    }

    /**
     * Get or initialize driver's progress for an incentive in the current period.
     */
    public static function getOrCreateProgress(Incentive $incentive, User $driver, ?Carbon $date = null): DriverIncentiveProgress
    {
        self::ensureTables();

        $d = $date ?: Carbon::now();
        $periodKey = $incentive->getPeriodKey($d);

        $progress = DriverIncentiveProgress::firstOrCreate(
            [
                'incentive_id' => $incentive->id,
                'driver_id' => $driver->id,
                'period_key' => $periodKey,
            ],
            [
                'completed_rides_count' => 0,
                'achieved_milestones' => [],
                'total_reward_earned' => 0.00,
                'status' => 'in_progress',
            ]
        );

        return $progress;
    }

    /**
     * Real-time handler called when any ride is marked 'completed'.
     */
    public static function handleRideCompleted(Ride $ride): void
    {
        try {
            self::ensureTables();

            if (empty($ride->driver_id)) {
                return;
            }

            $driver = User::find($ride->driver_id);
            if (!$driver) {
                return;
            }

            // Find all applicable active incentives for this driver
            $incentives = self::getApplicableIncentives($driver, null, Carbon::now());

            foreach ($incentives as $incentive) {
                self::processDriverIncentiveForRide($incentive, $driver, $ride);
            }
        } catch (\Throwable $e) {
            Log::error("Error in IncentiveService::handleRideCompleted: " . $e->getMessage(), [
                'ride_id' => $ride->id ?? null,
                'driver_id' => $ride->driver_id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Process progress and reward logic for a specific incentive when a ride completes.
     */
    public static function processDriverIncentiveForRide(Incentive $incentive, User $driver, ?Ride $ride = null): void
    {
        $now = Carbon::now();
        $progress = self::getOrCreateProgress($incentive, $driver, $now);

        // Count actual completed rides in real time
        $completedCount = self::countCompletedRides($incentive, $driver, $now);
        $progress->completed_rides_count = $completedCount;
        if ($ride) {
            $progress->last_ride_id = $ride->id;
        }

        $achievedMilestones = is_array($progress->achieved_milestones) ? $progress->achieved_milestones : [];
        $targets = $incentive->getSortedTargets();
        $currency = $incentive->currency ?: '₹';

        $highestTarget = 0;
        $allAchieved = true;

        foreach ($targets as $target) {
            $targetRides = (int)($target['rides'] ?? 0);
            $rewardAmount = (float)($target['reward'] ?? 0);
            if ($targetRides <= 0) continue;

            if ($targetRides > $highestTarget) {
                $highestTarget = $targetRides;
            }

            if ($completedCount >= $targetRides) {
                // Check if already credited (DUPLICATE PREVENTION)
                if (!in_array($targetRides, $achievedMilestones)) {
                    // Credit reward!
                    self::creditMilestoneReward($incentive, $driver, $targetRides, $rewardAmount, $currency, $progress);
                    $achievedMilestones[] = $targetRides;
                    $progress->achieved_milestones = $achievedMilestones;
                    $progress->total_reward_earned += $rewardAmount;
                }
            } else {
                $allAchieved = false;

                // Check if close to milestone to send encouragement notification
                $remaining = $targetRides - $completedCount;
                if ($remaining > 0 && $remaining <= 5 && $incentive->notify_on_start) {
                    self::sendEncouragementNotification($driver, $incentive, $remaining, $rewardAmount, $currency);
                }
            }
        }

        if ($highestTarget > 0 && $completedCount >= $highestTarget) {
            $progress->status = 'completed';
        } else {
            $progress->status = 'in_progress';
        }

        $progress->save();
    }

    /**
     * Credit the bonus reward to the driver's wallet and send push/in-app notification.
     */
    protected static function creditMilestoneReward(Incentive $incentive, User $driver, int $milestoneRides, float $rewardAmount, string $currency, DriverIncentiveProgress $progress): void
    {
        DB::transaction(function () use ($incentive, $driver, $milestoneRides, $rewardAmount, $currency) {
            $txRef = 'INC-' . strtoupper(Str::random(10)) . '-' . $milestoneRides;

            // 1. Log Driver Wallet Transaction
            DriverWalletTransaction::create([
                'driver_id' => $driver->id,
                'incentive_id' => $incentive->id,
                'transaction_ref' => $txRef,
                'milestone_rides' => $milestoneRides,
                'type' => 'incentive_bonus',
                'amount' => $rewardAmount,
                'currency' => $currency,
                'description' => "{$incentive->name} - Completed {$milestoneRides} Rides",
                'status' => 'credited',
                'credited_at' => now(),
            ]);

            // 2. Update Driver / Owner Wallet balance
            if (class_exists(OwnerWallet::class)) {
                $wallet = OwnerWallet::firstOrCreate(
                    ['user_id' => $driver->id],
                    [
                        'ride_hailing_balance' => 0.00,
                        'driver_hiring_balance' => 0.00,
                        'vehicle_rental_balance' => 0.00,
                        'pending_payout_balance' => 0.00,
                        'total_withdrawn' => 0.00,
                    ]
                );
                $wallet->ride_hailing_balance = ($wallet->ride_hailing_balance ?? 0.00) + $rewardAmount;
                $wallet->save();
            }

            // 3. Log Activity
            if (class_exists(\App\Services\ActivityLogService::class)) {
                \App\Services\ActivityLogService::log(
                    'incentive_reward',
                    "Driver earned {$currency}{$rewardAmount} for reaching {$milestoneRides} rides in {$incentive->name}",
                    $driver->id
                );
            }

            // 4. Send Notification if enabled
            if ($incentive->notify_on_reward) {
                NotificationService::send(
                    $driver->id,
                    'incentive_reward',
                    'Congratulations!',
                    "{$currency}{$rewardAmount} has been credited to your wallet for completing {$milestoneRides} rides in {$incentive->name}.",
                    null,
                    '/driver/dashboard',
                    [
                        'type' => 'incentive_reward',
                        'amount' => $rewardAmount,
                        'currency' => $currency,
                        'milestone_rides' => $milestoneRides,
                        'icon' => 'gift',
                        'color' => 'emerald',
                    ]
                );
            }
        });
    }

    /**
     * Send encouragement notification when close to reaching target (e.g. 5 rides left).
     */
    protected static function sendEncouragementNotification(User $driver, Incentive $incentive, int $remaining, float $reward, string $currency): void
    {
        // Avoid sending duplicate alerts multiple times within the same hour
        $cacheKey = "incentive_encourage_{$incentive->id}_{$driver->id}_{$remaining}";
        if (cache()->has($cacheKey)) {
            return;
        }
        cache()->put($cacheKey, true, now()->addHours(2));

        NotificationService::send(
            $driver->id,
            'incentive_progress',
            "Incentive Target Close!",
            "Only {$remaining} rides left to earn {$currency}{$reward} in today's incentive.",
            null,
            '/driver/dashboard',
            [
                'type' => 'incentive_progress',
                'remaining' => $remaining,
                'reward' => $reward,
                'currency' => $currency,
                'icon' => 'sparkles',
                'color' => 'amber',
            ]
        );
    }

    /**
     * Build the structured payload for mobile app and website driver views.
     */
    public static function getDriverIncentivesPayload(User $driver): array
    {
        self::ensureTables();

        $profile = $driver->driverProfile;
        $driverCountry = $profile?->country ?? $driver->country ?? 'India';
        $driverState = $profile?->state ?? '';
        $driverCity = $profile?->city ?? $profile?->service_area ?? $driver->city ?? 'Hyderabad';
        $driverZone = $profile?->zone ?? '';
        $driverVehicle = $profile?->vehicle_type ?? 'Car';

        $tabs = [
            'daily' => self::buildIncentiveTabPayload($driver, 'daily'),
            'weekly' => self::buildIncentiveTabPayload($driver, 'weekly'),
            'monthly' => self::buildIncentiveTabPayload($driver, 'monthly'),
        ];

        // Reward History
        $rewardHistory = DriverWalletTransaction::where('driver_id', $driver->id)
            ->where('type', 'incentive_bonus')
            ->orderBy('credited_at', 'desc')
            ->take(50)
            ->get()
            ->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'date' => $tx->credited_at ? $tx->credited_at->format('d M') : $tx->created_at->format('d M'),
                    'full_date' => $tx->credited_at ? $tx->credited_at->format('d M Y, h:i A') : $tx->created_at->format('d M Y'),
                    'incentive' => $tx->incentive ? $tx->incentive->name : 'Incentive Program',
                    'incentive_type' => $tx->incentive?->type ?? 'daily',
                    'reward' => ($tx->currency ?? '₹') . number_format($tx->amount, 0),
                    'reward_raw' => (float)$tx->amount,
                    'trips' => ($tx->milestone_rides ? "{$tx->milestone_rides} Rides" : 'Target Achieved'),
                    'status' => 'Credited',
                    'transaction_ref' => $tx->transaction_ref,
                ];
            });

        $totalEarned = DriverWalletTransaction::where('driver_id', $driver->id)
            ->where('type', 'incentive_bonus')
            ->sum('amount');

        $walletBalance = 0.00;
        if (class_exists(OwnerWallet::class)) {
            $w = OwnerWallet::where('user_id', $driver->id)->first();
            $walletBalance = (float)($w->ride_hailing_balance ?? 0.00);
        }

        return [
            'success' => true,
            'driver_location' => [
                'country' => $driverCountry,
                'state' => $driverState,
                'city' => $driverCity,
                'zone' => $driverZone,
                'vehicle_type' => $driverVehicle,
                'display_location' => trim("{$driverCity}, {$driverCountry}", ', '),
            ],
            'wallet_summary' => [
                'balance' => $walletBalance,
                'total_incentive_earned' => (float)$totalEarned,
                'currency' => '₹',
            ],
            'tabs' => $tabs,
            'reward_history' => $rewardHistory,
        ];
    }

    /**
     * Build the payload for a specific tab (daily, weekly, monthly).
     */
    protected static function buildIncentiveTabPayload(User $driver, string $type): ?array
    {
        $now = Carbon::now();
        $applicableIncentives = self::getApplicableIncentives($driver, $type, $now);

        if (empty($applicableIncentives)) {
            // Check if there was an incentive earlier today/week/month that expired
            $pastIncentive = Incentive::where('type', $type)
                ->where('status', 'active')
                ->latest()
                ->first();

            $isMissed = false;
            if ($pastIncentive && $pastIncentive->matchesDriver($driver)) {
                $isMissed = true;
            }

            return [
                'has_incentive' => false,
                'type' => $type,
                'title' => ucfirst($type) . " Incentive",
                'is_missed' => $isMissed,
                'missed_message' => "Today's incentive expired. Better luck tomorrow.",
                'incentive' => null,
            ];
        }

        $incentive = $applicableIncentives[0]; // Primary matching incentive
        $progress = self::getOrCreateProgress($incentive, $driver, $now);

        $completedCount = self::countCompletedRides($incentive, $driver, $now);
        $progress->completed_rides_count = $completedCount;
        $progress->save();

        $achievedMilestones = is_array($progress->achieved_milestones) ? $progress->achieved_milestones : [];
        $targets = $incentive->getSortedTargets();
        $currency = $incentive->currency ?: '₹';

        $maxTarget = $incentive->getMaxTarget();
        $maxRides = (int)($maxTarget['rides'] ?? 0);
        $maxReward = (float)($maxTarget['reward'] ?? 0);

        // Find next milestone
        $nextMilestone = null;
        foreach ($targets as $tgt) {
            $tgtRides = (int)($tgt['rides'] ?? 0);
            if ($completedCount < $tgtRides) {
                $nextMilestone = $tgt;
                break;
            }
        }

        // Active target info for Hero Card
        $activeTargetRides = $nextMilestone ? (int)$nextMilestone['rides'] : $maxRides;
        $activeTargetReward = $nextMilestone ? (float)$nextMilestone['reward'] : $maxReward;
        $remainingRides = max(0, $activeTargetRides - $completedCount);

        // Calculate progress ratio & percentage
        $progressPercentage = $activeTargetRides > 0 ? min(100, (int)round(($completedCount / $activeTargetRides) * 100)) : 0;
        $overallPercentage = $maxRides > 0 ? min(100, (int)round(($completedCount / $maxRides) * 100)) : 0;

        // Build milestone cards list
        $milestoneCards = [];
        foreach ($targets as $tgt) {
            $tRides = (int)($tgt['rides'] ?? 0);
            $tReward = (float)($tgt['reward'] ?? 0);

            $isAchieved = in_array($tRides, $achievedMilestones) || $completedCount >= $tRides;
            $isCurrent = !$isAchieved && ($nextMilestone && (int)$nextMilestone['rides'] === $tRides);

            $milestoneCards[] = [
                'rides' => $tRides,
                'reward' => $tReward,
                'reward_formatted' => "{$currency}{$tReward}",
                'title' => "Complete {$tRides} Rides",
                'is_completed' => $isAchieved,
                'is_current' => $isCurrent,
                'progress_text' => $isAchieved 
                    ? 'Completed' 
                    : "{$completedCount} / {$tRides}",
                'remaining_text' => $isAchieved 
                    ? null 
                    : max(0, $tRides - $completedCount) . " Rides Remaining",
            ];
        }

        $isFullyCompleted = ($maxRides > 0 && $completedCount >= $maxRides);
        $totalRewardEarned = (float)($progress->total_reward_earned ?? 0.00);

        return [
            'has_incentive' => true,
            'id' => $incentive->id,
            'type' => $incentive->type,
            'title' => match ($incentive->type) {
                'daily' => "Today's Incentive",
                'weekly' => "This Week's Incentive",
                'monthly' => "This Month's Incentive",
            },
            'name' => $incentive->name,
            'description' => $incentive->description,
            'country' => $incentive->country,
            'city' => $incentive->city,
            'vehicle_type' => $incentive->vehicle_type,
            'currency' => $currency,
            
            // Hero Card Specs
            'hero_reward_text' => "Earn {$currency}" . number_format($activeTargetReward, 0),
            'hero_reward_amount' => $activeTargetReward,
            'hero_target_text' => "Complete {$activeTargetRides} Rides",
            'hero_target_rides' => $activeTargetRides,
            'progress_fraction' => "{$completedCount} / {$activeTargetRides}",
            'completed_rides' => $completedCount,
            'remaining_rides' => $remainingRides,
            'remaining_text' => $isFullyCompleted ? "Target Achieved!" : "{$remainingRides} Rides Remaining",
            'progress_percentage' => $progressPercentage,
            'overall_percentage' => $overallPercentage,

            // State indicators
            'is_completed' => $isFullyCompleted,
            'completed_card' => [
                'title' => 'Congratulations!',
                'message' => "{$currency}" . number_format($totalRewardEarned ?: $maxReward, 0) . " Credited",
                'credited_amount' => $totalRewardEarned ?: $maxReward,
            ],
            'is_missed' => false,
            'missed_card' => [
                'title' => "Today's incentive expired.",
                'message' => "Better luck tomorrow.",
            ],

            // Milestone cards
            'milestones' => $milestoneCards,
            'total_reward_earned' => $totalRewardEarned,
        ];
    }
}
