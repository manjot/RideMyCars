<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailOtpService
{
    /**
     * Send OTP Verification Code via Email
     *
     * @param string $toEmail
     * @param string $otp
     * @return array
     */
    public function sendOtp(string $toEmail, string $otp): array
    {
        $toEmail = trim(strtolower($toEmail));
        $fromEmail = config('mail.from.address') ?: env('MAIL_FROM_ADDRESS', 'support@ridemycars.com');
        $fromName = config('mail.from.name') ?: env('MAIL_FROM_NAME', 'RideMyCars');
        $timeStr = date('g:i A');
        $subject = "Your RideMyCars Security Code: {$otp} ({$timeStr})";

        $htmlContent = $this->buildHtmlTemplate($otp);
        $textContent = "{$otp} is your RideMyCars verification code. This code is valid for 5 minutes. Do not share this code with anyone. For assistance, visit https://ridemycars.com";

        $dispatched = false;

        // 1. Attempt delivery via Laravel Mail (Symfony Mailer)
        try {
            Mail::send([], [], function ($message) use ($toEmail, $fromEmail, $fromName, $subject, $htmlContent, $textContent) {
                $message->from($fromEmail, $fromName)
                        ->to($toEmail)
                        ->subject($subject)
                        ->html($htmlContent)
                        ->text($textContent);

                $headers = $message->getHeaders();
                $headers->addTextHeader('Auto-Submitted', 'auto-generated');
                $headers->addTextHeader('X-Priority', '1');
                $headers->addTextHeader('Importance', 'High');
                $headers->addTextHeader('X-Mailer', 'RideMyCars-Mailer/2.0');
                $headers->addTextHeader('X-Entity-Ref-ID', (string) uniqid('rmc_', true));
            });

            Log::info("Email OTP {$otp} successfully dispatched via Laravel Mailer to {$toEmail}");
            $dispatched = true;
        } catch (\Throwable $e) {
            Log::warning("Laravel Mailer dispatch to {$toEmail} failed: " . $e->getMessage() . ". Attempting Direct Bluehost SMTP Socket fallback...");
        }

        // 2. Resilient Direct SMTP Socket Fallback if Laravel Mailer failed
        if (!$dispatched) {
            $socketResult = $this->sendDirectSocket($toEmail, $otp, $fromEmail, $fromName, $subject, $htmlContent, $textContent);
            if ($socketResult['success']) {
                $dispatched = true;
            } else {
                Log::error("Direct SMTP Socket failed for {$toEmail}: " . ($socketResult['error'] ?? 'Unknown error'));
            }
        }

        // 3. Sync to Roundcube Webmail 'Sent' folder via IMAP
        $this->appendSentMessage($toEmail, $otp, $fromEmail, $fromName, $subject, $htmlContent, $textContent);

        if ($dispatched) {
            return [
                'success' => true,
                'message' => "Verification code sent to {$toEmail}",
            ];
        }

        return [
            'success' => false,
            'error' => "Unable to send verification code. Please check your email or contact support.",
        ];
    }

    /**
     * Direct Bluehost SMTP SSL Socket Dispatcher
     */
    protected function sendDirectSocket(
        string $toEmail,
        string $otp,
        string $fromEmail,
        string $fromName,
        string $subject,
        string $htmlContent,
        string $textContent
    ): array {
        $host = \App\Services\SettingService::get('mail.host') ?: (config('mail.mailers.smtp.host') ?: env('MAIL_HOST', 'mail.ridemycars.com'));
        $port = (int) (\App\Services\SettingService::get('mail.port') ?: (config('mail.mailers.smtp.port') ?: env('MAIL_PORT', 465)));
        $user = \App\Services\SettingService::get('mail.username') ?: (config('mail.mailers.smtp.username') ?: env('MAIL_USERNAME', 'support@ridemycars.com'));
        $pass = \App\Services\SettingService::get('mail.password') ?: (config('mail.mailers.smtp.password') ?: env('MAIL_PASSWORD', ''));

        try {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ]
            ]);

            $target = ($port === 465) ? "ssl://{$host}:{$port}" : "tcp://{$host}:{$port}";
            $fp = @stream_socket_client($target, $errno, $errstr, 12, STREAM_CLIENT_CONNECT, $context);

            if (!$fp) {
                Log::error("Direct SMTP Socket connect error to {$target}: {$errstr} ({$errno})");
                return [
                    'success' => false,
                    'error' => "Unable to connect to email gateway ({$errstr})",
                ];
            }

            $read = function () use ($fp) {
                $buffer = '';
                while ($line = fgets($fp, 512)) {
                    $buffer .= $line;
                    if (substr($line, 3, 1) === ' ') break;
                }
                return $buffer;
            };

            $read(); // Server Banner

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
                Log::error("Direct SMTP Auth failed: {$authResp}");
                return [
                    'success' => false,
                    'error' => "SMTP Authentication failed",
                ];
            }

            fwrite($fp, "MAIL FROM:<{$fromEmail}>\r\n");
            $read();

            fwrite($fp, "RCPT TO:<{$toEmail}>\r\n");
            $rcptResp = $read();

            if (strpos($rcptResp, '250') === false) {
                fclose($fp);
                Log::error("Direct SMTP recipient {$toEmail} rejected: {$rcptResp}");
                return [
                    'success' => false,
                    'error' => "Recipient address rejected by mail server",
                ];
            }

            fwrite($fp, "DATA\r\n");
            $read();

            $boundary = 'rmc_otp_' . md5(uniqid((string) time(), true));
            $msgId = '<' . md5(uniqid('rmc_', true)) . '.' . time() . '@ridemycars.com>';

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
                "X-Entity-Ref-ID: " . uniqid('rmc_', true),
                "MIME-Version: 1.0",
                "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
                "X-Mailer: RideMyCars-Mailer/2.0",
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
                Log::info("Email OTP {$otp} successfully dispatched via Direct SMTP to {$toEmail}");
                return [
                    'success' => true,
                    'message' => "Verification code sent to {$toEmail}",
                ];
            }

            Log::error("Direct SMTP DATA response failed: {$dataResp}");
            return [
                'success' => false,
                'error' => "Failed to deliver verification code",
            ];
        } catch (\Throwable $e) {
            Log::error("Direct SMTP exception for {$toEmail}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => "Mail delivery error: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Append Sent OTP Email to Roundcube / cPanel Webmail 'Sent' Folder via IMAP
     */
    protected function appendSentMessage(
        string $toEmail,
        string $otp,
        string $fromEmail,
        string $fromName,
        string $subject,
        string $htmlContent,
        string $textContent
    ): void {
        try {
            $host = \App\Services\SettingService::get('mail.host') ?: (config('mail.mailers.smtp.host') ?: env('MAIL_HOST', 'mail.ridemycars.com'));
            $user = \App\Services\SettingService::get('mail.username') ?: (config('mail.mailers.smtp.username') ?: env('MAIL_USERNAME', 'support@ridemycars.com'));
            $pass = \App\Services\SettingService::get('mail.password') ?: (config('mail.mailers.smtp.password') ?: env('MAIL_PASSWORD', ''));

            $fp = @fsockopen('ssl://' . $host, 993, $errno, $errstr, 4);
            if (!$fp) return;

            fgets($fp, 512);
            fwrite($fp, "a001 LOGIN \"{$user}\" \"{$pass}\"\r\n");
            $resp = fgets($fp, 512);
            if (strpos($resp, 'OK') === false) {
                fclose($fp);
                return;
            }

            $boundary = 'rmc_sent_' . md5(uniqid((string) time(), true));
            $date = date('r');
            $msgId = '<' . md5(uniqid('rmc_sent_', true)) . '@ridemycars.com>';

            $headers = [
                "From: {$fromName} <{$fromEmail}>",
                "To: <{$toEmail}>",
                "Subject: {$subject}",
                "Date: {$date}",
                "Message-ID: {$msgId}",
                "MIME-Version: 1.0",
                "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
                "X-Mailer: RideMyCars-Mailer/2.0",
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

            Log::info("Email OTP {$otp} successfully appended to IMAP INBOX.Sent folder for {$toEmail}");
        } catch (\Throwable $e) {
            Log::warning("IMAP Sent append notice: " . $e->getMessage());
        }
    }

    /**
     * Build Branded Responsive HTML Template
     */
    protected function buildHtmlTemplate(string $otp): string
    {
        $year = date('Y');
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RideMyCars Verification Code</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 24px;">
    <div style="max-width: 480px; margin: 0 auto; background-color: #ffffff; border-radius: 20px; overflow: hidden; border: 1px solid #e5e7eb; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #0b0f17 0%, #1e293b 100%); padding: 28px 32px; text-align: center;">
            <h1 style="margin: 0; font-size: 26px; font-weight: 900; letter-spacing: -0.5px; color: #f59e0b;">
                Ride<span style="color: #ffffff;">My</span>Cars
            </h1>
            <p style="margin: 4px 0 0 0; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8;">
                Executive Mobility & Logistics
            </p>
        </div>

        <!-- Body -->
        <div style="padding: 36px 32px 32px 32px;">
            <h2 style="margin: 0 0 12px 0; font-size: 20px; font-weight: 800; color: #0f172a;">
                Your Verification Code
            </h2>
            <p style="margin: 0 0 24px 0; font-size: 14px; line-height: 1.6; color: #475569;">
                Please use the 4-digit verification code below to complete your sign in. This code is valid for <strong>5 minutes</strong>.
            </p>

            <!-- OTP Box -->
            <div style="background-color: #fef3c7; border: 2px dashed #f59e0b; border-radius: 16px; padding: 20px; text-align: center; margin-bottom: 24px;">
                <span style="font-family: 'Courier New', Courier, monospace; font-size: 38px; font-weight: 900; letter-spacing: 10px; color: #b45309;">
                    {$otp}
                </span>
            </div>

            <div style="background-color: #f8fafc; border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; border: 1px solid #e2e8f0;">
                <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #64748b;">
                    ⏱ <strong>Valid for 5 minutes.</strong> If you did not request this verification code, please ignore this email or contact support.
                </p>
            </div>

            <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #94a3b8;">
                🔒 <strong>Security Tip:</strong> Never share your verification code with anyone. RideMyCars representatives will never ask for your code.
            </p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8fafc; padding: 20px 32px; border-top: 1px solid #f1f5f9; text-align: center; font-size: 11px; color: #94a3b8; line-height: 1.6;">
            &copy; {$year} New Development Finance Group Pty Ltd. All rights reserved.<br>
            4301 Saddle River Drive, Bowie, MD 20720, USA<br>
            <a href="https://ridemycars.com" style="color: #d97706; text-decoration: none; font-weight: 600;">ridemycars.com</a>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
