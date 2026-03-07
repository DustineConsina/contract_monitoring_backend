<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;

echo "\n✅ Creating/Updating Test Users with Simple Passwords...\n\n";

$testUsers = [
    [
        'name' => 'Admin Test',
        'email' => 'admin@test.com',
        'password' => bcrypt('test123'),
        'role' => 'ADMIN',
    ],
    [
        'name' => 'Staff Test',
        'email' => 'staff@test.com',
        'password' => bcrypt('test123'),
        'role' => 'STAFF',
    ],
    [
        'name' => 'Tenant Test',
        'email' => 'tenant@test.com',
        'password' => bcrypt('test123'),
        'role' => 'TENANT',
    ],
];

foreach ($testUsers as $userData) {
    $user = User::updateOrCreate(
        ['email' => $userData['email']],
        $userData
    );
    echo "✓ {$userData['role']}: {$userData['email']} / test123\n";
}

echo "\n✅ Test Users Ready!\n";
?>
