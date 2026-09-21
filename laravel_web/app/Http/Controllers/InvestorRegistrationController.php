<?php

namespace App\Http\Controllers;

use App\Models\CountryComplianceRule;
use App\Models\EsignatureLog;
use App\Models\InvestmentPlan;
use App\Models\InvestorProfile;
use App\Models\User;
use App\Services\InvestorComplianceService;
use App\Services\InvestorEmailService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvestorRegistrationController extends Controller
{
    /**
     * Display the 5-step onboarding wizard
     */
    public function create(Request $request)
    {
        $selectedTranche = strtoupper($request->query('tranche', 'B'));
        if (!in_array($selectedTranche, ['A', 'B', 'C'])) {
            $selectedTranche = 'B';
        }

        $plans = InvestmentPlan::active()->get();
        if ($plans->isEmpty()) {
            $plans = collect([
                (object) [
                    'tranche_code' => 'A',
                    'tier_name' => 'Seed Tier',
                    'capital_commitment_ghc' => 720000.00,
                    'capital_commitment_usd' => 60000.00,
                    'equity_percentage' => 10.00,
                    'formatted_ghc' => '720,000 GHC',
                    'formatted_usd' => '$60,000',
                ],
                (object) [
                    'tranche_code' => 'B',
                    'tier_name' => 'Growth Tier',
                    'capital_commitment_ghc' => 1440000.00,
                    'capital_commitment_usd' => 120000.00,
                    'equity_percentage' => 14.00,
                    'formatted_ghc' => '1,440,000 GHC',
                    'formatted_usd' => '$120,000',
                ],
                (object) [
                    'tranche_code' => 'C',
                    'tier_name' => 'Venture Tier',
                    'capital_commitment_ghc' => 2640000.00,
                    'capital_commitment_usd' => 220000.00,
                    'equity_percentage' => 22.00,
                    'formatted_ghc' => '2,640,000 GHC',
                    'formatted_usd' => '$220,000',
                ],
            ]);
        }

        $countryRules = [
            'USA' => InvestorComplianceService::getCountryRules('USA'),
            'GHA' => InvestorComplianceService::getCountryRules('GHA'),
            'CAN' => InvestorComplianceService::getCountryRules('CAN'),
            'GBR' => InvestorComplianceService::getCountryRules('GBR'),
            'EU' => InvestorComplianceService::getCountryRules('EU'),
            'AFRICA' => InvestorComplianceService::getCountryRules('AFRICA'),
            'ROW' => InvestorComplianceService::getCountryRules('ROW'),
        ];

        return view('investor.onboarding', compact('selectedTranche', 'plans', 'countryRules'));
    }

    /**
     * API endpoint to dynamically retrieve country rules
     */
    public function getCountryRules(string $countryCode)
    {
        return response()->json(InvestorComplianceService::getCountryRules($countryCode));
    }

    /**
     * Store completed 5-step registration
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Step 1: Identity & Location
            'legal_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:50',
            'country_code' => 'required|string|max:10',
            'country_residence' => 'required|string|max:100',
            'entity_type' => 'required|string|in:individual,corporate,institutional',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6',

            // Step 2: Accreditation & Declarations
            'tax_id_or_national_id' => 'nullable|string|max:100',
            'declarations' => 'nullable|array',

            // Step 3: Allocation Selection
            'selected_tranche' => 'required|in:A,B,C',
            'remittance_method' => 'required|string|max:100',

            // Step 4: Documents
            'document_cpa_letter' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:12288',
            'document_gov_id' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:12288',
            'document_passport' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:12288',
            'document_gh_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:12288',
            'document_tax_tin' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:12288',
            'document_address_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:12288',
            'document_corp_docs' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:12288',
            'document_risk_ack' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:12288',

            // Step 5: e-Signature
            'governance_clause_accepted' => 'required|accepted',
            'signer_name' => 'required|string|max:255',
        ]);

        $email = strtolower(trim($validated['email']));

        // Check if an investor profile already exists for this email
        $existingProfile = InvestorProfile::where('email', $email)->first();
        if ($existingProfile) {
            return back()->withInput()->withErrors([
                'email' => 'An investor profile with this email already exists. Please log in to your dashboard.'
            ]);
        }

        DB::beginTransaction();
        try {
            // 1. Resolve or create associated User account
            $user = Auth::user();
            if (!$user) {
                $user = User::where('email', $email)->first();
                if (!$user) {
                    $password = !empty($validated['password']) ? $validated['password'] : Str::random(12);
                    $user = User::create([
                        'name' => $validated['legal_name'],
                        'email' => $email,
                        'phone' => $validated['phone_number'],
                        'country' => $validated['country_residence'],
                        'city' => $validated['city'] ?? null,
                        'password' => Hash::make($password),
                        'role' => 'investor',
                        'account_status' => 'active',
                        'email_verified_at' => now(),
                    ]);
                } else {
                    if ($user->role !== 'admin') {
                        $user->role = 'investor';
                        $user->save();
                    }
                }
            }

            // 2. Map tranche financials
            $trancheMap = [
                'A' => ['ghc' => 720000.00, 'usd' => 60000.00, 'equity' => 10.00],
                'B' => ['ghc' => 1440000.00, 'usd' => 120000.00, 'equity' => 14.00],
                'C' => ['ghc' => 2640000.00, 'usd' => 220000.00, 'equity' => 22.00],
            ];
            $financials = $trancheMap[$validated['selected_tranche']] ?? $trancheMap['B'];

            // 3. Resolve regulatory tier based on country
            $countryRule = InvestorComplianceService::getCountryRules($validated['country_code']);
            $regulatoryTier = $countryRule['regulatory_tier'] ?? 'ROW';

            // 4. Create InvestorProfile
            $investor = InvestorProfile::create([
                'user_id' => $user->id,
                'legal_name' => $validated['legal_name'],
                'email' => $email,
                'phone_number' => $validated['phone_number'],
                'country_residence' => $validated['country_residence'],
                'country_code' => strtoupper($validated['country_code']),
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'entity_type' => $validated['entity_type'],
                'tax_id_or_national_id' => $validated['tax_id_or_national_id'] ?? null,
                'regulatory_tier' => $regulatoryTier,
                'accreditation_details' => $validated['declarations'] ?? [],
                'selected_tranche' => $validated['selected_tranche'],
                'equity_percentage' => $financials['equity'],
                'capital_commitment_ghc' => $financials['ghc'],
                'capital_commitment_usd' => $financials['usd'],
                'remittance_method' => $validated['remittance_method'],
                'verification_status' => 'PENDING',
                'payment_unlocked' => false,
            ]);

            // 5. Store uploaded documents in private vault
            $docTypes = [
                'document_cpa_letter' => 'CPA_LETTER',
                'document_gov_id' => 'GOV_ID',
                'document_passport' => 'PASSPORT',
                'document_gh_card' => 'GH_CARD',
                'document_tax_tin' => 'TAX_TIN',
                'document_address_proof' => 'ADDRESS_PROOF',
                'document_corp_docs' => 'CORP_DOCS',
                'document_risk_ack' => 'ACCREDITED_RISK_ACK',
            ];

            foreach ($docTypes as $inputKey => $docType) {
                if ($request->hasFile($inputKey) && $request->file($inputKey)->isValid()) {
                    InvestorComplianceService::storeDocument($investor, $request->file($inputKey), $docType);
                }
            }

            // 6. Record immutable e-Signature audit log
            EsignatureLog::create([
                'investor_id' => $investor->id,
                'signer_name' => $validated['signer_name'],
                'agreement_version' => 'NDFG_OA_v3.1',
                'governance_clause_accepted' => true,
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'browser_user_agent' => $request->userAgent() ?? 'Unknown Browser',
                'signed_timestamp' => now(),
            ]);

            // 7. Audit log
            InvestorComplianceService::logAction(
                $investor->id,
                $user->id,
                'REGISTERED',
                "Investor onboarding profile created for {$investor->legal_name} under {$regulatoryTier} (Tranche {$investor->selected_tranche})",
                ['tranche' => $investor->selected_tranche, 'regulatory_tier' => $regulatoryTier]
            );

            // 8. Dispatch Email 1 (Post-Registration Verification Notice)
            InvestorEmailService::sendRegistrationNotice($investor);

            // 9. Send in-app user notification
            NotificationService::send(
                $user->id,
                'investor_registered',
                'Onboarding File Received',
                "Your investor verification file for Tranche {$investor->selected_tranche} has been submitted to the Compliance Desk.",
                null,
                '/investor/dashboard'
            );

            DB::commit();

            // 10. Authenticate session if not already logged in
            if (!Auth::check()) {
                Auth::login($user);
            }

            return redirect('/investor/dashboard')->with('success', '🎉 Welcome to the NDFG LLC Investor Portal! Your compliance documents are under official review.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Investor registration failed: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());

            return back()->withInput()->withErrors([
                'error' => 'An error occurred during submission: ' . $e->getMessage() . '. Please try again or contact investors@ridemycars.com.'
            ]);
        }
    }
}
