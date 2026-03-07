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
    echo "Admin user not found\n";
    exit(1);
}

echo "Testing Tenant Update API\n";
echo "========================\n\n";

// Get first tenant
$tenant = Tenant::first();

if (!$tenant) {
    echo "No tenants found in database\n";
    exit(1);
}

echo "Found Tenant:\n";
echo "  ID: {$tenant->id}\n";
echo "  Business Name: {$tenant->business_name}\n";
echo "  Contact Person: {$tenant->contact_person}\n";
echo "  User Email: {$tenant->user->email}\n\n";

// Create test data to update
$testData = [
    'firstName' => 'Juan',
    'lastName' => 'Dela Cruz',
    'email' => 'juan.delacruz.' . time() . '@test.com',
    'contactNumber' => '09123456789',
    'address' => 'Updated Address',
    'business_name' => 'Updated Business ' . time(),
    'business_type' => 'partnership',
    'business_address' => 'Updated Business Address',
    'tin' => '123-456-789-012',
];

echo "Attempting to update tenant with:\n";
echo json_encode($testData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

// Create a mock request
$request = Request::create(
    "/api/tenants/{$tenant->id}",
    'PUT',
    $testData,
    [],
    [],
    ['HTTP_AUTHORIZATION' => 'Bearer test-token']
);

$request->setUserResolver(function() use ($user) {
    return $user;
});

try {
    $controller = new TenantController();
    $response = $controller->update($request, $tenant->id);
    $responseData = $response->getData(true);
    
    echo "Response from API:\n";
    echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
    
    if (isset($responseData['success']) && $responseData['success']) {
        echo "✓ Tenant updated successfully!\n\n";
        
        // Refresh from database
        $tenant->refresh();
        $tenant->load('user');
        
        echo "Updated Tenant Values:\n";
        echo "  Business Name: {$tenant->business_name}\n";
        echo "  Contact Person: {$tenant->contact_person}\n";
        echo "  Business Type: {$tenant->business_type}\n";
        echo "  Contact Number: {$tenant->contact_number}\n";
        echo "  User Email: {$tenant->user->email}\n";
        echo "  User Address: {$tenant->user->address}\n";
    } else {
        echo "✗ Tenant update failed\n";
        if (isset($responseData['errors'])) {
            echo "Errors:\n";
            foreach ($responseData['errors'] as $field => $messages) {
                echo "  {$field}: " . implode(', ', (array)$messages) . "\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "✗ Exception occurred: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}:{$e->getLine()}\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
?>
