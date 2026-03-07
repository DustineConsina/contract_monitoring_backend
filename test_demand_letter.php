<?php

require 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Tenant;
use App\Models\RentalSpace;
use App\Models\Contract;
use App\Models\Payment;
use Carbon\Carbon;

echo "======================================\n";
echo "TEST: Creating Contract with Unpaid Rent\n";
echo "======================================\n\n";

// 1. Create or get test user
echo "1. Creating/getting test user...\n";
$user = User::firstOrCreate(
    ['email' => 'testdemand@example.com'],
    [
        'name' => 'Test Demand Tenant',
        'password' => bcrypt('password'),
        'phone' => '09123456789',
        'address' => 'Test Address',
        'role' => 'tenant',
        'status' => 'active'
    ]
);
echo "   ✓ User ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n\n";

// 2. Create or get test tenant
echo "2. Creating/getting test tenant...\n";
$tenant = Tenant::firstOrCreate(
    ['user_id' => $user->id],
    [
        'tenant_code' => 'TESTDEMAND-' . date('YmdHis'),
        'business_name' => 'Test Demand Business',
        'business_type' => 'Retail',
        'contact_person' => 'Test Contact',
        'contact_number' => '09123456789',
        'status' => 'active'
    ]
);
echo "   ✓ Tenant ID: {$tenant->id}, Code: {$tenant->tenant_code}, Business: {$tenant->business_name}\n\n";

// 3. Create or get test rental space
echo "3. Creating/getting test rental space...\n";
$space = RentalSpace::firstOrCreate(
    ['space_code' => 'TESTSPACE-DL'],
    [
        'name' => 'Test Demand Space',
        'size_sqm' => 50,
        'base_rental_rate' => 15000.00,
        'status' => 'available'
    ]
);
echo "   ✓ Space ID: {$space->id}, Code: {$space->space_code}, Rate: ₱{$space->base_rental_rate}\n\n";

// 4. Create test contract
echo "4. Creating test contract...\n";
$contractNumber = 'TEST-DL-' . date('YmdHis');
$startDate = Carbon::now()->subMonths(3); // Started 3 months ago
$endDate = $startDate->copy()->addMonths(12);

$contract = Contract::create([
    'contract_number' => $contractNumber,
    'tenant_id' => $tenant->id,
    'rental_space_id' => $space->id,
    'start_date' => $startDate,
    'end_date' => $endDate,
    'duration_months' => 12,
    'monthly_rental' => 15000.00,
    'deposit_amount' => 45000.00,
    'interest_rate' => 3.00,
    'status' => 'active',
    'terms_conditions' => 'Test contract for demand letter testing'
]);
echo "   ✓ Contract ID: {$contract->id}\n";
echo "   Contract #: {$contract->contract_number}\n";
echo "   Start Date: {$startDate->format('Y-m-d')}\n";
echo "   Monthly Rental: ₱{$contract->monthly_rental}\n\n";

// 5. Create an overdue payment (from last month)
echo "5. Creating overdue payment...\n";
$billingStart = Carbon::now()->subMonth()->startOfMonth();
$billingEnd = Carbon::now()->subMonth()->endOfMonth();
// Set due date to 15 days ago (definitely overdue)
$dueDateObj = Carbon::now()->subDays(15);

$payment = Payment::create([
    'payment_number' => 'PAY-' . date('YmdHis'),
    'contract_id' => $contract->id,
    'tenant_id' => $tenant->id,
    'billing_period_start' => $billingStart,
    'billing_period_end' => $billingEnd,
    'due_date' => $dueDateObj,
    'amount_due' => 15000.00,
    'interest_amount' => 450.00, // 3% interest
    'total_amount' => 15450.00,
    'amount_paid' => 0.00,
    'balance' => 15450.00,
    'status' => 'overdue', // Marked as overdue
    'remarks' => 'Test payment for demand letter generation'
]);
echo "   ✓ Payment ID: {$payment->id}\n";
echo "   Payment #: {$payment->payment_number}\n";
echo "   Billing Period: {$billingStart->format('Y-m-d')} to {$billingEnd->format('Y-m-d')}\n";
echo "   Due Date: {$dueDateObj->format('Y-m-d')} (15 days overdue)\n";
echo "   Total Amount: ₱{$payment->total_amount}\n";
echo "   Outstanding Balance: ₱{$payment->balance}\n";
echo "   Status: {$payment->status}\n\n";

echo "6. Running demand letter generation command...\n";
$exitCode = \Artisan::call('payments:generate-demand-letters');
$output = \Artisan::output();
echo "   " . str_replace("\n", "\n   ", trim($output)) . "\n\n";

// Verify demand letter was created
$demandLetters = $contract->demandLetters()->get();
echo "7. Verifying demand letter creation...\n";
if ($demandLetters->count() > 0) {
    echo "   ✓ Demand letter(s) created successfully!\n\n";
    foreach ($demandLetters as $letter) {
        echo "   Demand Letter Details:\n";
        echo "   - Demand Number: {$letter->demand_number}\n";
        echo "   - Status: {$letter->status}\n";
        echo "   - Issued Date: {$letter->issued_date->format('Y-m-d H:i:s')}\n";
        echo "   - Due Date: {$letter->due_date->format('Y-m-d')}\n";
        echo "   - Outstanding Balance: ₱{$letter->outstanding_balance}\n";
        echo "   - Total Amount Demanded: ₱{$letter->total_amount_demanded}\n";
        echo "   - Email Sent To: {$letter->email_sent_to}\n";
    }
} else {
    echo "   ✗ No demand letters found!\n";
}

echo "\n======================================\n";
echo "TEST COMPLETE\n";
echo "======================================\n";
echo "\nSummary:\n";
echo "- User: {$user->name} ({$user->email}) (ID: {$user->id})\n";
echo "- Tenant: {$tenant->business_name} (ID: {$tenant->id})\n";
echo "- Rental Space: {$space->name} (ID: {$space->id})\n";
echo "- Contract: {$contract->contract_number} (ID: {$contract->id})\n";
echo "- Overdue Payment: {$payment->payment_number} (ID: {$payment->id})\n";
echo "- Demand Letters Generated: {$demandLetters->count()}\n";
