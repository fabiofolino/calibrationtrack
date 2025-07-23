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
                <th>Gage Name</th>
                <th>Serial Number</th>
                <th>Department</th>
                <th>Measured Value</th>
                <th>Calibrated Value</th>
                <th>Calibrated At</th>
                <th>Calibrated By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
                <tr>
                    <td>{{ $record->gage->name }}</td>
                    <td>{{ $record->gage->serial_number }}</td>
                    <td>{{ $record->gage->department->name }}</td>
                    <td>{{ $record->measured_value }}</td>
                    <td>{{ $record->calibrated_value }}</td>
                    <td>{{ $record->calibrated_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $record->user->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Total Records: {{ $records->count() }}</p>
        <p>This report was generated automatically by the Calibration Tracking System.</p>
    </div>
</body>
</html>