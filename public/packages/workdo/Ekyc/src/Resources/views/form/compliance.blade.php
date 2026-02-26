<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Compliance Declarations - Stockology eKYC</title>
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

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 0;
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
            background: var(--dark-bg);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            transition: var(--transition);
        }

        .btn-submit:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-submit-bottom {
            width: 100%;
            margin-top: 30px;
            padding: 18px;
            border-radius: 12px;
        }

        @media (max-width: 991px) {
            .section-header { flex-direction: column; gap: 15px; align-items: flex-start; }
            .btn-submit { width: 100%; }
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
            <!-- Left Side: Information -->
            <div class="col-lg-5">
                <div class="residency-card">
                    <h3 class="fw-bold mb-4" style="color: var(--dark-bg);">Compliance Declarations</h3>
                    <ul class="residency-list">
                        <li class="residency-item">
                            <i class="ti ti-info-circle-filled"></i>
                            <div>
                                SEBI and Exchange regulations require these declarations for opening and maintaining your trading and demat account.
                            </div>
                        </li>
                        <li class="residency-item">
                            <i class="ti ti-shield-check-filled"></i>
                            <div>
                                Your data is secured with industry-standard encryption and used only for regulatory compliance.
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Right Side: Compliance Form -->
            <div class="col-lg-7">
                <div class="form-card">
                    <form id="complianceForm">
                        @csrf
                        
                        <div class="section-header">
                            <h3 class="section-title">Additional Declarations</h3>
                            <button type="submit" class="btn-submit" id="submitBtnTop">
                                Continue <i class="ti ti-arrow-right"></i>
                            </button>
                        </div>

                        <!-- 1. DDPI -->
                        <div class="compliance-section">
                            <label class="compliance-label">DDPI <button type="button" class="info-btn" title="Demat Debit and Pledge Instruction"><i class="ti ti-info-circle"></i></button></label>
                            <div class="radio-options">
                                <label class="radio-option">
                                    <input type="radio" checked disabled>
                                    <input type="hidden" name="ddpi_consent" value="1">
                                    <i class="ti ti-circle-check-filled text-success"></i>
                                    <span>Yes</span>
                                </label>
                                <label class="radio-option disabled">
                                    <input type="radio" disabled>
                                    <span>No</span>
                                </label>
                            </div>
                        </div>

                        <!-- 2. Running Account Authorization -->
                        <div class="compliance-section">
                            <label class="compliance-label">Running Account Authorization</label>
                            <div class="radio-options">
                                <label class="radio-option">
                                    <input type="radio" name="running_account_auth" value="once_in_month" {{ $submission->running_account_auth == 'once_in_month' ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->running_account_auth == 'once_in_month' ? 'inline' : 'none' }}"></i>
                                    <span>Once In a Month</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="running_account_auth" value="once_in_quarter" {{ $submission->running_account_auth != 'once_in_month' ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->running_account_auth != 'once_in_month' ? 'inline' : 'none' }}"></i>
                                    <span>Once In a Quarter</span>
                                </label>
                            </div>
                        </div>

                        <!-- 3. Credits -->
                        <div class="compliance-section">
                            <label class="compliance-label">I / We instruct you to receive each & every credits in my / our account</label>
                            <div class="radio-options">
                                <label class="radio-option">
                                    <input type="radio" name="receive_credits" value="1" {{ $submission->receive_credits !== false ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->receive_credits !== false ? 'inline' : 'none' }}"></i>
                                    <span>Yes</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="receive_credits" value="0" {{ $submission->receive_credits === false ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->receive_credits === false ? 'inline' : 'none' }}"></i>
                                    <span>No</span>
                                </label>
                            </div>
                        </div>

                        <!-- 4. Pledge -->
                        <div class="compliance-section">
                            <label class="compliance-label">I / We would like to instruct you to accept all the pledge instructions in my / our account without any other further information from my / our end</label>
                            <div class="radio-options">
                                <label class="radio-option">
                                    <input type="radio" name="pledge_instruction" value="1" {{ $submission->pledge_instruction === true ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->pledge_instruction === true ? 'inline' : 'none' }}"></i>
                                    <span>Yes</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="pledge_instruction" value="0" {{ $submission->pledge_instruction !== true ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->pledge_instruction !== true ? 'inline' : 'none' }}"></i>
                                    <span>No</span>
                                </label>
                            </div>
                        </div>

                        <!-- 5. Nominee Statement -->
                        <div class="compliance-section">
                            <label class="compliance-label">I/We want the details of my/our nominee to be printed in the Statement of Holding</label>
                            <div class="radio-options">
                                <label class="radio-option">
                                    <input type="radio" name="nominee_statement_type" value="name" {{ $submission->nominee_statement_type == 'name' ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->nominee_statement_type == 'name' ? 'inline' : 'none' }}"></i>
                                    <span>Name of Nominee(s)</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="nominee_statement_type" value="nomination_status" {{ $submission->nominee_statement_type != 'name' ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->nominee_statement_type != 'name' ? 'inline' : 'none' }}"></i>
                                    <span>Nomination: Yes / No</span>
                                </label>
                            </div>
                        </div>

                        <!-- 6. Statement Requirement -->
                        <div class="compliance-section">
                            <label class="compliance-label">Account Statement Requirement</label>
                            <div class="radio-options">
                                <label class="radio-option">
                                    <input type="radio" name="statement_requirement" value="daily" {{ $submission->statement_requirement == 'daily' || empty($submission->statement_requirement) ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->statement_requirement == 'daily' || empty($submission->statement_requirement) ? 'inline' : 'none' }}"></i>
                                    <span>Daily</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="statement_requirement" value="weekly" {{ $submission->statement_requirement == 'weekly' ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->statement_requirement == 'weekly' ? 'inline' : 'none' }}"></i>
                                    <span>Weekly</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="statement_requirement" value="fortnightly" {{ $submission->statement_requirement == 'fortnightly' ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->statement_requirement == 'fortnightly' ? 'inline' : 'none' }}"></i>
                                    <span>Fortnightly</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="statement_requirement" value="monthly" {{ $submission->statement_requirement == 'monthly' ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->statement_requirement == 'monthly' ? 'inline' : 'none' }}"></i>
                                    <span>Monthly</span>
                                </label>
                            </div>
                        </div>

                        <!-- 7. Electronic Statement -->
                        <div class="compliance-section">
                            <label class="compliance-label">I / We request you to send electronic transaction-cum-holding statement at the e-mail ID.</label>
                            <div class="radio-options">
                                <label class="radio-option">
                                    <input type="radio" name="electronic_statement" value="1" {{ $submission->electronic_statement !== false ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->electronic_statement !== false ? 'inline' : 'none' }}"></i>
                                    <span>Yes</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="electronic_statement" value="0" {{ $submission->electronic_statement === false ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->electronic_statement === false ? 'inline' : 'none' }}"></i>
                                    <span>No</span>
                                </label>
                            </div>
                        </div>

                        <!-- 8. Share Email RTA -->
                        <div class="compliance-section">
                            <label class="compliance-label">I / We would like to share the email ID with the RTA</label>
                            <div class="radio-options">
                                <label class="radio-option">
                                    <input type="radio" name="share_email_rta" value="1" {{ $submission->share_email_rta !== false ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->share_email_rta !== false ? 'inline' : 'none' }}"></i>
                                    <span>Yes</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="share_email_rta" value="0" {{ $submission->share_email_rta === false ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->share_email_rta === false ? 'inline' : 'none' }}"></i>
                                    <span>No</span>
                                </label>
                            </div>
                        </div>

                        <!-- 9. Annual Report -->
                        <div class="compliance-section">
                            <label class="compliance-label">I / We would like to receive the Annual Report</label>
                            <div class="radio-options">
                                <label class="radio-option disabled">
                                    <input type="radio" disabled>
                                    <span>Physical</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" checked disabled>
                                    <input type="hidden" name="annual_report_media" value="electronic">
                                    <i class="ti ti-circle-check-filled text-success"></i>
                                    <span>Electronic</span>
                                </label>
                                <label class="radio-option disabled">
                                    <input type="radio" disabled>
                                    <span>Both Physical and Electronic</span>
                                </label>
                            </div>
                        </div>

                        <!-- 10. Dividend Direct -->
                        <div class="compliance-section">
                            <label class="compliance-label">I / We wish to receive dividend / interest directly into my bank account through ECS.</label>
                            <div class="radio-options">
                                <label class="radio-option">
                                    <input type="radio" name="receive_dividend_directly" value="1" {{ $submission->receive_dividend_directly !== false ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->receive_dividend_directly !== false ? 'inline' : 'none' }}"></i>
                                    <span>Yes</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="receive_dividend_directly" value="0" {{ $submission->receive_dividend_directly === false ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->receive_dividend_directly === false ? 'inline' : 'none' }}"></i>
                                    <span>No</span>
                                </label>
                            </div>
                        </div>

                        <!-- 11. DIS Booklet -->
                        <div class="compliance-section">
                            <label class="compliance-label">DIS Booklet</label>
                            <div class="radio-options">
                                <label class="radio-option">
                                    <input type="radio" name="dis_booklet" value="1" {{ $submission->dis_booklet === true ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->dis_booklet === true ? 'inline' : 'none' }}"></i>
                                    <span>Yes</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="dis_booklet" value="0" {{ $submission->dis_booklet !== true ? 'checked' : '' }}>
                                    <i class="ti ti-circle-check-filled text-success" style="display: {{ $submission->dis_booklet !== true ? 'inline' : 'none' }}"></i>
                                    <span>No</span>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit btn-submit-bottom" id="submitBtnBottom">
                            Continue <i class="ti ti-arrow-right"></i>
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
                if (!name) return;
                $(`input[name="${name}"]`).parent().find('.ti-circle-check-filled').hide();
                if ($(this).is(':checked')) {
                    $(this).parent().find('.ti-circle-check-filled').show();
                }
            });

            $('#complianceForm').on('submit', function(e) {
                e.preventDefault();
                
                const btnTop = $('#submitBtnTop');
                const btnBottom = $('#submitBtnBottom');
                const originalHtmlTop = btnTop.html();

                btnTop.prop('disabled', true).html('<i class="ti ti-loader-2 animate-spin"></i> Saving...');
                btnBottom.prop('disabled', true).html('<i class="ti ti-loader-2 animate-spin"></i> Saving...');

                $.ajax({
                    url: '{{ route("ekyc.form.submit-step", ["step" => $step]) }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.redirect;
                        } else {
                            alert(response.message || 'Saving failed');
                            btnTop.prop('disabled', false).html(originalHtmlTop);
                            btnBottom.prop('disabled', false).html('Continue <i class="ti ti-arrow-right"></i>');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        alert(response.message || 'An error occurred');
                        btnTop.prop('disabled', false).html(originalHtmlTop);
                        btnBottom.prop('disabled', false).html('Continue <i class="ti ti-arrow-right"></i>');
                    }
                });
            });
        });
    </script>
</body>
</html>
