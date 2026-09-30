<?php

namespace App\Console\Commands;

use App\Services\NaloSmsService;
use Illuminate\Console\Command;

class TestNaloSmsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nalo:test {phone? : Recipient Ghana phone number (e.g. +233559776761 or 0559776761)} {--message= : Custom SMS text message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Nalo Solutions Local Ghana SMS Gateway connectivity and send a live test SMS';

    /**
     * Execute the console command.
     */
    public function handle(NaloSmsService $naloService)
    {
        $this->info('==================================================');
        $this->info(' RideMyCars Nalo Solutions Ghana Gateway Tool');
        $this->info('==================================================');

        $this->line('Checking Nalo Solutions Configuration...');
        $this->table(
            ['Configuration Key', 'Value / Status'],
            [
                ['NALO_SMS_USERNAME', config('nalo.username') ?: '<fg=red>Not Set</>'],
                ['NALO_SMS_PASSWORD', config('nalo.password') ? '****** (Configured)' : '<fg=red>Not Set</>'],
                ['NALO_SMS_SENDER_ID', config('nalo.sender_id') ?: '<fg=yellow>None</>'],
                ['NALO_SMS_PREFIX', config('nalo.prefix') ?: 'Resl_Nalo'],
                ['NALO_SMS_BASE_URL', config('nalo.base_url')],
                ['NALO_SMS_ENABLED', config('nalo.enabled') ? '<fg=green>true</>' : '<fg=yellow>false</>'],
            ]
        );

        $phone = $this->argument('phone');
        if (!$phone) {
            $phone = $this->ask('Enter destination Ghana phone number (e.g., +233559776761 or 0559776761)');
        }

        if ($phone) {
            $msg = $this->option('message') ?: '8273 is OTP for your RideMyCars account. OTP is valid for 5 minutes. Do not share this OTP with anyone. For any help please visit https://ridemycars.com';
            $this->line("Sending SMS via Nalo Solutions to {$phone}...");

            $res = $naloService->sendSms($phone, $msg);

            if ($res['success']) {
                $ref = $res['message_id'] ?? 'delivered';
                $this->info("✓ SMS Sent Successfully via Nalo Solutions! Job ID / Ref: {$ref}");
                return 0;
            } else {
                $this->error("✗ Failed to send SMS via Nalo: {$res['error']} (Code: {$res['code']})");
                return 1;
            }
        }

        return 0;
    }
}
