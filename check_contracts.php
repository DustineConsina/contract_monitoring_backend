<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Contract;

// Get all contracts
$contracts = Contract::select('id', 'contract_number', 'status')->get();

echo "Total Contracts: " . $contracts->count() . "\n";
echo "======================================\n";

foreach ($contracts as $contract) {
    echo "ID: {$contract->id} | Number: {$contract->contract_number} | Status: {$contract->status}\n";
}

echo "\n\nTo test QR scanning, use contract ID: " . ($contracts->first()?->id ?? 'NO CONTRACTS FOUND') . "\n";
?>
