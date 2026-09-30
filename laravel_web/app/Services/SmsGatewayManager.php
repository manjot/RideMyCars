<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsGatewayManager
{
    protected NaloSmsService $naloService;
    protected TwilioSmsService $twilioService;
    protected bool $enableFallback;

    public function __construct(NaloSmsService $naloService, TwilioSmsService $twilioService)
    {
        $this->naloService = $naloService;
        $this->twilioService = $twilioService;
        $this->enableFallback = (bool) config('nalo.fallback_to_twilio', true);
    }

    /**
     * Send SMS automatically routing to the appropriate SMS provider based on destination country code.
     * Ghana (+233) -> Nalo Solutions SMS Gateway
     * All other countries -> Twilio Worldwide SMS Gateway
     *
     * @param string $to Recipient phone number
     * @param string $message Text message content
     * @return array [success => bool, provider => string, message_sid => string|null, error => string|null, code => int|string|null]
     */
    public function sendSms(string $to, string $message): array
    {
        $formattedPhone = $this->formatE164($to);

        // Pre-validate phone format
        $validation = $this->validatePhoneNumber($formattedPhone);
        if (!$validation['valid']) {
            Log::warning("SMS Gateway Manager: Pre-validation failed for [{$to}] -> [{$formattedPhone}]: {$validation['error']}");
            return [
                'success' => false,
                'provider' => 'none',
                'message_sid' => null,
                'error' => $validation['error'],
                'code' => 422,
            ];
        }

        $isGhana = $this->isGhanaNumber($formattedPhone);
        $provider = $isGhana ? 'nalo' : 'twilio';

        Log::info("SMS Gateway Manager: Routing {$formattedPhone} to primary provider [{$provider}].");

        if ($isGhana) {
            // Primary provider: Nalo Solutions
            $result = $this->naloService->sendSms($formattedPhone, $message);

            if ($result['success']) {
                $result['provider'] = 'nalo';
                $result['message_sid'] = $result['message_id'] ?? null;
                return $result;
            }

            // Fallback Behavior: If Nalo fails (e.g. temporary upstream outage or credit depletion),
            // and fallback is enabled, attempt Twilio as secondary fallback.
            if ($this->enableFallback) {
                Log::warning("Nalo SMS failed for Ghana number {$formattedPhone} ({$result['error']}). Attempting secondary fallback via Twilio...");
                $twilioResult = $this->twilioService->sendSmsDirect($formattedPhone, $message);
                if ($twilioResult['success']) {
                    Log::info("Secondary fallback to Twilio succeeded for {$formattedPhone}.");
                    $twilioResult['provider'] = 'twilio_fallback';
                    $twilioResult['original_provider'] = 'nalo';
                    return $twilioResult;
                }
                Log::warning("Twilio fallback also failed for {$formattedPhone}: {$twilioResult['error']}");
            }

            $result['provider'] = 'nalo';
            $result['message_sid'] = $result['message_id'] ?? null;
            return $result;
        }

        // Primary provider: Twilio (All International Destinations)
        $result = $this->twilioService->sendSmsDirect($formattedPhone, $message);
        $result['provider'] = 'twilio';
        return $result;
    }

    /**
     * Send an OTP verification code with the standardized RideMyCars template.
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
     * Check if a given phone number belongs to Ghana (+233).
     */
    public function isGhanaNumber(string $phone): bool
    {
        $formatted = $this->formatE164($phone);
        if (str_starts_with($formatted, '+233')) {
            return true;
        }

        $cleaned = preg_replace('/[^\d]/', '', $phone);
        if (str_starts_with($cleaned, '233')) {
            return true;
        }

        // Check local Ghana prefixes (020, 023, 024, 025, 026, 027, 028, 050, 053, 054, 055, 056, 057, 059)
        if (preg_match('/^0(20|23|24|25|26|27|28|50|53|54|55|56|57|59)\d{7}$/', $cleaned)) {
            return true;
        }

        return false;
    }

    /**
     * Determine which provider will handle the given phone number.
     */
    public function getProviderForNumber(string $phone): string
    {
        return $this->isGhanaNumber($phone) ? 'nalo' : 'twilio';
    }

    /**
     * Standardize international phone number into E.164 format.
     */
    public function formatE164(string $phone, string $defaultDialCode = '+233'): string
    {
        return $this->twilioService->formatE164($phone, $defaultDialCode);
    }

    /**
     * Validate phone number according to ITU-T E.164 standards.
     */
    public function validatePhoneNumber(string $phone): array
    {
        return $this->twilioService->validatePhoneNumber($phone);
    }

    /**
     * Test connectivity for specified or both providers.
     */
    public function testConnection(?string $provider = null): array
    {
        if ($provider === 'nalo') {
            return $this->naloService->testConnection();
        }

        if ($provider === 'twilio') {
            return $this->twilioService->testConnection();
        }

        return [
            'nalo' => $this->naloService->testConnection(),
            'twilio' => $this->twilioService->testConnection(),
        ];
    }
}
