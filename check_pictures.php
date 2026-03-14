<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenants = \App\Models\Tenant::all();

echo "=== TENANT PROFILE PICTURES ===\n";
foreach ($tenants as $tenant) {
    $pic = $tenant->profile_picture ?? '(NO PICTURE)';
    echo "ID: {$tenant->id}, Picture: {$pic}\n";
    
    if ($tenant->profile_picture) {
        $path = storage_path('app/public/' . $tenant->profile_picture);
        $exists = file_exists($path);
        echo "  → File exists: " . ($exists ? 'YES' : 'NO') . "\n";
        echo "  → Path: {$path}\n";
    }
}
