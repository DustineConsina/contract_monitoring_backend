<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Payment;

// Delete orphaned payment
$payment = Payment::where('payment_number', 'PAY-2026-000003')->first();

if ($payment) {
    echo "Deleting orphaned payment: {$payment->payment_number}\n";
    echo "Contract ID: {$payment->contract_id}\n";
    $payment->delete();
    echo "✓ Payment deleted successfully\n";
} else {
    echo "Payment not found\n";
}

echo "\nRemaining Payments:\n";
$allPayments = Payment::with('contract')->get();
foreach ($allPayments as $p) {
    $contractNum = $p->contract?->contract_number ?? 'N/A';
    echo "  {$p->payment_number} - Contract: {$contractNum}\n";
}
?>
