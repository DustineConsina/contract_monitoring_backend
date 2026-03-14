<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "=== Cloudinary Service Test ===\n";
    
    echo "CLOUDINARY_URL: " . (env('CLOUDINARY_URL') ? "SET" : "NOT SET") . "\n";
    
    $service = app('App\Services\CloudinaryService');
    echo "✓ CloudinaryService instantiated successfully\n";
    
    // Try to call a method
    echo "Testing generateUrl method...\n";
    $url = $service->generateUrl('test-public-id');
    echo "✓ Generated test URL: " . substr($url, 0, 50) . "...\n";
    
    echo "\n=== All tests passed ===\n";
    
} catch (\Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Trace:\n";
    echo $e->getTraceAsString() . "\n";
}
