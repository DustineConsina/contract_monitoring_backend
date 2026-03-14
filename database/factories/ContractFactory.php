<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Tenant;
use App\Models\RentalSpace;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contract>
 */
class ContractFactory extends Factory
{
    protected $model = Contract::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = Carbon::now()->subMonths(6);
        $endDate = $startDate->copy()->addMonths(12);

        return [
            'contract_number' => 'CNT-' . $this->faker->unique()->numerify('########'),
            'tenant_id' => Tenant::factory(),
            'rental_space_id' => RentalSpace::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_months' => 12,
            'monthly_rental' => $this->faker->randomFloat(2, 1000, 50000),
            'deposit_amount' => $this->faker->randomFloat(2, 1000, 10000),
            'interest_rate' => 3.00,
            'terms_conditions' => $this->faker->sentence(10),
            'contract_file' => null,
            'status' => 'active',
            'last_notification_sent' => null,
        ];
    }
}
