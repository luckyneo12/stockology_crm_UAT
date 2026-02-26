@extends('ekyc::layouts.ekyc')

@section('title', 'Video KYC - Stockology E-KYC')

@section('additional_css')
<style>
    .video-kyc-container {
        position: relative;
        width: 100%;
        background: #000;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: var(--shadow-premium);
        aspect-ratio: 4/3;
        margin-bottom: 2rem;
    }
    #video-kyc {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .recording-indicator {
        position: absolute;
        top: 20px;
        right: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(0,0,0,0.6);
        padding: 5px 12px;
        border-radius: 100px;
        color: white;
        font-weight: 700;
        font-size: 0.8rem;
    }
    .recording-dot {
        width: 10px;
        height: 10px;
        background: #ef4444;
        border-radius: 50%;
        animation: blink 1s infinite;
    }
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    .instruction-overlay {
        position: absolute;
        bottom: 20px;
        left: 20px;
        right: 20px;
        background: rgba(255,255,255,0.9);
        backdrop-filter: blur(8px);
        padding: 15px;
        border-radius: 16px;
        text-align: center;
        color: var(--dark);
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .otp-display {
        font-size: 1.5rem;
        color: var(--primary);
        font-weight: 800;
        letter-spacing: 4px;
        margin-top: 5px;
    }
</style>
@endsection

@section('content')
<div class="animate__animated animate__fadeIn">
    <div class="premium-card" style="max-width: 600px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold font-outfit mb-2">Video KYC</h3>
            <p class="text-muted small">Record a short video to confirm your identity</p>
        </div>

        <div class="video-kyc-container">
            <video id="video-kyc" autoplay muted playsinline></video>
            <div class="recording-indicator" id="recording-status" style="display:none;">
                <div class="recording-dot"></div> REC
            </div>
            <div class="instruction-overlay">
                <div>Please say these digits clearly:</div>
                <div class="otp-display">{{ $pendingVerification['id'] ?? '1234' }}</div>
            </div>
        </div>

        <div id="controls" class="text-center">
            <button type="button" class="btn-premium" id="startBtn">
                <i class="ti ti-video"></i> Start Recording
            </button>
            <button type="button" class="btn btn-danger w-100 py-3 rounded-4 fw-bold" id="stopBtn" style="display:none;">
                <i class="ti ti-player-stop"></i> Stop & Upload
            </button>
        </div>

        <form id="videoForm" action="{{ route('ekyc.form.submit-step', ['step' => $step]) }}" method="POST">
            @csrf
            <input type="hidden" name="video_data" id="video_data">
        </form>

        <div class="mt-4 p-3 bg-primary-light rounded-4">
            <div class="d-flex gap-3 align-items-center">
                <i class="ti ti-bulb text-primary fs-3"></i>
                <div class="small fw-medium text-dark-soft">
                    Tip: Hold your PAN card near your face for faster verification.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('additional_js')
<script>
    $(document).ready(function() {
        const video = document.getElementById('video-kyc');
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');
        let mediaRecorder;
        let recordedChunks = [];

        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true, audio: true }).then(function(stream) {
                video.srcObject = stream;
                
                startBtn.addEventListener('click', () => {
                    recordedChunks = [];
                    mediaRecorder = new MediaRecorder(stream, { mimeType: 'video/webm' });
                    
                    mediaRecorder.ondataavailable = (event) => {
                        if (event.data.size > 0) recordedChunks.push(event.data);
                    };

                    mediaRecorder.onstop = () => {
                        const blob = new Blob(recordedChunks, { type: 'video/webm' });
                        const reader = new FileReader();
                        reader.readAsDataURL(blob);
                        reader.onloadend = () => {
                            $('#video_data').val(reader.result);
                            $('#videoForm').submit();
                        };
                    };

                    mediaRecorder.start();
                    $(startBtn).hide();
                    $(stopBtn).fadeIn();
                    $('#recording-status').fadeIn();
                });

                stopBtn.addEventListener('click', () => {
                    mediaRecorder.stop();
                    $(stopBtn).prop('disabled', true).html('<i class="ti ti-loader-2 animate-spin me-2"></i> Uploading...');
                });
            }).catch(function(err) {
                showStatus('Permission Denied', 'Please grant camera and microphone access to complete Video KYC.', true);
            });
        }

        $('#videoForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $(stopBtn);
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        showStatus('Error', response.message || 'Video upload failed.', true);
                        btn.prop('disabled', false).html('<i class="ti ti-player-stop"></i> Stop & Upload');
                    }
                },
                error: function() {
                    showStatus('Error', 'Failed to save video. Please try again.', true);
                    btn.prop('disabled', false).html('<i class="ti ti-player-stop"></i> Stop & Upload');
                }
            });
        });
    });
</script>
@endsection
