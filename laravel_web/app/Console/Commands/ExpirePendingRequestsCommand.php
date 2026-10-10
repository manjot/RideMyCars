<?php

namespace App\Console\Commands;

use App\Services\RequestExpirationService;
use Illuminate\Console\Command;

class ExpirePendingRequestsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'requests:expire-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically cancel pending Ride and Delivery requests whose waiting time has expired';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for overdue pending ride and delivery requests...');

        $result = RequestExpirationService::expireAllOverdue();

        $this->info("Sweep completed: {$result['rides_expired']} rides cancelled, {$result['deliveries_expired']} deliveries cancelled.");

        return Command::SUCCESS;
    }
}
