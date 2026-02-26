<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eKYC - Under Maintenance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .maintenance-container {
            max-width: 600px;
            width: 100%;
            padding: 20px;
            text-align: center;
        }
        .maintenance-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 60px 40px;
        }
        .maintenance-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 60px;
            color: white;
        }
        h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
        }
        .message {
            font-size: 18px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .schedule {
            background: #f8f9ff;
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
        }
        .schedule-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 10px 0;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-card">
            <div class="maintenance-icon">
                <i class="ti ti-tool"></i>
            </div>
            
            <h1>System Under Maintenance</h1>
            
            <p class="message">
                {{ $message }}
            </p>

            @if($maintenanceStart && $maintenanceEnd)
                <div class="schedule">
                    <strong>Scheduled Maintenance Window</strong>
                    <div class="schedule-item">
                        <i class="ti ti-clock"></i>
                        <span>Start: {{ \Carbon\Carbon::parse($maintenanceStart)->format('d M Y, h:i A') }}</span>
                    </div>
                    <div class="schedule-item">
                        <i class="ti ti-clock"></i>
                        <span>End: {{ \Carbon\Carbon::parse($maintenanceEnd)->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
            @endif

            <div class="mt-4 text-muted">
                <small>We apologize for any inconvenience. Please try again later.</small>
            </div>
        </div>
    </div>
</body>
</html>
