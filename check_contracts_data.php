<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Contract;

// Get all contracts with relationships
$contracts = Contract::with(['tenant', 'rentalSpace'])->get();

echo "Total Contracts: " . $contracts->count() . "\n";
echo "======================================\n\n";

foreach ($contracts as $contract) {
    echo "Contract ID: {$contract->id}\n";
    echo "Contract Number: {$contract->contract_number}\n";
    echo "Tenant ID (from contract): {$contract->tenant_id}\n";
    if ($contract->tenant) {
        echo "Tenant Contact Person: {$contract->tenant->contact_person}\n";
        echo "Tenant Associated User ID: {$contract->tenant->user_id}\n";
    } else {
        echo "❌ No tenant associated!\n";
    }
    if ($contract->rentalSpace) {
        echo "Rental Space: {$contract->rentalSpace->space_code} - {$contract->rentalSpace->name}\n";
    }
    echo "Status: {$contract->status}\n";
    echo "---\n\n";
}
?>
