<?php
require_once '/xampp/htdocs/backend_contract/vendor/autoload.php';

$app = require '/xampp/htdocs/backend_contract/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Delete existing admin user
User::where('email', 'admin@pfda.gov.ph')->delete();

// Create new admin user with correct password
$password = Hash::make('password123');
User::create([
    'name' => 'Admin User',
    'email' => 'admin@pfda.gov.ph',
    'password' => $password,
    'role' => 'admin',
    'phone' => '09123456789',
    'address' => 'PFDA Office',
    'status' => 'active',
]);

echo "Admin user recreated with password hash: " . $password . "\n";
echo "Test login with email: admin@pfda.gov.ph, password: password123\n";
