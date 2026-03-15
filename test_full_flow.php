<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Http\Controllers\RentalSpaceController;
use Illuminate\Http\Request;

echo "=== FULL DATA FLOW SIMULATION ===\n\n";

// 1. Get the raw API response
$controller = new RentalSpaceController();
$request = new Request(['per_page' => 1000]);
$response = $controller->index($request);
$jsonResponse = $response->getContent();
$data = json_decode($jsonResponse, true);

echo "Step 1: Raw API Response\n";
echo "Total spaces returned: " . count($data['data']['data']) . "\n";

// 2. Simulate API client response (no transformation for this test)
$spaces = $data['data']['data'];

echo "\nStep 2: Spaces in data\n";
$occupiedExample = null;
foreach ($spaces as $space) {
    if ($space['active_contracts_count'] > 0) {
        $occupiedExample = $space;
        break;
    }
}

if ($occupiedExample) {
    echo "Example occupied space: " . $occupiedExample['space_code'] . "\n";
    echo "  - active_contracts_count: " . $occupiedExample['active_contracts_count'] . "\n";
    echo "  - is_occupied: " . ($occupiedExample['is_occupied'] ? 'true' : 'false') . "\n";
    echo "  - occupancy_status: " . $occupiedExample['occupancy_status'] . "\n";
}

// 3. Simulate frontend filtering (JavaScript code)
echo "\nStep 3: Frontend Filter Logic (JavaScript Simulation)\n";

$totalSpaces = count($spaces);
$availableCount = 0;
$occupiedCount = 0;

foreach ($spaces as $space) {
    // This is the actual filter from frontend:
    // const hasActiveContract = (space.activeContractsCount ?? space.active_contracts_count ?? 0) > 0
    // return !hasActiveContract
    
    // In snake_case (before transformation):
    $activeContractsCount = $space['active_contracts_count'] ?? 0;
    $hasActiveContract = $activeContractsCount > 0;
    
    if (!$hasActiveContract) {
        $availableCount++;
    } else {
        $occupiedCount++;
    }
}

echo "Total spaces: $totalSpaces\n";
echo "Available (should show in dropdown): $availableCount\n";
echo "Occupied (should NOT show in dropdown): $occupiedCount\n";

echo "\nExpected behavior:\n";
echo "✓ Dropdown should show $availableCount spaces\n";
echo "✓ Occupied spaces ($occupiedCount) should be filtered out\n";
?>
