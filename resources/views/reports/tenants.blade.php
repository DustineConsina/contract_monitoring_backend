<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tenants Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 3px solid #9b59b6;
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
            border-left: 4px solid #9b59b6;
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
            background: #9b59b6;
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
        .status {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .status.active {
            background: #d4edda;
            color: #155724;
        }
        .status.inactive {
            background: #e2e3e5;
            color: #383d41;
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
    <h1>Tenants Report</h1>
    
    @if(isset($summary))
    <div class="summary">
        <div class="summary-item">
            <label>Total Tenants</label>
            <div class="value">{{ $summary['total_tenants'] ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <label>Active Tenants</label>
            <div class="value">{{ $summary['active_tenants'] ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <label>Inactive Tenants</label>
            <div class="value">{{ $summary['inactive_tenants'] ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <label>Tenants with Active Contracts</label>
            <div class="value">{{ $summary['tenants_with_active_contracts'] ?? 0 }}</div>
        </div>
    </div>
    @endif

    @if($tenants && count($tenants) > 0)
    <table>
        <thead>
            <tr>
                <th>Business Name</th>
                <th>Contact Person</th>
                <th>Email</th>
                <th>TIN</th>
                <th>Status</th>
                <th>Member Since</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tenants as $item)
            <tr>
                <td>{{ $item['tenant']['business_name'] ?? 'N/A' }}</td>
                <td>{{ $item['tenant']['contact_person'] ?? 'N/A' }}</td>
                <td>{{ (isset($item['tenant']['user']) && is_array($item['tenant']['user']) && isset($item['tenant']['user']['email'])) ? $item['tenant']['user']['email'] : 'N/A' }}</td>
                <td>{{ $item['tenant']['tin'] ?? 'N/A' }}</td>
                <td><span class="status {{ isset($item['tenant']['status']) ? strtolower($item['tenant']['status']) : 'inactive' }}">{{ isset($item['tenant']['status']) ? ucfirst($item['tenant']['status']) : 'Inactive' }}</span></td>
                <td>{{ isset($item['tenant']['created_at']) && $item['tenant']['created_at'] ? date('M d, Y', strtotime($item['tenant']['created_at'])) : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color: #999; text-align: center; margin-top: 20px;">No tenants found</p>
    @endif

    <div class="footer">
        <p>Generated on {{ date('F d, Y \a\t H:i A') }}</p>
    </div>
</body>
</html>
