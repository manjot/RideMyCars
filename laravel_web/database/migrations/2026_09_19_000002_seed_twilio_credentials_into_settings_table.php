<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            [
                'key' => 'sms.twilio_enabled',
                'label' => 'Twilio SMS Gateway Enabled',
                'value' => (string) env('TWILIO_SMS_ENABLED', '1'),
                'group' => 'SMS Gateway',
                'type' => 'text',
            ],
            [
                'key' => 'sms.twilio_account_sid',
                'label' => 'Twilio Account SID',
                'value' => (string) (env('TWILIO_ACCOUNT_SID') ?: config('twilio.account_sid', '')),
                'group' => 'SMS Gateway',
                'type' => 'text',
            ],
            [
                'key' => 'sms.twilio_auth_token',
                'label' => 'Twilio Auth Token',
                'value' => (string) (env('TWILIO_AUTH_TOKEN') ?: config('twilio.auth_token', '')),
                'group' => 'SMS Gateway',
                'type' => 'text',
            ],
            [
                'key' => 'sms.twilio_phone_number',
                'label' => 'Twilio Sender Phone Number',
                'value' => (string) (env('TWILIO_PHONE_NUMBER') ?: config('twilio.phone_number', '+18555933328')),
                'group' => 'SMS Gateway',
                'type' => 'text',
            ],
            [
                'key' => 'sms.twilio_messaging_service_sid',
                'label' => 'Twilio Messaging Service SID',
                'value' => (string) (env('TWILIO_MESSAGING_SERVICE_SID') ?: config('twilio.messaging_service_sid', 'MG52943f1aec2747d5dc09206da8ff2c4e')),
                'group' => 'SMS Gateway',
                'type' => 'text',
            ],
            [
                'key' => 'sms.twilio_alphanumeric_sender',
                'label' => 'Twilio Alphanumeric Sender ID',
                'value' => (string) (env('TWILIO_ALPHANUMERIC_SENDER') ?: config('twilio.alphanumeric_sender', 'RideMyCars')),
                'group' => 'SMS Gateway',
                'type' => 'text',
            ],
        ];

        foreach ($settings as $setting) {
            // Only update if not empty or not yet in DB
            $existing = DB::table('settings')->where('key', $setting['key'])->first();
            if (!$existing) {
                DB::table('settings')->insert([
                    'key' => $setting['key'],
                    'label' => $setting['label'],
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'type' => $setting['type'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
            } elseif (!empty($setting['value'])) {
                DB::table('settings')->where('key', $setting['key'])->update([
                    'value' => $setting['value'],
                    'updated_at' => now(),
                ]);
            }
        }

        Cache::forget('site_settings');
        Cache::forget('site_settings_all');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep settings in DB to avoid accidental outage on rollback
    }
};
