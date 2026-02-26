<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eKYC - Selfie & Face Match</title>
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
        .step.completed { background: #6fd943; color: white; }
        .step.active { background: #6fd943; color: white; }
        .step.completed:not(:last-child)::after { background: #6fd943; }
        .step:not(:last-child)::after { content: ''; position: absolute; width: 50px; height: 2px; background: #e9ecef; right: -50px; top: 50%; transform: translateY(-50%); }
        .camera-box { width: 100%; height: 300px; background: #000; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; color: white; position: relative; overflow: hidden; }
        .face-overlay { width: 200px; height: 250px; border: 2px dashed #6fd943; border-radius: 50%; position: absolute; }
    </style>
</head>
<body>
    <div class="kyc-card">
        <div class="kyc-header">
            <h3>eKYC Process</h3>
            <p>Step 3: Selfie & Face Match</p>
        </div>
        <div class="kyc-body">
            <div class="progress-stepper">
                <div class="step completed">✓</div>
                <div class="step completed">✓</div>
                <div class="step active">3</div>
                <div class="step">4</div>
                <div class="step">5</div>
            </div>
            <div class="camera-box">
                <div class="face-overlay"></div>
                <p>Camera Interface (Demo)</p>
            </div>
            <p class="text-center small text-muted mb-4">Liveness check is required for anti-fraud measures.</p>
            <form action="{{ route('client.kyc.selfie.match') }}" method="POST">
                @csrf
                <button type="submit" class="btn-kyc">{{ __('Capture & Match') }}</button>
            </form>
        </div>
    </div>
</body>
</html>
