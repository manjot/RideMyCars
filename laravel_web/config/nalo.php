<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Nalo Solutions Ghana SMS Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Credentials and API settings for Nalo Solutions SMS Gateway
    | Portal: https://app.nalosolutions.com/app/send-sms
    | API Documentation: https://documenter.getpostman.com/view/7705958/Uyr7Hydn
    |
    */
    'enabled' => env('NALO_SMS_ENABLED', true),

    'username' => env('NALO_SMS_USERNAME', 'Ridemycars'),
    'password' => env('NALO_SMS_PASSWORD', 'wEST123456#'),
    'auth_key' => env('NALO_SMS_AUTH_KEY', ''),

    // Approved Nalo Alphanumeric Sender ID (e.g. RIDEMYCARS, up to 11 characters)
    'sender_id' => env('NALO_SMS_SENDER_ID', 'RIDEMYCARS'),

    // Reseller or routing prefix (default 'Resl_Nalo')
    'prefix' => env('NALO_SMS_PREFIX', 'Resl_Nalo'),

    // Base API URL
    'base_url' => env('NALO_SMS_BASE_URL', 'https://sms.nalosolutions.com/smsbackend'),

    // HTTP timeout in seconds
    'timeout' => env('NALO_SMS_TIMEOUT', 15),

    // Enable secondary SMS fallback to Twilio if Nalo delivery returns an error
    'fallback_to_twilio' => env('NALO_FALLBACK_TO_TWILIO', true),
];
