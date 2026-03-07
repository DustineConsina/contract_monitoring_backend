<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\TenantController;

// Create a mock authenticated user (admin)
$user = User::where('email', 'admin@pfda.gov.ph')->first();

if (!$user) {
    echo "❌ Admin user not found\n";
    exit(1);
}

echo "===========================================\n";
echo "COMPREHENSIVE TENANT UPDATE TEST\n";
echo "===========================================\n\n";

// Get first tenant
$tenant = Tenant::with('user')->first();

if (!$tenant) {
    echo "❌ No tenants found in database\n";
    exit(1);
}

echo "📋 TEST SETUP\n";
echo "─────────────────────────────────────────\n";
echo "Tenant ID: {$tenant->id}\n";
echo "Current Business Name: {$tenant->business_name}\n";
echo "Current Contact Person: {$tenant->contact_person}\n";
echo "Current User Email: {$tenant->user->email}\n";
echo "Current User Address: {$tenant->user->address}\n";
echo "Current Phone: {$tenant->contact_number}\n\n";

// EXACT DATA THAT FRONTEND SENDS (after camelCase transformation)
$frontendData = [
    'firstName' => 'Updated',
    'lastName' => 'Name',
    'email' => 'newemail' . time() . '@test.com',
    'contactNumber' => '09876543210',
    'address' => 'New Personal Address Line', // PERSONAL ADDRESS (user table)
    'businessName' => 'Updated Business LLC',
    'businessType' => 'corporation',
    'businessAddress' => 'New Business Address Suite 100', // BUSINESS ADDRESS (tenant table)
    'tin' => '999-999-999-999',
];

echo "📤 FRONTEND REQUEST DATA\n";
echo "─────────────────────────────────────────\n";
echo json_encode($frontendData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

// Create mock request (simulating frontend POST/PUT)
$request = Request::create(
    "/api/tenants/{$tenant->id}",
    'PUT',
    $frontendData,
    [],
    [],
    ['HTTP_AUTHORIZATION' => 'Bearer test-token']
);

$request->setUserResolver(function() use ($user) {
    return $user;
});

try {
    echo "🔄 PROCESSING REQUEST\n";
    echo "─────────────────────────────────────────\n";
    
    $controller = new TenantController();
    $response = $controller->update($request, $tenant->id);
    $responseData = $response->getData(true);
    
    echo "Status: " . ($responseData['success'] ? '✅ SUCCESS' : '❌ FAILED') . "\n";
    echo "Message: {$responseData['message']}\n\n";
    
    if (!$responseData['success']) {
        echo "Errors:\n";
        if (isset($responseData['errors'])) {
            foreach ($responseData['errors'] as $field => $messages) {
                echo "  • {$field}: " . implode(', ', (array)$messages) . "\n";
            }
        }
        exit(1);
    }
    
    // Verify the updates
    $tenant->refresh();
    $tenant->load('user');
    
    echo "✅ VERIFICATION - DATABASE VALUES AFTER UPDATE\n";
    echo "─────────────────────────────────────────\n";
    
    $tests = [
        'Business Name' => [
            'expected' => $frontendData['businessName'],
            'actual' => $tenant->business_name,
            'db_field' => 'tenants.business_name'
        ],
        'Business Type' => [
            'expected' => $frontendData['businessType'],
            'actual' => $tenant->business_type,
            'db_field' => 'tenants.business_type'
        ],
        'Business Address' => [
            'expected' => $frontendData['businessAddress'],
            'actual' => $tenant->business_address,
            'db_field' => 'tenants.business_address'
        ],
        'Contact Person (from firstName+lastName)' => [
            'expected' => trim($frontendData['firstName'] . ' ' . $frontendData['lastName']),
            'actual' => $tenant->contact_person,
            'db_field' => 'tenants.contact_person'
        ],
        'Contact Number' => [
            'expected' => $frontendData['contactNumber'],
            'actual' => $tenant->contact_number,
            'db_field' => 'tenants.contact_number'
        ],
        'TIN' => [
            'expected' => $frontendData['tin'],
            'actual' => $tenant->tin,
            'db_field' => 'tenants.tin'
        ],
        'User Email' => [
            'expected' => $frontendData['email'],
            'actual' => $tenant->user->email,
            'db_field' => 'users.email'
        ],
        'User Address (CRITICAL!)' => [
            'expected' => $frontendData['address'],
            'actual' => $tenant->user->address,
            'db_field' => 'users.address'
        ],
        'User Phone' => [
            'expected' => $frontendData['contactNumber'],
            'actual' => $tenant->user->phone,
            'db_field' => 'users.phone'
        ],
    ];
    
    $allPass = true;
    foreach ($tests as $testName => $test) {
        $passes = $test['expected'] === $test['actual'];
        $icon = $passes ? '✓' : '✗';
        echo "\n{$icon} {$testName}\n";
        echo "  Field: {$test['db_field']}\n";
        echo "  Expected: {$test['expected']}\n";
        echo "  Got:      {$test['actual']}\n";
        if (!$passes) {
            $allPass = false;
        }
    }
    
    echo "\n\n";
    if ($allPass) {
        echo "🎉 ALL TESTS PASSED! Frontend-Backend integration is working correctly.\n";
        echo "═══════════════════════════════════════════════════════════════════\n";
        exit(0);
    } else {
        echo "⚠️  SOME TESTS FAILED! See mismatches above.\n";
        echo "═══════════════════════════════════════════════════════════════════\n";
        exit(1);
    }
    
} catch (\Exception $e) {
    echo "❌ EXCEPTION OCCURRED\n";
    echo "─────────────────────────────────────────\n";
    echo "Message: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}:{$e->getLine()}\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
