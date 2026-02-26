<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Email Verification - Stockology eKYC</title>
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
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --white: #ffffff;
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
            width: 100px;
        }

        .step-icon {
            width: 54px;
            height: 54px;
            background: white;
            border: 4px solid #e5e7eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #9ca3af;
            transition: var(--transition);
            font-size: 1.25rem;
        }

        .step-item.active .step-icon {
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);
        }

        .step-item.completed .step-icon {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .step-label {
            margin-top: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #9ca3af;
            text-align: center;
        }

        /* Card Styles */
        .kyc-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.05);
            overflow: hidden;
            border: 1px solid #f3f4f6;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-header-custom {
            background: var(--dark-bg);
            padding: 40px;
            color: white;
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .header-icon-circle {
            width: 70px;
            height: 70px;
            background: rgba(16, 185, 129, 0.15);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--primary-color);
        }

        .header-text h2 {
            font-size: 1.8rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: -1px;
        }

        .header-text p {
            color: #9ca3af;
            margin: 5px 0 0;
            font-size: 1rem;
        }

        .card-body-custom {
            padding: 50px;
        }

        .form-label {
            font-weight: 700;
            color: var(--dark-bg);
            font-size: 0.95rem;
            margin-bottom: 12px;
            display: block;
        }

        .input-wrapper {
            position: relative;
            margin-bottom: 30px;
        }

        .input-wrapper i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.4rem;
            transition: var(--transition);
        }

        .form-control-custom {
            width: 100%;
            background: #F9FAFB;
            border: 2px solid #E5E7EB;
            border-radius: 16px;
            padding: 18px 18px 18px 55px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .form-control-custom:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.1);
        }

        .btn-submit {
            width: 100%;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 20px;
            border-radius: 18px;
            font-size: 1.2rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: var(--transition);
            cursor: pointer;
            box-shadow: 0 15px 30px rgba(16, 185, 129, 0.2);
        }

        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.3);
        }

        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #D1FAE5;
            color: #065F46;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 30px;
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

        <div class="kyc-card">
            <div class="card-header-custom">
                <div class="header-icon-circle">
                    <i class="ti ti-mail"></i>
                </div>
                <div class="header-text">
                    <h2>Email Verification</h2>
                    <p>Verify your email to continue your application</p>
                </div>
            </div>

            <div class="card-body-custom">
                @if($submission->mobile_verified_at)
                    <div class="verified-badge">
                        <i class="ti ti-circle-check"></i> Mobile Verified
                    </div>
                @endif

                <form id="emailEntryForm">
                    @csrf
                    <input type="hidden" name="verification_type" value="email">
                    
                    <label class="form-label">Full Name</label>
                    <div class="input-wrapper">
                        <input type="text" name="full_name" class="form-control-custom" placeholder="As per PAN" value="{{ $submission->additional_data['full_name'] ?? '' }}" required>
                        <i class="ti ti-user"></i>
                    </div>

                    <label class="form-label">Email Address</label>
                    <div class="input-wrapper">
                        <input type="email" name="identifier" class="form-control-custom" placeholder="you@example.com" value="{{ $submission->email }}" required>
                        <i class="ti ti-mail"></i>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        Send Verification Code
                        <i class="ti ti-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-body text-center p-5">
                    <div class="mb-4">
                        <i class="ti ti-circle-x-filled text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="fw-bold mb-3" id="errorModalLabel">Error!</h3>
                    <p class="text-muted mb-4" id="errorMsg">An unexpected error occurred. Please try again.</p>
                    <button type="button" class="btn btn-danger w-100 py-3 fw-bold" data-bs-dismiss="modal" style="border-radius: 12px;">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery & Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function showError(message) {
            $('#errorMsg').text(message || 'An unexpected error occurred. Please try again.');
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
        }

        $(document).ready(function() {
            $('#emailEntryForm').on('submit', function(e) {
                e.preventDefault();
                
                const btn = $('#submitBtn');
                const originalHtml = btn.html();

                btn.prop('disabled', true).html('<i class="ti ti-loader-2 animate-spin"></i> Sending OTP...');

                $.ajax({
                    url: '{{ route("ekyc.form.verify-contact") }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.redirect;
                        } else {
                            showError(response.message || 'Failed to send OTP');
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        let message = 'An error occurred. Please try again.';
                        
                        if(response && response.message) {
                            message = response.message;
                        } else if(response && response.errors) {
                            message = Object.values(response.errors)[0][0];
                        }
                        
                        showError(message);
                        btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });
        });
    </script>
</body>
</html>
