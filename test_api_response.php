<?php

require 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;
use Illuminate\Http\Request;

echo "======================================\n";
echo "Testing Payment API Response\n";
echo "======================================\n\n";

// Simulate what the API would return
$payments = Payment::with(['contract', 'tenant'])
    ->orderBy('due_date', 'asc')
    ->limit(15)
    ->get();

echo "Total payments to be returned: " . $payments->count() . "\n\n";

$response = [
    'success' => true,
    'data' => $payments->map(function ($payment) {
        return [
            'id' => $payment->id,
            'payment_number' => $payment->payment_number,
            'contract_id' => $payment->contract_id,
            'status' => $payment->status,
            'balance' => (float) $payment->balance,
            'amount_due' => (float) $payment->amount_due,
            'total_amount' => (float) $payment->total_amount,
            'due_date' => $payment->due_date?->format('Y-m-d'),
            'contract' => [
                'id' => $payment->contract?->id,
                'contract_number' => $payment->contract?->contract_number,
            ]
        ];
    })->toArray()
];

echo "API Response Sample (first 5 payments):\n";
echo json_encode(array_slice($response['data'], 0, 5), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

echo "\n\n";
echo "===  LOOKING FOR OVERDUE PAYMENT ===\n";
foreach ($response['data'] as $p) {
    if ($p['status'] === 'overdue') {
        echo "✓ FOUND OVERDUE PAYMENT!\n";
        echo json_encode($p, JSON_PRETTY_PRINT);
        break;
    }
}

echo "\n";
