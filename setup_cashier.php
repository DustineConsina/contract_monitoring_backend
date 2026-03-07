<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== Creating Cashier User ===\n\n";

// Check if cashier already exists
$cashier = User::where('email', 'cashier@pfda.gov.ph')->first();

if ($cashier) {
    echo "✓ Cashier account already exists:\n";
} else {
    $cashier = User::create([
        'name' => 'Maria Cashier',
        'email' => 'cashier@pfda.gov.ph',
        'password' => bcrypt('cashier123'),
        'role' => 'cashier',
    ]);
    echo "✓ Cashier account created:\n";
}

echo "  Email: {$cashier->email}\n";
echo "  Password: cashier123\n";
echo "  Role: {$cashier->role}\n\n";

// Test login
$token = $cashier->createToken('cashier-token')?->plainTextToken;

echo "✓ Auth token generated\n\n";

// Test API endpoints
echo "Testing Cashier API Endpoints:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$baseUrl = 'http://127.0.0.1:8000/api/cashier';
$headers = [
    "Authorization: Bearer $token",
    "Content-Type: application/json"
];

// Test today's collection
echo "\n1. GET /cashier/todays-collection\n";
curl_setopt($ch, CURLOPT_URL, "$baseUrl/todays-collection");
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($ch);
if ($response) {
    $data = json_decode($response, true);
    echo "   Status: " . ($data['success'] ? '✓ SUCCESS' : '✗ FAILED') . "\n";
}

// Test collectibles
echo "\n2. GET /cashier/collectibles?status=all\n";
curl_setopt($ch, CURLOPT_URL, "$baseUrl/collectibles?status=all");
$response = curl_exec($ch);
if ($response) {
    $data = json_decode($response, true);
    echo "   Status: " . ($data['success'] ? '✓ SUCCESS' : '✗ FAILED') . "\n";
    if ($data['success'] && isset($data['data']['total_balance'])) {
        echo "   Total Balance: ₱" . $data['data']['total_balance'] . "\n";
    }
}

curl_close($ch);
echo "\n✓ Cashier setup complete!\n";
