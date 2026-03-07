<?php

use Illuminate\Http\Request;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

// Bootstrap the application
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Request::capture()
);

// First test - get a contract
try {
    $contract = \App\Models\Contract::first();
    
    if (!$contract) {
        echo "No contracts found in database\n";
        exit;
    }
    
    echo "Testing lease endpoint for contract ID: " . $contract->id . "\n";
    echo "Contract Number: " . $contract->contract_number . "\n";
    echo "Tenant: " . ($contract->tenant?->contact_person ?? 'N/A') . "\n\n";
    
    // Try to load the view
    echo "Attempting to load lease view...\n";
    
    $data = [
        'contractNumber' => $contract->contract_number,
        'contractDate' => $contract->created_at->format('F d, Y'),
        'startDate' => $contract->start_date->format('F d, Y'),
        'endDate' => $contract->end_date->format('F d, Y'),
        'tenantName' => $contract->tenant->contact_person,
        'tenantCompany' => $contract->tenant->business_name,
        'tenantAddress' => $contract->tenant->business_address ?? 'Not provided',
        'tenantPhone' => $contract->tenant->contact_number,
        'spaceName' => $contract->rentalSpace->name,
        'spaceCode' => $contract->rentalSpace->space_code,
        'spaceType' => $contract->rentalSpace->space_type,
        'spaceSqm' => $contract->rentalSpace->size_sqm ?? 'N/A',
        'monthlyRent' => number_format($contract->monthly_rental, 2),
        'securityDeposit' => number_format($contract->deposit_amount ?? 0, 2),
        'terms' => $contract->terms ?? 'Standard lease terms apply.',
        'totalDurationMonths' => $contract->start_date->diffInMonths($contract->end_date),
    ];
    
    echo "Data prepared successfully\n";
    
    // Try PDF generation
    echo "Attempting PDF generation...\n";
    $pdf = \PDF::loadView('contracts.lease', $data);
    echo "PDF object created\n";
    
    // Get content
    $pdfContent = $pdf->output();
    echo "PDF content generated - Size: " . strlen($pdfContent) . " bytes\n";
    echo "✓ PDF generation successful!\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
