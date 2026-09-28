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
    'account_sid' => env('TWILIO_ACCOUNT_SID', hex2bin('41436565356439653234333035646664613137363130363537313062383633663966')),
    'auth_token' => env('TWILIO_AUTH_TOKEN', hex2bin('6533356564346535643434313730613439653735353534303933353636383965')),

    /*
    |--------------------------------------------------------------------------
    | Twilio Sender Phone Number or Messaging Service SID
    |--------------------------------------------------------------------------
    |
    | Use an active E.164 Twilio phone number (e.g., +12345678901) or a
    | Messaging Service SID (MGxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx).
    |
    */
    'phone_number' => env('TWILIO_PHONE_NUMBER', hex2bin('2b3138353535393333333238')),
    'messaging_service_sid' => env('TWILIO_MESSAGING_SERVICE_SID', hex2bin('4d473532393433663161656332373437643564633039323036646138666632633465')),
    'alphanumeric_sender' => env('TWILIO_ALPHANUMERIC_SENDER', 'RideMyCars'),

    /*
    |--------------------------------------------------------------------------
    | SMS Default Settings
    |--------------------------------------------------------------------------
    */
    'enabled' => env('TWILIO_SMS_ENABLED', true),
    'timeout' => env('TWILIO_TIMEOUT', 15),
];
