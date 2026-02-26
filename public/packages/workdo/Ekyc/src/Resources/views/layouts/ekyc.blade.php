<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'E-KYC Verification - Stockology')</title>
    
    <!-- Google Fonts: Inter & Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --primary-light: #d1fae5;
            --secondary: #6366f1;
            --dark: #0f172a;
            --dark-soft: #1e293b;
            --white: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-600: #475569;
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.3);
            --shadow-premium: 0 10px 30px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at 0% 0%, rgba(16, 185, 129, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at 100% 100%, rgba(99, 102, 241, 0.05) 0%, transparent 50%),
                        var(--gray-50);
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        h1, h2, h3, .font-outfit {
            font-family: 'Outfit', sans-serif;
        }

        /* Navbar Styling */
        .navbar-premium {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--dark);
            font-weight: 800;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }

        .logo-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        /* Progress Tracker */
        .progress-wrapper {
            background: var(--white);
            border-bottom: 1px solid var(--gray-200);
            padding: 1.5rem 0;
        }

        .stepper {
            display: flex;
            justify-content: space-between;
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }

        .stepper::before {
            content: '';
            position: absolute;
            top: 18px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--gray-200);
            z-index: 1;
        }

        .step-item {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        .step-dot {
            width: 38px;
            height: 38px;
            background: var(--white);
            border: 2px solid var(--gray-200);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--gray-600);
            transition: var(--transition);
            margin-bottom: 8px;
        }

        .step-item.active .step-dot {
            background: var(--primary);
            border-color: var(--primary);
            color: var(--white);
            box-shadow: 0 0 0 5px var(--primary-light);
        }

        .step-item.completed .step-dot {
            background: var(--primary);
            border-color: var(--primary);
            color: var(--white);
        }

        .step-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-600);
            text-align: center;
            transition: var(--transition);
        }

        .step-item.active .step-label {
            color: var(--primary);
        }

        /* Main Content Area */
        .main-journey {
            flex: 1;
            padding: 4rem 0;
            display: flex;
            align-items: center;
        }

        .premium-card {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 32px;
            box-shadow: var(--shadow-premium);
            padding: 3rem;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }

        .premium-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        /* Form Elements */
        .form-label-premium {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.75rem;
            color: var(--dark-soft);
        }

        .form-control-premium {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid var(--gray-200);
            padding: 0.75rem 1.25rem;
            border-radius: 16px;
            font-weight: 500;
            transition: var(--transition);
        }

        .form-control-premium:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .btn-premium {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border: none;
            padding: 1rem 2rem;
            border-radius: 18px;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: var(--transition);
            width: 100%;
            box-shadow: 0 8px 20px -6px rgba(16, 185, 129, 0.4);
        }

        .btn-premium:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px -8px rgba(16, 185, 129, 0.5);
            color: var(--white);
        }

        .btn-premium:active:not(:disabled) {
            transform: translateY(-1px);
        }

        .btn-premium:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Micro-animations */
        .hover-scale {
            transition: var(--transition);
        }

        .hover-scale:hover {
            transform: scale(1.02);
        }

        .animate-delay-1 { animation-delay: 0.1s; }
        .animate-delay-2 { animation-delay: 0.2s; }
        .animate-delay-3 { animation-delay: 0.3s; }

        @media (max-width: 768px) {
            .premium-card {
                padding: 2rem;
                border-radius: 24px;
                margin: 0 15px;
            }
            .stepper {
                padding: 0 20px;
            }
            .step-label {
                display: none;
            }
        }

        @yield('additional_css')
    </style>
</head>
<body>

    <!-- Header -->
    <nav class="navbar-premium">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="#" class="brand-logo">
                <div class="logo-icon">
                    <i class="ti ti-chart-candle"></i>
                </div>
                Stockology
            </a>
            <div class="d-none d-md-block text-muted small fw-medium">
                <i class="ti ti-lock-check text-primary me-1"></i> 256-bit Encrypted Security
            </div>
        </div>
    </nav>

    <!-- Progress Tracker (Conditional) -->
    @if(isset($step) && $step > 0)
    <div class="progress-wrapper animate__animated animate__fadeInDown">
        <div class="container text-center mb-4">
             <h4 class="font-outfit fw-bold mb-1">Verify Your Identity</h4>
             <p class="text-muted small">Complete the easy steps to start your investment journey</p>
        </div>
        <div class="container">
            <div class="stepper">
                @php
                    $steps = [
                        1 => ['id' => 'mobile', 'label' => 'Mobile'],
                        2 => ['id' => 'pan', 'label' => 'PAN'],
                        3 => ['id' => 'aadhaar', 'label' => 'Aadhaar'],
                        4 => ['id' => 'bank', 'label' => 'Bank'],
                        5 => ['id' => 'selfie', 'label' => 'Selfie'],
                        // Removed Video KYC step
                    ];
                @endphp
                @foreach($steps as $s_num => $s_data)
                    <div class="step-item {{ $step == $s_num ? 'active' : ($step > $s_num ? 'completed' : '') }}">
                        <div class="step-dot">
                            @if($step > $s_num)
                                <i class="ti ti-check"></i>
                            @else
                                {{ $s_num }}
                            @endif
                        </div>
                        <div class="step-label">{{ $s_data['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <main class="main-journey">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-4 mt-auto">
        <div class="container text-center text-muted small">
            &copy; {{ date('Y') }} Stockology Broking Ltd. All Rights Reserved. <br>
            <span class="mt-2 d-inline-block">SEBI Reg No: INZ000000000 | NSE | BSE | MCX</span>
        </div>
    </footer>

    <!-- Modals -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
                <div class="modal-body text-center p-5">
                    <div id="statusIcon" class="mb-4"></div>
                    <h3 id="statusTitle" class="fw-bold mb-3 font-outfit"></h3>
                    <p id="statusMsg" class="text-muted mb-4"></p>
                    <button type="button" class="btn-premium py-2" data-bs-dismiss="modal">
                        Understood
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function showStatus(title, message, isError = true) {
            const icon = isError 
                ? '<i class="ti ti-circle-x-filled text-danger animate__animated animate__shakeX" style="font-size: 5rem;"></i>'
                : '<i class="ti ti-circle-check-filled text-success animate__animated animate__bounceIn" style="font-size: 5rem;"></i>';
            
            $('#statusIcon').html(icon);
            $('#statusTitle').text(title);
            $('#statusMsg').text(message);
            
            const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
            statusModal.show();
        }

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });
    </script>
    
    @yield('additional_js')
</body>
</html>
