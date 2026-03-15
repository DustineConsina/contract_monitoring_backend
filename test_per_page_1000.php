<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Http\Controllers\RentalSpaceController;
use Illuminate\Http\Request;

echo "=== TESTING per_page=1000 RESPONSE ===\n\n";

$controller = new RentalSpaceController();
$request = new Request(['per_page' => 1000]);
$response = $controller->index($request);
$data = json_decode($response->getContent(), true);

echo "Response with per_page=1000:\n";
echo "- success: " . ($data['success'] ? 'yes' : 'no') . "\n";

if (isset($data['data']['data'])) {
    $spaces = $data['data']['data'];
    echo "- Number of spaces returned: " . count($spaces) . "\n";
    echo "- Per page from meta: " . ($data['data']['per_page'] ?? 'N/A') . "\n";
    echo "- Current page: " . ($data['data']['current_page'] ?? 'N/A') . "\n";
    echo "- Total: " . ($data['data']['total'] ?? 'N/A') . "\n";
    
    echo "\n=== CHECKING ACTIVE_CONTRACTS_COUNT IN RESPONSE ===\n";
    
    $occupiedCount = 0;
    $availableCount = 0;
    
    echo "First 10 spaces:\n";
    for ($i = 0; $i < min(10, count($spaces)); $i++) {
        $space = $spaces[$i];
        $activeCount = $space['active_contracts_count'] ?? 'MISSING';
        if ($activeCount === 'MISSING') {
            echo "  $i. {$space['space_code']}: active_contracts_count = MISSING ❌\n";
        } else {
            echo "  $i. {$space['space_code']}: active_contracts_count = {$activeCount}\n";
            if ($activeCount > 0) {
                $occupiedCount++;
            } else {
                $availableCount++;
            }
        }
    }
    
    echo "\nTotal count check:\n";
    foreach ($spaces as $space) {
        if (($space['active_contracts_count'] ?? 0) > 0) {
            $occupiedCount++;
        } else {
            $availableCount++;
        }
    }
    
    echo "Total spaces: " . count($spaces) . "\n";
    echo "Available (activeCount=0): " . $availableCount . "\n";
    echo "Occupied (activeCount>0): " . $occupiedCount . "\n";
}
?>
