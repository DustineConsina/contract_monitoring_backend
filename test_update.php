<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use App\Models\Contract;

// Test updating a tenant
echo "=== Testing Tenant Update ===\n";
$tenant = Tenant::first();

if ($tenant) {
    echo "Tenant ID: {$tenant->id}\n";
    echo "Current business_name: {$tenant->business_name}\n";
    
    try {
        $tenant->update([
            'business_name' => 'Updated Business Name ' . time()
        ]);
        echo "✓ Tenant updated successfully\n";
        echo "New business_name: {$tenant->business_name}\n";
    } catch (\Exception $e) {
        echo "✗ Error updating tenant: {$e->getMessage()}\n";
        echo "File: {$e->getFile()}\n";
        echo "Line: {$e->getLine()}\n";
    }
} else {
    echo "No tenants found\n";
}

echo "\n=== Testing Contract Update ===\n";
$contract = Contract::first();

if ($contract) {
    echo "Contract ID: {$contract->id}\n";
    echo "Current monthly_rental: {$contract->monthly_rental}\n";
    
    try {
        $contract->update([
            'monthly_rental' => 5500
        ]);
        echo "✓ Contract updated successfully\n";
        echo "New monthly_rental: {$contract->monthly_rental}\n";
    } catch (\Exception $e) {
        echo "✗ Error updating contract: {$e->getMessage()}\n";
        echo "File: {$e->getFile()}\n";
        echo "Line: {$e->getLine()}\n";
    }
} else {
    echo "No contracts found\n";
}
?>
