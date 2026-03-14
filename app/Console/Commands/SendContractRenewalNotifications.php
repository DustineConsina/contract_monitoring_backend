<?php

namespace App\Console\Commands;

use App\Models\Contract;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendContractRenewalNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:send-renewal-notifications {--days=60 : Check for contracts expiring within this many days}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Send renewal notifications for contracts expiring soon (in-app only, no emails)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $this->info("Checking for contracts expiring within {$days} days...");

        try {
            // Get contracts that are active and expiring soon
            $expiryDate = Carbon::now()->addDays($days);
            
            $contracts = Contract::where('status', 'active')
                ->whereBetween('end_date', [Carbon::now(), $expiryDate])
                ->with(['tenant', 'tenant.user', 'rentalSpace'])
                ->get();

            $this->info("Found {$contracts->count()} contracts for renewal.");

            $notificationsSent = 0;
            $notificationsFailed = 0;

            foreach ($contracts as $contract) {
                $this->line("Processing: {$contract->contract_number}");

                try {
                    if ($contract->createRenewalNotification()) {
                        $notificationsSent++;
                        $this->line("  ✓ Notification sent for contract #{$contract->contract_number}");
                    } else {
                        $notificationsFailed++;
                        $this->line("  ✗ Failed to send notification for contract #{$contract->contract_number} (returned false)");
                    }
                } catch (\Exception $e) {
                    $notificationsFailed++;
                    $this->error("  ✗ Exception for contract #{$contract->contract_number}: {$e->getMessage()}");
                    $this->error("     File: {$e->getFile()}:{$e->getLine()}");
                }
            }

            $this->newLine();
            $this->info("Summary:");
            $this->info("  Notifications sent: {$notificationsSent}");
            $this->warn("  Notifications failed: {$notificationsFailed}");
            $this->info("Contract renewal notification check completed successfully!");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
