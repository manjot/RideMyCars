<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'veriff' => [
        'api_key' => env('VERIFF_API_KEY'),
        'secret_key' => env('VERIFF_SECRET_KEY'),
        'url' => env('VERIFF_API_URL', 'https://stationapi.veriff.com/v1'),
    ],

    'onfido' => [
        'api_token' => env('ONFIDO_API_TOKEN'),
        'url' => env('ONFIDO_API_URL', 'https://api.onfido.com/v3.4'),
    ],

    'checkr' => [
        'api_key' => env('CHECKR_API_KEY'),
        'env' => env('CHECKR_ENV', 'sandbox'),
    ],

    'apple_pay' => [
        'merchant_id' => env('APPLE_PAY_MERCHANT_ID'),
        'domain' => env('APPLE_PAY_DOMAIN'),
        'cert_path' => env('APPLE_PAY_CERT_PATH'),
    ],

    'cashapp' => [
        'client_id' => env('CASHAPP_CLIENT_ID'),
        'secret' => env('CASHAPP_SECRET'),
        'env' => env('CASHAPP_ENV', 'sandbox'),
    ],

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'stripe' => [
        'key' => env('STRIPE_PUBLISHABLE_KEY', env('STRIPE_KEY')),
        'secret' => env('STRIPE_SECRET_KEY', env('STRIPE_SECRET')),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'firebase' => [
        'api_key' => env('FIREBASE_API_KEY'),
        'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
        'app_id' => env('FIREBASE_APP_ID'),
        'measurement_id' => env('FIREBASE_MEASUREMENT_ID'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', 'https://ridemycars.com/auth/google/callback'),
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
        'client_secret' => env('APPLE_CLIENT_SECRET'),
        'redirect' => env('APPLE_REDIRECT_URI', 'https://ridemycars.com/auth/apple/callback'),
    ],

    'momo' => [
        'api_user' => env('MOMO_API_USER'),
        'api_key' => env('MOMO_API_KEY'),
        'subscription_key' => env('MOMO_SUBSCRIPTION_KEY'),
        'target_environment' => env('MOMO_TARGET_ENV', 'sandbox'),
        'currency' => env('MOMO_CURRENCY', 'GHS'),
        'callback_url' => env('MOMO_CALLBACK_URL', 'https://ridemycars.com/api/payment/momo/callback'),
    ],

    'expresspay' => [
        'enabled' => env('EXPRESSPAY_ENABLED', true),
        'mode' => env('EXPRESSPAY_MODE', 'sandbox'),
        'merchant_id' => env('EXPRESSPAY_MERCHANT_ID', '562786243097'),
        'api_key' => env('EXPRESSPAY_API_KEY', 'DInEOn1ayqtjC420gHLJ4-IiCSoZKPR13lxkLyzqiD-PcXhMFOBwKyoUw9hzAY1-hYnIGJov5Rbz8hme7Nm'),
        'sandbox_merchant_id' => env('EXPRESSPAY_SANDBOX_MERCHANT_ID', '562786243097'),
        'sandbox_api_key' => env('EXPRESSPAY_SANDBOX_API_KEY', 'DInEOn1ayqtjC420gHLJ4-IiCSoZKPR13lxkLyzqiD-PcXhMFOBwKyoUw9hzAY1-hYnIGJov5Rbz8hme7Nm'),
        'live_merchant_id' => env('EXPRESSPAY_LIVE_MERCHANT_ID', ''),
        'live_api_key' => env('EXPRESSPAY_LIVE_API_KEY', ''),
        'currency' => env('EXPRESSPAY_CURRENCY', 'GHS'),
    ],

];

