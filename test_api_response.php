<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Contract;

// Test what the API returns (simulating the controller)
$contract = Contract::with(['tenant.user', 'rentalSpace', 'payments'])->first();

if ($contract) {
    // This is what gets returned to the frontend
    $response = [
        'success' => true,
        'data' => $contract->toArray()
    ];
    
    echo "=== API RESPONSE STRUCTURE ===\n";
    echo "Contract ID: " . $response['data']['id'] . "\n";
    echo "Has tenant: " . (isset($response['data']['tenant']) ? 'YES' : 'NO') . "\n";
    
    if (isset($response['data']['tenant'])) {
        echo "  - business_name: " . ($response['data']['tenant']['business_name'] ?? 'NULL') . "\n";
        echo "  - business_type: " . ($response['data']['tenant']['business_type'] ?? 'NULL') . "\n";
    }
    
    echo "\nHas payments array: " . (isset($response['data']['payments']) ? 'YES' : 'NO') . "\n";
    if (isset($response['data']['payments'])) {
        echo "  - Payment count: " . count($response['data']['payments']) . "\n";
        if (count($response['data']['payments']) > 0) {
            $firstPayment = $response['data']['payments'][0];
            echo "  - First payment fields:\n";
            echo "    * payment_number: " . ($firstPayment['payment_number'] ?? 'NULL') . "\n";
            echo "    * amount_due: " . ($firstPayment['amount_due'] ?? 'NULL') . "\n";
            echo "    * interest_amount: " . ($firstPayment['interest_amount'] ?? 'NULL') . "\n";
            echo "    * total_amount: " . ($firstPayment['total_amount'] ?? 'NULL') . "\n";
        }
    } else {
        echo "  ⚠️  PAYMENTS ARRAY IS MISSING!\n";
    }
} else {
    echo "No contracts found\n";
}
