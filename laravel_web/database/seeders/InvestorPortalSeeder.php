<?php

namespace Database\Seeders;

use App\Models\ComplianceVault;
use App\Models\CountryComplianceRule;
use App\Models\EsignatureLog;
use App\Models\InvestmentPlan;
use App\Models\InvestorAuditLog;
use App\Models\InvestorProfile;
use App\Models\User;
use App\Services\InvestorComplianceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InvestorPortalSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Investment Plans (Tranches A, B, C)
        $plans = [
            [
                'tranche_code' => 'A',
                'tier_name' => 'Seed Tier',
                'capital_commitment_ghc' => 720000.00,
                'capital_commitment_usd' => 60000.00,
                'equity_percentage' => 10.00,
                'min_investment_usd' => 10000.00,
                'max_investment_usd' => 60000.00,
                'summary_headline' => 'Early Angel Participation in West Africa Fleet Tech',
                'description' => 'Targeted at angel investors entering initial cohort expansion with quarterly dividend participation.',
                'perks' => [
                    '10.0% non-dilutive single cohort equity stake',
                    'Quarterly multi-currency dividend distributions',
                    '24/7 Access to Live Micro-Transaction Ticker',
                    'Standard Year 3 structured buyout option',
                ],
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'tranche_code' => 'B',
                'tier_name' => 'Growth Tier',
                'capital_commitment_ghc' => 1440000.00,
                'capital_commitment_usd' => 120000.00,
                'equity_percentage' => 14.00,
                'min_investment_usd' => 25000.00,
                'max_investment_usd' => 120000.00,
                'summary_headline' => 'Flagship Allocation with Priority Escrow Clearance',
                'description' => 'Engineered for family offices and syndicates with enhanced liquidity provisions and clawback reserves.',
                'perks' => [
                    '14.0% non-dilutive single cohort equity stake',
                    'Accelerated quarterly dividend wire clearance',
                    'Full 3-Year Master Cash Ledger telemetry data access',
                    'Priority clearance via Ride My Cars (Ghana) escrow',
                    'Enhanced Year 3 structured buyout terms',
                ],
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'tranche_code' => 'C',
                'tier_name' => 'Venture Tier',
                'capital_commitment_ghc' => 2640000.00,
                'capital_commitment_usd' => 220000.00,
                'equity_percentage' => 22.00,
                'min_investment_usd' => 50000.00,
                'max_investment_usd' => 220000.00,
                'summary_headline' => 'Institutional Scale Allocation with Governance Seat',
                'description' => 'Designed for venture capital funds and sovereign investment vehicles with advisory observer representation.',
                'perks' => [
                    '22.0% non-dilutive single cohort equity stake',
                    'Maximum quarterly dividend pool participation',
                    'Advisory board observer seat consideration',
                    'Dedicated wire SWIFT & MoMo escrow desk',
                    'Premium institutional Year 3 buyout multiple',
                ],
                'is_active' => true,
                'display_order' => 3,
            ],
        ];

        foreach ($plans as $planData) {
            InvestmentPlan::updateOrCreate(
                ['tranche_code' => $planData['tranche_code']],
                $planData
            );
        }

        // 2. Seed Country Compliance Rules
        $countries = ['USA', 'GHA', 'CAN', 'GBR', 'EU', 'AFRICA', 'ROW'];
        foreach ($countries as $idx => $code) {
            $rule = InvestorComplianceService::getDefaultRules($code);
            CountryComplianceRule::updateOrCreate(
                ['country_code' => $rule['country_code']],
                [
                    'country_name' => $rule['country_name'],
                    'regulatory_body' => $rule['regulatory_body'],
                    'regulatory_tier' => $rule['regulatory_tier'],
                    'verification_gate_title' => $rule['verification_gate_title'],
                    'verification_gate_description' => $rule['verification_gate_description'],
                    'required_documents' => $rule['required_documents'],
                    'declarations' => $rule['declarations'],
                    'compliance_text' => $rule['compliance_text'],
                    'legal_notices' => $rule['legal_notices'],
                    'is_active' => true,
                    'display_order' => $idx + 1,
                ]
            );
        }

        // 3. Seed Demo Approved Investor
        $userApproved = User::updateOrCreate(
            ['email' => 'investor.approved@ridemycars.com'],
            [
                'name' => 'Apex Global Capital Partners LLC',
                'phone' => '+1 (202) 555-0199',
                'country' => 'United States',
                'city' => 'New York',
                'password' => Hash::make('123456'),
                'role' => 'investor',
                'account_status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $investorApproved = InvestorProfile::updateOrCreate(
            ['email' => 'investor.approved@ridemycars.com'],
            [
                'user_id' => $userApproved->id,
                'legal_name' => 'Apex Global Capital Partners LLC',
                'phone_number' => '+1 (202) 555-0199',
                'country_residence' => 'United States',
                'country_code' => 'USA',
                'address' => '350 Fifth Avenue, Suite 4800',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10118',
                'entity_type' => 'institutional',
                'tax_id_or_national_id' => 'EIN-12-3456789',
                'regulatory_tier' => 'US_REG_D',
                'accreditation_details' => [
                    'income_check' => true,
                    'net_worth_check' => true,
                    'rule_506c_ack' => true,
                ],
                'selected_tranche' => 'B',
                'equity_percentage' => 14.00,
                'capital_commitment_ghc' => 1440000.00,
                'capital_commitment_usd' => 120000.00,
                'remittance_method' => 'wire_swift',
                'verification_status' => 'APPROVED',
                'payment_unlocked' => true,
                'payment_reference_code' => 'NDFG-INV-2026-APEX77',
                'admin_notes' => 'Accredited investor certification verified by CPA verification letter dated August 2026. Ride My Cars (Ghana) escrow coordinates released.',
                'verified_at' => now()->subDays(2),
            ]
        );

        EsignatureLog::updateOrCreate(
            ['investor_id' => $investorApproved->id],
            [
                'signer_name' => 'Jonathan Vance, Managing Director',
                'agreement_version' => 'NDFG_OA_v3.1',
                'governance_clause_accepted' => true,
                'ip_address' => '64.233.160.1',
                'browser_user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko)',
                'signed_timestamp' => now()->subDays(3),
            ]
        );

        ComplianceVault::updateOrCreate(
            ['investor_id' => $investorApproved->id, 'document_type' => 'CPA_LETTER'],
            [
                'document_title' => 'Certified CPA Accreditation Verification Letter',
                'original_filename' => 'Apex_Capital_CPA_Verification_2026.pdf',
                'file_path' => 'investor_vault/' . $investorApproved->id . '/cpa_verification.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 245760,
                'status' => 'VERIFIED',
                'uploaded_at' => now()->subDays(3),
            ]
        );

        InvestorAuditLog::create([
            'investor_id' => $investorApproved->id,
            'user_id' => $userApproved->id,
            'action' => 'PAYMENT_UNLOCKED',
            'description' => 'Escrow wire coordinates unlocked for Tranche B (1,440,000 GHC / $120,000 USD)',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Admin Console',
            'created_at' => now()->subDays(2),
        ]);

        // 4. Seed Demo Pending Investor
        $userPending = User::updateOrCreate(
            ['email' => 'investor.pending@ridemycars.com'],
            [
                'name' => 'Kofi Mensah Holdings',
                'phone' => '+233 24 412 3456',
                'country' => 'Ghana',
                'city' => 'Accra',
                'password' => Hash::make('123456'),
                'role' => 'investor',
                'account_status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $investorPending = InvestorProfile::updateOrCreate(
            ['email' => 'investor.pending@ridemycars.com'],
            [
                'user_id' => $userPending->id,
                'legal_name' => 'Kofi Mensah Holdings',
                'phone_number' => '+233 24 412 3456',
                'country_residence' => 'Ghana',
                'country_code' => 'GHA',
                'address' => '14 Independence Avenue, Ridge',
                'city' => 'Accra',
                'state' => 'Greater Accra',
                'postal_code' => 'GA-039-1234',
                'entity_type' => 'corporate',
                'tax_id_or_national_id' => 'GHA-712839102-4',
                'regulatory_tier' => 'GH_SEC',
                'accreditation_details' => [
                    'gh_sec_hnw_ack' => true,
                    'gh_eminsang_ack' => true,
                ],
                'selected_tranche' => 'A',
                'equity_percentage' => 10.00,
                'capital_commitment_ghc' => 720000.00,
                'capital_commitment_usd' => 60000.00,
                'remittance_method' => 'local_bank_eminsang',
                'verification_status' => 'PENDING',
                'payment_unlocked' => false,
                'payment_reference_code' => 'NDFG-INV-2026-GH720A',
            ]
        );

        EsignatureLog::updateOrCreate(
            ['investor_id' => $investorPending->id],
            [
                'signer_name' => 'Kofi Mensah, Executive Chairman',
                'agreement_version' => 'NDFG_OA_v3.1',
                'governance_clause_accepted' => true,
                'ip_address' => '41.215.160.10',
                'browser_user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'signed_timestamp' => now()->subHours(8),
            ]
        );

        ComplianceVault::updateOrCreate(
            ['investor_id' => $investorPending->id, 'document_type' => 'GH_CARD'],
            [
                'document_title' => 'ECOWAS Ghana Card Photo Identification',
                'original_filename' => 'Ghana_Card_Kofi_Mensah.jpg',
                'file_path' => 'investor_vault/' . $investorPending->id . '/gh_card.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 184320,
                'status' => 'PENDING',
                'uploaded_at' => now()->subHours(8),
            ]
        );

        // 5. Seed Demo Action Needed Investor
        $userDocs = User::updateOrCreate(
            ['email' => 'investor.docs@ridemycars.com'],
            [
                'name' => 'Sterling Fleet Venture Fund',
                'phone' => '+44 20 7946 0912',
                'country' => 'United Kingdom',
                'city' => 'London',
                'password' => Hash::make('123456'),
                'role' => 'investor',
                'account_status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $investorDocs = InvestorProfile::updateOrCreate(
            ['email' => 'investor.docs@ridemycars.com'],
            [
                'user_id' => $userDocs->id,
                'legal_name' => 'Sterling Fleet Venture Fund',
                'phone_number' => '+44 20 7946 0912',
                'country_residence' => 'United Kingdom',
                'country_code' => 'GBR',
                'address' => '25 Bank Street, Canary Wharf',
                'city' => 'London',
                'state' => 'Greater London',
                'postal_code' => 'E14 5JP',
                'entity_type' => 'institutional',
                'tax_id_or_national_id' => 'UK-CRN-08912410',
                'regulatory_tier' => 'UK_FCA',
                'accreditation_details' => [
                    'fca_sophisticated_ack' => true,
                ],
                'selected_tranche' => 'C',
                'equity_percentage' => 22.00,
                'capital_commitment_ghc' => 2640000.00,
                'capital_commitment_usd' => 220000.00,
                'remittance_method' => 'wire_swift',
                'verification_status' => 'NEED_MORE_DOCS',
                'payment_unlocked' => false,
                'payment_reference_code' => 'NDFG-INV-2026-UK264C',
                'document_request_notes' => 'Please provide proof of UK corporate registry certificate of incorporation and updated utility statement dated within the last 90 days.',
            ]
        );

        EsignatureLog::updateOrCreate(
            ['investor_id' => $investorDocs->id],
            [
                'signer_name' => 'Alistair Sterling, Managing Partner',
                'agreement_version' => 'NDFG_OA_v3.1',
                'governance_clause_accepted' => true,
                'ip_address' => '82.165.197.1',
                'browser_user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                'signed_timestamp' => now()->subDay(),
            ]
        );
    }
}
