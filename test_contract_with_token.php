<?php
// Read the token
$token = trim(file_get_contents('token.txt'));
echo "Using token: " . substr($token, 0, 20) . "...\n\n";

// Test contract 14 (agatha's contract)
$ch = curl_init('http://127.0.0.1:8000/api/contracts/14');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_VERBOSE, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

echo "=== CONTRACT 14 RESPONSE ===\n";
echo "HTTP Status: $http_code\n";
echo "Content-Type: $content_type\n";
echo "Response Size: " . strlen($response) . " bytes\n\n";

echo "Response:\n";
echo $response . "\n";

if ($http_code === 200) {
    $data = json_decode($response, true);
    if ($data) {
        echo "\n=== PARSED DATA ===\n";
        echo "Success: " . ($data['success'] ? 'YES' : 'NO') . "\n";
        if (isset($data['data'])) {
            echo "Data exists: YES\n";
            echo "Data keys: " . implode(', ', array_keys($data['data'])) . "\n";
            if (isset($data['data']['tenant'])) {
                echo "Tenant exists: YES\n";
            }
            if (isset($data['data']['payments'])) {
                echo "Payments exists: YES (count: " . count($data['data']['payments']) . ")\n";
            }
        }
    }
}

curl_close($ch);
