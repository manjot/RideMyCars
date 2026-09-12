<?php

namespace Tests\Feature;

use App\Models\DriverBooking;
use App\Models\PaymentTransaction;
use App\Models\Ride;
use App\Models\Setting;
use App\Models\User;
use App\Services\ExpressPayService;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExpressPayGhanaIntegrationTest extends TestCase
{
    /**
     * Test SettingService dynamic getters for ExpressPay.
     */
    public function test_setting_service_returns_active_expresspay_credentials(): void
    {
        Setting::updateOrCreate(['key' => 'payment.expresspay_enabled'], ['value' => '1', 'group' => 'Payment Gateways', 'type' => 'text']);
        Setting::updateOrCreate(['key' => 'payment.expresspay_mode'], ['value' => 'sandbox', 'group' => 'Payment Gateways', 'type' => 'text']);
        Setting::updateOrCreate(['key' => 'payment.expresspay_sandbox_merchant_id'], ['value' => '562786243097', 'group' => 'Payment Gateways', 'type' => 'text']);
        Setting::updateOrCreate(['key' => 'payment.expresspay_sandbox_api_key'], ['value' => 'DInEOn1ayqtjC420gHLJ4-IiCSoZKPR13lxkLyzqiD-PcXhMFOBwKyoUw9hzAY1-hYnIGJov5Rbz8hme7Nm', 'group' => 'Payment Gateways', 'type' => 'text']);
        SettingService::flushCache();
        SettingService::syncToConfig();

        $this->assertTrue(SettingService::isExpressPayEnabled());
        $this->assertEquals('sandbox', SettingService::getActiveExpressPayMode());
        $this->assertEquals('562786243097', SettingService::getActiveExpressPayMerchantId());
        $this->assertEquals('DInEOn1ayqtjC420gHLJ4-IiCSoZKPR13lxkLyzqiD-PcXhMFOBwKyoUw9hzAY1-hYnIGJov5Rbz8hme7Nm', SettingService::getActiveExpressPayApiKey());
        $this->assertEquals('https://sandbox.expresspaygh.com/api/submit.php', SettingService::getExpressPaySubmitUrl());
        $this->assertEquals('https://sandbox.expresspaygh.com/api/query.php', SettingService::getExpressPayQueryUrl());
        $this->assertEquals('https://sandbox.expresspaygh.com/api/checkout.php?token=test_tok', SettingService::getExpressPayCheckoutUrl('test_tok'));
    }

    /**
     * Test live sandbox API connectivity with user credentials.
     */
    public function test_expresspay_sandbox_submit_api_creates_token(): void
    {
        $payload = [
            'service_type' => 'ride',
            'service_id' => 9999,
            'amount' => 25.00,
            'currency' => 'GHS',
            'customer_name' => 'Kojo Mensah',
            'customer_email' => 'kojo.mensah@example.com',
            'customer_phone' => '0244444444',
            'order_desc' => 'Test Ride #9999',
        ];

        $result = ExpressPayService::createPayment($payload);

        $this->assertTrue($result['success'], 'ExpressPay submit API call failed: ' . ($result['message'] ?? ''));
        $this->assertNotEmpty($result['token']);
        $this->assertNotEmpty($result['checkout_url']);
        $this->assertStringContainsString('token=', $result['checkout_url']);
    }

    /**
     * Test ExpressPayController initiation endpoint.
     */
    public function test_expresspay_controller_initiate_endpoint(): void
    {
        $user = User::factory()->create();
        $ride = Ride::create([
            'rider_id' => $user->id,
            'passenger_name' => 'Ama Serwaa',
            'passenger_email' => 'ama.serwaa@example.com',
            'phone_number' => '0244444444',
            'pickup_location' => 'Accra Mall',
            'dropoff_location' => 'Kotoka International Airport',
            'fare' => 35.00,
            'total_amount' => 35.00,
            'currency' => 'GHS',
            'payment_method' => 'momo',
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->postJson('/payment/expresspay/initiate', [
            'service_type' => 'ride',
            'service_id' => $ride->id,
            'phone' => '0244444444',
            'network' => 'MTN',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'order_id',
            'token',
            'checkout_url',
            'redirect_url',
        ]);
        $this->assertTrue($response->json('success'));
    }

    /**
     * Test ExpressPay IPN webhook returns HTTP 200 OK.
     */
    public function test_expresspay_ipn_returns_http_200_ok(): void
    {
        $response = $this->post('/api/payment/expresspay/ipn', [
            'order-id' => 'EXP-TEST-1234',
            'token' => 'mock_token_123',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('OK', $response->getContent());
    }
}
