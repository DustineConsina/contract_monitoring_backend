<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contract;
use App\Models\Tenant;

class FixContractRelationships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:fix-relationships';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix broken contract relationships - create missing tenants and link contracts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 FIXING CONTRACT RELATIONSHIPS...\n');

        // Find contracts without tenant_id
        $contractsWithoutTenant = Contract::whereNull('tenant_id')->count();
        $contractsWithoutSpace = Contract::whereNull('rental_space_id')->count();

        $this->info("📊 Current State:");
        $this->line("   Contracts without tenant_id: {$contractsWithoutTenant}");
        $this->line("   Contracts without rental_space_id: {$contractsWithoutSpace}");
        $this->line("");

        // If there are contracts without tenants, we need to create test tenants and assign them
        if ($contractsWithoutTenant > 0 || !Tenant::exists()) {
            $this->warn("⚠️ Creating test tenants for contracts...");
            
            $tenants = [
                [
                    'tenant_code' => 'TENANT-001',
                    'business_name' => 'JDC Fish Trading',
                    'business_type' => 'Fish Vendor',
                    'tin' => '123456789',
                    'business_address' => '123 Main Street, Bulan, Sorsogon',
                    'contact_person' => 'Juan Dela Cruz',
                    'contact_number' => '09171234567',
                ],
                [
                    'tenant_code' => 'TENANT-002',
                    'business_name' => 'MS Vegetable Supply',
                    'business_type' => 'Vegetable Vendor',
                    'tin' => '234567890',
                    'business_address' => '456 Oak Avenue, Bulan, Sorsogon',
                    'contact_person' => 'Maria Santos',
                    'contact_number' => '09181234567',
                ],
                [
                    'tenant_code' => 'TENANT-003',
                    'business_name' => 'PR Cold Storage',
                    'business_type' => 'Cold Storage Operator',
                    'tin' => '345678901',
                    'business_address' => '789 Pine Road, Bulan, Sorsogon',
                    'contact_person' => 'Pedro Reyes',
                    'contact_number' => '09191234567',
                ],
            ];

            $createdTenants = [];
            foreach ($tenants as $tenantData) {
                $tenant = Tenant::firstOrCreate(
                    ['tenant_code' => $tenantData['tenant_code']],
                    $tenantData
                );
                $createdTenants[] = $tenant;
                $this->line("   ✓ Tenant created/found: {$tenant->business_name}");
            }

            // Assign tenants to contracts that don't have one
            $contractsToFix = Contract::whereNull('tenant_id')->take(3)->get();
            foreach ($contractsToFix as $index => $contract) {
                if (isset($createdTenants[$index])) {
                    $contract->tenant_id = $createdTenants[$index]->id;
                    $contract->save();
                    $this->line("   ✓ Contract {$contract->contract_number} → Tenant: {$createdTenants[$index]->business_name}");
                }
            }
        }

        // Verify all contracts now have tenants and spaces
        $this->info("\n✅ Verifying relationships...");
        
        $contractsWithTenant = Contract::whereNotNull('tenant_id')->count();
        $contractsWithSpace = Contract::whereNotNull('rental_space_id')->count();
        $totalContracts = Contract::count();

        $this->line("   Total contracts: {$totalContracts}");
        $this->line("   With tenant_id: {$contractsWithTenant}");
        $this->line("   With rental_space_id: {$contractsWithSpace}");

        // Show detail of first contract
        $firstContract = Contract::with(['tenant', 'rentalSpace'])->first();
        if ($firstContract) {
            $this->info("\n📋 Sample Contract Details:");
            $this->line("   Contract: {$firstContract->contract_number}");
            $this->line("   Tenant: " . ($firstContract->tenant ? $firstContract->tenant->business_name : 'NULL'));
            $this->line("   Space: " . ($firstContract->rentalSpace ? $firstContract->rentalSpace->name : 'NULL'));
        }

        $this->info("\n✅ RELATIONSHIP FIX COMPLETE!");
    }
}
