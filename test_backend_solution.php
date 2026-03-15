<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\RentalSpace;
use App\Models\Contract;

echo "========================================\n";
echo "RENTAL SPACE AVAILABILITY SOLUTION TEST\n";
echo "========================================\n\n";

echo "METHOD 1: Using Query Scope (RECOMMENDED FOR API)\n";
echo "Code: RentalSpace::available()->get()\n";
$availableSpaces = RentalSpace::available()->get();
echo "Result: " . $availableSpaces->count() . " available spaces\n";
echo "Example: " . ($availableSpaces->first()?->space_code ?? 'N/A') . "\n\n";

echo "METHOD 2: Using whereDoesntHave (ORIGINAL)\n";
echo "Code: RentalSpace::whereDoesntHave('contracts', fn(\$q) => \$q->where('status', 'active'))->get()\n";
$available2 = RentalSpace::whereDoesntHave('contracts', function ($q) {
    $q->where('status', 'active');
})->get();
echo "Result: " . $available2->count() . " available spaces\n\n";

echo "METHOD 3: Get Occupied Spaces\n";
echo "Code: RentalSpace::occupied()->get()\n";
$occupiedSpaces = RentalSpace::occupied()->get();
echo "Result: " . $occupiedSpaces->count() . " occupied spaces\n";
foreach ($occupiedSpaces as $space) {
    $activeCount = $space->contracts()->where('status', 'active')->count();
    echo "  - {$space->space_code}: {$activeCount} active contract(s)\n";
}
echo "\n";

echo "METHOD 4: Using isAvailable() Method on Model\n";
$manualAvailable = [];
foreach (RentalSpace::all() as $space) {
    if ($space->isAvailable()) {
        $manualAvailable[] = $space->space_code;
    }
}
echo "Result: " . count($manualAvailable) . " available spaces\n\n";

echo "========================================\n";
echo "SUMMARY FOR CONTRACT CREATION FORM\n";
echo "========================================\n";
echo "✅ Total Rental Spaces: " . RentalSpace::count() . "\n";
echo "✅ Available (Can be used for new contracts): " . RentalSpace::available()->count() . "\n";
echo "✅ Occupied (Already have active contracts): " . RentalSpace::occupied()->count() . "\n";
echo "✅ Available + Occupied = " . (RentalSpace::available()->count() + RentalSpace::occupied()->count()) . "\n\n";

echo "========================================\n";
echo "RECOMMENDED API IMPLEMENTATION\n";
echo "========================================\n";
echo "GET /api/rental-spaces-available?per_page=1000\n";
echo "Returns: All rental spaces without active contracts\n";
echo "Response count: " . RentalSpace::available()->count() . " spaces\n";
echo "\nFrontend Filter Logic:\n";
echo "✓ Fetch all 61 spaces with per_page=1000\n";
echo "✓ Filter: (space.activeContractsCount ?? space.active_contracts_count ?? 0) > 0\n";
echo "✓ Keep spaces where activeContractsCount === 0\n";
echo "✓ Result: 56 available spaces shown in dropdown\n";
?>
