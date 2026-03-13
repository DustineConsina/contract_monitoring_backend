<?php

// Test the dashboard stats values directly
require 'bootstrap/app.php';

use App\Models\RentalSpace;
use App\Models\Contract;

$app = app();

echo "=== DASHBOARD STATS FIX VERIFICATION ===\n\n";

$totalSpaces = RentalSpace::count();
echo "Total Rental Spaces: " . $totalSpaces . "\n";

$occupiedSpaces = RentalSpace::whereHas('contracts', function ($q) {
    $q->where('status', 'active');
})->count();
echo "Spaces with Active Contracts: " . $occupiedSpaces . "\n";

$availableSpaces = RentalSpace::whereDoesntHave('contracts', function ($q) {
    $q->where('status', 'active');
})->count();
echo "Spaces without Active Contracts: " . $availableSpaces . "\n";

echo "\nSpace Utilization by Type:\n";
echo "Food Stall: ";
$foodStallTotal = RentalSpace::where('space_type', 'food_stall')->count();
$foodStallOccupied = RentalSpace::where('space_type', 'food_stall')->whereHas('contracts', function ($q) {
    $q->where('status', 'active');
})->count();
echo $foodStallOccupied . " occupied / " . $foodStallTotal . " total\n";

echo "Market Hall: ";
$hallTotal = RentalSpace::where('space_type', 'market_hall')->count();
$hallOccupied = RentalSpace::where('space_type', 'market_hall')->whereHas('contracts', function ($q) {
    $q->where('status', 'active');
})->count();
echo $hallOccupied . " occupied / " . $hallTotal . " total\n";

echo "Bañera Warehouse: ";
$warehouseTotal = RentalSpace::where('space_type', 'banera_warehouse')->count();
$warehouseOccupied = RentalSpace::where('space_type', 'banera_warehouse')->whereHas('contracts', function ($q) {
    $q->where('status', 'active');
})->count();
echo $warehouseOccupied . " occupied / " . $warehouseTotal . " total\n";

echo "\n✅ Dashboard should show: " . $availableSpaces . " available, " . $occupiedSpaces . " occupied (Total: " . $totalSpaces . ")\n";
