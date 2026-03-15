<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

echo "Testing RentalSpace model...\n";
echo "✅ Model loaded successfully\n";
echo "✅ Scope method exists: " . (method_exists(\App\Models\RentalSpace::class, 'scopeAvailable') ? 'YES' : 'NO') . "\n";
