<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contract;
use App\Models\Tenant;
use App\Models\RentalSpace;
use Carbon\Carbon;

class ContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Log::info('🌱 ContractSeeder starting...');

        // Get all tenants
        $tenants = Tenant::all();
        $spaces = RentalSpace::all();

        if ($tenants->count() === 0) {
            $this->command->warn('⚠️ No tenants found - skipping contract seeding');
            return;
        }

        if ($spaces->count() === 0) {
            $this->command->warn('⚠️ No rental spaces found - skipping contract seeding');
            return;
        }

        $this->command->info("Creating contracts for {$tenants->count()} tenants...");

        foreach ($tenants as $index => $tenant) {
            // Get a rental space (rotate through available spaces)
            $space = $spaces->get($index % $spaces->count());

            if (!$tenant || !$space) {
                \Log::warning("Skipping contract creation - missing tenant or space");
                continue;
            }

            Contract::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'rental_space_id' => $space->id,
                ],
                [
                    'contract_number' => 'CNT-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                    'start_date' => Carbon::now()->subMonths(3),
                    'duration_months' => 12,
                    'monthly_rental' => (int)$space->base_rental_rate,
                    'deposit_amount' => (int)$space->base_rental_rate * 2,
                    'interest_rate' => 5.00,
                    'terms_conditions' => 'Standard lease terms and conditions apply.',
                    'status' => 'active',
                ]
            );

            \Log::info("✓ Contract created: Tenant {$tenant->business_name} ↔ Space {$space->name}");
        }

        $this->command->info('✓ ContractSeeder complete - contracts created with proper relationships');
    }
}
