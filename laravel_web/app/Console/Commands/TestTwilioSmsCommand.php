<?php

namespace App\Console\Commands;

use App\Services\TwilioSmsService;
use Illuminate\Console\Command;

class TestTwilioSmsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twilio:test {phone? : Recipient phone number in E.164 format (e.g. +1234567890)} {--message= : Custom SMS text message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Twilio SMS Gateway connectivity and send a test SMS';

    /**
     * Execute the console command.
     */
    public function handle(TwilioSmsService $smsService)
    {
        $this->info('==================================================');
        $this->info(' RideMyCars Twilio SMS Gateway Diagnostic Tool');
        $this->info('==================================================');

        $this->line('Checking Twilio Configuration...');
        $this->table(
            ['Configuration Key', 'Value / Status'],
            [
                ['TWILIO_ACCOUNT_SID', config('twilio.account_sid') ? substr(config('twilio.account_sid'), 0, 8) . '...' : '<fg=red>Not Set</>'],
                ['TWILIO_AUTH_TOKEN', config('twilio.auth_token') ? '****** (Configured)' : '<fg=red>Not Set</>'],
                ['TWILIO_PHONE_NUMBER', config('twilio.phone_number') ?: '<fg=yellow>None</>'],
                ['TWILIO_MESSAGING_SERVICE_SID', config('twilio.messaging_service_sid') ?: '<fg=yellow>None</>'],
                ['TWILIO_SMS_ENABLED', config('twilio.enabled') ? '<fg=green>true</>' : '<fg=yellow>false</>'],
            ]
        );

        $this->newLine();
        $this->line('Testing API Connection to Twilio...');
        $conn = $smsService->testConnection();

        if (!$conn['success']) {
            $this->error('Connection Failed: ' . ($conn['error'] ?? 'Unknown error'));
            return 1;
        }

        $this->info('✓ Connection Successful!');
        $this->line("Account: {$conn['friendly_name']} | Status: {$conn['status']} | Type: {$conn['type']}");

        $phone = $this->argument('phone');
        if (!$phone) {
            $phone = $this->ask('Enter destination phone number (e.g., +15551234567 or +233240000000)');
        }

        if ($phone) {
            $msg = $this->option('message') ?: 'Hello from RideMyCars! Your Twilio Worldwide SMS Gateway is successfully configured and active.';
            $this->line("Sending SMS to {$phone}...");

            $res = $smsService->sendSms($phone, $msg);

            if ($res['success']) {
                $this->info("✓ SMS Sent Successfully! Message SID: {$res['message_sid']} (Status: {$res['status']})");
                return 0;
            } else {
                $this->error("✗ Failed to send SMS: {$res['error']} (Code: {$res['code']})");
                return 1;
            }
        }

        return 0;
    }
}
