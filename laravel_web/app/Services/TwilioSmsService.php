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
    protected bool $enabled;
    protected int $timeout;

    public function __construct()
    {
        $this->accountSid = (string) config('twilio.account_sid', '');
        $this->authToken = (string) config('twilio.auth_token', '');
        $this->fromNumber = (string) config('twilio.phone_number', '');
        $this->messagingServiceSid = (string) config('twilio.messaging_service_sid', '');
        $this->enabled = (bool) config('twilio.enabled', true);
        $this->timeout = (int) config('twilio.timeout', 15);
    }

    /**
     * Send an SMS message worldwide.
     *
     * @param string $to Recipient phone number (E.164 format or standard local format)
     * @param string $message The text content of the SMS
     * @return array [success => bool, message_sid => string|null, error => string|null, code => int|null]
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
            $err = 'Twilio Account SID or Auth Token is not configured in .env';
            Log::warning("Twilio SMS: {$err}");
            return [
                'success' => false,
                'message_sid' => null,
                'error' => $err,
                'code' => 500,
            ];
        }

        $formattedTo = $this->formatE164($to);

        $payload = [
            'To' => $formattedTo,
            'Body' => $message,
        ];

        // Use Messaging Service SID if available, otherwise fallback to From Phone Number
        if (!empty($this->messagingServiceSid)) {
            $payload['MessagingServiceSid'] = $this->messagingServiceSid;
        } elseif (!empty($this->fromNumber)) {
            $payload['From'] = $this->fromNumber;
        } else {
            $err = 'Neither TWILIO_PHONE_NUMBER nor TWILIO_MESSAGING_SERVICE_SID is configured in .env';
            Log::warning("Twilio SMS: {$err}");
            return [
                'success' => false,
                'message_sid' => null,
                'error' => $err,
                'code' => 500,
            ];
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";

        try {
            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->timeout($this->timeout)
                ->asForm()
                ->post($url, $payload);

            $data = $response->json();

            if ($response->successful() && !empty($data['sid'])) {
                Log::info("Twilio SMS sent successfully to {$formattedTo}. SID: {$data['sid']}");
                return [
                    'success' => true,
                    'message_sid' => $data['sid'],
                    'status' => $data['status'] ?? 'queued',
                    'error' => null,
                    'code' => null,
                ];
            }

            $errorMessage = $data['message'] ?? 'Failed to send SMS via Twilio';
            $errorCode = $data['code'] ?? $response->status();

            // Handle common Twilio Worldwide Geo-Permission error specifically
            if ($errorCode == 21408) {
                $errorMessage .= ' (Worldwide Geo-Permissions error: Enable the recipient country in Twilio Console -> Messaging -> Settings -> Geo Permissions)';
            }

            Log::error("Twilio SMS Error to {$formattedTo}: [Code {$errorCode}] {$errorMessage}");

            return [
                'success' => false,
                'message_sid' => null,
                'error' => $errorMessage,
                'code' => $errorCode,
            ];
        } catch (\Throwable $e) {
            Log::error("Twilio SMS Exception to {$formattedTo}: " . $e->getMessage());
            return [
                'success' => false,
                'message_sid' => null,
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }
    }

    /**
     * Send an OTP verification code.
     */
    public function sendOtp(string $to, string $code): array
    {
        $message = "Your RideMyCars verification code is: {$code}. Valid for 10 minutes. Do not share this code with anyone.";
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
                'error' => 'Twilio Account SID or Auth Token missing in .env',
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
     */
    public function formatE164(string $phone): string
    {
        // Strip spaces, dashes, parentheses, dots
        $cleaned = preg_replace('/[^\d+]/', '', trim($phone));

        // If it does not start with +, check if it starts with 00
        if (str_starts_with($cleaned, '00')) {
            $cleaned = '+' . substr($cleaned, 2);
        } elseif (!str_starts_with($cleaned, '+')) {
            // Default to + if 10-15 digits
            $cleaned = '+' . $cleaned;
        }

        return $cleaned;
    }
}
