<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating test tenants...');
        
        try {
            // Simple tenant creation
            $users = [
                ['email' => 'juan@example.com', 'name' => 'Juan Dela Cruz'],
                ['email' => 'maria@example.com', 'name' => 'Maria Santos'],
                ['email' => 'pedro@example.com', 'name' => 'Pedro Reyes'],
            ];

            $tenantCount = 0;
            foreach ($users as $userData) {
                try {
                    $user = User::firstOrCreate(
                        ['email' => $userData['email']],
                        [
                            'name' => $userData['name'],
                            'password' => Hash::make('password123'),
                            'role' => 'tenant',
                            'status' => 'active',
                        ]
                    );
                    
                    $tenant = Tenant::firstOrCreate(
                        ['user_id' => $user->id],
                        [
                            'tenant_code' => 'T' . str_pad($user->id, 3, '0', STR_PAD_LEFT),
                            'business_name' => $userData['name'] . ' Trading',
                            'status' => 'active',
                        ]
                    );
                    
                    $this->command->line("✓ Tenant created: {$tenant->business_name} (ID: {$tenant->id})");
                    $tenantCount++;
                } catch (QueryException $e) {
                    if (strpos($e->getMessage(), 'Duplicate') !== false) {
                        $this->command->line("⚠️  Tenant already exists: {$userData['email']}");
                    } else {
                        $this->command->warn("✗ Failed to create tenant: " . $e->getMessage());
                        \Illuminate\Support\Facades\Log::error('TenantSeeder error', ['email' => $userData['email'], 'error' => $e->getMessage()]);
                    }
                } catch (\Exception $e) {
                    $this->command->warn("✗ Unexpected error: " . $e->getMessage());
                    \Illuminate\Support\Facades\Log::error('TenantSeeder exception', ['error' => $e->getMessage()]);
                }
            }

            $this->command->info("✓ Tenants creation complete (total: {$tenantCount})");
        } catch (\Exception $e) {
            $this->command->error('✗ Critical seeder error: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('TenantSeeder critical', ['error' => $e->getMessage()]);
        }
    }
}
