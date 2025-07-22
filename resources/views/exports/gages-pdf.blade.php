<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 18px;
            color: #666;
            margin-bottom: 10px;
        }
        .export-date {
            font-size: 10px;
            color: #999;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 11px;
        }
        .table td {
            font-size: 10px;
        }
        .status-overdue {
            color: #dc3545;
            font-weight: bold;
        }
        .status-due-soon {
            color: #ffc107;
            font-weight: bold;
        }
        .status-on-schedule {
            color: #28a745;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name }}</div>
        <div class="report-title">{{ $title }}</div>
        <div class="export-date">Generated on {{ $export_date->format('F j, Y \a\t g:i A') }}</div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Serial Number</th>
                <th>Department</th>
                <th>Frequency</th>
                <th>Next Due Date</th>
                <th>Status</th>
                <th>Days Until Due</th>
                <th>Last Calibration</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gages as $gage)
                @php
                    $status = $gage->getCalibrationStatus();
                    $statusClass = 'status-' . str_replace('_', '-', $status);
                @endphp
                <tr>
                    <td>{{ $gage->name }}</td>
                    <td>{{ $gage->serial_number }}</td>
                    <td>{{ $gage->department->name }}</td>
                    <td>{{ $gage->frequency_days }} days</td>
                    <td>{{ $gage->next_due_date ? $gage->next_due_date->format('Y-m-d') : 'N/A' }}</td>
                    <td class="{{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</td>
                    <td>{{ $gage->getDaysUntilDue() ?? 'N/A' }}</td>
                    <td>{{ $gage->calibrationRecords->first() ? $gage->calibrationRecords->first()->calibrated_at->format('Y-m-d') : 'Never' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Total Gages: {{ $gages->count() }}</p>
        <p>This report was generated automatically by the Calibration Tracking System.</p>
    </div>
</body>
</html>