<?php

require 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;
use App\Models\DemandLetter;
use Carbon\Carbon;

echo "======================================\n";
echo "Checking Test Data\n";
echo "======================================\n\n";

// Get all overdue payments
$overduePayments = Payment::where('status', 'overdue')->get();

echo "Overdue Payments in Database:\n";
if ($overduePayments->count() === 0) {
    echo "❌ NO OVERDUE PAYMENTS FOUND\n";
} else {
    echo "✓ Found " . $overduePayments->count() . " overdue payment(s):\n";
    foreach ($overduePayments as $payment) {
        echo "\n  Payment ID: {$payment->id}\n";
        echo "  Payment #: {$payment->payment_number}\n";
        echo "  Status: {$payment->status}\n";
        echo "  Balance: ₱{$payment->balance}\n";
        echo "  Contract ID: {$payment->contract_id}\n";
    }
}

echo "\n\nDemand Letters in Database:\n";
$demandLetters = DemandLetter::all();
if ($demandLetters->count() === 0) {
    echo "❌ NO DEMAND LETTERS FOUND\n";
} else {
    echo "✓ Found " . $demandLetters->count() . " demand letter(s):\n";
    foreach ($demandLetters as $letter) {
        echo "\n  Demand Letter ID: {$letter->id}\n";
        echo "  Demand #: {$letter->demand_number}\n";
        echo "  Status: {$letter->status}\n";
        echo "  Payment ID: {$letter->payment_id}\n";
        echo "  Contract ID: {$letter->contract_id}\n";
    }
}

echo "\n\n======================================\n";
echo "API Endpoint Test\n";
echo "======================================\n\n";

// Test the API endpoint structure
if ($demandLetters->count() > 0) {
    $letter = $demandLetters->first();
    echo "Download endpoint for first demand letter:\n";
    echo "GET /api/demand-letters/{$letter->id}/download\n";
    echo "\nFull URL: http://192.168.1.5:8000/api/demand-letters/{$letter->id}/download\n";
}

echo "\n\nTest all payments to see status values:\n";
$allPayments = Payment::limit(5)->get();
foreach ($allPayments as $p) {
    echo "- {$p->payment_number}: status='{$p->status}' (contract: {$p->contract_id})\n";
}

echo "\n";
