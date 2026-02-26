@extends('ekyc::layouts.ekyc')

@section('title', 'Selfie Verification - Stockology E-KYC')

@section('additional_css')
<style>
    .camera-container {
        position: relative;
        width: 100%;
        max-width: 320px;
        height: 320px;
        margin: 0 auto 2rem;
        border-radius: 50%;
        border: 4px solid var(--primary);
        overflow: hidden;
        box-shadow: 0 0 0 8px var(--primary-light), 0 20px 40px rgba(0,0,0,0.1);
        background: #000;
    }
    #video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scaleX(-1);
    }
    .camera-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border: 40px solid rgba(0,0,0,0.3);
        border-radius: 50%;
        pointer-events: none;
    }
    .capture-ring {
        width: 70px;
        height: 70px;
        border: 4px solid var(--white);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition);
        background: rgba(255, 255, 255, 0.2);
        margin: 0 auto;
    }
    .capture-ring:hover {
        transform: scale(1.1);
        background: var(--white);
        color: var(--primary);
    }
    .capture-btn-inner {
        width: 50px;
        height: 50px;
        background: var(--white);
        border-radius: 50%;
    }
</style>
@endsection

@section('content')
<div class="animate__animated animate__fadeIn">
    <div class="premium-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold font-outfit mb-2">Selfie Verification</h3>
            <p class="text-muted small">Place your face inside the circle and look at the camera</p>
        </div>

        <div class="camera-container">
            <video id="video" autoplay playsinline></video>
            <div class="camera-overlay"></div>
            <canvas id="canvas" style="display:none;"></canvas>
        </div>

        <div id="preview-container" style="display:none;" class="text-center mb-4">
             <img id="selfie-preview" src="" class="rounded-pill border border-4 border-primary shadow" style="width: 200px; height: 200px; object-fit: cover;">
             <div class="mt-3">
                 <button type="button" class="btn btn-link text-primary fw-bold" id="retakeBtn">Retake Photo</button>
             </div>
        </div>

        <div id="capture-controls" class="text-center">
            <button type="button" class="capture-ring border-0" id="captureBtn">
                <div class="capture-btn-inner"></div>
            </button>
            <p class="text-muted small mt-3 fw-bold">Tap to capture</p>
        </div>

        <form id="selfieForm" action="{{ route('ekyc.form.submit-step', ['step' => $step]) }}" method="POST">
            @csrf
            <input type="hidden" name="selfie_data" id="selfie_data">
            
            <button type="submit" class="btn-premium mt-4" id="submitBtn" style="display:none;">
                Confirm & Continue
                <i class="ti ti-arrow-right"></i>
            </button>
        </form>

        <div class="alert bg-gray-100 border-0 rounded-4 small p-3 mt-4">
            <ul class="mb-0 ps-3">
                <li>Make sure your face is clearly visible</li>
                <li>Avoid wearing hats or sunglasses</li>
                <li>Ensure good lighting conditions</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@section('additional_js')
<script>
    $(document).ready(function() {
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const captureBtn = document.getElementById('captureBtn');
        const submitBtn = document.getElementById('submitBtn');
        const retakeBtn = document.getElementById('retakeBtn');
        const previewImg = document.getElementById('selfie-preview');
        const context = canvas.getContext('2d');

        // Access Camera
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } }).then(function(stream) {
                video.srcObject = stream;
                video.play();
            }).catch(function(err) {
                showStatus('Camera Error', 'Please enable camera permissions to continue.', true);
            });
        }

        // Capture Logic
        captureBtn.addEventListener('click', function() {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.translate(canvas.width, 0); // Horizontal flip
            context.scale(-1, 1);
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            const dataUrl = canvas.toDataURL('image/jpeg');
            $('#selfie_data').val(dataUrl);
            previewImg.src = dataUrl;
            
            $('.camera-container, #capture-controls').hide();
            $('#preview-container, #submitBtn').fadeIn();
        });

        retakeBtn.addEventListener('click', function() {
            $('#preview-container, #submitBtn').hide();
            $('.camera-container, #capture-controls').fadeIn();
            $('#selfie_data').val('');
        });

        $('#selfieForm').on('submit', function(e) {
            e.preventDefault();
            
            const btn = $('#submitBtn');
            const originalHtml = btn.html();
            btn.prop('disabled', true).html('<i class="ti ti-loader-2 animate-spin me-2"></i> Uploading...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        showStatus('Error', response.message || 'Upload failed.', true);
                        btn.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function() {
                    showStatus('Error', 'Failed to save selfie. Please try again.', true);
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    });
</script>
@endsection
