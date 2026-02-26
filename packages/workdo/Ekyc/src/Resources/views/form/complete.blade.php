<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Application Status - Stockology eKYC</title>
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

        /* Application Tracker */
        .status-card {
            background: white;
            border-radius: 32px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.05);
            padding: 50px;
            text-align: center;
            border: 1px solid #f1f5f9;
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .tracker-header h1 {
            font-size: 2.2rem;
            font-weight: 850;
            letter-spacing: -1px;
            margin-bottom: 10px;
        }

        .tracker-header p {
            color: var(--text-muted);
            font-size: 1rem;
            margin-bottom: 50px;
        }

        /* Horizontal Progress Line */
        .progress-line-container {
            position: relative;
            margin: 60px 0;
            display: flex;
            justify-content: space-between;
        }

        .progress-line-container::before {
            content: '';
            position: absolute;
            top: 25px;
            left: 10%;
            right: 10%;
            height: 4px;
            background: #e2e8f0;
            z-index: 1;
        }

        .progress-line-active {
            position: absolute;
            top: 25px;
            left: 10%;
            width: 40%; /* Progress to "Sent for Verification" */
            height: 4px;
            background: var(--primary-color);
            z-index: 2;
            transition: width 1s ease-in-out;
        }

        .tracker-step {
            position: relative;
            z-index: 3;
            width: 150px;
            text-align: center;
        }

        .tracker-dot {
            width: 54px;
            height: 54px;
            background: white;
            border: 4px solid #e2e8f0;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #94a3b8;
            transition: var(--transition);
        }

        .tracker-step.completed .tracker-dot {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .tracker-step.active .tracker-dot {
            border-color: var(--primary-color);
            color: var(--primary-color);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
        }

        .tracker-label {
            font-weight: 700;
            font-size: 0.95rem;
            color: #94a3b8;
        }

        .tracker-step.completed .tracker-label, .tracker-step.active .tracker-label {
            color: var(--dark-bg);
        }

        /* Summary Grid */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 50px;
        }

        .summary-box {
            background: #F8FAFC;
            padding: 25px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }

        .success-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 15px;
        }
        
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef9c3; color: #854d0e; }

        .summary-box h6 { font-weight: 800; margin-bottom: 5px; font-size: 0.95rem; }
        .summary-box p { font-size: 0.8rem; color: var(--text-muted); margin: 0; }

        .btn-finish {
            margin-top: 50px;
            padding: 18px 40px;
            background: var(--dark-bg);
            color: white;
            border: none;
            border-radius: 18px;
            font-size: 1.1rem;
            font-weight: 800;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .btn-finish:hover {
            background: black;
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .summary-grid { grid-template-columns: 1fr; }
            .tracker-step { width: 100px; }
            .tracker-label { font-size: 0.7rem; }
        }
    </style>
</head>
<body>

    <div class="kyc-container">
        <div class="status-card">
            
            <div class="tracker-header">
                <h1>Let's track your application</h1>
                <p>Last updated on {{ date('d M, Y') }} at {{ date('h:i A') }}</p>
            </div>

            <div class="progress-line-container">
                <div class="progress-line-active" style="width: 50%"></div>
                
                <div class="tracker-step completed">
                    <div class="tracker-dot"><i class="ti ti-check"></i></div>
                    <div class="tracker-label">On-boarding Started</div>
                </div>

                <div class="tracker-step active">
                    <div class="tracker-dot"><i class="ti ti-loader-2 animate-spin"></i></div>
                    <div class="tracker-label">Sent for Verification</div>
                </div>

                <div class="tracker-step">
                    <div class="tracker-dot"><i class="ti ti-circle-check"></i></div>
                    <div class="tracker-label">Approved</div>
                </div>
            </div>

            <div class="summary-grid">
                <div class="summary-box">
                    @php 
                        $personalDone = $submission->details_completed_at !== null;
                    @endphp
                    <div class="success-badge {{ $personalDone ? 'badge-success' : 'badge-warning' }}">
                        <i class="ti ti-{{ $personalDone ? 'check' : 'dots' }}"></i> {{ $personalDone ? 'Success' : 'In Progress' }}
                    </div>
                    <h6>Personal Details</h6>
                    <p>{{ $personalDone ? 'Verified from Digilocker' : 'Profile information pending' }}</p>
                </div>
                
                <div class="summary-box">
                    @php 
                        $nomineeDone = $submission->nominee_completed_at !== null;
                    @endphp
                    <div class="success-badge {{ $nomineeDone ? 'badge-success' : 'badge-warning' }}">
                        <i class="ti ti-{{ $nomineeDone ? 'check' : 'dots' }}"></i> {{ $nomineeDone ? 'Success' : 'In Progress' }}
                    </div>
                    <h6>Nominee Added</h6>
                    <p>{{ $nomineeDone ? '01 Nominee details saved' : 'Nominee details pending' }}</p>
                </div>

                <div class="summary-box">
                    @php 
                        $docsDone = $submission->documents_completed_at !== null;
                        
                        // Check e-sign status
                        $esignDone = false;
                        $settings = getCompanyAllSetting($submission->user_id);
                        
                        $templates = !empty($settings['ekyc_pdf_templates']) ? json_decode($settings['ekyc_pdf_templates'], true) : [];
                        $esignDocs = $submission->additional_data['esign_docs'] ?? [];
                        $hasEnabled = false;
                        $allSigned = true;
                        foreach ($templates as $t) {
                            if (($t['is_enabled'] ?? 'off') === 'on') {
                                $hasEnabled = true;
                                if (($esignDocs[$t['id']]['status'] ?? '') !== 'signed') {
                                    $allSigned = false;
                                    break;
                                }
                            }
                        }

                        // If e-sign is globally ON, or if we specifically have enabled templates
                        if (($settings['ekyc_esign'] ?? 'off') === 'on' || $hasEnabled) {
                             $esignDone = ($hasEnabled && $allSigned);
                        } else {
                            $esignDone = true; // Not required and no templates
                        }

                        $attachmentsSuccess = $docsDone && $esignDone;
                    @endphp
                    <div class="success-badge {{ $attachmentsSuccess ? 'badge-success' : 'badge-warning' }}">
                        <i class="ti ti-{{ $attachmentsSuccess ? 'check' : 'dots' }}"></i> {{ $attachmentsSuccess ? 'Success' : 'Pending' }}
                    </div>
                    <h6>Attachments</h6>
                    <p>
                        @if(!$docsDone) 
                            Document upload pending 
                        @elseif(!$esignDone) 
                            E-sign pending signature
                            @php
                                $esignStepIdx = 11; // Fallback
                                $enabledSteps = \Workdo\Ekyc\Entities\EkycSubmission::getEnabledSteps($submission->user_id);
                                foreach($enabledSteps as $idx => $s) {
                                    if($s['name'] === 'e-Sign Verification') {
                                        $esignStepIdx = $idx;
                                        break;
                                    }
                                }
                            @endphp
                            <div class="mt-2">
                                <a href="{{ route('ekyc.form.step', ['step' => $esignStepIdx]) }}" class="btn btn-sm btn-primary py-1 px-3 fw-bold" style="border-radius: 10px; font-size: 0.75rem;">
                                    <i class="ti ti-edit"></i> Complete Signing
                                </a>
                            </div>
                        @else 
                            E-sign & Documents uploaded 
                        @endif
                    </p>
                </div>
            </div>

            <a href="https://www.stockologysecurities.com" class="btn-finish">
                Back to Stockology
                <i class="ti ti-browser"></i>
            </a>

            <div class="mt-5 pt-4 border-top">
                <p class="small text-muted mb-0">
                    Need help? Contact our support at <strong>support@stockology.com</strong>
                </p>
            </div>

        </div>
    </div>

</body>
</html>
