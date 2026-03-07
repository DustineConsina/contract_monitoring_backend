<?php

// Test PDF export endpoint
$base_url = 'http://localhost:8000';

// First, we need to get an authentication token
// For testing, we'll assume the token is available

$contracts_url = $base_url . '/api/reports/contracts?format=pdf';

echo "Testing PDF Export Endpoint\n";
echo "===========================\n";
echo "URL: " . $contracts_url . "\n\n";

// Use curl to test the endpoint
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $contracts_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Add headers
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/pdf',
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

echo "HTTP Status Code: " . $http_code . "\n";
echo "Content-Type: " . $content_type . "\n";
echo "Response Length: " . strlen($response) . " bytes\n\n";

if ($http_code === 200) {
    echo "✓ PDF export endpoint is working!\n";
    echo "✓ PDF file generated successfully.\n";
    
    // Check if response looks like a PDF
    if (strpos($response, '%PDF') === 0) {
        echo "✓ Response is a valid PDF file (starts with %PDF header)\n";
    } else {
        echo "✗ Response doesn't look like a PDF file\n";
        echo "First 100 characters: " . substr($response, 0, 100) . "\n";
    }
} else if ($http_code === 401) {
    echo "✗ Authentication error - token required\n";
} else if ($http_code === 403) {
    echo "✗ Authorization error - user doesn't have permission\n";
} else if ($http_code === 404) {
    echo "✗ Endpoint not found\n";
} else if ($http_code === 500) {
    echo "✗ Server error\n";
    echo "Response: " . substr($response, 0, 500) . "\n";
} else {
    echo "✗ Unexpected error\n";
    echo "Response: " . substr($response, 0, 500) . "\n";
}

curl_close($ch);
?>
