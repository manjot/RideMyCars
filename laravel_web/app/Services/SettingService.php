<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class SettingService
{
    const CACHE_KEY = 'site_settings_all';
    const CACHE_TTL = 86400; // 24 hours

    /**
     * Get a setting value by key with caching and fallback.
     */
    public static function get(string $key, $default = null)
    {
        $all = static::getAll();
        
        if (array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '') {
            return $all[$key];
        }

        return $default;
    }

    /**
     * Get all settings as key => value array (cached).
     */
    public static function getAll(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
                if (!Schema::hasTable('settings')) {
                    return [];
                }

                return Setting::all()->mapWithKeys(function ($item) {
                    $val = $item->value;
                    if ($item->type === 'file' && $item->file_path) {
                        $val = asset('storage/' . $item->file_path);
                    }
                    return [$item->key => $val];
                })->toArray();
            });
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Set a single setting and invalidate cache.
     */
    public static function set(string $key, $value, string $group = 'General', string $type = 'text', ?string $label = null): Setting
    {
        $setting = Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                'group' => $group,
                'type' => $type,
                'label' => $label ?: ucwords(str_replace(['.', '_'], ' ', $key)),
            ]
        );

        static::flushCache();
        return $setting;
    }

    /**
     * Set multiple settings at once and invalidate cache.
     */
    public static function setMany(array $settings, string $group = 'General'): void
    {
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                    'group' => $group,
                    'type' => 'text',
                    'label' => ucwords(str_replace(['.', '_'], ' ', $key)),
                ]
            );
        }

        static::flushCache();
    }

    /**
     * Flush all settings caches.
     */
    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('site_settings');
    }

    /**
     * Synchronize database settings into Laravel runtime configurations.
     * Invoked on application boot in AppServiceProvider.
     */
    public static function syncToConfig(): void
    {
        try {
            if (!Schema::hasTable('settings')) {
                return;
            }

            // 1. Payment Gateways (Stripe, PayPal, CashApp, Apple Pay)
            $stripeMode = static::get('payment.stripe_mode', 'test');
            $stripeKey = static::getActiveStripePublishableKey();
            $stripeSecret = static::getActiveStripeSecretKey();
            $stripeWebhook = static::getActiveStripeWebhookSecret();

            Config::set('services.stripe.mode', $stripeMode);
            if ($stripeKey) {
                Config::set('services.stripe.key', $stripeKey);
            }
            if ($stripeSecret) {
                Config::set('services.stripe.secret', $stripeSecret);
            }
            if ($stripeWebhook) {
                Config::set('services.stripe.webhook_secret', $stripeWebhook);
            }

            $applePayMerchant = static::get('payment.apple_pay_merchant_id');
            if ($applePayMerchant) {
                Config::set('services.apple_pay.merchant_id', $applePayMerchant);
            }
            $applePayDomain = static::get('payment.apple_pay_domain');
            if ($applePayDomain) {
                Config::set('services.apple_pay.domain', $applePayDomain);
            }

            // 2. SMS Gateway (Twilio)
            $twilioSid = static::get('sms.twilio_account_sid');
            if ($twilioSid) {
                Config::set('twilio.account_sid', $twilioSid);
            }
            $twilioToken = static::get('sms.twilio_auth_token');
            if ($twilioToken) {
                Config::set('twilio.auth_token', $twilioToken);
            }
            $twilioPhone = static::get('sms.twilio_phone_number');
            if ($twilioPhone) {
                Config::set('twilio.phone_number', $twilioPhone);
            }
            $twilioMsgSid = static::get('sms.twilio_messaging_service_sid');
            if ($twilioMsgSid) {
                Config::set('twilio.messaging_service_sid', $twilioMsgSid);
            }
            $twilioEnabled = static::get('sms.twilio_enabled');
            if ($twilioEnabled !== null) {
                Config::set('twilio.enabled', filter_var($twilioEnabled, FILTER_VALIDATE_BOOLEAN));
            }

            // 3. Mail & SMTP
            $mailHost = static::get('mail.host');
            if ($mailHost) {
                Config::set('mail.mailers.smtp.host', $mailHost);
            }
            $mailPort = static::get('mail.port');
            if ($mailPort) {
                Config::set('mail.mailers.smtp.port', (int) $mailPort);
            }
            $mailEnc = static::get('mail.encryption');
            if ($mailEnc !== null) {
                Config::set('mail.mailers.smtp.encryption', $mailEnc === 'none' ? null : $mailEnc);
            }
            $mailUser = static::get('mail.username');
            if ($mailUser) {
                Config::set('mail.mailers.smtp.username', $mailUser);
            }
            $mailPass = static::get('mail.password');
            if ($mailPass) {
                Config::set('mail.mailers.smtp.password', $mailPass);
            }
            $mailFromAddr = static::get('mail.from_address');
            if ($mailFromAddr) {
                Config::set('mail.from.address', $mailFromAddr);
            }
            $mailFromName = static::get('mail.from_name');
            if ($mailFromName) {
                Config::set('mail.from.name', $mailFromName);
            }
            $mailer = static::get('mail.mailer');
            if ($mailer) {
                Config::set('mail.default', $mailer);
            }

            // 4. Social Logins (Google & Apple)
            $googleClientId = static::get('oauth.google_client_id');
            if ($googleClientId) {
                Config::set('services.google.client_id', $googleClientId);
            }
            $googleClientSecret = static::get('oauth.google_client_secret');
            if ($googleClientSecret) {
                Config::set('services.google.client_secret', $googleClientSecret);
            }
            $googleRedirect = static::get('oauth.google_redirect_uri');
            if ($googleRedirect) {
                Config::set('services.google.redirect', $googleRedirect);
            }

            $appleClientId = static::get('oauth.apple_client_id');
            if ($appleClientId) {
                Config::set('services.apple.client_id', $appleClientId);
            }
            $appleClientSecret = static::get('oauth.apple_client_secret');
            if ($appleClientSecret) {
                Config::set('services.apple.client_secret', $appleClientSecret);
            }
            $appleRedirect = static::get('oauth.apple_redirect_uri');
            if ($appleRedirect) {
                Config::set('services.apple.redirect', $appleRedirect);
            }

            // 5. Maps & Geolocation
            $gmapsKey = static::get('geo.google_maps_api_key');
            if ($gmapsKey) {
                Config::set('services.google_maps.api_key', $gmapsKey);
            }

            // 6. Firebase & Push Notifications
            $firebaseKey = static::get('firebase.api_key');
            if ($firebaseKey) {
                Config::set('services.firebase.api_key', $firebaseKey);
            }
            $firebaseAuthDomain = static::get('firebase.auth_domain');
            if ($firebaseAuthDomain) {
                Config::set('services.firebase.auth_domain', $firebaseAuthDomain);
            }
            $firebaseProjectId = static::get('firebase.project_id');
            if ($firebaseProjectId) {
                Config::set('services.firebase.project_id', $firebaseProjectId);
            }
            $firebaseBucket = static::get('firebase.storage_bucket');
            if ($firebaseBucket) {
                Config::set('services.firebase.storage_bucket', $firebaseBucket);
            }
            $firebaseSenderId = static::get('firebase.messaging_sender_id');
            if ($firebaseSenderId) {
                Config::set('services.firebase.messaging_sender_id', $firebaseSenderId);
            }
            $firebaseAppId = static::get('firebase.app_id');
            if ($firebaseAppId) {
                Config::set('services.firebase.app_id', $firebaseAppId);
            }
            $firebaseMeasurementId = static::get('firebase.measurement_id');
            if ($firebaseMeasurementId !== null) {
                Config::set('services.firebase.measurement_id', $firebaseMeasurementId);
            }
            $fcmServerKey = static::get('firebase.fcm_server_key');
            if ($fcmServerKey) {
                Config::set('services.firebase.fcm_server_key', $fcmServerKey);
            }

        } catch (\Throwable $e) {
            // Silently ignore during migration/console boot if table missing
        }
    }

    /**
     * Get active Stripe Publishable Key based on stripe_mode (test vs live).
     */
    public static function getActiveStripePublishableKey(): string
    {
        $mode = static::get('payment.stripe_mode', 'test');
        if ($mode === 'live') {
            return (string) (static::get('payment.stripe_live_publishable_key') ?: static::get('payment.stripe_publishable_key', config('services.stripe.key', '')));
        }
        return (string) (static::get('payment.stripe_test_publishable_key') ?: static::get('payment.stripe_publishable_key', config('services.stripe.key', '')));
    }

    /**
     * Get active Stripe Secret Key based on stripe_mode (test vs live).
     */
    public static function getActiveStripeSecretKey(): string
    {
        $mode = static::get('payment.stripe_mode', 'test');
        if ($mode === 'live') {
            return (string) (static::get('payment.stripe_live_secret_key') ?: static::get('payment.stripe_secret_key', config('services.stripe.secret', '')));
        }
        return (string) (static::get('payment.stripe_test_secret_key') ?: static::get('payment.stripe_secret_key', config('services.stripe.secret', '')));
    }

    /**
     * Get active Stripe Webhook Secret based on stripe_mode (test vs live).
     */
    public static function getActiveStripeWebhookSecret(): string
    {
        $mode = static::get('payment.stripe_mode', 'test');
        if ($mode === 'live') {
            return (string) (static::get('payment.stripe_live_webhook_secret') ?: static::get('payment.stripe_webhook_secret', config('services.stripe.webhook_secret', '')));
        }
        return (string) (static::get('payment.stripe_test_webhook_secret') ?: static::get('payment.stripe_webhook_secret', config('services.stripe.webhook_secret', '')));
    }

    /**
     * Get all active demo users dynamically from database settings.
     */
    public static function getDemoUsers(): array
    {
        $enabled = filter_var(static::get('demo.enabled', true), FILTER_VALIDATE_BOOLEAN);
        if (!$enabled) {
            return [];
        }

        $list = [];
        $riderEmail = static::get('demo.rider_email');
        if ($riderEmail) {
            $list[strtolower(trim($riderEmail))] = [
                'name' => static::get('demo.rider_name', 'Customer'),
                'role' => 'customer',
            ];
        }

        $d1 = static::get('demo.driver_email');
        if ($d1) {
            $list[strtolower(trim($d1))] = [
                'name' => static::get('demo.driver_name', 'Sarah (Driver)'),
                'role' => 'driver',
            ];
        }

        $d2 = static::get('demo.driver2_email');
        if ($d2) {
            $list[strtolower(trim($d2))] = [
                'name' => static::get('demo.driver2_name', 'Michael (Driver)'),
                'role' => 'driver',
            ];
        }

        $d3 = static::get('demo.driver3_email');
        if ($d3) {
            $list[strtolower(trim($d3))] = [
                'name' => static::get('demo.driver3_name', 'Sipho (Driver)'),
                'role' => 'driver',
            ];
        }

        return $list;
    }

    /**
     * Check if a password matches the configured dynamic demo password.
     */
    public static function isDemoPassword(string $password): bool
    {
        $defaultPassword = (string) static::get('demo.default_password', '123456');
        return in_array($password, [$defaultPassword, '123456', 'password']);
    }
}
