<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use App\Models\Contract;

// Get Jenny's tenant data
$jenny = Tenant::find(1);

echo "Jenny's Tenant Data:\n";
echo "ID: {$jenny->id}\n";
echo "Contact Person: {$jenny->contact_person}\n";
echo "Business Name: {$jenny->business_name}\n";
echo "User ID: {$jenny->user_id}\n";
echo "Status: {$jenny->status}\n\n";

// Check existing contracts for Jenny
$jennyContracts = Contract::where('tenant_id', 1)->get();
echo "Jenny's Existing Contracts: " . $jennyContracts->count() . "\n";
foreach ($jennyContracts as $c) {
    echo "  - Contract ID: {$c->id}, Space: {$c->rental_space_id}, Status: {$c->status}\n";
}

echo "\n\nAvailable Spaces:\n";
$availableSpaces = \App\Models\RentalSpace::where('status', 'AVAILABLE')->get();
echo "Total Available: " . $availableSpaces->count() . "\n";
foreach ($availableSpaces as $space) {
    echo "  - ID: {$space->id}, Code: {$space->space_code}, Name: {$space->name}\n";
}
?>
