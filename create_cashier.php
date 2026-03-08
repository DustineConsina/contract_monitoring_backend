<?php

/**
 * Script to create cashier account directly
 * Run: php create_cashier.php
 */

// Load Laravel application
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

// Create application instance
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->handle(
    $input = new \Symfony\Component\Console\Input\ArgvInput,
    new \Symfony\Component\Console\Output\ConsoleOutput
);

// Use Laravel's service container
$app->make(\Illuminate\Contracts\Http\Kernel::class);

// Get the User model
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

// Ensure database is connected
try {
    DB::connection()->getPdo();
    echo "[✓] Database connected\n";
} catch (\Exception $e) {
    echo "[✗] Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if cashier already exists
$cashier = User::where('email', 'cashier@pfda.gov.ph')->first();

if ($cashier) {
    echo "[!] Cashier account already exists\n";
    echo "Email: " . $cashier->email . "\n";
    echo "Status: " . $cashier->status . "\n";
    exit(0);
}

// Create cashier user
try {
    $user = User::create([
        'name' => 'Cashier User',
        'email' => 'cashier@pfda.gov.ph',
        'password' => Hash::make('password123'),
        'role' => 'cashier',
        'phone' => '09123456791',
        'address' => 'PFDA Office, Bulan, Sorsogon',
        'status' => 'active',
    ]);

    echo "[✓] Cashier account created successfully!\n";
    echo "Email: " . $user->email . "\n";
    echo "Role: " . $user->role . "\n";
    echo "Password: password123\n";
    exit(0);
} catch (\Exception $e) {
    echo "[✗] Failed to create cashier: " . $e->getMessage() . "\n";
    exit(1);
}
