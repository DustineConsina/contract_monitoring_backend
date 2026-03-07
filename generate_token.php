<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

// Get first admin user
$user = User::where('role', 'admin')->first();
if (!$user) {
    echo "No admin user found\n";
    exit(1);
}

// Create a test token
$token = $user->createToken('test-token')->plainTextToken;
echo "Test Token: " . $token . "\n";
echo "Token saved to token.txt\n";

file_put_contents('token.txt', $token);
