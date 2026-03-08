<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contract;
use App\Models\Tenant;
use App\Models\User;
use App\Models\RentalSpace;

class DiagnoseContractData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diagnose:contracts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose contract data relationships and structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 DIAGNOSING CONTRACT DATA...\n');

        // Check counts
        $userCount = User::count();
        $tenantCount = Tenant::count();
        $contractCount = Contract::count();
        $spaceCount = RentalSpace::count();

        $this->info("📊 DATABASE COUNTS:");
        $this->line("   Users: {$userCount}");
        $this->line("   Tenants: {$tenantCount}");
        $this->line("   Contracts: {$contractCount}");
        $this->line("   Rental Spaces: {$spaceCount}");
        $this->line("");

        // Check users
        $this->info("👥 USERS IN DATABASE:");
        $users = User::all(['id', 'name', 'email', 'role']);
        if ($users->isEmpty()) {
            $this->warn("   ❌ NO USERS FOUND");
        } else {
            foreach ($users as $user) {
                $this->line("      {$user->id}: {$user->name} ({$user->email}) - Role: {$user->role}");
            }
        }
        $this->line("");

        // Check tenants and their users
        $this->info("🏢 TENANTS IN DATABASE:");
        $tenants = Tenant::with('user')->all();
        if ($tenants->isEmpty()) {
            $this->warn("   ❌ NO TENANTS FOUND");
        } else {
            foreach ($tenants as $tenant) {
                $userName = $tenant->user ? $tenant->user->name : "NO USER";
                $userId = $tenant->user_id;
                $this->line("      ID {$tenant->id}: {$tenant->business_name} (Tenant Code: {$tenant->tenant_code})");
                $this->line("         User ID: {$userId} → {$userName}");
            }
        }
        $this->line("");

        // Check contracts
        $this->info("📋 CONTRACTS IN DATABASE:");
        $contracts = Contract::with(['tenant', 'tensor.user', 'rentalSpace'])->get();
        if ($contracts->isEmpty()) {
            $this->warn("   ❌ NO CONTRACTS FOUND");
        } else {
            foreach ($contracts as $contract) {
                $this->line("      Contract #{$contract->id}: {$contract->contract_number}");
                
                // Tenant info
                if ($contract->tenant) {
                    $tenantUserName = $contract->tenant->user ? $contract->tenant->user->name : "NO USER";
                    $this->line("         Tenant: {$contract->tenant->business_name} (User: {$tenantUserName})");
                } else {
                    $this->warn("         Tenant: ❌ NULL (tenant_id: {$contract->tenant_id})");
                }
                
                // Rental space info
                if ($contract->rentalSpace) {
                    $this->line("         Space: {$contract->rentalSpace->name} ({$contract->rentalSpace->space_code}) - Type: {$contract->rentalSpace->space_type}");
                } else {
                    $this->warn("         Space: ❌ NULL (rental_space_id: {$contract->rental_space_id})");
                }
                
                $this->line("         Status: {$contract->status}");
                $this->line("");
            }
        }

        // Check rental spaces
        $this->info("🏪 RENTAL SPACES IN DATABASE:");
        $spaces = RentalSpace::all(['id', 'space_code', 'name', 'space_type', 'size_sqm']);
        if ($spaces->isEmpty()) {
            $this->warn("   ❌ NO RENTAL SPACES FOUND");
        } else {
            $this->line("   Total: " . $spaces->count() . " spaces");
            $this->line("   Sample spaces:");
            foreach ($spaces->take(5) as $space) {
                $this->line("      {$space->space_code}: {$space->name} ({$space->space_type}) - {$space->size_sqm} m²");
            }
            if ($spaces->count() > 5) {
                $this->line("      ... and " . ($spaces->count() - 5) . " more spaces");
            }
        }

        $this->info("\n✅ DIAGNOSIS COMPLETE");
    }
}
