<?php

require 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Contract;
use App\Models\DemandLetter;

echo "======================================\n";
echo "Testing Demand Letter API Endpoint\n";
echo "======================================\n\n";

// Get the test contract
$contract = Contract::find(33);

if (!$contract) {
    echo "❌ Contract ID 33 not found!\n";
    exit(1);
}

echo "Contract Found: {$contract->contract_number}\n";
echo "Contract ID: {$contract->id}\n\n";

// Get demand letters for this contract (simulating the API call)
$demandLetters = $contract->demandLetters()
    ->with('payment', 'tenant')
    ->orderBy('issued_date', 'desc')
    ->get()
    ->map(function ($letter) {
        return [
            'id' => $letter->id,
            'demand_number' => $letter->demand_number,
            'issued_date' => $letter->issued_date->format('M d, Y'),
            'due_date' => $letter->due_date->format('M d, Y'),
            'outstanding_balance' => (float) $letter->outstanding_balance,
            'total_amount_demanded' => (float) $letter->total_amount_demanded,
            'status' => $letter->status,
            'sent_date' => $letter->sent_date ? $letter->sent_date->format('M d, Y H:i') : null,
            'days_remaining' => $letter->due_date->diffInDays(\Carbon\Carbon::now()),
        ];
    });

$response = [
    'success' => true,
    'data' => $demandLetters
];

echo "API Response Structure:\n";
echo json_encode($response, JSON_PRETTY_PRINT);

echo "\n\nFrontend will receive:\n";
echo "response.data?.data = " . (count($demandLetters) > 0 ? "✓ Array with " . count($demandLetters) . " items" : "❌ Empty") . "\n";

if (count($demandLetters) > 0) {
    echo "\nFirst Demand Letter:\n";
    echo "- ID: " . $demandLetters[0]['id'] . "\n";
    echo "- Demand #: " . $demandLetters[0]['demand_number'] . "\n";
    echo "- Status: " . $demandLetters[0]['status'] . "\n";
    echo "- Amount Demanded: ₱" . number_format($demandLetters[0]['total_amount_demanded'], 2) . "\n";
}

echo "\n";
