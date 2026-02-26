@extends('ekyc::layouts.ekyc')

@section('title', 'Get Started - Stockology E-KYC')

@section('additional_css')
<style>
    .hero-content {
        padding-right: 2rem;
    }
    .hero-title-main {
        font-size: 3.5rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 1.5rem;
        background: linear-gradient(135deg, var(--dark) 0%, var(--gray-600) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .hero-subtitle-main {
        font-size: 1.1rem;
        color: var(--gray-600);
        margin-bottom: 2.5rem;
        max-width: 500px;
    }
    .feature-item {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 1rem;
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--dark-soft);
    }
    .feature-icon {
        width: 24px;
        height: 24px;
        background: var(--primary-light);
        color: var(--primary);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }
    
    @media (max-width: 991px) {
        .hero-content {
            text-align: center;
            padding-right: 0;
            margin-bottom: 3rem;
        }
        .hero-title-main { font-size: 2.5rem; }
        .hero-subtitle-main { margin: 0 auto 2.5rem; }
        .feature-list {
            display: inline-block;
            text-align: left;
        }
    }
</style>
@endsection

@section('content')
<div class="row align-items-center">
    <!-- Left Side: Hero Info -->
    <div class="col-lg-7 animate__animated animate__fadeInLeft">
        <div class="hero-content">
            <div class="badge bg-primary-light text-primary mb-3 px-3 py-2 border-0 rounded-pill fw-bold small animate__animated animate__fadeInDown animate__delay-1s">
                <i class="ti ti-sparkles me-1"></i> FASTER & SECURE ONBOARDING
            </div>
            <h1 class="hero-title-main font-outfit">
                Open Your Demat <br>
                <span class="text-primary">Account in Minutes</span>
            </h1>
            <p class="hero-subtitle-main">
                Experience the next generation of investing. Simple, digital, and completely paperless E-KYC journey powered by Stockology.
            </p>
            
            <div class="feature-list mb-5">
                <div class="feature-item animate__animated animate__fadeInUp animate__delay-1s">
                    <div class="feature-icon"><i class="ti ti-check"></i></div>
                    100% Digital & Paperless
                </div>
                <div class="feature-item animate__animated animate__fadeInUp animate__delay-1s">
                    <div class="feature-icon"><i class="ti ti-check"></i></div>
                    Secure Bank & Aadhaar Link
                </div>
                <div class="feature-item animate__animated animate__fadeInUp animate__delay-1s">
                    <div class="feature-icon"><i class="ti ti-check"></i></div>
                    Zero Account Opening Charges
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Form Card -->
    <div class="col-lg-5 animate__animated animate__zoomIn">
        <div class="premium-card">
            <div class="text-center mb-4">
                <h3 class="fw-bold font-outfit mb-2">Get Started</h3>
                <p class="text-muted small">Enter your mobile number linked with Aadhaar</p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger mb-4 py-2 border-0 small rounded-3 animate__animated animate__shakeX">
                    <i class="ti ti-alert-circle me-1"></i> {{ session('error') }}
                </div>
            @endif

            <form id="startForm" action="{{ route('ekyc.form.verify-contact') }}" method="POST">
                @csrf
                <input type="hidden" name="verification_type" value="mobile">
                
                <div class="mb-4">
                    <label for="mobile" class="form-label-premium">Mobile Number</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-4 ps-3 pe-0 text-muted">+91</span>
                        <input type="tel" 
                               name="identifier" 
                               id="mobile" 
                               class="form-control-premium border-start-0 rounded-start-0" 
                               placeholder="98765 43210" 
                               maxlength="10"
                               required
                               autofocus>
                    </div>
                    @error('identifier')
                        <div class="text-danger small mt-2 fw-medium">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-4 small">
                    <input class="form-check-input" type="checkbox" id="terms" required checked>
                    <label class="form-check-label text-muted" for="terms">
                        I agree to the <a href="#" class="text-primary text-decoration-none fw-bold">Terms & Conditions</a> and authorize Stockology to contact me.
                    </label>
                </div>

                <button type="submit" class="btn-premium" id="submitBtn">
                    Verify Mobile
                    <i class="ti ti-arrow-right"></i>
                </button>
            </form>
            
            <div class="mt-4 text-center">
                <p class="text-muted x-small mb-0">
                    <i class="ti ti-shield-lock text-success me-1"></i>
                    Your data is safe with us.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('additional_js')
<script>
    $(document).ready(function() {
        // Mobile number input validation (numbers only)
        $('#mobile').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // AJAX Form Submission
        $('#startForm').on('submit', function(e) {
            e.preventDefault();
            
            const mobile = $('#mobile').val();
            if(mobile.length !== 10) {
                showStatus('Invalid Number', 'Please enter a valid 10-digit mobile number.', true);
                return false;
            }

            const form = $(this);
            const btn = $('#submitBtn');
            const originalBtnHtml = btn.html();

            // Disable button and show loading
            btn.prop('disabled', true);
            btn.html('<i class="ti ti-loader-2 animate-spin me-2"></i> Processing...');

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if(response.success && response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        showStatus('Oops!', response.message || 'Something went wrong', true);
                        btn.prop('disabled', false).html(originalBtnHtml);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    let message = 'Something went wrong. Please try again.';
                    
                    if(response && response.message) {
                        message = response.message;
                    } else if(response && response.errors) {
                        message = Object.values(response.errors)[0][0];
                    }
                    
                    showStatus('Error', message, true);
                    btn.prop('disabled', false).html(originalBtnHtml);
                }
            });
        });
    });
</script>
@endsection
