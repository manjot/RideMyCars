<?php

namespace Tests\Feature;

use App\Models\CountryComplianceRule;
use App\Models\InvestmentPlan;
use App\Models\InvestorProfile;
use App\Models\User;
use Database\Seeders\InvestorPortalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvestorPortalFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(InvestorPortalSeeder::class);
    }

    public function test_public_investor_pages_return_successful_response(): void
    {
        $routes = [
            '/investor',
            '/investor/why-invest',
            '/investor/opportunity',
            '/investor/plans',
            '/investor/faq',
            '/investor/contact',
            '/investor/regulatory-notices',
            '/investor/login',
            '/investor/register',
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);
            $response->assertSee('REGULATORY COMPLIANCE');
        }
    }

    public function test_country_compliance_rules_api(): void
    {
        $response = $this->getJson('/api/investor/country-rules/USA');
        $response->assertStatus(200);
        $response->assertJson([
            'country_code' => 'USA',
            'regulatory_tier' => 'US_REG_D',
        ]);

        $ghanaResponse = $this->getJson('/api/investor/country-rules/GHA');
        $ghanaResponse->assertStatus(200);
        $ghanaResponse->assertJson([
            'country_code' => 'GHA',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_investor_dashboard(): void
    {
        $response = $this->get('/investor/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_pending_investor_dashboard_is_gated(): void
    {
        $user = User::where('email', 'investor.pending@ridemycars.com')->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->get('/investor/dashboard');
        $response->assertStatus(200);

        // Must show pending verification badge & status
        $response->assertSee('Compliance Review Initiated');
        $response->assertSee('Regulatory Tier Vetting');

        // Must NOT show escrow wire coordinates or financial ledger
        $response->assertDontSee('3-Year Master Cash Ledger');
        $response->assertDontSee('Authorized Capital Remittance Coordinates');
    }

    public function test_approved_investor_dashboard_unlocks_ledger_and_payment(): void
    {
        $user = User::where('email', 'investor.approved@ridemycars.com')->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->get('/investor/dashboard');
        $response->assertStatus(200);

        // Must show official welcome message from SRS
        $response->assertSee('Welcome to the NDFG LLC Development Portal');
        $response->assertSee('Your account has been securely verified');

        // Must show unlocked ledger and escrow coordinates
        $response->assertSee('3-Year Master Cash Ledger');
        $response->assertSee('Authorized Capital Remittance Coordinates');
        $response->assertSee('Eminsang Group Limited');
        $response->assertSee('MTN MoMo / Telecel Cash');
    }

    public function test_investor_registration_flow(): void
    {
        Storage::fake('local');

        $uniqueEmail = 'new.investor.' . time() . '@example.com';

        $file = UploadedFile::fake()->create('accreditation_cpa.pdf', 500, 'application/pdf');

        $payload = [
            'legal_name' => 'Apex Capital Ventures LLC',
            'email' => $uniqueEmail,
            'phone_number' => '+1 415 555 0199',
            'country_code' => 'USA',
            'country_residence' => 'United States',
            'entity_type' => 'corporate',
            'address' => '100 Financial Way, Suite 400',
            'city' => 'San Francisco',
            'state' => 'CA',
            'postal_code' => '94105',
            'password' => 'CapitalSecure2026!',
            'selected_tranche' => 'B',
            'remittance_method' => 'bank_wire',
            'declarations' => [
                'income_check' => '1',
                'net_worth_check' => '1',
                'rule_506c_ack' => '1',
            ],
            'document_cpa_letter' => $file,
            'governance_clause_accepted' => '1',
            'signer_name' => 'Alexander Vance, Managing Director',
        ];

        $response = $this->post('/investor/register', $payload);
        $response->assertRedirect('/investor/dashboard');

        $user = User::where('email', $uniqueEmail)->first();
        $this->assertNotNull($user);
        $this->assertEquals('investor', $user->role);

        $profile = InvestorProfile::where('email', $uniqueEmail)->first();
        $this->assertNotNull($profile);
        $this->assertEquals('PENDING', $profile->verification_status);
        $this->assertEquals('B', $profile->selected_tranche);
        $this->assertEquals(14.00, (float)$profile->equity_percentage);
        $this->assertEquals(1440000.00, (float)$profile->capital_commitment_ghc);

        // Verify e-signature log was recorded
        $this->assertDatabaseHas('esignature_logs', [
            'investor_id' => $profile->id,
            'agreement_version' => 'NDFG_OA_v3.1',
        ]);

        // Verify audit log was recorded
        $this->assertDatabaseHas('investor_audit_logs', [
            'investor_id' => $profile->id,
            'action' => 'REGISTERED',
        ]);
    }

    public function test_approved_investor_can_download_agreement_certificate(): void
    {
        $user = User::where('email', 'investor.approved@ridemycars.com')->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->get('/investor/agreement/download');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/plain; charset=utf-8');
        $response->assertSee('OPERATING AGREEMENT GOVERNANCE CERTIFICATE (v3.1)');
        $response->assertSee('NDFG_OA_v3.1');
    }

    public function test_unapproved_investor_cannot_download_agreement_certificate(): void
    {
        $user = User::where('email', 'investor.pending@ridemycars.com')->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->get('/investor/agreement/download');
        $response->assertStatus(403);
    }

    public function test_investor_can_reupload_requested_documents(): void
    {
        Storage::fake('local');

        $user = User::where('email', 'investor.docs@ridemycars.com')->first();
        $this->assertNotNull($user);

        $file = UploadedFile::fake()->create('fresh_bank_statement.pdf', 300, 'application/pdf');

        $response = $this->actingAs($user)->post('/investor/vault/reupload', [
            'document_type' => 'ADDRESS_PROOF',
            'reupload_file' => $file,
        ]);

        $response->assertRedirect('/investor/dashboard');
        $response->assertSessionHas('success');

        $profile = InvestorProfile::where('email', 'investor.docs@ridemycars.com')->first();
        $this->assertEquals('UNDER_REVIEW', $profile->verification_status);

        $this->assertDatabaseHas('compliance_vault', [
            'investor_id' => $profile->id,
            'document_type' => 'ADDRESS_PROOF',
            'original_filename' => 'fresh_bank_statement.pdf',
        ]);
    }
}

