<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;

// Get all tenants with user data
$tenants = Tenant::with('user')->get();

echo "Total Tenants: " . $tenants->count() . "\n";
echo "======================================\n\n";

foreach ($tenants as $tenant) {
    echo "Tenant ID: {$tenant->id}\n";
    echo "Tenant Code: {$tenant->tenant_code}\n";
    echo "Contact Person: {$tenant->contact_person}\n";
    echo "Business Name: {$tenant->business_name}\n";
    if ($tenant->user) {
        echo "Associated User ID: {$tenant->user_id}\n";
        echo "User Name: {$tenant->user->name}\n";
        echo "User Email: {$tenant->user->email}\n";
    } else {
        echo "❌ No associated user!\n";
    }
    echo "---\n\n";
}
?>
