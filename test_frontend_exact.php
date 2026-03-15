<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== TESTING EXACT FRONTEND SCENARIO ===\n\n";

// 1. Test what apiClient.getRentalSpaces({ per_page: 1000 }) does
// The frontend sends: GET /rental-spaces?per_page=1000

use App\Http\Controllers\RentalSpaceController;
use Illuminate\Http\Request;

echo "Test 1: getRentalSpaces() with per_page=1000\n";
$controller = new RentalSpaceController();
$request = new Request(['per_page' => 1000]);
$response = $controller->index($request);
$data = json_decode($response->getContent(), true);

echo "Response success: " . ($data['success'] ? 'YES' : 'NO') . "\n";
echo "Total spaces in response: " . count($data['data']['data']) . "\n";

// Check the structure
if (isset($data['data']['data'])) {
    echo "Response structure: data.data[...] (PAGINATED)\n";
} elseif (isset($data['data'])) {
    echo "Response structure: data[...] (FLAT)\n";
}

// Check what's in each space
$spaces = $data['data']['data'] ?? $data['data'] ?? [];
echo "First space keys:\n";
if (count($spaces) > 0) {
    foreach (array_keys((array)$spaces[0]) as $key) {
        echo "  - $key\n";
    }
}

// Now simulate the frontend extraction
echo "\nTest 2: Frontend Extraction Logic\n";

$allSpacesArray = $spaces; // This is what frontend extracts
echo "allSpacesArray = spacesData.data?.data || spacesData.data || []\n";
echo "allSpacesArray.length = " . count($allSpacesArray) . "\n";

// The frontend filter
$availableSpaces = [];
$occupiedSpaces = [];

foreach ($allSpacesArray as $space) {
    $space = (array)$space;
    // Frontend code: (space.activeContractsCount ?? space.active_contracts_count ?? 0) > 0
    $activeCount = $space['activeContractsCount'] ?? $space['active_contracts_count'] ?? $space['active_contracts_count'] ?? 0;
    
    if ($activeCount > 0) {
        $occupiedSpaces[] = $space['space_code'];
    } else {
        $availableSpaces[] = $space['space_code'];
    }
}

echo "\nTest 3: Filter Results\n";
echo "Available count: " . count($availableSpaces) . "\n";
echo "Occupied count: " . count($occupiedSpaces) . "\n";
echo "Total: " . (count($availableSpaces) + count($occupiedSpaces)) . "\n";

if (count($occupiedSpaces) > 0) {
    echo "\nOccupied spaces (should be HIDDEN): " . implode(', ', array_slice($occupiedSpaces, 0, 5)) . "\n";
}

if (count($availableSpaces) > 0) {
    echo "Available spaces (should be SHOWN): " . implode(', ', array_slice($availableSpaces, 0, 5)) . "\n";
}

// Check if maybe the spaces are being sorted differently
echo "\nTest 4: Checking Spaces Array Order\n";
echo "First 5 spaces in API response:\n";
for ($i = 0; $i < min(5, count($spaces)); $i++) {
    $space = (array)$spaces[$i];
    $activeCount = $space['active_contracts_count'] ?? 0;
    $status = $activeCount > 0 ? '🔴 OCCUPIED' : '🟢 AVAILABLE';
    echo "  $i. {$space['space_code']} - $status\n";
}
?>
