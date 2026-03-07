<?php
// Create a simple test of the Contract model
require 'bootstrap/app.php';
require 'vendor/autoload.php';

use App\Models\Contract;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Start the app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

echo "=== TESTING CONTRACT MODEL WITH RELATIONSHIPS ===\n\n";

try {
    // Get contract 14 with relationships
    $contract = Contract::with([
        'tenant.user',
        'rentalSpace',
        'payments'
    ])->findOrFail(14);
    
    echo "Contract found!\n";
    echo "ID: " . $contract->id . "\n";
    echo "Contract Number: " . $contract->contract_number . "\n";
    echo "Tenant exists: " . ($contract->tenant ? "YES" : "NO") . "\n";
    
    if ($contract->tenant) {
        echo "  Tenant ID: " . $contract->tenant->id . "\n";
        echo "  Business Name: " . $contract->tenant->business_name . "\n";
        echo "  Tenant has user: " . ($contract->tenant->user ? "YES" : "NO") . "\n";
    }
    
    echo "Payments count: " . (isset($contract->payments) ? count($contract->payments) : "N/A") . "\n";
    
    // Try to convert to JSON
    echo "\n=== JSON CONVERSION ===\n";
    try {
        $json = json_encode($contract->toArray());
        echo "JSON Size: " . strlen($json) . " bytes\n";
        echo "First 500 chars:\n";
        echo substr($json, 0, 500) . "\n";
    } catch (\Exception $e) {
        echo "JSON conversion failed: " . $e->getMessage() . "\n";
    }
    
    // Try response format
    echo "\n=== RESPONSE FORMAT ===\n";
    $response = [
        'success' => true,
        'data' => $contract
    ];
    
    try {
        $json = json_encode($response);
        echo "Response JSON Size: " . strlen($json) . " bytes\n";
        echo "First 500 chars:\n";
        echo substr($json, 0, 500) . "\n";
    } catch (\Exception $e) {
        echo "Response JSON failed: " . $e->getMessage() . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
