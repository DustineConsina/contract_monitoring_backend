<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\RentalSpace;

echo "=== TESTING SCOPE APPLICATION ===\n\n";

// Direct scope test
$allSpaces = RentalSpace::count();
$availableWithScope = RentalSpace::available()->count();

echo "Total spaces: $allSpaces\n";
echo "Available (using scope): $availableWithScope\n\n";

// Check if any available spaces have contracts
$withContractsCount = 0;
$available = RentalSpace::available()->get();
foreach ($available as $space) {
    if ($space->contracts->count() > 0) {
        echo "WARNING: {$space->space_code} is marked available but has " . $space->contracts->count() . " contracts\n";
        foreach ($space->contracts as $contract) {
            echo "  - Contract #{$contract->id}: status = {$contract->status}\n";
        }
        $withContractsCount++;
    }
}

echo "\nTotal available spaces with contracts: $withContractsCount\n";

if ($withContractsCount > 0) {
    echo "\n❌ PROBLEM: Some available spaces have non-active contracts!\n";
    echo "The scope is only filtering out ACTIVE contracts, not PENDING ones.\n";
} else {
    echo "\n✅ All available spaces have no contracts.\n";
}
