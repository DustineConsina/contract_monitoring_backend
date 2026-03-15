<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\RentalSpace;
use App\Models\Contract;

echo "=== TESTING AVAILABLE SPACES QUERY ===\n\n";

// Test 1: All spaces
$allSpaces = RentalSpace::count();
echo "1. ALL SPACES: $allSpaces\n";

// Test 2: Using scopes
$availableCount = RentalSpace::available()->count();
echo "2. AVAILABLE (using scope): $availableCount\n";

// Test 3: Using whereDoesntHave directly
$availableManual = RentalSpace::whereDoesntHave('contracts', function ($q) {
    $q->where('status', 'active');
})->count();
echo "3. AVAILABLE (manual whereDoesntHave): $availableManual\n";

// Test 4: Occupied spaces
$occupiedCount = RentalSpace::occupied()->count();
echo "4. OCCUPIED (using scope): $occupiedCount\n";

// Test 5: Show occupied spaces
echo "\n5. OCCUPIED SPACES:\n";
$occupied = RentalSpace::occupied()->get();
foreach ($occupied as $space) {
    echo "   - {$space->space_code}\n";
}

// Test 6: Get first 5 available
echo "\n6. FIRST 5 AVAILABLE SPACES:\n";
$available = RentalSpace::available()->limit(5)->get();
foreach ($available as $space) {
    echo "   - {$space->space_code}\n";
}
