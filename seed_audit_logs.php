<?php

// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Carbon;

// Get first user
$user = User::first();
if (!$user) {
    echo "No users found. Create a user first.\n";
    exit(1);
}

// Create sample audit logs
$actions = ['create', 'update', 'delete', 'view'];
$modelTypes = ['Contract', 'Payment', 'Tenant', 'RentalSpace'];
$descriptions = [
    'Created new contract',
    'Updated contract terms',
    'Deleted old contract',
    'Viewed contract details',
    'Created payment record',
    'Updated payment status',
    'Created new tenant',
    'Updated tenant information',
];

for ($i = 0; $i < 20; $i++) {
    AuditLog::create([
        'user_id' => $user->id,
        'action' => $actions[array_rand($actions)],
        'model_type' => $modelTypes[array_rand($modelTypes)],
        'model_id' => rand(1, 10),
        'description' => $descriptions[array_rand($descriptions)],
        'old_values' => null,
        'new_values' => null,
        'ip_address' => '192.168.1.4',
        'user_agent' => 'Laravel Testing',
        'created_at' => Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
        'updated_at' => Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
    ]);
}

echo "✅ Created 20 sample audit logs!\n";
