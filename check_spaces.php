<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CONTRACT COUNT ===\n";
$contracts = \App\Models\Contract::all();
echo "Total Contracts: " . count($contracts) . "\n";
foreach ($contracts as $c) {
    echo "  - " . $c->contract_number . " (Status: " . $c->status . ", Space ID: " . $c->rental_space_id . ")\n";
}

echo "\n=== RENTAL SPACE STATUS ===\n";
$occupied = \App\Models\RentalSpace::where('status', 'occupied')->count();
$available = \App\Models\RentalSpace::where('status', 'available')->count();
$maintenance = \App\Models\RentalSpace::where('status', 'maintenance')->count();
echo "Occupied: " . $occupied . "\n";
echo "Available: " . $available . "\n";
echo "Maintenance: " . $maintenance . "\n";

echo "\n=== SPACES WITH ACTIVE CONTRACTS ===\n";
$activeContracts = \App\Models\Contract::where('status', 'active')->count();
echo "Active Contracts: " . $activeContracts . "\n";

echo "\n=== OCCUPIED SPACES WITHOUT ACTIVE CONTRACTS ===\n";
$occupiedSpaces = \App\Models\RentalSpace::where('status', 'occupied')->get();
echo "Checking " . count($occupiedSpaces) . " occupied spaces:\n";
$needsReset = 0;
foreach ($occupiedSpaces as $space) {
    $activeCount = \App\Models\Contract::where('rental_space_id', $space->id)
        ->where('status', 'active')
        ->count();
    if ($activeCount == 0) {
        echo "  - Space {$space->id} ({$space->space_code}) - NO ACTIVE CONTRACTS (needs reset)\n";
        $needsReset++;
    }
}
echo "Total spaces needing reset: $needsReset\n";

echo "\n=== RESETTING OCCUPIED SPACES WITHOUT ACTIVE CONTRACTS ===\n";
$reset = \App\Models\RentalSpace::where('status', 'occupied')->get();
foreach ($reset as $space) {
    $activeCount = \App\Models\Contract::where('rental_space_id', $space->id)
        ->where('status', 'active')
        ->count();
    if ($activeCount == 0) {
        $space->status = 'available';
        $space->save();
        echo "Reset: Space {$space->id} ({$space->space_code}) -> available\n";
    }
}

echo "\n=== FINAL STATUS ===\n";
$occupied = \App\Models\RentalSpace::where('status', 'occupied')->count();
$available = \App\Models\RentalSpace::where('status', 'available')->count();
$maintenance = \App\Models\RentalSpace::where('status', 'maintenance')->count();
echo "Occupied: " . $occupied . "\n";
echo "Available: " . $available . "\n";
echo "Maintenance: " . $maintenance . "\n";
