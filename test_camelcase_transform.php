<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Http\Controllers\RentalSpaceController;
use Illuminate\Http\Request;

echo "=== SIMULATING FRONTEND API CLIENT CALL ===\n\n";

$controller = new RentalSpaceController();
$request = new Request();
$response = $controller->index($request);
$json = $response->getContent();

echo "Raw JSON response (first 500 chars):\n";
echo substr($json, 0, 500) . "\n\n";

$data = json_decode($json, true);

// Simulate what the API client transformation does (camelCase conversion)
function toCamelCase($str) {
    return preg_replace_callback('/_([a-z])/', function($m) { 
        return strtoupper($m[1]); 
    }, $str);
}

function transformToCamelCase($obj) {
    if (is_null($obj) || !is_array($obj)) return $obj;
    if (is_array($obj) && isset($obj[0])) {
        // It's an array
        return array_map('transformToCamelCase', $obj);
    }
    
    $result = [];
    foreach ($obj as $key => $value) {
        $camelKey = toCamelCase($key);
        if (is_array($value) || is_object($value)) {
            $result[$camelKey] = transformToCamelCase($value);
        } else {
            $result[$camelKey] = $value;
        }
    }
    return $result;
}

$transformed = transformToCamelCase($data);

echo "After camelCase transformation:\n";
echo "Top level keys: " . implode(', ', array_keys($transformed)) . "\n";
echo "data['data'] exists: " . (isset($transformed['data']['data']) ? 'yes' : 'no') . "\n";

if (isset($transformed['data']['data'])) {
    $spaces = $transformed['data']['data'];
    echo "Number of spaces: " . count($spaces) . "\n";
    
    if (count($spaces) > 0) {
        echo "\nFirst space keys:\n";
        foreach (array_keys((array)$spaces[0]) as $key) {
            echo "  - $key\n";
        }
        
        echo "\nActiveContractsCount values:\n";
        foreach ($spaces as $space) {
            $space = (array)$space;
            echo "  {$space['spaceCode']}: activeContractsCount={$space['activeContractsCount']}\n";
        }
    }
}
?>
