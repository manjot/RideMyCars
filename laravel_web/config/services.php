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

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
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
        'key' => 'pk_test_51U3x2DC7C86Til8eAZJGEFBhLZrMFHIcevu4MkguwQEou96bLAwB55DBluqtKrWy2n2McEmV0u3scO63VsuNSa8K00GGo8dqfA',
        'secret' => 'sk_test_51U3x2DC7C86Til8e3eB2j2fEsobrRVVfHlSwzMGrLfoeqHVI8U1zGoJCpzyhiQQMIBKyP9eQ7Be6pcTa5UPcQf5o00F59JZR7i',
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

];
