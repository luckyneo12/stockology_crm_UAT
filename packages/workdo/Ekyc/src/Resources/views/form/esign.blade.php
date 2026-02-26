@extends('ekyc::layouts.ekyc')

@section('title', 'e-Sign Dashboard - Stockology eKYC')

@section('additional_css')
<style>
    :root {
        --pl-primary: #1a237e;
        --pl-accent: #ff0000;
        --pl-success: #10b981;
        --pl-warning: #f59e0b;
        --pl-light: #f8fafc;
    }
    
    .status-dashboard {
        background: #fff;
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.04);
        margin-bottom: 30px;
        border: 1px solid rgba(0,0,0,0.03);
    }

    .dashboard-header {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 40px;
        padding-bottom: 25px;
        border-bottom: 1.5px dashed #e2e8f0;
    }

    .user-avatar {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--pl-primary), #3949ab);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: #fff;
        box-shadow: 0 10px 20px rgba(26, 35, 126, 0.15);
    }

    .timeline-container {
        position: relative;
        padding: 20px 0;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin: 20px 0;
    }

    .timeline-line {
        position: absolute;
        top: 25px;
        left: 5%;
        right: 5%;
        height: 6px;
        background: #f1f5f9;
        z-index: 1;
        border-radius: 10px;
    }

    .timeline-line-progress {
        position: absolute;
        top: 25px;
        left: 5%;
        width: 100%;
        height: 6px;
        background: linear-gradient(to right, var(--pl-success), #34d399);
        z-index: 2;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(16, 185, 129, 0.2);
    }

    .timeline-step {
        position: relative;
        z-index: 3;
        text-align: center;
        flex: 1;
    }

    .step-icon {
        width: 50px;
        height: 50px;
        background: #fff;
        color: var(--pl-success);
        border: 4px solid var(--pl-success);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 1.2rem;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .timeline-step:hover .step-icon {
        transform: scale(1.1);
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
    }

    .step-label {
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 5px;
        color: #334155;
    }

    .step-date {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 500;
    }

    .docs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 25px;
    }

    .doc-card {
        background: #fff;
        border-radius: 20px;
        padding: 25px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.03);
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .doc-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 0;
        background: var(--pl-primary);
        transition: height 0.3s ease;
    }

    .doc-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.06);
        border-color: rgba(26, 35, 126, 0.1);
    }

    .doc-card:hover::before {
        height: 100%;
    }

    .doc-header {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .doc-type-icon {
        width: 45px;
        height: 45px;
        background: #f1f5f9;
        color: var(--pl-primary);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }

    .doc-title {
        font-weight: 800;
        font-size: 1.15rem;
        color: #1e293b;
        line-height: 1.2;
    }

    .doc-actions {
        display: flex;
        gap: 12px;
        margin-top: auto;
    }

    .btn-preview {
        flex: 1;
        border: 1.5px solid #e2e8f0;
        color: #475569;
        background: #fff;
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 0.9rem;
        font-weight: 700;
        text-align: center;
        text-decoration: none;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-preview:hover {
        border-color: var(--pl-primary);
        color: var(--pl-primary);
        background: #f8fafc;
    }

    .btn-esign {
        flex: 1.5;
        background: var(--pl-primary);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 10px 20px;
        font-size: 0.9rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-esign:hover {
        background: #0d1642;
        transform: scale(1.02);
        box-shadow: 0 6px 15px rgba(26, 35, 126, 0.25);
    }

    .btn-status.signed {
        flex: 1.5;
        background: #ecfdf5;
        color: var(--pl-success);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-weight: 700;
        border: 1px solid rgba(16, 185, 129, 0.1);
        cursor: default;
    }

    .badge-signed {
        position: absolute;
        top: 20px;
        right: 20px;
        background: var(--pl-success);
        color: #fff;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
    }

    .preview-app-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .main-title {
        font-size: 2rem;
        font-weight: 850;
        color: #0f172a;
        letter-spacing: -0.5px;
    }

    .btn-preview-main {
        background: #fff;
        color: var(--pl-primary);
        border: 2px solid var(--pl-primary);
        border-radius: 14px;
        padding: 10px 24px;
        font-weight: 700;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
    }

    .btn-preview-main:hover {
        background: var(--pl-primary);
        color: #fff;
        box-shadow: 0 8px 20px rgba(26, 35, 126, 0.2);
    }

    .ask-lila {
        position: fixed;
        bottom: 40px;
        right: 40px;
        background: linear-gradient(135deg, #0d9488, #0f766e);
        color: #fff;
        padding: 14px 28px;
        border-radius: 50px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 10px 25px rgba(13, 148, 136, 0.4);
        text-decoration: none;
        z-index: 1000;
        font-weight: 800;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 2px solid rgba(255,255,255,0.1);
    }

    .ask-lila:hover {
        transform: scale(1.1) translateY(-5px);
        box-shadow: 0 15px 35px rgba(13, 148, 136, 0.5);
        color: #fff;
    }

    .ask-lila i {
        font-size: 1.4rem;
        animation: shake 2s infinite;
    }

    @keyframes shake {
        0%, 100% { transform: rotate(0); }
        10%, 30%, 50%, 70%, 90% { transform: rotate(-10deg); }
        20%, 40%, 60%, 80% { transform: rotate(10deg); }
    }

    .step-tracker {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        padding: 30px 40px;
        border-radius: 24px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.02);
        position: relative;
    }

    .tracker-line {
        position: absolute;
        top: 55px;
        left: 60px;
        right: 60px;
        height: 2px;
        background: #e2e8f0;
        z-index: 1;
    }

    .tp-step {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        width: 80px;
    }

    .tp-dot {
        width: 38px;
        height: 38px;
        background: #10b981;
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        font-weight: 700;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
    }

    .tp-dot.active {
        background: #fff;
        color: #10b981;
        border: 2px solid #10b981;
        box-shadow: 0 0 15px rgba(16, 185, 129, 0.3);
        animation: pulse-green 2s infinite;
    }

    @keyframes pulse-green {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .tp-label {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        text-align: center;
        line-height: 1.2;
        letter-spacing: 0.5px;
    }

    .tp-label.active {
        color: #10b981;
    }

    .tracker-main-title {
        text-align: center;
        margin-bottom: 40px;
    }

    .tracker-main-title h1 {
        font-size: 1.75rem;
        font-weight: 850;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .tracker-main-title p {
        color: #64748b;
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<div class="tracker-main-title animate__animated animate__fadeIn">
    <h1>Verify Your Identity</h1>
    <p>Complete the easy steps to start your investment journey</p>
</div>

<div class="step-tracker animate__animated animate__fadeIn">
    <div class="tracker-line"></div>
    
    @php
        $steps = [
            ['name' => "Mobile Verification", 'icon' => 'ti-check'],
            ['name' => "Email Verification", 'icon' => 'ti-check'],
            ['name' => "PAN Verification", 'icon' => 'ti-check'],
            ['name' => "Aadhaar Verification", 'icon' => 'ti-check'],
            ['name' => "Bank Account Verification", 'icon' => 'ti-check'],
            ['name' => "Trading Segments", 'icon' => 'ti-check'],
            ['name' => "Personal Details", 'icon' => 'ti-check'],
            ['name' => "Compliance Declarations", 'icon' => 'ti-check'],
            ['name' => "Nominee Details", 'icon' => 'ti-check'],
            ['name' => "Document Upload", 'icon' => 'ti-check'],
            ['name' => "e-Sign Verification", 'number' => 11]
        ];
    @endphp

    @foreach($steps as $index => $step)
        @php $isActive = ($index === 10); @endphp
        <div class="tp-step">
            <div class="tp-dot {{ $isActive ? 'active' : '' }}">
                @if($index < 10)
                    <i class="ti ti-check"></i>
                @else
                    {{ $step['number'] }}
                @endif
            </div>
            <div class="tp-label {{ $isActive ? 'active' : '' }}">
                {!! str_replace(' ', '<br>', $step['name']) !!}
            </div>
        </div>
    @endforeach
</div>

<div class="status-dashboard animate__animated animate__fadeIn">
    <div class="preview-app-container">
        <h3 class="main-title">Application Status</h3>
        <a href="{{ route('ekyc.form.view-esign', ['template_id' => 'combined']) }}" class="btn-preview-main preview-btn">
            <i class="ti ti-report-analytics"></i> Full Application Preview
        </a>
    </div>
    
    <div class="dashboard-header">
        <div class="user-avatar text-uppercase ">
            {{ substr($submission->pan_name ?? 'U', 0, 1) }}
        </div>
        <div>
            <div class="text-muted small fw-bold text-uppercase mb-1" style="letter-spacing: 1px;">Applicant Name</div> 
            <div class="h4 fw-bold text-primary mb-0">{{ $submission->pan_name ?? 'N/A' }}</div>
        </div>
    </div>

    <div class="row align-items-center">
        <div class="col-md-3 text-center border-end">
            <div class="text-muted small fw-bold text-uppercase mb-1">Started At</div>
            <div class="fw-bold">{{ $submission->created_at->format('d-m-Y') }}</div>
            <div class="small text-muted">{{ $submission->created_at->format('g:i A') }}</div>
        </div>
        <div class="col-md-3 text-center border-end">
            <div class="text-muted small fw-bold text-uppercase mb-1">Last Update</div>
            <div class="fw-bold">{{ $submission->updated_at->format('d-m-Y') }}</div>
            <div class="small text-muted">{{ $submission->updated_at->format('g:i A') }}</div>
        </div>
        <div class="col-md-3 text-center border-end">
            <div class="text-muted small fw-bold text-uppercase mb-1">Step Status</div>
            <div class="badge bg-soft-success text-success fw-bold p-2 px-3" style="background: #ecfdf5; border-radius: 10px;">
                <i class="ti ti-clock-play me-1"></i> IN PROGRESS
            </div>
        </div>
        <div class="col-md-3 text-center">
            <div class="text-muted small fw-bold text-uppercase mb-1">Current Task</div>
            <div class="fw-bold text-primary">E-Sign Verification</div>
        </div>
    </div>
</div>

<div class="docs-grid animate__animated animate__fadeInUp">
    @php
        $esignDocs = $submission->additional_data['esign_docs'] ?? [];
        $signableCount = 0;
    @endphp

    @foreach($pdfTemplates as $template)
        @if(($template['is_enabled'] ?? 'off') === 'on')
            @php 
                $signableCount++;
                $status = $esignDocs[$template['id']]['status'] ?? 'pending';
                $isSigned = ($status === 'signed');
                
                $iconClass = 'ti-file-description';
                if(str_contains(strtolower($template['name']), 'kra')) $iconClass = 'ti-id-badge';
                if(str_contains(strtolower($template['name']), 'ddpi')) $iconClass = 'ti-signature';
                if(str_contains(strtolower($template['name']), 'equity')) $iconClass = 'ti-chart-line';
            @endphp
            <div class="doc-card" id="card-{{ $template['id'] }}">
                @if($isSigned)
                    <div class="badge-signed" title="Signed"><i class="ti ti-check"></i></div>
                @endif
                
                <div class="doc-header">
                    <div class="doc-type-icon">
                        <i class="ti {{ $iconClass }}"></i>
                    </div>
                    <div class="doc-title">{{ $template['name'] }}</div>
                </div>
                
                <div class="doc-actions">
                    <a href="{{ route('ekyc.form.view-esign', ['template_id' => $template['id']]) }}" class="btn-preview preview-btn">
                        <i class="ti ti-eye"></i> View
                    </a>
                    
                    @if($isSigned)
                        <div class="btn-status signed">
                            <i class="ti ti-circle-check"></i> Signed
                        </div>
                    @else
                        <button class="btn-esign start-esign-btn" data-id="{{ $template['id'] }}">
                            <i class="ti ti-pencil"></i> Sign Now
                        </button>
                    @endif
                </div>
            </div>
        @endif
    @endforeach

    @if($signableCount === 0)
        <div class="alert alert-info w-100">No documents are currently pending signature.</div>
    @endif
</div>
                


<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            <div class="modal-header bg-light border-0 py-3">
                <h5 class="modal-title fw-bold" id="previewModalTitle">Document Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: 75vh; background: #f8fafc;">
                <div id="previewLoader" class="d-flex align-items-center justify-content-center h-100">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="text-muted fw-bold">Generating premium preview...</p>
                    </div>
                </div>
                <iframe id="previewFrame" src="" frameborder="0" style="width: 100%; height: 100%; display: none;"></iframe>
            </div>
            <div class="modal-footer border-0 bg-light py-3">
                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal" style="border-radius: 10px;">Close Preview</button>
            </div>
        </div>
    </div>
</div>

<a href="#" class="ask-lila animate__animated animate__bounceInUp animate__delay-1s">
    <i class="ti ti-message-dots"></i> Ask Lila
</a>
@endsection

@section('additional_js')
<script src="https://www.digio.in/sdk/v3/digio.js"></script>
<script>
    $(document).ready(function() {
        // Preview Modal Logic
        $('.preview-btn').on('click', function(e) {
            e.preventDefault();
            const btn = $(this);
            const url = btn.attr('href') + (btn.attr('href').includes('?') ? '&' : '?') + 'format=html';
            const title = btn.closest('.doc-card').find('.doc-title').text() || 'Application Form Preview';

            $('#previewModalTitle').text(title);
            $('#previewFrame').hide().attr('src', url);
            $('#previewLoader').show();
            $('#previewModal').modal('show');

            $('#previewFrame').off('load').on('load', function() {
                $('#previewLoader').hide();
                $(this).fadeIn();
            });
        });

        $('.start-esign-btn').on('click', function() {
            const btn = $(this);
            const templateId = btn.data('id');
            const originalHtml = btn.html();

            btn.prop('disabled', true).html('<i class="ti ti-loader-2 animate-spin"></i> Initializing...');

            $.ajax({
                url: '{{ route("ekyc.form.init-esign") }}',
                type: 'POST',
                data: { template_id: templateId },
                success: function(res) {
                    if (res.success) {
                        launchDigio(res.document_id, '{{ $submission->mobile_number }}', res.access_token);
                    } else {
                        Swal.fire('Error', res.message || 'Initialization failed', 'error');
                        btn.prop('disabled', false).html(originalHtml);
                    }
                },
                error: (xhr) => {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Failed to initialize e-Sign', 'error');
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });

        function launchDigio(docId, identifier, token) {
            const options = {
                environment: "{{ $digio_environment }}",
                callback: function(response) {
                    if (response.hasOwnProperty('error_code')) {
                        // User cancelled or error
                        console.log('Digio Error:', response);
                        $('.start-esign-btn[data-id]').prop('disabled', false).html('Esign');
                    } else {
                        confirmSign(docId);
                    }
                },
                is_iframe: true,
                logo_url: "https://stockology.com/logo.png"
            };

            const digio = new Digio(options);
            digio.init();
            digio.submit(docId, identifier, token);
        }

        function confirmSign(docId) {
            Swal.fire({
                title: 'Processing...',
                text: 'Saving your signed document',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            
            $.ajax({
                url: '{{ route("ekyc.form.confirm-esign") }}',
                type: 'POST',
                data: { document_id: docId },
                success: function(res) {
                    if (res.success) {
                        if (res.all_signed && res.redirect) {
                            window.location.href = res.redirect;
                        } else {
                            location.reload(); // Refresh to show signed status for this doc
                        }
                    } else {
                        Swal.fire('Error', res.message || 'Verification failed', 'error');
                        $('.start-esign-btn').prop('disabled', false).html('Esign');
                    }
                },
                error: () => {
                    Swal.fire('Error', 'An error occurred while confirming signature', 'error');
                    $('.start-esign-btn').prop('disabled', false).html('Esign');
                }
            });
        }
    });
</script>
@endsection
