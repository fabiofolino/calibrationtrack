<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Calibration Reminder</title>
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
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .gage-list {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .gage-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .gage-item:last-child {
            border-bottom: none;
        }
        .gage-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .gage-details {
            color: #666;
            font-size: 14px;
        }
        .due-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .due-soon {
            background-color: #fff3cd;
            color: #856404;
        }
        .overdue {
            background-color: #f8d7da;
            color: #721c24;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Calibration Reminder</h1>
        <p><strong>Department:</strong> {{ $department->name }}</p>
        @if($department->manager_name)
            <p><strong>Manager:</strong> {{ $department->manager_name }}</p>
        @endif
    </div>

    <div>
        <p>Dear {{ $department->manager_name ?? 'Department Manager' }},</p>
        
        <p>This is a reminder that the following gages in your department are due for calibration within the next 7 days:</p>
    </div>

    <div class="gage-list">
        @foreach($gages as $gage)
            @php
                $daysUntilDue = now()->diffInDays($gage->next_due_date, false);
                $isOverdue = $daysUntilDue < 0;
                $statusClass = $isOverdue ? 'overdue' : 'due-soon';
                $statusText = $isOverdue ? 'Overdue (' . abs($daysUntilDue) . ' days ago)' : 'Due in ' . $daysUntilDue . ' days';
            @endphp
            
            <div class="gage-item">
                <div class="gage-name">{{ $gage->name }}</div>
                <div class="gage-details">
                    <strong>Serial Number:</strong> {{ $gage->serial_number }}<br>
                    <strong>Frequency:</strong> {{ $gage->frequency_days }} days<br>
                    <strong>Due Date:</strong> {{ $gage->next_due_date->format('M j, Y') }}<br>
                    <span class="due-status {{ $statusClass }}">{{ $statusText }}</span>
                    
                    @if($gage->calibrationRecords->count() > 0)
                        <br><strong>Last Calibration:</strong> {{ $gage->calibrationRecords->first()->calibrated_at->format('M j, Y') }}
                    @else
                        <br><strong>Last Calibration:</strong> Never calibrated
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div>
        <p>Please schedule calibrations for these gages as soon as possible to ensure compliance with your quality management system.</p>
        
        <p>If you have any questions or need assistance, please contact your quality assurance team.</p>
        
        <p>Best regards,<br>Calibration Tracking System</p>
    </div>

    <div class="footer">
        <p>This is an automated reminder from the Calibration Tracking System.</p>
        <p>Generated on {{ now()->format('M j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>