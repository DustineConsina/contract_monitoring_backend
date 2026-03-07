<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;
use App\Models\Contract;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "=== Fixing Payment Dates ===\n\n";

// Delete all existing payments
$count = Payment::count();
Payment::query()->delete();
echo "✓ Deleted $count old payments\n\n";

// Regenerate payments based on contracts
$contracts = Contract::all();
echo "Regenerating payments for " . $contracts->count() . " contracts...\n\n";

foreach ($contracts as $contract) {
    $startDate = Carbon::parse($contract->start_date);
    $endDate = Carbon::parse($contract->end_date);
    
    $monthCount = 0;
    $currentDate = $startDate->copy();
    
    while ($currentDate->lte($endDate) && $monthCount < 60) { // Limit to 60 months
        // Calculate period based on contract anniversary
        $periodStart = $startDate->copy()->addMonths($monthCount);
        $periodEnd = $startDate->copy()->addMonths($monthCount + 1);
        
        if ($periodEnd->gt($endDate)) {
            $periodEnd = $endDate;
        }
        
        // Due date is same as period end (contract anniversary)
        $dueDate = $periodEnd->copy();
        
        // Calculate with 3% interest
        $baseRent = $contract->monthly_rental;
        $interest = $baseRent * 0.03;
        $totalAmount = $baseRent + $interest;
        
        // Use raw insert to bypass fillable restrictions
        DB::table('payments')->insert([
            'payment_number' => 'PAY-' . $contract->contract_number . '-' . str_pad($monthCount + 1, 3, '0', STR_PAD_LEFT),
            'contract_id' => $contract->id,
            'tenant_id' => $contract->tenant_id,
            'billing_period_start' => $periodStart,
            'billing_period_end' => $periodEnd,
            'due_date' => $dueDate,
            'amount_due' => $baseRent,
            'interest_amount' => $interest,
            'total_amount' => $totalAmount,
            'amount_paid' => 0,
            'balance' => $totalAmount,
            'status' => 'pending',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        
        echo "Contract {$contract->contract_number}: {$periodStart->format('d/m/Y')} → {$periodEnd->format('d/m/Y')} (Due: {$dueDate->format('d/m/Y')})\n";
        
        $monthCount++;
        $currentDate->addMonth();
    }
}

echo "\n✓ All payments regenerated with correct dates!\n";
