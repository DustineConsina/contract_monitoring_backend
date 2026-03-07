<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Contract;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\AuditLog;

// Test contracts
echo "=== CONTRACTS ===\n";
$contracts = Contract::with(['tenant.user', 'rentalSpace'])->take(3)->get();
echo "Count: " . $contracts->count() . "\n";
echo json_encode($contracts, JSON_PRETTY_PRINT) . "\n\n";

// Test payments
echo "=== PAYMENTS ===\n";
$payments = Payment::with(['tenant.user', 'contract.rentalSpace'])->take(3)->get();
echo "Count: " . $payments->count() . "\n";
echo json_encode($payments, JSON_PRETTY_PRINT) . "\n\n";

// Test tenants
echo "=== TENANTS ===\n";
$tenants = Tenant::with(['user', 'activeContracts.rentalSpace'])->take(3)->get();
echo "Count: " . $tenants->count() . "\n";
echo json_encode($tenants, JSON_PRETTY_PRINT) . "\n\n";

// Test audit logs
echo "=== AUDIT LOGS ===\n";
$logs = AuditLog::take(3)->get();
echo "Count: " . $logs->count() . "\n";
echo json_encode($logs, JSON_PRETTY_PRINT) . "\n";
