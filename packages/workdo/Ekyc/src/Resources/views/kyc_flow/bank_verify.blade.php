<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eKYC - Bank Account Verification</title>
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        body { background: #f4f7fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .kyc-card { max-width: 500px; width: 100%; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .kyc-header { background: #6fd943; color: white; padding: 30px; text-align: center; }
        .kyc-body { padding: 40px; background: white; }
        .btn-kyc { background: #6fd943; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 600; width: 100%; }
        .btn-kyc:hover { background: #5dbb36; color: white; }
        .step { width: 30px; height: 30px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; position: relative; }
        .step.completed { background: #6fd943; color: white; }
        .step.active { background: #6fd943; color: white; }
        .step.completed:not(:last-child)::after { background: #6fd943; }
        .step:not(:last-child)::after { content: ''; position: absolute; width: 50px; height: 2px; background: #e9ecef; right: -50px; top: 50%; transform: translateY(-50%); }
        .progress-stepper { display: flex; justify-content: space-between; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="kyc-card">
        <div class="kyc-header">
            <h3>eKYC Process</h3>
            <p>Step 4: Bank Account Verification</p>
        </div>
        <div class="kyc-body">
            <div class="progress-stepper">
                <div class="step completed">✓</div>
                <div class="step completed">✓</div>
                <div class="step completed">✓</div>
                <div class="step active">4</div>
                <div class="step">5</div>
            </div>
            <form action="{{ route('client.kyc.bank.verify') }}" method="POST">
                @csrf
                <div class="form-group mb-4">
                    <label class="form-label">Account Number</label>
                    <input type="text" class="form-control" name="account_number" placeholder="XXXXXXXXXXXX" required>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">IFSC Code</label>
                    <input type="text" class="form-control" name="ifsc" placeholder="SBIN0001234" required>
                </div>
                <div class="alert alert-info py-2 small">
                    <i class="ti ti-info-circle me-1"></i> A penny will be dropped to verify your account name.
                </div>
                <button type="submit" class="btn-kyc">{{ __('Verify Bank Details') }}</button>
            </form>
        </div>
    </div>
</body>
</html>
