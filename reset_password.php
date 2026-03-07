<?php
require_once '/xampp/htdocs/backend_contract/vendor/autoload.php';

use Illuminate\Support\Facades\Hash;

// Initialize Laravel
$app = require '/xampp/htdocs/backend_contract/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Generate hash
$password = 'password123';
$hash = Hash::make($password);
echo "Password: $password\n";
echo "Hash: $hash\n";

// Update admin user with new password hash
\App\Models\User::where('email', 'admin@pfda.gov.ph')->update(['password' => $hash]);
echo "Admin password updated successfully!\n";
