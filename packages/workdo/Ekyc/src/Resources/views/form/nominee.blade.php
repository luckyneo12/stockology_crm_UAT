@extends('ekyc::layouts.ekyc')

@section('title', 'Nominee Details - Stockology eKYC')

@section('additional_css')
<style>
    .nominee-card {
        background: #ffffff;
        border-radius: 28px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.06);
        padding: 40px;
        border: 1px solid #f1f5f9;
    }
    .nominee-block {
        background: #f8fafc;
        border-radius: 24px;
        padding: 25px;
        margin-bottom: 25px;
        border: 1px solid #e2e8f0;
        position: relative;
    }
    .opt-toggle { position: relative; cursor: pointer; display: inline-block; }
    .opt-toggle input { position: absolute; opacity: 0; }
    .opt-label {
        padding: 10px 30px; border: 2px solid #E2E8F0; border-radius: 30px;
        font-weight: 700; font-size: 0.9rem; color: #64748b; transition: all 0.3s;
        display: flex; align-items: center; gap: 8px;
    }
    .opt-toggle input:checked + .opt-label {
        background: #10b981; border-color: #10b981; color: white;
    }
    .remove-nominee {
        position: absolute; top: -10px; right: -10px; width: 32px; height: 32px;
        background: #ef4444; color: white; border-radius: 50%; display: flex;
        align-items: center; justify-content: center; cursor: pointer; border: none;
    }
</style>
@endsection

@section('content')
<div class="nominee-card animate__animated animate__fadeIn">
    <div class="text-center mb-5">
        <h2 class="fw-bold mb-2">Nominee Assignment</h2>
        <p class="text-muted">You can add up to 3 nominees for your account.</p>
    </div>

    <form id="nomineeForm">
        @csrf
        <div class="mb-5 text-center">
            <label class="form-label d-block mb-3 fw-bold">Do you want to add a nominee? *</label>
            <div class="d-flex justify-content-center gap-3">
                <label class="opt-toggle">
                    <input type="radio" name="has_nominee" value="1" id="has_nominee_yes" {{ !empty($submission->nominee_data) ? 'checked' : '' }}>
                    <span class="opt-label"><i class="ti ti-user-plus"></i> Yes, I Want</span>
                </label>
                <label class="opt-toggle">
                    <input type="radio" name="has_nominee" value="0" id="has_nominee_no" {{ empty($submission->nominee_data) ? 'checked' : '' }}>
                    <span class="opt-label"><i class="ti ti-user-x"></i> No, Skip Now</span>
                </label>
            </div>
        </div>

        <div id="nominee-container" style="{{ empty($submission->nominee_data) ? 'display: none;' : '' }}">
            @php 
                $nominees = $submission->nominee_data ?? [['name' => '', 'relation' => '', 'dob' => '', 'share' => 100, 'id_number' => '']];
            @endphp
            
            <div id="nominee-list">
                @foreach($nominees as $index => $nominee)
                    <div class="nominee-block" data-index="{{ $index }}">
                        @if($index > 0)
                            <button type="button" class="remove-nominee"><i class="ti ti-x"></i></button>
                        @endif
                        <h5 class="fw-bold mb-4 text-primary">Nominee #{{ $index + 1 }}</h5>
                        <div class="row g-4">
                            <div class="col-md-6 text-start">
                                <label class="form-label small fw-bold">Full Name as per ID *</label>
                                <input type="text" name="nominees[{{ $index }}][name]" class="form-control rounded-3" value="{{ $nominee['name'] ?? '' }}" required>
                            </div>
                            <div class="col-md-6 text-start">
                                <label class="form-label small fw-bold">Relationship *</label>
                                <select name="nominees[{{ $index }}][relation]" class="form-select rounded-3" required>
                                    <option value="">Select Relation</option>
                                    @foreach(['SPOUSE', 'FATHER', 'MOTHER', 'SON', 'DAUGHTER', 'BROTHER', 'SISTER'] as $rel)
                                        <option value="{{ $rel }}" {{ ($nominee['relation'] ?? '') == $rel ? 'selected' : '' }}>{{ $rel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 text-start">
                                <label class="form-label small fw-bold">Date of Birth *</label>
                                <input type="date" name="nominees[{{ $index }}][dob]" class="form-control rounded-3" value="{{ $nominee['dob'] ?? '' }}" required>
                            </div>
                            <div class="col-md-4 text-start">
                                <label class="form-label small fw-bold">Share Percentage (%) *</label>
                                <input type="number" name="nominees[{{ $index }}][share]" class="form-control rounded-3 share-input" value="{{ $nominee['share'] ?? 100 }}" min="1" max="100" required>
                            </div>
                            <div class="col-md-4 text-start">
                                <label class="form-label small fw-bold">ID Number (Optional)</label>
                                <input type="text" name="nominees[{{ $index }}][id_number]" class="form-control rounded-3" value="{{ $nominee['id_number'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" class="btn btn-outline-primary rounded-pill px-4 mb-5" id="add-nominee-btn">
                <i class="ti ti-plus me-1"></i> Add Another Nominee
            </button>
        </div>

        <button type="submit" class="btn-premium" id="submitBtn">
            Continue to Next Step
            <i class="ti ti-arrow-narrow-right"></i>
        </button>
    </form>
</div>
@endsection

@section('additional_js')
<script>
    $(document).ready(function() {
        $('input[name="has_nominee"]').on('change', function() {
            if ($(this).val() === '1') {
                $('#nominee-container').fadeIn();
                $('#nominee-list input, #nominee-list select').prop('required', true);
            } else {
                $('#nominee-container').fadeOut();
                $('#nominee-list input, #nominee-list select').prop('required', false);
            }
        });

        // Initialize state on load
        $('input[name="has_nominee"]:checked').trigger('change');

        $('#add-nominee-btn').on('click', function() {
            const count = $('.nominee-block').length;
            if (count >= 3) return showStatus('Limit Reached', 'You can add maximum 3 nominees.');

            const html = `
                <div class="nominee-block animate__animated animate__fadeInUp" data-index="${count}">
                    <button type="button" class="remove-nominee"><i class="ti ti-x"></i></button>
                    <h5 class="fw-bold mb-4 text-primary">Nominee #${count + 1}</h5>
                    <div class="row g-4">
                        <div class="col-md-6 text-start">
                            <label class="form-label small fw-bold">Full Name as per ID *</label>
                            <input type="text" name="nominees[${count}][name]" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6 text-start">
                            <label class="form-label small fw-bold">Relationship *</label>
                            <select name="nominees[${count}][relation]" class="form-select rounded-3" required>
                                <option value="">Select Relation</option>
                                <option value="SPOUSE">SPOUSE</option>
                                <option value="FATHER">FATHER</option>
                                <option value="MOTHER">MOTHER</option>
                                <option value="SON">SON</option>
                                <option value="DAUGHTER">DAUGHTER</option>
                                <option value="BROTHER">BROTHER</option>
                                <option value="SISTER">SISTER</option>
                            </select>
                        </div>
                        <div class="col-md-4 text-start">
                            <label class="form-label small fw-bold">Date of Birth *</label>
                            <input type="date" name="nominees[${count}][dob]" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-4 text-start">
                            <label class="form-label small fw-bold">Share Percentage (%) *</label>
                            <input type="number" name="nominees[${count}][share]" class="form-control rounded-3 share-input" min="1" max="100" required>
                        </div>
                        <div class="col-md-4 text-start">
                            <label class="form-label small fw-bold">ID Number (Optional)</label>
                            <input type="text" name="nominees[${count}][id_number]" class="form-control rounded-3">
                        </div>
                    </div>
                </div>
            `;
            $('#nominee-list').append(html);
        });

        $(document).on('click', '.remove-nominee', function() {
            $(this).closest('.nominee-block').remove();
            // Re-index remaining
            $('.nominee-block').each(function(idx) {
                $(this).find('h5').text(`Nominee #${idx + 1}`);
                $(this).find('input, select').each(function() {
                    const name = $(this).attr('name');
                    if (name) $(this).attr('name', name.replace(/\[\d+\]/, `[${idx}]`));
                });
            });
        });

        $('#nomineeForm').on('submit', function(e) {
            e.preventDefault();
            
            if ($('input[name="has_nominee"]:checked').val() === '1') {
                let totalShare = 0;
                $('.share-input').each(function() { totalShare += parseInt($(this).val() || 0); });
                if (totalShare !== 100) return showStatus('Validation Error', 'Total share percentage must equal 100%. Current: ' + totalShare + '%');
            }

            const btn = $('#submitBtn');
            btn.prop('disabled', true).html('<i class="ti ti-loader-2 animate-spin"></i> Saving...');

            $.ajax({
                url: '{{ route("ekyc.form.submit-step", ["step" => $step]) }}',
                type: 'POST',
                data: $(this).serialize(),
                success: (res) => window.location.href = res.redirect,
                error: (xhr) => {
                    showStatus('Error', xhr.responseJSON?.message || 'Save failed');
                    btn.prop('disabled', false).html('Continue to Next Step <i class="ti ti-arrow-narrow-right"></i>');
                }
            });
        });
    });
</script>
@endsection
