<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        try {
            // Simple tenant creation
            $users = [
                ['email' => 'juan@example.com', 'name' => 'Juan Dela Cruz'],
                ['email' => 'maria@example.com', 'name' => 'Maria Santos'],
                ['email' => 'pedro@example.com', 'name' => 'Pedro Reyes'],
            ];

            foreach ($users as $userData) {
                $user = User::firstOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'],
                        'password' => Hash::make('password123'),
                        'role' => 'tenant',
                        'status' => 'active',
                    ]
                );

                Tenant::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'tenant_code' => 'T' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
                        'business_name' => $userData['name'] . ' Trading',
                        'status' => 'active',
                    ]
                );
            }

            $this->command->info('✓ Tenants created');
        } catch (\Exception $e) {
            $this->command->warn('⚠️ TenantSeeder: ' . $e->getMessage());
        }
    }
}
