<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #0066cc;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
        }
        .contract-details {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .contract-details table {
            width: 100%;
        }
        .contract-details td {
            padding: 8px 0;
        }
        .contract-details td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .highlight {
            background-color: #fff3cd;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PFDA Contract Monitoring System</h1>
        <p>Philippine Fisheries Development Authority - Bulan, Sorsogon</p>
    </div>
    
    <div class="content">
        <div class="alert">
            <h2 style="margin-top: 0;">⏰ Contract Expiring Soon</h2>
            <p>Your contract will expire in <strong>{{ $daysUntilExpiry }} days</strong>.</p>
        </div>

        <p>Dear {{ $tenant->contact_person }},</p>
        
        <p>This is a reminder that your rental contract for <strong>{{ $rentalSpace->name }}</strong> is approaching its expiration date.</p>

        <div class="contract-details">
            <h3 style="margin-top: 0; color: #0066cc;">Contract Details</h3>
            <table>
                <tr>
                    <td>Contract Number:</td>
                    <td>{{ $contract->contract_number }}</td>
                </tr>
                <tr>
                    <td>Business Name:</td>
                    <td>{{ $tenant->business_name }}</td>
                </tr>
                <tr>
                    <td>Rental Space:</td>
                    <td>{{ $rentalSpace->name }} ({{ $rentalSpace->space_code }})</td>
                </tr>
                <tr>
                    <td>Space Type:</td>
                    <td>{{ $rentalSpace->getSpaceTypeLabel() }}</td>
                </tr>
                <tr>
                    <td>Contract Start Date:</td>
                    <td>{{ $contract->start_date->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td>Contract End Date:</td>
                    <td><span class="highlight">{{ $contract->end_date->format('F d, Y') }}</span></td>
                </tr>
                <tr>
                    <td>Monthly Rental:</td>
                    <td>₱{{ number_format($contract->monthly_rental, 2) }}</td>
                </tr>
            </table>
        </div>

        <h3 style="color: #0066cc;">What You Need to Do:</h3>
        
        @if($daysUntilExpiry <= 7)
            <div style="background-color: #ffe6e6; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545;">
                <p style="margin: 0; font-weight: bold; color: #dc3545;">⚠️ Urgent Action Required!</p>
                <p style="margin: 10px 0 0 0;">Your contract is expiring very soon. Please visit our office immediately to renew your contract.</p>
            </div>
        @else
            <p>If you wish to continue renting <strong>{{ $rentalSpace->name }}</strong>, please visit our office to process your contract renewal.</p>
        @endif

        <h4>Requirements for Contract Renewal:</h4>
        <ul>
            <li>Valid ID</li>
            <li>Business Permit (if applicable)</li>
            <li>TIN (Tax Identification Number)</li>
            <li>Proof of address</li>
            <li>Settlement of any outstanding balances</li>
        </ul>

        <p><strong>Important Notes:</strong></p>
        <ul>
            <li>Renewals should be processed before the contract expiration date</li>
            <li>All outstanding payments must be settled</li>
            <li>Rental rates may be subject to adjustment upon renewal</li>
            <li>Failure to renew on time may result in reassignment of the space</li>
        </ul>

        <p>Please visit the PFDA Office during business hours (Monday-Friday, 8:00 AM - 5:00 PM) to process your renewal.</p>

        <p>For any questions or to schedule an appointment, please contact us:</p>
        <p>
            <strong>PFDA Bulan, Sorsogon</strong><br>
            Phone: [Contact Number]<br>
            Email: [Email Address]
        </p>

        <p>We look forward to continuing our partnership with you.</p>

        <p>
            Sincerely,<br>
            <strong>PFDA Management</strong>
        </p>
    </div>

    <div class="footer">
        <p>This is an automated message from PFDA Contract Monitoring System.</p>
        <p>© {{ date('Y') }} Philippine Fisheries Development Authority - Bulan, Sorsogon</p>
    </div>
</body>
</html>
