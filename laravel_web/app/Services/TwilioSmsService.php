<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioSmsService
{
    protected string $accountSid;
    protected string $authToken;
    protected string $fromNumber;
    protected string $messagingServiceSid;
    protected string $alphanumericSender;
    protected bool $enabled;
    protected int $timeout;

    public function __construct()
    {
        $this->accountSid = (string) (\App\Services\SettingService::get('sms.twilio_account_sid') ?: config('twilio.account_sid', ''));
        $this->authToken = (string) (\App\Services\SettingService::get('sms.twilio_auth_token') ?: config('twilio.auth_token', ''));
        $this->fromNumber = (string) (\App\Services\SettingService::get('sms.twilio_phone_number') ?: config('twilio.phone_number', ''));
        $this->messagingServiceSid = (string) (\App\Services\SettingService::get('sms.twilio_messaging_service_sid') ?: config('twilio.messaging_service_sid', ''));
        $this->alphanumericSender = (string) (\App\Services\SettingService::get('sms.twilio_alphanumeric_sender') ?: config('twilio.alphanumeric_sender', 'RideMyCars'));
        $this->enabled = filter_var(\App\Services\SettingService::get('sms.twilio_enabled', config('twilio.enabled', true)), FILTER_VALIDATE_BOOLEAN);
        $this->timeout = (int) config('twilio.timeout', 15);
    }

    /**
     * Send an SMS message worldwide with smart sender selection and pre-validation.
     *
     * @param string $to Recipient phone number (E.164 format or standard local format)
     * @param string $message The text content of the SMS
     * @return array [success => bool, message_sid => string|null, error => string|null, code => int|null, friendly_error => string|null]
     */
    public function sendSms(string $to, string $message): array
    {
        if (!$this->enabled) {
            Log::info("Twilio SMS disabled by config. Message to {$to}: {$message}");
            return [
                'success' => true,
                'message_sid' => 'SIMULATED_SMS_DISABLED',
                'error' => null,
                'code' => null,
            ];
        }

        if (empty($this->accountSid) || empty($this->authToken)) {
            $err = 'Twilio Account SID or Auth Token is not configured. Please check Admin Settings > SMS Gateway.';
            Log::warning("Twilio SMS: {$err}");
            return [
                'success' => false,
                'message_sid' => null,
                'error' => $err,
                'code' => 500,
            ];
        }

        // 1. Format and sanitize phone number to valid E.164
        $formattedTo = $this->formatE164($to);

        // 2. Pre-validate phone number locally before calling Twilio (prevents Twilio 21211 fee & provides instant feedback)
        $validation = $this->validatePhoneNumber($formattedTo);
        if (!$validation['valid']) {
            Log::warning("Twilio SMS Pre-validation Failed for [{$to}] -> [{$formattedTo}]: {$validation['error']}");
            return [
                'success' => false,
                'message_sid' => null,
                'error' => $validation['error'],
                'code' => 21211,
            ];
        }

        $payload = [
            'To' => $formattedTo,
            'Body' => $message,
        ];

        // 3. Smart Sender Selection:
        // Identify whether destination is US/Canada (+1) or International
        $isNorthAmerica = str_starts_with($formattedTo, '+1');
        $isFromTollFree = $this->isTollFreeNumber($this->fromNumber);

        if (!$isNorthAmerica) {
            // Destination is outside North America (e.g. Ghana +233, UK +44, Nigeria +234)
            // US Toll-Free numbers (+1855...) CANNOT route to Ghana/international (Error 21612).
            // Explicitly force Alphanumeric Sender ID 'RideMyCars' so Twilio never selects the toll-free number.
            if (!empty($this->alphanumericSender)) {
                $payload['From'] = substr($this->alphanumericSender, 0, 11);
                Log::info("Twilio SMS: Routing international message to {$formattedTo} explicitly via Alphanumeric Sender ID '{$payload['From']}'");
            } elseif (!empty($this->messagingServiceSid)) {
                $payload['MessagingServiceSid'] = $this->messagingServiceSid;
            } elseif (!empty($this->fromNumber)) {
                $payload['From'] = $this->fromNumber;
            } else {
                return [
                    'success' => false,
                    'message_sid' => null,
                    'error' => 'No valid sender (Phone Number, Messaging Service SID, or Alphanumeric Sender ID) is configured.',
                    'code' => 500,
                ];
            }
        } else {
            // Destination is North America (+1): Alpha Sender ID is NOT supported by US/CA carriers.
            if (!empty($this->messagingServiceSid)) {
                $payload['MessagingServiceSid'] = $this->messagingServiceSid;
            } elseif (!empty($this->fromNumber)) {
                $payload['From'] = $this->fromNumber;
            } else {
                return [
                    'success' => false,
                    'message_sid' => null,
                    'error' => 'A valid Twilio phone number or Messaging Service SID is required for US/Canada destinations.',
                    'code' => 500,
                ];
            }
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";

        try {
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->timeout($this->timeout)
                ->asForm()
                ->post($url, $payload);

            $data = $response->json();

            if ($response->successful() && !empty($data['sid'])) {
                Log::info("Twilio SMS sent successfully to {$formattedTo}. SID: {$data['sid']}. Status: " . ($data['status'] ?? 'unknown'));
                return [
                    'success' => true,
                    'message_sid' => $data['sid'],
                    'status' => $data['status'] ?? 'queued',
                    'error' => null,
                    'code' => null,
                ];
            }

            $rawMessage = $data['message'] ?? 'Failed to send SMS via Twilio';
            $errorCode = (int) ($data['code'] ?? $response->status());

            // If primary sender failed with routing error 21612 or 21614, attempt fallback sender if configured
            if (!$response->successful() && in_array($errorCode, [21612, 21614])) {
                if (isset($payload['From']) && !empty($this->messagingServiceSid)) {
                    Log::info("Twilio SMS: Sender '{$payload['From']}' returned {$errorCode}. Retrying {$formattedTo} via MessagingServiceSid {$this->messagingServiceSid}...");
                    $retryPayload = [
                        'To' => $formattedTo,
                        'Body' => $message,
                        'MessagingServiceSid' => $this->messagingServiceSid,
                    ];
                    $retryResponse = Http::withBasicAuth($this->accountSid, $this->authToken)
                        ->timeout($this->timeout)
                        ->asForm()
                        ->post($url, $retryPayload);
                    $retryData = $retryResponse->json();
                    if ($retryResponse->successful() && !empty($retryData['sid'])) {
                        Log::info("Twilio SMS sent successfully on retry to {$formattedTo}. SID: {$retryData['sid']}");
                        return [
                            'success' => true,
                            'message_sid' => $retryData['sid'],
                            'status' => $retryData['status'] ?? 'queued',
                            'error' => null,
                            'code' => null,
                        ];
                    }
                }
            }

            // 4. Translate Twilio errors into clear, actionable messages
            $friendlyError = $this->translateTwilioError($errorCode, $rawMessage, $formattedTo);

            Log::error("Twilio SMS Error to {$formattedTo}: [Code {$errorCode}] {$rawMessage} -> Friendly: {$friendlyError}");

            return [
                'success' => false,
                'message_sid' => null,
                'error' => $friendlyError,
                'code' => $errorCode,
            ];
        } catch (\Throwable $e) {
            Log::error("Twilio SMS Exception to {$formattedTo}: " . $e->getMessage());
            return [
                'success' => false,
                'message_sid' => null,
                'error' => 'Network error connecting to Twilio SMS gateway: ' . $e->getMessage(),
                'code' => 500,
            ];
        }
    }

    /**
     * Send an OTP verification code for login or registration.
     */
    public function sendOtp(string $to, string $code): array
    {
        $message = "{$code} is OTP for your RideMyCars account. OTP is valid for 5 minutes. Do not share this OTP with anyone. For any help please visit https://ridemycars.com";
        return $this->sendSms($to, $message);
    }

    /**
     * Test Twilio Account credentials and connectivity.
     */
    public function testConnection(): array
    {
        if (empty($this->accountSid) || empty($this->authToken)) {
            return [
                'success' => false,
                'error' => 'Twilio Account SID or Auth Token missing in settings',
            ];
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}.json";

        try {
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->timeout(10)
                ->get($url);

            $data = $response->json();

            if ($response->successful() && !empty($data['sid'])) {
                return [
                    'success' => true,
                    'friendly_name' => $data['friendly_name'] ?? 'Twilio Account',
                    'status' => $data['status'] ?? 'active',
                    'type' => $data['type'] ?? 'Full',
                ];
            }

            return [
                'success' => false,
                'error' => $data['message'] ?? 'Authentication failed. Please check Account SID and Auth Token.',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Standardize international phone number into E.164 format (+[country][number]).
     * Strips invalid trunk prefixes (e.g. +233055... -> +23355...) and handles local inputs.
     */
    public function formatE164(string $phone, string $defaultDialCode = '+233'): string
    {
        // Strip spaces, dashes, parentheses, dots, all non-digit and non-plus
        $cleaned = preg_replace('/[^\d+]/', '', trim($phone));

        // Convert 00 prefix to +
        if (str_starts_with($cleaned, '00')) {
            $cleaned = '+' . substr($cleaned, 2);
        }

        // If it starts with +0, remove the invalid 0 immediately
        if (str_starts_with($cleaned, '+0')) {
            $cleaned = '+' . ltrim(substr($cleaned, 2), '0');
        }

        // If no leading +, handle local formatting
        if (!str_starts_with($cleaned, '+')) {
            if (str_starts_with($cleaned, '0')) {
                // Local number with trunk prefix (e.g. 0559776761 or 0202724315 in Ghana)
                $cleaned = '+' . ltrim($defaultDialCode, '+') . substr($cleaned, 1);
            } else {
                $cleaned = '+' . $cleaned;
            }
        }

        // Strip national trunk zero when country code is explicitly present:
        // e.g. Ghana (+233 0XX -> +233 XX), UK (+44 07X -> +44 7X), Nigeria (+234 0XX -> +234 XX)
        $trunkPatterns = [
            '/^\+2330(\d{9})$/' => '+233$1',  // Ghana
            '/^\+2340(\d{10})$/' => '+234$1', // Nigeria
            '/^\+2540(\d{9})$/' => '+254$1',  // Kenya
            '/^\+270(\d{9})$/' => '+27$1',    // South Africa
            '/^\+440(\d{10})$/' => '+44$1',   // United Kingdom
            '/^\+910(\d{10})$/' => '+91$1',   // India
            '/^\+330(\d{9})$/' => '+33$1',    // France
            '/^\+490(\d{10,11})$/' => '+49$1',// Germany
            '/^\+610(\d{9})$/' => '+61$1',    // Australia
        ];

        foreach ($trunkPatterns as $pattern => $replacement) {
            if (preg_match($pattern, $cleaned)) {
                $cleaned = preg_replace($pattern, $replacement, $cleaned);
                break;
            }
        }

        return $cleaned;
    }

    /**
     * Validate an E.164 phone number before transmitting to Twilio.
     * Prevents Twilio Error 21211 (Invalid 'To' number) and billing charges.
     */
    public function validatePhoneNumber(string $phone): array
    {
        // Must start with + followed by 7 to 15 digits (ITU-T E.164 specification)
        if (!preg_match('/^\+[1-9]\d{6,14}$/', $phone)) {
            return [
                'valid' => false,
                'error' => "Invalid phone number format ({$phone}). Please include your country code (e.g. +233 55 977 6761 or +1 305 368 8734).",
            ];
        }

        // North American Numbering Plan (+1) specific verification:
        // Valid format: +1 [2-9]XX [2-9]XX XXXX (11 digits total)
        // Area code and exchange code CANNOT start with 0 or 1.
        if (str_starts_with($phone, '+1')) {
            $digits = substr($phone, 2); // 10 digits after +1
            if (strlen($digits) !== 10) {
                return [
                    'valid' => false,
                    'error' => "US/Canada phone numbers must contain exactly 10 digits after +1.",
                ];
            }

            $areaFirstDigit = $digits[0];
            $exchangeFirstDigit = $digits[3];

            if ($areaFirstDigit === '0' || $areaFirstDigit === '1') {
                return [
                    'valid' => false,
                    'error' => "The area code in {$phone} is invalid. US/Canada area codes cannot start with 0 or 1.",
                ];
            }

            if ($exchangeFirstDigit === '0' || $exchangeFirstDigit === '1') {
                return [
                    'valid' => false,
                    'error' => "The phone number {$phone} has an invalid exchange code. In North America, the middle 3 digits cannot start with 0 or 1.",
                ];
            }
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Check whether a phone number is a US/Canada Toll-Free number (800, 888, 877, 866, 855, 844, 833).
     */
    public function isTollFreeNumber(string $phone): bool
    {
        $cleaned = preg_replace('/[^\d]/', '', $phone);
        if (str_starts_with($cleaned, '1') && strlen($cleaned) === 11) {
            $cleaned = substr($cleaned, 1);
        }
        if (strlen($cleaned) === 10) {
            $npa = substr($cleaned, 0, 3);
            return in_array($npa, ['800', '888', '877', '866', '855', '844', '833'], true);
        }
        return false;
    }

    /**
     * Translate common Twilio error codes into clear, actionable user guidance.
     */
    protected function translateTwilioError(int $code, string $rawMessage, string $phone): string
    {
        return match ($code) {
            21211 => "The phone number {$phone} is invalid according to telecom carrier standards. Please check the country code and mobile number.",
            21612 => "SMS carrier routing to {$phone} is restricted by international telecom regulations. Please verify via email or contact support.",
            30032 => "Toll-Free Verification Required: The Twilio toll-free number has not completed carrier verification. US carriers block unverified toll-free SMS. Submit Toll-Free Verification in Twilio Console.",
            30006 => "Undelivered: The destination number is either a landline, unreachable carrier, or being filtered by carriers. SMS can only be sent to mobile handsets.",
            30005 => "Handset unreachable: The recipient mobile phone is turned off, out of cell coverage, or unreachable.",
            21408 => "Twilio Geo-Permissions error: Outbound SMS to this country is disabled. Please enable this country in Twilio Console -> Messaging -> Settings -> Geo Permissions.",
            21614 => "SMS capability is not enabled on this Twilio phone number.",
            default => $rawMessage,
        };
    }
}

