<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\RentalSpace;

$all = RentalSpace::count();
$available = RentalSpace::available()->count();

echo "All spaces: $all\n";
echo "Available: $available\n";
