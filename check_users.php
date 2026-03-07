<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;

$users = User::select('id', 'email', 'name', 'role')->limit(10)->get();

echo "\n=== Users in Database ===\n";
if ($users->isEmpty()) {
    echo "No users found!\n";
} else {
    foreach ($users as $user) {
        echo "• {$user->name} ({$user->email}) - Role: {$user->role}\n";
    }
}

// Check if we need to create test users
if ($users->isEmpty()) {
    echo "\n✓ Creating test users...\n";
    
    $testUsers = [
        [
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('admin123'),
            'role' => 'ADMIN',
        ],
        [
            'name' => 'Jenny Smith',
            'email' => 'jenny@test.com',
            'password' => bcrypt('jenny123'),
            'role' => 'TENANT',
        ],
        [
            'name' => 'Dustine Johnson',
            'email' => 'dustine@test.com',
            'password' => bcrypt('dustine123'),
            'role' => 'TENANT',
        ],
    ];
    
    foreach ($testUsers as $userData) {
        if (!User::where('email', $userData['email'])->exists()) {
            User::create($userData);
            echo "✓ Created: {$userData['email']}\n";
        }
    }
} else {
    echo "\n✓ Users already exist\n";
}

echo "\n✅ Test Credentials:\n";
echo "Admin: admin@test.com / admin123\n";
echo "Tenant: jenny@test.com / jenny123\n";
echo "Tenant: dustine@test.com / dustine123\n\n";
?>

