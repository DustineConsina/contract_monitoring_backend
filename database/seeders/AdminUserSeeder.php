<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'email' => 'admin@pfda.gov.ph',
                'name' => 'Admin User',
                'role' => 'admin',
                'phone' => '09123456789',
            ],
            [
                'email' => 'staff@pfda.gov.ph',
                'name' => 'Staff User',
                'role' => 'staff',
                'phone' => '09123456790',
            ],
            [
                'email' => 'cashier@pfda.gov.ph',
                'name' => 'Cashier User',
                'role' => 'cashier',
                'phone' => '09123456791',
            ],
        ];

        foreach ($users as $userData) {
            try {
                User::firstOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'],
                        'password' => Hash::make('password123'),
                        'role' => $userData['role'],
                        'phone' => $userData['phone'],
                        'address' => 'PFDA Office, Bulan, Sorsogon',
                        'status' => 'active',
                    ]
                );
                $this->command->line("✓ {$userData['email']} ({$userData['role']}) ready");
            } catch (QueryException $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $this->command->line("⚠️  {$userData['email']} already exists");
                } else {
                    $this->command->error("✗ Failed to create {$userData['email']}: " . $e->getMessage());
                    \Illuminate\Support\Facades\Log::error('AdminUserSeeder error', [
                        'email' => $userData['email'],
                        'error' => $e->getMessage()
                    ]);
                }
            } catch (\Exception $e) {
                $this->command->error("✗ Unexpected error for {$userData['email']}: " . $e->getMessage());
                \Illuminate\Support\Facades\Log::error('AdminUserSeeder unexpected error', [
                    'email' => $userData['email'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->command->info('✓ Admin, staff, and cashier users setup complete');
        $this->command->info('Credentials:');
        $this->command->info('  Admin: admin@pfda.gov.ph / password123');
        $this->command->info('  Staff: staff@pfda.gov.ph / password123');
        $this->command->info('  Cashier: cashier@pfda.gov.ph / password123');
    }
}
