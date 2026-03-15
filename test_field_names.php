<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Http\Controllers\RentalSpaceController;
use Illuminate\Http\Request;

echo "=== CHECKING EXACT FIELD NAMES ===\n\n";

$controller = new RentalSpaceController();
$request = new Request(['per_page' => 1000]);
$response = $controller->index($request);
$data = json_decode($response->getContent(), true);  
$spaces = $data['data']['data'];

if (count($spaces) > 0) {
    echo "Checking first AVAILABLE space (no active contracts):\n";
    $available = null;
    foreach ($spaces as $s) {
        if (($s['active_contracts_count'] ?? 0) == 0) {
            $available = $s;
            break;
        }
    }
    
    if ($available) {
        echo "Available space: {$available['space_code']}\n";
        echo "Keys containing 'contract':\n";
        foreach ($available as $key => $value) {
            if (stripos($key, 'contract') !== false) {
                $display = is_array($value) ? '[array]' : $value;
                echo "  - $key: $display\n";
            }
        }
    }
    
    echo "\nChecking first OCCUPIED space (has active contracts):\n";
    $occupied = null;
    foreach ($spaces as $s) {
        if (($s['active_contracts_count'] ?? 0) > 0) {
            $occupied = $s;
            break;
        }
    }
    
    if ($occupied) {
        echo "Occupied space: {$occupied['space_code']}\n";
        echo "Keys containing 'contract':\n";
        foreach ($occupied as $key => $value) {
            if (stripos($key, 'contract') !== false) {
                $display = is_array($value) ? '[array]' : $value;
                echo "  - $key: $display\n";
            }
        }
    }
    
    echo "\n=== SIMULATING FRONTEND FILTER (Python-like pseudocode) ===\n";
    echo "Filter checks:\n";
    echo "  space.activeContractsCount ?? space.active_contracts_count ?? 0\n";
    echo "\nFor available space ({$available['space_code']}):\n";
    echo "  activeContractsCount: " . ($available['activeContractsCount'] ?? 'MISSING') . "\n";
    echo "  active_contracts_count: " . ($available['active_contracts_count'] ?? 'MISSING') . "\n";
    echo "  So filter gets: " . (($available['activeContractsCount'] ?? $available['active_contracts_count'] ?? 0)) . "\n";
    echo "  activeCount > 0? " . (($available['activeContractsCount'] ?? $available['active_contracts_count'] ?? 0) > 0 ? 'YES' : 'NO') . "\n";
    echo "  SHOWS IN DROPDOWN? " . (($available['activeContractsCount'] ?? $available['active_contracts_count'] ?? 0) > 0 ? 'NO' : 'YES') . "\n";
    
    echo "\nFor occupied space ({$occupied['space_code']}):\n";
    echo "  activeContractsCount: " . ($occupied['activeContractsCount'] ?? 'MISSING') . "\n";
    echo "  active_contracts_count: " . ($occupied['active_contracts_count'] ?? 'MISSING') . "\n";
    echo "  So filter gets: " . (($occupied['activeContractsCount'] ?? $occupied['active_contracts_count'] ?? 0)) . "\n";
    echo "  activeCount > 0? " . (($occupied['activeContractsCount'] ?? $occupied['active_contracts_count'] ?? 0) > 0 ? 'YES' : 'NO') . "\n";
    echo "  SHOWS IN DROPDOWN? " . (($occupied['activeContractsCount'] ?? $occupied['active_contracts_count'] ?? 0) > 0 ? 'NO' : 'YES') . "\n";
}
?>
