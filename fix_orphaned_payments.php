<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ALL CONTRACTS ===\n";
$contracts = \App\Models\Contract::pluck('id')->toArray();
sort($contracts);
echo "Existing Contract IDs: " . implode(', ', $contracts) . "\n";

echo "\n=== PAYMENTS REFERENCING MISSING CONTRACTS ===\n";
$payments = \App\Models\Payment::get(); // Don't eager load, just get all
$missing = [];
foreach ($payments as $p) {
    if (!in_array($p->contract_id, $contracts)) {
        $missing[$p->contract_id][] = $p->id;
    }
}

if (empty($missing)) {
    echo "✓ All payments reference existing contracts\n";
} else {
    foreach ($missing as $contractId => $paymentIds) {
        echo "Contract ID $contractId is missing but referenced by Payments: " . implode(', ', $paymentIds) . "\n";
    }
    
    echo "\n=== DELETING ORPHANED PAYMENTS ===\n";
    $deleteCount = 0;
    foreach ($missing as $contractId => $paymentIds) {
        foreach ($paymentIds as $paymentId) {
            $payment = \App\Models\Payment::find($paymentId);
            if ($payment) {
                $payment->delete();
                echo "Deleted Payment $paymentId (was referencing non-existent Contract $contractId)\n";
                $deleteCount++;
            }
        }
    }
    echo "\nTotal deleted: $deleteCount orphaned payments\n";
}

echo "\n=== FINAL PAYMENT COUNT ===\n";
$finalCount = \App\Models\Payment::count();
echo "Remaining Payments: $finalCount\n";
