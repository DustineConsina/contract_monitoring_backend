<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING PAYMENTS WITH MISSING CONTRACTS ===\n";
$payments = \App\Models\Payment::with('contract')->get();
echo "Total Payments: " . count($payments) . "\n\n";

foreach ($payments as $p) {
    $contractNum = $p->contract->contract_number ?? 'NULL';
    $contractId = $p->contract_id ?? 'NULL';
    echo "Payment {$p->id}: contract_id=$contractId, contract_number=$contractNum\n";
    if (!$p->contract) {
        echo "  ⚠️ NO CONTRACT RELATIONSHIP FOUND!\n";
    }
}

echo "\n=== PAYMENTS WITH NULL contract_id ===\n";
$nullReferences = \App\Models\Payment::whereNull('contract_id')->get();
echo "Found: " . count($nullReferences) . " payments with NULL contract_id\n";
foreach ($nullReferences as $p) {
    echo "  - Payment {$p->id}: payment_number={$p->payment_number}\n";
}
