<?php

namespace App\Http\Controllers;

use App\Models\ComplianceVault;
use App\Models\InvestorProfile;
use App\Services\InvestorComplianceService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvestorDashboardController extends Controller
{
    /**
     * Display the Investor Portal Dashboard (gated based on verification status)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/investor/login');
        }

        // Retrieve investor profile for this user
        $investor = InvestorProfile::where('user_id', $user->id)
            ->orWhere('email', strtolower($user->email))
            ->with(['documents', 'esignatureLog', 'auditLogs', 'verifier'])
            ->first();

        // If no profile exists yet, redirect to onboarding wizard
        if (!$investor) {
            return redirect('/investor/register')->with('info', 'Please complete your investor accreditation profile to access the portal.');
        }

        // Link user_id if not already linked
        if (!$investor->user_id) {
            $investor->user_id = $user->id;
            $investor->save();
        }

        // Log dashboard access in audit trail
        InvestorComplianceService::logAction(
            $investor->id,
            $user->id,
            'LOGGED_IN',
            "Investor accessed portal dashboard (Status: {$investor->verification_status})"
        );

        $regulatoryTierName = match ($investor->regulatory_tier) {
            'US_REG_D' => 'US SEC Rule 506(c)',
            'UK_FCA' => 'UK FCA FPO Sophisticated/HNW',
            'GH_SEC' => 'Ghana SEC / Exempt Private Placement',
            'CA_NI_45_106' => 'Canada NI 45-106 Exemption',
            'EU_ESMA' => 'EU Prospectus Regulation Article 1(4)',
            'AFRICA_REGIONAL' => 'Regional Pan-African SEC Protocol',
            default => 'International Private Placement Framework',
        };

        // Escrow coordinates (managed by Ride My Cars (Ghana))
        $escrowDetails = [
            'swift_bank_name' => 'First National Bank / Standard Chartered Bank (Escrow Trust)',
            'swift_account_name' => 'Ride My Cars (Ghana) - Escrow Custody',
            'swift_account_usd' => 'USD-990823410294-RMC',
            'swift_iban' => 'GH29FNBG0000009908234102',
            'swift_code' => 'FIRNGHACXXX',
            'local_bank_ghc' => 'Ride My Cars (Ghana) (Ghana Operations Rail)',
            'local_account_number' => '1029384756102',
            'local_branch' => 'Airport City Branch, Accra',
            'momo_merchant_id' => 'RMC-GHANA-ESCROW',
            'momo_number' => '+233 24 000 8899 (MTN MoMo / Telecel Cash)',
            'stripe_merchant_name' => 'Ride My Cars LLC (Stripe Verified)',
            'stripe_support' => 'Visa, MasterCard, American Express, Apple Pay',
            'reference_code' => $investor->payment_reference_code,
        ];

        return view('investor.dashboard', compact('investor', 'regulatoryTierName', 'escrowDetails'));
    }

    /**
     * Securely download a vaulted compliance document
     */
    public function downloadDocument(int $id)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $doc = ComplianceVault::with('investor')->findOrFail($id);

        // Security Authorization Check: Only document owner or admin can download
        $isOwner = ($user->id === $doc->investor->user_id) || (strtolower($user->email) === strtolower($doc->investor->email));
        $isAdmin = ($user->role === 'admin') || (in_array(strtolower($user->email), ['admin@ridemycars.com', 'ridemycars1@gmail.com']));

        if (!$isOwner && !$isAdmin) {
            abort(403, 'Unauthorized access to confidential document vault.');
        }

        // Verify file exists on local storage
        if (!Storage::disk('local')->exists($doc->file_path)) {
            abort(404, 'The requested document file could not be located on secure storage.');
        }

        // Record download in audit log
        InvestorComplianceService::logAction(
            $doc->investor_id,
            $user->id,
            'DOC_DOWNLOADED',
            "Downloaded vaulted document: {$doc->document_title} ({$doc->original_filename})",
            ['vault_id' => $doc->id, 'mime_type' => $doc->mime_type]
        );

        $path = Storage::disk('local')->path($doc->file_path);
        return response()->download($path, $doc->original_filename, [
            'Content-Type' => $doc->mime_type ?: 'application/octet-stream',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, private',
        ]);
    }

    /**
     * Allow investor to upload additional documents requested by compliance officers
     */
    public function reuploadDocument(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/investor/login');
        }

        $investor = InvestorProfile::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'document_type' => 'required|string',
            'document_title' => 'nullable|string|max:255',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:12288',
            'reupload_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:12288',
        ]);

        $file = $request->file('document_file') ?? $request->file('reupload_file');
        if (!$file) {
            return redirect('/investor/dashboard')->withErrors(['document_file' => 'A valid document file is required.']);
        }

        $title = $request->input('document_title') ?? ucwords(str_replace('_', ' ', strtolower($request->input('document_type'))));

        $doc = InvestorComplianceService::storeDocument(
            $investor,
            $file,
            $request->input('document_type'),
            $title
        );

        // Update verification status back to UNDER_REVIEW
        if ($investor->verification_status === 'NEED_MORE_DOCS') {
            $investor->verification_status = 'UNDER_REVIEW';
            $investor->save();
        }

        return redirect('/investor/dashboard')->with('success', 'Document uploaded successfully! Compliance officers have been notified.');
    }

    /**
     * Download the executed NDFG LLC Operating Agreement Governance Certificate (v3.1)
     */
    public function downloadAgreement()
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $investor = InvestorProfile::where('user_id', $user->id)
            ->with('esignatureLog')
            ->firstOrFail();

        if (!$investor->isVerified()) {
            abort(403, 'The NDFG LLC Operating Agreement certificate is only available for download after positive compliance verification.');
        }

        $eLog = $investor->esignatureLog;
        $signDate = $eLog ? $eLog->signed_timestamp->format('F d, Y \a\t H:i:s T') : now()->toFormattedDateString();
        $ip = $eLog ? $eLog->ip_address : '127.0.0.1';
        $signer = $eLog ? $eLog->signer_name : $investor->legal_name;

        $content = "================================================================================\n"
            . "        RIDE MY CARS NEW DEVELOPMENT FINANCE GROUP (NDFG) LLC\n"
            . "           OPERATING AGREEMENT GOVERNANCE CERTIFICATE (v3.1)\n"
            . "================================================================================\n\n"
            . "PARTICIPANT / INVESTOR DETAILS:\n"
            . "Legal Entity Name:       {$investor->legal_name}\n"
            . "Primary Contact Email:   {$investor->email}\n"
            . "Jurisdiction / Country:  {$investor->country_residence} ({$investor->country_code})\n"
            . "Regulatory Routing Tier: {$investor->regulatory_tier}\n"
            . "Verification Status:     {$investor->verification_status}\n\n"
            . "TRANCHE & ALLOCATION PARAMETERS:\n"
            . "Tranche Selection:       Tranche {$investor->selected_tranche}\n"
            . "Equity Stake Allocation: {$investor->equity_percentage}%\n"
            . "Capital Commitment:      " . number_format((float) $investor->capital_commitment_ghc, 2) . " GHC ($" . number_format((float) $investor->capital_commitment_usd, 2) . " USD)\n"
            . "Remittance Protocol:     {$investor->remittance_method}\n"
            . "Escrow Custody Partner:  Ride My Cars (Ghana) (Republic of Ghana)\n"
            . "Payment Reference Code:  {$investor->payment_reference_code}\n\n"
            . "COHORT BASELINE SPECIFICATIONS:\n"
            . "Active Initial Cohort:   500 Drivers\n"
            . "Daily Revenue Baseline:  25,000 GHC across cohort (50 GHC / driver daily)\n"
            . "Net Operating Margin:    92% platform net margin\n"
            . "Cohort Term Duration:    3-Year Fixed Single Cohort\n\n"
            . "DIGITAL E-SIGNATURE AUDIT TRAIL:\n"
            . "Authorized Signer:       {$signer}\n"
            . "Agreement Version:       NDFG_OA_v3.1\n"
            . "Executed Timestamp:      {$signDate}\n"
            . "Signer IP Address:       {$ip}\n"
            . "Browser User Agent:      " . ($eLog ? $eLog->browser_user_agent : 'Browser Client') . "\n"
            . "Governance Clauses:      Fully Accepted & Digitally Logged\n\n"
            . "GOVERNANCE ACKNOWLEDGEMENT:\n"
            . "The participant and NDFG LLC covenant to abide by the terms set forth in the\n"
            . "Operating Agreement, including Year 3 structured buyout provisions and performance\n"
            . "clawback matrices. All capital commitments are held under institutional escrow\n"
            . "safeguards in coordination with Ride My Cars (Ghana).\n\n"
            . "Signed on behalf of NDFG LLC: Marilyn Watson, Investor Relations Manager\n"
            . "Cc: Ride My Cars (Ghana)\n"
            . "================================================================================\n";

        InvestorComplianceService::logAction(
            $investor->id,
            $user->id,
            'DOC_DOWNLOADED',
            "Downloaded executed Operating Agreement certificate (NDFG_OA_v3.1)"
        );

        $filename = 'NDFG_Operating_Agreement_' . Str::slug($investor->legal_name) . '_v3.1.txt';

        return Response::make($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store, private',
        ]);
    }
}
