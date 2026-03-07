<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;
use App\Models\Tenant;
use App\Models\Contract;
use Carbon\Carbon;

$tenants = Tenant::limit(5)->get();
echo "Creating sample payments for different tenants...\n";

foreach ($tenants as $tenant) {
    // Get a contract for this tenant
    $contract = $tenant->contracts()->first();
    
    if ($contract) {
        Payment::create([
            'tenant_id' => $tenant->id,
            'contract_id' => $contract->id,
            'payment_number' => 'PAY-' . uniqid(),
            'billing_period_start' => Carbon::now()->startOfMonth(),
            'billing_period_end' => Carbon::now()->endOfMonth(),
            'amount_due' => $contract->monthly_rental,
            'total_amount' => $contract->monthly_rental,
            'amount_paid' => $contract->monthly_rental,
            'balance' => 0,
            'payment_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(7),
            'status' => 'paid',
            'remarks' => 'Test payment',
        ]);
        echo "✓ Created payment for {$tenant->business_name}\n";
    }
}

echo "\nRecent 5 Payments:\n";
Payment::with('tenant')
    ->latest()
    ->take(5)
    ->get()
    ->each(function ($p) {
        echo "- {$p->tenant->business_name}: ₱{$p->total_amount} on {$p->payment_date->format('Y-m-d')}\n";
    });
