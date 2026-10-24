<?php

namespace App\Services;

use App\Models\User;
use App\Models\DriverProfile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Send high-priority push notification to a specific user (or driver).
     */
    public static function sendToUser(int $userId, string $title, string $body, array $data = [], string $sound = 'incoming_order_ringtone'): bool
    {
        $user = User::with('driverProfile')->find($userId);
        if (!$user) {
            return false;
        }

        $token = $user->driverProfile?->fcm_token ?: $user->fcm_token;
        if (empty($token)) {
            Log::info("[FcmService] No FCM token found for user #{$userId}");
            return false;
        }

        return self::sendToToken($token, $title, $body, $data, $sound);
    }

    /**
     * Send high-priority push notification to multiple drivers.
     */
    public static function sendToDrivers(array $driverUserIds, string $title, string $body, array $data = [], string $sound = 'incoming_order_ringtone'): int
    {
        $tokens = DriverProfile::whereIn('user_id', $driverUserIds)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->filter()
            ->unique()
            ->toArray();

        if (empty($tokens)) {
            $userTokens = User::whereIn('id', $driverUserIds)
                ->whereNotNull('fcm_token')
                ->pluck('fcm_token')
                ->filter()
                ->unique()
                ->toArray();
            $tokens = array_unique(array_merge($tokens, $userTokens));
        }

        if (empty($tokens)) {
            return 0;
        }

        $successCount = 0;
        foreach ($tokens as $token) {
            if (self::sendToToken($token, $title, $body, $data, $sound)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Send FCM message directly to a device token.
     */
    public static function sendToToken(string $token, string $title, string $body, array $data = [], string $sound = 'incoming_order_ringtone'): bool
    {
        $serverKey = SettingService::get('firebase.fcm_server_key') 
            ?: config('services.firebase.fcm_server_key') 
            ?: env('FIREBASE_FCM_SERVER_KEY');

        // Always format data values as strings for FCM compatibility
        $stringData = [];
        foreach ($data as $k => $v) {
            $stringData[(string)$k] = is_array($v) || is_object($v) ? json_encode($v) : (string)$v;
        }
        $stringData['title'] = $title;
        $stringData['body'] = $body;
        $stringData['sound'] = $sound;
        $stringData['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';

        $payload = [
            'to' => $token,
            'priority' => 'high',
            'content_available' => true,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => $sound,
                'android_channel_id' => 'ridemycars_alerts',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ],
            'data' => $stringData,
        ];

        if ($serverKey) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'key=' . trim($serverKey),
                    'Content-Type' => 'application/json',
                ])->timeout(5)->post('https://fcm.googleapis.com/fcm/send', $payload);

                if ($response->successful()) {
                    Log::info("[FcmService] Sent push to token successfully: {$response->body()}");
                    return true;
                } else {
                    Log::warning("[FcmService] FCM send error {$response->status()}: {$response->body()}");
                }
            } catch (\Throwable $e) {
                Log::warning("[FcmService] Push dispatch exception: " . $e->getMessage());
            }
        } else {
            Log::info("[FcmService] No FCM server key configured. Notification logged for token: " . substr($token, 0, 16) . "...");
        }

        return false;
    }
}
