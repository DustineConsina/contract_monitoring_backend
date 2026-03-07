<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $contract->contract_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #4ade80;
            color: white;
        }
        
        .status-inactive {
            background: #ef4444;
            color: white;
        }
        
        .status-pending {
            background: #f59e0b;
            color: white;
        }
        
        .content {
            padding: 30px 20px;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            color: #667eea;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .field {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .field:last-child {
            border-bottom: none;
        }
        
        .field-label {
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }
        
        .field-value {
            color: #1f2937;
            font-size: 14px;
            font-weight: 600;
            text-align: right;
        }
        
        .footer {
            background: #f9fafb;
            padding: 20px;
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
            border-top: 1px solid #e5e7eb;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
        .contact-info {
            background: #f0f9ff;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        
        .contact-info .field {
            padding: 8px 0;
        }
        
        .highlight {
            background: #fef3c7;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #f59e0b;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>{{ $contract->contract_number }}</h1>
            <p>Contract Details</p>
            <span class="status-badge status-{{ strtolower($contract->status) }}">
                {{ ucfirst($contract->status) }}
            </span>
        </div>
        
        <!-- Content -->
        <div class="content">
            <!-- Tenant Information -->
            <div class="section">
                <div class="section-title">Tenant Information</div>
                <div class="field">
                    <span class="field-label">Name</span>
                    <span class="field-value">{{ $contract->tenant->user->name ?? $contract->tenant->business_name }}</span>
                </div>
                <div class="contact-info">
                    <div class="field">
                        <span class="field-label">Email</span>
                        <span class="field-value">{{ $contract->tenant->user->email }}</span>
                    </div>
                    <div class="field">
                        <span class="field-label">Phone</span>
                        <span class="field-value">{{ $contract->tenant->user->phone ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Rental Space Information -->
            <div class="section">
                <div class="section-title">Rental Space</div>
                <div class="field">
                    <span class="field-label">Space Name</span>
                    <span class="field-value">{{ $contract->rentalSpace->name }}</span>
                </div>
                <div class="field">
                    <span class="field-label">Code</span>
                    <span class="field-value">{{ $contract->rentalSpace->code }}</span>
                </div>
                <div class="field">
                    <span class="field-label">Type</span>
                    <span class="field-value">{{ $contract->rentalSpace->type ?? 'N/A' }}</span>
                </div>
                <div class="field">
                    <span class="field-label">Size (sqm)</span>
                    <span class="field-value">{{ $contract->rentalSpace->size_sqm ?? 'N/A' }}</span>
                </div>
            </div>
            
            <!-- Contract Terms -->
            <div class="section">
                <div class="section-title">Contract Terms</div>
                <div class="field">
                    <span class="field-label">Start Date</span>
                    <span class="field-value">{{ \Carbon\Carbon::parse($contract->start_date)->format('M d, Y') }}</span>
                </div>
                <div class="field">
                    <span class="field-label">End Date</span>
                    <span class="field-value">{{ \Carbon\Carbon::parse($contract->end_date)->format('M d, Y') }}</span>
                </div>
            </div>
            
            <!-- Financial Information -->
            <div class="section">
                <div class="section-title">Financial Details</div>
                <div class="field">
                    <span class="field-label">Monthly Rental</span>
                    <span class="field-value">₱ {{ number_format($contract->monthly_rental, 2) }}</span>
                </div>
                <div class="field">
                    <span class="field-label">Deposit</span>
                    <span class="field-value">₱ {{ number_format($contract->deposit ?? 0, 2) }}</span>
                </div>
                <div class="highlight">
                    <strong>Total Initial Payment:</strong> ₱ {{ number_format(($contract->monthly_rental + ($contract->deposit ?? 0)), 2) }}
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>PFDA Contract Monitoring System</p>
            <p>Generated on {{ \Carbon\Carbon::now()->format('M d, Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
