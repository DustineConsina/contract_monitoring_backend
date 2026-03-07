<?php

require 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DemandLetter;

echo "======================================\n";
echo "TEST: Download Demand Letter PDF\n";
echo "======================================\n\n";

// Get the latest demand letter
$demandLetter = DemandLetter::latest()->first();

if (!$demandLetter) {
    echo "✗ No demand letters found in database!\n";
    exit(1);
}

echo "Found demand letter: {$demandLetter->demand_number}\n";
echo "Payment ID: {$demandLetter->payment_id}\n";
echo "Demand Letter ID: {$demandLetter->id}\n\n";

// Test the download endpoint URL
$downloadUrl = "/api/demand-letters/{$demandLetter->id}/download";

echo "Download URL: {$downloadUrl}\n";
echo "You can visit this URL in the browser to download the PDF:\n";
echo "http://192.168.1.5:8000{$downloadUrl}\n\n";

// Load the view and generate PDF
try {
    $payment = $demandLetter->payment;
    $contract = $payment->contract;
    $tenant = $demandLetter->tenant;

    // Calculate days overdue
    $daysOverdue = \Carbon\Carbon::now()->diffInDays($payment->due_date);

    // Prepare data for PDF
    $data = [
        'demandNumber' => $demandLetter->demand_number,
        'tenantName' => $tenant->contact_person,
        'tenantCompany' => $tenant->business_name,
        'tenantAddress' => $tenant->business_address ?? 'Not provided',
        'tenantPhone' => $tenant->contact_number,
        'spaceName' => $contract->rentalSpace->name,
        'spaceCode' => $contract->rentalSpace->space_code,
        'billingPeriod' => $payment->billing_period_start->format('F d, Y') . ' to ' . $payment->billing_period_end->format('F d, Y'),
        'rentalAmount' => number_format($payment->amount_due, 2),
        'interestAmount' => number_format($payment->interest_amount, 2),
        'originalDueDate' => $payment->due_date->format('F d, Y'),
        'daysOverdue' => max(0, $daysOverdue),
        'totalAmountDemanded' => number_format($demandLetter->total_amount_demanded, 2),
        'issuedDate' => $demandLetter->issued_date->format('F d, Y'),
        'settlementDeadline' => $demandLetter->due_date->format('F d, Y'),
        'contractDate' => $contract->created_at->format('F d, Y'),
        'currentDate' => \Carbon\Carbon::now()->format('F d, Y'),
        'generatedDate' => \Carbon\Carbon::now()->format('F d, Y h:i A'),
    ];

    echo "PDF Data Prepared:\n";
    echo "- Tenant: {$data['tenantName']} ({$data['tenantCompany']})\n";
    echo "- Space: {$data['spaceName']} ({$data['spaceCode']})\n";
    echo "- Billing Period: {$data['billingPeriod']}\n";
    echo "- Total Amount: ₱{$data['totalAmountDemanded']}\n";
    echo "- Days Overdue: {$data['daysOverdue']} days\n";
    echo "- Settlement Deadline: {$data['settlementDeadline']}\n\n";

    // Try to generate PDF
    $pdf = \PDF::loadView('demand-letters.letter', $data);
    
    // Save to file to test
    $filename = "demand-letter-{$demandLetter->demand_number}.pdf";
    $filepath = storage_path("app/temp/{$filename}");
    
    // Create temp directory if it doesn't exist
    if (!file_exists(storage_path('app/temp'))) {
        mkdir(storage_path('app/temp'), 0755, true);
    }
    
    $pdf->save($filepath);
    
    if (file_exists($filepath)) {
        $filesize = filesize($filepath);
        echo "✓ PDF generated successfully!\n";
        echo "- File: {$filepath}\n";
        echo "- Size: " . number_format($filesize) . " bytes\n";
    } else {
        echo "✗ PDF generation failed!\n";
    }

} catch (\Exception $e) {
    echo "✗ Error generating PDF: {$e->getMessage()}\n";
    exit(1);
}

echo "\n======================================\n";
echo "TEST COMPLETE\n";
echo "======================================\n";
