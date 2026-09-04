<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Twilio Account Credentials
    |--------------------------------------------------------------------------
    |
    | These credentials can be found in the Twilio Console dashboard:
    | https://console.twilio.com
    |
    */
    'account_sid' => env('TWILIO_ACCOUNT_SID', ''),
    'auth_token' => env('TWILIO_AUTH_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Twilio Sender Phone Number or Messaging Service SID
    |--------------------------------------------------------------------------
    |
    | Use an active E.164 Twilio phone number (e.g., +12345678901) or a
    | Messaging Service SID (MGxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx).
    |
    */
    'phone_number' => env('TWILIO_PHONE_NUMBER', ''),
    'messaging_service_sid' => env('TWILIO_MESSAGING_SERVICE_SID', ''),

    /*
    |--------------------------------------------------------------------------
    | SMS Default Settings
    |--------------------------------------------------------------------------
    */
    'enabled' => env('TWILIO_SMS_ENABLED', true),
    'timeout' => env('TWILIO_TIMEOUT', 15),
];
