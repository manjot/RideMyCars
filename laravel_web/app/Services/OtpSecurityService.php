<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OtpSecurityService
{
    /**
     * Cooldown in seconds between OTP dispatches to the same destination.
     */
    const COOLDOWN_SECONDS = 60;

    /**
     * Maximum OTP requests allowed per destination in 1 hour.
     */
    const MAX_PER_HOUR = 3;

    /**
     * Maximum OTP requests allowed per destination in 24 hours.
     */
    const MAX_PER_DAY = 5;

    /**
     * Maximum OTP requests allowed per IP address in 10 minutes.
     */
    const MAX_IP_10M = 5;

    /**
     * Maximum OTP requests allowed per IP address in 24 hours.
     */
    const MAX_IP_DAILY = 20;

    /**
     * Inspect an incoming OTP request for bot activity, rate limiting, and cooldown violations.
     *
     * @param Request $request
     * @param string $destination Phone number or email address
     * @param string $type 'phone' or 'email'
     * @return array ['allowed' => bool, 'silent_mock' => bool, 'error' => ?string, 'code' => int, 'retry_after' => int]
     */
    public static function checkOtpRequest(Request $request, string $destination, string $type = 'phone'): array
    {
        $cleanDest = preg_replace('/\s+/', '', strtolower(trim($destination)));
        $ip = (string) ($request->ip() ?? '127.0.0.1');
        $userAgent = (string) ($request->header('User-Agent') ?? '');

        // 1. Honeypot Trap Check (Zero-Cost Bot Neutralizer)
        // Hidden fields in frontend forms: bots automatically populate all inputs.
        $honeypotValue = $request->input('website_anti_bot_check') 
            ?? $request->input('user_verification_token_dummy')
            ?? $request->input('confirm_bot_check');

        if (!empty($honeypotValue)) {
            Log::warning("[OTP SECURITY] Bot detected via Honeypot trap! IP: {$ip}, Destination: {$cleanDest}, Payload: {$honeypotValue}");
            // Return silent_mock: server replies with HTTP 200 success to the bot,
            // but NEVER calls Twilio or spends money.
            return [
                'allowed' => false,
                'silent_mock' => true,
                'error' => null,
                'code' => 200,
                'retry_after' => 0,
            ];
        }

        // 2. Suspicious Headless Script / Scraper User-Agent Check
        $suspiciousAgents = [
            'python-requests',
            'curl/',
            'go-http-client',
            'scrapy',
            'httpclient',
            'aiohttp',
            'wget',
            'postmanruntime',
            'insomnia',
            'node-fetch',
            'axios/',
        ];
        $lowerAgent = strtolower($userAgent);
        foreach ($suspiciousAgents as $badAgent) {
            if (str_contains($lowerAgent, $badAgent)) {
                // Check if mobile app authorization token or explicit mobile app header is present
                $isMobileApp = $request->hasHeader('X-RideMyCars-Mobile-App') || $request->hasHeader('Authorization');
                if (!$isMobileApp) {
                    Log::warning("[OTP SECURITY] Bot scraper blocked by User-Agent: {$userAgent}, IP: {$ip}, Destination: {$cleanDest}");
                    return [
                        'allowed' => false,
                        'silent_mock' => true,
                        'error' => null,
                        'code' => 200,
                        'retry_after' => 0,
                    ];
                }
            }
        }

        // 3. Human Timing Check (Fast-Submission Trap)
        // Form timestamp: if submitted in under 1.2 seconds, it is automated.
        $formLoadedAt = $request->input('form_loaded_at');
        if (!empty($formLoadedAt) && is_numeric($formLoadedAt)) {
            $elapsedSeconds = time() - ((int) $formLoadedAt);
            if ($elapsedSeconds < 1) {
                Log::warning("[OTP SECURITY] Instant automated submission (<1s): {$elapsedSeconds}s, IP: {$ip}, Destination: {$cleanDest}");
                return [
                    'allowed' => false,
                    'silent_mock' => true,
                    'error' => null,
                    'code' => 200,
                    'retry_after' => 0,
                ];
            }
        }

        // 4. Strict 60-Second Cooldown per Destination
        $cooldownKey = "otp_cooldown_{$type}_{$cleanDest}";
        $cooldownExpires = Cache::get($cooldownKey);
        if ($cooldownExpires && is_numeric($cooldownExpires)) {
            $remaining = (int) ($cooldownExpires - time());
            if ($remaining > 0) {
                Log::info("[OTP SECURITY] Cooldown active for {$cleanDest} ({$remaining}s remaining). IP: {$ip}");
                return [
                    'allowed' => false,
                    'silent_mock' => false,
                    'error' => "Please wait {$remaining} seconds before requesting another verification code.",
                    'code' => 429,
                    'retry_after' => $remaining,
                ];
            }
        }

        // 5. Hourly Cap per Destination (Max 3 / hour)
        $hourlyKey = "otp_hourly_{$type}_{$cleanDest}";
        $hourlyCount = (int) Cache::get($hourlyKey, 0);
        if ($hourlyCount >= self::MAX_PER_HOUR) {
            Log::warning("[OTP SECURITY] Hourly limit reached for {$cleanDest} ({$hourlyCount} requests). IP: {$ip}");
            return [
                'allowed' => false,
                'silent_mock' => false,
                'error' => "Too many verification requests for this {$type}. Please wait an hour before trying again.",
                'code' => 429,
                'retry_after' => 3600,
            ];
        }

        // 6. Daily Cap per Destination (Max 5 / 24 hours)
        $dailyKey = "otp_daily_{$type}_{$cleanDest}";
        $dailyCount = (int) Cache::get($dailyKey, 0);
        if ($dailyCount >= self::MAX_PER_DAY) {
            Log::warning("[OTP SECURITY] Daily limit reached for {$cleanDest} ({$dailyCount} requests). IP: {$ip}");
            return [
                'allowed' => false,
                'silent_mock' => false,
                'error' => "Daily verification limit reached for this {$type}. Please try again tomorrow.",
                'code' => 429,
                'retry_after' => 86400,
            ];
        }

        // 7. IP Velocity Shield: 10-Minute Limit (Max 5 requests across all numbers)
        $ip10mKey = "otp_ip_10m_{$ip}";
        $ip10mCount = (int) Cache::get($ip10mKey, 0);
        if ($ip10mCount >= self::MAX_IP_10M) {
            Log::warning("[OTP SECURITY] IP 10-minute rate limit reached for IP: {$ip} ({$ip10mCount} attempts). Target: {$cleanDest}");
            return [
                'allowed' => false,
                'silent_mock' => false,
                'error' => "Too many verification requests from your network. Please wait a few minutes before trying again.",
                'code' => 429,
                'retry_after' => 600,
            ];
        }

        // 8. IP Velocity Shield: Daily Limit (Max 20 requests per day)
        $ipDailyKey = "otp_ip_24h_{$ip}";
        $ipDailyCount = (int) Cache::get($ipDailyKey, 0);
        if ($ipDailyCount >= self::MAX_IP_DAILY) {
            Log::warning("[OTP SECURITY] IP daily rate limit reached for IP: {$ip} ({$ipDailyCount} attempts). Target: {$cleanDest}");
            return [
                'allowed' => false,
                'silent_mock' => false,
                'error' => "Network verification limit reached for today. Please try again tomorrow.",
                'code' => 429,
                'retry_after' => 86400,
            ];
        }

        return [
            'allowed' => true,
            'silent_mock' => false,
            'error' => null,
            'code' => 200,
            'retry_after' => 0,
        ];
    }

    /**
     * Record a successful OTP dispatch to maintain rate limits, cooldowns, and audit counters.
     *
     * @param string $destination Phone or email
     * @param string|null $ip Client IP
     * @param string $type 'phone' or 'email'
     */
    public static function recordOtpSent(string $destination, ?string $ip = null, string $type = 'phone'): void
    {
        $cleanDest = preg_replace('/\s+/', '', strtolower(trim($destination)));
        $ip = (string) ($ip ?? request()->ip() ?? '127.0.0.1');

        // 1. Set Cooldown timestamp (60 seconds)
        Cache::put("otp_cooldown_{$type}_{$cleanDest}", time() + self::COOLDOWN_SECONDS, now()->addSeconds(self::COOLDOWN_SECONDS));

        // 2. Increment Destination Hourly Counter (1 hour TTL)
        $hourlyKey = "otp_hourly_{$type}_{$cleanDest}";
        $currentHourly = (int) Cache::get($hourlyKey, 0);
        Cache::put($hourlyKey, $currentHourly + 1, now()->addHour());

        // 3. Increment Destination Daily Counter (24 hours TTL)
        $dailyKey = "otp_daily_{$type}_{$cleanDest}";
        $currentDaily = (int) Cache::get($dailyKey, 0);
        Cache::put($dailyKey, $currentDaily + 1, now()->addDay());

        // 4. Increment IP 10-Minute Counter (10 minutes TTL)
        $ip10mKey = "otp_ip_10m_{$ip}";
        $currentIp10m = (int) Cache::get($ip10mKey, 0);
        Cache::put($ip10mKey, $currentIp10m + 1, now()->addMinutes(10));

        // 5. Increment IP Daily Counter (24 hours TTL)
        $ipDailyKey = "otp_ip_24h_{$ip}";
        $currentIpDaily = (int) Cache::get($ipDailyKey, 0);
        Cache::put($ipDailyKey, $currentIpDaily + 1, now()->addDay());

        Log::info("[OTP SECURITY] Dispatched OTP recorded for {$cleanDest}. IP: {$ip}. Cooldown set: " . self::COOLDOWN_SECONDS . "s");
    }

    /**
     * Get remaining cooldown seconds for a destination.
     */
    public static function getRemainingCooldown(string $destination, string $type = 'phone'): int
    {
        $cleanDest = preg_replace('/\s+/', '', strtolower(trim($destination)));
        $cooldownExpires = Cache::get("otp_cooldown_{$type}_{$cleanDest}");
        if ($cooldownExpires && is_numeric($cooldownExpires)) {
            $remaining = (int) ($cooldownExpires - time());
            return max(0, $remaining);
        }
        return 0;
    }
}
