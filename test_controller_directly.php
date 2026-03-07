<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\ReportController;

// Create a fake request
$user = User::where('role', 'admin')->first();
auth()->guard('web')->setUser($user);

// Create request
$request = new Request();

// Call the controller method directly
$controller = new ReportController();
$response = $controller->auditLogReport($request);

// Get the JSON
$content = json_decode($response->getContent(), true);

echo "Response Status: {$response->getStatusCode()}\n";
echo "Has audit_logs? " . (isset($content['data']['audit_logs']) ? 'YES' : 'NO') . "\n";
echo "Audit logs count: " . count($content['data']['audit_logs'] ?? []) . "\n";
echo "Summary total_logs: " . ($content['data']['summary']['total_logs'] ?? 0) . "\n";

if (count($content['data']['audit_logs'] ?? []) === 0) {
    echo "\n❌ ERROR: No audit logs returned!\n";
} else {
    echo "\n✅ Audit logs are being returned correctly!\n";
}
