<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Contract;
use App\Models\Tenant;
use App\Models\RentalSpace;

echo "\n=== CONTRACT DATA CHECK ===\n\n";

$tenantCount = Tenant::count();
$spaceCount = RentalSpace::count();
echo "Total Tenants: {$tenantCount}\n";
echo "Total Rental Spaces: {$spaceCount}\n\n";

// Get contracts with relationships
$contracts = Contract::with(['tenant', 'rentalSpace'])->get();

echo "Total Contracts: " . $contracts->count() . "\n";
echo "======================================\n\n";

foreach ($contracts->take(10) as $contract) {
    echo "Contract: {$contract->contract_number} (ID: {$contract->id})\n";
    echo "  Tenant ID: " . ($contract->tenant_id ?? 'NULL');
    echo " | Has Tenant: " . ($contract->tenant ? 'YES' : 'NO');
    echo " | Name: " . ($contract->tenant?->business_name ?? 'N/A') . "\n";
    
    echo "  Space ID: " . ($contract->rental_space_id ?? 'NULL');
    echo " | Has Space: " . ($contract->rentalSpace ? 'YES' : 'NO');
    echo " | Name: " . ($contract->rentalSpace?->name ?? 'N/A') . "\n";
    echo "\n";
}

echo "\nChecking contract CON-2026-000002:\n";
$contract = Contract::where('contract_number', 'CON-2026-000002')->with(['tenant', 'rentalSpace'])->first();
if ($contract) {
    echo "Found: ID {$contract->id}\n";
    echo "  Tenant ID in DB: " . ($contract->tenant_id ?? 'NULL') . "\n";
    echo "  Tenant relationship: " . ($contract->tenant ? 'LOADED' : 'NULL') . "\n";
    if ($contract->tenant) {
        echo "    - Name: " . $contract->tenant->business_name . "\n";
        echo "    - Contact: " . $contract->tenant->contact_person . "\n";
    }
    echo "  Space ID in DB: " . ($contract->rental_space_id ?? 'NULL') . "\n";
    echo "  Space relationship: " . ($contract->rentalSpace ? 'LOADED' : 'NULL') . "\n";
    if ($contract->rentalSpace) {
        echo "    - Name: " . $contract->rentalSpace->name . "\n";
    }
} else {
    echo "Contract not found!\n";
}
?>

