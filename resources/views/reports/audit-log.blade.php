<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Log Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 3px solid #34495e;
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
            border-left: 4px solid #34495e;
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
            font-size: 12px;
        }
        table thead {
            background: #34495e;
            color: white;
        }
        table th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        .action {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .action.create {
            background: #d4edda;
            color: #155724;
        }
        .action.update {
            background: #cce5ff;
            color: #004085;
        }
        .action.delete {
            background: #f8d7da;
            color: #721c24;
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
    <h1>Audit Log Report</h1>
    
    @if(isset($summary))
    <div class="summary">
        <div class="summary-item">
            <label>Total Audit Logs</label>
            <div class="value">{{ $summary['total_logs'] ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <label>Create Actions</label>
            <div class="value">{{ $summary['create_count'] ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <label>Update Actions</label>
            <div class="value">{{ $summary['update_count'] ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <label>Delete Actions</label>
            <div class="value">{{ $summary['delete_count'] ?? 0 }}</div>
        </div>
    </div>
    @endif

    @if($audit_logs && count($audit_logs) > 0)
    <table>
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>User</th>
                <th>Action</th>
                <th>Model Type</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($audit_logs as $log)
            <tr>
                <td>{{ $log['created_at'] ?? 'N/A' }}</td>
                <td>{{ $log['user']['name'] ?? 'System' }}</td>
                <td><span class="action {{ strtolower($log['action'] ?? 'unknown') }}">{{ ucfirst($log['action'] ?? 'Unknown') }}</span></td>
                <td>{{ $log['model_type'] ?? 'N/A' }}</td>
                <td>{{ substr($log['description'] ?? 'No description', 0, 100) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color: #999; text-align: center; margin-top: 20px;">No audit logs found</p>
    @endif

    <div class="footer">
        <p>Generated on {{ date('F d, Y \a\t H:i A') }}</p>
    </div>
</body>
</html>
