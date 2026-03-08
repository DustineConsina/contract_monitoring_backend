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
        $tenants = Tenant::all();
        $spaces = RentalSpace::all();

        if ($tenants->isEmpty() || $spaces->isEmpty()) {
            $this->command->warn('⚠️ No tenants or spaces found - skipping contracts');
            return;
        }

        foreach ($tenants as $index => $tenant) {
            try {
                $space = $spaces->get($index % $spaces->count());

                if (!$tenant || !$space) {
                    continue;
                }

                $startDate = Carbon::now()->subMonths(3);
                Contract::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'rental_space_id' => $space->id,
                    ],
                    [
                        'contract_number' => 'CNT-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                        'start_date' => $startDate,
                        'end_date' => $startDate->addMonths(12),
                        'duration_months' => 12,
                        'monthly_rental' => (int)$space->base_rental_rate,
                        'deposit_amount' => ((int)$space->base_rental_rate) * 2,
                        'interest_rate' => 5.00,
                        'terms_conditions' => 'Standard lease terms.',
                        'status' => 'active',
                    ]
                );

                $this->command->line("✓ Contract created for {$tenant->tenant_code}");
            } catch (\Exception $e) {
                $this->command->warn("✗ Contract seed failed: " . $e->getMessage());
            }
        }

        $this->command->info('✓ ContractSeeder complete');
    }
}
