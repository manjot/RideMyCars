<?php

namespace App\Services;

use App\Models\DriverProfile;

class LicenseVerificationService
{
    /**
     * Submit driver license for verification.
     */
    public static function submitLicense(
        DriverProfile $profile,
        array $data,
        ?string $frontImagePath = null,
        ?string $backImagePath = null
    ): DriverProfile {
        $updateData = [
            'license_number' => $data['license_number'] ?? $profile->license_number,
            'license_country' => $data['license_country'] ?? $profile->country,
            'license_expiry' => $data['license_expiry'] ?? null,
            'verification_status' => 'submitted',
        ];

        if ($frontImagePath) {
            $updateData['license_front_image'] = $frontImagePath;
        }

        if ($backImagePath) {
            $updateData['license_back_image'] = $backImagePath;
        }

        $profile->update($updateData);

        ActivityLogService::log(
            'driver_verification_submitted',
            "Driver {$profile->user->name} submitted license #{$profile->license_number} for verification",
            $profile->user_id,
            [
                'driver_profile_id' => $profile->id,
                'status' => 'submitted',
            ]
        );

        return $profile;
    }

    /**
     * Admin updates verification status.
     */
    public static function updateStatus(
        DriverProfile $profile,
        string $status,
        ?string $notes = null
    ): DriverProfile {
        $profile->update([
            'verification_status' => $status,
            'kyc_status' => ($status === 'verified') ? 'approved' : (($status === 'rejected') ? 'rejected' : 'pending'),
            'verification_notes' => $notes,
        ]);

        $actType = ($status === 'verified') ? 'driver_verification_approved' : (($status === 'rejected') ? 'driver_verification_rejected' : 'verification');

        ActivityLogService::log(
            $actType,
            "Admin updated driver {$profile->user->name} verification status to '{$status}'",
            null,
            [
                'driver_profile_id' => $profile->id,
                'status' => $status,
                'notes' => $notes,
            ]
        );

        return $profile;
    }
}
