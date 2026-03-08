<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@pfda.gov.ph',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '09123456789',
            'address' => 'PFDA Office, Bulan, Sorsogon',
            'status' => 'active',
        ]);

        // Create Staff User
        User::create([
            'name' => 'Staff User',
            'email' => 'staff@pfda.gov.ph',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'phone' => '09123456790',
            'address' => 'PFDA Office, Bulan, Sorsogon',
            'status' => 'active',
        ]);

        // Create Cashier User
        User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@pfda.gov.ph',
            'password' => Hash::make('password123'),
            'role' => 'cashier',
            'phone' => '09123456791',
            'address' => 'PFDA Office, Bulan, Sorsogon',
            'status' => 'active',
        ]);

        $this->command->info('Created admin, staff, and cashier users');
        $this->command->info('Admin Email: admin@pfda.gov.ph, Password: password123');
        $this->command->info('Staff Email: staff@pfda.gov.ph, Password: password123');
        $this->command->info('Cashier Email: cashier@pfda.gov.ph, Password: password123');
    }
}
