<?php

namespace App\Console\Commands;

use App\Services\SmsGatewayManager;
use Illuminate\Console\Command;

class TestSmsGatewayCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:test {phone? : Destination phone number (e.g. +233559776761 or +13053688734)} {--provider=auto : Force provider (auto, nalo, twilio)} {--message= : Custom SMS text message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test RideMyCars SMS Gateway smart routing (Ghana -> Nalo, International -> Twilio)';

    /**
     * Execute the console command.
     */
    public function handle(SmsGatewayManager $manager)
    {
        $this->info('==================================================');
        $this->info(' RideMyCars SMS Gateway Diagnostic & Routing Tool');
        $this->info('==================================================');

        $this->line('Current Gateway Status:');
        $this->table(
            ['Gateway', 'Country Scope', 'Status', 'Sender ID / From'],
            [
                ['Nalo Solutions', 'Ghana (+233)', config('nalo.enabled') ? '<fg=green>ACTIVE</>' : '<fg=yellow>DISABLED</>', config('nalo.sender_id')],
                ['Twilio Worldwide', 'International (Rest of World)', config('twilio.enabled') ? '<fg=green>ACTIVE</>' : '<fg=yellow>DISABLED</>', config('twilio.alphanumeric_sender') ?: config('twilio.phone_number')],
            ]
        );

        $phone = $this->argument('phone');
        if (!$phone) {
            $phone = $this->ask('Enter destination phone number (e.g. +233559776761 for Ghana, +13053688734 for USA)');
        }

        if (!$phone) {
            $this->warn('No phone number provided. Exiting.');
            return 1;
        }

        $formatted = $manager->formatE164($phone);
        $isGhana = $manager->isGhanaNumber($formatted);
        $expectedProvider = $isGhana ? 'Nalo Solutions (Ghana)' : 'Twilio Worldwide (International)';

        $this->newLine();
        $this->line("Recipient: <fg=cyan>{$formatted}</>");
        $this->line("Detected Destination: " . ($isGhana ? "<fg=green>Ghana (+233)</>" : "<fg=yellow>International</>"));
        $this->line("Automatic Routed Provider: <fg=cyan>{$expectedProvider}</>");

        $msg = $this->option('message') ?: '8273 is OTP for your RideMyCars account. OTP is valid for 5 minutes. Do not share this OTP with anyone. For any help please visit https://ridemycars.com';

        $this->newLine();
        $this->line("Dispatching message...");

        $res = $manager->sendSms($phone, $msg);

        if ($res['success']) {
            $providerUsed = $res['provider'] ?? 'unknown';
            $refId = $res['message_sid'] ?? $res['message_id'] ?? 'n/a';
            $this->info("✓ Message Dispatched Successfully via [{$providerUsed}]!");
            $this->line("Reference / Message ID: {$refId}");
            return 0;
        } else {
            $this->error("✗ SMS Delivery Failed: {$res['error']} (Code: {$res['code']})");
            return 1;
        }
    }
}
