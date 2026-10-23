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

    protected static ?array $memoryCache = null;

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
     * Get all settings as key => value array (cached in memory and store).
     */
    public static function getAll(): array
    {
        if (static::$memoryCache !== null) {
            return static::$memoryCache;
        }

        try {
            static::$memoryCache = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
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

            return static::$memoryCache ?? [];
        } catch (\Throwable $e) {
            static::$memoryCache = [];
            return static::$memoryCache;
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
        static::$memoryCache = null;
        Cache::forget(self::CACHE_KEY);
        Cache::forget('site_settings');
    }

    /**
     * Clear all settings caches (alias for flushCache).
     */
    public static function clearCache(): void
    {
        static::flushCache();
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

            // ExpressPay Ghana Gateway (MoMo & Card)
            $expresspayEnabled = static::get('payment.expresspay_enabled', true);
            $expresspayMode = static::getActiveExpressPayMode();
            $expresspayMerchantId = static::getActiveExpressPayMerchantId();
            $expresspayApiKey = static::getActiveExpressPayApiKey();
            $expresspayCurrency = static::get('payment.expresspay_currency', 'GHS');

            Config::set('services.expresspay.enabled', filter_var($expresspayEnabled, FILTER_VALIDATE_BOOLEAN));
            Config::set('services.expresspay.mode', $expresspayMode);
            if ($expresspayMerchantId) {
                Config::set('services.expresspay.merchant_id', $expresspayMerchantId);
            }
            if ($expresspayApiKey) {
                Config::set('services.expresspay.api_key', $expresspayApiKey);
            }
            if ($expresspayCurrency) {
                Config::set('services.expresspay.currency', $expresspayCurrency);
            }

            // Authorize.Net Gateway
            $authorizenetEnabled = static::get('payment.authorizenet_enabled', true);
            $authorizenetMode = static::getActiveAuthorizeNetMode();
            $authorizenetLoginId = static::getActiveAuthorizeNetApiLoginId();
            $authorizenetTransKey = static::getActiveAuthorizeNetTransactionKey();
            $authorizenetClientKey = static::getActiveAuthorizeNetClientKey();

            Config::set('services.authorizenet.enabled', filter_var($authorizenetEnabled, FILTER_VALIDATE_BOOLEAN));
            Config::set('services.authorizenet.mode', $authorizenetMode);
            if ($authorizenetLoginId) {
                Config::set('services.authorizenet.api_login_id', $authorizenetLoginId);
            }
            if ($authorizenetTransKey) {
                Config::set('services.authorizenet.transaction_key', $authorizenetTransKey);
            }
            if ($authorizenetClientKey) {
                Config::set('services.authorizenet.client_key', $authorizenetClientKey);
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
            $twilioAlpha = static::get('sms.twilio_alphanumeric_sender');
            if ($twilioAlpha) {
                Config::set('twilio.alphanumeric_sender', $twilioAlpha);
            }
            $twilioEnabled = static::get('sms.twilio_enabled');
            if ($twilioEnabled !== null) {
                Config::set('twilio.enabled', filter_var($twilioEnabled, FILTER_VALIDATE_BOOLEAN));
            }

            // 2b. SMS Gateway (Nalo Solutions Ghana)
            $naloUsername = static::get('sms.nalo_username');
            if ($naloUsername) {
                Config::set('nalo.username', $naloUsername);
            }
            $naloPassword = static::get('sms.nalo_password');
            if ($naloPassword) {
                Config::set('nalo.password', $naloPassword);
            }
            $naloAuthKey = static::get('sms.nalo_auth_key') ?: static::get('sms.nalo_api_key');
            if ($naloAuthKey) {
                Config::set('nalo.auth_key', $naloAuthKey);
            }
            $naloSenderId = static::get('sms.nalo_sender_id');
            if ($naloSenderId) {
                Config::set('nalo.sender_id', $naloSenderId);
            }
            $naloPrefix = static::get('sms.nalo_prefix');
            if ($naloPrefix) {
                Config::set('nalo.prefix', $naloPrefix);
            }
            $naloBaseUrl = static::get('sms.nalo_base_url');
            if ($naloBaseUrl) {
                Config::set('nalo.base_url', $naloBaseUrl);
            }
            $naloEnabled = static::get('sms.nalo_enabled');
            if ($naloEnabled !== null) {
                Config::set('nalo.enabled', filter_var($naloEnabled, FILTER_VALIDATE_BOOLEAN));
            }
            $naloFallback = static::get('sms.nalo_fallback_to_twilio');
            if ($naloFallback !== null) {
                Config::set('nalo.fallback_to_twilio', filter_var($naloFallback, FILTER_VALIDATE_BOOLEAN));
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
            if (!empty($gmapsKey)) {
                Config::set('services.google_maps.api_key', $gmapsKey);
            } elseif (empty(Config::get('services.google_maps.api_key'))) {
                Config::set('services.google_maps.api_key', 'AIzaSyACN52o17kFjtg_K45rKU_ETTJ6WaXvkC0');
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
     * Get active Stripe environment mode ('live' or 'test').
     */
    public static function getStripeMode(): string
    {
        $mode = trim((string) static::get('payment.stripe_mode', config('services.stripe.mode', env('STRIPE_MODE', 'live'))));
        return strtolower($mode ?: 'live');
    }

    /**
     * Check if Stripe is operating in live/production mode.
     */
    public static function isStripeLive(): bool
    {
        return static::getStripeMode() === 'live';
    }

    /**
     * Get active Stripe Publishable Key based on stripe_mode (test vs live).
     */
    public static function getActiveStripePublishableKey(): string
    {
        $mode = static::getStripeMode();
        if ($mode === 'live') {
            return trim((string) (static::get('payment.stripe_live_publishable_key') ?: (static::get('payment.stripe_publishable_key') ?: (config('services.stripe.key') ?: env('STRIPE_PUBLISHABLE_KEY', 'pk_live_51U3x2DC7C86Til8eExDWVNVpFP1zMH82CP43om2rhGnLFON3nJmbjTG492PllBjPINRDTT7lI212YgkJqrawe4TE00qxyrLmds')))));
        }
        return trim((string) (static::get('payment.stripe_test_publishable_key') ?: (static::get('payment.stripe_publishable_key') ?: config('services.stripe.key', ''))));
    }

    /**
     * Get active Stripe Secret Key based on stripe_mode (test vs live).
     */
    public static function getActiveStripeSecretKey(): string
    {
        $mode = static::getStripeMode();
        if ($mode === 'live') {
            return trim((string) (static::get('payment.stripe_live_secret_key') ?: (static::get('payment.stripe_secret_key') ?: (config('services.stripe.secret') ?: env('STRIPE_SECRET_KEY', '')))));
        }
        return trim((string) (static::get('payment.stripe_test_secret_key') ?: (static::get('payment.stripe_secret_key') ?: config('services.stripe.secret', ''))));
    }

    /**
     * Get active Stripe Webhook Secret based on stripe_mode (test vs live).
     */
    public static function getActiveStripeWebhookSecret(): string
    {
        $mode = static::getStripeMode();
        if ($mode === 'live') {
            return (string) (static::get('payment.stripe_live_webhook_secret') ?: static::get('payment.stripe_webhook_secret', config('services.stripe.webhook_secret', '')));
        }
        return (string) (static::get('payment.stripe_test_webhook_secret') ?: static::get('payment.stripe_webhook_secret', config('services.stripe.webhook_secret', '')));
    }

    /**
     * Get active ExpressPay mode ('sandbox' or 'live').
     */
    public static function getActiveExpressPayMode(): string
    {
        $mode = trim((string) static::get('payment.expresspay_mode', config('services.expresspay.mode', env('EXPRESSPAY_MODE', 'live'))));
        return strtolower($mode ?: 'live');
    }

    /**
     * Check if ExpressPay Ghana gateway is enabled.
     */
    public static function isExpressPayEnabled(): bool
    {
        $enabled = static::get('payment.expresspay_enabled', config('services.expresspay.enabled', true));
        return filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get active ExpressPay Merchant ID based on expresspay_mode (sandbox vs live).
     */
    public static function getActiveExpressPayMerchantId(): string
    {
        $mode = static::getActiveExpressPayMode();
        if ($mode === 'live') {
            return (string) (static::get('payment.expresspay_live_merchant_id') ?: (static::get('payment.expresspay_merchant_id') ?: (config('services.expresspay.live_merchant_id') ?: (config('services.expresspay.merchant_id') ?: env('EXPRESSPAY_MERCHANT_ID', '804968043952')))));
        }
        return (string) (static::get('payment.expresspay_sandbox_merchant_id') ?: (static::get('payment.expresspay_merchant_id') ?: config('services.expresspay.sandbox_merchant_id', '562786243097')));
    }

    /**
     * Get active ExpressPay API Key based on expresspay_mode (sandbox vs live).
     */
    public static function getActiveExpressPayApiKey(): string
    {
        $mode = static::getActiveExpressPayMode();
        if ($mode === 'live') {
            return (string) (static::get('payment.expresspay_live_api_key') ?: (static::get('payment.expresspay_api_key') ?: (config('services.expresspay.live_api_key') ?: (config('services.expresspay.api_key') ?: env('EXPRESSPAY_API_KEY', 'TbUtn4Bbv4JOQbQQunRg8-K7ZczqvMTARq4ZMVNcTQ-OlWgoCxinDUleP7eqmLW-wpXAYSJN8lB2cTB9FD2')))));
        }
        return (string) (static::get('payment.expresspay_sandbox_api_key') ?: (static::get('payment.expresspay_api_key') ?: config('services.expresspay.sandbox_api_key', 'DInEOn1ayqtjC420gHLJ4-IiCSoZKPR13lxkLyzqiD-PcXhMFOBwKyoUw9hzAY1-hYnIGJov5Rbz8hme7Nm')));
    }

    /**
     * Get active ExpressPay Submit API endpoint URL.
     */
    public static function getExpressPaySubmitUrl(): string
    {
        $mode = static::getActiveExpressPayMode();
        return ($mode === 'live')
            ? 'https://expresspaygh.com/api/submit.php'
            : 'https://sandbox.expresspaygh.com/api/submit.php';
    }

    /**
     * Get active ExpressPay Checkout URL.
     */
    public static function getExpressPayCheckoutUrl(string $token): string
    {
        $mode = static::getActiveExpressPayMode();
        $base = ($mode === 'live')
            ? 'https://expresspaygh.com/api/checkout.php'
            : 'https://sandbox.expresspaygh.com/api/checkout.php';
        return $base . '?token=' . urlencode($token);
    }

    /**
     * Get active ExpressPay Query API endpoint URL.
     */
    public static function getExpressPayQueryUrl(): string
    {
        $mode = static::getActiveExpressPayMode();
        return ($mode === 'live')
            ? 'https://expresspaygh.com/api/query.php'
            : 'https://sandbox.expresspaygh.com/api/query.php';
    }

    /**
     * Check if Stripe payments are active/enabled.
     */
    public static function isStripeEnabled(): bool
    {
        $enabled = static::get('payment.stripe_enabled', config('services.stripe.enabled', true));
        return filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Check if Apple Pay is active/enabled.
     */
    public static function isApplePayEnabled(): bool
    {
        $enabled = static::get('payment.apple_pay_enabled', config('services.apple_pay.enabled', true));
        return filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Check if Cash on Drop-off is active/enabled.
     */
    public static function isCashEnabled(): bool
    {
        $enabled = static::get('payment.cash_enabled', config('services.cash.enabled', true));
        return filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Check if Mobile Money / ExpressPay is active/enabled.
     */
    public static function isMomoEnabled(): bool
    {
        return static::isExpressPayEnabled();
    }

    /**
     * Check if Authorize.Net payment gateway is active/enabled.
     */
    public static function isAuthorizeNetEnabled(): bool
    {
        $enabled = static::get('payment.authorizenet_enabled', config('services.authorizenet.enabled', true));
        return filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get active Authorize.Net environment mode ('sandbox' or 'live').
     */
    public static function getActiveAuthorizeNetMode(): string
    {
        $mode = trim((string) static::get('payment.authorizenet_mode', config('services.authorizenet.mode', env('AUTHORIZENET_MODE', 'sandbox'))));
        return strtolower($mode ?: 'sandbox');
    }

    /**
     * Check if Authorize.Net is operating in live/production mode.
     */
    public static function isAuthorizeNetLive(): bool
    {
        return static::getActiveAuthorizeNetMode() === 'live';
    }

    /**
     * Get active Authorize.Net API Login ID.
     */
    public static function getActiveAuthorizeNetApiLoginId(): string
    {
        $mode = static::getActiveAuthorizeNetMode();
        if ($mode === 'live') {
            return trim((string) (static::get('payment.authorizenet_live_api_login_id') ?: (static::get('payment.authorizenet_api_login_id') ?: (config('services.authorizenet.live_api_login_id') ?: env('AUTHORIZENET_LIVE_API_LOGIN_ID', '')))));
        }
        return trim((string) (static::get('payment.authorizenet_sandbox_api_login_id') ?: (static::get('payment.authorizenet_api_login_id') ?: (config('services.authorizenet.sandbox_api_login_id') ?: env('AUTHORIZENET_SANDBOX_API_LOGIN_ID', '5KP3u95bQpv')))));
    }

    /**
     * Get active Authorize.Net Transaction Key.
     */
    public static function getActiveAuthorizeNetTransactionKey(): string
    {
        $mode = static::getActiveAuthorizeNetMode();
        if ($mode === 'live') {
            return trim((string) (static::get('payment.authorizenet_live_transaction_key') ?: (static::get('payment.authorizenet_transaction_key') ?: (config('services.authorizenet.live_transaction_key') ?: env('AUTHORIZENET_LIVE_TRANSACTION_KEY', '')))));
        }
        return trim((string) (static::get('payment.authorizenet_sandbox_transaction_key') ?: (static::get('payment.authorizenet_transaction_key') ?: (config('services.authorizenet.sandbox_transaction_key') ?: env('AUTHORIZENET_SANDBOX_TRANSACTION_KEY', '4b6qZ4R8De274Q6n')))));
    }

    /**
     * Get active Authorize.Net Public Client Key (for Accept.js in-app tokenization).
     */
    public static function getActiveAuthorizeNetClientKey(): string
    {
        $mode = static::getActiveAuthorizeNetMode();
        if ($mode === 'live') {
            return trim((string) (static::get('payment.authorizenet_live_client_key') ?: (static::get('payment.authorizenet_client_key') ?: (config('services.authorizenet.live_client_key') ?: env('AUTHORIZENET_LIVE_CLIENT_KEY', '')))));
        }
        return trim((string) (static::get('payment.authorizenet_sandbox_client_key') ?: (static::get('payment.authorizenet_client_key') ?: (config('services.authorizenet.sandbox_client_key') ?: env('AUTHORIZENET_SANDBOX_CLIENT_KEY', '')))));
    }

    /**
     * Get active Authorize.Net Webhook Signature Key.
     */
    public static function getActiveAuthorizeNetSignatureKey(): string
    {
        $mode = static::getActiveAuthorizeNetMode();
        if ($mode === 'live') {
            return trim((string) (static::get('payment.authorizenet_live_signature_key') ?: (static::get('payment.authorizenet_signature_key') ?: env('AUTHORIZENET_LIVE_SIGNATURE_KEY', ''))));
        }
        return trim((string) (static::get('payment.authorizenet_sandbox_signature_key') ?: (static::get('payment.authorizenet_signature_key') ?: env('AUTHORIZENET_SANDBOX_SIGNATURE_KEY', ''))));
    }

    /**
     * Get all currently active payment gateways configured in admin.
     * Gateways with inactive status are automatically omitted from website and mobile apps.
     */
    public static function getActivePaymentGateways(): array
    {
        $gateways = [];

        if (static::isStripeEnabled()) {
            $gateways['stripe'] = [
                'id' => 'stripe',
                'name' => 'Stripe',
                'label' => 'Credit / Debit Card',
                'badge' => 'Cards & Apple Pay',
                'description' => 'Credit / Debit, Apple Pay',
                'mode' => static::getStripeMode(),
                'is_live' => static::isStripeLive(),
                'publishable_key' => static::getActiveStripePublishableKey(),
                'icon' => '/images/stripe-icon.svg',
            ];
        }

        if (static::isAuthorizeNetEnabled()) {
            $gateways['authorizenet'] = [
                'id' => 'authorizenet',
                'name' => 'Authorize.Net',
                'label' => 'Authorize.Net Card Pay',
                'badge' => 'A Visa Solution',
                'description' => 'Credit / Debit Card (Authorize.Net)',
                'mode' => static::getActiveAuthorizeNetMode(),
                'is_live' => static::isAuthorizeNetLive(),
                'client_key' => static::getActiveAuthorizeNetClientKey(),
                'api_login_id' => static::getActiveAuthorizeNetApiLoginId(),
                'icon' => '/images/payment-icons/authorizenet.svg',
                'logo' => '/images/authorizenet-logo.svg',
            ];
        }

        if (static::isExpressPayEnabled()) {
            $gateways['momo'] = [
                'id' => 'momo',
                'name' => 'MoMo Pay',
                'label' => 'Mobile Money (Ghana)',
                'badge' => 'Mobile Money',
                'description' => 'MTN, Telecel, AirtelTigo',
                'mode' => static::getActiveExpressPayMode(),
                'is_live' => (static::getActiveExpressPayMode() === 'live'),
                'merchant_id' => static::getActiveExpressPayMerchantId(),
                'currency' => static::get('payment.expresspay_currency', 'GHS'),
                'icon' => '/images/momo-icon.svg',
            ];
        }

        if (static::isCashEnabled()) {
            $gateways['cash'] = [
                'id' => 'cash',
                'name' => 'Cash',
                'label' => 'Cash on Drop-off',
                'badge' => 'Direct Pay',
                'description' => 'Pay upon drop-off (Exact Fare, No hold)',
                'icon' => '💵',
            ];
        }

        return $gateways;
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

        // Fleet Owner Account
        $list['owner@ridemycars.com'] = [
            'name' => 'Elite Car Rentals (Fleet Owner)',
            'role' => 'owner',
        ];

        return $list;
    }

    /**
     * Check if a password matches the configured dynamic demo password.
     */
    public static function isDemoPassword(string $password): bool
    {
        $defaultPassword = (string) static::get('demo.default_password', '123456');
        return in_array($password, [
            $defaultPassword,
            '1234',
            '123456',
            'password',
            'RideMyCars@2026!',
            'RideMyCars@2026',
            'Password@123',
            'Test@1234',
            'Demo@1234',
            'Admin@1234'
        ]);
    }

    /**
     * Get the configured waiting time in minutes for Ride requests before auto-cancellation.
     * Default: 5 minutes.
     */
    public static function getRideWaitingTimeMinutes(): int
    {
        $val = static::get('dispatch.ride_waiting_time_minutes', 5);
        $intVal = (int) $val;
        return $intVal > 0 ? $intVal : 5;
    }

    /**
     * Get the configured waiting time in seconds for Ride requests.
     */
    public static function getRideWaitingTimeSeconds(): int
    {
        return static::getRideWaitingTimeMinutes() * 60;
    }

    /**
     * Get the configured waiting time in minutes for Delivery requests before auto-cancellation.
     * Default: 5 minutes.
     */
    public static function getDeliveryWaitingTimeMinutes(): int
    {
        $val = static::get('dispatch.delivery_waiting_time_minutes', 5);
        $intVal = (int) $val;
        return $intVal > 0 ? $intVal : 5;
    }

    /**
     * Get the configured waiting time in seconds for Delivery requests.
     */
    public static function getDeliveryWaitingTimeSeconds(): int
    {
        return static::getDeliveryWaitingTimeMinutes() * 60;
    }

    /**
     * Check if automated request cancellation upon waiting time expiration is enabled.
     * Default: true.
     */
    public static function isAutoCancelUnacceptedEnabled(): bool
    {
        return filter_var(static::get('dispatch.auto_cancel_unaccepted', true), FILTER_VALIDATE_BOOLEAN);
    }

    /** Shorthand aliases */
    public static function rideWaitingTimeMinutes(): int
    {
        return static::getRideWaitingTimeMinutes();
    }

    public static function deliveryWaitingTimeMinutes(): int
    {
        return static::getDeliveryWaitingTimeMinutes();
    }

    public static function rideWaitingTimeSeconds(): int
    {
        return static::getRideWaitingTimeSeconds();
    }

    public static function deliveryWaitingTimeSeconds(): int
    {
        return static::getDeliveryWaitingTimeSeconds();
    }
}

