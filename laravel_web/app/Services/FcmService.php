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

        $targetRole = strtolower((string) ($data['target_role'] ?? ''));
        if ($targetRole === 'driver') {
            $token = $user->driverProfile?->fcm_token
                ?: (in_array($user->role, ['driver', 'chauffeur'], true) ? $user->fcm_token : null);
        } elseif ($targetRole === 'rider') {
            $token = $user->fcm_token;
        } else {
            $token = $user->driverProfile?->fcm_token ?: $user->fcm_token;
        }

        if (empty($token)) {
            Log::info("[FcmService] No FCM token found for user #{$userId}");
            return false;
        }

        $data['recipient_user_id'] = (string) $userId;
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
    public static function sendToToken(string $token, string $title, string $body, array $data = [], string $sound = ''): bool
    {
        $serverKey = SettingService::get('firebase.fcm_server_key') 
            ?: config('services.firebase.fcm_server_key') 
            ?: env('FIREBASE_FCM_SERVER_KEY');

        $isActionableRingtone = ($sound === 'incoming_order_ringtone');

        // Always format data values as strings for FCM compatibility
        $stringData = [];
        foreach ($data as $k => $v) {
            $stringData[(string)$k] = is_array($v) || is_object($v) ? json_encode($v) : (string)$v;
        }
        $stringData['title'] = $title;
        $stringData['body'] = $body;
        $stringData['sound'] = $isActionableRingtone ? 'incoming_order_ringtone' : 'silent';
        $stringData['is_actionable'] = $isActionableRingtone ? 'true' : 'false';
        $stringData['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';

        $notificationPayload = [
            'title' => $title,
            'body' => $body,
            'android_channel_id' => $isActionableRingtone
                ? 'ridemycars_incoming_orders_v2'
                : 'ridemycars_silent_notifications',
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];
        if ($isActionableRingtone) {
            $notificationPayload['sound'] = 'incoming_order_ringtone';
        }

        $payload = [
            'to' => $token,
            'priority' => $isActionableRingtone ? 'high' : 'normal',
            'content_available' => true,
            'notification' => $notificationPayload,
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
