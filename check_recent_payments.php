<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;

echo "Recent 5 Payments:\n";
$payments = Payment::with(['tenant.user', 'contract.rentalSpace'])
    ->latest()
    ->take(5)
    ->get();

foreach ($payments as $p) {
    echo "- ID: {$p->id}, Tenant: " . ($p->tenant?->business_name ?? 'N/A') . ", Amount: {$p->total_amount}, Date: {$p->payment_date}\n";
}

echo "\n\nTotal payments: " . Payment::count() . "\n";
