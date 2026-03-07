<?php
// Quick test to check QR code endpoint

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:8000/api/contracts/1/qr-code");
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer 24|Q3ZRIe3aCIkl0Pzgie9eKuCUf0L4pTnknMmXOhTX4b5b757f",
    "Accept: application/json"
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $data = json_decode($response, true);
    echo "QR Code test successful!\n";
    echo "Status Code: $httpCode\n";
    echo "QR Code length: " . strlen($data['qrCode']) . " bytes\n";
    echo "First 150 chars of QR: " . substr($data['qrCode'], 0, 150) . "\n";
    
    // Decode the base64 to see what URL is in there
    $svgData = base64_decode(substr($data['qrCode'], 26)); // Remove "data:image/svg+xml;base64,"
    
    // Extract the text content to see what URL was encoded
    if (preg_match('/<text[^>]*>([^<]+)<\/text>/i', $svgData, $matches)) {
        echo "\nScanned text would show: " . $matches[1] . "\n";
    } else {
        echo "\nFirst 300 chars of SVG: " . substr($svgData, 0, 300) . "\n";
    }
} else {
    echo "Error: HTTP $httpCode\n";
    echo "Response: " . substr($response, 0, 500) . "\n";
}
?>
