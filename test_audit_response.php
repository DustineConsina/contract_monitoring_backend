<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AuditLog;
use Illuminate\Http\Request;

// Simulate the API response
$query = AuditLog::with('user');
$auditLogs = $query->orderBy('created_at', 'desc')->get();

$summary = [
    'total_logs' => $auditLogs->count(),
    'create_count' => $auditLogs->where('action', 'create')->count(),
    'update_count' => $auditLogs->where('action', 'update')->count(),
    'delete_count' => $auditLogs->where('action', 'delete')->count(),
];

$response = [
    'success' => true,
    'data' => [
        'audit_logs' => $auditLogs->take(5), // Only first 5 for testing
        'summary' => $summary,
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
