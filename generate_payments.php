<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$contract = \App\Models\Contract::find(1);
if ($contract) {
    $contract->generatePaymentSchedule();
    echo "✓ Payment schedule generated for contract " . $contract->contract_number . "\n";
    
    // Show generated payments
    $payments = $contract->payments()->get();
    echo "Generated " . count($payments) . " payment records\n";
} else {
    echo "✗ Contract not found\n";
}
?>
