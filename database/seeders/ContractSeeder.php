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
        // Get first 3 tenants and rental spaces
        $tenants = Tenant::take(3)->get();
        $rentalSpaces = RentalSpace::inRandomOrder()->take(3)->get();

        if ($tenants->count() >= 3 && $rentalSpaces->count() >= 3) {
            // Create contracts for each tenant with a rental space
            foreach ($tenants as $index => $tenant) {
                if (isset($rentalSpaces[$index])) {
                    $rentalSpace = $rentalSpaces[$index];
                    
                    Contract::firstOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'rental_space_id' => $rentalSpace->id,
                        ],
                        [
                            'contract_number' => 'CNT-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                            'start_date' => Carbon::now()->subMonths(6),
                            'duration_months' => 12,
                            'monthly_rental' => $rentalSpace->base_rental_rate,
                            'deposit_amount' => $rentalSpace->base_rental_rate * 2,
                            'interest_rate' => 5.00,
                            'terms_conditions' => 'Standard lease terms and conditions apply.',
                            'status' => 'active',
                        ]
                    );
                }
            }

            $this->command->info('✓ Created test contracts with proper tenant and rental space relationships');
        } else {
            $this->command->warning('⚠ Not enough tenants or rental spaces to create contracts');
        }
    }
}
