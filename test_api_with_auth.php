<?php

// Get a valid token from the user who created the contract
$db_file = 'database.sqlite';
if (file_exists($db_file)) {
    $pdo = new PDO('sqlite:' . $db_file);
} else {
    // Use MySQL or other db
    $host = getenv('DB_HOST') ?? 'localhost';
    $db = getenv('DB_DATABASE') ?? 'contract_monitoring';
    $user = getenv('DB_USERNAME') ?? 'root';
    $pass = getenv('DB_PASSWORD') ?? '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    } catch (Exception $e) {
        echo "Database error: " . $e->getMessage();
        exit;
    }
}

// Get first user's token
$sql = "SELECT id, name FROM users LIMIT 1";
try {
    $result = $pdo->query($sql);
    $user = $result->fetch(PDO::FETCH_ASSOC);
    echo "User: " . json_encode($user) . "\n";
} catch (Exception $e) {
    echo "Query error: " . $e->getMessage();
}

// Try to curl the contract API
// First, login
$ch = curl_init('http://127.0.0.1:8000/api/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'admin@example.com',
    'password' => 'password'
]));
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "\n=== LOGIN RESPONSE ===\n";
echo "HTTP Code: $http_code\n";
echo "Response: $response\n";
curl_close($ch);

// Parse the token
$login_data = json_decode($response, true);
if ($login_data && isset($login_data['token'])) {
    $token = $login_data['token'];
    echo "\nGot token: " . substr($token, 0, 20) . "...\n";
    
    // Now fetch contract 6
    $ch = curl_init('http://127.0.0.1:8000/api/contracts/6');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $response_size = strlen($response);
    
    echo "\n=== CONTRACT RESPONSE ===\n";
    echo "HTTP Code: $http_code\n";
    echo "Content-Type: $content_type\n";
    echo "Response Size: $response_size bytes\n";
    echo "Response Preview:\n";
    echo substr($response, 0, 500);
    
    curl_close($ch);
} else {
    echo "Login failed, no token received";
}
