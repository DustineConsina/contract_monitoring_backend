<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

use App\Models\Contract;

// Get the contract ID from command line or as parameter
$contractId = $argv[1] ?? null;

if (!$contractId) {
    echo "Usage: php check_contract.php <contract_id>\n";
    exit(1);
}

$contract = Contract::with(['tenant.user', 'rentalSpace'])->find($contractId);

if (!$contract) {
    echo "Contract #{$contractId} not found\n";
    exit(1);
}

echo "=== Contract Details ===\n";
echo "ID: {$contract->id}\n";
echo "Number: {$contract->contract_number}\n";
echo "Status: '{$contract->status}' (length: " . strlen($contract->status) . ")\n";
echo "Status bytes: " . bin2hex($contract->status) . "\n";
echo "Tenant: {$contract->tenant->business_name}\n";
echo "Space: {$contract->rentalSpace->name}\n";
echo "\nFull record:\n";
print_r($contract->toArray());
