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
        // Create test users and their associated tenants
        $tenants = [
            [
                'user_name' => 'Juan Dela Cruz',
                'email' => 'juan.delacruz@example.com',
                'phone' => '09171234567',
                'address' => '123 Main Street, Bulan, Sorsogon',
                'tenant_code' => 'TENANT-001',
                'business_name' => 'JDC Fish Trading',
                'business_type' => 'Fish Vendor',
                'tin' => '123456789',
                'business_address' => '123 Main Street, Bulan, Sorsogon',
                'contact_person' => 'Juan Dela Cruz',
                'contact_number' => '09171234567',
            ],
            [
                'user_name' => 'Maria Santos',
                'email' => 'maria.santos@example.com',
                'phone' => '09181234567',
                'address' => '456 Oak Avenue, Bulan, Sorsogon',
                'tenant_code' => 'TENANT-002',
                'business_name' => 'MS Vegetable Supply',
                'business_type' => 'Vegetable Vendor',
                'tin' => '234567890',
                'business_address' => '456 Oak Avenue, Bulan, Sorsogon',
                'contact_person' => 'Maria Santos',
                'contact_number' => '09181234567',
            ],
            [
                'user_name' => 'Pedro Reyes',
                'email' => 'pedro.reyes@example.com',
                'phone' => '09191234567',
                'address' => '789 Pine Road, Bulan, Sorsogon',
                'tenant_code' => 'TENANT-003',
                'business_name' => 'PR Cold Storage',
                'business_type' => 'Cold Storage Operator',
                'tin' => '345678901',
                'business_address' => '789 Pine Road, Bulan, Sorsogon',
                'contact_person' => 'Pedro Reyes',
                'contact_number' => '09191234567',
            ],
        ];

        foreach ($tenants as $tenantData) {
            // Create or get user
            $user = User::firstOrCreate(
                ['email' => $tenantData['email']],
                [
                    'name' => $tenantData['user_name'],
                    'password' => Hash::make('password123'),
                    'role' => 'tenant',
                    'phone' => $tenantData['phone'],
                    'address' => $tenantData['address'],
                    'status' => 'active',
                ]
            );

            // Create or get tenant
            Tenant::firstOrCreate(
                ['tenant_code' => $tenantData['tenant_code']],
                [
                    'user_id' => $user->id,
                    'business_name' => $tenantData['business_name'],
                    'business_type' => $tenantData['business_type'],
                    'tin' => $tenantData['tin'],
                    'business_address' => $tenantData['business_address'],
                    'contact_person' => $tenantData['contact_person'],
                    'contact_number' => $tenantData['contact_number'],
                    'status' => 'active',
                ]
            );
        }

        $this->command->info('✓ Created test tenants with associated users');
    }
}
