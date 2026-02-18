<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contract;
use Carbon\Carbon;

class UpdateExpiredContracts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:update-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status of expired contracts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating expired contracts...');

        // Get active contracts that have passed their end date
        $expiredContracts = Contract::where('status', 'active')
            ->where('end_date', '<', Carbon::now())
            ->get();

        $count = 0;
        foreach ($expiredContracts as $contract) {
            $contract->status = 'expired';
            $contract->save();

            // Update rental space status to available
            $rentalSpace = $contract->rentalSpace;
            $rentalSpace->status = 'available';
            $rentalSpace->save();

            $count++;
        }

        $this->info("Updated {$count} expired contracts");
        $this->info('Expired contracts update completed!');

        return Command::SUCCESS;
    }
}
