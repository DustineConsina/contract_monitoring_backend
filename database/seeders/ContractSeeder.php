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
        $this->command->info('Creating test contracts...');
        
        try {
            $tenants = Tenant::all();
            $spaces = RentalSpace::all();

            if ($tenants->isEmpty()) {
                $this->command->warn('⚠️ No tenants found, skipping contracts');
                return;
            }
            
            if ($spaces->isEmpty()) {
                $this->command->warn('⚠️ No rental spaces found, skipping contracts');
                return;
            }

            $contractCount = 0;
            foreach ($tenants as $index => $tenant) {
                try {
                    $space = $spaces->get($index % $spaces->count());

                    if (!$tenant || !$space) {
                        continue;
                    }

                    $startDate = Carbon::now()->subMonths(3);
                    $endDate = $startDate->copy()->addMonths(12);
                    
                    Contract::updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'rental_space_id' => $space->id,
                        ],
                        [
                            'contract_number' => 'CNT-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'duration_months' => 12,
                            'monthly_rental' => (int)$space->base_rental_rate,
                            'deposit_amount' => ((int)$space->base_rental_rate) * 2,
                            'interest_rate' => 5.00,
                            'terms_conditions' => 'Standard lease terms.',
                            'status' => 'active',
                        ]
                    );

                    $this->command->line("✓ Contract for {$tenant->business_name} ↔ {$space->name}");
                    $contractCount++;
                } catch (\Exception $e) {
                    $this->command->warn("✗ Contract for {$tenant->business_name} failed: " . $e->getMessage());
                    \Illuminate\Support\Facades\Log::error('ContractSeeder error', ['tenant_id' => $tenant->id, 'error' => $e->getMessage()]);
                }
            }

            $this->command->info("✓ Contracts creation complete ({$contractCount} contracts)");
        } catch (\Exception $e) {
            $this->command->error('✗ Critical error: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('ContractSeeder critical', ['error' => $e->getMessage()]);
        }
    }
}
