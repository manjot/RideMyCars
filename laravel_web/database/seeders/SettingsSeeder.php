<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Home Page
            ['key' => 'home.hero.title', 'label' => 'Home - Hero Title', 'value' => 'One App.<br><span class="text-orange-500">Three Ways</span><br>to Move.', 'group' => 'Home Page', 'type' => 'text'],
            ['key' => 'home.hero.subtitle', 'label' => 'Home - Hero Subtitle', 'value' => 'Book a ride, rent a vehicle, or hire a professional driver — all from a single platform built for the modern traveler.', 'group' => 'Home Page', 'type' => 'textarea'],
            ['key' => 'home.features.title', 'label' => 'Home - Features Title', 'value' => 'Everything you need. Nothing you don\'t.', 'group' => 'Home Page', 'type' => 'text'],
            
            // Footer
            ['key' => 'footer.support_email', 'label' => 'Footer - Support Email', 'value' => 'support@ridemycars.com', 'group' => 'Footer', 'type' => 'text'],
            ['key' => 'footer.support_phone', 'label' => 'Footer - Support Phone', 'value' => '+1 855 203 3177', 'group' => 'Footer', 'type' => 'text'],
            ['key' => 'footer.location', 'label' => 'Footer - Location', 'value' => 'Washington, DC', 'group' => 'Footer', 'type' => 'text'],
            ['key' => 'footer.copyright', 'label' => 'Footer - Copyright', 'value' => '© 2026 New Development Finance Group Pty Ltd. All rights reserved.', 'group' => 'Footer', 'type' => 'text'],

            // Social Media
            ['key' => 'social.instagram_url', 'label' => 'Social - Instagram Profile URL', 'value' => 'https://www.instagram.com/ridemycars1?igsi=ZHc2ZjltdHdiaDNj&utm_source=qr', 'group' => 'Social Media', 'type' => 'text'],
            ['key' => 'social.x_url', 'label' => 'Social - X (Twitter) Profile URL', 'value' => 'https://x.com/ridemycars', 'group' => 'Social Media', 'type' => 'text'],
            ['key' => 'social.tiktok_url', 'label' => 'Social - TikTok Profile URL', 'value' => 'https://www.tiktok.com/@ridemycars', 'group' => 'Social Media', 'type' => 'text'],
            ['key' => 'social.facebook_url', 'label' => 'Social - Facebook Profile URL', 'value' => 'https://www.facebook.com/profile.php?id=61594184214102', 'group' => 'Social Media', 'type' => 'text'],
            ['key' => 'social.linkedin_url', 'label' => 'Social - LinkedIn Profile URL', 'value' => 'https://www.linkedin.com/in/ride-mycars-587b03432', 'group' => 'Social Media', 'type' => 'text'],

            // App Links
            ['key' => 'app.ios_link', 'label' => 'App - iOS Download Link', 'value' => '#', 'group' => 'App Links', 'type' => 'text'],
            ['key' => 'app.android_link', 'label' => 'App - Android Download Link', 'value' => '#', 'group' => 'App Links', 'type' => 'text'],

            // Financial & Commission Rules
            ['key' => 'ride_hailing.platform_commission', 'label' => 'Ride Hailing - Platform Commission (%)', 'value' => '10', 'group' => 'Commissions', 'type' => 'text'],
            ['key' => 'ride_hailing.maintenance_fee_percent', 'label' => 'Ride Hailing - App Maintenance Fee (% of Owner Share)', 'value' => '2.5', 'group' => 'Commissions', 'type' => 'text'],
            ['key' => 'ride_hailing.gateway_fee_absorber', 'label' => 'Ride Hailing - Gateway Fee Absorber (passenger, platform, fleet_owner)', 'value' => 'fleet_owner', 'group' => 'Commissions', 'type' => 'text'],
            ['key' => 'driver_hiring.platform_commission', 'label' => 'Driver Hiring - Platform Commission (%)', 'value' => '15', 'group' => 'Commissions', 'type' => 'text'],
            ['key' => 'vehicle_rental.platform_commission', 'label' => 'Vehicle Rental - Platform Commission (%)', 'value' => '20', 'group' => 'Commissions', 'type' => 'text'],

            // Payment Gateways
            ['key' => 'payment.stripe_enabled', 'label' => 'Stripe Gateway Enabled', 'value' => '1', 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.stripe_mode', 'label' => 'Active Stripe Mode (test / live)', 'value' => 'test', 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.stripe_test_publishable_key', 'label' => 'Stripe Test Publishable Key', 'value' => env('STRIPE_TEST_PUBLISHABLE_KEY', env('STRIPE_PUBLISHABLE_KEY', '')), 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.stripe_test_secret_key', 'label' => 'Stripe Test Secret Key', 'value' => env('STRIPE_TEST_SECRET_KEY', env('STRIPE_SECRET_KEY', '')), 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.stripe_test_webhook_secret', 'label' => 'Stripe Test Webhook Secret', 'value' => env('STRIPE_TEST_WEBHOOK_SECRET', ''), 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.stripe_live_publishable_key', 'label' => 'Stripe Live Publishable Key', 'value' => env('STRIPE_LIVE_PUBLISHABLE_KEY', ''), 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.stripe_live_secret_key', 'label' => 'Stripe Live Secret Key', 'value' => env('STRIPE_LIVE_SECRET_KEY', ''), 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.stripe_live_webhook_secret', 'label' => 'Stripe Live Webhook Secret', 'value' => env('STRIPE_LIVE_WEBHOOK_SECRET', ''), 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.stripe_publishable_key', 'label' => 'Stripe Publishable Key (Active)', 'value' => env('STRIPE_PUBLISHABLE_KEY', ''), 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.stripe_secret_key', 'label' => 'Stripe Secret Key (Active)', 'value' => env('STRIPE_SECRET_KEY', ''), 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.stripe_webhook_secret', 'label' => 'Stripe Webhook Secret (Active)', 'value' => env('STRIPE_WEBHOOK_SECRET', ''), 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.apple_pay_enabled', 'label' => 'Apple Pay Enabled', 'value' => '1', 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.apple_pay_merchant_id', 'label' => 'Apple Pay Merchant ID', 'value' => env('APPLE_PAY_MERCHANT_ID', 'merchant.com.ridemycars'), 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.apple_pay_domain', 'label' => 'Apple Pay Verified Domain', 'value' => env('APPLE_PAY_DOMAIN', 'ridemycars.com'), 'group' => 'Payment Gateways', 'type' => 'text'],

            // ExpressPay Ghana Gateway
            ['key' => 'payment.expresspay_enabled', 'label' => 'ExpressPay Ghana Gateway Enabled', 'value' => '1', 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.expresspay_mode', 'label' => 'Active ExpressPay Mode (sandbox / live)', 'value' => env('EXPRESSPAY_MODE', 'sandbox'), 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.expresspay_sandbox_merchant_id', 'label' => 'ExpressPay Sandbox Merchant ID', 'value' => env('EXPRESSPAY_SANDBOX_MERCHANT_ID', '562786243097'), 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.expresspay_sandbox_api_key', 'label' => 'ExpressPay Sandbox API Key', 'value' => env('EXPRESSPAY_SANDBOX_API_KEY', 'DInEOn1ayqtjC420gHLJ4-IiCSoZKPR13lxkLyzqiD-PcXhMFOBwKyoUw9hzAY1-hYnIGJov5Rbz8hme7Nm'), 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.expresspay_live_merchant_id', 'label' => 'ExpressPay Live Merchant ID', 'value' => env('EXPRESSPAY_LIVE_MERCHANT_ID', ''), 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.expresspay_live_api_key', 'label' => 'ExpressPay Live API Key', 'value' => env('EXPRESSPAY_LIVE_API_KEY', ''), 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.expresspay_merchant_id', 'label' => 'ExpressPay Merchant ID (Active)', 'value' => env('EXPRESSPAY_MERCHANT_ID', '562786243097'), 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.expresspay_api_key', 'label' => 'ExpressPay API Key (Active)', 'value' => env('EXPRESSPAY_API_KEY', 'DInEOn1ayqtjC420gHLJ4-IiCSoZKPR13lxkLyzqiD-PcXhMFOBwKyoUw9hzAY1-hYnIGJov5Rbz8hme7Nm'), 'group' => 'Payment Gateways', 'type' => 'text'],
            ['key' => 'payment.expresspay_currency', 'label' => 'ExpressPay Default Currency', 'value' => env('EXPRESSPAY_CURRENCY', 'GHS'), 'group' => 'Payment Gateways', 'type' => 'text'],

            // SMS Gateway (Twilio)
            ['key' => 'sms.twilio_enabled', 'label' => 'Twilio SMS Gateway Enabled', 'value' => '1', 'group' => 'SMS Gateway', 'type' => 'text'],
            ['key' => 'sms.twilio_account_sid', 'label' => 'Twilio Account SID', 'value' => env('TWILIO_ACCOUNT_SID', ''), 'group' => 'SMS Gateway', 'type' => 'text'],
            ['key' => 'sms.twilio_auth_token', 'label' => 'Twilio Auth Token', 'value' => env('TWILIO_AUTH_TOKEN', ''), 'group' => 'SMS Gateway', 'type' => 'text'],
            ['key' => 'sms.twilio_phone_number', 'label' => 'Twilio Sender Phone Number', 'value' => env('TWILIO_PHONE_NUMBER', ''), 'group' => 'SMS Gateway', 'type' => 'text'],
            ['key' => 'sms.twilio_messaging_service_sid', 'label' => 'Twilio Messaging Service SID', 'value' => env('TWILIO_MESSAGING_SERVICE_SID', ''), 'group' => 'SMS Gateway', 'type' => 'text'],

            // Mail & SMTP Settings
            ['key' => 'mail.mailer', 'label' => 'Mail Transport Driver (smtp, log, sendmail)', 'value' => env('MAIL_MAILER', 'smtp'), 'group' => 'Mail & SMTP', 'type' => 'text'],
            ['key' => 'mail.host', 'label' => 'SMTP Host', 'value' => env('MAIL_HOST', 'mail.ridemycars.com'), 'group' => 'Mail & SMTP', 'type' => 'text'],
            ['key' => 'mail.port', 'label' => 'SMTP Port (465, 587, 25)', 'value' => env('MAIL_PORT', '465'), 'group' => 'Mail & SMTP', 'type' => 'text'],
            ['key' => 'mail.encryption', 'label' => 'SMTP Encryption (ssl, tls, none)', 'value' => env('MAIL_ENCRYPTION', 'ssl'), 'group' => 'Mail & SMTP', 'type' => 'text'],
            ['key' => 'mail.username', 'label' => 'SMTP Username', 'value' => env('MAIL_USERNAME', 'support@ridemycars.com'), 'group' => 'Mail & SMTP', 'type' => 'text'],
            ['key' => 'mail.password', 'label' => 'SMTP Password', 'value' => env('MAIL_PASSWORD', ''), 'group' => 'Mail & SMTP', 'type' => 'text'],
            ['key' => 'mail.from_address', 'label' => 'Sender From Email Address', 'value' => env('MAIL_FROM_ADDRESS', 'support@ridemycars.com'), 'group' => 'Mail & SMTP', 'type' => 'text'],
            ['key' => 'mail.from_name', 'label' => 'Sender From Name', 'value' => env('MAIL_FROM_NAME', 'RideMyCars'), 'group' => 'Mail & SMTP', 'type' => 'text'],

            // Social Logins (Google & Apple)
            ['key' => 'oauth.google_enabled', 'label' => 'Google Login Enabled', 'value' => '1', 'group' => 'Social Logins', 'type' => 'text'],
            ['key' => 'oauth.google_client_id', 'label' => 'Google OAuth Client ID', 'value' => env('GOOGLE_CLIENT_ID', ''), 'group' => 'Social Logins', 'type' => 'text'],
            ['key' => 'oauth.google_client_secret', 'label' => 'Google OAuth Client Secret', 'value' => env('GOOGLE_CLIENT_SECRET', ''), 'group' => 'Social Logins', 'type' => 'text'],
            ['key' => 'oauth.google_redirect_uri', 'label' => 'Google OAuth Redirect URI', 'value' => env('GOOGLE_REDIRECT_URI', 'https://ridemycars.com/auth/google/callback'), 'group' => 'Social Logins', 'type' => 'text'],
            ['key' => 'oauth.apple_enabled', 'label' => 'Apple Login Enabled', 'value' => '1', 'group' => 'Social Logins', 'type' => 'text'],
            ['key' => 'oauth.apple_client_id', 'label' => 'Apple Service ID / Client ID', 'value' => env('APPLE_CLIENT_ID', 'com.ridemycars.web.auth'), 'group' => 'Social Logins', 'type' => 'text'],
            ['key' => 'oauth.apple_team_id', 'label' => 'Apple Developer Team ID', 'value' => env('APPLE_TEAM_ID', ''), 'group' => 'Social Logins', 'type' => 'text'],
            ['key' => 'oauth.apple_key_id', 'label' => 'Apple Key ID', 'value' => env('APPLE_KEY_ID', ''), 'group' => 'Social Logins', 'type' => 'text'],
            ['key' => 'oauth.apple_client_secret', 'label' => 'Apple Generated Client Secret / Key', 'value' => env('APPLE_CLIENT_SECRET', ''), 'group' => 'Social Logins', 'type' => 'text'],
            ['key' => 'oauth.apple_redirect_uri', 'label' => 'Apple Redirect URI', 'value' => env('APPLE_REDIRECT_URI', 'https://ridemycars.com/auth/apple/callback'), 'group' => 'Social Logins', 'type' => 'text'],

            // Maps & Geolocation
            ['key' => 'geo.google_maps_api_key', 'label' => 'Google Maps JavaScript & Places API Key', 'value' => env('GOOGLE_MAPS_API_KEY', ''), 'group' => 'Maps & Geolocation', 'type' => 'text'],
            ['key' => 'geo.distance_unit', 'label' => 'Distance Measurement Unit (km / miles)', 'value' => 'km', 'group' => 'Maps & Geolocation', 'type' => 'text'],

            // Firebase & Push Notifications
            ['key' => 'firebase.api_key', 'label' => 'Firebase Web API Key', 'value' => env('FIREBASE_API_KEY', ''), 'group' => 'Firebase & Push', 'type' => 'text'],
            ['key' => 'firebase.auth_domain', 'label' => 'Firebase Auth Domain', 'value' => env('FIREBASE_AUTH_DOMAIN', 'ridemycars.firebaseapp.com'), 'group' => 'Firebase & Push', 'type' => 'text'],
            ['key' => 'firebase.project_id', 'label' => 'Firebase Project ID', 'value' => env('FIREBASE_PROJECT_ID', 'ridemycars'), 'group' => 'Firebase & Push', 'type' => 'text'],
            ['key' => 'firebase.storage_bucket', 'label' => 'Firebase Storage Bucket', 'value' => env('FIREBASE_STORAGE_BUCKET', 'ridemycars.firebasestorage.app'), 'group' => 'Firebase & Push', 'type' => 'text'],
            ['key' => 'firebase.messaging_sender_id', 'label' => 'Firebase Messaging Sender ID', 'value' => env('FIREBASE_MESSAGING_SENDER_ID', ''), 'group' => 'Firebase & Push', 'type' => 'text'],
            ['key' => 'firebase.app_id', 'label' => 'Firebase App ID', 'value' => env('FIREBASE_APP_ID', ''), 'group' => 'Firebase & Push', 'type' => 'text'],
            ['key' => 'firebase.measurement_id', 'label' => 'Firebase Analytics Measurement ID', 'value' => env('FIREBASE_MEASUREMENT_ID', ''), 'group' => 'Firebase & Push', 'type' => 'text'],
            ['key' => 'firebase.fcm_server_key', 'label' => 'Firebase Cloud Messaging (FCM) Server Key', 'value' => env('FIREBASE_FCM_SERVER_KEY', ''), 'group' => 'Firebase & Push', 'type' => 'text'],

            // Demo Accounts & Development
            ['key' => 'demo.enabled', 'label' => 'Show Demo Accounts on Login Page', 'value' => '1', 'group' => 'Demo Accounts', 'type' => 'text'],
            ['key' => 'demo.default_password', 'label' => 'Demo Accounts Default Password', 'value' => '123456', 'group' => 'Demo Accounts', 'type' => 'text'],
            ['key' => 'demo.rider_email', 'label' => 'Demo Rider Email', 'value' => 'customer@ridemycars.com', 'group' => 'Demo Accounts', 'type' => 'text'],
            ['key' => 'demo.rider_name', 'label' => 'Demo Rider Name', 'value' => 'Customer', 'group' => 'Demo Accounts', 'type' => 'text'],
            ['key' => 'demo.driver_email', 'label' => 'Demo Driver 1 Email', 'value' => 'sarah@example.com', 'group' => 'Demo Accounts', 'type' => 'text'],
            ['key' => 'demo.driver_name', 'label' => 'Demo Driver 1 Name', 'value' => 'Sarah (Driver)', 'group' => 'Demo Accounts', 'type' => 'text'],
            ['key' => 'demo.driver2_email', 'label' => 'Demo Driver 2 Email', 'value' => 'michael@example.com', 'group' => 'Demo Accounts', 'type' => 'text'],
            ['key' => 'demo.driver2_name', 'label' => 'Demo Driver 2 Name', 'value' => 'Michael (Driver)', 'group' => 'Demo Accounts', 'type' => 'text'],
            ['key' => 'demo.driver3_email', 'label' => 'Demo Driver 3 Email', 'value' => 'sipho.driver@ridemycars.com', 'group' => 'Demo Accounts', 'type' => 'text'],
            ['key' => 'demo.driver3_name', 'label' => 'Demo Driver 3 Name', 'value' => 'Sipho (Driver)', 'group' => 'Demo Accounts', 'type' => 'text'],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
