<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test tenants and their users
        $tenants = [
            [
                'name' => 'Juan Dela Cruz',
                'email' => 'juan@example.com',
                'phone' => '09171234567',
                'address' => '123 Main Street, Bulan',
                'business_name' => 'JDC Fish Trading',
                'business_type' => 'Fish Vendor',
                'tin' => '123456789',
                'contact_person' => 'Juan Dela Cruz',
                'contact_number' => '09171234567',
                'business_address' => '123 Main Street',
                'tenant_code' => 'T001',
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria@example.com',
                'phone' => '09181234567',
                'address' => '456 Oak Avenue, Bulan',
                'business_name' => 'MS Vegetable Supply',
                'business_type' => 'Vegetable Vendor',
                'tin' => '234567890',
                'contact_person' => 'Maria Santos',
                'contact_number' => '09181234567',
                'business_address' => '456 Oak Avenue',
                'tenant_code' => 'T002',
            ],
            [
                'name' => 'Pedro Reyes',
                'email' => 'pedro@example.com',
                'phone' => '09191234567',
                'address' => '789 Pine Road, Bulan',
                'business_name' => 'PR Cold Storage',
                'business_type' => 'Cold Storage Operator',
                'tin' => '345678901',
                'contact_person' => 'Pedro Reyes',
                'contact_number' => '09191234567',
                'business_address' => '789 Pine Road',
                'tenant_code' => 'T003',
            ],
        ];

        foreach ($tenants as $data) {
            try {
                // Create user if doesn't exist
                $user = User::updateOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'],
                        'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                        'role' => 'tenant',
                        'phone' => $data['phone'],
                        'address' => $data['address'],
                        'status' => 'active',
                    ]
                );

                // Create tenant for the user
                Tenant::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'tenant_code' => $data['tenant_code'],
                        'business_name' => $data['business_name'],
                        'business_type' => $data['business_type'],
                        'tin' => $data['tin'],
                        'contact_person' => $data['contact_person'],
                        'contact_number' => $data['contact_number'],
                        'business_address' => $data['business_address'],
                        'status' => 'active',
                    ]
                );

                $this->command->line("✓ Created: {$data['business_name']}");
            } catch (\Exception $e) {
                $this->command->warn("✗ Failed to create {$data['business_name']}: " . $e->getMessage());
            }
        }

        $this->command->info('✓ TenantSeeder complete');
    }
}
