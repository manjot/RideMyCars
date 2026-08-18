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

];
