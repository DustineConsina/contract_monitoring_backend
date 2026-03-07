<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Contract;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

try {
    $contract = Contract::with(['tenant.user', 'rentalSpace'])->find(20);
    
    if (!$contract) {
        echo "Contract 20 not found\n";
        exit(1);
    }
    
    echo "Contract found: {$contract->contract_number}\n";
    
    $backendUrl = config('app.url') ?? env('APP_URL', 'http://localhost:8000');
    $qrData = $backendUrl . '/api/contracts/' . $contract->id . '/lease';
    
    echo "QR Data (now points to Lease): $qrData\n";
    
    $qrCode = new QrCode($qrData);
    $writer = new SvgWriter();
    $qrSvg = $writer->write($qrCode);
    $svgString = $qrSvg->getString();
    
    echo "QR SVG generated successfully\n";
    echo "SVG length: " . strlen($svgString) . "\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
