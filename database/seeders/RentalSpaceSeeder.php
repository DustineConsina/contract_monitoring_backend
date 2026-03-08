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
        try {
            // Food Stalls (10 stalls)
            for ($i = 1; $i <= 10; $i++) {
                RentalSpace::updateOrCreate(
                    ['space_code' => 'FS-' . str_pad($i, 3, '0', STR_PAD_LEFT)],
                    [
                        'space_type' => 'food_stall',
                        'name' => 'Food Stall ' . $i,
                        'size_sqm' => rand(15, 25),
                        'description' => 'Food stall space',
                        'base_rental_rate' => rand(3000, 5000),
                        'status' => 'available',
                    ]
                );
            }

            // Market Hall Bays (39 bays)
            for ($i = 1; $i <= 39; $i++) {
                RentalSpace::updateOrCreate(
                    ['space_code' => 'MH-' . str_pad($i, 3, '0', STR_PAD_LEFT)],
                    [
                        'space_type' => 'market_hall',
                        'name' => 'Market Bay ' . $i,
                        'size_sqm' => rand(12, 20),
                        'description' => 'Market hall bay',
                        'base_rental_rate' => rand(2000, 4000),
                        'status' => 'available',
                    ]
                );
            }

            // Bañera Warehouse Bays (12 bays)
            for ($i = 1; $i <= 12; $i++) {
                RentalSpace::updateOrCreate(
                    ['space_code' => 'BW-' . str_pad($i, 3, '0', STR_PAD_LEFT)],
                    [
                        'space_type' => 'banera_warehouse',
                        'name' => 'Bañera Bay ' . $i,
                        'size_sqm' => rand(20, 35),
                        'description' => 'Warehouse bay',
                        'base_rental_rate' => rand(4000, 7000),
                        'status' => 'available',
                    ]
                );
            }

            $this->command->info('✓ Created 61 rental spaces');
        } catch (\Exception $e) {
            $this->command->warn('⚠️ RentalSpaceSeeder: ' . $e->getMessage());
        }
    }
}
