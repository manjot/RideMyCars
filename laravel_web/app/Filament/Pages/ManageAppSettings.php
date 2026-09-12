<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\EmailOtpService;
use App\Services\SettingService;
use App\Services\TwilioSmsService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Mail;

class ManageAppSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'Financials & Audit';
    protected static ?string $navigationLabel = 'App Settings Hub';
    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.manage-app-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $all = Setting::all()->pluck('value', 'key')->toArray();

        $this->form->fill([
            // Payment Gateways
            'payment_stripe_enabled' => (bool) ($all['payment.stripe_enabled'] ?? true),
            'payment_stripe_mode' => $all['payment.stripe_mode'] ?? 'test',
            'payment_stripe_test_publishable_key' => $all['payment.stripe_test_publishable_key'] ?? ($all['payment.stripe_publishable_key'] ?? ''),
            'payment_stripe_test_secret_key' => $all['payment.stripe_test_secret_key'] ?? ($all['payment.stripe_secret_key'] ?? ''),
            'payment_stripe_test_webhook_secret' => $all['payment.stripe_test_webhook_secret'] ?? ($all['payment.stripe_webhook_secret'] ?? ''),
            'payment_stripe_live_publishable_key' => $all['payment.stripe_live_publishable_key'] ?? '',
            'payment_stripe_live_secret_key' => $all['payment.stripe_live_secret_key'] ?? '',
            'payment_stripe_live_webhook_secret' => $all['payment.stripe_live_webhook_secret'] ?? '',
            'payment_stripe_publishable_key' => $all['payment.stripe_publishable_key'] ?? '',
            'payment_stripe_secret_key' => $all['payment.stripe_secret_key'] ?? '',
            'payment_stripe_webhook_secret' => $all['payment.stripe_webhook_secret'] ?? '',
            'payment_apple_pay_enabled' => (bool) ($all['payment.apple_pay_enabled'] ?? true),
            'payment_apple_pay_merchant_id' => $all['payment.apple_pay_merchant_id'] ?? 'merchant.com.ridemycars',
            'payment_apple_pay_domain' => $all['payment.apple_pay_domain'] ?? 'ridemycars.com',

            // ExpressPay Ghana Gateway
            'payment_expresspay_enabled' => (bool) ($all['payment.expresspay_enabled'] ?? true),
            'payment_expresspay_mode' => $all['payment.expresspay_mode'] ?? 'sandbox',
            'payment_expresspay_sandbox_merchant_id' => $all['payment.expresspay_sandbox_merchant_id'] ?? ($all['payment.expresspay_merchant_id'] ?? '562786243097'),
            'payment_expresspay_sandbox_api_key' => $all['payment.expresspay_sandbox_api_key'] ?? ($all['payment.expresspay_api_key'] ?? 'DInEOn1ayqtjC420gHLJ4-IiCSoZKPR13lxkLyzqiD-PcXhMFOBwKyoUw9hzAY1-hYnIGJov5Rbz8hme7Nm'),
            'payment_expresspay_live_merchant_id' => $all['payment.expresspay_live_merchant_id'] ?? '',
            'payment_expresspay_live_api_key' => $all['payment.expresspay_live_api_key'] ?? '',
            'payment_expresspay_merchant_id' => $all['payment.expresspay_merchant_id'] ?? '562786243097',
            'payment_expresspay_api_key' => $all['payment.expresspay_api_key'] ?? 'DInEOn1ayqtjC420gHLJ4-IiCSoZKPR13lxkLyzqiD-PcXhMFOBwKyoUw9hzAY1-hYnIGJov5Rbz8hme7Nm',
            'payment_expresspay_currency' => $all['payment.expresspay_currency'] ?? 'GHS',

            // SMS Gateway
            'sms_twilio_enabled' => (bool) ($all['sms.twilio_enabled'] ?? true),
            'sms_twilio_account_sid' => $all['sms.twilio_account_sid'] ?? '',
            'sms_twilio_auth_token' => $all['sms.twilio_auth_token'] ?? '',
            'sms_twilio_phone_number' => $all['sms.twilio_phone_number'] ?? '',
            'sms_twilio_messaging_service_sid' => $all['sms.twilio_messaging_service_sid'] ?? '',

            // Mail & SMTP
            'mail_mailer' => $all['mail.mailer'] ?? 'smtp',
            'mail_host' => $all['mail.host'] ?? 'mail.ridemycars.com',
            'mail_port' => $all['mail.port'] ?? '465',
            'mail_encryption' => $all['mail.encryption'] ?? 'ssl',
            'mail_username' => $all['mail.username'] ?? 'support@ridemycars.com',
            'mail_password' => $all['mail.password'] ?? '',
            'mail_from_address' => $all['mail.from_address'] ?? 'support@ridemycars.com',
            'mail_from_name' => $all['mail.from_name'] ?? 'RideMyCars',

            // Social Logins
            'oauth_google_enabled' => (bool) ($all['oauth.google_enabled'] ?? true),
            'oauth_google_client_id' => $all['oauth.google_client_id'] ?? '',
            'oauth_google_client_secret' => $all['oauth.google_client_secret'] ?? '',
            'oauth_google_redirect_uri' => $all['oauth.google_redirect_uri'] ?? 'https://ridemycars.com/auth/google/callback',
            'oauth_apple_enabled' => (bool) ($all['oauth.apple_enabled'] ?? true),
            'oauth_apple_client_id' => $all['oauth.apple_client_id'] ?? 'com.ridemycars.web.auth',
            'oauth_apple_team_id' => $all['oauth.apple_team_id'] ?? '',
            'oauth_apple_key_id' => $all['oauth.apple_key_id'] ?? '',
            'oauth_apple_client_secret' => $all['oauth.apple_client_secret'] ?? '',
            'oauth_apple_redirect_uri' => $all['oauth.apple_redirect_uri'] ?? 'https://ridemycars.com/auth/apple/callback',

            // Maps & Geolocation
            'geo_google_maps_api_key' => $all['geo.google_maps_api_key'] ?? '',
            'geo_distance_unit' => $all['geo.distance_unit'] ?? 'km',

            // Firebase & Push
            'firebase_api_key' => $all['firebase.api_key'] ?? '',
            'firebase_auth_domain' => $all['firebase.auth_domain'] ?? 'ridemycars.firebaseapp.com',
            'firebase_project_id' => $all['firebase.project_id'] ?? 'ridemycars',
            'firebase_storage_bucket' => $all['firebase.storage_bucket'] ?? 'ridemycars.firebasestorage.app',
            'firebase_messaging_sender_id' => $all['firebase.messaging_sender_id'] ?? '235015074737',
            'firebase_app_id' => $all['firebase.app_id'] ?? '1:235015074737:web:e23df51eb2a0b129f30390',
            'firebase_measurement_id' => $all['firebase.measurement_id'] ?? '',
            'firebase_fcm_server_key' => $all['firebase.fcm_server_key'] ?? '',

            // Demo Accounts
            'demo_enabled' => (bool) ($all['demo.enabled'] ?? true),
            'demo_default_password' => $all['demo.default_password'] ?? '123456',
            'demo_rider_name' => $all['demo.rider_name'] ?? 'Customer',
            'demo_rider_email' => $all['demo.rider_email'] ?? 'customer@ridemycars.com',
            'demo_driver_name' => $all['demo.driver_name'] ?? 'Sarah (Driver)',
            'demo_driver_email' => $all['demo.driver_email'] ?? 'sarah@example.com',
            'demo_driver2_name' => $all['demo.driver2_name'] ?? 'Michael (Driver)',
            'demo_driver2_email' => $all['demo.driver2_email'] ?? 'michael@example.com',
            'demo_driver3_name' => $all['demo.driver3_name'] ?? 'Sipho (Driver)',
            'demo_driver3_email' => $all['demo.driver3_email'] ?? 'sipho.driver@ridemycars.com',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings Tabs')
                    ->tabs([
                        // TAB 1: Payment Gateways
                        Forms\Components\Tabs\Tab::make('Payment Gateways')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Forms\Components\Section::make('Stripe Gateway')
                                    ->description('Manage Credit/Debit Card payments with dedicated Test & Live credentials.')
                                    ->schema([
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\Toggle::make('payment_stripe_enabled')
                                                ->label('Enable Stripe Payments')
                                                ->default(true),
                                            Forms\Components\Select::make('payment_stripe_mode')
                                                ->label('Active Environment Mode')
                                                ->options([
                                                    'test' => '🧪 Test / Sandbox Mode',
                                                    'live' => '🚀 Live / Production Mode',
                                                ])
                                                ->default('test')
                                                ->helperText('Select which key set to use dynamically for customer transactions.')
                                                ->required(),
                                        ]),

                                        Forms\Components\Fieldset::make('🧪 Sandbox / Test Mode Keys')
                                            ->schema([
                                                Forms\Components\TextInput::make('payment_stripe_test_publishable_key')
                                                    ->label('Test Publishable Key (pk_test_...)')
                                                    ->placeholder('pk_test_...')
                                                    ->columnSpanFull(),
                                                Forms\Components\TextInput::make('payment_stripe_test_secret_key')
                                                    ->label('Test Secret Key (sk_test_...)')
                                                    ->placeholder('sk_test_...')
                                                    ->password()
                                                    ->revealable()
                                                    ->columnSpanFull(),
                                                Forms\Components\TextInput::make('payment_stripe_test_webhook_secret')
                                                    ->label('Test Webhook Signing Secret (whsec_...)')
                                                    ->placeholder('whsec_...')
                                                    ->password()
                                                    ->revealable()
                                                    ->columnSpanFull(),
                                            ]),

                                        Forms\Components\Fieldset::make('🚀 Live / Production Mode Keys')
                                            ->schema([
                                                Forms\Components\TextInput::make('payment_stripe_live_publishable_key')
                                                    ->label('Live Publishable Key (pk_live_...)')
                                                    ->placeholder('pk_live_...')
                                                    ->columnSpanFull(),
                                                Forms\Components\TextInput::make('payment_stripe_live_secret_key')
                                                    ->label('Live Secret Key (sk_live_...)')
                                                    ->placeholder('sk_live_...')
                                                    ->password()
                                                    ->revealable()
                                                    ->columnSpanFull(),
                                                Forms\Components\TextInput::make('payment_stripe_live_webhook_secret')
                                                    ->label('Live Webhook Signing Secret (whsec_...)')
                                                    ->placeholder('whsec_...')
                                                    ->password()
                                                    ->revealable()
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),

                                Forms\Components\Section::make('Apple Pay')
                                    ->description('Apple Pay Merchant details for Safari & iOS.')
                                    ->schema([
                                        Forms\Components\Toggle::make('payment_apple_pay_enabled')
                                            ->label('Enable Apple Pay')
                                            ->default(true),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('payment_apple_pay_merchant_id')
                                                ->label('Merchant Identifier')
                                                ->placeholder('merchant.com.ridemycars'),
                                            Forms\Components\TextInput::make('payment_apple_pay_domain')
                                                ->label('Verified Domain Name')
                                                ->placeholder('ridemycars.com'),
                                        ]),
                                    ]),

                                Forms\Components\Section::make('ExpressPay Ghana Gateway (Mobile Money & Cards)')
                                    ->description('Manage ExpressPay Ghana merchant credentials for MTN MoMo, Telecel Cash, AirtelTigo Money, and GH Cards.')
                                    ->schema([
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\Toggle::make('payment_expresspay_enabled')
                                                ->label('Enable ExpressPay Ghana')
                                                ->default(true),
                                            Forms\Components\Select::make('payment_expresspay_mode')
                                                ->label('Active Environment Mode')
                                                ->options([
                                                    'sandbox' => '🧪 Sandbox / Test Mode',
                                                    'live' => '🚀 Live / Production Mode',
                                                ])
                                                ->default('sandbox')
                                                ->helperText('Select which key set to use dynamically for customer transactions.')
                                                ->required(),
                                        ]),

                                        Forms\Components\Fieldset::make('🧪 Sandbox / Test Mode Keys')
                                            ->schema([
                                                Forms\Components\TextInput::make('payment_expresspay_sandbox_merchant_id')
                                                    ->label('Sandbox Merchant ID')
                                                    ->placeholder('562786243097')
                                                    ->columnSpanFull(),
                                                Forms\Components\TextInput::make('payment_expresspay_sandbox_api_key')
                                                    ->label('Sandbox API Key')
                                                    ->placeholder('DInEOn...')
                                                    ->password()
                                                    ->revealable()
                                                    ->columnSpanFull(),
                                            ]),

                                        Forms\Components\Fieldset::make('🚀 Live / Production Mode Keys')
                                            ->schema([
                                                Forms\Components\TextInput::make('payment_expresspay_live_merchant_id')
                                                    ->label('Live Merchant ID')
                                                    ->placeholder('Your production merchant ID')
                                                    ->columnSpanFull(),
                                                Forms\Components\TextInput::make('payment_expresspay_live_api_key')
                                                    ->label('Live API Key')
                                                    ->placeholder('Your production API key')
                                                    ->password()
                                                    ->revealable()
                                                    ->columnSpanFull(),
                                            ]),

                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('payment_expresspay_currency')
                                                ->label('Settlement Currency')
                                                ->default('GHS')
                                                ->disabled(),
                                            Forms\Components\Placeholder::make('endpoints_info')
                                                ->label('Gateway Endpoints')
                                                ->content('Sandbox: sandbox.expresspaygh.com • Live: expresspaygh.com'),
                                        ]),
                                    ]),
                            ]),

                        // TAB 2: SMS Gateway (Twilio)
                        Forms\Components\Tabs\Tab::make('SMS Gateway')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Forms\Components\Section::make('Twilio Worldwide SMS')
                                    ->description('Configure Twilio credentials for phone OTP verification & live ride alerts.')
                                    ->schema([
                                        Forms\Components\Toggle::make('sms_twilio_enabled')
                                            ->label('Enable SMS Sending')
                                            ->helperText('If disabled, OTPs will be simulated in server logs for testing.')
                                            ->default(true),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('sms_twilio_account_sid')
                                                ->label('Twilio Account SID')
                                                ->placeholder('AC...'),
                                            Forms\Components\TextInput::make('sms_twilio_auth_token')
                                                ->label('Twilio Auth Token')
                                                ->password()
                                                ->revealable()
                                                ->placeholder('Auth Token'),
                                        ]),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('sms_twilio_phone_number')
                                                ->label('Sender Phone Number (E.164)')
                                                ->placeholder('+1234567890'),
                                            Forms\Components\TextInput::make('sms_twilio_messaging_service_sid')
                                                ->label('Messaging Service SID (Optional)')
                                                ->placeholder('MG...'),
                                        ]),
                                    ]),
                            ]),

                        // TAB 3: Mail & SMTP Settings
                        Forms\Components\Tabs\Tab::make('Email & SMTP')
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                Forms\Components\Section::make('SMTP Server Configuration')
                                    ->description('Configure transactional email delivery for booking receipts, OTPs, and notifications.')
                                    ->schema([
                                        Forms\Components\Grid::make(3)->schema([
                                            Forms\Components\Select::make('mail_mailer')
                                                ->label('Mail Driver')
                                                ->options([
                                                    'smtp' => 'SMTP (Standard)',
                                                    'sendmail' => 'Sendmail (Local)',
                                                    'log' => 'Log (Testing / Mock)',
                                                ])
                                                ->default('smtp'),
                                            Forms\Components\TextInput::make('mail_host')
                                                ->label('SMTP Host')
                                                ->placeholder('mail.ridemycars.com'),
                                            Forms\Components\TextInput::make('mail_port')
                                                ->label('SMTP Port')
                                                ->placeholder('465 or 587')
                                                ->numeric(),
                                        ]),
                                        Forms\Components\Grid::make(3)->schema([
                                            Forms\Components\Select::make('mail_encryption')
                                                ->label('Encryption')
                                                ->options([
                                                    'ssl' => 'SSL (Port 465)',
                                                    'tls' => 'TLS (Port 587)',
                                                    'none' => 'None',
                                                ])
                                                ->default('ssl'),
                                            Forms\Components\TextInput::make('mail_username')
                                                ->label('SMTP Username')
                                                ->placeholder('support@ridemycars.com'),
                                            Forms\Components\TextInput::make('mail_password')
                                                ->label('SMTP Password')
                                                ->password()
                                                ->revealable(),
                                        ]),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('mail_from_address')
                                                ->label('From Email Address')
                                                ->placeholder('support@ridemycars.com'),
                                            Forms\Components\TextInput::make('mail_from_name')
                                                ->label('From Name')
                                                ->placeholder('RideMyCars'),
                                        ]),
                                    ]),
                            ]),

                        // TAB 4: Social Logins (Google & Apple)
                        Forms\Components\Tabs\Tab::make('Social Logins')
                            ->icon('heroicon-o-user-group')
                            ->schema([
                                Forms\Components\Section::make('Google Sign-In (OAuth 2.0 & GIS)')
                                    ->description('Google OAuth credentials for 1-click customer & driver authentication.')
                                    ->schema([
                                        Forms\Components\Toggle::make('oauth_google_enabled')
                                            ->label('Enable Google Sign-In')
                                            ->default(true),
                                        Forms\Components\TextInput::make('oauth_google_client_id')
                                            ->label('Google OAuth Client ID')
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('oauth_google_client_secret')
                                            ->label('Google OAuth Client Secret')
                                            ->password()
                                            ->revealable()
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('oauth_google_redirect_uri')
                                            ->label('Authorized Redirect URI')
                                            ->default('https://ridemycars.com/auth/google/callback')
                                            ->columnSpanFull(),
                                    ]),

                                Forms\Components\Section::make('Sign in with Apple')
                                    ->description('Apple Developer Service ID, Team ID, Key ID, and credentials.')
                                    ->schema([
                                        Forms\Components\Toggle::make('oauth_apple_enabled')
                                            ->label('Enable Apple Sign-In')
                                            ->default(true),
                                        Forms\Components\Grid::make(3)->schema([
                                            Forms\Components\TextInput::make('oauth_apple_client_id')
                                                ->label('Apple Services ID')
                                                ->placeholder('com.ridemycars.web.auth'),
                                            Forms\Components\TextInput::make('oauth_apple_team_id')
                                                ->label('Apple Team ID')
                                                ->placeholder('e.g. 4XPL9Y766Z'),
                                            Forms\Components\TextInput::make('oauth_apple_key_id')
                                                ->label('Apple Key ID')
                                                ->placeholder('e.g. ABC123DEF4'),
                                        ]),
                                        Forms\Components\Textarea::make('oauth_apple_client_secret')
                                            ->label('Apple Private Key (.p8) / Client Secret')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('oauth_apple_redirect_uri')
                                            ->label('Apple Redirect URI')
                                            ->default('https://ridemycars.com/auth/apple/callback')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // TAB 5: Maps & Geolocation
                        Forms\Components\Tabs\Tab::make('Maps & Geo')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Forms\Components\Section::make('Google Maps & Places API')
                                    ->description('API Key used for Map rendering, Autocomplete, Directions, and Live Driver tracking.')
                                    ->schema([
                                        Forms\Components\TextInput::make('geo_google_maps_api_key')
                                            ->label('Google Maps API Key')
                                            ->placeholder('AIzaSy...')
                                            ->columnSpanFull(),
                                        Forms\Components\Select::make('geo_distance_unit')
                                            ->label('Distance Measurement Unit')
                                            ->options([
                                                'km' => 'Kilometers (Metric - km)',
                                                'miles' => 'Miles (Imperial - mi)',
                                            ])
                                            ->default('km'),
                                    ]),
                            ]),

                        // TAB 6: Firebase & Push Notifications
                        Forms\Components\Tabs\Tab::make('Firebase & Push')
                            ->icon('heroicon-o-bell-alert')
                            ->schema([
                                Forms\Components\Section::make('Firebase Web SDK & Cloud Messaging')
                                    ->description('Firebase configurations for Web push notifications and realtime events.')
                                    ->schema([
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('firebase_api_key')
                                                ->label('Firebase API Key')
                                                ->placeholder('AIzaSy...'),
                                            Forms\Components\TextInput::make('firebase_project_id')
                                                ->label('Project ID')
                                                ->placeholder('ridemycars'),
                                        ]),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('firebase_auth_domain')
                                                ->label('Auth Domain')
                                                ->placeholder('ridemycars.firebaseapp.com'),
                                            Forms\Components\TextInput::make('firebase_storage_bucket')
                                                ->label('Storage Bucket')
                                                ->placeholder('ridemycars.firebasestorage.app'),
                                        ]),
                                        Forms\Components\Grid::make(3)->schema([
                                            Forms\Components\TextInput::make('firebase_messaging_sender_id')
                                                ->label('Messaging Sender ID')
                                                ->placeholder('235015074737'),
                                            Forms\Components\TextInput::make('firebase_app_id')
                                                ->label('App ID')
                                                ->placeholder('1:235015074737:web:...'),
                                            Forms\Components\TextInput::make('firebase_measurement_id')
                                                ->label('Measurement ID (Optional)'),
                                        ]),
                                        Forms\Components\TextInput::make('firebase_fcm_server_key')
                                            ->label('FCM Legacy Server Key / Token')
                                            ->password()
                                            ->revealable()
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // TAB 7: Demo Accounts & Quick Logins
                        Forms\Components\Tabs\Tab::make('Demo Accounts')
                            ->icon('heroicon-o-user-plus')
                            ->schema([
                                Forms\Components\Section::make('Demo Quick Fill Box on Login Page')
                                    ->description('Controls the visibility and pre-configured credentials of the Quick Demo Logins box on the customer & driver login page.')
                                    ->schema([
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\Toggle::make('demo_enabled')
                                                ->label('Show Quick Demo Box on Login Page')
                                                ->helperText('Disable in live production if you do not want public visitors to see demo credentials.')
                                                ->default(true),
                                            Forms\Components\TextInput::make('demo_default_password')
                                                ->label('Default Demo Password')
                                                ->default('123456'),
                                        ]),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('demo_rider_name')
                                                ->label('Demo Rider Display Name')
                                                ->default('Customer'),
                                            Forms\Components\TextInput::make('demo_rider_email')
                                                ->label('Demo Rider Email')
                                                ->default('customer@ridemycars.com'),
                                        ]),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('demo_driver_name')
                                                ->label('Demo Driver 1 Name')
                                                ->default('Sarah (Driver)'),
                                            Forms\Components\TextInput::make('demo_driver_email')
                                                ->label('Demo Driver 1 Email')
                                                ->default('sarah@example.com'),
                                        ]),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('demo_driver2_name')
                                                ->label('Demo Driver 2 Name')
                                                ->default('Michael (Driver)'),
                                            Forms\Components\TextInput::make('demo_driver2_email')
                                                ->label('Demo Driver 2 Email')
                                                ->default('michael@example.com'),
                                        ]),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('demo_driver3_name')
                                                ->label('Demo Driver 3 Name')
                                                ->default('Sipho (Driver)'),
                                            Forms\Components\TextInput::make('demo_driver3_email')
                                                ->label('Demo Driver 3 Email')
                                                ->default('sipho.driver@ridemycars.com'),
                                        ]),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $mapping = [
            // Payment Gateways
            'payment_stripe_enabled' => ['key' => 'payment.stripe_enabled', 'group' => 'Payment Gateways'],
            'payment_stripe_mode' => ['key' => 'payment.stripe_mode', 'group' => 'Payment Gateways'],
            'payment_stripe_test_publishable_key' => ['key' => 'payment.stripe_test_publishable_key', 'group' => 'Payment Gateways'],
            'payment_stripe_test_secret_key' => ['key' => 'payment.stripe_test_secret_key', 'group' => 'Payment Gateways'],
            'payment_stripe_test_webhook_secret' => ['key' => 'payment.stripe_test_webhook_secret', 'group' => 'Payment Gateways'],
            'payment_stripe_live_publishable_key' => ['key' => 'payment.stripe_live_publishable_key', 'group' => 'Payment Gateways'],
            'payment_stripe_live_secret_key' => ['key' => 'payment.stripe_live_secret_key', 'group' => 'Payment Gateways'],
            'payment_stripe_live_webhook_secret' => ['key' => 'payment.stripe_live_webhook_secret', 'group' => 'Payment Gateways'],
            'payment_apple_pay_enabled' => ['key' => 'payment.apple_pay_enabled', 'group' => 'Payment Gateways'],
            'payment_apple_pay_merchant_id' => ['key' => 'payment.apple_pay_merchant_id', 'group' => 'Payment Gateways'],
            'payment_apple_pay_domain' => ['key' => 'payment.apple_pay_domain', 'group' => 'Payment Gateways'],

            // ExpressPay Gateway
            'payment_expresspay_enabled' => ['key' => 'payment.expresspay_enabled', 'group' => 'Payment Gateways'],
            'payment_expresspay_mode' => ['key' => 'payment.expresspay_mode', 'group' => 'Payment Gateways'],
            'payment_expresspay_sandbox_merchant_id' => ['key' => 'payment.expresspay_sandbox_merchant_id', 'group' => 'Payment Gateways'],
            'payment_expresspay_sandbox_api_key' => ['key' => 'payment.expresspay_sandbox_api_key', 'group' => 'Payment Gateways'],
            'payment_expresspay_live_merchant_id' => ['key' => 'payment.expresspay_live_merchant_id', 'group' => 'Payment Gateways'],
            'payment_expresspay_live_api_key' => ['key' => 'payment.expresspay_live_api_key', 'group' => 'Payment Gateways'],
            'payment_expresspay_currency' => ['key' => 'payment.expresspay_currency', 'group' => 'Payment Gateways'],

            // SMS
            'sms_twilio_enabled' => ['key' => 'sms.twilio_enabled', 'group' => 'SMS Gateway'],
            'sms_twilio_account_sid' => ['key' => 'sms.twilio_account_sid', 'group' => 'SMS Gateway'],
            'sms_twilio_auth_token' => ['key' => 'sms.twilio_auth_token', 'group' => 'SMS Gateway'],
            'sms_twilio_phone_number' => ['key' => 'sms.twilio_phone_number', 'group' => 'SMS Gateway'],
            'sms_twilio_messaging_service_sid' => ['key' => 'sms.twilio_messaging_service_sid', 'group' => 'SMS Gateway'],

            // Mail
            'mail_mailer' => ['key' => 'mail.mailer', 'group' => 'Mail & SMTP'],
            'mail_host' => ['key' => 'mail.host', 'group' => 'Mail & SMTP'],
            'mail_port' => ['key' => 'mail.port', 'group' => 'Mail & SMTP'],
            'mail_encryption' => ['key' => 'mail.encryption', 'group' => 'Mail & SMTP'],
            'mail_username' => ['key' => 'mail.username', 'group' => 'Mail & SMTP'],
            'mail_password' => ['key' => 'mail.password', 'group' => 'Mail & SMTP'],
            'mail_from_address' => ['key' => 'mail.from_address', 'group' => 'Mail & SMTP'],
            'mail_from_name' => ['key' => 'mail.from_name', 'group' => 'Mail & SMTP'],

            // Social
            'oauth_google_enabled' => ['key' => 'oauth.google_enabled', 'group' => 'Social Logins'],
            'oauth_google_client_id' => ['key' => 'oauth.google_client_id', 'group' => 'Social Logins'],
            'oauth_google_client_secret' => ['key' => 'oauth.google_client_secret', 'group' => 'Social Logins'],
            'oauth_google_redirect_uri' => ['key' => 'oauth.google_redirect_uri', 'group' => 'Social Logins'],
            'oauth_apple_enabled' => ['key' => 'oauth.apple_enabled', 'group' => 'Social Logins'],
            'oauth_apple_client_id' => ['key' => 'oauth.apple_client_id', 'group' => 'Social Logins'],
            'oauth_apple_team_id' => ['key' => 'oauth.apple_team_id', 'group' => 'Social Logins'],
            'oauth_apple_key_id' => ['key' => 'oauth.apple_key_id', 'group' => 'Social Logins'],
            'oauth_apple_client_secret' => ['key' => 'oauth.apple_client_secret', 'group' => 'Social Logins'],
            'oauth_apple_redirect_uri' => ['key' => 'oauth.apple_redirect_uri', 'group' => 'Social Logins'],

            // Maps
            'geo_google_maps_api_key' => ['key' => 'geo.google_maps_api_key', 'group' => 'Maps & Geolocation'],
            'geo_distance_unit' => ['key' => 'geo.distance_unit', 'group' => 'Maps & Geolocation'],

            // Firebase
            'firebase_api_key' => ['key' => 'firebase.api_key', 'group' => 'Firebase & Push'],
            'firebase_auth_domain' => ['key' => 'firebase.auth_domain', 'group' => 'Firebase & Push'],
            'firebase_project_id' => ['key' => 'firebase.project_id', 'group' => 'Firebase & Push'],
            'firebase_storage_bucket' => ['key' => 'firebase.storage_bucket', 'group' => 'Firebase & Push'],
            'firebase_messaging_sender_id' => ['key' => 'firebase.messaging_sender_id', 'group' => 'Firebase & Push'],
            'firebase_app_id' => ['key' => 'firebase.app_id', 'group' => 'Firebase & Push'],
            'firebase_measurement_id' => ['key' => 'firebase.measurement_id', 'group' => 'Firebase & Push'],
            'firebase_fcm_server_key' => ['key' => 'firebase.fcm_server_key', 'group' => 'Firebase & Push'],

            // Demo
            'demo_enabled' => ['key' => 'demo.enabled', 'group' => 'Demo Accounts'],
            'demo_default_password' => ['key' => 'demo.default_password', 'group' => 'Demo Accounts'],
            'demo_rider_name' => ['key' => 'demo.rider_name', 'group' => 'Demo Accounts'],
            'demo_rider_email' => ['key' => 'demo.rider_email', 'group' => 'Demo Accounts'],
            'demo_driver_name' => ['key' => 'demo.driver_name', 'group' => 'Demo Accounts'],
            'demo_driver_email' => ['key' => 'demo.driver_email', 'group' => 'Demo Accounts'],
            'demo_driver2_name' => ['key' => 'demo.driver2_name', 'group' => 'Demo Accounts'],
            'demo_driver2_email' => ['key' => 'demo.driver2_email', 'group' => 'Demo Accounts'],
            'demo_driver3_name' => ['key' => 'demo.driver3_name', 'group' => 'Demo Accounts'],
            'demo_driver3_email' => ['key' => 'demo.driver3_email', 'group' => 'Demo Accounts'],
        ];

        foreach ($mapping as $field => $info) {
            if (array_key_exists($field, $state)) {
                $val = $state[$field];
                SettingService::set($info['key'], $val, $info['group']);
            }
        }

        // Dynamically assign active Stripe keys based on stripe_mode
        $stripeMode = $state['payment_stripe_mode'] ?? 'test';
        if ($stripeMode === 'live') {
            SettingService::set('payment.stripe_publishable_key', $state['payment_stripe_live_publishable_key'] ?? '', 'Payment Gateways');
            SettingService::set('payment.stripe_secret_key', $state['payment_stripe_live_secret_key'] ?? '', 'Payment Gateways');
            SettingService::set('payment.stripe_webhook_secret', $state['payment_stripe_live_webhook_secret'] ?? '', 'Payment Gateways');
        } else {
            SettingService::set('payment.stripe_publishable_key', $state['payment_stripe_test_publishable_key'] ?? '', 'Payment Gateways');
            SettingService::set('payment.stripe_secret_key', $state['payment_stripe_test_secret_key'] ?? '', 'Payment Gateways');
            SettingService::set('payment.stripe_webhook_secret', $state['payment_stripe_test_webhook_secret'] ?? '', 'Payment Gateways');
        }

        // Dynamically assign active ExpressPay keys based on expresspay_mode
        $expresspayMode = $state['payment_expresspay_mode'] ?? 'sandbox';
        if ($expresspayMode === 'live') {
            SettingService::set('payment.expresspay_merchant_id', $state['payment_expresspay_live_merchant_id'] ?? '', 'Payment Gateways');
            SettingService::set('payment.expresspay_api_key', $state['payment_expresspay_live_api_key'] ?? '', 'Payment Gateways');
        } else {
            SettingService::set('payment.expresspay_merchant_id', $state['payment_expresspay_sandbox_merchant_id'] ?? '', 'Payment Gateways');
            SettingService::set('payment.expresspay_api_key', $state['payment_expresspay_sandbox_api_key'] ?? '', 'Payment Gateways');
        }

        // Re-sync to runtime config
        SettingService::syncToConfig();

        Notification::make()
            ->title('Settings Saved Successfully!')
            ->body('All configurations updated in database. Stripe mode: ' . strtoupper($stripeMode) . ' • ExpressPay mode: ' . strtoupper($expresspayMode))
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testStripe')
                ->label('Test Stripe')
                ->icon('heroicon-o-credit-card')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Test Live Stripe Connection')
                ->modalDescription('Verify that your Stripe credentials can authenticate and communicate with the Stripe API.')
                ->modalSubmitActionLabel('Run Stripe Test')
                ->action(function (): void {
                    try {
                        SettingService::syncToConfig();
                        $mode = SettingService::get('payment.stripe_mode', 'test');
                        $secretKey = SettingService::getActiveStripeSecretKey();
                        if (empty($secretKey)) {
                            throw new \Exception("No Stripe Secret Key configured for active " . strtoupper($mode) . " mode. Please enter and save the secret key first.");
                        }

                        if (!class_exists(\Stripe\Stripe::class)) {
                            $initFile = base_path('vendor/stripe/stripe-php/init.php');
                            if (file_exists($initFile)) {
                                require_once $initFile;
                            }
                        }

                        \Stripe\Stripe::setApiKey($secretKey);
                        $account = \Stripe\Account::retrieve();
                        $displayName = $account->settings->dashboard->display_name ?? $account->business_profile->name ?? $account->id;
                        $modeLabel = $mode === 'live' ? '🚀 LIVE MODE' : '🧪 TEST MODE';

                        Notification::make()
                            ->title("Stripe Connected ({$modeLabel})!")
                            ->body("Account: {$displayName} ({$account->id}) • Currency: " . strtoupper($account->default_currency) . " • Charges: " . ($account->charges_enabled ? 'Active' : 'Disabled'))
                            ->success()
                            ->duration(8000)
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Stripe Connection Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->duration(10000)
                            ->send();
                    }
                }),

            Action::make('testExpressPay')
                ->label('Test ExpressPay')
                ->icon('heroicon-o-check-badge')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Test ExpressPay Ghana Gateway')
                ->modalDescription('Verify that your ExpressPay Ghana credentials can authenticate and communicate with the ExpressPay API.')
                ->modalSubmitActionLabel('Run ExpressPay Test')
                ->action(function (): void {
                    try {
                        SettingService::syncToConfig();
                        $mode = SettingService::getActiveExpressPayMode();
                        $merchantId = SettingService::getActiveExpressPayMerchantId();
                        $apiKey = SettingService::getActiveExpressPayApiKey();

                        if (empty($merchantId) || empty($apiKey)) {
                            throw new \Exception("No ExpressPay Merchant ID or API Key configured for active " . strtoupper($mode) . " mode. Please enter and save credentials first.");
                        }

                        $url = SettingService::getExpressPaySubmitUrl();
                        $testOrderId = 'PING-' . time();

                        $response = \Illuminate\Support\Facades\Http::asForm()->timeout(15)->post($url, [
                            'merchant-id' => $merchantId,
                            'api-key' => $apiKey,
                            'firstname' => 'Test',
                            'lastname' => 'Admin',
                            'email' => 'admin-test@ridemycars.com',
                            'phonenumber' => '0244444444',
                            'currency' => 'GHS',
                            'amount' => '1.00',
                            'order-id' => $testOrderId,
                            'redirect-url' => url('/payment/expresspay/callback'),
                            'post-url' => url('/api/payment/expresspay/ipn'),
                        ]);

                        $data = $response->json();
                        $modeLabel = ($mode === 'live') ? '🚀 LIVE MODE' : '🧪 SANDBOX MODE';

                        if (($data['status'] ?? 0) === 1) {
                            $merchantName = $data['merchant-name'] ?? ($data['merchantservice-name'] ?? 'Ride my Cars');
                            Notification::make()
                                ->title("ExpressPay Ghana Connected ({$modeLabel})!")
                                ->body("Merchant: {$merchantName} ({$merchantId}) • Status: Operational • Settlement Currency: GHS")
                                ->success()
                                ->duration(8000)
                                ->send();
                        } else {
                            $msg = $data['message'] ?? 'Authentication failed with ExpressPay API.';
                            throw new \Exception("ExpressPay Error [Status: " . ($data['status'] ?? 'Unknown') . "]: {$msg}");
                        }
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('ExpressPay Connection Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->duration(10000)
                            ->send();
                    }
                }),

            Action::make('testEmail')
                ->label('Send Test Email')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->modalHeading('Send Test Email via SMTP')
                ->modalDescription('Verify your configured SMTP credentials by sending a live test message.')
                ->form([
                    Forms\Components\TextInput::make('test_email')
                        ->label('Recipient Email')
                        ->email()
                        ->required()
                        ->placeholder('admin@example.com'),
                ])
                ->action(function (array $data): void {
                    try {
                        SettingService::syncToConfig();
                        Mail::raw("This is a live test email sent from the RideMyCars App Settings Hub.\nYour SMTP settings are properly configured.\nTimestamp: " . now()->toDateTimeString(), function ($message) use ($data) {
                            $message->to($data['test_email'])
                                    ->subject('RideMyCars - SMTP Test Email');
                        });

                        Notification::make()
                            ->title('Test Email Sent Successfully!')
                            ->body("Dispatched to {$data['test_email']}.")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('SMTP Test Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('testSms')
                ->label('Send Test SMS')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('gray')
                ->modalHeading('Send Test SMS via Twilio')
                ->modalDescription('Verify your Twilio credentials by sending a live test SMS.')
                ->form([
                    Forms\Components\TextInput::make('test_phone')
                        ->label('Recipient Phone (E.164)')
                        ->required()
                        ->placeholder('+1234567890'),
                ])
                ->action(function (array $data): void {
                    try {
                        SettingService::syncToConfig();
                        $smsService = app(TwilioSmsService::class);
                        $result = $smsService->sendSms(
                            $data['test_phone'],
                            "RideMyCars SMS Gateway Test: Twilio credentials are functioning! (Sent: " . now()->format('H:i:s') . ")"
                        );

                        if ($result['success']) {
                            Notification::make()
                                ->title('Test SMS Sent Successfully!')
                                ->body("Dispatched to {$data['test_phone']}. SID: " . ($result['message_sid'] ?? 'simulated'))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('SMS Sending Failed')
                                ->body($result['error'] ?? 'Unknown error from Twilio gateway.')
                                ->danger()
                                ->send();
                        }
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Twilio Test Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
