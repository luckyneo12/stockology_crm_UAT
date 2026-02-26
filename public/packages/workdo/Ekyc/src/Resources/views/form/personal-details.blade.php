<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Personal Details - Stockology eKYC</title>
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
            max-width: 1100px;
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

        /* Layout Cards */
        .residency-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            border: 1px solid #f3f4f6;
            padding: 30px;
            height: 100%;
        }

        .form-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.05);
            border: 1px solid #f3f4f6;
            padding: 40px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 25px;
            color: var(--dark-bg);
        }

        .residency-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .residency-item {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            font-size: 0.85rem;
            line-height: 1.6;
            color: #4b5563;
        }

        .residency-item i {
            color: var(--primary-color);
            font-size: 1.1rem;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .form-label {
            font-weight: 600;
            color: #4b5563;
            font-size: 0.85rem;
            margin-bottom: 6px;
        }

        .form-label span { color: #ef4444; }

        .form-control-custom {
            width: 100%;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 10px 15px;
            font-size: 0.95rem;
            color: var(--text-main);
            transition: var(--transition);
        }

        .form-control-custom:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .compliance-section {
            padding: 20px 0;
            border-top: 1px solid #f1f5f9;
        }

        .compliance-label {
            font-size: 0.85rem;
            color: #4b5563;
            margin-bottom: 12px;
            display: block;
            line-height: 1.4;
        }

        .radio-options {
            display: flex;
            gap: 25px;
            align-items: center;
            flex-wrap: wrap;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            color: #374151;
        }

        .radio-option input {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
        }

        .radio-option.disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .info-btn {
            background: none;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
            padding: 0;
            margin-left: 5px;
        }

        .btn-submit {
            width: 100%;
            background: var(--dark-bg);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            margin-top: 30px;
            transition: var(--transition);
        }

        .btn-submit:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        @media (max-width: 991px) {
            .grid-2 { grid-template-columns: 1fr; }
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

        <div class="row g-4">
            <!-- Left Side: Residency for Taxation -->
            <div class="col-lg-5">
                <div class="residency-card">
                    <h3 class="section-title">Residency for Taxation</h3>
                    <ul class="residency-list">
                        <li class="residency-item">
                            <i class="ti ti-square-check-filled"></i>
                            <div>
                                I have understood the information requirements of this form (read along with FATCA and CRS instructions) and hereby confirm that the information provided by me/us on this form is true correct and complete. I also confirm that I have read and understood the FATCA & Terms and conditions below and hereby accept the same, Name of your account will be updated as per the income TAX database due to exchange regulations.
                            </div>
                        </li>
                        <li class="residency-item">
                            <i class="ti ti-square-check-filled"></i>
                            <div>
                                I confirm to have read and understood the contents of equity Annexure and commodity annexure documents.
                            </div>
                        </li>
                        <li class="residency-item">
                            <i class="ti ti-square-check-filled"></i>
                            <div>
                                I confirm that my investor/trader category for commodity segment is by default marked as others. I have read and understood the same and updated if required.
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Right Side: Personal Details Form -->
            <div class="col-lg-7">
                <div class="form-card">
                    <form id="detailsForm">
                        @csrf
                        
                        <div class="grid-2">
                            <div>
                                <label class="form-label">Father/Spouse Name <span>*</span></label>
                                <input type="text" name="father_name" class="form-control-custom" value="{{ $submission->father_name }}" placeholder="Enter Name" required>
                            </div>
                            <div>
                                <label class="form-label">Mother Name <span>*</span></label>
                                <input type="text" name="mother_name" class="form-control-custom" value="{{ $submission->mother_name }}" placeholder="Enter Name" required>
                            </div>
                        </div>

                        <div class="grid-2">
                            <div>
                                <label class="form-label">Marital Status <span>*</span></label>
                                <select name="marital_status" class="form-control-custom" required>
                                    <option value="">--Marital Status--</option>
                                    <option value="single" {{ $submission->marital_status == 'single' ? 'selected' : '' }}>Single</option>
                                    <option value="married" {{ $submission->marital_status == 'married' ? 'selected' : '' }}>Married</option>
                                    <option value="others" {{ $submission->marital_status == 'others' ? 'selected' : '' }}>Others</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Education <span>*</span></label>
                                <select name="education" class="form-control-custom" required>
                                    <option value="">--Education--</option>
                                    <option value="under_graduate" {{ $submission->education == 'under_graduate' ? 'selected' : '' }}>Under Graduate</option>
                                    <option value="graduate" {{ $submission->education == 'graduate' ? 'selected' : '' }}>Graduate</option>
                                    <option value="post_graduate" {{ $submission->education == 'post_graduate' ? 'selected' : '' }}>Post Graduate</option>
                                    <option value="professional" {{ $submission->education == 'professional' ? 'selected' : '' }}>Professional</option>
                                    <option value="others" {{ $submission->education == 'others' ? 'selected' : '' }}>Others</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid-2">
                            <div>
                                <label class="form-label">Occupation <span>*</span></label>
                                <select name="occupation" class="form-control-custom" required>
                                    <option value="">--Occupation--</option>
                                    <option value="private_sector" {{ $submission->occupation == 'private_sector' ? 'selected' : '' }}>Private Sector</option>
                                    <option value="public_sector" {{ $submission->occupation == 'public_sector' ? 'selected' : '' }}>Public Sector</option>
                                    <option value="government" {{ $submission->occupation == 'government' ? 'selected' : '' }}>Government Service</option>
                                    <option value="business" {{ $submission->occupation == 'business' ? 'selected' : '' }}>Business</option>
                                    <option value="professional" {{ $submission->occupation == 'professional' ? 'selected' : '' }}>Professional</option>
                                    <option value="retired" {{ $submission->occupation == 'retired' ? 'selected' : '' }}>Retired</option>
                                    <option value="housewife" {{ $submission->occupation == 'housewife' ? 'selected' : '' }}>Housewife</option>
                                    <option value="student" {{ $submission->occupation == 'student' ? 'selected' : '' }}>Student</option>
                                    <option value="others" {{ $submission->occupation == 'others' ? 'selected' : '' }}>Others</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Annual Income <span>*</span></label>
                                <select name="annual_income" class="form-control-custom" required>
                                    <option value="">--Annual Income--</option>
                                    <option value="below_1" {{ $submission->annual_income == 'below_1' ? 'selected' : '' }}>Below 1 Lakh</option>
                                    <option value="1_5" {{ $submission->annual_income == '1_5' ? 'selected' : '' }}>1 - 5 Lakhs</option>
                                    <option value="5_10" {{ $submission->annual_income == '5_10' ? 'selected' : '' }}>5 - 10 Lakhs</option>
                                    <option value="10_25" {{ $submission->annual_income == '10_25' ? 'selected' : '' }}>10 - 25 Lakhs</option>
                                    <option value="over_25" {{ $submission->annual_income == 'over_25' ? 'selected' : '' }}>Above 25 Lakhs</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid-2">
                            <div>
                                <label class="form-label">Trading Experience (Years)<span>*</span></label>
                                <select name="trading_experience" class="form-control-custom" required>
                                    <option value="">--Trading Experience--</option>
                                    <option value="no_experience" {{ $submission->trading_experience == 'no_experience' ? 'selected' : '' }}>No Experience</option>
                                    <option value="1" {{ $submission->trading_experience == '1' ? 'selected' : '' }}>1 Year</option>
                                    <option value="2" {{ $submission->trading_experience == '2' ? 'selected' : '' }}>2 Years</option>
                                    <option value="3" {{ $submission->trading_experience == '3' ? 'selected' : '' }}>3 Years</option>
                                    <option value="4" {{ $submission->trading_experience == '4' ? 'selected' : '' }}>4 Years</option>
                                    <option value="5_plus" {{ $submission->trading_experience == '5_plus' ? 'selected' : '' }}>5+ Years</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Politically Exposed <span>*</span> <button type="button" class="info-btn" title="Politically Exposed Person"><i class="ti ti-info-circle"></i></button></label>
                                <select name="is_pep" class="form-control-custom" required>
                                    <option value="">--Politically Exposed--</option>
                                    <option value="0" {{ $submission->is_pep === 0 ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ $submission->is_pep === 1 ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid-2">
                            <div>
                                <label class="form-label">Networth<span>*</span></label>
                                <input type="text" name="networth" class="form-control-custom" value="{{ $submission->networth }}" placeholder="Networth" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                            <div>
                                <label class="form-label">Networth Date<span>*</span></label>
                                <select name="networth_date" class="form-control-custom" required>
                                    <option value="{{ date('Y-m-d') }}" selected>{{ date('d/m/Y') }}</option>
                                </select>
                            </div>
                        </div>


                        <button type="submit" class="btn-submit" id="submitBtn">
                            Submit Details
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Visual feedback for radio selections
            $('input[type="radio"]').on('change', function() {
                const name = $(this).attr('name');
                $(`input[name="${name}"]`).parent().find('.ti-circle-check-filled').hide();
                if ($(this).is(':checked')) {
                    $(this).parent().find('.ti-circle-check-filled').show();
                }
            });

            $('#detailsForm').on('submit', function(e) {
                e.preventDefault();
                
                const btn = $('#submitBtn');
                const originalHtml = btn.html();

                btn.prop('disabled', true).html('<i class="ti ti-loader-2 animate-spin"></i> Saving Details...');

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
