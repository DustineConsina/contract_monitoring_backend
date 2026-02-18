<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RentalSpace;

class RentalSpaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Food Stalls (10 stalls)
        for ($i = 1; $i <= 10; $i++) {
            RentalSpace::create([
                'space_code' => 'FS-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'space_type' => 'food_stall',
                'name' => 'Food Stall ' . $i,
                'size_sqm' => rand(15, 25),
                'description' => 'Food stall space located at the food court area',
                'base_rental_rate' => rand(3000, 5000),
                'status' => 'available',
            ]);
        }

        // Market Hall Bays (39 bays)
        for ($i = 1; $i <= 39; $i++) {
            RentalSpace::create([
                'space_code' => 'MH-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'space_type' => 'market_hall',
                'name' => 'Market Bay ' . $i,
                'size_sqm' => rand(12, 20),
                'description' => 'Market hall bay for fish vendors',
                'base_rental_rate' => rand(2000, 4000),
                'status' => 'available',
            ]);
        }

        // Bañera Warehouse Bays (12 bays)
        for ($i = 1; $i <= 12; $i++) {
            RentalSpace::create([
                'space_code' => 'BW-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'space_type' => 'banera_warehouse',
                'name' => 'Bañera Bay ' . $i,
                'size_sqm' => rand(20, 35),
                'description' => 'Warehouse bay for cold storage',
                'base_rental_rate' => rand(4000, 7000),
                'status' => 'available',
            ]);
        }

        $this->command->info('Created 61 rental spaces (10 food stalls, 39 market bays, 12 warehouse bays)');
    }
}
