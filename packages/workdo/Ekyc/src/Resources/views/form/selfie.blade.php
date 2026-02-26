@extends('ekyc::layouts.ekyc')

@section('title', 'Selfie Verification - Stockology eKYC')

@section('additional_css')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
        --glass-bg: rgba(255, 255, 255, 0.9);
        --glass-border: rgba(255, 255, 255, 0.2);
    }
    .selfie-page-wrapper {
        min-height: 80vh; display: flex; align-items: center; justify-content: center;
        background: radial-gradient(circle at top right, rgba(79, 70, 229, 0.05), transparent),
                    radial-gradient(circle at bottom left, rgba(6, 182, 212, 0.05), transparent);
    }
    .selfie-card {
        background: var(--glass-bg); backdrop-filter: blur(10px);
        border-radius: 32px; padding: 40px; text-align: center; max-width: 580px; width: 100%;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--glass-border); position: relative; overflow: hidden;
    }
    .camera-viewport {
        position: relative; width: 320px; height: 320px; margin: 0 auto 30px;
        border-radius: 50%; padding: 8px; background: white;
        box-shadow: 0 0 0 1px rgba(0,0,0,0.05), 0 10px 30px rgba(0,0,0,0.1);
    }
    .camera-container {
        position: relative; width: 100%; height: 100%; border-radius: 50%;
        overflow: hidden; background: #000;
        z-index: 5;
    }
    #video { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
    
    .radar-pulse {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        border-radius: 50%; border: 2px solid #4f46e5;
        animation: radar-beam 3s infinite ease-out; opacity: 0; pointer-events: none;
    }
    @keyframes radar-beam {
        0% { transform: scale(0.95); opacity: 0.5; }
        100% { transform: scale(1.15); opacity: 0; }
    }

    .scanner-line {
        position: absolute; top: 0; left: 0; width: 100%; height: 60px;
        background: linear-gradient(180deg, rgba(79, 70, 229, 0) 0%, rgba(79, 70, 229, 0.2) 100%);
        border-bottom: 2px solid #4f46e5; animation: scan-move 2.5s infinite ease-in-out;
        display: none; z-index: 10;
    }
    @keyframes scan-move {
        0% { transform: translateY(-100%); }
        100% { transform: translateY(320px); }
    }

    .capture-ring {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
        width: 105%; height: 105%; border: 2px solid transparent;
        border-top-color: #4f46e5; border-radius: 50%;
        animation: spin 3s linear infinite; display: none;
    }
    @keyframes spin { 100% { transform: translate(-50%, -50%) rotate(360deg); } }

    .status-pill {
        display: inline-flex; align-items: center; padding: 6px 16px; 
        border-radius: 100px; font-size: 0.75rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 24px;
        background: #f1f5f9; color: #64748b; transition: all 0.3s;
    }
    .status-pill.active { background: #eef2ff; color: #4f46e5; border: 1px solid rgba(79, 70, 229, 0.1); }

    .btn-capture-selfie {
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 14px 40px;
        border-radius: 100px;
        font-weight: 700;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
        transition: all 0.3s;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .btn-capture-selfie:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px rgba(79, 70, 229, 0.4);
    }
    .btn-capture-selfie:active:not(:disabled) {
        transform: translateY(0);
    }
    .btn-capture-selfie:disabled {
        background: #cbd5e1;
        box-shadow: none;
        cursor: not-allowed;
    }

    .instruction-icon { font-size: 1.25rem; color: #4f46e5; margin-right: 12px; }
    .preview-img { width: 100%; height: 100%; object-fit: cover; display: none; transform: scaleX(-1); }
    
    .result-box {
        display: none; padding: 20px; border-radius: 20px; margin-top: 20px;
        animation: slideUp 0.4s ease-out;
    }
    @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>
@endsection

@section('content')
<div class="selfie-page-wrapper">
    <div class="selfie-card">
        <div class="status-pill active" id="current-step-badge">
            <span class="spinner-grow spinner-grow-sm me-2" role="status"></span>
            Biometric Analysis Mode
        </div>
        
        <h3 class="fw-bold text-dark mb-1">Face Verification</h3>
        <p class="text-muted small mb-4">Securely matching your identity via Encrypted AI</p>

        <div class="camera-viewport">
            <div class="radar-pulse"></div>
            <div class="capture-ring" id="capture-ring"></div>
            <div class="camera-container">
                <video id="video" autoplay playsinline></video>
                <img id="preview" class="preview-img" alt="Captured Selfie">
                <div class="scanner-line" id="scanner"></div>
            </div>
        </div>

        <div id="ai-feedback" class="mb-4 text-center small text-muted min-vh-20">
            <div class="d-flex align-items-center justify-content-center mb-2" id="feedback-icon-box">
                <i class="ti ti-loader-2 spin fs-4 text-primary"></i>
            </div>
            <div id="feedback-text" class="fw-semibold text-dark">Initializing Secure AI Engine...</div>
            <div id="feedback-subtext" class="opacity-75">Fetching latest biometric models</div>
        </div>

        <div id="kyc-error" class="alert alert-danger d-none text-start small mb-4"></div>

        <div class="d-flex justify-content-center gap-4 py-2">
            <button type="button" id="capture-btn" class="btn-capture-selfie" disabled>
                <i class="ti ti-camera fs-4"></i>
                <span id="capture-btn-text">Detecting Face...</span>
            </button>
            <button type="button" id="retry-btn" class="btn btn-light rounded-pill px-4 py-2 d-none shadow-sm fw-semibold">
                <i class="ti ti-refresh me-1"></i> Retake
            </button>
            <button type="button" id="confirm-btn" class="btn btn-dark rounded-pill px-5 py-2 d-none shadow-lg fw-bold">
                <i class="ti ti-shield-check me-2"></i> Confirm Identity
            </button>
        </div>

        <div id="result-display" class="result-box text-start">
            <div class="d-flex align-items-center">
                <div id="result-icon" class="fs-2 me-3 p-3 bg-white rounded-circle shadow-sm"></div>
                <div>
                    <div id="result-title" class="fw-bold fs-5 text-dark"></div>
                    <div id="result-msg" class="small text-muted"></div>
                </div>
            </div>
        </div>
        
        <canvas id="canvas" class="d-none"></canvas>
        
        <div class="mt-5 border-top pt-4">
            <div class="row text-center g-3">
                <div class="col-4">
                    <i class="ti ti-shield-lock d-block text-primary fs-4 mb-1"></i>
                    <span class="d-block small text-muted" style="font-size: 0.65rem;">End-to-End Encrypted</span>
                </div>
                <div class="col-4">
                    <i class="ti ti-device-mobile d-block text-primary fs-4 mb-1"></i>
                    <span class="d-block small text-muted" style="font-size: 0.65rem;">Edge Processing</span>
                </div>
                <div class="col-4">
                    <i class="ti ti-user-check d-block text-primary fs-4 mb-1"></i>
                    <span class="d-block small text-muted" style="font-size: 0.65rem;">Privacy Compliant</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('additional_js')
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const preview = document.getElementById('preview');
    const captureBtn = document.getElementById('capture-btn');
    const retryBtn = document.getElementById('retry-btn');
    const confirmBtn = document.getElementById('confirm-btn');
    const errorBox = document.getElementById('kyc-error');
    const scanner = document.getElementById('scanner');

    let stream = null;
    let modelsLoaded = false;
    let faceMatcher = null;
    let detectionInterval = null;
    let livenessConfirmed = false;
    
    // Config
    const MODEL_URL = "{{ asset('assets/face-api-models/') }}/";
    const AADHAAR_BASE64 = "{{ $aadhaar_photo }}";

    function updateFeedback(title, subtext, iconClass = 'ti ti-loader-2 spin', color = 'primary') {
        const textEl = document.getElementById('feedback-text');
        const subEl = document.getElementById('feedback-subtext');
        const iconBox = document.getElementById('feedback-icon-box');
        
        textEl.innerText = title;
        subEl.innerText = subtext;
        iconBox.innerHTML = `<i class="${iconClass} fs-4 text-${color}"></i>`;
    }

    let obstructionBuffer = [];
    const BUFFER_SIZE = 8; // Number of frames to stabilize detection

    async function loadHighPrecisionModels() {
        try {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);
            console.log("High-precision models loaded in background");
            
            if (AADHAAR_BASE64 && !faceMatcher) {
                await prepareReference();
            }
        } catch (err) {
            console.warn("Background model load failed:", err);
        }
    }

    let userLocation = { lat: null, lng: null, address: 'Detecting address...' };

    async function reverseGeocode(lat, lng) {
        try {
            const response = await fetch("{{ route('ekyc.form.reverse-geocode') }}?lat=" + lat + "&lng=" + lng);
            const data = await response.json();
            if (data && data.success) {
                userLocation.address = data.address;
                if (document.getElementById('feedback-text').innerText === 'System Ready' || document.getElementById('feedback-text').innerText === 'Warming Up') {
                    updateFeedback('System Ready', `Location: ${userLocation.address}`, 'ti ti-map-pin-check', 'info');
                }
            }
        } catch (err) {
            userLocation.address = "Address unavailable";
        }
    }

    async function getGeolocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(async (pos) => {
                userLocation.lat = pos.coords.latitude;
                userLocation.lng = pos.coords.longitude;
                await reverseGeocode(userLocation.lat, userLocation.lng);
            }, (err) => {
                userLocation.address = "Location permission denied";
            });
        }
    }

    async function initFaceAI() {
        updateFeedback('Warming Up', 'Igniting fast tracking engine...', 'ti ti-flame', 'primary');
        getGeolocation();
        
        // Start camera immediately so user sees something while models load
        startCamera();
        
        // Timeout to ensure user can still capture if models take too long (e.g. slow connection)
        setTimeout(() => {
            if (!modelsLoaded) {
                console.log("AI Model load timeout - enabling bypass capture");
                updateFeedback('System Ready', 'Biometric tracking is slow - you can still capture manually.', 'ti ti-camera', 'info');
                captureBtn.disabled = false;
                document.getElementById('capture-btn-text').innerText = 'Capture Selfie (Standard)';
            }
        }, 4000);

        try {
            // Priority 1: Fast tracking & Landmarks
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL)
            ]);
            
            modelsLoaded = true;
            console.log("Priority models loaded");
            
            // Priority 2: High precision (SSD, Recognition) loaded in background
            loadHighPrecisionModels();
            
        } catch (err) {
            console.warn("AI Model load failed - switching to manual capture mode.");
            // Don't show blocking error anymore, just log it.
            // if initialization failed, the timeout above will handle enabling the button.
        }
    }

    function checkObstructions(detection) {
        if (!detection || !detection.landmarks) return { cap: false };
        
        const landmarks = detection.landmarks.positions;
        const box = detection.detection.box;
        
        // Refined Cap Check: Use the distance from eyes to the top of the box
        const leftEyeY = (landmarks[36].y + landmarks[39].y) / 2;
        const rightEyeY = (landmarks[42].y + landmarks[45].y) / 2;
        const eyeCenterY = (leftEyeY + rightEyeY) / 2;
        
        const faceHeight = box.height;
        const upperFaceHeight = eyeCenterY - box.top;
        
        // Ratio of upper face (eyes to top) to total face height
        // Normally, eyes are roughly in the middle, so this should be ~35-45%
        // If it's less than 25%, something (like a cap) is likely covering the forehead
        const upperFaceRatio = upperFaceHeight / faceHeight;
        const rawHasCap = upperFaceRatio < 0.28;

        // Stability buffering
        obstructionBuffer.push(rawHasCap);
        if (obstructionBuffer.length > BUFFER_SIZE) obstructionBuffer.shift();
        
        // Only confirm if detected in majority of recent frames
        const capCount = obstructionBuffer.filter(Boolean).length;
        const hasCap = capCount >= (BUFFER_SIZE * 0.7);
        
        return { cap: hasCap };
    }

    async function prepareReference() {
        try {
            const img = new Image();
            img.src = 'data:image/jpeg;base64,' + AADHAAR_BASE64;
            await img.decode();
            const results = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor();
            if (results) {
                faceMatcher = new faceapi.FaceMatcher(results);
                console.log("SSD Ref Prepared");
            }
        } catch (err) {}
    }

    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: "user", width: 640, height: 480 }, 
                audio: false 
            });
            video.srcObject = stream;
            video.onloadedmetadata = () => {
                updateFeedback('System Ready', 'Please align your face in the center', 'ti ti-video', 'info');
                startDetectionLoop();
            };
        } catch (err) {
            showError("Camera access denied. Enable permissions to proceed.");
        }
    }

    function startDetectionLoop() {
        if (detectionInterval) clearInterval(detectionInterval);
        
        detectionInterval = setInterval(async () => {
            if (!modelsLoaded) return;
            
            // Use detectAllFaces to enforce single person requirement
            const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks();

            if (detections.length > 1) {
                scanner.style.display = 'none';
                document.getElementById('capture-ring').style.display = 'none';
                captureBtn.disabled = true;
                document.getElementById('capture-btn-text').innerText = 'Single Person Required';
                livenessConfirmed = false;
                updateFeedback('Single Person Required', 'Multiple faces detected in frame. Please be alone.', 'ti ti-users-minus', 'danger');
            } else if (detections.length === 1) {
                const obstructions = checkObstructions(detections[0]);
                
                if (obstructions.cap) {
                    scanner.style.display = 'none';
                    document.getElementById('capture-ring').style.display = 'none';
                    captureBtn.disabled = true;
                    document.getElementById('capture-btn-text').innerText = 'Obstruction Detected';
                    livenessConfirmed = false;
                    updateFeedback('Obstruction Detected', 'Please remove cap/hat for verification', 'ti ti-mop', 'warning');
                } else {
                    scanner.style.display = 'block';
                    document.getElementById('capture-ring').style.display = 'block';
                    captureBtn.disabled = false;
                    document.getElementById('capture-btn-text').innerText = 'Capture Selfie';
                    
                    if (!livenessConfirmed) {
                        livenessConfirmed = true;
                        updateFeedback('Face Locked', 'Identity confirmed. You can now capture.', 'ti ti-circle-check', 'success');
                    }
                }
            } else {
                scanner.style.display = 'none';
                document.getElementById('capture-ring').style.display = 'none';
                captureBtn.disabled = true;
                document.getElementById('capture-btn-text').innerText = 'Detecting Face...';
                
                if (livenessConfirmed) {
                    livenessConfirmed = false;
                    updateFeedback('Searching...', 'Please look directly into the scanner', 'ti ti-face-id-error', 'warning');
                }
            }
        }, 500);
    }

    captureBtn.addEventListener('click', async () => {
        clearInterval(detectionInterval);
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.translate(canvas.width, 0);
        context.scale(-1, 1);
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
        preview.src = dataUrl;
        
        video.style.display = 'none';
        preview.style.display = 'block';
        captureBtn.classList.add('d-none');
        retryBtn.classList.remove('d-none');
        confirmBtn.classList.remove('d-none');
        document.getElementById('capture-ring').style.display = 'none';
        
        performMatch();
    });

    async function performMatch() {
        // Ensure background models are ready before matching
        if (!faceapi.nets.ssdMobilenetv1.params) {
            updateFeedback('Synchronizing', 'Finalizing security patterns...', 'ti ti-refresh spin', 'primary');
            await new Promise(r => setTimeout(r, 1000));
            return performMatch();
        }

        if (!faceMatcher) {
            showResult('manual_review', 'Match Pending', 'Aadhaar data quality low. Manual validation required.');
            window.lastMatchScore = 50;
            return;
        }

        updateFeedback('Identity Analysis', 'Cross-referencing biometric patterns...', 'ti ti-adjustments-alt spin', 'primary');
        
        // Final check for multiple people in the captured frame
        const allResults = await faceapi.detectAllFaces(preview).withFaceLandmarks().withFaceDescriptors();
        
        if (allResults.length > 1) {
            showResult('fail', 'Security Violation', 'Multiple people detected in image. Please ensure you are alone.');
            updateFeedback('Access Denied', 'Multi-face sample rejected for security.', 'ti ti-users-minus', 'danger');
            confirmBtn.classList.add('d-none');
            setTimeout(() => retryBtn.click(), 4000);
            return;
        }

        if (allResults.length === 1) {
            const singleResult = allResults[0];
            const bestMatch = faceMatcher.findBestMatch(singleResult.descriptor);
            const score = Math.round((1 - bestMatch.distance) * 100);
            window.lastMatchScore = score;
            
            if (score >= 60) {
                showResult('success', 'Identity Verified', `Biometric Match: ${score}% - Trusted identity.`);
                updateFeedback('Verified', 'Match score exceeds security threshold', 'ti ti-shield-check', 'success');
            } else if (score >= 35) {
                showResult('manual_review', 'Queue for Review', `Match: ${score}% - Borderline. Officer will manually verify.`);
                updateFeedback('Review Pending', 'Identity queued for manual oversight', 'ti ti-hourglass', 'warning');
            } else {
                showResult('fail', 'Mismatch Detected', `Confidence ${score}% is too low. Please retry in better light.`);
                updateFeedback('Retry Failed', 'Significant variance detected. Resetting camera in 3s...', 'ti ti-alert-triangle', 'danger');
                confirmBtn.classList.add('d-none'); // Force retry
                setTimeout(() => {
                    if (preview.style.display === 'block') {
                        retryBtn.click();
                    }
                }, 3000);
            }
        } else {
            showError("Face analysis failed. Ensure face is clear.");
            retryBtn.click();
        }
    }

    confirmBtn.addEventListener('click', () => {
        const score = window.lastMatchScore || 0;
        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Finalizing...';

        $.ajax({
            url: "{{ route('ekyc.form.submit-step', ['step' => $step]) }}",
            type: 'POST',
            data: { 
                _token: '{{ csrf_token() }}', 
                selfie_data: dataUrl, 
                match_score: score,
                lat: userLocation.lat,
                lng: userLocation.lng,
                address: userLocation.address
            },
            success: (res) => {
                if (res.success) window.location.href = res.redirect;
                else { showError(res.message); confirmBtn.disabled = false; }
            },
            error: () => { showError("Communication error with vault."); confirmBtn.disabled = false; }
        });
    });

    retryBtn.addEventListener('click', () => {
        video.style.display = 'block';
        preview.style.display = 'none';
        captureBtn.classList.remove('d-none');
        retryBtn.classList.add('d-none');
        confirmBtn.classList.add('d-none');
        errorBox.classList.add('d-none');
        document.getElementById('result-display').style.display = 'none';
        livenessConfirmed = false;
        startDetectionLoop();
    });

    function showResult(type, title, msg) {
        const rd = document.getElementById('result-display');
        const rt = document.getElementById('result-title');
        const rm = document.getElementById('result-msg');
        const ri = document.getElementById('result-icon');
        
        rd.className = 'result-box mb-4 result-' + (type === 'fail' ? 'fail' : (type === 'manual_review' ? 'review' : 'success'));
        rd.style.background = type === 'success' ? '#ecfdf5' : (type === 'fail' ? '#fef2f2' : '#fffbeb');
        rd.style.border = `1px solid ${type === 'success' ? '#10b981' : (type === 'fail' ? '#ef4444' : '#f59e0b')}`;
        
        rt.innerText = title;
        rm.innerText = msg;
        ri.innerHTML = `<i class="ti ti-${type === 'success' ? 'circle-check' : (type === 'fail' ? 'circle-x' : 'alert-circle')} text-${type === 'success' ? 'success' : (type === 'fail' ? 'danger' : 'warning')}"></i>`;
        rd.style.display = 'block';
    }

    function showError(msg) {
        errorBox.innerHTML = `<strong>Security Notice:</strong> ${msg}`;
        errorBox.classList.remove('d-none');
    }

    initFaceAI();
</script>
@endsection
