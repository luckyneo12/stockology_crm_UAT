@extends('ekyc::layouts.ekyc')

@section('title', 'PAN Verification - Stockology E-KYC')

@section('additional_css')
<style>
    .pan-card-preview {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    .pan-card-preview::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
        pointer-events: none;
    }
    .pan-chip {
        width: 45px;
        height: 35px;
        background: linear-gradient(135deg, #fbbf24 0%, #b45309 100%);
        border-radius: 6px;
        margin-bottom: 1.5rem;
    }
    .pan-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.6;
        margin-bottom: 4px;
    }
    .pan-value {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 1.1rem;
        letter-spacing: 1px;
        margin-bottom: 1.25rem;
    }
</style>
@endsection

@section('content')
<div class="animate__animated animate__fadeIn">
    <div class="premium-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold font-outfit mb-2">PAN Verification</h3>
            <p class="text-muted small">Enter your PAN details as per official records</p>
        </div>

        <div class="pan-card-preview">
            <div class="d-flex justify-content-between align-items-start">
                <div class="pan-chip"></div>
                <div class="small fw-bold opacity-50">INCOME TAX DEPARTMENT</div>
            </div>
            
            <div class="row">
                <div class="col-8">
                    <div class="pan-label">Account Number</div>
                    <div class="pan-value" id="preview-pan">XXXXX 0000 X</div>
                    
                    <div class="pan-label">Holder Name</div>
                    <div class="pan-value text-uppercase" id="preview-name">Your Full Name</div>
                </div>
                <div class="col-4 text-end">
                    <div class="pan-label">Date of Birth</div>
                    <div class="pan-value" id="preview-dob">DD/MM/YYYY</div>
                </div>
            </div>
        </div>

        <form id="panForm" action="{{ route('ekyc.form.submit-step', ['step' => $step]) }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="pan_number" class="form-label-premium">PAN Card Number</label>
                <input type="text" 
                       name="pan_number" 
                       id="pan_number" 
                       class="form-control-premium w-100 text-uppercase" 
                       placeholder="ABCDE1234F" 
                       maxlength="10"
                       required
                       value="{{ $submission->pan_number }}">
            </div>

            <div class="mb-3">
                <label for="pan_name" class="form-label-premium">Full Name (as on PAN)</label>
                <input type="text" 
                       name="pan_name" 
                       id="pan_name" 
                       class="form-control-premium w-100 text-uppercase" 
                       placeholder="JOHN DOE" 
                       required
                       value="{{ $submission->pan_name }}">
            </div>

            <div class="mb-4">
                <label for="pan_dob" class="form-label-premium">Date of Birth</label>
                <input type="date" 
                       name="pan_dob" 
                       id="pan_dob" 
                       class="form-control-premium w-100" 
                       required
                       value="{{ $submission->pan_dob }}">
            </div>

            <button type="submit" class="btn-premium" id="submitBtn">
                Verify PAN
                <i class="ti ti-arrow-right"></i>
            </button>
        </form>
    </div>
</div>
@endsection

@section('additional_js')
<script>
    $(document).ready(function() {
        // Real-time preview updates
        $('#pan_number').on('input', function() {
            let val = this.value.toUpperCase();
            this.value = val;
            $('#preview-pan').text(val || 'XXXXX 0000 X');
        });
        
        $('#pan_name').on('input', function() {
            $('#preview-name').text(this.value.toUpperCase() || 'Your Full Name');
        });
        
        $('#pan_dob').on('change', function() {
            let date = new Date(this.value);
            if (!isNaN(date)) {
                let formatted = date.toLocaleDateString('en-GB');
                $('#preview-dob').text(formatted);
            }
        });

        $('#panForm').on('submit', function(e) {
            e.preventDefault();
            
            const btn = $('#submitBtn');
            const originalHtml = btn.html();
            btn.prop('disabled', true).html('<i class="ti ti-loader-2 animate-spin me-2"></i> Verifying...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        showStatus('Oops!', response.message || 'Verification failed.', true);
                        btn.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    let msg = 'Validation error. Please check your inputs.';
                    if(response && response.message) msg = response.message;
                    showStatus('Error', msg, true);
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    });
</script>
@endsection
