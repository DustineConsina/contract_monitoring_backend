<?php

namespace Database\Factories;

use App\Models\RentalSpace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RentalSpace>
 */
class RentalSpaceFactory extends Factory
{
    protected $model = RentalSpace::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Unit ' . $this->faker->numerify('###'),
            'description' => $this->faker->sentence(),
            'location' => $this->faker->address(),
            'area_sqm' => $this->faker->numberBetween(20, 500),
            'price_per_sqm' => $this->faker->randomFloat(2, 50, 500),
            'status' => 'available',
            'features' => json_encode(['WiFi', 'Parking', 'Security']),
        ];
    }
}
