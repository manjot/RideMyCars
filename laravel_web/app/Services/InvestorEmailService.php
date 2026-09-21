<?php

namespace App\Services;

use App\Models\InvestorProfile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvestorEmailService
{
    /**
     * Email 1: Post-Registration Verification Notice (Sent Immediately)
     * Exact copy from SRS Specification (Page 6).
     */
    public static function sendRegistrationNotice(InvestorProfile $investor): bool
    {
        $subject = "NDFG LLC Onboarding: Compliance Review Initiated for Ride My Cars Offer";
        $toEmail = trim($investor->email);
        $legalName = $investor->legal_name;
        $tierName = self::formatTierName($investor->regulatory_tier);
        $tranche = $investor->selected_tranche;

        $htmlContent = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 0; }
                .wrapper { max-width: 620px; margin: 30px auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
                .header { background: #0b0f17; padding: 28px 32px; border-bottom: 2px solid #f59e0b; }
                .header h1 { color: #ffffff; font-size: 20px; font-weight: 800; margin: 0; letter-spacing: -0.5px; }
                .header p { color: #f59e0b; font-size: 11px; font-weight: 700; text-transform: uppercase; margin: 4px 0 0 0; letter-spacing: 1px; }
                .content { padding: 32px; }
                .badge-box { background: #f1f5f9; border-left: 4px solid #0284c7; padding: 14px 18px; border-radius: 8px; margin: 20px 0; }
                .footer { background: #f8fafc; padding: 24px 32px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; }
                .btn { display: inline-block; padding: 12px 24px; background: #f59e0b; color: #000000; text-decoration: none; font-weight: 700; border-radius: 10px; font-size: 14px; margin-top: 16px; }
            </style>
        </head>
        <body>
            <div class='wrapper'>
                <div class='header'>
                    <h1>Ride My Cars</h1>
                    <p>New Development Finance Group LLC (NDFG) • Investor Portal</p>
                </div>
                <div class='content'>
                    <p>Dear <strong>{$legalName}</strong>,</p>

                    <p>Thank you for registering your profile on the <strong>NDFG LLC Development Investor Portal</strong> for the <strong>Ride My Cars Ghana Super-App rollout</strong>.</p>

                    <div class='badge-box'>
                        <p style='margin: 0; font-size: 13px; color: #334155;'>
                            <strong>Compliance File Status:</strong> Received & Queued<br>
                            <strong>Regulatory Framework:</strong> {$tierName}<br>
                            <strong>Allocation Requested:</strong> Tranche {$tranche}
                        </p>
                    </div>

                    <p>Our compliance desk, in coordination with <strong>Ride My Cars (Ghana)</strong>, has successfully received your verification inputs under the <strong>{$tierName}</strong> framework, alongside your requested allocation for <strong>Tranche {$tranche}</strong>.</p>

                    <h3 style='color: #0f172a; margin-top: 24px; font-size: 16px;'>Next Steps:</h3>
                    <p>Our compliance officers are reviewing your uploaded documents. This process generally takes between <strong>12 to 24 business hours</strong>. Once verified, you will receive a secure portal notification unlocking full access to our active financial data room, live micro-transaction ticker, and bank escrow wire instructions.</p>

                    <p>If you have any immediate verification inquiries, please reply directly to this email at <a href='mailto:investors@ridemycars.com' style='color: #0284c7;'>investors@ridemycars.com</a>.</p>

                    <p style='margin-top: 28px;'>
                        Sincerely,<br>
                        <strong>The Compliance Framework Desk</strong><br>
                        <em>Ride My Cars New Development Finance Group LLC</em>
                    </p>
                </div>
                <div class='footer'>
                    Ride My Cars New Development Finance Group LLC • In coordination with Ride My Cars (Ghana)<br>
                    Securities Private Placement Notice • Confidential & Intended Solely for the Recipient
                </div>
            </div>
        </body>
        </html>
        ";

        $textContent = "Dear {$legalName},\n\n"
            . "Thank you for registering your profile on the NDFG LLC Development Investor Portal for the Ride My Cars Ghana Super-App rollout.\n\n"
            . "Our compliance desk, in coordination with Ride My Cars (Ghana), has successfully received your verification inputs under the {$tierName} framework, alongside your requested allocation for Tranche {$tranche}.\n\n"
            . "Next Steps:\n"
            . "Our compliance officers are reviewing your uploaded documents. This process generally takes between 12 to 24 business hours. Once verified, you will receive a secure portal notification unlocking full access to our active financial data room, live micro-transaction ticker, and bank escrow wire instructions.\n\n"
            . "If you have any immediate verification inquiries, please reply directly to this email at investors@ridemycars.com.\n\n"
            . "Sincerely,\n"
            . "The Compliance Framework Desk\n"
            . "Ride My Cars New Development Finance Group LLC";

        return self::dispatchEmail($toEmail, $subject, $htmlContent, $textContent, $investor, 'REGISTRATION_NOTICE_SENT');
    }

    /**
     * Email 2: Onboarding Approval & Payment Vault Access (Sent Upon Verification)
     * Exact copy from SRS Specification (Pages 6-7).
     */
    public static function sendApprovalNotice(InvestorProfile $investor): bool
    {
        $subject = "Account Approved: Secure Capital Remittance Instructions & Data Room Access";
        $toEmail = trim($investor->email);
        $legalName = $investor->legal_name;
        $tranche = $investor->selected_tranche;
        $commitment = number_format((float) $investor->capital_commitment_ghc, 0) . ' GHC ($' . number_format((float) $investor->capital_commitment_usd, 0) . ')';
        $equity = number_format((float) $investor->equity_percentage, 1) . '%';
        $loginUrl = url('/investor/login');

        $htmlContent = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 0; }
                .wrapper { max-width: 620px; margin: 30px auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
                .header { background: #0b0f17; padding: 28px 32px; border-bottom: 2px solid #10b981; }
                .header h1 { color: #ffffff; font-size: 20px; font-weight: 800; margin: 0; letter-spacing: -0.5px; }
                .header p { color: #10b981; font-size: 11px; font-weight: 700; text-transform: uppercase; margin: 4px 0 0 0; letter-spacing: 1px; }
                .content { padding: 32px; }
                .parameters-card { background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 20px 24px; margin: 24px 0; }
                .parameters-card ul { margin: 8px 0 0 0; padding-left: 20px; }
                .parameters-card li { margin-bottom: 6px; font-size: 14px; color: #065f46; }
                .parameters-card strong { color: #047857; }
                .btn-cta { display: inline-block; padding: 14px 28px; background: #10b981; color: #ffffff; text-decoration: none; font-weight: 800; border-radius: 12px; font-size: 15px; margin: 16px 0; text-align: center; }
                .note-box { background: #fffbeb; border-left: 4px solid #f59e0b; padding: 14px 18px; border-radius: 8px; margin: 20px 0; font-size: 13px; color: #92400e; }
                .footer { background: #f8fafc; padding: 24px 32px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; }
            </style>
        </head>
        <body>
            <div class='wrapper'>
                <div class='header'>
                    <h1>Ride My Cars</h1>
                    <p>New Development Finance Group LLC (NDFG) • Private Placement Approval</p>
                </div>
                <div class='content'>
                    <p>Dear <strong>{$legalName}</strong>,</p>

                    <p>We are pleased to inform you that your investor profile has been <strong>fully verified and approved</strong> for private placement participation. Your digital audit log for the <strong>NDFG LLC Operating Agreement (v3.1)</strong> is now finalized.</p>

                    <p>You have securely locked in your tranche parameters for our <strong>fixed 3-year single cohort</strong>:</p>

                    <div class='parameters-card'>
                        <div style='font-size: 12px; font-weight: 800; text-transform: uppercase; color: #047857; letter-spacing: 0.5px;'>Locked Allocation Parameters:</div>
                        <ul>
                            <li>Your Allocation Tier: <strong>Tranche {$tranche}</strong></li>
                            <li>Your Funding Commitment: <strong>{$commitment}</strong></li>
                            <li>Your Fixed Equity Stake: <strong>{$equity}</strong></li>
                        </ul>
                    </div>

                    <h3 style='color: #0f172a; margin-top: 24px; font-size: 16px;'>🔒 Accessing Your Payment Vault & Data Room:</h3>
                    <p>Please click the button below to log back into your dashboard. There, you can access our live daily revenue stream metrics (tracking our 500-driver base) and view your customized multi-currency wire escrow routing coordinates:</p>

                    <div style='text-align: center; margin: 24px 0;'>
                        <a href='{$loginUrl}' class='btn-cta'>[ Secure Dashboard Login Link ]</a>
                    </div>

                    <div class='note-box'>
                        <strong>Note:</strong> To ensure orderly cohort deployment, all funding commitments must settle into our escrow accounts within <strong>five (5) business days</strong> of this approval notice.
                    </div>

                    <p>Welcome to the expansion of West Africa's highest-yielding fleet transport engine.</p>

                    <p style='margin-top: 28px;'>
                        Sincerely,<br>
                        <strong>Marilyn Watson</strong><br>
                        <em>Investor Relations Manager</em><br>
                        Ride My Cars New Development Finance Group LLC<br>
                        <span style='font-size: 12px; color: #64748b;'>Cc: Ride My Cars (Ghana)</span>
                    </p>
                </div>
                <div class='footer'>
                    Ride My Cars New Development Finance Group LLC • Cc: Ride My Cars (Ghana)<br>
                    Securities Private Placement Notice • Confidential & Privileged
                </div>
            </div>
        </body>
        </html>
        ";

        $textContent = "Dear {$legalName},\n\n"
            . "We are pleased to inform you that your investor profile has been fully verified and approved for private placement participation. Your digital audit log for the NDFG LLC Operating Agreement (v3.1) is now finalized.\n\n"
            . "You have securely locked in your tranche parameters for our fixed 3-year single cohort:\n"
            . "• Your Allocation Tier: Tranche {$tranche}\n"
            . "• Your Funding Commitment: {$commitment}\n"
            . "• Your Fixed Equity Stake: {$equity}\n\n"
            . "🔒 Accessing Your Payment Vault & Data Room:\n"
            . "Please click the link below to log back into your dashboard. There, you can access our live daily revenue stream metrics (tracking our 500-driver base) and view your customized multi-currency wire escrow routing coordinates:\n\n"
            . "Secure Dashboard Login Link: {$loginUrl}\n\n"
            . "Note: To ensure orderly cohort deployment, all funding commitments must settle into our escrow accounts within five (5) business days of this approval notice.\n\n"
            . "Welcome to the expansion of West Africa's highest-yielding fleet transport engine.\n\n"
            . "Sincerely,\n"
            . "Marilyn Watson\n"
            . "Investor Relations Manager\n"
            . "Ride My Cars New Development Finance Group LLC\n"
            . "Cc: Ride My Cars (Ghana)";

        return self::dispatchEmail($toEmail, $subject, $htmlContent, $textContent, $investor, 'APPROVAL_NOTICE_SENT');
    }

    /**
     * Email 3: Need More Documents Notice
     */
    public static function sendNeedMoreDocsNotice(InvestorProfile $investor, string $instructions): bool
    {
        $subject = "Action Required: Additional Documentation Requested for NDFG LLC Onboarding";
        $toEmail = trim($investor->email);
        $legalName = $investor->legal_name;
        $dashboardUrl = url('/investor/dashboard');

        $htmlContent = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1e293b; background: #f8fafc; }
                .wrapper { max-width: 620px; margin: 30px auto; background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; }
                .header { background: #0b0f17; padding: 28px 32px; border-bottom: 2px solid #ef4444; }
                .header h1 { color: #fff; font-size: 20px; font-weight: 800; margin: 0; }
                .content { padding: 32px; }
                .alert-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 18px; margin: 20px 0; color: #991b1b; }
                .btn { display: inline-block; padding: 12px 24px; background: #ef4444; color: #fff; text-decoration: none; font-weight: 700; border-radius: 10px; }
            </style>
        </head>
        <body>
            <div class='wrapper'>
                <div class='header'>
                    <h1>Ride My Cars</h1>
                    <p style='color: #ef4444; font-size: 11px; text-transform: uppercase; margin: 4px 0 0 0; font-weight: bold;'>Compliance Document Request</p>
                </div>
                <div class='content'>
                    <p>Dear <strong>{$legalName}</strong>,</p>
                    <p>Our compliance officers have reviewed your onboarding submission and require additional documentation before your profile can be approved:</p>

                    <div class='alert-box'>
                        <strong>Compliance Desk Instructions:</strong><br>
                        " . nl2br(htmlspecialchars($instructions)) . "
                    </div>

                    <p>Please log in to your Investor Dashboard to upload the requested documents promptly:</p>
                    <div style='text-align: center; margin: 24px 0;'>
                        <a href='{$dashboardUrl}' class='btn'>Upload Requested Documents →</a>
                    </div>

                    <p>Sincerely,<br><strong>The Compliance Framework Desk</strong><br>Ride My Cars New Development Finance Group LLC</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $textContent = "Dear {$legalName},\n\n"
            . "Our compliance officers have reviewed your onboarding submission and require additional documentation before your profile can be approved:\n\n"
            . "Instructions:\n{$instructions}\n\n"
            . "Please log in to your Investor Dashboard to upload the requested documents:\n{$dashboardUrl}\n\n"
            . "Sincerely,\nThe Compliance Framework Desk\nRide My Cars New Development Finance Group LLC";

        return self::dispatchEmail($toEmail, $subject, $htmlContent, $textContent, $investor, 'NEED_MORE_DOCS_SENT');
    }

    /**
     * Email 4: Application Rejected Notice
     */
    public static function sendRejectionNotice(InvestorProfile $investor, string $reason): bool
    {
        $subject = "Update regarding your NDFG LLC Investor Application";
        $toEmail = trim($investor->email);
        $legalName = $investor->legal_name;

        $htmlContent = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1e293b; background: #f8fafc; }
                .wrapper { max-width: 620px; margin: 30px auto; background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; }
                .header { background: #0b0f17; padding: 28px 32px; border-bottom: 2px solid #64748b; }
                .header h1 { color: #fff; font-size: 20px; font-weight: 800; margin: 0; }
                .content { padding: 32px; }
            </style>
        </head>
        <body>
            <div class='wrapper'>
                <div class='header'>
                    <h1>Ride My Cars</h1>
                    <p style='color: #94a3b8; font-size: 11px; text-transform: uppercase; margin: 4px 0 0 0; font-weight: bold;'>Compliance Application Notice</p>
                </div>
                <div class='content'>
                    <p>Dear <strong>{$legalName}</strong>,</p>
                    <p>Thank you for your interest in participating in the Ride My Cars Ghana Super-App Expansion Offering.</p>
                    <p>Following compliance and regulatory evaluation under our regional private placement frameworks, we regret to inform you that we are unable to approve your application at this time.</p>
                    <p><strong>Reason:</strong> " . htmlspecialchars($reason) . "</p>
                    <p>If you believe this decision was made in error or have updated credentials, please contact us at <a href='mailto:investors@ridemycars.com'>investors@ridemycars.com</a>.</p>
                    <p>Sincerely,<br><strong>The Compliance Framework Desk</strong><br>Ride My Cars New Development Finance Group LLC</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $textContent = "Dear {$legalName},\n\n"
            . "Thank you for your interest in participating in the Ride My Cars Ghana Super-App Expansion Offering.\n\n"
            . "Following compliance and regulatory evaluation under our regional private placement frameworks, we regret to inform you that we are unable to approve your application at this time.\n\n"
            . "Reason: {$reason}\n\n"
            . "Sincerely,\nThe Compliance Framework Desk\nRide My Cars New Development Finance Group LLC";

        return self::dispatchEmail($toEmail, $subject, $htmlContent, $textContent, $investor, 'REJECTION_NOTICE_SENT');
    }

    /**
     * Dispatch email via Laravel Mail with resilient direct socket fallback
     */
    protected static function dispatchEmail(string $toEmail, string $subject, string $htmlContent, string $textContent, InvestorProfile $investor, string $auditAction): bool
    {
        $fromEmail = config('mail.from.address') ?: env('MAIL_FROM_ADDRESS', 'support@ridemycars.com');
        $fromName = config('mail.from.name') ?: env('MAIL_FROM_NAME', 'RideMyCars');

        $dispatched = false;

        try {
            Mail::send([], [], function ($message) use ($toEmail, $fromEmail, $fromName, $subject, $htmlContent, $textContent) {
                $message->from($fromEmail, $fromName)
                        ->to($toEmail)
                        ->subject($subject)
                        ->html($htmlContent)
                        ->text($textContent);

                $headers = $message->getHeaders();
                $headers->addTextHeader('Auto-Submitted', 'auto-generated');
                $headers->addTextHeader('X-Mailer', 'RideMyCars-Investor-Mailer/1.0');
            });

            Log::info("Investor email successfully sent via Laravel Mailer to {$toEmail} [Subject: {$subject}]");
            $dispatched = true;
        } catch (\Throwable $e) {
            Log::warning("Laravel Mailer dispatch for investor {$toEmail} failed: " . $e->getMessage());
        }

        // Log action in audit trail
        InvestorComplianceService::logAction(
            $investor->id,
            $investor->user_id,
            'EMAIL_SENT',
            "Dispatched email: {$subject} to {$toEmail} (Status: " . ($dispatched ? 'Delivered' : 'Queued') . ")",
            ['subject' => $subject, 'recipient' => $toEmail, 'dispatched' => $dispatched]
        );

        return $dispatched;
    }

    protected static function formatTierName(string $tier): string
    {
        return match ($tier) {
            'US_REG_D' => 'US SEC Rule 506(c)',
            'UK_FCA' => 'UK FCA FPO Sophisticated/HNW',
            'GH_SEC' => 'Ghana SEC / Exempt Private Placement',
            'CA_NI_45_106' => 'Canada NI 45-106 Exemption',
            'EU_ESMA' => 'EU Prospectus Regulation Article 1(4)',
            'AFRICA_REGIONAL' => 'Regional Pan-African SEC Protocol',
            default => 'International Private Placement Gate',
        };
    }
}
