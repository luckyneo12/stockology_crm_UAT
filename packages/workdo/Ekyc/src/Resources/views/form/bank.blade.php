@extends('ekyc::layouts.ekyc')

@section('title', 'Bank Verification - Stockology E-KYC')

@section('additional_css')
<style>
    .bank-card-container {
        perspective: 1000px;
        margin-bottom: 2.5rem;
    }
    .bank-card-visual {
        background: linear-gradient(135deg, #10b981 0%, #064e3b 100%);
        height: 200px;
        border-radius: 24px;
        padding: 2rem;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(16, 185, 129, 0.2);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .bank-card-visual::before {
        content: '';
        position: absolute;
        top: -10%;
        right: -10%;
        width: 150px;
        height: 150px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    .bank-name-display {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 1.25rem;
        letter-spacing: -0.5px;
    }
    .bank-acc-display {
        font-family: 'Outfit', sans-serif;
        font-size: 1.5rem;
        letter-spacing: 2px;
        font-weight: 600;
    }
    .bank-meta {
        font-size: 0.75rem;
        opacity: 0.8;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
</style>
@endsection

@section('content')
<div class="animate__animated animate__fadeIn">
    <div class="premium-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold font-outfit mb-2">Bank Verification</h3>
            <p class="text-muted small">Link your primary bank account for transactions</p>
        </div>

        <div class="bank-card-container animate__animated animate__flipInX">
            <div class="bank-card-visual">
                <div class="d-flex justify-content-between">
                    <div class="bank-name-display" id="display-bank">LINKED BANK</div>
                    <i class="ti ti-building-bank fs-2"></i>
                </div>
                
                <div class="bank-acc-display" id="display-acc">XXXX XXXX 0000</div>
                
                <div class="d-flex justify-content-between align-items-end">
                    <div>
                        <div class="bank-meta">Holder Name</div>
                        <div class="fw-bold text-uppercase" id="display-name">John Doe</div>
                    </div>
                    <div>
                        <div class="bank-meta">IFSC Code</div>
                        <div class="fw-bold" id="display-ifsc">XXXX0000000</div>
                    </div>
                </div>
            </div>
        </div>

        <form id="bankForm" action="{{ route('ekyc.form.submit-step', ['step' => $step]) }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="bank_account_number" class="form-label-premium">Bank Account Number</label>
                <input type="password" 
                       name="bank_account_number" 
                       id="bank_account_number" 
                       class="form-control-premium w-100" 
                       placeholder="Enter Account Number" 
                       required
                       value="{{ $submission->bank_account_number }}">
            </div>

            <div class="mb-3">
                <label for="bank_account_number_confirmation" class="form-label-premium">Confirm Account Number</label>
                <input type="text" 
                       name="bank_account_number_confirmation" 
                       id="bank_account_number_confirmation" 
                       class="form-control-premium w-100" 
                       placeholder="Re-enter Account Number" 
                       required>
            </div>

            <div class="mb-3">
                <label for="bank_ifsc" class="form-label-premium">IFSC Code</label>
                <input type="text" 
                       name="bank_ifsc" 
                       id="bank_ifsc" 
                       class="form-control-premium w-100 text-uppercase" 
                       placeholder="SBIN0001234" 
                       maxlength="11"
                       required
                       value="{{ $submission->bank_ifsc }}">
            </div>

            <div class="mb-4">
                <label for="bank_account_holder_name" class="form-label-premium">Account Holder Name</label>
                <input type="text" 
                       name="bank_account_holder_name" 
                       id="bank_account_holder_name" 
                       class="form-control-premium w-100 text-uppercase" 
                       placeholder="AS PER BANK RECORDS" 
                       required
                       value="{{ $submission->bank_account_holder_name }}">
            </div>

            <button type="submit" class="btn-premium" id="submitBtn">
                Verify Account
                <i class="ti ti-arrow-right"></i>
            </button>
        </form>
    </div>
</div>
@endsection

@section('additional_js')
<script>
    $(document).ready(function() {
        // Visual updates
        $('#bank_account_number_confirmation').on('input', function() {
            let val = this.value;
            if(val.length > 4) {
                $('#display-acc').text('XXXX XXXX ' + val.slice(-4));
            } else {
                $('#display-acc').text(val || 'XXXX XXXX 0000');
            }
        });

        $('#bank_ifsc').on('input', function() {
            let val = this.value.toUpperCase();
            this.value = val;
            $('#display-ifsc').text(val || 'XXXX0000000');
        });

        $('#bank_account_holder_name').on('input', function() {
            $('#display-name').text(this.value.toUpperCase() || 'Account Holder');
        });

        $('#bankForm').on('submit', function(e) {
            e.preventDefault();
            
            const acc = $('#bank_account_number').val();
            const accConf = $('#bank_account_number_confirmation').val();

            if(acc !== accConf) {
                showStatus('Mismatch', 'Account numbers do not match.', true);
                return;
            }

            const btn = $('#submitBtn');
            const originalHtml = btn.html();
            btn.prop('disabled', true).html('<i class="ti ti-loader-2 animate-spin me-2"></i> Penny Drop In Progress...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        showStatus('Oops!', response.message || 'Bank verification failed.', true);
                        btn.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showStatus('Error', (response && response.message) ? response.message : 'Validation error, please check bank details.', true);
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    });
</script>
@endsection
