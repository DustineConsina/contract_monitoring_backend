<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contract;
use App\Models\Tenant;
use App\Models\RentalSpace;
use Carbon\Carbon;

class EnsureContractIntegrity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:ensure-integrity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure all contracts have valid tenant and rental space relationships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking contract integrity...');

        // Find contracts without tenant_id
        $contractsWithoutTenant = Contract::whereNull('tenant_id')->count();
        $contractsWithoutRentalSpace = Contract::whereNull('rental_space_id')->count();

        if ($contractsWithoutTenant > 0) {
            $this->warn("⚠ Found {$contractsWithoutTenant} contracts without tenant_id");
        } else {
            $this->info('✓ All contracts have tenant_id');
        }

        if ($contractsWithoutRentalSpace > 0) {
            $this->warn("⚠ Found {$contractsWithoutRentalSpace} contracts without rental_space_id");
        } else {
            $this->info('✓ All contracts have rental_space_id');
        }

        // Check for invalid tenant references
        $invalidTenants = Contract::whereNotNull('tenant_id')
            ->whereDoesntHave('tenant')
            ->count();

        if ($invalidTenants > 0) {
            $this->warn("⚠ Found {$invalidTenants} contracts with non-existent tenant references");
        } else {
            $this->info('✓ All contracts have valid tenant references');
        }

        // Check for invalid rental space references
        $invalidRentalSpaces = Contract::whereNotNull('rental_space_id')
            ->whereDoesntHave('rentalSpace')
            ->count();

        if ($invalidRentalSpaces > 0) {
            $this->warn("⚠ Found {$invalidRentalSpaces} contracts with non-existent rental space references");
        } else {
            $this->info('✓ All contracts have valid rental space references');
        }

        // Check for tenants without users
        $tenantsWithoutUser = Tenant::whereNull('user_id')->count();
        if ($tenantsWithoutUser > 0) {
            $this->warn("⚠ Found {$tenantsWithoutUser} tenants without user_id");
        } else {
            $this->info('✓ All tenants have user_id');
        }

        // If asked to fix, provide instructions
        if ($contractsWithoutTenant > 0 || $contractsWithoutRentalSpace > 0 || $invalidTenants > 0 || $invalidRentalSpaces > 0 || $tenantsWithoutUser > 0) {
            $this->error("\n❌ Data integrity issues found!");
            $this->info("\nTo fix these issues, you may need to:");
            $this->line("1. Run: php artisan migrate:fresh --seed");
            $this->line("2. Or manually assign tenant_id and rental_space_id to contracts");
            return 1;
        }

        $this->info("\n✓ All contract data integrity checks passed!");
        return 0;
    }
}
