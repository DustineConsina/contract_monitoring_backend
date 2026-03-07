<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

// Generate a token for the admin user
$user = User::where('email', 'admin@pfda.gov.ph')->first();

if (!$user) {
    echo "User not found\n";
    exit(1);
}

$token = $user->createToken('test-token')->plainTextToken;

echo "Bearer Token: $token\n";
file_put_contents('test_token.txt', $token);
?>
