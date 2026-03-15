<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Http\Request;

echo "=== CHECKING /rental-spaces-available ENDPOINT ===\n\n";

$request = new Request(['per_page' => 1000]);
$controller = app(\App\Http\Controllers\RentalSpaceController::class);
$response = $controller->getAvailableSpaces($request);
$data = json_decode($response->getContent(), true);

echo "Response structure:\n";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

echo "\n=== ANALYSIS ===\n";
echo "Success: " . ($data['success'] ? 'YES' : 'NO') . "\n";

if (isset($data['data']['data'])) {
    echo "Total returned: " . count($data['data']['data']) . "\n";
    echo "Response has data.data: YES\n";
} elseif (isset($data['data'])) {
    echo "Total returned: " . count($data['data']) . "\n";
    echo "Response has data: " . (is_array($data['data']) ? 'YES (array)' : 'NO') . "\n";
} else {
    echo "Response structure: " . print_r($data, true) . "\n";
}
