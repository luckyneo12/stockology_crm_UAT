<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Document Verification - Stockology eKYC</title>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <style>
        :root {
            --primary-color: #10b981;
            --primary-dark: #059669;
            --primary-light: #d1fae5;
            --dark-bg: #111827;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #F9FAFB;
            color: var(--text-main);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .kyc-container {
            max-width: 1100px;
            margin: 0 auto;
        }

        /* Progress Stepper */
        .stepper {
            display: flex;
            justify-content: space-between;
            margin-bottom: 50px;
            position: relative;
        }

        .stepper::before {
            content: '';
            position: absolute;
            top: 25px;
            left: 0;
            right: 0;
            height: 4px;
            background: #e5e7eb;
            z-index: 1;
        }

        .step-item {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 80px;
        }

        .step-icon {
            width: 50px;
            height: 50px;
            background: white;
            border: 4px solid #e5e7eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #9ca3af;
            transition: var(--transition);
        }

        .step-item.active .step-icon {
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: scale(1.1);
        }

        .step-item.completed .step-icon {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .step-label {
            margin-top: 10px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #9ca3af;
            text-align: center;
        }

        /* Layout */
        .doc-card {
            background: white;
            border-radius: 28px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.06);
            overflow: hidden;
            border: 1px solid #f1f5f9;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .checklist-sidebar {
            background: #F8FAFC;
            padding: 40px;
            height: 100%;
            border-right: 1px solid #f1f5f9;
        }

        .main-content {
            padding: 40px;
        }

        .checklist-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: white;
            border-radius: 16px;
            margin-bottom: 12px;
            border: 1px solid #e2e8f0;
            transition: var(--transition);
        }

        .checklist-item.verified {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }

        .checklist-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #E2E8F0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
        }

        .verified .checklist-icon {
            background: var(--primary-color);
            color: white;
        }

        .checklist-text {
            font-size: 0.9rem;
            font-weight: 600;
            color: #475569;
        }

        /* Capture Sections */
        .capture-area {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .capture-box {
            background: #F8FAFC;
            border: 2px dashed #CBD5E1;
            border-radius: 24px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .capture-box:hover {
            border-color: var(--primary-color);
            background: white;
        }

        .capture-btn {
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 15px;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
        }

        #signature-pad {
            width: 100%;
            height: 200px;
            background: white;
            border-radius: 15px;
            border: 1px solid #E2E8F0;
            touch-action: none;
        }

        .preview-img {
            max-width: 100%;
            max-height: 150px;
            border-radius: 12px;
            display: none;
        }

        .btn-submit {
            width: 100%;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 20px;
            border-radius: 20px;
            font-size: 1.2rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: var(--transition);
            box-shadow: 0 15px 30px rgba(16, 185, 129, 0.25);
        }

        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
        }

        @media (max-width: 991px) {
            .capture-area { grid-template-columns: 1fr; }
            .checklist-sidebar { padding: 30px; display: none; }
        }
    </style>
</head>
<body>

    <div class="kyc-container">
        
        <!-- Stepper -->
        <div class="stepper">
            @foreach($enabledSteps as $num => $info)
                <div class="step-item {{ $num < $step ? 'completed' : ($num == $step ? 'active' : '') }}">
                    <div class="step-icon">
                        @if($num < $step)
                            <i class="ti ti-check"></i>
                        @else
                            {{ $num }}
                        @endif
                    </div>
                    <div class="step-label">{{ __($info['name']) }}</div>
                </div>
            @endforeach
        </div>

        <div class="doc-card">
            <div class="row g-0">
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="checklist-sidebar">
                        <h4 class="fw-bold mb-4">Verification Checklist</h4>
                        
                        <div class="checklist-item verified">
                            <div class="checklist-icon"><i class="ti ti-check"></i></div>
                            <span class="checklist-text">Aadhaar Verified</span>
                        </div>
                        <div class="checklist-item verified">
                            <div class="checklist-icon"><i class="ti ti-check"></i></div>
                            <span class="checklist-text">PAN Details Fetched</span>
                        </div>
                        <div class="checklist-item verified">
                            <div class="checklist-icon"><i class="ti ti-check"></i></div>
                            <span class="checklist-text">Bank Account Linked</span>
                        </div>
                        <div class="checklist-item {{ $submission->face_selfie ? 'verified' : '' }}" id="chk-selfie">
                            <div class="checklist-icon"><i class="ti ti-camera"></i></div>
                            <span class="checklist-text">Live Selfie</span>
                        </div>
                        <div class="checklist-item {{ $submission->signature ? 'verified' : '' }}" id="chk-sign">
                            <div class="checklist-icon"><i class="ti ti-signature"></i></div>
                            <span class="checklist-text">Signature Capture</span>
                        </div>

                        <div class="mt-5 p-3 bg-white border rounded-4 small text-muted">
                            <p class="mb-2"><i class="ti ti-bulb text-warning me-2"></i> <strong>Tip:</strong></p>
                            Use a plain background for your selfie and sign on a white paper or digital screen clearly.
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-8">
                    <div class="main-content">
                        <h2 class="fw-bold mb-2">Final Step: AI Verification</h2>
                        <p class="text-muted mb-5">Click capture to finish your application instantly.</p>

                        @php
                            $settings = getCompanyAllSetting();
                            $isKycTesting = !empty($settings['ekyc_testing_mode']) && $settings['ekyc_testing_mode'] == 'on';
                        @endphp

                        @if($isKycTesting)
                            <div class="alert alert-info d-flex align-items-center mb-4" style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--primary-color); border-radius: 12px;">
                                <i class="ti ti-info-circle me-2 fs-4 text-primary"></i>
                                <div class="flex-grow-1">
                                    <strong>Testing Mode Active:</strong> Use demo captures to skip camera.
                                </div>
                                <button type="button" class="btn btn-sm btn-primary" id="fillDemoSelfie">
                                    <i class="ti ti-magic-wand me-1"></i> Use Demo Selfie
                                </button>
                            </div>
                        @endif

                        <form id="docsForm" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="capture-area">
                                <!-- Signature Section -->
                                <div class="text-start">
                                    <label class="form-label mb-3">Your Digital Signature</label>
                                    <div class="capture-box" id="sign-box">
                                        @if($submission->signature)
                                            <img src="{{ route('ekyc.submission.image', ['id' => $submission->id, 'field' => 'signature']) }}" style="max-height: 150px; border-radius: 12px; margin-bottom: 10px;">
                                            <div class="text-success small mb-2"><i class="ti ti-circle-check-filled"></i> Already Uploaded</div>
                                        @endif
                                        <canvas id="signature-pad" style="{{ $submission->signature ? 'display:none' : '' }}"></canvas>
                                        <div class="mt-2 d-flex justify-content-between">
                                            <button type="button" class="btn btn-sm text-danger fw-bold" id="clear-sign">{{ $submission->signature ? 'Redo' : 'Clear' }}</button>
                                            <span class="text-muted small">Sign inside the box</span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="signature_data" id="signature_input" value="{{ $submission->signature }}">
                                </div>

                                <!-- Selfie Section -->
                                <div class="text-start">
                                    <label class="form-label mb-3">Live Selfie Capture</label>
                                    <div class="capture-box" id="selfie-box">
                                        <div id="camera-overlay" style="{{ $submission->face_selfie ? 'display:none' : '' }}">
                                            <div class="capture-btn" id="open-camera">
                                                <i class="ti ti-camera"></i>
                                            </div>
                                            <h6 class="fw-bold">Open Camera</h6>
                                            <p class="small text-muted">Click to capture live photo</p>
                                        </div>
                                        @if($submission->face_selfie)
                                            <img src="{{ route('ekyc.submission.image', ['id' => $submission->id, 'field' => 'face_selfie']) }}" style="max-height: 150px; border-radius: 12px; margin-bottom: 10px;">
                                            <div class="text-success small mb-2"><i class="ti ti-circle-check-filled"></i> Already Uploaded</div>
                                        @endif
                                        <video id="video-preview" style="display:none; width:100%; border-radius:15px;" autoplay playsinline></video>
                                        <canvas id="photo-canvas" style="display:none"></canvas>
                                        <img id="selfie-preview" class="preview-img" alt="Selfie Preview">
                                        <button type="button" class="btn btn-primary w-100 mt-2" id="take-photo" style="display:none">Capture Photo</button>
                                    </div>
                                    <input type="hidden" name="selfie_data" id="selfie_input" value="{{ $submission->face_selfie }}">
                                </div>
                            </div>

                            <!-- Optional Documents -->
                            <div class="mb-5">
                                <label class="form-label mb-3">Income Proof (Passbook / 6M Statement)</label>
                                <div class="input-group">
                                    <input type="file" name="income_proof" class="form-control rounded-4 p-3 border-2" accept=".pdf,.jpg,.png">
                                </div>
                                <p class="small text-muted mt-2">Required only for Derivative (F&O) segment trading.</p>
                            </div>

                            <button type="submit" class="btn-submit" id="submitBtn">
                                Complete Application
                                <i class="ti ti-circle-check"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Signature Pad Logic
            const canvas = document.querySelector("#signature-pad");
            const signaturePad = new SignaturePad(canvas);

            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
            }
            window.addEventListener("resize", resizeCanvas);
            resizeCanvas();

            $('#clear-sign').on('click', () => signaturePad.clear());

            // Camera Capture Logic
            let stream = null;
            $('#open-camera').on('click', async function() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                    const video = document.getElementById('video-preview');
                    video.srcObject = stream;
                    video.style.display = 'block';
                    $('#camera-overlay').hide();
                    $('#take-photo').show();
                } catch (err) {
                    alert('Could not access camera: ' + err.message);
                }
            });

            $('#take-photo').on('click', function() {
                const video = document.getElementById('video-preview');
                const canvas = document.getElementById('photo-canvas');
                const photo = document.getElementById('selfie-preview');
                
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                
                const data = canvas.toDataURL('image/jpeg');
                photo.src = data;
                photo.style.display = 'block';
                
                video.style.display = 'none';
                $(this).hide();
                $('#selfie_input').val(data);
                
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                }

                $('#chk-selfie').addClass('verified');
            });

            // Demo Selfie Logic
            $('#fillDemoSelfie').on('click', function() {
                // Using a transparent 1x1 pixel as a placeholder or a generic base64 image if possible
                const demoCanvas = document.createElement('canvas');
                demoCanvas.width = 300;
                demoCanvas.height = 300;
                const ctx = demoCanvas.getContext('2d');
                ctx.fillStyle = '#10b981';
                ctx.fillRect(0,0,300,300);
                ctx.fillStyle = 'white';
                ctx.font = '20px Inter';
                ctx.fillText('DEMO SELFIE', 80, 150);
                
                const data = demoCanvas.toDataURL('image/jpeg');
                $('#selfie-preview').attr('src', data).show();
                $('#camera-overlay').hide();
                $('#video-preview').hide();
                $('#take-photo').hide();
                $('#selfie_input').val(data);
                $('#chk-selfie').addClass('verified');
                
                // Also auto-fill signature for convenience in testing mode
                if (signaturePad.isEmpty()) {
                   ctx.clearRect(0,0,300,300);
                   ctx.strokeStyle = 'black';
                   ctx.lineWidth = 2;
                   ctx.beginPath();
                   ctx.moveTo(50, 200);
                   ctx.lineTo(250, 100);
                   ctx.stroke();
                   const sigData = demoCanvas.toDataURL();
                   $('#signature_input').val(sigData);
                   $('#chk-sign').addClass('verified');
                   // Show a visual hint in sign box
                   $('#sign-box canvas').hide();
                   $('#sign-box img').remove(); // Remove existing if any
                   $('<img src="'+sigData+'" style="max-height: 100px; margin-bottom: 10px;">').prependTo('#sign-box');
                }
            });

            signaturePad.addEventListener("endStroke", () => {
                $('#chk-sign').addClass('verified');
            });

            // Form Submission
            $('#docsForm').on('submit', function(e) {
                e.preventDefault();
                
                if (signaturePad.isEmpty()) {
                    alert('Please provide your signature');
                    return;
                }
                if (!$('#selfie_input').val()) {
                    alert('Please capture a live selfie');
                    return;
                }

                $('#signature_input').val(signaturePad.toDataURL());

                const btn = $('#submitBtn');
                const originalHtml = btn.html();
                btn.prop('disabled', true).html('<i class="ti ti-loader-2 animate-spin"></i> Finalizing Application...');

                $.ajax({
                    url: '{{ route("ekyc.form.submit-step", ["step" => $step]) }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.redirect;
                        } else {
                            alert(response.message || 'Submission failed');
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        alert(response.message || 'An error occurred');
                        btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });
        });
    </script>
</body>
</html>
