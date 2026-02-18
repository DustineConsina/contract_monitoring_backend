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
        }
        .alert-warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
        }
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .payment-details {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .payment-details table {
            width: 100%;
        }
        .payment-details td {
            padding: 8px 0;
        }
        .payment-details td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #0066cc;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #0066cc;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PFDA Contract Monitoring System</h1>
        <p>Philippine Fisheries Development Authority - Bulan, Sorsogon</p>
    </div>
    
    <div class="content">
        @if($reminderType === 'overdue')
            <div class="alert alert-danger">
                <h2 style="margin-top: 0;">⚠️ Payment Overdue</h2>
                <p>Your payment is now <strong>{{ $payment->daysOverdue() }} days overdue</strong>. Please settle immediately to avoid additional charges.</p>
            </div>
        @else
            <div class="alert alert-warning">
                <h2 style="margin-top: 0;">📅 Payment Due Soon</h2>
                <p>This is a friendly reminder that your payment is due soon.</p>
            </div>
        @endif

        <p>Dear {{ $tenant->contact_person }},</p>
        
        <p>This is a reminder regarding your rental payment for <strong>{{ $rentalSpace->name }}</strong>.</p>

        <div class="payment-details">
            <h3 style="margin-top: 0; color: #0066cc;">Payment Details</h3>
            <table>
                <tr>
                    <td>Payment Number:</td>
                    <td>{{ $payment->payment_number }}</td>
                </tr>
                <tr>
                    <td>Contract Number:</td>
                    <td>{{ $contract->contract_number }}</td>
                </tr>
                <tr>
                    <td>Rental Space:</td>
                    <td>{{ $rentalSpace->name }} ({{ $rentalSpace->space_code }})</td>
                </tr>
                <tr>
                    <td>Billing Period:</td>
                    <td>{{ $payment->billing_period_start->format('M d, Y') }} - {{ $payment->billing_period_end->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td>Due Date:</td>
                    <td><strong>{{ $payment->due_date->format('F d, Y') }}</strong></td>
                </tr>
                <tr>
                    <td>Base Amount:</td>
                    <td>₱{{ number_format($payment->amount_due, 2) }}</td>
                </tr>
                @if($payment->interest_amount > 0)
                <tr>
                    <td>Interest (Late Payment):</td>
                    <td style="color: #dc3545;">₱{{ number_format($payment->interest_amount, 2) }}</td>
                </tr>
                @endif
                <tr style="border-top: 2px solid #ddd;">
                    <td>Total Amount Due:</td>
                    <td><span class="amount">₱{{ number_format($payment->total_amount, 2) }}</span></td>
                </tr>
            </table>
        </div>

        @if($reminderType === 'overdue')
            <p style="color: #dc3545; font-weight: bold;">
                Additional interest is being charged at {{ $contract->interest_rate }}% per month for overdue payments. 
                Please settle your account immediately.
            </p>
        @endif

        <p>You can make your payment at the PFDA Office during business hours (Monday-Friday, 8:00 AM - 5:00 PM).</p>

        <p><strong>Payment Methods:</strong></p>
        <ul>
            <li>Cash payment at PFDA Office</li>
            <li>Bank Transfer</li>
            <li>Check payment</li>
        </ul>

        <p>If you have already made this payment, please disregard this message and contact our office to update your records.</p>

        <p>For any questions or concerns, please contact us:</p>
        <p>
            <strong>PFDA Bulan, Sorsogon</strong><br>
            Phone: [Contact Number]<br>
            Email: [Email Address]
        </p>

        <p>Thank you for your cooperation.</p>

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
