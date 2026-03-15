<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Http\Controllers\RentalSpaceController;
use Illuminate\Http\Request;

echo "=== TESTING /rental-spaces RESPONSE ===\n\n";

$controller = new RentalSpaceController();
$request = new Request();
$response = $controller->index($request);
$data = json_decode($response->getContent(), true);

echo "Response structure:\n";
echo "- success: " . ($data['success'] ? 'yes' : 'no') . "\n";
echo "- Has 'data' key: " . (isset($data['data']) ? 'yes' : 'no') . "\n";

// Check if it's paginated
if (isset($data['data']['data'])) {
    echo "- Response is PAGINATED (has data.data structure)\n";
    $spaces = $data['data']['data'];
    echo "- Number of spaces: " . count($spaces) . "\n";
    echo "- Pagination meta: " . json_encode($data['data']['meta'] ?? 'no meta') . "\n";
} elseif (isset($data['data']) && is_array($data['data'])) {
    $spaces = $data['data'];
    echo "- Response is FLAT ARRAY\n";
    echo "- Number of spaces: " . count($spaces) . "\n";
} else {
    die("ERROR: Unexpected response structure");
}

if (count($spaces) > 0) {
    echo "\n=== FIRST SPACE STRUCTURE ===\n";
    $first = (array)$spaces[0];
    
    echo "Keys in first space:\n";
    foreach ($first as $key => $value) {
        $display = $value;
        if (is_array($value)) {
            $display = '[array]';
        } elseif (is_object($value)) {
            $display = '[object]';
        } elseif (strlen((string)$value) > 50) {
            $display = substr((string)$value, 0, 50) . '...';
        }
        echo "  - $key: $display\n";
    }
    
    echo "\n=== CHECKING ACTIVE CONTRACTS COUNT ===\n";
    echo "activeContractsCount: " . ($first['activeContractsCount'] ?? $first['active_contracts_count'] ?? 'NOT FOUND') . "\n";
    echo "is_occupied: " . ($first['is_occupied'] ?? 'NOT FOUND') . "\n";
    echo "occupancy_status: " . ($first['occupancy_status'] ?? 'NOT FOUND') . "\n";
    
    echo "\n=== CHECKING ALL SPACES FOR OCCUPIED ===\n";
    $occupiedCount = 0;
    $availableCount = 0;
    
    foreach ($spaces as $space) {
        $space = (array)$space;
        $activeCount = $space['activeContractsCount'] ?? $space['active_contracts_count'] ?? 0;
        if ($activeCount > 0) {
            $occupiedCount++;
            echo "  OCCUPIED: {$space['space_code']} - active contracts: {$activeCount}\n";
        } else {
            $availableCount++;
        }
    }
    
    echo "\nTotal Spaces: " . count($spaces) . "\n";
    echo "Available: " . $availableCount . "\n";
    echo "Occupied: " . $occupiedCount . "\n";
}
?>
