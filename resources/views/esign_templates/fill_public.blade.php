<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>E-Sign Document - {{ $lead ? $lead->name : 'Portal' }}</title>
    
    <!-- CDNs for visual assets & styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-brand: #0F62FE;
            --secondary-brand: #393939;
            --accent-success: #198038;
            --accent-danger: #da1e28;
            --bg-editor: #f4f5f7;
            --border-color: #e2e8f0;
            --font-outfit: 'Outfit', sans-serif;
            --font-jakarta: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            font-family: var(--font-jakarta);
            background-color: #f8fafc;
            color: #334155;
            margin: 0;
            padding: 0;
        }

        /* Premium Public Header Styles */
        .public-portal-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            color: #ffffff;
            padding: 1.25rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .editor-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
            background: #ffffff;
        }

        /* Editor Toolbar Styling */
        .editor-top-toolbar {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            padding: 10px 20px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
        }

        .tool-group {
            display: flex;
            gap: 6px;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 8px;
        }

        .tool-btn {
            border: none;
            background: transparent;
            padding: 8px 16px;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 6px;
            color: #475569;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tool-btn i {
            font-size: 1.1rem;
        }

        .tool-btn:hover {
            background: rgba(15, 98, 254, 0.05);
            color: var(--primary-brand);
        }

        .tool-btn.active-tool {
            background: var(--primary-brand);
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(15, 98, 254, 0.25);
        }

        .tool-btn.active-tool i {
            color: #ffffff;
        }

        /* PDF Page Workspace Backdrop */
        .workspace-outer {
            background: #2b303b;
            border-radius: 16px;
            padding: 24px;
            box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.2);
            max-height: 800px;
            overflow: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .pdf-page-container {
            position: relative;
            margin-bottom: 30px;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.35);
            background: #ffffff;
            border-radius: 6px;
        }

        .page-label-indicator {
            position: absolute;
            top: -25px;
            left: 0;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .pdf-interaction-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: auto;
            z-index: 15;
        }

        /* PDF-Lib Editor Spawning Elements */
        .editor-element {
            position: absolute;
            box-sizing: border-box;
            cursor: move;
            z-index: 22;
        }

        .editor-element-delete-btn {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: var(--accent-danger);
            color: white;
            border: none;
            font-size: 10px;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 30;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .editor-element:hover .editor-element-delete-btn, 
        .editor-element.selected-element .editor-element-delete-btn {
            display: flex;
        }

        .editor-text-element {
            border: 1px dashed var(--primary-brand);
            background-color: rgba(15, 98, 254, 0.05);
            min-width: 60px;
            min-height: 20px;
            font-size: 11px;
            font-weight: 500;
            color: #000000;
            outline: none;
            padding: 2px 4px;
            word-break: break-all;
        }

        .editor-text-element:focus {
            border: 1px solid var(--primary-brand);
            background-color: #ffffff;
            box-shadow: 0 0 6px rgba(15, 98, 254, 0.2);
        }

        .editor-checkmark-element {
            border: 1px dashed var(--accent-success);
            background-color: rgba(25, 128, 56, 0.05);
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            color: var(--accent-success);
            font-size: 14px;
        }

        .editor-whiteout-element {
            border: 1px dashed #64748b;
            background-color: #ffffff;
            min-width: 40px;
            min-height: 16px;
            box-shadow: inset 0 0 2px rgba(0, 0, 0, 0.15);
        }

        .editor-resize-handle {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 8px;
            height: 8px;
            background: #64748b;
            cursor: se-resize;
            z-index: 25;
        }

        .visual-field-input-box {
            position: absolute;
            border: 1px dashed #64748b;
            background-color: rgba(100, 116, 139, 0.05);
            border-radius: 4px;
            z-index: 20;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        }

        .visual-field-input-box input {
            width: 100%;
            height: 100%;
            border: none;
            background: transparent;
            font-family: var(--font-jakarta);
            font-weight: 600;
            font-size: 10px;
            color: #000000;
            padding: 0 4px;
            outline: none;
        }

        .visual-field-input-box.signature-type {
            border: 2px dashed var(--accent-danger);
            background-color: rgba(218, 30, 40, 0.05);
            color: var(--accent-danger);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sticky-sidebar {
            position: -webkit-sticky;
            position: sticky;
            top: 100px;
            max-height: calc(100vh - 140px);
            overflow-y: auto;
        }

        /* Alert/Toast customization */
        .toast-notification {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 999999;
            background: #1e293b;
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            display: none;
            align-items: center;
            gap: 10px;
            animation: toastFadeInUp 0.3s ease-out;
        }

        @keyframes toastFadeInUp {
            from { transform: translateY(12px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>

    <!-- Header Portal Layout -->
    <header class="public-portal-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 42px; height: 42px; background: var(--primary-brand); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i class="ti ti-signature fs-4" style="color: white;"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold" style="font-family: var(--font-outfit); letter-spacing: -0.5px;">Stockology E-Sign Portal</h4>
                <small class="text-muted" style="color: #94a3b8 !important;">Secure Document Verification Gate</small>
            </div>
        </div>
        
        <div class="text-end">
            <span class="badge bg-light-primary text-primary px-3 py-2" style="border-radius: 8px; font-weight: 600; font-family: var(--font-outfit);">
                <i class="ti ti-lock me-1"></i> SSL SECURE 256-BIT
            </span>
        </div>
    </header>

    <main class="container-fluid py-4 px-4">
        
        <!-- Floating/Sticky Top Toolbar for Sejda PDF Editor Controls -->
        <div class="editor-top-toolbar">
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold text-dark"><i class="ti ti-settings text-primary me-1"></i>{{ __('Editing Tools') }}:</span>
                <div class="tool-group">
                    <button type="button" class="tool-btn active-tool" id="tool-select" onclick="setEditorTool('select')">
                        <i class="ti ti-mouse-pointer"></i> {{ __('Select') }}
                    </button>
                    <button type="button" class="tool-btn" id="tool-text" onclick="setEditorTool('text')">
                        <i class="ti ti-letter-a"></i> {{ __('Text') }}
                    </button>
                    <button type="button" class="tool-btn" id="tool-checkmark" onclick="setEditorTool('checkmark')">
                        <i class="ti ti-square-check"></i> {{ __('Checkmark') }}
                    </button>
                    <button type="button" class="tool-btn" id="tool-whiteout" onclick="setEditorTool('whiteout')">
                        <i class="ti ti-eraser"></i> {{ __('Whiteout') }}
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-light-danger px-3 py-2 border rounded" onclick="clearAllEdits()" style="font-weight: 600;">
                    <i class="ti ti-refresh me-1"></i> {{ __('Clear Edits') }}
                </button>
            </div>

            <!-- Zoom & Layout Indicators -->
            <div class="d-flex align-items-center gap-2">
                <button type="button" onclick="adjustZoom(-0.1)" class="btn btn-sm btn-outline-secondary px-2"><i class="ti ti-minus"></i></button>
                <span class="toolbar-badge" id="zoom-value-label" style="background:#f1f5f9; padding: 6px 12px; border-radius: 8px; font-weight:600; font-size: 0.85rem;">100%</span>
                <button type="button" onclick="adjustZoom(0.1)" class="btn btn-sm btn-outline-secondary px-2"><i class="ti ti-plus"></i></button>
                <button type="button" onclick="resetZoom()" class="btn btn-sm btn-outline-secondary px-2" title="Fit Width"><i class="ti ti-arrows-maximize"></i></button>
            </div>
        </div>

        <div class="row">
            <!-- Variables Control Panel (Sticky Sidebar) -->
            <div class="col-xl-4 col-md-5 sticky-sidebar">
                
                <!-- Signer Lead Details Context Card -->
                <div class="card editor-card mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="mb-0 text-dark fw-bold" style="font-family: var(--font-outfit);"><i class="ti ti-user-check text-primary me-2"></i>{{ __('Signer Verification Details') }}</h5>
                        <small class="text-muted">{{ __('Please review and verify your prefilled data below.') }}</small>
                    </div>
                    <div class="card-body pt-0">
                        <div class="badge bg-light-primary text-primary p-3 rounded-3 mb-3 w-100 border text-start">
                            <i class="ti ti-user me-1"></i> Full Name: <strong id="signer-display-name">{{ $lead ? $lead->name : __('New Client') }}</strong>
                        </div>
                        
                        <div class="text-xs text-muted mb-0">
                            * Confirm details. Fields can be verified and modified directly on the sidebar or by double-clicking on the PDF page canvas values.
                        </div>
                    </div>
                </div>
 
                @if($selectedTemplate)
                    <!-- Mapped Fields Form Card -->
                    <div class="card editor-card mb-4">
                        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark fw-bold" style="font-family: var(--font-outfit);"><i class="ti ti-edit text-primary me-2"></i>{{ __('Form Variables') }}</h5>
                            <span class="badge bg-light-primary text-primary">{{ $selectedTemplate->fields->count() }} Variables</span>
                        </div>
                        <div class="card-body pt-0">
                            <form id="pdf-variables-form" onsubmit="event.preventDefault(); downloadFilledPdf();">
                                <div class="row g-3" style="max-height: 350px; overflow-y: auto; padding-right: 5px;">
                                    @forelse($selectedTemplate->fields as $index => $field)
                                        @if($field->type === 'text')
                                            @php
                                                // Prefilling logic matching lead info custom fields mapping
                                                $prefill = '';
                                                $key = strtolower($field->field_key);
                                                if ($lead) {
                                                    if ($key === 'full_name' || $key === 'name') {
                                                        $prefill = $lead->name;
                                                    } elseif ($key === 'email') {
                                                        $prefill = $lead->email;
                                                    } elseif ($key === 'phone' || $key === 'mobile') {
                                                        $prefill = $lead->phone;
                                                    } elseif ($key === 'pan_number' || $key === 'pan') {
                                                        $prefill = $lead->pan_number;
                                                    } elseif ($key === 'aadhar_number' || $key === 'aadhar') {
                                                        $prefill = $lead->aadhar_number;
                                                    } elseif ($key === 'dp_id' || $key === 'client_code') {
                                                        $prefill = $lead->dp_id;
                                                    } else {
                                                        $normKey = preg_replace('/[^a-z0-9]/', '', $key);
                                                        if ($normKey === 'aadhar') $normKey = 'aadhaar';
                                                        if ($normKey === 'mobileno' || $normKey === 'mobilenumber') $normKey = 'phone';
                                                        
                                                        foreach($customFields as $cf) {
                                                            $cfKey = strtolower(str_replace(' ', '_', $cf->name));
                                                            $normCfName = preg_replace('/[^a-z0-9]/', '', strtolower($cf->name));
                                                            if ($cfKey === $key || 'custom_' . $cf->id === $key || $normCfName === $normKey) {
                                                                $prefill = $leadCustomFieldValues[$cf->id] ?? '';
                                                                break;
                                                            }
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <div class="col-12 form-group">
                                                <label class="form-label text-xs fw-bold text-muted uppercase mb-1">{{ $field->label }}</label>
                                                <input type="text" 
                                                       data-key="{{ $field->field_key }}" 
                                                       class="form-control border-2 variables-sidebar-input" 
                                                       value="{{ $prefill }}" 
                                                       style="border-radius: 8px; font-weight:600;"
                                                       oninput="syncSidebarToCanvas(this)">
                                            </div>
                                        @elseif($field->type === 'signature')
                                            <div class="col-12 form-group">
                                                <label class="form-label text-xs fw-bold text-muted uppercase mb-1">{{ $field->label }}</label>
                                                <div class="p-2 border rounded bg-light text-center text-xs text-danger font-style-semibold">
                                                    <i class="ti ti-signature"></i> Signature Coordinate Overlay (Page {{ $field->page_num }})
                                                </div>
                                            </div>
                                        @elseif($field->type === 'checkmark')
                                            <div class="col-12 form-group">
                                                <label class="form-label text-xs fw-bold text-muted uppercase mb-1">{{ $field->label }}</label>
                                                <div class="p-2 border rounded bg-light text-center text-xs text-success font-style-semibold">
                                                    <i class="ti ti-square-check"></i> Checkmark Coordinate Overlay (Page {{ $field->page_num }})
                                                </div>
                                            </div>
                                        @elseif($field->type === 'whiteout')
                                            <div class="col-12 form-group">
                                                <label class="form-label text-xs fw-bold text-muted uppercase mb-1">{{ $field->label }}</label>
                                                <div class="p-2 border rounded bg-light text-center text-xs text-secondary font-style-semibold">
                                                    <i class="ti ti-eraser"></i> Whiteout Coordinate Overlay (Page {{ $field->page_num }})
                                                </div>
                                            </div>
                                        @endif
                                    @empty
                                        <div class="col-12 text-center py-4 text-muted">
                                            No variables mapped on this template.
                                        </div>
                                    @endforelse
                                </div>

                                <div class="mt-4 pt-3 border-top g-2 d-flex flex-column gap-2">
                                    <button type="button" onclick="initiateMockEsign()" class="btn btn-success w-100 py-3" style="border-radius: 8px; font-weight:700; font-size:1rem; box-shadow: 0 4px 12px rgba(25, 128, 56, 0.2);">
                                        <i class="ti ti-edit-circle me-1"></i> {{ __('Initiate E-Sign Submission') }}
                                    </button>
                                    
                                    <button type="submit" class="btn btn-light border w-100 py-2" style="border-radius: 8px; font-weight:600; font-size:0.85rem;">
                                        <i class="ti ti-download me-1"></i> {{ __('Download Draft PDF') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Interactive Workspace (Sejda Style Canvas Editor) -->
            <div class="col-xl-8 col-md-7">
                @if($selectedTemplate)
                    <!-- Scrollable Workspace Outer Container -->
                    <div class="workspace-outer" id="pdf-workspace-container-outer">
                        <!-- Loader -->
                        <div id="pdf-loader" class="py-5 text-center text-white">
                            <div class="spinner-border text-light mb-3" role="status"></div>
                            <p style="font-weight: 600;">Streaming PDF form canvas...</p>
                        </div>
                    </div>
                @else
                    <div class="card editor-card py-5 text-center text-muted">
                        <i class="ti ti-alert-triangle fs-1 text-warning mb-3 d-block"></i>
                        <h5>{{ __('No Templates Available') }}</h5>
                        <p class="text-sm">{{ __('Please create an E-Sign template first to start editing.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <!-- Simulated E-Sign Modal -->
    <div class="modal fade" id="esignSimulationModal" tabindex="-1" aria-labelledby="esignSimulationLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px; border:none; box-shadow:0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header bg-dark text-white" style="border-radius:16px 16px 0 0;">
                    <h5 class="modal-title fw-bold" id="esignSimulationLabel" style="font-family:var(--font-outfit);"><i class="ti ti-fingerprint text-warning me-2"></i>{{ __('Digio Aadhaar E-Sign') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <i class="ti ti-shield-lock text-primary mb-3" style="font-size: 60px;"></i>
                    <h5 class="fw-bold mb-1">Aadhaar Authentication & OTP</h5>
                    <p class="text-muted text-sm px-3 mb-4">You are about to sign this document via mock Aadhaar gateway validation.</p>
                    
                    <div class="mb-4">
                        <div class="badge bg-light-success text-success p-2.5 rounded-3 mb-2 w-100 border text-start">
                            <i class="ti ti-user me-1"></i> Signer: <strong id="signer-display-name-modal">{{ $lead ? $lead->name : __('New Client') }}</strong>
                        </div>
                    </div>

                    <div class="form-group text-start mb-4">
                        <label class="form-label fw-bold text-xs uppercase text-muted">Aadhaar Virtual ID</label>
                        <input type="text" class="form-control text-center fw-bold fs-5" placeholder="XXXX - XXXX - XXXX" value="1234 5678 9012" disabled style="border-radius:8px;">
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-radius:0 0 16px 16px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" onclick="submitSimulationCallback()" class="btn btn-success px-4" style="border-radius:8px; font-weight:700;">Sign & Save PDF</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Toast Alert Element -->
    <div class="toast-notification" id="custom-toast">
        <i class="ti ti-info-circle text-primary fs-5"></i>
        <span id="toast-message">Notification message</span>
    </div>

    <!-- Scripts CDN Loading -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
    
    @if($selectedTemplate)
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        const pdfUrl = "{{ route('esign-templates.pdf.stream', $selectedTemplate->id) }}";
        const leadName = "{{ $lead ? $lead->name : 'New_Client' }}";
        const templateName = "{{ $selectedTemplate->name }}";
        const fieldPlacements = @json($selectedTemplate->fields);
        
        let pdfDoc = null;
        let currentZoom = 1.0;
        let originalPageWidth = 595.0;

        let activeTool = 'select';
        let customEdits = [];
        let customEditCounter = 0;

        let isDraggingElement = false;
        let isResizingElement = false;
        let dragStartX, dragStartY, dragStartLeft, dragStartTop, dragStartWidth, dragStartHeight;
        let activeDragElement = null;

        document.addEventListener("DOMContentLoaded", function() {
            loadPdfDocument();
            setupGlobalClickHandlers();
            
            // Dynamic Signer Name display updates as the client types
            $(document).on('input', '.variables-sidebar-input', function() {
                const key = $(this).data('key');
                if (key === 'full_name' || key === 'name') {
                    const val = $(this).val() || 'New Client';
                    $('#signer-display-name').text(val);
                    $('#signer-display-name-modal').text(val);
                }
            });
        });

        function show_toastr(type, message, category) {
            const toast = document.getElementById('custom-toast');
            const toastMsg = document.getElementById('toast-message');
            toastMsg.textContent = message;
            
            // Customize style based on toast type
            const icon = toast.querySelector('i');
            icon.className = 'ti fs-5';
            if (category === 'success') {
                icon.classList.add('ti-circle-check', 'text-success');
            } else if (category === 'error') {
                icon.classList.add('ti-circle-x', 'text-danger');
            } else {
                icon.classList.add('ti-info-circle', 'text-primary');
            }
            
            $(toast).fadeIn(200);
            setTimeout(() => { $(toast).fadeOut(400); }, 3500);
        }

        function setEditorTool(tool) {
            activeTool = tool;
            document.querySelectorAll('.tool-btn').forEach(btn => btn.classList.remove('active-tool'));
            document.getElementById(`tool-${tool}`).classList.add('active-tool');

            const containers = document.querySelectorAll('.pdf-interaction-overlay');
            containers.forEach(container => {
                if (tool === 'select') {
                    container.style.cursor = 'default';
                } else if (tool === 'text') {
                    container.style.cursor = 'text';
                } else if (tool === 'checkmark') {
                    container.style.cursor = 'cell';
                } else if (tool === 'whiteout') {
                    container.style.cursor = 'crosshair';
                }
            });
        }

        async function loadPdfDocument() {
            try {
                const loadingTask = pdfjsLib.getDocument(pdfUrl);
                pdfDoc = await loadingTask.promise;
                document.getElementById('pdf-loader').style.display = 'none';
                
                const containerWidth = document.getElementById('pdf-workspace-container-outer').clientWidth - 48;
                const firstPage = await pdfDoc.getPage(1);
                const defaultViewport = firstPage.getViewport({ scale: 1.0 });
                originalPageWidth = defaultViewport.width;
                
                currentZoom = containerWidth / originalPageWidth;
                currentZoom = Math.max(0.6, Math.min(currentZoom, 1.4));
                
                updateZoomUI();
                await renderAllPages();
            } catch (err) {
                console.error('Error loading PDF: ', err);
                document.getElementById('pdf-loader').innerHTML = `
                    <i class="ti ti-alert-triangle fs-1 text-danger"></i>
                    <p class="text-danger mt-2">Failed to load PDF template file.</p>
                `;
            }
        }

        async function renderAllPages() {
            const outer = document.getElementById('pdf-workspace-container-outer');
            document.querySelectorAll('.pdf-page-container').forEach(el => el.remove());

            for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
                const page = await pdfDoc.getPage(pageNum);
                const viewport = page.getViewport({ scale: currentZoom });

                const pageWrapper = document.createElement('div');
                pageWrapper.className = 'pdf-page-container';
                pageWrapper.id = `pdf-page-wrapper-${pageNum}`;
                pageWrapper.style.width = `${viewport.width}px`;
                pageWrapper.style.height = `${viewport.height}px`;

                const label = document.createElement('div');
                label.className = 'page-label-indicator';
                label.textContent = `Page ${pageNum} of ${pdfDoc.numPages}`;
                pageWrapper.appendChild(label);

                const canvas = document.createElement('canvas');
                canvas.id = `pdf-canvas-${pageNum}`;
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                canvas.style.display = 'block';

                const overlay = document.createElement('div');
                overlay.className = 'pdf-interaction-overlay';
                overlay.id = `pdf-overlay-${pageNum}`;
                overlay.addEventListener('mousedown', function(e) {
                    handleOverlayClick(e, pageNum);
                });

                pageWrapper.appendChild(canvas);
                pageWrapper.appendChild(overlay);
                outer.appendChild(pageWrapper);

                const context = canvas.getContext('2d');
                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };
                await page.render(renderContext).promise;
            }

            renderAllFieldsInputs();
            reRenderCustomElements();
        }

        function renderAllFieldsInputs() {
            fieldPlacements.forEach((field, index) => {
                const pageNum = field.page_num;
                const overlay = document.getElementById(`pdf-overlay-${pageNum}`);
                if (!overlay) return;

                const pageHeight = overlay.clientHeight;
                const boxLeft = field.x_coordinate * currentZoom;
                const boxWidth = field.width * currentZoom;
                const boxHeight = field.height * currentZoom;
                const boxTop = pageHeight - ((field.y_coordinate * currentZoom) + boxHeight);

                const box = document.createElement('div');
                box.className = `visual-field-input-box ${field.type === 'signature' ? 'signature-type' : ''}`;
                box.id = `premapped-field-${index}`;
                box.style.left = `${boxLeft}px`;
                box.style.top = `${boxTop}px`;
                box.style.width = `${boxWidth}px`;
                box.style.height = `${boxHeight}px`;

                if (field.type === 'text') {
                    const sidebarEl = document.querySelector(`.variables-sidebar-input[data-key="${field.field_key}"]`);
                    const val = sidebarEl ? sidebarEl.value : '';

                    box.innerHTML = `<input type="text" 
                                            data-key="${field.field_key}" 
                                            value="${val}" 
                                            placeholder="${field.label}"
                                            oninput="syncCanvasToSidebar(this)">`;
                } else if (field.type === 'checkmark') {
                    box.className += ' checkmark-type';
                    box.style.cursor = 'pointer';
                    box.style.border = '1px solid var(--accent-success)';
                    box.style.backgroundColor = 'rgba(25, 128, 56, 0.05)';
                    box.style.color = 'var(--accent-success)';
                    box.style.display = 'flex';
                    box.style.alignItems = 'center';
                    box.style.justifyContent = 'center';
                    box.style.fontSize = `${boxHeight - 4}px`;
                    box.style.fontWeight = '900';
                    box.innerHTML = '✓';
                    
                    box.setAttribute('data-checked', 'true');
                    box.onclick = function() {
                        const isChecked = box.getAttribute('data-checked') === 'true';
                        if (isChecked) {
                            box.setAttribute('data-checked', 'false');
                            box.innerHTML = '';
                            box.style.backgroundColor = 'rgba(0, 0, 0, 0.02)';
                        } else {
                            box.setAttribute('data-checked', 'true');
                            box.innerHTML = '✓';
                            box.style.backgroundColor = 'rgba(25, 128, 56, 0.05)';
                        }
                    };
                } else if (field.type === 'whiteout') {
                    box.style.border = '1px dashed #64748b';
                    box.style.backgroundColor = '#ffffff';
                    box.innerHTML = '';
                } else {
                    box.innerHTML = `<span><i class="ti ti-signature"></i> SIGN</span>`;
                    box.title = "Aadhaar Signature (Digio)";
                    box.onclick = function(e) {
                        e.stopPropagation();
                        initiateMockEsign();
                    }
                }

                overlay.appendChild(box);
            });
        }

        function handleOverlayClick(e, pageNum) {
            if (activeTool === 'select') return;
            if (!e.target.classList.contains('pdf-interaction-overlay')) return;

            const overlay = e.currentTarget;
            const rect = overlay.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const clickY = e.clientY - rect.top;

            if (activeTool === 'text') {
                spawnTextElement(pageNum, clickX, clickY, "Type text...", 120, 24);
            } else if (activeTool === 'checkmark') {
                spawnCheckmarkElement(pageNum, clickX, clickY, 20, 20);
            } else if (activeTool === 'whiteout') {
                spawnWhiteoutElement(pageNum, clickX, clickY, 80, 20);
            }
            setEditorTool('select');
        }

        function spawnTextElement(pageNum, x, y, value, width, height) {
            customEditCounter++;
            const editId = `custom-edit-${customEditCounter}`;
            const textEdit = {
                id: editId,
                type: 'text',
                page_num: pageNum,
                x: x / currentZoom,
                y: y / currentZoom,
                width: width / currentZoom,
                height: height / currentZoom,
                value: value
            };
            customEdits.push(textEdit);
            createDOMElement(textEdit);
        }

        function spawnCheckmarkElement(pageNum, x, y, width, height) {
            customEditCounter++;
            const editId = `custom-edit-${customEditCounter}`;
            const checkmarkEdit = {
                id: editId,
                type: 'checkmark',
                page_num: pageNum,
                x: x / currentZoom,
                y: y / currentZoom,
                width: width / currentZoom,
                height: height / currentZoom
            };
            customEdits.push(checkmarkEdit);
            createDOMElement(checkmarkEdit);
        }

        function spawnWhiteoutElement(pageNum, x, y, width, height) {
            customEditCounter++;
            const editId = `custom-edit-${customEditCounter}`;
            const whiteoutEdit = {
                id: editId,
                type: 'whiteout',
                page_num: pageNum,
                x: x / currentZoom,
                y: y / currentZoom,
                width: width / currentZoom,
                height: height / currentZoom
            };
            customEdits.push(whiteoutEdit);
            createDOMElement(whiteoutEdit);
        }

        function createDOMElement(editObj) {
            const overlay = document.getElementById(`pdf-overlay-${editObj.page_num}`);
            if (!overlay) return;

            const el = document.createElement('div');
            el.className = `editor-element`;
            el.id = editObj.id;
            el.style.left = `${editObj.x * currentZoom}px`;
            el.style.top = `${editObj.y * currentZoom}px`;
            el.style.width = `${editObj.width * currentZoom}px`;
            el.style.height = `${editObj.height * currentZoom}px`;

            if (editObj.type === 'text') {
                const inner = document.createElement('div');
                inner.className = 'editor-text-element';
                inner.contentEditable = 'true';
                inner.textContent = editObj.value;
                inner.style.width = '100%';
                inner.style.height = '100%';

                inner.addEventListener('blur', function() {
                    editObj.value = this.textContent;
                });
                inner.addEventListener('focus', function() {
                    el.classList.add('selected-element');
                });
                inner.addEventListener('blur', function() {
                    el.classList.remove('selected-element');
                });
                el.appendChild(inner);
            } else if (editObj.type === 'checkmark') {
                const inner = document.createElement('div');
                inner.className = 'editor-checkmark-element';
                inner.innerHTML = '✓';
                inner.style.width = '100%';
                inner.style.height = '100%';
                el.appendChild(inner);
            } else if (editObj.type === 'whiteout') {
                const inner = document.createElement('div');
                inner.className = 'editor-whiteout-element';
                inner.style.width = '100%';
                inner.style.height = '100%';
                el.appendChild(inner);

                const resizeHandle = document.createElement('div');
                resizeHandle.className = 'editor-resize-handle';
                el.appendChild(resizeHandle);
            }

            const delBtn = document.createElement('button');
            delBtn.className = 'editor-element-delete-btn';
            delBtn.innerHTML = '×';
            delBtn.onclick = function(e) {
                e.stopPropagation();
                removeCustomElement(editObj.id);
            };
            el.appendChild(delBtn);

            setupDraggableMechanics(el, editObj);
            overlay.appendChild(el);
        }

        function setupDraggableMechanics(el, editObj) {
            el.addEventListener('mousedown', function(e) {
                if (activeTool !== 'select') return;
                if (e.target.classList.contains('editor-element-delete-btn')) return;

                document.querySelectorAll('.editor-element').forEach(item => item.classList.remove('selected-element'));
                el.classList.add('selected-element');

                if (e.target.classList.contains('editor-resize-handle')) {
                    isResizingElement = true;
                    activeDragElement = el;
                    dragStartX = e.clientX;
                    dragStartY = e.clientY;
                    dragStartWidth = parseInt(el.style.width, 10);
                    dragStartHeight = parseInt(el.style.height, 10);
                    e.preventDefault();
                    e.stopPropagation();
                } else {
                    isDraggingElement = true;
                    activeDragElement = el;
                    dragStartX = e.clientX;
                    dragStartY = e.clientY;
                    dragStartLeft = parseInt(el.style.left, 10);
                    dragStartTop = parseInt(el.style.top, 10);
                    e.preventDefault();
                }

                document.addEventListener('mousemove', onElementMouseMove);
                document.addEventListener('mouseup', onElementMouseUp);
            });

            function onElementMouseMove(e) {
                if (!activeDragElement || activeDragElement.id !== editObj.id) return;
                const overlay = activeDragElement.parentElement;

                const deltaX = e.clientX - dragStartX;
                const deltaY = e.clientY - dragStartY;

                if (isDraggingElement) {
                    let newLeft = dragStartLeft + deltaX;
                    let newTop = dragStartTop + deltaY;

                    newLeft = Math.max(0, Math.min(newLeft, overlay.clientWidth - activeDragElement.clientWidth));
                    newTop = Math.max(0, Math.min(newTop, overlay.clientHeight - activeDragElement.clientHeight));

                    activeDragElement.style.left = `${newLeft}px`;
                    activeDragElement.style.top = `${newTop}px`;

                    editObj.x = newLeft / currentZoom;
                    editObj.y = newTop / currentZoom;
                }

                if (isResizingElement) {
                    let newWidth = dragStartWidth + deltaX;
                    let newHeight = dragStartHeight + deltaY;

                    newWidth = Math.max(15, Math.min(newWidth, overlay.clientWidth - parseInt(activeDragElement.style.left, 10)));
                    newHeight = Math.max(10, Math.min(newHeight, overlay.clientHeight - parseInt(activeDragElement.style.top, 10)));

                    activeDragElement.style.width = `${newWidth}px`;
                    activeDragElement.style.height = `${newHeight}px`;

                    editObj.width = newWidth / currentZoom;
                    editObj.height = newHeight / currentZoom;
                }
            }

            function onElementMouseUp() {
                isDraggingElement = false;
                isResizingElement = false;
                document.removeEventListener('mousemove', onElementMouseMove);
                document.removeEventListener('mouseup', onElementMouseUp);
            }
        }

        function reRenderCustomElements() {
            customEdits.forEach(edit => {
                createDOMElement(edit);
            });
        }

        function removeCustomElement(id) {
            const index = customEdits.findIndex(item => item.id === id);
            if (index !== -1) {
                customEdits.splice(index, 1);
            }
            const el = document.getElementById(id);
            if (el) el.remove();
        }

        function clearAllEdits() {
            customEdits = [];
            document.querySelectorAll('.editor-element').forEach(el => el.remove());
            show_toastr('Info', 'Custom visual elements cleared.', 'info');
        }

        function syncCanvasToSidebar(canvasInputEl) {
            const key = canvasInputEl.getAttribute('data-key');
            const val = canvasInputEl.value;
            const sidebarEl = document.querySelector(`.variables-sidebar-input[data-key="${key}"]`);
            if (sidebarEl) {
                sidebarEl.value = val;
            }
        }

        function syncSidebarToCanvas(sidebarInputEl) {
            const key = sidebarInputEl.getAttribute('data-key');
            const val = sidebarInputEl.value;
            const canvasInput = document.querySelector(`.visual-field-input-box input[data-key="${key}"]`);
            if (canvasInput) {
                canvasInput.value = val;
            }
        }

        function adjustZoom(delta) {
            currentZoom = Math.max(0.5, Math.min(currentZoom + delta, 1.8));
            updateZoomUI();
            renderAllPages();
        }

        function resetZoom() {
            const containerWidth = document.getElementById('pdf-workspace-container-outer').clientWidth - 48;
            currentZoom = containerWidth / originalPageWidth;
            currentZoom = Math.max(0.6, Math.min(currentZoom, 1.4));
            updateZoomUI();
            renderAllPages();
        }

        function updateZoomUI() {
            document.getElementById('zoom-value-label').textContent = `${Math.round(currentZoom * 100)}%`;
        }

        function setupGlobalClickHandlers() {
            document.addEventListener('mousedown', function(e) {
                if (!e.target.closest('.editor-element')) {
                    document.querySelectorAll('.editor-element').forEach(item => item.classList.remove('selected-element'));
                }
            });
        }

        async function downloadFilledPdf() {
            try {
                show_toastr('Info', 'Preparing client-side Sejda PDF compilation...', 'info');
                const { PDFDocument, rgb } = PDFLib;
                
                const existingPdfBytes = await fetch(pdfUrl).then(res => res.arrayBuffer());
                const pdfDoc = await PDFDocument.load(existingPdfBytes);
                const pages = pdfDoc.getPages();
                
                fieldPlacements.forEach((field, index) => {
                    const pageIndex = field.page_num - 1;
                    if (pageIndex < 0 || pageIndex >= pages.length) return;
                    const page = pages[pageIndex];
                    
                    const pdfX = parseFloat(field.x_coordinate);
                    const pdfY = parseFloat(field.y_coordinate);
                    const pdfW = parseFloat(field.width);
                    const pdfH = parseFloat(field.height);

                    if (field.type === 'text') {
                        const sidebarEl = document.querySelector(`.variables-sidebar-input[data-key="${field.field_key}"]`);
                        const textValue = sidebarEl ? sidebarEl.value : '';
                        
                        if (textValue) {
                            page.drawText(textValue, {
                                x: pdfX,
                                y: pdfY + 4,
                                size: 9,
                                color: rgb(0, 0, 0)
                            });
                        }
                    } else if (field.type === 'checkmark') {
                        const boxEl = document.getElementById(`premapped-field-${index}`);
                        const isChecked = boxEl ? boxEl.getAttribute('data-checked') === 'true' : true;
                        if (isChecked) {
                            page.drawText('✓', {
                                x: pdfX + 2,
                                y: pdfY + 2,
                                size: 13,
                                color: rgb(0.1, 0.5, 0.22)
                            });
                        }
                    } else if (field.type === 'whiteout') {
                        page.drawRectangle({
                            x: pdfX,
                            y: pdfY,
                            width: pdfW,
                            height: pdfH,
                            color: rgb(1, 1, 1),
                            filled: true
                        });
                    }
                });

                customEdits.forEach((edit) => {
                    const pageIndex = edit.page_num - 1;
                    if (pageIndex < 0 || pageIndex >= pages.length) return;
                    const page = pages[pageIndex];
                    const pageHeightPoints = page.getHeight();

                    const pdfX = edit.x;
                    const pdfY = pageHeightPoints - (edit.y + edit.height);

                    if (edit.type === 'text' && edit.value) {
                        page.drawText(edit.value, {
                            x: pdfX,
                            y: pdfY + 4,
                            size: 9,
                            color: rgb(0, 0, 0)
                        });
                    } else if (edit.type === 'checkmark') {
                        page.drawText('✓', {
                            x: pdfX + 2,
                            y: pdfY + 2,
                            size: 13,
                            color: rgb(0.1, 0.5, 0.22)
                        });
                    } else if (edit.type === 'whiteout') {
                        page.drawRectangle({
                            x: pdfX,
                            y: pdfY,
                            width: edit.width,
                            height: edit.height,
                            color: rgb(1, 1, 1),
                            filled: true
                        });
                    }
                });
                
                const pdfBytes = await pdfDoc.save();
                const blob = new Blob([pdfBytes], { type: "application/pdf" });
                
                const downloadLink = document.createElement('a');
                downloadLink.href = window.URL.createObjectURL(blob);
                downloadLink.download = `Draft_Lead_${leadName.replace(/\s+/g, '_')}_${templateName.replace(/\s+/g, '_')}.pdf`;
                downloadLink.click();
                
                show_toastr('Success', 'Draft PDF downloaded successfully!', 'success');
            } catch (err) {
                console.error('Error compiling PDF: ', err);
                show_toastr('Error', 'Failed to compile PDF: ' + err.message, 'error');
            }
        }

        function initiateMockEsign() {
            const modal = new bootstrap.Modal(document.getElementById('esignSimulationModal'));
            modal.show();
        }

        async function submitSimulationCallback() {
            const modalEl = document.getElementById('esignSimulationModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            
            modal.hide();
            show_toastr('Info', 'Compiling and saving authenticated document to CRM...', 'info');

            try {
                const { PDFDocument, rgb } = PDFLib;
                const existingPdfBytes = await fetch(pdfUrl).then(res => res.arrayBuffer());
                const compiledPdfDoc = await PDFDocument.load(existingPdfBytes);
                const pages = compiledPdfDoc.getPages();
                
                fieldPlacements.forEach((field, index) => {
                    const pageIndex = field.page_num - 1;
                    if (pageIndex < 0 || pageIndex >= pages.length) return;
                    const page = pages[pageIndex];
                    
                    const pdfX = parseFloat(field.x_coordinate);
                    const pdfY = parseFloat(field.y_coordinate);
                    const pdfW = parseFloat(field.width);
                    const pdfH = parseFloat(field.height);

                    if (field.type === 'text') {
                        const sidebarEl = document.querySelector(`.variables-sidebar-input[data-key="${field.field_key}"]`);
                        const textValue = sidebarEl ? sidebarEl.value : '';
                        
                        if (textValue) {
                            page.drawText(textValue, {
                                x: pdfX,
                                y: pdfY + 4,
                                size: 9,
                                color: rgb(0, 0, 0)
                            });
                        }
                    } else if (field.type === 'checkmark') {
                        const boxEl = document.getElementById(`premapped-field-${index}`);
                        const isChecked = boxEl ? boxEl.getAttribute('data-checked') === 'true' : true;
                        if (isChecked) {
                            page.drawText('✓', {
                                x: pdfX + 2,
                                y: pdfY + 2,
                                size: 13,
                                color: rgb(0.1, 0.5, 0.22)
                            });
                        }
                    } else if (field.type === 'whiteout') {
                        page.drawRectangle({
                            x: pdfX,
                            y: pdfY,
                            width: pdfW,
                            height: pdfH,
                            color: rgb(1, 1, 1),
                            filled: true
                        });
                    }
                });

                customEdits.forEach((edit) => {
                    const pageIndex = edit.page_num - 1;
                    if (pageIndex < 0 || pageIndex >= pages.length) return;
                    const page = pages[pageIndex];
                    const pageHeightPoints = page.getHeight();

                    const pdfX = edit.x;
                    const pdfY = pageHeightPoints - (edit.y + edit.height);

                    if (edit.type === 'text' && edit.value) {
                        page.drawText(edit.value, {
                            x: pdfX,
                            y: pdfY + 4,
                            size: 9,
                            color: rgb(0, 0, 0)
                        });
                    } else if (edit.type === 'checkmark') {
                        page.drawText('✓', {
                            x: pdfX + 2,
                            y: pdfY + 2,
                            size: 13,
                            color: rgb(0.1, 0.5, 0.22)
                        });
                    } else if (edit.type === 'whiteout') {
                        page.drawRectangle({
                            x: pdfX,
                            y: pdfY,
                            width: edit.width,
                            height: edit.height,
                            color: rgb(1, 1, 1),
                            filled: true
                        });
                    }
                });
                
                const pdfBytes = await compiledPdfDoc.save();
                const blob = new Blob([pdfBytes], { type: "application/pdf" });
                
                const formData = new FormData();
                formData.append('lead_id', "{{ $lead ? $lead->id : '' }}");
                formData.append('signed_pdf_file', blob, 'signed_kyc_form.pdf');
                
                document.querySelectorAll('.variables-sidebar-input').forEach(input => {
                    const key = input.getAttribute('data-key');
                    if (key) {
                        formData.append('variables[' + key + ']', input.value);
                    }
                });
                
                $.ajax({
                    url: "/api/esign/callback",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            show_toastr('Success', 'E-Sign complete! Document successfully signed.', 'success');
                            
                            // Visual success transition
                            setTimeout(() => {
                                document.body.innerHTML = `
                                    <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100vh; background:#f4f5f7; font-family:'Plus Jakarta Sans',sans-serif; text-align:center;">
                                        <div style="background:white; padding:3.5rem 2.5rem; border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,0.04); max-width:500px; width:90%; border:1px solid #e2e8f0;">
                                            <div style="width:72px; height:72px; background:#e6f4ea; color:#137333; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem auto; font-size:2.5rem;">
                                                ✓
                                            </div>
                                            <h3 style="font-weight:800; color:#0f172a; margin-bottom:0.75rem; font-family: var(--font-outfit); letter-spacing:-0.5px;">E-Sign Verification Completed!</h3>
                                            <p style="color:#64748b; font-size:0.95rem; line-height:1.6; margin-bottom:2rem;">Your KYC document has been successfully compiled, digitally signed, and uploaded to the CRM backoffice system.</p>
                                            <div style="font-size:0.8rem; color:#94a3b8; border-top:1px solid #f1f5f9; padding-top:1.5rem;">You may now safely close this browser window. Thank you.</div>
                                        </div>
                                    </div>
                                `;
                            }, 1200);
                        } else {
                            show_toastr('Error', res.message || 'E-Sign callback sync failed.', 'error');
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        show_toastr('Error', 'Server connection failure. Signed document not saved.', 'error');
                    }
                });
            } catch (err) {
                console.error('Error compiling PDF: ', err);
                show_toastr('Error', 'Failed to compile and save PDF: ' + err.message, 'error');
            }
        }
    </script>
    @endif
</body>
</html>
