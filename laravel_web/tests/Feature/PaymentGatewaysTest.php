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
}
