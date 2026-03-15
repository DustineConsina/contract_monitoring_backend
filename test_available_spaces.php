<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\RentalSpace;

echo "=== RENTAL SPACE STATUS CHECK ===\n\n";

$totalSpaces = RentalSpace::count();
echo "Total spaces: " . $totalSpaces . "\n";

echo "\n=== SPACES WITH ACTIVE CONTRACTS (Occupied) ===\n";
$occupiedSpaces = RentalSpace::whereHas('contracts', function ($q) {
    $q->where('status', 'active');
})->get();
echo "Occupied count: " . $occupiedSpaces->count() . "\n";
foreach ($occupiedSpaces as $space) {
    $contracts = $space->contracts()->where('status', 'active')->get();
    echo "  - {$space->space_code} (ID: {$space->id}, status: {$space->status})\n";
    foreach ($contracts as $contract) {
        echo "    Active contract: {$contract->contract_number}\n";
    }
}

echo "\n=== SPACES WITHOUT ACTIVE CONTRACTS (Available) ===\n";
$availableSpaces = RentalSpace::whereDoesntHave('contracts', function ($q) {
    $q->where('status', 'active');
})->get();
echo "Available count: " . $availableSpaces->count() . "\n";
foreach ($availableSpaces->take(10) as $space) {
    $contractCount = $space->contracts()->count();
    echo "  - {$space->space_code} (ID: {$space->id}, status: {$space->status}, all contracts: {$contractCount})\n";
}

echo "\n=== API ENDPOINT TEST ===\n";
$controller = new \App\Http\Controllers\RentalSpaceController();
$request = new \Illuminate\Http\Request();
$response = $controller->getAvailableSpaces($request);
$data = json_decode($response->getContent(), true);
echo "Endpoint returned: " . ($data['data'] ? count($data['data']) : 0) . " spaces\n";
?>
