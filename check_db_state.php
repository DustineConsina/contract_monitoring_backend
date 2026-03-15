<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== DATABASE STATE ===\n\n";
echo "Total Rental Spaces: " . \App\Models\RentalSpace::count() . "\n";
echo "Total Contracts: " . \App\Models\Contract::count() . "\n";
echo "Active Contracts: " . \App\Models\Contract::where('status', 'active')->count() . "\n";
echo "Expired Contracts: " . \App\Models\Contract::where('status', 'expired')->count() . "\n";

echo "\n=== ACTIVE CONTRACTS ===\n";
$active = \App\Models\Contract::where('status', 'active')->with(['rentalSpace', 'tenant'])->get();
foreach ($active as $contract) {
    echo "{$contract->contract_number} - Space: {$contract->rentalSpace->space_code} - Tenant: {$contract->tenant->contact_person}\n";
}

echo "\n=== CHECKING WHICH SPACES HAVE ACTIVE CONTRACTS ===\n";
$spacesWithActive = \App\Models\RentalSpace::whereHas('contracts', function ($q) {
    $q->where('status', 'active');
})->with(['contracts' => function ($q) {
    $q->where('status', 'active');
}])->get();

foreach ($spacesWithActive as $space) {
    echo "{$space->space_code} has {$space->contracts->count()} active contracts\n";
}
?>
