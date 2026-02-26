<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eKYC - Video KYC (IPV)</title>
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
        .video-overlay { width: 100%; height: 250px; background: #222; border-radius: 10px; position: relative; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; color: #fff; }
        .rec-dot { width: 10px; height: 10px; background: red; border-radius: 50%; position: absolute; top: 15px; left: 15px; animation: blink 1s infinite; }
        @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0; } 100% { opacity: 1; } }
    </style>
</head>
<body>
    <div class="kyc-card">
        <div class="kyc-header">
            <h3>eKYC Process</h3>
            <p>Step 5: In-Person Verification (Video KYC)</p>
        </div>
        <div class="kyc-body">
            <div class="progress-stepper">
                <div class="step completed">✓</div>
                <div class="step completed">✓</div>
                <div class="step completed">✓</div>
                <div class="step completed">✓</div>
                <div class="step active">5</div>
            </div>
            <div class="video-overlay">
                <div class="rec-dot"></div>
                <i class="ti ti-video me-2"></i> Ready for Video KYC
            </div>
            <p class="text-center small mb-4">Please keep your original PAN card ready for verification during the call.</p>
            <form action="{{ route('client.kyc.video.kyc') }}" method="POST">
                @csrf
                <button type="submit" class="btn-kyc">{{ __('Start Video Recording') }}</button>
            </form>
        </div>
    </div>
</body>
</html>
