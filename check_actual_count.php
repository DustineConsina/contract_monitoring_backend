<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\RentalSpace;
use App\Models\Contract;

echo "=== ACTUAL DATABASE STATE ===\n\n";
echo "Total Rental Spaces: " . RentalSpace::count() . "\n";
echo "Total Contracts: " . Contract::count() . "\n";
echo "Active Contracts: " . Contract::where('status', 'active')->count() . "\n";
