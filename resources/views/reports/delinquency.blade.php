<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delinquency Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 3px solid #e74c3c;
            padding-bottom: 10px;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .summary-item {
            background: white;
            padding: 10px;
            border-left: 4px solid #e74c3c;
        }
        .summary-item label {
            font-weight: bold;
            color: #555;
            display: block;
            font-size: 12px;
        }
        .summary-item .value {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table thead {
            background: #e74c3c;
            color: white;
        }
        table th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Delinquency Report</h1>
    
    @if(isset($summary))
    <div class="summary">
        <div class="summary-item">
            <label>Total Delinquent Tenants</label>
            <div class="value">{{ $summary['total_delinquent_tenants'] ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <label>Total Overdue Amount</label>
            <div class="value">PHP {{ number_format($summary['total_overdue_amount'] ?? 0, 2) }}</div>
        </div>
        <div class="summary-item">
            <label>Tenants 30+ Days Overdue</label>
            <div class="value">{{ $summary['days_30_plus'] ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <label>Tenants 90+ Days Overdue</label>
            <div class="value">{{ $summary['days_90_plus'] ?? 0 }}</div>
        </div>
    </div>
    @endif

    @if($delinquent_tenants && count($delinquent_tenants) > 0)
    <table>
        <thead>
            <tr>
                <th>Tenant</th>
                <th>Contact Person</th>
                <th>Email</th>
                <th>Total Overdue</th>
                <th>Days Overdue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($delinquent_tenants as $tenant)
            <tr>
                <td>{{ $tenant['contact_person'] ?? $tenant['business_name'] ?? 'N/A' }}</td>
                <td>{{ $tenant['contact_person'] ?? 'N/A' }}</td>
                <td>{{ $tenant['email'] ?? 'N/A' }}</td>
                <td>PHP {{ number_format($tenant['total_overdue'] ?? 0, 2) }}</td>
                <td>{{ $tenant['days_overdue'] ?? 0 }} days</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color: #999; text-align: center; margin-top: 20px;">No delinquent tenants found</p>
    @endif

    <div class="footer">
        <p>Generated on {{ date('F d, Y \a\t H:i A') }}</p>
    </div>
</body>
</html>
