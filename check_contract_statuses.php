<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Contract;

echo "=== CONTRACT STATUS SUMMARY ===\n\n";

$statuses = Contract::distinct()->pluck('status');
echo "Contract statuses in database:\n";
foreach ($statuses as $status) {
    $count = Contract::where('status', $status)->count();
    echo "  - $status: $count\n";
}

echo "\n=== SPACES WITH NON-ACTIVE CONTRACTS ===\n\n";

// Get spaces that have pending or future contracts
$spacesWithPending = \App\Models\RentalSpace::whereHas('contracts', function ($q) {
    $q->whereIn('status', ['pending', 'upcoming', 'future']);
})->get();

if ($spacesWithPending->count() > 0) {
    echo "Spaces with pending/upcoming contracts:\n";
    foreach ($spacesWithPending as $space) {
        $contracts = $space->contracts()->whereIn('status', ['pending', 'upcoming', 'future'])->get();
        echo "  - {$space->space_code}: " . $contracts->pluck('status')->join(', ') . "\n";
    }
}

echo "\n=== TRULY AVAILABLE SPACES ===\n\n";
// Only spaces with NO contracts at all OR only terminated/expired contracts
$trulyAvailable = \App\Models\RentalSpace::where(function ($q) {
    $q->doesntHave('contracts')
      ->orWhereDoesntHave('contracts', function ($q2) {
          $q2->whereIn('status', ['active', 'pending', 'upcoming']);
      });
})->count();

echo "Truly available spaces (no active/pending): $trulyAvailable\n";
