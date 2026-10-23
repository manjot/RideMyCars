<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatewaysTest extends TestCase
{
    /**
     * Test public active gateways endpoint returns proper structure.
     */
    public function test_get_active_gateways_endpoint()
    {
        $response = $this->getJson('/api/payment/gateways');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data',
            'active_gateways',
            'authorizenet',
        ]);
    }

    /**
     * Test disabling Authorize.Net in settings omits it from active gateways.
     */
    public function test_disabling_gateway_hides_it_from_active_list()
    {
        // Explicitly disable Authorize.Net
        Setting::updateOrCreate(
            ['key' => 'payment.authorizenet_enabled'],
            ['value' => '0', 'group' => 'payment']
        );
        SettingService::clearCache();

        $this->assertFalse(SettingService::isAuthorizeNetEnabled());

        $gateways = SettingService::getActivePaymentGateways();
        $this->assertArrayNotHasKey('authorizenet', $gateways);

        $response = $this->getJson('/api/payment/gateways');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertFalse($data['active_gateways']['authorizenet']);

        // Explicitly re-enable Authorize.Net
        Setting::updateOrCreate(
            ['key' => 'payment.authorizenet_enabled'],
            ['value' => '1', 'group' => 'payment']
        );
        SettingService::clearCache();

        $this->assertTrue(SettingService::isAuthorizeNetEnabled());
        $gateways = SettingService::getActivePaymentGateways();
        $this->assertArrayHasKey('authorizenet', $gateways);
    }

    /**
     * Test disabling Stripe in settings omits it from active gateways.
     */
    public function test_disabling_stripe_gateway_hides_it_from_active_list()
    {
        // Explicitly disable Stripe
        Setting::updateOrCreate(
            ['key' => 'payment.stripe_enabled'],
            ['value' => '0', 'group' => 'Payment Gateways']
        );
        SettingService::clearCache();

        $this->assertFalse(SettingService::isStripeEnabled());

        $gateways = SettingService::getActivePaymentGateways();
        $this->assertArrayNotHasKey('stripe', $gateways);

        $response = $this->getJson('/api/payment/gateways');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertFalse($data['active_gateways']['stripe']);

        // Explicitly re-enable Stripe
        Setting::updateOrCreate(
            ['key' => 'payment.stripe_enabled'],
            ['value' => '1', 'group' => 'Payment Gateways']
        );
        SettingService::clearCache();

        $this->assertTrue(SettingService::isStripeEnabled());
        $gateways = SettingService::getActivePaymentGateways();
        $this->assertArrayHasKey('stripe', $gateways);
    }

    /**
     * Test live Authorize.Net merchant connection with real credentials.
     */
    public function test_authorizenet_live_authentication_connection()
    {
        $result = \App\Services\AuthorizeNetService::authenticateTest();

        $this->assertTrue($result['success'], 'Authorize.Net live connection failed: ' . ($result['message'] ?? ''));
        $this->assertEquals('live', $result['mode']);
        $this->assertEquals('3mAE6w39', $result['login_id']);
    }

    /**
     * Test Authorize.Net card transaction processing (pre-auth hold).
     */
    public function test_authorizenet_card_transaction_processing()
    {
        $params = [
            'amount' => 1.00,
            'card_number' => '4007000000027',
            'card_expiry' => '1228',
            'card_code' => '123',
            'card_holder' => 'Jane Doe',
            'description' => 'RideMyCars Automated Gateway Test',
            'order_id' => 'PHPUNIT-' . time(),
        ];

        $result = \App\Services\AuthorizeNetService::authorizeHold($params);

        $this->assertTrue($result['success'], 'Authorize.Net transaction failed: ' . ($result['message'] ?? ''));
        $this->assertEquals('approved', $result['status']);
        $this->assertNotEmpty($result['transaction_id']);
        $this->assertEquals('live', $result['mode']);
    }
}
