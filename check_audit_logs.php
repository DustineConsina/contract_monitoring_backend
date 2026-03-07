<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

use App\Models\AuditLog;

// Check for duplicate entries
$auditLogs = AuditLog::with('user')
    ->orderBy('created_at', 'desc')
    ->get();

echo "=== Total Audit Log Entries: " . $auditLogs->count() . " ===\n\n";

// Get unique by description and count occurrences
$grouped = $auditLogs->groupBy('description');

$duplicates = $grouped->filter(fn($group) => $group->count() > 1);

if ($duplicates->count() > 0) {
    echo "=== DUPLICATES FOUND ===\n";
    foreach ($duplicates as $description => $entries) {
        echo "Description: $description\n";
        echo "Count: " . $entries->count() . "\n";
        foreach ($entries as $entry) {
            echo "  - ID: {$entry->id}, Created: {$entry->created_at}, User: {$entry->user?->email}\n";
        }
        echo "\n";
    }
} else {
    echo "✓ No duplicates found!\n";
}

echo "\n=== Latest 5 Audit Log Entries ===\n";
$auditLogs->take(5)->each(function($log) {
    echo "{$log->created_at} | {$log->user?->email} | {$log->action} | {$log->model_type} | {$log->description}\n";
});
