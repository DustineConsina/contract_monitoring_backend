<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\RentalSpace;
use Illuminate\Http\Request;

echo "=== TESTING /rental-spaces-available ENDPOINT ===\n\n";

// Create a request object
$request = new Request(['per_page' => 1000]);

// Get the controller
$controller = app(\App\Http\Controllers\RentalSpaceController::class);

// Call the method directly
$response = $controller->getAvailableSpaces($request);
$data = json_decode($response->getContent(), true);

echo "Endpoint Response:\n";
echo "Success: " . ($data['success'] ? 'YES' : 'NO') . "\n";
echo "Total returned: " . count($data['data']['data']) . "\n";
echo "Per page from meta: " . $data['data']['meta']['per_page'] . "\n";
echo "Total in database: " . $data['data']['meta']['total'] . "\n\n";

// Count available vs occupied in response
$available = 0;
$occupied = [];

foreach ($data['data']['data'] as $space) {
    $activeCount = $space['active_contracts_count'] ?? 0;
    if ($activeCount == 0) {
        $available++;
    } else {
        $occupied[] = $space['space_code'];
    }
}

echo "Analysis of returned spaces:\n";
echo "Available (activeCount=0): $available\n";
echo "Occupied (activeCount>0): " . count($occupied) . "\n";
if (count($occupied) > 0) {
    echo "Occupied spaces: " . implode(', ', $occupied) . "\n";
}

// Also check the direct query
echo "\n=== DIRECT QUERY TEST ===\n";
$allCount = RentalSpace::count();
$availCount = RentalSpace::available()->count();

echo "All spaces: $allCount\n";
echo "Available (scope): $availCount\n";
