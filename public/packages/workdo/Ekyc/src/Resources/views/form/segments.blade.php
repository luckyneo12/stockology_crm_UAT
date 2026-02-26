<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Trading Segments - Stockology eKYC</title>
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
            max-width: 900px;
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

        /* Card Styles */
        .segment-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.05);
            border: 1px solid #f3f4f6;
            padding: 40px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 30px;
            letter-spacing: -0.5px;
        }

        /* Segment Grid */
        .segment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .segment-item {
            position: relative;
            cursor: pointer;
        }

        .segment-item input {
            position: absolute;
            opacity: 0;
        }

        .segment-box {
            background: #F9FAFB;
            border: 2px solid #E5E7EB;
            border-radius: 20px;
            padding: 25px 20px;
            text-align: center;
            transition: var(--transition);
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .segment-item input:checked + .segment-box,
        .segment-box.permanent-selected {
            background: white !important;
            border-color: var(--primary-color) !important;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.15) !important;
        }

        .segment-icon {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #6b7280;
            transition: var(--transition);
        }

        .segment-item input:checked + .segment-box .segment-icon,
        .segment-box.permanent-selected .segment-icon {
            background: var(--primary-light) !important;
            color: var(--primary-color) !important;
        }

        .segment-title {
            font-weight: 700;
            font-size: 1rem;
            color: var(--dark-bg);
        }

        /* Brokerage Plans */
        .plan-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .plan-item {
            position: relative;
            cursor: pointer;
        }

        .plan-item input {
            position: absolute;
            opacity: 0;
        }

        .plan-box {
            background: #F9FAFB;
            border: 2px solid #E5E7EB;
            border-radius: 20px;
            padding: 30px;
            transition: var(--transition);
            height: 100%;
        }

        .plan-item input:checked + .plan-box {
            background: white;
            border-color: var(--primary-color);
            box-shadow: 0 15px 35px rgba(16, 185, 129, 0.2);
        }

        .plan-badge {
            display: inline-block;
            padding: 6px 12px;
            background: #E5E7EB;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 15px;
            transition: var(--transition);
        }

        .plan-item input:checked + .plan-box .plan-badge {
            background: var(--primary-color);
            color: white;
        }

        .plan-price {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .plan-features {
            font-size: 0.85rem;
            color: var(--text-muted);
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .plan-features li {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .plan-features li i { color: var(--primary-color); }

        /* Helpers */
        .toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #F8FAFC;
            padding: 20px 25px;
            border-radius: 18px;
            margin-bottom: 15px;
            border: 1px solid #E2E8F0;
        }

        .toggle-info h5 {
            font-weight: 750;
            margin: 0;
            font-size: 1rem;
        }

        .toggle-info p {
            margin: 5px 0 0;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
            cursor: pointer;
        }

        .form-switch .form-check-input:checked {
            background-color: var(--primary-color);
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

        @media (max-width: 768px) {
            .stepper { display: none; }
            .segment-grid { grid-template-columns: repeat(2, 1fr); }
            .plan-grid { grid-template-columns: 1fr; }
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

        <div class="segment-card">
            <h2 class="section-title">Where do you want to trade?</h2>
            
            <form id="segmentsForm">
                @csrf
                
                <h5 class="fw-bold mb-3">Select Segments</h5>
                <div class="segment-grid">
                    @php
                        $userSegments = json_decode($submission->trading_segments, true) ?? ['equity'];
                        $segments = [
                            ['id' => 'equity', 'name' => 'Cash (Equity)', 'icon' => 'ti-trending-up'],
                            ['id' => 'fno', 'name' => 'F&O', 'icon' => 'ti-chart-arrows'],
                        ];
                    @endphp

                    @foreach($segments as $seg)
                        <label class="segment-item">
                            @if($seg['id'] === 'equity')
                                <input type="checkbox" checked disabled>
                                <input type="hidden" name="segments[]" value="equity">
                                <div class="segment-box permanent-selected">
                            @else
                                <input type="checkbox" name="segments[]" value="{{ $seg['id'] }}" {{ in_array($seg['id'], $userSegments) ? 'checked' : '' }}>
                                <div class="segment-box">
                            @endif
                                <div class="segment-icon">
                                    <i class="ti {{ $seg['icon'] }}"></i>
                                </div>
                                <span class="segment-title">{{ $seg['name'] }}</span>
                                @if($seg['id'] === 'equity')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle mt-1" style="font-size: 0.65rem;">MANDATORY</span>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>


                <h5 class="fw-bold mb-3 mt-5">Brokerage Plan</h5>
                <div class="plan-grid">
                    <label class="plan-item">
                        <input type="radio" name="brokerage_plan" value="standard" checked>
                        <div class="plan-box" style="border-color: var(--primary-color); background: white;">
                            <span class="plan-badge" style="background: var(--primary-color); color: white;">BROKERAGE PLAN</span>
                            <div class="plan-price" style="font-size: 1.2rem; margin-bottom: 15px;">Standard Pricing</div>
                            <ul class="plan-features">
                                <li><i class="ti ti-check"></i> <strong>EQUITY CASH:</strong> 0.05%</li>
                                <li><i class="ti ti-check"></i> <strong>EQUITY DELIVER:</strong> 0.5%</li>
                                <li><i class="ti ti-check"></i> <strong>FUTURE:</strong> 0.05%</li>
                                <li><i class="ti ti-check"></i> <strong>OPTIONS:</strong> ₹50 PER LOT</li>
                            </ul>
                        </div>
                    </label>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    Continue to Personal Details
                    <i class="ti ti-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#segmentsForm').on('submit', function(e) {
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
