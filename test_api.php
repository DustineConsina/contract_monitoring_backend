<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make('Illuminate\Foundation\Http\Kernel');
$request = \Illuminate\Http\Request::capture();

// Get a user and create a token
try {
    $user = \App\Models\User::first();
    
    if (!$user) {
        echo "No users found in database.\n";
        echo "Creating test user...\n";
        $user = \App\Models\User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin',
        ]);
    }
    
    echo "User found: {$user->email}\n";
    
    // Create a token
    $token = $user->createToken('API Token')->plainTextToken;
    echo "Generated Token: {$token}\n";
    
} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}\n";
    echo $e->getTraceAsString();
}
