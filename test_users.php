<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = User::all(['id', 'email', 'name']);
foreach ($users as $user) {
    echo "{$user->id} - {$user->email} - {$user->name}\n";
}

echo "\n\nAuditLog Count: " . \App\Models\AuditLog::count() . "\n";

echo "\nSample Audit Logs:\n";
\App\Models\AuditLog::with('user')
    ->latest()
    ->limit(5)
    ->get()
    ->each(function ($log) {
        $user = $log->user ? $log->user->name : 'System';
        echo "- {$log->id}: {$log->action} {$log->model_type} by {$user}\n";
    });
