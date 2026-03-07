<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Payment;

// Find payment PAY-2026-000003
$payment = Payment::where('payment_number', 'PAY-2026-000003')->first();

if ($payment) {
    echo "Payment Found!\n";
    echo "Payment ID: {$payment->id}\n";
    echo "Payment Number: {$payment->payment_number}\n";
    echo "Contract ID: {$payment->contract_id}\n";
    echo "Tenant ID: {$payment->tenant_id}\n";
    echo "Amount Due: {$payment->amount_due}\n";
    echo "Balance: {$payment->balance}\n\n";
    
    // Load relationships
    $payment->load(['tenant', 'contract']);
    
    echo "Contract Data:\n";
    if ($payment->contract) {
        echo "✓ Contract loaded\n";
        echo "  Contract Number: {$payment->contract->contract_number}\n";
        echo "  Contract Status: {$payment->contract->status}\n";
    } else {
        echo "✗ Contract NOT found (contract_id: {$payment->contract_id})\n";
    }
    
    echo "\nTenant Data:\n";
    if ($payment->tenant) {
        echo "✓ Tenant loaded\n";
        echo "  Tenant: {$payment->tenant->contact_person}\n";
    } else {
        echo "✗ Tenant NOT found (tenant_id: {$payment->tenant_id})\n";
    }
} else {
    echo "Payment PAY-2026-000003 NOT found\n";
    
    // List all payments
    echo "\nAll Payments:\n";
    $allPayments = Payment::select('id', 'payment_number', 'contract_id', 'tenant_id')->get();
    foreach ($allPayments as $p) {
        echo "  {$p->payment_number} (Contract ID: {$p->contract_id}, Tenant ID: {$p->tenant_id})\n";
    }
}
?>
