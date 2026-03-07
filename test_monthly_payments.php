<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Contract;

echo "=== Testing Monthly Payment Generation with 3% Interest ===\n\n";

$contracts = Contract::with('payments')->limit(3)->get();

foreach ($contracts as $contract) {
    echo "Contract: {$contract->contract_number} | Base Rent: ₱{$contract->monthly_rent}\n";
    echo str_repeat('-', 110) . "\n";
    
    $payments = $contract->payments()->orderBy('due_date')->take(5)->get();
    
    foreach ($payments as $i => $payment) {
        $baseRent = $payment->amount_due;
        $interest = $payment->interest_amount ?? 0;
        $total = $payment->total_amount;
        $expectedInterest = ($baseRent * 0.03);
        
        echo "Month " . ($i+1) . ": {$payment->billing_period_start} → {$payment->billing_period_end}\n";
        echo "  Due Date: {$payment->due_date}\n";
        echo "  Base Rent: ₱" . number_format($baseRent, 2) . "\n";
        echo "  Interest (3%): ₱" . number_format($interest, 2) . " (Expected: ₱" . number_format($expectedInterest, 2) . ")\n";
        echo "  Total: ₱" . number_format($total, 2) . "\n";
        echo "  Status: {$payment->status} | Balance: ₱" . number_format($payment->balance, 2) . "\n";
        echo "\n";
    }
    
    echo "\n";
}

echo "✓ Payment schedule test complete!\n";
