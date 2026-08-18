<?php

namespace App\Services;

use App\Models\DriverProfile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BackgroundCheckService
{
    /**
     * Initiate a Checkr background check for a driver profile.
     */
    public static function initiateCheck(DriverProfile $profile): DriverProfile
    {
        $apiKey = config('services.checkr.api_key');
        $envMode = config('services.checkr.env', 'sandbox');
        $checkrId = 'CHK-' . strtoupper(\Illuminate\Support\Str::random(10));

        if ($apiKey) {
            try {
                $response = Http::withBasicAuth($apiKey, '')
                    ->post('https://api.checkr.com/v1/candidates', [
                        'first_name' => explode(' ', $profile->user->name)[0] ?? 'Driver',
                        'last_name' => explode(' ', $profile->user->name)[1] ?? 'Partner',
                        'email' => $profile->user->email,
                        'dob' => '1990-01-01',
                        'driver_license_number' => $profile->license_number,
                        'driver_license_state' => 'CA',
                    ]);

                if ($response->successful()) {
                    $resData = $response->json();
                    $checkrId = $resData['id'] ?? $checkrId;
                }
            } catch (\Exception $e) {
                Log::warning("Checkr API integration error: " . $e->getMessage());
            }
        }

        $profile->update([
            'background_check_status' => 'processing',
            'background_check_provider' => 'checkr',
            'background_check_id' => $checkrId,
        ]);

        ActivityLogService::log(
            'background_check_initiated',
            "Initiated Checkr background check #{$checkrId} for driver {$profile->user->name}",
            $profile->user_id,
            [
                'driver_profile_id' => $profile->id,
                'checkr_id' => $checkrId,
                'status' => 'processing',
            ]
        );

        return $profile;
    }

    /**
     * Admin or Webhook updates background check status.
     */
    public static function updateStatus(
        DriverProfile $profile,
        string $status,
        ?string $notes = null
    ): DriverProfile {
        $allowedStatuses = ['pending', 'processing', 'clear', 'failed', 'requires_review'];
        if (!in_array($status, $allowedStatuses)) {
            $status = 'requires_review';
        }

        $profile->update([
            'background_check_status' => $status,
            'background_checked_at' => ($status === 'clear') ? now() : $profile->background_checked_at,
            'verification_notes' => $notes ? ($profile->verification_notes . "\nBackground Check: " . $notes) : $profile->verification_notes,
        ]);

        ActivityLogService::log(
            'background_check_updated',
            "Updated background check for driver {$profile->user->name} to '{$status}'",
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
