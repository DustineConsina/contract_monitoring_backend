<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contract;
use App\Models\Tenant;
use App\Models\RentalSpace;

class LinkOrphanedContracts extends Command
{
    protected $signature = 'contracts:link-orphaned {--dry-run}';
    protected $description = 'Link contracts that are missing tenant or rental space relationships. Use --dry-run to preview changes.';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - no changes will be made');
        }

        $this->info("\n=== Finding Orphaned Contracts ===\n");

        // Find contracts with no tenant
        $orphanedTenantContracts = Contract::whereNull('tenant_id')->get();
        
        // Find contracts with no rental space
        $orphanedSpaceContracts = Contract::whereNull('rental_space_id')->get();

        $this->line("Contracts missing tenant: " . $orphanedTenantContracts->count());
        $this->line("Contracts missing rental space: " . $orphanedSpaceContracts->count());

        if ($orphanedTenantContracts->isEmpty() && $orphanedSpaceContracts->isEmpty()) {
            $this->info("\n✓ No orphaned contracts found!");
            return 0;
        }

        // For contracts missing tenant, try to assign the first available tenant
        if ($orphanedTenantContracts->isNotEmpty()) {
            $this->info("\n--- Processing Contracts Missing Tenant ---\n");
            
            $availableTenants = Tenant::where('status', 'active')->get();
            
            if ($availableTenants->isEmpty()) {
                $this->warn("⚠️  No active tenants available to assign!");
            } else {
                $tenantIndex = 0;
                foreach ($orphanedTenantContracts as $contract) {
                    $tenant = $availableTenants[$tenantIndex % $availableTenants->count()];
                    
                    $this->line("Contract {$contract->contract_number}: Assigning Tenant #{$tenant->id} ({$tenant->business_name})");
                    
                    if (!$dryRun) {
                        $contract->tenant_id = $tenant->id;
                        $contract->save();
                    }
                    
                    $tenantIndex++;
                }
            }
        }

        // For contracts missing rental space, try to assign the first available space
        if ($orphanedSpaceContracts->isNotEmpty()) {
            $this->info("\n--- Processing Contracts Missing Rental Space ---\n");
            
            $availableSpaces = RentalSpace::where('status', 'available')
                ->orWhere('status', 'active')
                ->get();
            
            if ($availableSpaces->isEmpty()) {
                $this->warn("⚠️  No available rental spaces found!");
            } else {
                $spaceIndex = 0;
                foreach ($orphanedSpaceContracts as $contract) {
                    $space = $availableSpaces[$spaceIndex % $availableSpaces->count()];
                    
                    $this->line("Contract {$contract->contract_number}: Assigning Space #{$space->id} ({$space->name})");
                    
                    if (!$dryRun) {
                        $contract->rental_space_id = $space->id;
                        $contract->save();
                    }
                    
                    $spaceIndex++;
                }
            }
        }

        if ($dryRun) {
            $this->info("\n\n🔍 DRY RUN COMPLETE - No changes were made.\n");
            $this->info("Run command without --dry-run flag to apply these changes:");
            $this->line("php artisan contracts:link-orphaned\n");
        } else {
            $this->info("\n\n✓ Orphaned contracts have been linked!\n");
        }

        return 0;
    }
}
