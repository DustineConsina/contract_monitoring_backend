<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "Users and their roles:\n";
User::select('id', 'name', 'email', 'role')->get()->each(function($u) {
    echo "- [{$u->id}] {$u->name} ({$u->email}) - Role: {$u->role}\n";
});

echo "\n\nChecking middleware 'role:admin,staff':\n";
$adminUser = User::where('role', 'admin')->first();
if ($adminUser) {
    echo "✅ Admin user found: {$adminUser->name}\n";
}

$staffUser = User::where('role', 'staff')->first();
if ($staffUser) {
    echo "✅ Staff user found: {$staffUser->name}\n";
}

$tenantUser = User::where('role', 'tenant')->first();
if ($tenantUser) {
    echo "❌ Tenant user found (shouldn't access reports): {$tenantUser->name}\n";
}
