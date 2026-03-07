<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Revenue Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 3px solid #f39c12;
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
            border-left: 4px solid #f39c12;
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
            background: #f39c12;
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
    <h1>Revenue Report</h1>
    
    @if(isset($summary))
    <div class="summary">
        <div class="summary-item">
            <label>Total Monthly Revenue</label>
            <div class="value">PHP {{ number_format($summary['total_monthly_revenue'] ?? 0, 2) }}</div>
        </div>
        <div class="summary-item">
            <label>Total Paid This Period</label>
            <div class="value">PHP {{ number_format($summary['total_paid_this_period'] ?? 0, 2) }}</div>
        </div>
        <div class="summary-item">
            <label>Total Pending This Period</label>
            <div class="value">PHP {{ number_format($summary['total_pending_this_period'] ?? 0, 2) }}</div>
        </div>
        <div class="summary-item">
            <label>Collection Rate</label>
            <div class="value">{{ $summary['collection_rate'] ?? 0 }}%</div>
        </div>
    </div>
    @endif

    @if($revenue && count($revenue) > 0)
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th>Expected Revenue</th>
                <th>Received Revenue</th>
                <th>Pending Revenue</th>
                <th>Collection Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($revenue as $month)
            <tr>
                <td>{{ $month['month'] ?? 'N/A' }}</td>
                <td>PHP {{ number_format($month['expected_revenue'] ?? 0, 2) }}</td>
                <td>PHP {{ number_format($month['received_revenue'] ?? 0, 2) }}</td>
                <td>PHP {{ number_format($month['pending_revenue'] ?? 0, 2) }}</td>
                <td>{{ $month['collection_rate'] ?? 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color: #999; text-align: center; margin-top: 20px;">No revenue data found</p>
    @endif

    <div class="footer">
        <p>Generated on {{ date('F d, Y \a\t H:i A') }}</p>
    </div>
</body>
</html>
