<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\ContractController;

// Create a mock authenticated user (admin)
$user = User::where('email', 'admin@pfda.gov.ph')->first();

if (!$user) {
    echo "Admin user not found\n";
    exit(1);
}

echo "Testing API endpoints as: {$user->email}\n\n";

// Test Tenant Update API
echo "=== Testing Tenant Update API ===\n";
$tenant = Tenant::first();

if ($tenant) {
    $request = Request::create(
        "/api/tenants/{$tenant->id}",
        'PUT',
        [
            'business_name' => 'Updated via API ' . time(),
            'contact_number' => '9876543210'
        ]
    );
    
    $request->setUserResolver(function() use ($user) {
        return $user;
    });
    
    try {
        $controller = new TenantController();
        $response = $controller->update($request, $tenant->id);
        $responseData = $response->getData(true);
        
        if (isset($responseData['success']) && $responseData['success']) {
            echo "✓ Tenant updated successfully\n";
            echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "✗ API returned error:\n";
            echo json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
        }
    } catch (\Exception $e) {
        echo "✗ Exception: {$e->getMessage()}\n";
        echo "File: {$e->getFile()}:{$e->getLine()}\n";
        echo "Stack trace:\n{$e->getTraceAsString()}\n";
    }
}

// Test Contract Update API
echo "\n=== Testing Contract Update API ===\n";
$contract = Contract::first();

if ($contract) {
    $request = Request::create(
        "/api/contracts/{$contract->id}",
        'PUT',
        [
            'monthly_rental' => 6000,
            'interest_rate' => 2.5
        ]
    );
    
    $request->setUserResolver(function() use ($user) {
        return $user;
    });
    
    try {
        $controller = new ContractController();
        $response = $controller->update($request, $contract->id);
        $responseData = $response->getData(true);
        
        if (isset($responseData['success']) && $responseData['success']) {
            echo "✓ Contract updated successfully\n";
            echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "✗ API returned error:\n";
            echo json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
        }
    } catch (\Exception $e) {
        echo "✗ Exception: {$e->getMessage()}\n";
        echo "File: {$e->getFile()}:{$e->getLine()}\n";
        echo "Stack trace:\n{$e->getTraceAsString()}\n";
    }
}
?>
