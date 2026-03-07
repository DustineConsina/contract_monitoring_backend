<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Contract;

$contract = Contract::with(['tenant.user', 'rentalSpace', 'payments'])->first();

if ($contract) {
    echo "Contract ID: {$contract->id}\n";
    echo "Contract Number: {$contract->contract_number}\n\n";
    
    echo "=== TENANT DATA ===\n";
    if ($contract->tenant) {
        echo "  business_name: " . ($contract->tenant->business_name ?? 'NULL') . "\n";
        echo "  business_type: " . ($contract->tenant->business_type ?? 'NULL') . "\n";
        echo "  tin: " . ($contract->tenant->tin ?? 'NULL') . "\n";
        echo "  business_address: " . ($contract->tenant->business_address ?? 'NULL') . "\n";
        echo "  contact_number: " . ($contract->tenant->contact_number ?? 'NULL') . "\n";
        
        if ($contract->tenant->user) {
            echo "  user.name: " . ($contract->tenant->user->name ?? 'NULL') . "\n";
        }
    }
    
    echo "\n=== PAYMENTS ===\n";
    echo "Payment count: " . $contract->payments->count() . "\n";
    if ($contract->payments->count() > 0) {
        $payment = $contract->payments->first();
        echo "First payment:\n";
        echo "  payment_number: {$payment->payment_number}\n";
        echo "  amount_due: {$payment->amount_due}\n";
        echo "  interest_amount: {$payment->interest_amount}\n";
        echo "  total_amount: {$payment->total_amount}\n";
        echo "  amount_paid: {$payment->amount_paid}\n";
        echo "  status: {$payment->status}\n";
    }
} else {
    echo "No contracts found\n";
}
