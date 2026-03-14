<?php

namespace Database\Factories;

use App\Models\DemandLetter;
use App\Models\Contract;
use App\Models\Tenant;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DemandLetter>
 */
class DemandLetterFactory extends Factory
{
    protected $model = DemandLetter::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'demand_number' => 'DL-' . date('Ymd') . '-' . $this->faker->unique()->numerify('###'),
            'contract_id' => Contract::factory(),
            'tenant_id' => Tenant::factory(),
            'payment_id' => Payment::factory(),
            'outstanding_balance' => $this->faker->randomFloat(2, 1000, 50000),
            'total_amount_demanded' => $this->faker->randomFloat(2, 1000, 50000),
            'issued_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(5),
            'status' => 'issued',
            'email_sent_to' => $this->faker->email(),
            'remarks' => $this->faker->sentence(),
        ];
    }
}
