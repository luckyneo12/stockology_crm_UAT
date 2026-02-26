@extends('ekyc::layouts.ekyc')

@section('title', 'Signature & Documents - Stockology eKYC')

@section('additional_css')
    <style>
        /* Layout Fixes */
        .doc-card {
            background: white;
            border-radius: 28px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.06);
            overflow: hidden;
            border: 1px solid #f1f5f9;
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
            transition: all 0.3s;
        }

        .checklist-item.verified {
            border-color: #10b981;
            background: #d1fae5;
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
            background: #10b981;
            color: white;
        }

        /* Capture Box Styles */
        .capture-area {
            max-width: 600px;
            margin-bottom: 40px;
        }

        .capture-box {
            background: #F8FAFC;
            border: 2px dashed #CBD5E1;
            border-radius: 24px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            min-height: 250px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .capture-box:hover {
            border-color: #10b981;
            background: white;
        }

        #signature-pad-modal-canvas {
            width: 100%;
            height: 300px;
            background: white;
            border: 1px solid #E2E8F0;
            touch-action: none;
        }

        .preview-img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 16px;
            display: none;
            margin-bottom: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .verification-badge {
            display: inline-flex; align-items: center; gap: 5px; padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; margin-top: 10px;
        }

        .badge-success { background: #d1fae5; color: #065f46; }
        
        @media (max-width: 991px) {
            .capture-area { max-width: 100%; }
        }
    </style>
@endsection

@section('content')
<div class="doc-card">
    <div class="row g-0">
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="checklist-sidebar">
                <h4 class="fw-bold mb-4">Final Documentation</h4>
                
                <div class="checklist-item verified">
                    <div class="checklist-icon"><i class="ti ti-check"></i></div>
                    <span class="checklist-text">Identity Verified</span>
                </div>
                <div class="checklist-item verified">
                    <div class="checklist-icon"><i class="ti ti-check"></i></div>
                    <span class="checklist-text">Apna Liveness Capture</span>
                </div>
                <div class="checklist-item {{ !empty($submission->additional_data['signature']) ? 'verified' : '' }}" id="chk-sign">
                    <div class="checklist-icon"><i class="ti ti-signature"></i></div>
                    <span class="checklist-text">Signature Capture</span>
                </div>

                <div class="mt-5 p-3 bg-white border rounded-4 small text-muted">
                    <p class="mb-2"><i class="ti ti-info-circle text-primary me-2"></i> <strong>Pro Tip:</strong></p>
                    Provide a clear signature on a plain white paper or draw it precisely on the screen.
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="main-content">
                <h2 class="fw-bold mb-2">Signature & Documents</h2>
                <p class="text-muted mb-5">Please upload or draw your signature to complete your profile.</p>

                <form id="docsForm">
                    @csrf
                    
                    <div class="capture-area mb-5">
                        <!-- Signature Section -->
                        <div class="text-start">
                            <label class="form-label fw-bold mb-3">Your Digital Signature *</label>
                            <div class="capture-box" id="sign-box">
                                @php $signatureVal = $submission->additional_data['signature'] ?? ''; @endphp
                                
                                <img id="sign-preview" class="preview-img" src="{{ $signatureVal }}" alt="Signature Preview" style="{{ $signatureVal ? 'display:block' : '' }}">
                                
                                <div id="sign-placeholder" style="{{ $signatureVal ? 'display:none' : '' }}">
                                    <div class="d-flex flex-column align-items-center gap-3">
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-4" onclick="$('#signatureModal').modal('show')">
                                            <i class="ti ti-edit me-1"></i> Draw Signature
                                        </button>
                                        <span class="text-muted small">OR</span>
                                        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-4" onclick="$('#signature_file').click()">
                                            <i class="ti ti-upload me-1"></i> Upload Image
                                        </button>
                                    </div>
                                </div>

                                <div id="sign-verification-status">
                                    @if($signatureVal)
                                        <div class="verification-badge badge-success">Signature Captured</div>
                                    @endif
                                </div>

                                @if($signatureVal)
                                    <button type="button" class="btn btn-link text-decoration-none text-muted small mt-2" onclick="resetSignature()">
                                        <i class="ti ti-rotate"></i> Replace Signature
                                    </button>
                                @endif
                            </div>
                            <input type="hidden" name="signature_data" id="signature_input" value="{{ $signatureVal }}">
                            <input type="file" id="signature_file" accept="image/*" style="display: none">
                        </div>
                    </div>

                    <!-- Optional Documents -->
                    <div class="mb-5">
                        <label class="form-label fw-bold mb-3">Income Proof (Optional)</label>
                        <div class="input-group">
                            <input type="file" name="income_proof" class="form-control rounded-4 p-3 border-2" accept=".pdf,.jpg,.png">
                        </div>
                        <p class="small text-muted mt-2">Required only for Trading in F&O Segments. Supports PDF, JPG, PNG.</p>
                    </div>

                    <button type="submit" class="btn-premium" id="submitBtn">
                        Save and Continue
                        <i class="ti ti-player-play"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Signature Modal -->
<div class="modal fade" id="signatureModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 32px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Draw Signature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <canvas id="signature-pad-modal-canvas"></canvas>
                <div class="mt-4 d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-secondary px-4 rounded-pill" id="clear-sign">Clear</button>
                    <button type="button" class="btn btn-primary px-4 rounded-pill" id="save-drawn-sign">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('additional_js')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

    <script>
        let signaturePad;

        $(document).ready(function() {
            // Signature Logic
            const canvas = document.querySelector("#signature-pad-modal-canvas");
            signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });

            $('#signatureModal').on('shown.bs.modal', function () {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                signaturePad.clear();
            });

            $('#clear-sign').on('click', () => signaturePad.clear());
            $('#save-drawn-sign').on('click', function() {
                if (signaturePad.isEmpty()) return alert('Please provide your signature.');
                verifySignature(signaturePad.toDataURL('image/jpeg', 0.8));
            });

            $('#signature_file').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 2 * 1024 * 1024) return showStatus('Error', 'File size too large (max 2MB)', true);
                    const reader = new FileReader();
                    reader.onload = (ev) => verifySignature(ev.target.result);
                    reader.readAsDataURL(file);
                }
            });

            $('#docsForm').on('submit', function(e) {
                e.preventDefault();
                if (!$('#signature_input').val()) return showStatus('Missing Data', 'Signature is required.', true);
                
                const btn = $('#submitBtn');
                btn.prop('disabled', true).html('<i class="ti ti-loader-2 animate-spin"></i> Saving...');

                const formData = new FormData(this);
                $.ajax({
                    url: '{{ route("ekyc.form.submit-step", ["step" => $step]) }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: (res) => window.location.href = res.redirect,
                    error: (xhr) => {
                        showStatus('Error', xhr.responseJSON?.message || 'Submission failed');
                        btn.prop('disabled', false).html('Save and Continue <i class="ti ti-player-play"></i>');
                    }
                });
            });
        });

        function verifySignature(dataUrl) {
            $('#sign-preview').attr('src', dataUrl).fadeIn();
            $('#sign-placeholder').hide();
            $('#signature_input').val(dataUrl);
            $('#signatureModal').modal('hide');
            $('#chk-sign').addClass('verified');
            $('#sign-verification-status').html('<div class="verification-badge badge-success">Signature Captured</div>');
        }

        function resetSignature() {
            $('#sign-preview').hide(); 
            $('#sign-placeholder').show(); 
            $('#signature_input').val(''); 
            $('#chk-sign').removeClass('verified');
            $('#sign-verification-status').empty();
        }
    </script>
@endsection
