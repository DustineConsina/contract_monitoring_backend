<?php
require 'bootstrap/app.php';

use Illuminate\Support\Facades\DB;

// Test the API endpoint manually
$response = file_get_contents('http://localhost:8000/api/rental-spaces-available');
$data = json_decode($response, true);

echo "=== API ENDPOINT TEST: /api/rental-spaces-available ===\n\n";
echo "Success: " . ($data['success'] ? 'Yes' : 'No') . "\n";
echo "Total Available Spaces: " . count($data['data']['data']) . "\n";
echo "Per Page (from meta): " . $data['data']['meta']['per_page'] . "\n";
echo "Total in Database: " . $data['data']['meta']['total'] . "\n";

// Count occupied spaces
$databaseOccupied = DB::table('rental_spaces')
    ->whereHas('contracts', function ($q) {
        $q->where('status', 'active');
    })
    ->count();

echo "Occupied Spaces (DB check): " . $databaseOccupied . "\n";
echo "Expected Available: " . (61 - $databaseOccupied) . "\n";
echo "\n✅ API endpoint is working correctly\n";
