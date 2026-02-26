@extends('ekyc::layouts.ekyc')

@section('title', 'Aadhaar Verification - Stockology E-KYC')

@section('additional_css')
<style>
    .aadhaar-status-card {
        background: var(--white);
        border: 2px dashed var(--gray-200);
        border-radius: 24px;
        padding: 2.5rem;
        text-align: center;
        margin-bottom: 2rem;
        transition: var(--transition);
    }
    .aadhaar-status-card.active {
        border-color: var(--primary);
        background: var(--primary-light);
    }
    .status-icon-wrapper {
        width: 80px;
        height: 80px;
        background: var(--gray-100);
        color: var(--gray-600);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin: 0 auto 1.5rem;
        transition: var(--transition);
    }
    .aadhaar-status-card.active .status-icon-wrapper {
        background: var(--primary);
        color: var(--white);
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
    }
</style>
@endsection

@section('content')
<div class="animate__animated animate__fadeIn">
    <div class="premium-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold font-outfit mb-2">Aadhaar Verification</h3>
            <p class="text-muted small">Verify your identity securely via UIDAI</p>
        </div>

        <div class="aadhaar-status-card active" id="status-card">
            <div class="status-icon-wrapper">
                <i class="ti ti-loader-2 animate-spin"></i>
            </div>
            <h4 class="font-outfit fw-bold mb-2" id="aadhaar-title">Initializing Verification</h4>
            <p class="text-muted small mb-0" id="aadhaar-subtitle">Please wait while we connect to secure Aadhaar services...</p>
        </div>

        <form id="aadhaarForm" action="{{ route('ekyc.form.submit-step', ['step' => $step]) }}" method="POST">
            @csrf
            
            <div id="fallback-ui" style="display: none;">
                <div class="alert bg-soft-primary border-0 rounded-4 p-4 mb-4 text-center">
                    <div class="mb-3">
                        <i class="ti ti-shield-check text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-2 font-outfit">Continue Aadhaar Verification</h5>
                    <p class="text-muted small mb-0 px-3">
                        If the verification window didn't open automatically, click the button below.
                    </p>
                </div>

                <button type="submit" class="btn-premium w-100" id="submitBtn">
                    Verify with Aadhaar OTP
                    <i class="ti ti-arrow-right ms-2"></i>
                </button>
            </div>
        </form>

        <div class="text-center mt-4">
            <p class="x-small text-muted">
                Step 4 of 5: Secure Digital Identity Verification
            </p>
        </div>
    </div>
</div>
@endsection

@section('additional_js')
<script src="https://app.digio.in/sdk/v11/digio.js"></script>
<script>
    $(document).ready(function() {
        // Auto-trigger the verification
        let autoTriggered = false;
        
        function initializeVerification() {
            if (autoTriggered) return;
            autoTriggered = true;

            const btn = $('#submitBtn');
            const originalHtml = btn.html();
            
            $.ajax({
                url: $('#aadhaarForm').attr('action'),
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.is_digio) {
                        console.log("Auto-Initializing Digio SDK...");
                        
                        const options = {
                            environment: "{{ $digio_environment ?? 'sandbox' }}",
                            is_iframe: true,
                            service_mode: 'AADHAAR', // Specific mode for Aadhaar flow skip
                            tokenId: response.digio_access_token,
                            token_id: response.digio_access_token,
                            callback: function(digioResponse) {
                                console.log("Digio Callback Response:", digioResponse);
                                if (digioResponse.hasOwnProperty('error_code')) {
                                    showStatus('Error', 'Verification failed: ' + digioResponse.message, true);
                                    $('#fallback-ui').fadeIn();
                                    $('#aadhaar-title').text('Verification Paused');
                                    $('#aadhaar-subtitle').text('Please click the button below to retry.');
                                    $('.ti-loader-2').removeClass('ti-loader-2 animate-spin').addClass('ti-fingerprint');
                                } else {
                                    // Extract request ID - it could be in different keys
                                    let requestId = digioResponse.request_id || digioResponse.id || response.digio_request_id;
                                    
                                    console.log("Using Request ID for confirmation:", requestId);

                                    if (!requestId) {
                                        showStatus('Error', 'Missing Request ID. Please refresh and try again.', true);
                                        $('#fallback-ui').fadeIn();
                                        return;
                                    }

                                    $('#aadhaar-title').text('Confirming Details...');
                                    $.ajax({
                                        url: "{{ route('ekyc.form.confirm-aadhaar') }}",
                                        type: 'POST',
                                        dataType: 'json',
                                        data: {
                                            _token: $('meta[name="csrf-token"]').attr('content'),
                                            digio_request_id: requestId
                                        },
                                        success: function(confirmResponse) {
                                            if (confirmResponse.success) {
                                                window.location.href = confirmResponse.redirect;
                                            } else if (confirmResponse.is_mismatch) {
                                                showMismatchModal(confirmResponse.pan_name, confirmResponse.aadhaar_name);
                                                $('#fallback-ui').fadeIn();
                                            } else {
                                                showStatus('Error', confirmResponse.message, true);
                                                $('#fallback-ui').fadeIn();
                                            }
                                        },
                                        error: function(xhr) {
                                            console.error("Confirmation AJAX Error:", xhr);
                                            let errorMsg = 'Failed to confirm details with server.';
                                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                                errorMsg += ' Details: ' + xhr.responseJSON.message;
                                            }
                                            showStatus('Error', errorMsg, true);
                                            $('#fallback-ui').fadeIn();
                                        }
                                    });
                                }
                            },
                            logo: "{{ asset('storage/logo/logo.png') }}",
                            theme: {
                                primaryColor: "#10b981"
                            }
                        };

                        try {
                            const digio = new Digio(options);
                            digio.init();
                            digio.submit(response.digio_request_id, response.digio_identifier, response.digio_access_token);
                        } catch (err) {
                            console.error("Digio SDK Error:", err);
                            $('#fallback-ui').fadeIn();
                        }
                    } else if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        showStatus('Oops!', response.message || 'Verification failed.', true);
                        $('#fallback-ui').fadeIn();
                    }
                },
                error: function(xhr) {
                    $('#fallback-ui').fadeIn();
                }
            });
        }

        // Delay slightly for smooth transition
        setTimeout(initializeVerification, 1500);

        $('#aadhaarForm').on('submit', function(e) {
            e.preventDefault();
            autoTriggered = false; // Allow re-trigger via manual click
            initializeVerification();
        });

        function showMismatchModal(panName, aadhaarName) {
            // Check if modal exists, if not create it
            if ($('#mismatchModal').length === 0) {
                $('body').append(`
                    <div class="modal fade" id="mismatchModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4 shadow-lg">
                                <div class="modal-body p-4 text-center">
                                    <div class="mb-4">
                                        <i class="ti ti-alert-triangle text-warning" style="font-size: 4rem;"></i>
                                    </div>
                                    <h4 class="fw-bold font-outfit mb-3">Name Mismatch Detected</h4>
                                    <p class="text-muted mb-4">The name on your Aadhaar card does not match the name provided for PAN.</p>
                                    
                                    <div class="bg-light rounded-4 p-3 mb-4 text-start">
                                        <div class="mb-2">
                                            <span class="small text-muted d-block">Name as per PAN:</span>
                                            <strong class="text-dark" id="modal-pan-name"></strong>
                                        </div>
                                        <div>
                                            <span class="small text-muted d-block">Name as per Aadhaar:</span>
                                            <strong class="text-primary" id="modal-aadhaar-name"></strong>
                                        </div>
                                    </div>
                                    
                                    <p class="small text-danger mb-4">Please ensure both names match exactly to proceed with KYC.</p>
                                    
                                    <div class="d-grid">
                                        <button type="button" class="btn btn-dark rounded-pill py-2" data-bs-dismiss="modal">I will check again</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            }
            
            $('#modal-pan-name').text(panName);
            $('#modal-aadhaar-name').text(aadhaarName);
            const myModal = new bootstrap.Modal(document.getElementById('mismatchModal'));
            myModal.show();
        }
    });
</script>
@endsection
