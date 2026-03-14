<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddForRenewalStatus extends Command
{
    protected $signature = 'db:add-for-renewal-status';
    protected $description = 'Add for_renewal status to contracts table enum';

    public function handle()
    {
        try {
            $this->info('Adding for_renewal status to contracts table...');
            
            DB::statement("ALTER TABLE contracts MODIFY status ENUM('active', 'expired', 'terminated', 'pending', 'for_renewal') DEFAULT 'pending'");
            
            $this->info('✓ Successfully added for_renewal status!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
