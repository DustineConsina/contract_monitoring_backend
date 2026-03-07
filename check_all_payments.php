<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Payment;

echo "All Payments Current State:\n";
echo "======================================\n\n";

$payments = Payment::with('contract')->get();

foreach ($payments as $payment) {
    $contractNum = $payment->contract?->contract_number ?? 'N/A';
    echo "Payment ID: {$payment->id}\n";
    echo "Payment Number: {$payment->payment_number}\n";
    echo "Contract: {$contractNum}\n";
    echo "Amount Due: {$payment->amount_due}\n";
    echo "Interest: {$payment->interest_amount}\n";
    echo "Total Amount: {$payment->total_amount}\n";
    echo "Amount Paid: {$payment->amount_paid}\n";
    echo "Balance: {$payment->balance}\n";
    echo "Status: {$payment->status}\n";
    echo "---\n\n";
}

echo "Summary:\n";
$totalPaid = Payment::where('status', 'paid')->sum('amount_paid');
$totalPending = Payment::whereIn('status', ['pending', 'overdue'])->sum('balance');
echo "Total Paid (all 'paid' status): ₱{$totalPaid}\n";
echo "Total Pending (pending + overdue): ₱{$totalPending}\n";
?>
