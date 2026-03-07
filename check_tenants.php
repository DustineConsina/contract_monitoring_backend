<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING TENANT DATA ===\n\n";
$tenants = \App\Models\Tenant::with('user')->get();

foreach ($tenants as $t) {
    echo "Tenant ID: {$t->id}\n";
    echo "  Contact Person: {$t->contact_person}\n";
    echo "  Business Name: " . ($t->business_name ? $t->business_name : 'NULL') . "\n";
    echo "  TIN: " . ($t->tin ? $t->tin : 'NULL') . "\n";
    echo "  Tenant Code: {$t->tenant_code}\n";
    echo "  Business Type: " . ($t->business_type ? $t->business_type : 'NULL') . "\n";
    echo "  Business Address: " . ($t->business_address ? $t->business_address : 'NULL') . "\n";
    echo "  User Name: " . ($t->user->name ?? 'NULL') . "\n";
    echo "  User Email: " . ($t->user->email ?? 'NULL') . "\n";
    echo "\n";
}
