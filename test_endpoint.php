<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

// Set up the kernel
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Create fake request and test the controller directly
use App\Http\Controllers\RentalSpaceController;
use Illuminate\Http\Request;

$controller = new RentalSpaceController();
$request = new Request();

// Make the request
$response = $controller->getAvailableSpaces($request);
$data = json_decode($response->getContent(), true);

echo "Testing /api/rental-spaces-available endpoint response:\n\n";

if ($data) {
    echo "Response structure:\n";
    echo "- success: " . ($data['success'] ? 'yes' : 'no') . "\n";
    echo "- data is array: " . (is_array($data['data']) ? 'yes' : 'no') . "\n";
    echo "- data count: " . (is_array($data['data']) ? count($data['data']) : 'N/A') . "\n\n";
    
    if (is_array($data['data']) && count($data['data']) > 0) {
        echo "Total available spaces: " . count($data['data']) . "\n\n";
        echo "Sample space:\n";
        $first = (array)$data['data'][0];
        foreach ($first as $key => $value) {
            $display = $value;
            if (is_array($value)) {
                $display = '[array with ' . count($value) . ' items]';
            } elseif (is_object($value)) {
                $display = '[object]';
            } elseif (strlen($display) > 50) {
                $display = substr($display, 0, 50) . '...';
            }
            echo "  {$key}: {$display}\n";
        }
        
        echo "\n✅ First 5 spaces:\n";
        for ($i = 0; $i < min(5, count($data['data'])); $i++) {
            $space = (array)$data['data'][$i];
            echo "  " . ($i+1) . ". {$space['space_code']} - {$space['name']}\n";
        }
    } else {
        echo "⚠️ WARNING: No spaces returned!\n";
    }
} else {
    echo "ERROR: Could not decode response\n";
}
?>
