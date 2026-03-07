<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Contract;

// Get contract 3 with relationships
$contract = Contract::with(['tenant.user', 'rentalSpace'])->find(3);

if ($contract) {
    echo "✓ Contract Found!\n";
    echo "Contract ID: " . $contract->id . "\n";
    echo "Contract Number: " . $contract->contract_number . "\n";
    echo "Status: " . $contract->status . "\n\n";
    
    echo "Tenant Check:\n";
    if ($contract->tenant) {
        echo "✓ Tenant loaded\n";
        echo "  Name: " . ($contract->tenant->contact_person ?? 'NULL') . "\n";
        echo "  Business: " . ($contract->tenant->business_name ?? 'NULL') . "\n";
        echo "  User: " . ($contract->tenant->user ? 'YES' : 'NO') . "\n";
    } else {
        echo "✗ Tenant NOT loaded (tenant_id: " . $contract->tenant_id . ")\n";
    }
    
    echo "\nRental Space Check:\n";
    if ($contract->rentalSpace) {
        echo "✓ Rental Space loaded\n";
        echo "  Code: " . ($contract->rentalSpace->space_code ?? 'NULL') . "\n";
        echo "  Type: " . ($contract->rentalSpace->space_type ?? 'NULL') . "\n";
    } else {
        echo "✗ Rental Space NOT loaded (rental_space_id: " . $contract->rental_space_id . ")\n";
    }
} else {
    echo "✗ Contract NOT found!\n";
}
?>
