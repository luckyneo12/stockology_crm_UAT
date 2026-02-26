@extends('ekyc::layouts.ekyc')

@section('title', 'Verify OTP - Stockology E-KYC')

@section('additional_css')
<style>
    .otp-inputs-wrapper {
        display: flex;
        gap: 12px;
        justify-content: center;
        margin: 2rem 0;
    }
    .otp-field {
        width: 54px;
        height: 64px;
        background: var(--white);
        border: 2px solid var(--gray-200);
        border-radius: 16px;
        text-align: center;
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--dark);
        transition: var(--transition);
        font-family: 'Outfit', sans-serif;
    }
    .otp-field:focus {
        outline: none;
        border-color: var(--primary);
        background: var(--primary-light);
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.2);
    }
    .timer-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--gray-100);
        padding: 8px 16px;
        border-radius: 100px;
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--gray-600);
    }
    .resend-link {
        color: var(--primary);
        font-weight: 700;
        text-decoration: none;
        transition: var(--transition);
        background: none;
        border: none;
        padding: 0;
    }
    .resend-link:hover:not(:disabled) {
        color: var(--primary-dark);
        text-decoration: underline;
    }
    .resend-link:disabled {
        color: var(--gray-200);
        cursor: not-allowed;
    }
    
    @media (max-width: 576px) {
        .otp-field {
            width: 45px;
            height: 55px;
            font-size: 1.5rem;
            border-radius: 12px;
        }
        .otp-inputs-wrapper {
            gap: 8px;
        }
    }
</style>
@endsection

@section('content')
<div class="animate__animated animate__fadeIn">
    <div class="premium-card">
        <div class="text-center mb-4">
            <div class="logo-icon mx-auto mb-4 animate__animated animate__bounceIn">
                <i class="ti ti-shield-check"></i>
            </div>
            <h3 class="fw-bold font-outfit mb-2">Verify OTP</h3>
            <p class="text-muted small">
                We've sent a 6-digit verification code to <br>
                <span class="text-dark fw-bold">{{ $pendingVerification['identifier'] }}</span>
            </p>
        </div>

        <form id="otpForm">
            @csrf
            <div class="otp-inputs-wrapper">
                <input type="text" class="otp-field" maxlength="1" data-index="0" autofocus autocomplete="one-time-code">
                <input type="text" class="otp-field" maxlength="1" data-index="1">
                <input type="text" class="otp-field" maxlength="1" data-index="2">
                <input type="text" class="otp-field" maxlength="1" data-index="3">
                <input type="text" class="otp-field" maxlength="1" data-index="4">
                <input type="text" class="otp-field" maxlength="1" data-index="5">
            </div>

            <div class="text-center mb-4">
                <div class="timer-badge" id="timer">
                    <i class="ti ti-clock text-primary"></i>
                    <span id="time">{{ gmdate('i:s', $pendingVerification['expires_in']) }}</span>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">Didn't receive code?</span>
                    <button type="button" class="resend-link ms-1 small" id="resendBtn" disabled>Resend Code</button>
                </div>
            </div>

            <div class="mb-4">
                <label for="relation" class="form-label-premium">Relationship (Optional)</label>
                <select class="form-control-premium w-100" name="relation" id="relation">
                    <option value="Self">Self</option>
                    <option value="Spouse">Spouse</option>
                    <option value="Father">Father</option>
                    <option value="Mother">Mother</option>
                    <option value="Sibling">Sibling</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <button type="submit" class="btn-premium" id="verifyBtn">
                Verify & Continue
                <i class="ti ti-arrow-right"></i>
            </button>
        </form>
        
        <div class="mt-4 text-center">
            <a href="{{ route('ekyc.form.start') }}" class="text-muted small text-decoration-none">
                <i class="ti ti-arrow-left me-1"></i> Use a different number
            </a>
        </div>
    </div>
</div>
@endsection

@section('additional_js')
<script>
    $(document).ready(function() {
        let timeLeft = {{ $pendingVerification['expires_in'] }};
        let timerInterval;
        const otpInputs = $('.otp-field');

        // OTP Input Flow
        otpInputs.each(function(index) {
            $(this).on('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs.eq(index + 1).focus();
                }
            });

            $(this).on('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpInputs.eq(index - 1).focus();
                }
            });

            $(this).on('paste', function(e) {
                e.preventDefault();
                const pastedData = e.originalEvent.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                pastedData.split('').forEach((char, i) => {
                    if (otpInputs.eq(i).length) {
                        otpInputs.eq(i).val(char);
                    }
                });
                if (pastedData.length > 0) {
                    otpInputs.eq(Math.min(pastedData.length, 5)).focus();
                }
            });
        });

        // Timer Logic
        function startTimer() {
            timerInterval = setInterval(() => {
                timeLeft--;
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                $('#time').text(`${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`);
                
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    $('#timer').addClass('bg-danger-subtle text-danger');
                    $('#resendBtn').prop('disabled', false);
                }
            }, 1000);
        }
        startTimer();

        // Form Submit
        $('#otpForm').on('submit', function(e) {
            e.preventDefault();
            
            let otp = '';
            otpInputs.each(function() { otp += this.value; });

            if (otp.length !== 6) {
                showStatus('Incomplete Code', 'Please enter the full 6-digit verification code.', true);
                return;
            }

            const btn = $('#verifyBtn');
            const originalHtml = btn.html();
            btn.prop('disabled', true).html('<i class="ti ti-loader-2 animate-spin me-2"></i> Verifying...');

            $.ajax({
                url: '{{ route("ekyc.otp.verify") }}',
                type: 'POST',
                data: {
                    otp: otp,
                    relation: $('#relation').val()
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        showStatus('Verification Failed', response.message || 'Invalid code.', true);
                        btn.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showStatus('Error', (response && response.message) ? response.message : 'Invalid code, please try again.', true);
                    btn.prop('disabled', false).html(originalHtml);
                    otpInputs.val('').eq(0).focus();
                }
            });
        });

        // Resend OTP
        $('#resendBtn').on('click', function() {
            const btn = $(this);
            btn.prop('disabled', true);
            
            $.ajax({
                url: '{{ route("ekyc.otp.resend") }}',
                type: 'POST',
                success: function(response) {
                    if (response.success) {
                        showStatus('Resent!', response.message, false);
                        timeLeft = response.expires_in;
                        $('#timer').removeClass('bg-danger-subtle text-danger');
                        clearInterval(timerInterval);
                        startTimer();
                    } else {
                        showStatus('Failed', response.message || 'Could not resend code.', true);
                        btn.prop('disabled', false);
                    }
                },
                error: function() {
                    showStatus('Error', 'Failed to resend code. Please try again.', true);
                    btn.prop('disabled', false);
                }
            });
        });
    });
</script>
@endsection
