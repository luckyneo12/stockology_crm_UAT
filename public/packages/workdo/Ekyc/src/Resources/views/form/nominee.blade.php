<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nominee Details - Stockology eKYC</title>
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
            --card-bg: #ffffff;
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
            max-width: 800px;
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

        /* Nominee Card */
        .nominee-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.05);
            border: 1px solid #f3f4f6;
            padding: 45px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .choice-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 40px;
        }

        .choice-item {
            position: relative;
            cursor: pointer;
        }

        .choice-item input {
            position: absolute;
            opacity: 0;
        }

        .choice-box {
            background: #F9FAFB;
            border: 2px solid #E5E7EB;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .choice-item input:checked + .choice-box {
            background: white;
            border-color: var(--primary-color);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.15);
        }

        .choice-icon {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #9ca3af;
            transition: var(--transition);
        }

        .choice-item input:checked + .choice-box .choice-icon {
            background: var(--primary-light);
            color: var(--primary-color);
        }

        .choice-title {
            font-weight: 800;
            font-size: 1.15rem;
            color: var(--dark-bg);
        }

        /* Nominee Form */
        #nomineeFormContent {
            display: none;
            background: #F8FAFC;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid #E2E8F0;
        }

        .form-label {
            font-weight: 700;
            color: var(--dark-bg);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .form-control-custom {
            width: 100%;
            background: white;
            border: 2px solid #E5E7EB;
            border-radius: 14px;
            padding: 12px 15px;
            font-size: 1rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .form-control-custom:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .btn-submit {
            width: 100%;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 18px;
            font-size: 1.1rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: var(--transition);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
        }

        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        @media (max-width: 600px) {
            .stepper { display: none; }
            .choice-grid { grid-template-columns: 1fr; }
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

        <div class="nominee-card">
            <h2 class="fw-bold mb-2">Nominee Details</h2>
            <p class="text-muted mb-5">Would you like to add a nominee to your account?</p>
            
            <form id="nomineeForm">
                @csrf
                
                @php
                    $nomineeData = json_decode($submission->nominee, true);
                    $hasNominee = !empty($nomineeData['name']) || $submission->has_nominee == 'yes';
                @endphp
                
                <div class="choice-grid">
                    <label class="choice-item">
                        <input type="radio" name="has_nominee" value="no" {{ !$hasNominee ? 'checked' : '' }}>
                        <div class="choice-box">
                            <div class="choice-icon">
                                <i class="ti ti-user-x"></i>
                            </div>
                            <span class="choice-title">I'll do it later</span>
                            <small class="text-muted">Skip for now</small>
                        </div>
                    </label>

                    <label class="choice-item">
                        <input type="radio" name="has_nominee" value="yes" {{ $hasNominee ? 'checked' : '' }}>
                        <div class="choice-box">
                            <div class="choice-icon">
                                <i class="ti ti-user-plus"></i>
                            </div>
                            <span class="choice-title">Add Nominee</span>
                            <small class="text-muted">Secure your account</small>
                        </div>
                    </label>
                </div>

                <!-- Hidden Nominee Form -->
                <div id="nomineeFormContent" style="{{ $hasNominee ? 'display: block;' : '' }}">
                    <h5 class="fw-bold mb-4">Nominee Information</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Nominee Name</label>
                        <input type="text" name="nominee[name]" class="form-control-custom" placeholder="Full name of nominee" value="{{ $nomineeData['name'] ?? '' }}">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Relation</label>
                            <select name="nominee[relation]" class="form-control-custom">
                                @foreach(['spouse', 'father', 'mother', 'son', 'daughter', 'brother', 'sister'] as $rel)
                                    <option value="{{ $rel }}" {{ ($nomineeData['relation'] ?? '') == $rel ? 'selected' : '' }}>{{ ucfirst($rel) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="nominee[dob]" class="form-control-custom" value="{{ $nomineeData['dob'] ?? '' }}">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Share (%)</label>
                        <input type="number" name="nominee[share]" class="form-control-custom" value="{{ $nomineeData['share'] ?? '100' }}" min="1" max="100">
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    Continue to Document Upload
                    <i class="ti ti-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('input[name="has_nominee"]').on('change', function() {
                if ($(this).val() === 'yes') {
                    $('#nomineeFormContent').slideDown();
                    $('#nomineeFormContent input, #nomineeFormContent select').prop('required', true);
                } else {
                    $('#nomineeFormContent').slideUp();
                    $('#nomineeFormContent input, #nomineeFormContent select').prop('required', false);
                }
            });

            $('#nomineeForm').on('submit', function(e) {
                e.preventDefault();
                
                const btn = $('#submitBtn');
                const originalHtml = btn.html();

                btn.prop('disabled', true).html('<i class="ti ti-loader-2 animate-spin"></i> Saving...');

                $.ajax({
                    url: '{{ route("ekyc.form.submit-step", ["step" => $step]) }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.redirect;
                        } else {
                            alert(response.message || 'Saving failed');
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
