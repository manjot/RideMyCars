<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NaloSmsService
{
    protected string $username;
    protected string $password;
    protected string $authKey;
    protected string $senderId;
    protected string $prefix;
    protected string $baseUrl;
    protected bool $enabled;
    protected int $timeout;
    protected bool $fallbackToTwilio;

    public function __construct()
    {
        $dbUsername = trim((string) \App\Services\SettingService::get('sms.nalo_username'));
        $dbPassword = trim((string) \App\Services\SettingService::get('sms.nalo_password'));
        $dbAuthKey = trim((string) \App\Services\SettingService::get('sms.nalo_auth_key'));
        $dbSenderId = trim((string) \App\Services\SettingService::get('sms.nalo_sender_id'));
        $dbPrefix = trim((string) \App\Services\SettingService::get('sms.nalo_prefix'));
        $dbBaseUrl = trim((string) \App\Services\SettingService::get('sms.nalo_base_url'));
        $dbEnabled = \App\Services\SettingService::get('sms.nalo_enabled');

        $this->username = $dbUsername !== '' ? $dbUsername : (string) config('nalo.username', 'Ridemycars');
        $this->password = $dbPassword !== '' ? $dbPassword : (string) config('nalo.password', 'wEST123456#');
        $this->authKey = $dbAuthKey !== '' ? $dbAuthKey : (string) config('nalo.auth_key', '');
        // The approved Nalo sender ID is uppercase RIDEMYCARS
        $this->senderId = $dbSenderId !== '' ? $dbSenderId : (string) config('nalo.sender_id', 'RIDEMYCARS');
        $this->prefix = $dbPrefix !== '' ? $dbPrefix : (string) config('nalo.prefix', 'Resl_Nalo');
        $this->baseUrl = rtrim($dbBaseUrl !== '' ? $dbBaseUrl : (string) config('nalo.base_url', 'https://sms.nalosolutions.com/smsbackend'), '/');
        $this->enabled = $dbEnabled !== null
            ? filter_var($dbEnabled, FILTER_VALIDATE_BOOLEAN)
            : (bool) config('nalo.enabled', true);
        $this->timeout = (int) config('nalo.timeout', 15);
        $this->fallbackToTwilio = (bool) config('nalo.fallback_to_twilio', true);
    }

    /**
     * Send SMS via Nalo Solutions Gateway to a Ghana recipient.
     *
     * @param string $to Recipient phone number (e.g. +233559776761 or 0559776761)
     * @param string $message The text message to send
     * @return array [success => bool, provider => 'nalo', message_id => string|null, error => string|null, code => int|string|null]
     */
    public function sendSms(string $to, string $message): array
    {
        if (!$this->enabled) {
            Log::info("Nalo SMS Gateway disabled by config. Simulated message to {$to}: {$message}");
            return [
                'success' => true,
                'provider' => 'nalo',
                'message_id' => 'SIMULATED_NALO_DISABLED',
                'error' => null,
                'code' => null,
            ];
        }

        if (empty($this->authKey) && (empty($this->username) || empty($this->password))) {
            $err = 'Nalo Solutions credentials (Username/Password or Auth Key) are not configured.';
            Log::warning("Nalo SMS: {$err}");
            return [
                'success' => false,
                'provider' => 'nalo',
                'message_id' => null,
                'error' => $err,
                'code' => 500,
            ];
        }

        // Format to Ghana MSISDN: 233XXXXXXXXX
        $msisdn = $this->formatGhanaMsisdn($to);
        if (!$this->isValidGhanaNumber($msisdn)) {
            $err = "Invalid Ghana phone number format ({$to}). Mobile numbers must contain 9 digits following +233.";
            Log::warning("Nalo SMS: {$err}");
            return [
                'success' => false,
                'provider' => 'nalo',
                'message_id' => null,
                'error' => $err,
                'code' => 1706,
            ];
        }

        // Method 1: Send via Primary JSON POST API
        $postResult = $this->sendViaJsonPost($msisdn, $message);
        if ($postResult['success']) {
            return $postResult;
        }

        // If POST failed due to network / gateway error (and not a validation failure like invalid credentials or credits),
        // attempt secondary GET API endpoint for resilience
        if (in_array($postResult['code'], [500, 502, 503, 504, 'CONNECTION_ERROR'])) {
            Log::info("Nalo SMS POST API failed with {$postResult['code']}. Retrying via secondary GET API for {$msisdn}...");
            $getResult = $this->sendViaGetApi($msisdn, $message);
            if ($getResult['success']) {
                return $getResult;
            }
        }

        return $postResult;
    }

    /**
     * Send an OTP verification code with the RideMyCars standard template.
     *
     * @param string $to Recipient phone number
     * @param string $code Verification code (e.g. '8273')
     * @return array
     */
    public function sendOtp(string $to, string $code): array
    {
        $message = "{$code} is OTP for your RideMyCars account. OTP is valid for 5 minutes. Do not share this OTP with anyone. For any help please visit https://ridemycars.com";
        return $this->sendSms($to, $message);
    }

    /**
     * Send message via JSON POST endpoint.
     */
    protected function sendViaJsonPost(string $msisdn, string $message): array
    {
        $url = "{$this->baseUrl}/{$this->prefix}/send-message/";

        $payload = [
            'msisdn' => $msisdn,
            'message' => $message,
            'sender_id' => $this->senderId,
        ];

        if (!empty($this->authKey)) {
            $payload['key'] = $this->authKey;
        } else {
            $payload['username'] = $this->username;
            $payload['password'] = $this->password;
        }

        try {
            // Note: withoutVerifying() prevents cURL SSL error 60 with custom CA cert chains on Nalo's servers
            $response = Http::withoutVerifying()
                ->timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($url, $payload);

            $data = $response->json();
            $status = (string) ($data['status'] ?? $data['code'] ?? $response->status());

            if ($status === '1701' || ($response->successful() && isset($data['job_id']))) {
                $jobId = $data['job_id'] ?? ('nalo_' . uniqid());
                Log::info("Nalo SMS sent successfully to {$msisdn}. Job ID: {$jobId}");
                return [
                    'success' => true,
                    'provider' => 'nalo',
                    'message_id' => $jobId,
                    'status' => 'delivered',
                    'error' => null,
                    'code' => 1701,
                ];
            }

            $rawCode = $data['code'] ?? $status;
            $rawMsg = $data['message'] ?? $response->body();
            $friendlyError = $this->translateNaloError($rawCode, $rawMsg);

            Log::error("Nalo SMS POST Error to {$msisdn}: [Code {$rawCode}] {$rawMsg} -> Friendly: {$friendlyError}");

            return [
                'success' => false,
                'provider' => 'nalo',
                'message_id' => null,
                'error' => $friendlyError,
                'code' => $rawCode,
            ];
        } catch (\Throwable $e) {
            Log::error("Nalo SMS POST Exception to {$msisdn}: " . $e->getMessage());
            return [
                'success' => false,
                'provider' => 'nalo',
                'message_id' => null,
                'error' => 'Connection error to Nalo Solutions SMS gateway: ' . $e->getMessage(),
                'code' => 'CONNECTION_ERROR',
            ];
        }
    }

    /**
     * Send message via GET endpoint (fallback method).
     */
    protected function sendViaGetApi(string $msisdn, string $message): array
    {
        $queryParams = [
            'type' => '0',
            'destination' => $msisdn,
            'dlr' => '1',
            'source' => $this->senderId,
            'message' => $message,
        ];

        if (!empty($this->authKey)) {
            $queryParams['key'] = $this->authKey;
        } else {
            $queryParams['username'] = $this->username;
            $queryParams['password'] = $this->password;
        }

        $url = "{$this->baseUrl}/clientapi/{$this->prefix}/send-message/?" . http_build_query($queryParams);

        try {
            $response = Http::withoutVerifying()
                ->timeout($this->timeout)
                ->get($url);

            $body = trim($response->body());

            // Check if starts with success code 1701
            if (str_starts_with($body, '1701|') || $body === '1701') {
                $parts = explode('|', $body);
                $jobId = $parts[2] ?? ('nalo_' . uniqid());
                Log::info("Nalo SMS (GET) sent successfully to {$msisdn}. Job ID: {$jobId}");
                return [
                    'success' => true,
                    'provider' => 'nalo',
                    'message_id' => $jobId,
                    'status' => 'delivered',
                    'error' => null,
                    'code' => 1701,
                ];
            }

            // Error format: "1707:Invalid Source(Sender)" or similar
            $errParts = explode(':', $body, 2);
            $errCode = $errParts[0] ?? (string) $response->status();
            $errMsg = $errParts[1] ?? $body;
            $friendly = $this->translateNaloError($errCode, $errMsg);

            Log::error("Nalo SMS GET Error to {$msisdn}: [Code {$errCode}] {$body} -> Friendly: {$friendly}");

            return [
                'success' => false,
                'provider' => 'nalo',
                'message_id' => null,
                'error' => $friendly,
                'code' => $errCode,
            ];
        } catch (\Throwable $e) {
            Log::error("Nalo SMS GET Exception to {$msisdn}: " . $e->getMessage());
            return [
                'success' => false,
                'provider' => 'nalo',
                'message_id' => null,
                'error' => 'Connection error to Nalo Solutions SMS gateway: ' . $e->getMessage(),
                'code' => 'CONNECTION_ERROR',
            ];
        }
    }

    /**
     * Test Nalo Solutions credentials and API connectivity.
     */
    public function testConnection(): array
    {
        if (empty($this->authKey) && (empty($this->username) || empty($this->password))) {
            return [
                'success' => false,
                'error' => 'Nalo username or password is missing in configuration.',
            ];
        }

        // Test with a harmless query to check credentials
        try {
            $payload = [
                'username' => $this->username,
                'password' => $this->password,
                'msisdn' => '233559776761',
                'message' => 'RideMyCars Gateway Diagnostic Ping',
                'sender_id' => $this->senderId,
            ];

            if (!empty($this->authKey)) {
                unset($payload['username'], $payload['password']);
                $payload['key'] = $this->authKey;
            }

            $response = Http::withoutVerifying()
                ->timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post("{$this->baseUrl}/{$this->prefix}/send-message/", $payload);

            $data = $response->json();
            $code = (int) ($data['code'] ?? $data['status'] ?? $response->status());

            // Status 1701 (success) or valid submission
            if ($code === 1701 || ($response->successful() && isset($data['job_id']))) {
                return [
                    'success' => true,
                    'status' => 'active',
                    'message' => 'Nalo Solutions SMS Gateway connected successfully! Sender ID approved.',
                    'job_id' => $data['job_id'] ?? null,
                ];
            }

            // If credentials are invalid (1703 or 1709)
            if ($code === 1703 || $code === 1709) {
                return [
                    'success' => false,
                    'error' => 'Authentication failed. Please verify Nalo username and password.',
                    'code' => $code,
                ];
            }

            // If sender ID is invalid
            if ($code === 1707) {
                return [
                    'success' => false,
                    'error' => "Sender ID '{$this->senderId}' is not authorized. The sender ID must be approved in Nalo portal.",
                    'code' => $code,
                ];
            }

            // Insufficient credit
            if ($code === 1025 || $code === 1026) {
                return [
                    'success' => false,
                    'error' => 'Insufficient SMS credit on Nalo account. Please recharge units.',
                    'code' => $code,
                ];
            }

            return [
                'success' => false,
                'error' => $this->translateNaloError($code, $data['message'] ?? $response->body()),
                'code' => $code,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => 'Failed to reach Nalo Solutions gateway: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format any valid Ghana phone number into Nalo's expected MSISDN format (233XXXXXXXXX).
     */
    public function formatGhanaMsisdn(string $phone): string
    {
        $cleaned = preg_replace('/[^\d]/', '', trim($phone));

        // If starts with 00233, strip 00
        if (str_starts_with($cleaned, '00233')) {
            $cleaned = substr($cleaned, 2);
        }

        // If starts with 2330, remove the national trunk zero: 233055... -> 23355...
        if (str_starts_with($cleaned, '2330')) {
            $cleaned = '233' . substr($cleaned, 4);
        }

        // If starts with local 0 (e.g. 0559776761)
        if (str_starts_with($cleaned, '0') && strlen($cleaned) === 10) {
            $cleaned = '233' . substr($cleaned, 1);
        }

        // If 9 digits without country code or 0 (e.g. 559776761)
        if (strlen($cleaned) === 9) {
            $cleaned = '233' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Verify whether an MSISDN is a valid Ghana mobile number (233 followed by 9 digits).
     */
    public function isValidGhanaNumber(string $msisdn): bool
    {
        // Valid Ghana mobile numbers: 233 followed by 9 digits (total 12 digits)
        return (bool) preg_match('/^233\d{9}$/', $msisdn);
    }

    /**
     * Translate Nalo status / error codes into friendly human-readable messages.
     */
    public function translateNaloError(int|string $code, ?string $rawMessage = null): string
    {
        $codeInt = (int) $code;

        return match ($codeInt) {
            1701 => 'Message submitted successfully.',
            1702 => 'Invalid URL Error: One or more required SMS parameters were missing.',
            1703 => 'Invalid username or password on Nalo SMS Gateway.',
            1704 => 'Invalid value in type field.',
            1705 => 'Invalid message content.',
            1706 => 'Invalid destination mobile number. Please check the recipient phone number.',
            1707 => "Invalid Sender ID ({$this->senderId}). The sender ID must be registered and approved in Nalo portal.",
            1708 => 'Invalid value for delivery report (dlr) field.',
            1709 => 'User account validation failed on Nalo portal.',
            1710 => 'Internal error at Nalo Solutions SMS gateway. Please try again.',
            1025 => 'Insufficient SMS credit balance in your Nalo account. Please top up your SMS units.',
            1026 => 'Insufficient reseller credit balance on Nalo portal.',
            default => $rawMessage ?: "Nalo Gateway Error (Code {$code}).",
        };
    }
}
