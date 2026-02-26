<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eKYC - Status</title>
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        body { background: #f4f7fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .status-card { max-width: 500px; width: 100%; text-align: center; background: white; padding: 50px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .success-icon { font-size: 80px; color: #6fd943; margin-bottom: 20px; }
        .btn-home { background: #6fd943; color: white; border-radius: 8px; padding: 12px 30px; text-decoration: none; display: inline-block; margin-top: 30px; }
        .btn-home:hover { background: #5dbb36; color: white; }
    </style>
</head>
<body>
    <div class="status-card">
        <div class="success-icon"><i class="ti ti-circle-check"></i></div>
        <h2>KYC Submitted!</h2>
        <p class="text-muted">Thank you for completing the eKYC process. Your documents are being verified by our compliance team. You will be notified once approved.</p>
        <div class="mt-4 text-start small">
            <h6>Verification Timeline:</h6>
            <ul class="list-unstyled">
                <li><i class="ti ti-check text-success"></i> PAN & Aadhaar: Instant</li>
                <li><i class="ti ti-check text-success"></i> Bank Verification: Instant</li>
                <li><i class="ti ti-clock text-warning"></i> Video KYC: Under Review (24-48 hrs)</li>
            </ul>
        </div>
        <a href="/" class="btn-home">{{ __('Back to Dashboard') }}</a>
    </div>
</body>
</html>
