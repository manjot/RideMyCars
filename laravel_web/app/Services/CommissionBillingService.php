<?php

namespace App\Services;

use App\Models\Setting;

class CommissionBillingService
{
    /**
     * Calculate itemized commission & payout breakdown based on service vertical.
     *
     * @param float $grossAmount
     * @param string $serviceVertical RIDE_HAILING | DRIVER_HIRING | VEHICLE_RENTAL
     * @param float $gatewayFee
     * @return array
     */
    public static function calculate(
        float $grossAmount,
        string $serviceVertical = 'RIDE_HAILING',
        float $gatewayFee = 0.00
    ): array {
        $vertical = strtoupper(trim($serviceVertical));
        
        $platformFee = 0.00;
        $maintenanceFee = 0.00;
        $ownerShare = 0.00;
        $netPayout = 0.00;
        $absorber = 'fleet_owner';

        if ($vertical === 'RIDE_HAILING') {
            $platformPct = (float) (Setting::where('key', 'ride_hailing.platform_commission')->value('value') ?? 10.0);
            $maintPct = (float) (Setting::where('key', 'ride_hailing.maintenance_fee_percent')->value('value') ?? 2.5);
            $absorber = Setting::where('key', 'ride_hailing.gateway_fee_absorber')->value('value') ?? 'fleet_owner';

            $platformFee = round($grossAmount * ($platformPct / 100.0), 2);
            $ownerShareBeforeMaint = round($grossAmount - $platformFee, 2);
            
            // Maintenance fee = 2.5% taken FROM the fleet owner's 90% share
            $maintenanceFee = round($ownerShareBeforeMaint * ($maintPct / 100.0), 2);
            
            $ownerShare = round($ownerShareBeforeMaint - $maintenanceFee, 2);

            // Gateway fee absorption logic
            if ($absorber === 'fleet_owner') {
                $netPayout = round($ownerShare - $gatewayFee, 2);
            } elseif ($absorber === 'platform') {
                $platformFee = max(0, round($platformFee - $gatewayFee, 2));
                $netPayout = $ownerShare;
            } else {
                // passenger absorbs fee
                $netPayout = $ownerShare;
            }
        } elseif ($vertical === 'DRIVER_HIRING' || $vertical === 'PACKAGE_DELIVERY') {
            $key = ($vertical === 'PACKAGE_DELIVERY') ? 'package_delivery.platform_commission' : 'driver_hiring.platform_commission';
            $platformPct = (float) (Setting::where('key', $key)->value('value') ?? 15.0);
            
            $platformFee = round($grossAmount * ($platformPct / 100.0), 2);
            $ownerShare = round($grossAmount - $platformFee, 2);
            $netPayout = round($ownerShare - $gatewayFee, 2);
        } elseif ($vertical === 'VEHICLE_RENTAL') {
            $platformPct = (float) (Setting::where('key', 'vehicle_rental.platform_commission')->value('value') ?? 20.0);
            
            $platformFee = round($grossAmount * ($platformPct / 100.0), 2);
            $ownerShare = round($grossAmount - $platformFee, 2);
            $netPayout = round($ownerShare - $gatewayFee, 2);
        } else {
            // Default fallback
            $platformFee = round($grossAmount * 0.10, 2);
            $ownerShare = round($grossAmount - $platformFee, 2);
            $netPayout = $ownerShare;
        }

        return [
            'gross_amount' => round($grossAmount, 2),
            'platform_fee' => $platformFee,
            'maintenance_fee' => $maintenanceFee,
            'gateway_fee' => round($gatewayFee, 2),
            'owner_or_driver_share' => $ownerShare,
            'net_payout' => max(0, $netPayout),
            'service_vertical' => $vertical,
            'gateway_fee_absorber' => $absorber,
        ];
    }
}
