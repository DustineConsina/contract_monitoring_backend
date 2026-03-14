<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Contract;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amountDue = $this->faker->randomFloat(2, 1000, 50000);
        $amountPaid = $this->faker->randomFloat(2, 0, $amountDue);
        $interestAmount = $this->faker->randomFloat(2, 0, 500);
        $totalAmount = $amountDue + $interestAmount;
        $balance = $totalAmount - $amountPaid;

        return [
            'payment_number' => 'PAY-' . $this->faker->unique()->numerify('########'),
            'contract_id' => Contract::factory(),
            'tenant_id' => Tenant::factory(),
            'billing_period_start' => Carbon::now()->subMonths(1)->startOfMonth(),
            'billing_period_end' => Carbon::now()->subMonths(1)->endOfMonth(),
            'due_date' => Carbon::now()->addDays(10),
            'amount_due' => $amountDue,
            'interest_amount' => $interestAmount,
            'total_amount' => $totalAmount,
            'amount_paid' => $amountPaid,
            'balance' => $balance,
            'payment_date' => ($amountPaid > 0) ? Carbon::now()->subDays(5) : null,
            'payment_method' => $this->faker->randomElement(['cash', 'check', 'bank_transfer']),
            'reference_number' => 'REF-' . $this->faker->numerify('###########'),
            'remarks' => $this->faker->sentence(),
            'status' => ($balance == 0) ? 'paid' : 'pending',
        ];
    }
}
