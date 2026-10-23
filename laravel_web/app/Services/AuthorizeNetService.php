<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthorizeNetService
{
    /**
     * Get active endpoint URL based on environment mode.
     */
    public static function getEndpoint(): string
    {
        $mode = SettingService::getActiveAuthorizeNetMode();
        return ($mode === 'live')
            ? 'https://api.authorize.net/xml/v1/request.api'
            : 'https://apitest.authorize.net/xml/v1/request.api';
    }

    /**
     * Get active Accept.js client SDK URL.
     */
    public static function getAcceptJsUrl(): string
    {
        $mode = SettingService::getActiveAuthorizeNetMode();
        return ($mode === 'live')
            ? 'https://js.authorize.net/v1/Accept.js'
            : 'https://jstest.authorize.net/v1/Accept.js';
    }

    /**
     * Build merchant authentication credentials block.
     */
    protected static function getMerchantAuth(): array
    {
        return [
            'name' => SettingService::getActiveAuthorizeNetApiLoginId(),
            'transactionKey' => SettingService::getActiveAuthorizeNetTransactionKey(),
        ];
    }

    /**
     * Clean Authorize.Net response body by stripping possible UTF-8 BOM.
     */
    protected static function cleanResponse(string $body): array
    {
        $clean = trim($body);
        // Authorize.Net API occasionally prefixes UTF-8 BOM (\xEF\xBB\xBF)
        $clean = preg_replace('/^\xEF\xBB\xBF/', '', $clean);
        $decoded = json_decode($clean, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Test merchant credentials with the Authorize.Net API.
     */
    public static function authenticateTest(): array
    {
        $loginId = SettingService::getActiveAuthorizeNetApiLoginId();
        $transKey = SettingService::getActiveAuthorizeNetTransactionKey();
        $mode = SettingService::getActiveAuthorizeNetMode();

        if (empty($loginId) || empty($transKey)) {
            return [
                'success' => false,
                'mode' => $mode,
                'message' => "No Authorize.Net API Login ID or Transaction Key configured for {$mode} mode. Please enter credentials.",
                'code' => 'MISSING_KEYS',
            ];
        }

        $payload = [
            'authenticateTestRequest' => [
                'merchantAuthentication' => [
                    'name' => $loginId,
                    'transactionKey' => $transKey,
                ],
            ],
        ];

        try {
            $endpoint = static::getEndpoint();
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(15)->post($endpoint, $payload);

            $data = static::cleanResponse($response->body());
            $resultCode = $data['messages']['resultCode'] ?? 'Error';
            $messageObj = $data['messages']['message'][0] ?? [];
            $code = $messageObj['code'] ?? 'UNKNOWN';
            $text = $messageObj['text'] ?? 'No message returned from Authorize.Net.';

            if (strtolower($resultCode) === 'ok') {
                return [
                    'success' => true,
                    'mode' => $mode,
                    'message' => "Authorize.Net Connected Successfully ({$text})",
                    'code' => $code,
                    'login_id' => $loginId,
                    'endpoint' => $endpoint,
                ];
            }

            return [
                'success' => false,
                'mode' => $mode,
                'message' => "Authorize.Net Auth Failed [{$code}]: {$text}",
                'code' => $code,
                'login_id' => $loginId,
            ];
        } catch (\Throwable $e) {
            Log::error("Authorize.Net authenticateTest error: " . $e->getMessage());
            return [
                'success' => false,
                'mode' => $mode,
                'message' => "Connection Error: " . $e->getMessage(),
                'code' => 'EXCEPTION',
            ];
        }
    }

    /**
     * Execute a charge transaction (authCaptureTransaction).
     */
    public static function createPayment(array $data): array
    {
        return static::sendTransaction('authCaptureTransaction', $data);
    }

    /**
     * Execute a pre-authorization hold (authOnlyTransaction).
     */
    public static function authorizeHold(array $data): array
    {
        return static::sendTransaction('authOnlyTransaction', $data);
    }

    /**
     * Capture a prior pre-authorization hold (priorAuthCaptureTransaction).
     */
    public static function captureHold(string $transId, float $amount, ?string $refId = null): array
    {
        $payload = [
            'createTransactionRequest' => [
                'merchantAuthentication' => static::getMerchantAuth(),
                'refId' => $refId ?: ('CAP-' . time()),
                'transactionRequest' => [
                    'transactionType' => 'priorAuthCaptureTransaction',
                    'amount' => number_format($amount, 2, '.', ''),
                    'refTransId' => $transId,
                ],
            ],
        ];

        return static::dispatchRequest($payload);
    }

    /**
     * Void a transaction / release an uncaptured hold (voidTransaction).
     */
    public static function voidTransaction(string $transId, ?string $refId = null): array
    {
        $payload = [
            'createTransactionRequest' => [
                'merchantAuthentication' => static::getMerchantAuth(),
                'refId' => $refId ?: ('VOID-' . time()),
                'transactionRequest' => [
                    'transactionType' => 'voidTransaction',
                    'refTransId' => $transId,
                ],
            ],
        ];

        return static::dispatchRequest($payload);
    }

    /**
     * Refund a settled transaction (refundTransaction).
     */
    public static function refundTransaction(string $transId, float $amount, string $last4, ?string $refId = null): array
    {
        $payload = [
            'createTransactionRequest' => [
                'merchantAuthentication' => static::getMerchantAuth(),
                'refId' => $refId ?: ('REF-' . time()),
                'transactionRequest' => [
                    'transactionType' => 'refundTransaction',
                    'amount' => number_format($amount, 2, '.', ''),
                    'payment' => [
                        'creditCard' => [
                            'cardNumber' => $last4,
                            'expirationDate' => 'XXXX',
                        ],
                    ],
                    'refTransId' => $transId,
                ],
            ],
        ];

        return static::dispatchRequest($payload);
    }

    /**
     * Dispatch an authCaptureTransaction or authOnlyTransaction request.
     */
    protected static function sendTransaction(string $type, array $data): array
    {
        $amount = (float) ($data['amount'] ?? 0);
        $formattedAmount = number_format($amount, 2, '.', '');
        $refId = $data['ref_id'] ?? ('ANET-' . strtoupper(\Illuminate\Support\Str::random(10)));

        $paymentBlock = [];

        // Check if Accept.js token (opaqueData) was supplied
        if (!empty($data['opaque_data_value'])) {
            $paymentBlock['opaqueData'] = [
                'dataDescriptor' => $data['opaque_data_descriptor'] ?? 'COMMON.ACCEPT.INAPP.PAYMENT',
                'dataValue' => $data['opaque_data_value'],
            ];
        } else {
            // Direct card fields
            $cardNumber = preg_replace('/\D/', '', (string) ($data['card_number'] ?? ''));
            $expiry = str_replace(['/', ' '], '', (string) ($data['expiration_date'] ?? $data['card_expiry'] ?? ''));
            // If expiry is MMYY (4 digits) or MMYYYY, format for Authorize.Net (MMYY or YYYY-MM)
            if (strlen($expiry) === 4) {
                // e.g. "1228" -> "1228" or "2028-12"
                $expiry = substr($expiry, 0, 2) . substr($expiry, 2, 2);
            }
            $cvv = (string) ($data['card_code'] ?? $data['card_cvv'] ?? '');

            $paymentBlock['creditCard'] = [
                'cardNumber' => $cardNumber,
                'expirationDate' => $expiry,
            ];
            if (!empty($cvv)) {
                $paymentBlock['creditCard']['cardCode'] = $cvv;
            }
        }

        $txRequest = [
            'transactionType' => $type,
            'amount' => $formattedAmount,
            'payment' => $paymentBlock,
        ];

        // Line item / invoice details
        if (!empty($data['description']) || !empty($data['order_id'])) {
            $txRequest['order'] = [
                'invoiceNumber' => substr((string)($data['order_id'] ?? $refId), 0, 20),
                'description' => substr((string)($data['description'] ?? 'RideMyCars Booking'), 0, 255),
            ];
        }

        // Customer details
        if (!empty($data['customer_email']) || !empty($data['customer_id'])) {
            $txRequest['customer'] = array_filter([
                'id' => substr((string)($data['customer_id'] ?? ''), 0, 20),
                'email' => substr((string)($data['customer_email'] ?? ''), 0, 255),
            ]);
        }

        // Billing address details
        $firstName = $data['first_name'] ?? '';
        $lastName = $data['last_name'] ?? '';
        if (empty($firstName) && !empty($data['card_holder'])) {
            $parts = explode(' ', trim($data['card_holder']), 2);
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? 'Cardholder';
        }

        if (!empty($firstName)) {
            $txRequest['billTo'] = array_filter([
                'firstName' => substr($firstName, 0, 50),
                'lastName' => substr($lastName ?: 'Customer', 0, 50),
                'company' => substr((string)($data['company'] ?? ''), 0, 50),
                'address' => substr((string)($data['address'] ?? ''), 0, 60),
                'city' => substr((string)($data['city'] ?? ''), 0, 40),
                'state' => substr((string)($data['state'] ?? ''), 0, 40),
                'zip' => substr((string)($data['zip'] ?? ''), 0, 20),
                'country' => substr((string)($data['country'] ?? 'US'), 0, 60),
                'phoneNumber' => substr((string)($data['phone'] ?? ''), 0, 25),
            ]);
        }

        $payload = [
            'createTransactionRequest' => [
                'merchantAuthentication' => static::getMerchantAuth(),
                'refId' => $refId,
                'transactionRequest' => $txRequest,
            ],
        ];

        return static::dispatchRequest($payload);
    }

    /**
     * Send HTTP POST request to Authorize.Net JSON API and parse standardized result.
     */
    protected static function dispatchRequest(array $payload): array
    {
        $endpoint = static::getEndpoint();
        $mode = SettingService::getActiveAuthorizeNetMode();

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(25)->post($endpoint, $payload);

            $data = static::cleanResponse($response->body());
            $txResponse = $data['transactionResponse'] ?? [];
            $responseCode = (string) ($txResponse['responseCode'] ?? '0');
            $transId = (string) ($txResponse['transId'] ?? '');
            $authCode = (string) ($txResponse['authCode'] ?? '');
            $accountNumber = (string) ($txResponse['accountNumber'] ?? '');
            $accountType = (string) ($txResponse['accountType'] ?? 'Card');

            // Response Code 1 = Approved
            if ($responseCode === '1') {
                $msg = $txResponse['messages'][0]['description'] ?? 'Transaction approved by Authorize.Net.';
                return [
                    'success' => true,
                    'status' => 'approved',
                    'transaction_id' => $transId,
                    'auth_code' => $authCode,
                    'account_number' => $accountNumber,
                    'account_type' => $accountType,
                    'message' => $msg,
                    'mode' => $mode,
                    'raw' => $data,
                ];
            }

            // Response Code 4 = Held for Review
            if ($responseCode === '4') {
                $msg = $txResponse['messages'][0]['description'] ?? 'Transaction held for review.';
                return [
                    'success' => true,
                    'status' => 'held_for_review',
                    'transaction_id' => $transId,
                    'auth_code' => $authCode,
                    'message' => $msg,
                    'mode' => $mode,
                    'raw' => $data,
                ];
            }

            // Errors & Declines (Response Code 2 = Declined, 3 = Error)
            $errorDesc = $txResponse['errors'][0]['errorText'] 
                ?? $data['messages']['message'][0]['text'] 
                ?? 'Authorize.Net transaction declined.';
            $errorCode = $txResponse['errors'][0]['errorCode'] 
                ?? $data['messages']['message'][0]['code'] 
                ?? ('CODE_' . $responseCode);

            return [
                'success' => false,
                'status' => ($responseCode === '2') ? 'declined' : 'error',
                'transaction_id' => $transId,
                'error_code' => $errorCode,
                'message' => "Authorize.Net Error [{$errorCode}]: {$errorDesc}",
                'mode' => $mode,
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            Log::error("Authorize.Net API request error: " . $e->getMessage());
            return [
                'success' => false,
                'status' => 'network_error',
                'error_code' => 'EXCEPTION',
                'message' => 'Authorize.Net Gateway communication error: ' . $e->getMessage(),
                'mode' => $mode,
            ];
        }
    }
}
