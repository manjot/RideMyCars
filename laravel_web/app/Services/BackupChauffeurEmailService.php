<?php

namespace App\Services;

use App\Models\Ride;
use App\Models\User;
use App\Services\SettingService;
use App\Services\BackupChauffeurService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BackupChauffeurEmailService
{
    /**
     * Send automated email notification immediately after a customer confirms the assigned Backup Chauffeur.
     *
     * @param Ride $ride
     * @param User $driver
     * @param string|null $recipientEmail
     * @return array
     */
    public static function sendConfirmationEmail(Ride $ride, User $driver, ?string $recipientEmail = null): array
    {
        try {
            // Check if notifications are enabled
            $notificationsEnabled = (bool) BackupChauffeurService::getConfig('backup.notifications_enabled', true);
            if (!$notificationsEnabled) {
                Log::info("BackupChauffeurEmailService: Backup notifications globally disabled. Skipping email for Ride #{$ride->id}.");
                return ['success' => false, 'reason' => 'notifications_disabled'];
            }

            // Resolve recipient email
            $toEmail = trim(strtolower($recipientEmail ?: ($ride->rider?->email ?? '')));
            if (empty($toEmail)) {
                $ride->loadMissing('rider');
                $toEmail = trim(strtolower($ride->rider?->email ?? ''));
            }

            if (empty($toEmail)) {
                Log::info("BackupChauffeurEmailService: No valid recipient email found for Ride #{$ride->id}. Skipping email dispatch.");
                return ['success' => false, 'reason' => 'no_email'];
            }

            $fromEmail = SettingService::get('mail.from_address') ?: (config('mail.from.address') ?: env('MAIL_FROM_ADDRESS', 'support@ridemycars.com'));
            $fromName = SettingService::get('mail.from_name') ?: (config('mail.from.name') ?: env('MAIL_FROM_NAME', 'RideMyCars'));

            $customerName = $ride->rider?->name ?: ($ride->passenger_name ?: 'Valued Customer');
            $subject = "Backup Chauffeur Confirmed – Ride #{$ride->id} | RideMyCars";

            $htmlContent = self::buildHtmlTemplate($ride, $driver, $customerName);
            $textContent = self::buildPlainTextTemplate($ride, $driver, $customerName);

            $dispatched = false;

            // 1. Primary dispatch via Laravel Mailer
            try {
                Mail::send([], [], function ($message) use ($toEmail, $fromEmail, $fromName, $subject, $htmlContent, $textContent, $ride) {
                    $message->from($fromEmail, $fromName)
                            ->to($toEmail)
                            ->subject($subject)
                            ->html($htmlContent)
                            ->text($textContent);

                    $headers = $message->getHeaders();
                    $headers->addTextHeader('Auto-Submitted', 'auto-generated');
                    $headers->addTextHeader('X-Priority', '1');
                    $headers->addTextHeader('Importance', 'High');
                    $headers->addTextHeader('X-Mailer', 'RideMyCars-Chauffeur-Backup/2.0');
                    $headers->addTextHeader('X-Ride-ID', (string)$ride->id);
                    $headers->addTextHeader('X-Entity-Ref-ID', (string) uniqid('rmc_bk_', true));
                });

                Log::info("BackupChauffeurEmailService: Confirmation email for Ride #{$ride->id} successfully sent via Laravel Mailer to {$toEmail}");
                $dispatched = true;
            } catch (\Throwable $e) {
                Log::warning("BackupChauffeurEmailService: Laravel Mailer failed for Ride #{$ride->id} ({$toEmail}): " . $e->getMessage() . ". Attempting Direct SMTP Socket fallback...");
            }

            // 2. Direct Bluehost SMTP Socket fallback if Laravel Mailer failed
            if (!$dispatched) {
                $socketResult = self::sendDirectSocket($toEmail, $fromEmail, $fromName, $subject, $htmlContent, $textContent, $ride->id);
                if ($socketResult['success']) {
                    $dispatched = true;
                } else {
                    Log::error("BackupChauffeurEmailService: Direct SMTP Socket fallback failed for {$toEmail}: " . ($socketResult['error'] ?? 'Unknown error'));
                }
            }

            // 3. Optional sync to Roundcube / cPanel Webmail 'Sent' folder via IMAP
            self::appendSentMessage($toEmail, $fromEmail, $fromName, $subject, $htmlContent, $textContent);

            return [
                'success' => $dispatched,
                'email' => $toEmail,
                'message' => $dispatched ? "Backup chauffeur confirmation email sent to {$toEmail}" : "Failed to deliver email",
            ];
        } catch (\Throwable $e) {
            Log::error("BackupChauffeurEmailService exception for Ride #{$ride->id}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Direct Bluehost SMTP SSL Socket Dispatcher (Port 465)
     */
    protected static function sendDirectSocket(
        string $toEmail,
        string $fromEmail,
        string $fromName,
        string $subject,
        string $htmlContent,
        string $textContent,
        int $rideId
    ): array {
        $host = SettingService::get('mail.host') ?: (config('mail.mailers.smtp.host') ?: env('MAIL_HOST', 'mail.ridemycars.com'));
        $port = (int) (SettingService::get('mail.port') ?: (config('mail.mailers.smtp.port') ?: env('MAIL_PORT', 465)));
        $user = SettingService::get('mail.username') ?: (config('mail.mailers.smtp.username') ?: env('MAIL_USERNAME', 'support@ridemycars.com'));
        $pass = SettingService::get('mail.password') ?: (config('mail.mailers.smtp.password') ?: env('MAIL_PASSWORD', ''));

        if (empty($pass)) {
            return ['success' => false, 'error' => 'SMTP password not configured'];
        }

        try {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ]);

            $target = ($port === 465) ? "ssl://{$host}:{$port}" : "tcp://{$host}:{$port}";
            $fp = @stream_socket_client($target, $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
            if (!$fp) {
                return ['success' => false, 'error' => "Socket connect failed to {$target}: {$errstr} ({$errno})"];
            }

            stream_set_timeout($fp, 10);

            $read = function () use ($fp) {
                $data = '';
                while ($line = fgets($fp, 512)) {
                    $data .= $line;
                    if (isset($line[3]) && $line[3] === ' ') break;
                }
                return $data;
            };

            $read(); // Initial 220 banner

            fwrite($fp, "EHLO ridemycars.com\r\n");
            $read();

            fwrite($fp, "AUTH LOGIN\r\n");
            $read();

            fwrite($fp, base64_encode($user) . "\r\n");
            $read();

            fwrite($fp, base64_encode($pass) . "\r\n");
            $authResp = $read();

            if (strpos($authResp, '235') === false) {
                fclose($fp);
                return ['success' => false, 'error' => "SMTP Auth failed: {$authResp}"];
            }

            fwrite($fp, "MAIL FROM:<{$fromEmail}>\r\n");
            $read();

            fwrite($fp, "RCPT TO:<{$toEmail}>\r\n");
            $rcptResp = $read();

            if (strpos($rcptResp, '250') === false) {
                fclose($fp);
                return ['success' => false, 'error' => "Recipient {$toEmail} rejected: {$rcptResp}"];
            }

            fwrite($fp, "DATA\r\n");
            $read();

            $boundary = 'rmc_bk_' . md5(uniqid((string) time(), true));
            $msgId = '<' . md5(uniqid('rmc_bk_', true)) . '.' . time() . '@ridemycars.com>';

            $headers = [
                "From: {$fromName} <{$fromEmail}>",
                "Reply-To: <{$fromEmail}>",
                "To: <{$toEmail}>",
                "Subject: {$subject}",
                "Date: " . date('r'),
                "Message-ID: {$msgId}",
                "Auto-Submitted: auto-generated",
                "X-Priority: 1",
                "Importance: High",
                "X-Ride-ID: {$rideId}",
                "X-Entity-Ref-ID: " . uniqid('rmc_bk_', true),
                "MIME-Version: 1.0",
                "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
                "X-Mailer: RideMyCars-Chauffeur-Backup/2.0",
            ];

            $body = implode("\r\n", $headers) . "\r\n\r\n";
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: text/plain; charset=utf-8\r\n";
            $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $body .= $textContent . "\r\n\r\n";
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: text/html; charset=utf-8\r\n";
            $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $body .= $htmlContent . "\r\n\r\n";
            $body .= "--{$boundary}--\r\n.\r\n";

            fwrite($fp, $body);
            $dataResp = $read();

            fwrite($fp, "QUIT\r\n");
            fclose($fp);

            if (strpos($dataResp, '250') !== false) {
                Log::info("BackupChauffeurEmailService: Confirmation email for Ride #{$rideId} successfully sent via Direct SMTP to {$toEmail}");
                return ['success' => true];
            }

            return ['success' => false, 'error' => "Direct SMTP DATA response failed: {$dataResp}"];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => "Direct SMTP socket exception: " . $e->getMessage()];
        }
    }

    /**
     * Append Sent Backup Email to Roundcube / cPanel Webmail 'Sent' Folder via IMAP
     */
    protected static function appendSentMessage(
        string $toEmail,
        string $fromEmail,
        string $fromName,
        string $subject,
        string $htmlContent,
        string $textContent
    ): void {
        try {
            $host = SettingService::get('mail.host') ?: (config('mail.mailers.smtp.host') ?: env('MAIL_HOST', 'mail.ridemycars.com'));
            $user = SettingService::get('mail.username') ?: (config('mail.mailers.smtp.username') ?: env('MAIL_USERNAME', 'support@ridemycars.com'));
            $pass = SettingService::get('mail.password') ?: (config('mail.mailers.smtp.password') ?: env('MAIL_PASSWORD', ''));

            if (empty($pass)) return;

            $fp = @fsockopen('ssl://' . $host, 993, $errno, $errstr, 4);
            if (!$fp) return;

            fgets($fp, 512);
            fwrite($fp, "a001 LOGIN \"{$user}\" \"{$pass}\"\r\n");
            $resp = fgets($fp, 512);
            if (strpos($resp, 'OK') === false) {
                fclose($fp);
                return;
            }

            $boundary = 'rmc_sent_bk_' . md5(uniqid((string) time(), true));
            $date = date('r');
            $msgId = '<' . md5(uniqid('rmc_sent_bk_', true)) . '@ridemycars.com>';

            $headers = [
                "From: {$fromName} <{$fromEmail}>",
                "To: <{$toEmail}>",
                "Subject: {$subject}",
                "Date: {$date}",
                "Message-ID: {$msgId}",
                "MIME-Version: 1.0",
                "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
                "X-Mailer: RideMyCars-Chauffeur-Backup/2.0",
            ];

            $raw = implode("\r\n", $headers) . "\r\n\r\n";
            $raw .= "--{$boundary}\r\n";
            $raw .= "Content-Type: text/plain; charset=utf-8\r\n\r\n";
            $raw .= $textContent . "\r\n\r\n";
            $raw .= "--{$boundary}\r\n";
            $raw .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
            $raw .= $htmlContent . "\r\n\r\n";
            $raw .= "--{$boundary}--\r\n";

            $len = strlen($raw);
            fwrite($fp, "a002 APPEND \"INBOX.Sent\" (\\Seen) {{$len}}\r\n");
            $appendPrompt = fgets($fp, 512);
            if (strpos($appendPrompt, '+') !== false) {
                fwrite($fp, $raw . "\r\n");
                fgets($fp, 512);
            }
            fwrite($fp, "a003 LOGOUT\r\n");
            fclose($fp);
        } catch (\Throwable $e) {
            Log::warning("BackupChauffeurEmailService: IMAP Sent append notice: " . $e->getMessage());
        }
    }

    /**
     * Build responsive branded HTML email template
     */
    public static function buildHtmlTemplate(Ride $ride, User $driver, string $customerName): string
    {
        $year = date('Y');
        $rideId = $ride->id;
        $trackingUrl = url("/ride/track/{$rideId}");

        $dp = $driver->driverProfile;
        $driverName = htmlspecialchars($driver->name ?? 'Nearby Professional Chauffeur');
        $driverPhone = htmlspecialchars($driver->phone ?? 'Available via app');
        $driverRating = $dp?->rating ? number_format($dp->rating, 1) : '4.9';
        $vehicleMake = $dp?->vehicle_make ?? '';
        $vehicleModel = $dp?->vehicle_model ?? ($ride->vehicle_type ?? 'Executive Vehicle');
        $vehicle = htmlspecialchars(trim("{$vehicleMake} {$vehicleModel}") ?: ($ride->vehicle_type ?? 'Executive Sedan'));
        $plate = htmlspecialchars($dp?->license_number ?? ($dp?->vehicle_plate ?? 'REG-8899'));

        $pickup = htmlspecialchars($ride->pickup_location ?? 'Pickup Location');
        $dropoff = htmlspecialchars($ride->dropoff_location ?? 'Destination');

        $currency = $ride->currency ?? ($ride->country === 'GHA' ? 'GH₵' : '$');
        $fare = number_format((float)($ride->fare ?: ($ride->total_amount ?: 28.50)), 2);
        $fareFormatted = "{$currency}{$fare}";
        $paymentMethod = htmlspecialchars(ucfirst($ride->payment_method ?? 'Card'));
        $receiptCode = htmlspecialchars($ride->digital_receipt_code ?: "REC-{$rideId}");
        $confirmedAt = now()->format('M d, Y • h:i A');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Backup Chauffeur Confirmed — RideMyCars</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0b0f17; margin: 0; padding: 24px 12px; color: #1e293b;">
    <div style="max-width: 580px; margin: 0 auto; background-color: #ffffff; border-radius: 24px; overflow: hidden; border: 1px solid rgba(245, 158, 11, 0.3); box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.35);">
        
        <!-- Header Banner -->
        <div style="background: linear-gradient(135deg, #0b0f17 0%, #1a2234 100%); padding: 32px 28px; text-align: center; border-bottom: 2px solid #f59e0b;">
            <h1 style="margin: 0; font-size: 28px; font-weight: 900; letter-spacing: -0.5px; color: #f59e0b;">
                Ride<span style="color: #ffffff;">My</span>Cars
            </h1>
            <p style="margin: 6px 0 0 0; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; color: #94a3b8;">
                Executive Mobility & Logistics
            </p>
        </div>

        <!-- Hero Status Notification -->
        <div style="padding: 32px 28px 24px 28px;">
            <!-- Badge -->
            <div style="text-align: center; margin-bottom: 18px;">
                <span style="display: inline-block; background-color: #fef3c7; color: #b45309; border: 1px solid #fcd34d; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; padding: 6px 14px; rounded: 9999px; border-radius: 9999px;">
                    🛡️ Proximity Backup Chauffeur Confirmed
                </span>
            </div>

            <h2 style="margin: 0 0 10px 0; font-size: 22px; font-weight: 900; color: #0f172a; text-align: center; line-height: 1.3;">
                Your Backup Chauffeur is Assigned!
            </h2>
            <p style="margin: 0 0 24px 0; font-size: 14px; line-height: 1.6; color: #475569; text-align: center;">
                Hello <strong>{$customerName}</strong>, your primary chauffeur was unavailable. Thanks to your <strong>Backup Chauffeur</strong> preference, we have seamlessly connected you with a top-rated nearby professional chauffeur who has accepted your trip.
            </p>

            <!-- Assigned Driver Profile Box -->
            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 18px; padding: 20px; margin-bottom: 24px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 54px; vertical-align: top;">
                            <div style="width: 48px; height: 48px; background-color: #f59e0b; border-radius: 50%; text-align: center; line-height: 48px; font-size: 24px; color: #ffffff;">
                                👨‍✈️
                            </div>
                        </td>
                        <td style="vertical-align: middle; padding-left: 14px;">
                            <div style="font-size: 16px; font-weight: 900; color: #0f172a;">
                                {$driverName}
                            </div>
                            <div style="font-size: 13px; color: #64748b; font-weight: 600; margin-top: 2px;">
                                {$vehicle} • <span style="color: #d97706; font-weight: 700;">Plate: {$plate}</span>
                            </div>
                            <div style="font-size: 12px; color: #059669; font-weight: 800; margin-top: 4px;">
                                ⭐ {$driverRating} Rating • Verified Executive Chauffeur
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Route & Booking Summary Table -->
            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; overflow: hidden; margin-bottom: 26px;">
                <div style="background-color: #f1f5f9; padding: 12px 18px; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; color: #475569;">
                    Trip Details • Ride #{$rideId}
                </div>
                <div style="padding: 18px;">
                    <!-- Pickup -->
                    <div style="margin-bottom: 14px;">
                        <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #059669; letter-spacing: 0.5px; display: block; margin-bottom: 2px;">
                            ● Pickup Location
                        </span>
                        <span style="font-size: 13px; font-weight: 700; color: #0f172a; line-height: 1.4;">
                            {$pickup}
                        </span>
                    </div>

                    <!-- Dropoff -->
                    <div style="margin-bottom: 18px;">
                        <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #e11d48; letter-spacing: 0.5px; display: block; margin-bottom: 2px;">
                            ■ Destination Drop-off
                        </span>
                        <span style="font-size: 13px; font-weight: 700; color: #0f172a; line-height: 1.4;">
                            {$dropoff}
                        </span>
                    </div>

                    <div style="border-top: 1px dashed #cbd5e1; padding-top: 14px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                            <tr>
                                <td style="color: #64748b; padding: 4px 0;">Total Trip Fare:</td>
                                <td style="text-align: right; font-weight: 900; color: #059669; font-size: 15px; padding: 4px 0;">{$fareFormatted}</td>
                            </tr>
                            <tr>
                                <td style="color: #64748b; padding: 4px 0;">Payment Method:</td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a; padding: 4px 0;">{$paymentMethod}</td>
                            </tr>
                            <tr>
                                <td style="color: #64748b; padding: 4px 0;">Confirmation Time:</td>
                                <td style="text-align: right; font-weight: 600; color: #64748b; padding: 4px 0;">{$confirmedAt}</td>
                            </tr>
                            <tr>
                                <td style="color: #64748b; padding: 4px 0;">Digital Receipt Code:</td>
                                <td style="text-align: right; font-family: monospace; font-weight: 700; color: #334155; padding: 4px 0;">{$receiptCode}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Primary Action Call to Action Button -->
            <div style="text-align: center; margin-bottom: 26px;">
                <a href="{$trackingUrl}" target="_blank" style="display: inline-block; width: 85%; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 900; padding: 16px 24px; border-radius: 14px; box-shadow: 0 10px 20px -5px rgba(245, 158, 11, 0.4); text-align: center;">
                    📍 Track Chauffeur Live on GPS →
                </a>
                <p style="margin: 10px 0 0 0; font-size: 11px; color: #94a3b8;">
                    No login required. Follow driver arrival and ETA in real time.
                </p>
            </div>

            <!-- Reassurance Note -->
            <div style="background-color: #f8fafc; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 0 12px 12px 0;">
                <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #475569;">
                    🔒 <strong>RideMyCars Quality Guarantee:</strong> Every proximity backup chauffeur passes our standard background check and vehicle quality inspection to ensure safety, comfort, and punctuality.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8fafc; padding: 22px 28px; border-top: 1px solid #f1f5f9; text-align: center; font-size: 11px; color: #94a3b8; line-height: 1.6;">
            &copy; {$year} RideMyCars Inc. All rights reserved.<br>
            4301 Saddle River Drive, Bowie, MD 20720, USA<br>
            Need help? Contact our 24/7 executive desk at <a href="mailto:support@ridemycars.com" style="color: #d97706; text-decoration: none; font-weight: 600;">support@ridemycars.com</a>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Build Plaintext Fallback Email
     */
    public static function buildPlainTextTemplate(Ride $ride, User $driver, string $customerName): string
    {
        $rideId = $ride->id;
        $trackingUrl = url("/ride/track/{$rideId}");
        $dp = $driver->driverProfile;
        $driverName = $driver->name ?? 'Nearby Professional Chauffeur';
        $driverPhone = $driver->phone ?? 'Available via app';
        $vehicle = trim(($dp?->vehicle_make ?? '') . ' ' . ($dp?->vehicle_model ?? ($ride->vehicle_type ?? 'Executive Vehicle')));
        $plate = $dp?->license_number ?? ($dp?->vehicle_plate ?? 'N/A');
        $pickup = $ride->pickup_location ?? 'Pickup Location';
        $dropoff = $ride->dropoff_location ?? 'Destination';
        $currency = $ride->currency ?? ($ride->country === 'GHA' ? 'GH₵' : '$');
        $fare = number_format((float)($ride->fare ?: ($ride->total_amount ?: 28.50)), 2);
        $fareFormatted = "{$currency}{$fare}";

        return <<<TEXT
RideMyCars — Backup Chauffeur Confirmed
=====================================================

Hello {$customerName},

Your primary chauffeur was unavailable. Your assigned Backup Chauffeur has been confirmed and is now preparing to pick you up.

ASSIGNED CHAUFFEUR:
-------------------
Chauffeur: {$driverName}
Vehicle:   {$vehicle} (Plate: {$plate})
Contact:   {$driverPhone}

TRIP DETAILS:
-------------
Ride ID:      #{$rideId}
Pickup:       {$pickup}
Destination:  {$dropoff}
Estimated:    {$currency}{$fare} ({$ride->payment_method})

TRACK LIVE ON GPS:
------------------
{$trackingUrl}

Thank you for choosing RideMyCars Executive Mobility.
Need assistance? Contact us at support@ridemycars.com
TEXT;
    }
}
