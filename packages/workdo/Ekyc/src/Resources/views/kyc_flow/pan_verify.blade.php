<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eKYC - PAN Verification</title>
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        body { background: #f4f7fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .kyc-card { max-width: 500px; width: 100%; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .kyc-header { background: #6fd943; color: white; padding: 30px; text-align: center; }
        .kyc-body { padding: 40px; background: white; }
        .btn-kyc { background: #6fd943; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 600; width: 100%; }
        .btn-kyc:hover { background: #5dbb36; color: white; }
        .progress-stepper { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .step { width: 30px; height: 30px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; position: relative; }
        .step.active { background: #6fd943; color: white; }
        .step:not(:last-child)::after { content: ''; position: absolute; width: 50px; height: 2px; background: #e9ecef; right: -50px; top: 50%; transform: translateY(-50%); }
    </style>
</head>
<body>
    <div class="kyc-card">
        <div class="kyc-header">
            <h3>eKYC Process</h3>
            <p>Step 1: PAN Verification</p>
        </div>
        <div class="kyc-body">
            <div class="progress-stepper">
                <div class="step active">1</div>
                <div class="step">2</div>
                <div class="step">3</div>
                <div class="step">4</div>
                <div class="step">5</div>
            </div>
            <form id="pan-form">
                <div class="form-group mb-4">
                    <label class="form-label">PAN Number</label>
                    <input type="text" class="form-control" name="pan" placeholder="ABCDE1234F" required>
                    <small class="text-muted">Mandatory for SEBI compliance</small>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">Full Name as per PAN</label>
                    <input type="text" class="form-control" name="name" placeholder="John Doe" required>
                </div>
                <button type="submit" class="btn-kyc">{{ __('Verify & Next') }}</button>
            </form>
        </div>
    </div>
</body>
</html>
