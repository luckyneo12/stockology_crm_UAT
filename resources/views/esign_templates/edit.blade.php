@extends('layouts.main')

@section('page-title')
    {{ __('Visual Coordinate Mapper') }}
@endsection

@section('page-breadcrumb')
    {{ __('Sales') }},{{ __('E-Sign Templates') }},{{ __('Visual Mapper') }}
@endsection

@section('content')
<!-- Custom styles for Premium UI/UX visual design -->
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
    }

    .editor-card {
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
        background: #ffffff;
    }

    /* Variable Pill Grid Styles */
    .toolbox-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .toolbox-pill {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 14px 10px;
        border-radius: 12px;
        background: #f8fafc;
        border: 2px solid #f1f5f9;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-align: center;
        user-select: none;
    }

    .toolbox-pill:hover {
        transform: translateY(-2px);
        background: #ffffff;
        box-shadow: 0 8px 16px rgba(15, 98, 254, 0.08);
        border-color: var(--primary-brand);
    }

    .toolbox-pill i {
        font-size: 20px;
        margin-bottom: 6px;
        color: #64748b;
        transition: color 0.2s;
    }

    .toolbox-pill span {
        font-size: 11px;
        font-weight: 600;
        color: #334155;
    }

    .toolbox-pill:hover i {
        color: var(--primary-brand);
    }

    /* Active Variable Colors */
    .toolbox-pill[data-key="full_name"] { border-left: 4px solid #3b82f6; }
    .toolbox-pill[data-key="pan_number"] { border-left: 4px solid #0d9488; }
    .toolbox-pill[data-key="phone"] { border-left: 4px solid #f97316; }
    .toolbox-pill[data-key="email"] { border-left: 4px solid #8b5cf6; }
    .toolbox-pill[data-key="aadhar_number"] { border-left: 4px solid #6366f1; }
    .toolbox-pill[data-key="dob"] { border-left: 4px solid #ec4899; }
    .toolbox-pill[data-key="gender"] { border-left: 4px solid #06b6d4; }
    .toolbox-pill[data-key="marital_status"] { border-left: 4px solid #e11d48; }
    .toolbox-pill[data-key="father_name"] { border-left: 4px solid #8b5cf6; }
    .toolbox-pill[data-key="mother_name"] { border-left: 4px solid #ec4899; }
    .toolbox-pill[data-key="client_code"] { border-left: 4px solid #64748b; }
    
    .toolbox-pill[data-key^="address_"] { border-left: 4px solid #f59e0b; }
    .toolbox-pill[data-key^="bank_"] { border-left: 4px solid #10b981; }
    .toolbox-pill[data-key="occupation"] { border-left: 4px solid #10b981; }
    .toolbox-pill[data-key="annual_income"] { border-left: 4px solid #10b981; }
    .toolbox-pill[data-key="networth"] { border-left: 4px solid #10b981; }
    .toolbox-pill[data-key="networth_date"] { border-left: 4px solid #10b981; }
    
    .toolbox-pill[data-key^="nominee_"] { border-left: 4px solid #8b5cf6; }
    .toolbox-pill[data-key="signature"] { border-left: 4px solid var(--accent-danger); }

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
        transition: transform 0.2s;
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

    /* Draggable Overlays */
    .visual-field-box {
        position: absolute;
        border: 2px solid var(--primary-brand);
        background-color: rgba(15, 98, 254, 0.12);
        border-radius: 6px;
        color: var(--primary-brand);
        font-family: var(--font-jakarta);
        font-weight: 700;
        font-size: 11px;
        padding: 4px 8px;
        cursor: move;
        user-select: none;
        z-index: 20;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 12px rgba(15, 98, 254, 0.15);
        transition: border-color 0.15s, background-color 0.15s, box-shadow 0.15s;
    }

    .visual-field-box:hover, .visual-field-box.active-box {
        background-color: rgba(15, 98, 254, 0.22);
        box-shadow: 0 0 0 3px rgba(15, 98, 254, 0.35), 0 8px 20px rgba(0,0,0,0.15);
        z-index: 25;
    }

    .visual-field-box.signature-type {
        border-color: var(--accent-danger);
        background-color: rgba(218, 30, 40, 0.12);
        color: var(--accent-danger);
        box-shadow: 0 4px 12px rgba(218, 30, 40, 0.15);
    }

    .visual-field-box.signature-type:hover, .visual-field-box.signature-type.active-box {
        background-color: rgba(218, 30, 40, 0.22);
        box-shadow: 0 0 0 3px rgba(218, 30, 40, 0.35), 0 8px 20px rgba(0,0,0,0.15);
    }

    .visual-field-box.whiteout-type {
        border-color: #64748b;
        background-color: #ffffff;
        color: #000000;
        box-shadow: 0 4px 12px rgba(100, 116, 139, 0.15);
    }

    .visual-field-box.checkmark-type {
        border-color: var(--accent-success);
        background-color: rgba(25, 128, 56, 0.05);
        color: var(--accent-success);
        font-size: 14px;
        font-weight: 900;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .visual-field-box .box-label {
        font-weight: 700;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    .visual-field-box .box-close-btn {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.08);
        border: none;
        color: inherit;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .visual-field-box .box-close-btn:hover {
        background: currentColor;
        color: #ffffff;
    }

    /* Small float indicator inside boxes */
    .box-coords-tooltip {
        position: absolute;
        bottom: -20px;
        left: 50%;
        transform: translateX(-50%);
        background: #1e293b;
        color: #ffffff;
        font-size: 9px;
        padding: 2px 6px;
        border-radius: 4px;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.2s;
        white-space: nowrap;
        z-index: 30;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    .visual-field-box:hover .box-coords-tooltip, .visual-field-box.active-box .box-coords-tooltip {
        opacity: 1;
    }

    /* Workspace Toolbar (Zoom, Fit, Actions) */
    .workspace-toolbar {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        display: flex;
        gap: 8px;
        padding: 8px 16px;
        margin-bottom: 16px;
        align-items: center;
        width: 100%;
        justify-content: space-between;
    }

    .toolbar-btn {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .toolbar-btn:hover {
        background: #f1f5f9;
        color: var(--primary-brand);
        border-color: var(--primary-brand);
    }

    .toolbar-badge {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        background: #f1f5f9;
    }
    .sticky-sidebar {
        position: -webkit-sticky;
        position: sticky;
        top: 20px;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
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

    .resize-handle {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 8px;
        height: 8px;
        background: #64748b;
        cursor: se-resize;
        z-index: 25;
    }
</style>

<div class="row">
    <!-- Variables Control Panel (Sticky) -->
    <div class="col-xl-4 col-md-5 sticky-sidebar">
        <!-- Template Settings Card (Rename & Replace PDF) -->
        <div class="card editor-card mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 text-dark fw-bold" style="font-family: var(--font-outfit);"><i class="ti ti-file-text text-primary me-2"></i>{{ __('Template Settings') }}</h5>
                <small class="text-muted">{{ __('Manage the template details or replace the PDF document.') }}</small>
            </div>
            <div class="card-body pt-0">
                <!-- Rename Template Name -->
                <form action="{{ route('esign-templates.update', $template->id) }}" method="POST" class="mb-3">
                    @csrf
                    @method('PUT')
                    <label class="form-label fw-bold text-muted text-xs uppercase">{{ __('Template Name') }}</label>
                    <div class="input-group">
                        <input type="text" name="name" class="form-control form-control-sm border-2" value="{{ $template->name }}" style="border-radius: 8px 0 0 8px;" required>
                        <button type="submit" class="btn btn-sm btn-primary" style="border-radius: 0 8px 8px 0;"><i class="ti ti-check"></i></button>
                    </div>
                </form>

                <!-- Replace/Re-upload PDF Document -->
                <form action="{{ route('esign-templates.update', $template->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <label class="form-label fw-bold text-muted text-xs uppercase">{{ __('Replace Source PDF') }}</label>
                    <div class="input-group">
                        <input type="file" name="pdf_file" class="form-control form-control-sm border-2" accept="application/pdf" style="border-radius: 8px 0 0 8px;" required>
                        <button type="submit" class="btn btn-sm btn-danger" style="border-radius: 0 8px 8px 0;" title="Re-upload PDF"><i class="ti ti-upload"></i></button>
                    </div>
                    <small class="text-muted text-xxs mt-1 d-block">{{ __('Re-uploading preserves all your mapped variables coordinates.') }}</small>
                </form>
            </div>
        </div>

        <!-- Variable Toolbox Grid -->
        <div class="card editor-card mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 text-dark fw-bold" style="font-family: var(--font-outfit);"><i class="ti ti-grid-dots text-primary me-2"></i>{{ __('Variables Grid') }}</h5>
                <small class="text-muted">{{ __('Click a variable block to place it on the PDF canvas.') }}</small>
            </div>
            <div class="card-body pt-0">
                <!-- Select Placement Page -->
                <div class="form-group mb-3">
                    <label for="new_page_num" class="form-label fw-bold text-muted text-xs uppercase">{{ __('Target Page') }}</label>
                    <select id="new_page_num" class="form-select border-2" style="border-radius: 8px; font-weight:600;">
                        <option value="1">Page 1</option>
                    </select>
                </div>

                <div class="accordion accordion-flush" id="variablesAccordion">
                    <!-- Category: Personal Info -->
                    <div class="accordion-item border-0 mb-2">
                        <h2 class="accordion-header" id="headingPersonal">
                            <button class="accordion-button collapsed py-2 px-3 bg-light rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePersonal" aria-expanded="false" aria-controls="collapsePersonal" style="font-weight: 600; font-size: 0.85rem;">
                                <i class="ti ti-user text-primary me-2"></i> Personal Details
                            </button>
                        </h2>
                        <div id="collapsePersonal" class="accordion-collapse collapse" aria-labelledby="headingPersonal" data-bs-parent="#variablesAccordion">
                            <div class="accordion-body p-2">
                                <div class="toolbox-grid">
                                    <div class="toolbox-pill" data-key="full_name" data-label="Full Name" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-user"></i>
                                        <span>Full Name</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="pan_number" data-label="PAN Number" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-credit-card"></i>
                                        <span>PAN Number</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="aadhar_number" data-label="Aadhaar Number" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-id-badge"></i>
                                        <span>Aadhaar No</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="dob" data-label="Date of Birth" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-calendar"></i>
                                        <span>DOB</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="gender" data-label="Gender" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-gender-transgender"></i>
                                        <span>Gender</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="marital_status" data-label="Marital Status" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-heart"></i>
                                        <span>Marital Status</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="phone" data-label="Phone Number" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-phone"></i>
                                        <span>Phone No</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="email" data-label="Email" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-mail"></i>
                                        <span>Email ID</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="father_name" data-label="Father's Name" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-users"></i>
                                        <span>Father's Name</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="mother_name" data-label="Mother's Name" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-users"></i>
                                        <span>Mother's Name</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="client_code" data-label="Client Code" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-hash"></i>
                                        <span>Client Code</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category: Address -->
                    <div class="accordion-item border-0 mb-2">
                        <h2 class="accordion-header" id="headingAddress">
                            <button class="accordion-button collapsed py-2 px-3 bg-light rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAddress" aria-expanded="false" aria-controls="collapseAddress" style="font-weight: 600; font-size: 0.85rem;">
                                <i class="ti ti-map-pin text-primary me-2"></i> Address Details
                            </button>
                        </h2>
                        <div id="collapseAddress" class="accordion-collapse collapse" aria-labelledby="headingAddress" data-bs-parent="#variablesAccordion">
                            <div class="accordion-body p-2">
                                <div class="toolbox-grid">
                                    <div class="toolbox-pill" data-key="address_line_1" data-label="Address Line 1" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-map"></i>
                                        <span>Address Line 1</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="address_line_2" data-label="Address Line 2" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-map"></i>
                                        <span>Address Line 2</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="address_line_3" data-label="Address Line 3" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-map"></i>
                                        <span>Address Line 3</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="address_city" data-label="City" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-building"></i>
                                        <span>City</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="address_pincode" data-label="Pincode" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-number"></i>
                                        <span>Pincode</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="address_state" data-label="State" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-map-2"></i>
                                        <span>State</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category: Bank & Financials -->
                    <div class="accordion-item border-0 mb-2">
                        <h2 class="accordion-header" id="headingBank">
                            <button class="accordion-button collapsed py-2 px-3 bg-light rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBank" aria-expanded="false" aria-controls="collapseBank" style="font-weight: 600; font-size: 0.85rem;">
                                <i class="ti ti-building-bank text-primary me-2"></i> Bank & Financials
                            </button>
                        </h2>
                        <div id="collapseBank" class="accordion-collapse collapse" aria-labelledby="headingBank" data-bs-parent="#variablesAccordion">
                            <div class="accordion-body p-2">
                                <div class="toolbox-grid">
                                    <div class="toolbox-pill" data-key="bank_account_number" data-label="Account Number" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-hash"></i>
                                        <span>Account No</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="bank_ifsc" data-label="IFSC Code" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-code"></i>
                                        <span>IFSC Code</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="bank_name" data-label="Bank Name" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-building-bank"></i>
                                        <span>Bank Name</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="occupation" data-label="Occupation" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-briefcase"></i>
                                        <span>Occupation</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="annual_income" data-label="Annual Income" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-coin"></i>
                                        <span>Annual Income</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="networth" data-label="Net Worth" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-wallet"></i>
                                        <span>Net Worth</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="networth_date" data-label="Net Worth Date" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-calendar-event"></i>
                                        <span>Net Worth Date</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category: Nominee Details -->
                    <div class="accordion-item border-0 mb-2">
                        <h2 class="accordion-header" id="headingNominee">
                            <button class="accordion-button collapsed py-2 px-3 bg-light rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNominee" aria-expanded="false" aria-controls="collapseNominee" style="font-weight: 600; font-size: 0.85rem;">
                                <i class="ti ti-user-plus text-primary me-2"></i> Nominee Details
                            </button>
                        </h2>
                        <div id="collapseNominee" class="accordion-collapse collapse" aria-labelledby="headingNominee" data-bs-parent="#variablesAccordion">
                            <div class="accordion-body p-2">
                                <div class="toolbox-grid">
                                    <div class="toolbox-pill" data-key="nominee_name" data-label="Nominee Name" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-user"></i>
                                        <span>Nominee Name</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="nominee_relation" data-label="Nominee Relation" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-hierarchy-2"></i>
                                        <span>Relation</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="nominee_dob" data-label="Nominee DOB" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-calendar"></i>
                                        <span>Nominee DOB</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="nominee_pan" data-label="Nominee PAN" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-credit-card"></i>
                                        <span>Nominee PAN</span>
                                    </div>
                                    <div class="toolbox-pill" data-key="nominee_share" data-label="Nominee Share %" data-type="text" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-percentage"></i>
                                        <span>Share %</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category: E-Sign & Tools -->
                    <div class="accordion-item border-0 mb-2">
                        <h2 class="accordion-header" id="headingEsign">
                            <button class="accordion-button collapsed py-2 px-3 bg-light rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEsign" aria-expanded="false" aria-controls="collapseEsign" style="font-weight: 600; font-size: 0.85rem;">
                                <i class="ti ti-edit text-primary me-2"></i> E-Sign & Custom Tools
                            </button>
                        </h2>
                        <div id="collapseEsign" class="accordion-collapse collapse show" aria-labelledby="headingEsign" data-bs-parent="#variablesAccordion">
                            <div class="accordion-body p-2">
                                <div class="toolbox-grid">
                                    <div class="toolbox-pill" data-key="signature" data-label="Signature Box" data-type="signature" onclick="placeFieldFromPill(this)">
                                        <i class="ti ti-edit"></i>
                                        <span>Signature</span>
                                    </div>
                                </div>
                                <div class="border-top mt-3 pt-3" id="custom-variable-creator-panel">
                                    <h6 class="text-xs fw-bold text-muted mb-2"><i class="ti ti-circle-plus text-primary me-1"></i>Create Custom Variable Pill</h6>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input type="text" id="new-custom-label" class="form-control form-control-sm border-2" placeholder="Label (e.g. City)" style="font-size: 0.75rem;">
                                        </div>
                                        <div class="col-6">
                                            <input type="text" id="new-custom-key" class="form-control form-control-sm border-2" placeholder="Key (e.g. city)" style="font-size: 0.75rem;">
                                        </div>
                                        <div class="col-12">
                                            <button type="button" class="btn btn-sm btn-primary w-100 py-1.5" onclick="createNewCustomPill()" style="border-radius: 6px; font-weight:600; font-size: 0.8rem;">
                                                <i class="ti ti-plus me-1"></i> Create Custom Pill
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="toolbox-grid mt-3" id="created-custom-pills-container">
                                    <!-- Created custom variables will render here dynamically -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Box Details Inspector -->
        <div class="card editor-card mb-4" id="details-inspector-card" style="display:none;">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark fw-bold" style="font-family: var(--font-outfit);"><i class="ti ti-settings text-primary me-2"></i>{{ __('Variables Inspector') }}</h5>
                <span class="badge bg-light-primary text-primary" id="inspector-field-key">key</span>
            </div>
            <div class="card-body pt-0">
                <div class="row g-2">
                    <div class="col-12 form-group mb-2">
                        <label class="form-label text-xxs uppercase text-muted fw-bold">{{ __('Variable Key (slug)') }}</label>
                        <input type="text" id="insp-key" class="form-control form-control-sm" oninput="updateSelectedFromInspector()">
                        <small class="text-muted text-xxs">e.g. <code>father_name</code>, <code>dob</code>, <code>city</code></small>
                    </div>
                    <div class="col-12 form-group mb-2">
                        <label class="form-label text-xxs uppercase text-muted fw-bold">{{ __('Display Label') }}</label>
                        <input type="text" id="insp-label" class="form-control form-control-sm" oninput="updateSelectedFromInspector()">
                    </div>
                    <div class="col-6 form-group">
                        <label class="form-label text-xxs uppercase text-muted">{{ __('X Coordinate') }}</label>
                        <input type="number" id="insp-x" class="form-control form-control-sm" oninput="updateSelectedFromInspector()">
                    </div>
                    <div class="col-6 form-group">
                        <label class="form-label text-xxs uppercase text-muted">{{ __('Y Coordinate') }}</label>
                        <input type="number" id="insp-y" class="form-control form-control-sm" oninput="updateSelectedFromInspector()">
                    </div>
                    <div class="col-6 form-group">
                        <label class="form-label text-xxs uppercase text-muted">{{ __('Width') }}</label>
                        <input type="number" id="insp-w" class="form-control form-control-sm" oninput="updateSelectedFromInspector()">
                    </div>
                    <div class="col-6 form-group">
                        <label class="form-label text-xxs uppercase text-muted">{{ __('Height') }}</label>
                        <input type="number" id="insp-h" class="form-control form-control-sm" oninput="updateSelectedFromInspector()">
                    </div>
                </div>
                <div class="text-xxs text-muted mt-2 border-top pt-2">
                    <i class="ti ti-keyboard text-primary"></i> Keyboard Tip: Use <strong>Arrow Keys</strong> to nudge selected box 1px (Hold <strong>Shift</strong> to nudge 10px).
                </div>
            </div>
        </div>

        <!-- Active Placements Checklist -->
        <div class="card editor-card mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark fw-bold" style="font-family: var(--font-outfit);"><i class="ti ti-list text-primary me-2"></i>{{ __('Active Mappings') }}</h5>
                <span class="badge bg-light-success text-success" id="mapping-count-badge">0 Mapped</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush overflow-auto" id="active-mappings-list" style="max-height: 200px;">
                    <!-- Filled dynamically -->
                </div>
            </div>
            <div class="card-footer bg-white border-0 py-3 d-flex flex-column gap-2">
                <button type="button" onclick="autoMapCdslFields()" class="btn btn-primary w-100 py-2.5" style="border-radius: 8px; font-weight:700; font-size:0.95rem; box-shadow: 0 4px 12px rgba(15, 98, 254, 0.2);">
                    <i class="ti ti-wand me-1"></i> {{ __('Auto-Map KYC Fields') }}
                </button>
                <button type="button" onclick="saveAllCoordinates()" class="btn btn-success w-100 py-2.5" style="border-radius: 8px; font-weight:700; font-size:0.95rem; box-shadow: 0 4px 12px rgba(25, 128, 56, 0.2);">
                    <i class="ti ti-device-floppy me-1"></i> {{ __('Save Visual Placements') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Interactive Workspace column -->
    <div class="col-xl-8 col-md-7">
        <!-- Workspace Toolbar -->
        <div class="workspace-toolbar flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <span class="toolbar-badge"><i class="ti ti-file-description text-danger me-1"></i>{{ $template->name }}</span>
                <span class="toolbar-badge d-none d-md-inline-block" id="selected-indicator-text">{{ __('No Box Selected') }}</span>
            </div>

            <!-- Custom Template Editing Tools -->
            <div class="d-flex align-items-center gap-1 my-1">
                <div class="tool-group px-2 py-1 bg-light rounded d-flex gap-1">
                    <button type="button" class="btn btn-xs btn-primary py-1" id="tool-select" onclick="setEditorTool('select')"><i class="ti ti-mouse-pointer"></i> Select</button>
                    <button type="button" class="btn btn-xs btn-light py-1" id="tool-text" onclick="setEditorTool('text')"><i class="ti ti-letter-a"></i> Text</button>
                    <button type="button" class="btn btn-xs btn-light py-1" id="tool-checkmark" onclick="setEditorTool('checkmark')"><i class="ti ti-square-check"></i> Check</button>
                    <button type="button" class="btn btn-xs btn-light py-1" id="tool-whiteout" onclick="setEditorTool('whiteout')"><i class="ti ti-eraser"></i> Whiteout</button>
                </div>
            </div>

            <div class="d-flex align-items-center gap-1">
                <button type="button" onclick="adjustZoom(-0.1)" class="toolbar-btn" title="Zoom Out"><i class="ti ti-minus"></i></button>
                <span class="toolbar-badge" id="zoom-value-label">100%</span>
                <button type="button" onclick="adjustZoom(0.1)" class="toolbar-btn" title="Zoom In"><i class="ti ti-plus"></i></button>
                <button type="button" onclick="resetZoom()" class="toolbar-btn" title="Fit Width"><i class="ti ti-arrows-maximize"></i></button>
            </div>
        </div>

        <!-- Scrollable Workspace Outer Container -->
        <div class="workspace-outer" id="pdf-workspace-container-outer">
            <!-- Loader -->
            <div id="pdf-loader" class="py-5 text-center text-white">
                <div class="spinner-border text-light mb-3" role="status"></div>
                <p style="font-weight: 600;">Streaming PDF document canvas...</p>
            </div>

            <!-- Pages will render dynamically -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Load PDF.js from Cloudflare CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

    const pdfUrl = "{{ route('esign-templates.pdf.stream', $template->id) }}";
    const batchSaveUrl = "{{ route('esign-templates.fields.batch', $template->id) }}";
    
    // Core data array
    let fieldPlacements = @json($template->fields);
    
    let pdfDoc = null;
    let selectedIndex = -1; // Currently selected field box index
    let currentZoom = 1.0; // Zoom scale factor (default fits viewport container width)
    let originalPageWidth = 595.0; // Standard A4 page width points reference

    // Setup Workspace
    document.addEventListener("DOMContentLoaded", function() {
        loadPdfDocument();
        setupKeyboardListeners();
        setupDragAndDrop();
    });

    /**
     * Download and load PDF Document metadata
     */
    async function loadPdfDocument() {
        try {
            const loadingTask = pdfjsLib.getDocument(pdfUrl);
            pdfDoc = await loadingTask.promise;
            
            document.getElementById('pdf-loader').style.display = 'none';
            
            // Populate placement page dropdown option list
            const pageSelect = document.getElementById('new_page_num');
            pageSelect.innerHTML = '';
            for (let i = 1; i <= pdfDoc.numPages; i++) {
                const opt = document.createElement('option');
                opt.value = i;
                opt.textContent = `Page ${i}`;
                pageSelect.appendChild(opt);
            }

            // Calculate auto-fit zoom based on container width
            const containerWidth = document.getElementById('pdf-workspace-container-outer').clientWidth - 48;
            const firstPage = await pdfDoc.getPage(1);
            const defaultViewport = firstPage.getViewport({ scale: 1.0 });
            originalPageWidth = defaultViewport.width;
            
            currentZoom = containerWidth / originalPageWidth;
            // Bound zoom range between 0.6 and 1.8
            currentZoom = Math.max(0.6, Math.min(currentZoom, 1.4));
            
            updateZoomUI();
            await renderAllPages();

        } catch (err) {
            console.error('Error loading PDF: ', err);
            document.getElementById('pdf-loader').innerHTML = `
                <i class="ti ti-alert-triangle fs-1 text-danger"></i>
                <p class="text-danger mt-2">Failed to load PDF file.</p>
                <div class="text-xs text-start mt-3 p-3 bg-white border rounded text-dark overflow-auto" style="max-width:500px; font-family:monospace;">
                    <strong>Tried URL:</strong> ${pdfUrl}<br>
                    <strong>Error:</strong> ${err.message || err}<br>
                    <strong>Tip:</strong> Open the Developer Tools console (F12) to see detailed network or CORS errors.
                </div>
            `;
        }
    }

    /**
     * Render all PDF pages inside workspace based on currentZoom
     */
    async function renderAllPages() {
        const outer = document.getElementById('pdf-workspace-container-outer');
        
        // Remove existing pages rendering
        document.querySelectorAll('.pdf-page-container').forEach(el => el.remove());

        for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
            const page = await pdfDoc.getPage(pageNum);
            const viewport = page.getViewport({ scale: currentZoom });

            const pageWrapper = document.createElement('div');
            pageWrapper.className = 'pdf-page-container';
            pageWrapper.id = `pdf-page-wrapper-${pageNum}`;
            pageWrapper.style.width = `${viewport.width}px`;
            pageWrapper.style.height = `${viewport.height}px`;

            // Page label tag
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

            // Drag and Drop listeners for toolbox variables creation
            overlay.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'copy';
                overlay.style.backgroundColor = 'rgba(15, 98, 254, 0.05)';
            });

            overlay.addEventListener('dragleave', function(e) {
                overlay.style.backgroundColor = '';
            });

            overlay.addEventListener('drop', function(e) {
                e.preventDefault();
                overlay.style.backgroundColor = '';
                const dataStr = e.dataTransfer.getData('text/plain');
                if (!dataStr) return;
                try {
                    const info = JSON.parse(dataStr);
                    const rect = overlay.getBoundingClientRect();
                    const dropX = e.clientX - rect.left;
                    const dropY = e.clientY - rect.top;
                    
                    spawnFieldAtCoords(info.key, info.label, info.type, pageNum, dropX, dropY);
                } catch (err) {
                    console.error('Drop error:', err);
                }
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

        renderAllFields();
        updateMappingsUI();
    }

    /**
     * Draw variable placement overlay blocks
     */
    function renderAllFields() {
        document.querySelectorAll('.visual-field-box').forEach(el => el.remove());

        fieldPlacements.forEach((field, index) => {
            const pageNum = field.page_num;
            const overlay = document.getElementById(`pdf-overlay-${pageNum}`);
            if (!overlay) return;

            const pageHeight = overlay.clientHeight;

            // Convert PDF coordinates to rendered screen pixels coordinates
            const boxLeft = field.x_coordinate * currentZoom;
            const boxWidth = field.width * currentZoom;
            const boxHeight = field.height * currentZoom;
            const boxTop = pageHeight - ((field.y_coordinate * currentZoom) + boxHeight);

            let typeClass = '';
            if (field.type === 'signature') typeClass = 'signature-type';
            else if (field.type === 'whiteout') typeClass = 'whiteout-type';
            else if (field.type === 'checkmark') typeClass = 'checkmark-type';

            const box = document.createElement('div');
            box.className = `visual-field-box ${typeClass} ${index === selectedIndex ? 'active-box' : ''}`;
            box.id = `visual-box-${index}`;
            box.style.left = `${boxLeft}px`;
            box.style.top = `${boxTop}px`;
            box.style.width = `${boxWidth}px`;
            box.style.height = `${boxHeight}px`;

            box.innerHTML = `
                <span class="box-label">${field.label}</span>
                <div class="box-actions">
                    <button class="box-close-btn" onclick="event.stopPropagation(); removeFieldAtIndex(${index});">×</button>
                </div>
                <div class="resize-handle"></div>
                <div class="box-coords-tooltip">(${Math.round(field.x_coordinate)}, ${Math.round(field.y_coordinate)})</div>
            `;

            // Drag-drop & resize setup
            makeElementDraggableAndResizable(box, index, pageNum);

            // Select on click
            box.addEventListener('mousedown', function(e) {
                if (!e.target.classList.contains('box-close-btn')) {
                    selectBox(index);
                }
            });

            overlay.appendChild(box);
        });
    }

    /**
     * Drag-drop & Resizing visual math positioning
     */
    function makeElementDraggableAndResizable(el, index, pageNum) {
        let isDragging = false;
        let isResizing = false;
        let startX, startY, startLeft, startTop, startWidth, startHeight;

        const overlay = document.getElementById(`pdf-overlay-${pageNum}`);
        const pageHeight = overlay.clientHeight;

        el.addEventListener('mousedown', function(e) {
            if (e.target.classList.contains('resize-handle')) {
                isResizing = true;
                startX = e.clientX;
                startY = e.clientY;
                startWidth = parseInt(el.style.width, 10);
                startHeight = parseInt(el.style.height, 10);
                e.preventDefault();
            } else if (e.target.classList.contains('box-close-btn')) {
                return;
            } else {
                isDragging = true;
                startX = e.clientX;
                startY = e.clientY;
                startLeft = parseInt(el.style.left, 10);
                startTop = parseInt(el.style.top, 10);
                e.preventDefault();
            }

            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });

        function onMouseMove(e) {
            const deltaX = e.clientX - startX;
            const deltaY = e.clientY - startY;

            if (isDragging) {
                let newLeft = startLeft + deltaX;
                let newTop = startTop + deltaY;

                newLeft = Math.max(0, Math.min(newLeft, overlay.clientWidth - el.clientWidth));
                newTop = Math.max(0, Math.min(newTop, overlay.clientHeight - el.clientHeight));

                el.style.left = `${newLeft}px`;
                el.style.top = `${newTop}px`;

                const pdfX = newLeft / currentZoom;
                const pdfY = (pageHeight - (newTop + el.clientHeight)) / currentZoom;

                fieldPlacements[index].x_coordinate = Math.round(pdfX);
                fieldPlacements[index].y_coordinate = Math.round(pdfY);
                
                // Live tooltip & inspector values updating
                el.querySelector('.box-coords-tooltip').textContent = `(${Math.round(pdfX)}, ${Math.round(pdfY)})`;
                if (index === selectedIndex) {
                    document.getElementById('insp-x').value = Math.round(pdfX);
                    document.getElementById('insp-y').value = Math.round(pdfY);
                }
            }

            if (isResizing) {
                let newWidth = startWidth + deltaX;
                let newHeight = startHeight + deltaY;

                newWidth = Math.max(40, Math.min(newWidth, overlay.clientWidth - parseInt(el.style.left, 10)));
                newHeight = Math.max(15, Math.min(newHeight, overlay.clientHeight - parseInt(el.style.top, 10)));

                el.style.width = `${newWidth}px`;
                el.style.height = `${newHeight}px`;

                const pdfWidth = newWidth / currentZoom;
                const pdfHeight = newHeight / currentZoom;
                const newTop = parseInt(el.style.top, 10);
                const pdfY = (pageHeight - (newTop + newHeight)) / currentZoom;

                fieldPlacements[index].y_coordinate = Math.round(pdfY);
                fieldPlacements[index].width = Math.round(pdfWidth);
                fieldPlacements[index].height = Math.round(pdfHeight);

                el.querySelector('.box-coords-tooltip').textContent = `(${Math.round(fieldPlacements[index].x_coordinate)}, ${Math.round(pdfY)})`;
                if (index === selectedIndex) {
                    document.getElementById('insp-y').value = Math.round(pdfY);
                    document.getElementById('insp-w').value = Math.round(pdfWidth);
                    document.getElementById('insp-h').value = Math.round(pdfHeight);
                }
            }
        }

        function onMouseUp() {
            isDragging = false;
            isResizing = false;
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
            updateMappingsUI();
        }
    }

    /**
     * Zoom Actions
     */
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

    /**
     * Drag & Drop Setup for left sidebar pills
     */
    function setupDragAndDrop() {
        document.querySelectorAll('.toolbox-pill').forEach(pill => {
            pill.setAttribute('draggable', 'true');
            
            pill.addEventListener('dragstart', function(e) {
                const dragData = {
                    key: this.getAttribute('data-key'),
                    label: this.getAttribute('data-label'),
                    type: this.getAttribute('data-type')
                };
                e.dataTransfer.setData('text/plain', JSON.stringify(dragData));
                this.style.opacity = '0.5';
                e.dataTransfer.effectAllowed = 'copy';
            });
            
            pill.addEventListener('dragend', function(e) {
                this.style.opacity = '1';
            });
        });
    }

    /**
     * Spawns a field box at dropped coordinates on the canvas page wrapper
     */
    function spawnFieldAtCoords(fieldKey, label, type, pageNum, x, y) {
        if (fieldKey === 'custom_field') {
            const timestamp = Date.now().toString().slice(-4);
            fieldKey = `custom_${timestamp}`;
            label = `Custom Field ${timestamp}`;
        }

        // Avoid duplicate variables on same page
        const exists = fieldPlacements.some(f => f.field_key === fieldKey && f.page_num === pageNum);
        if (exists) {
            show_toastr('Error', `Variable "${fieldKey}" is already mapped on Page ${pageNum}`, 'error');
            return;
        }

        const defaultWidth = type === 'signature' ? 150 : 160;
        const defaultHeight = type === 'signature' ? 50 : 20;

        // Position coordinates conversion: client x/y relative to overlay viewport container width zoom
        const boxLeft = x - (defaultWidth * currentZoom) / 2;
        const boxTop = y - (defaultHeight * currentZoom) / 2;

        const overlay = document.getElementById(`pdf-overlay-${pageNum}`);
        const maxLeft = overlay.clientWidth - defaultWidth * currentZoom;
        const maxTop = overlay.clientHeight - defaultHeight * currentZoom;
        
        const cleanLeft = Math.max(0, Math.min(boxLeft, maxLeft));
        const cleanTop = Math.max(0, Math.min(boxTop, maxTop));

        // PDF coordinate systems start from bottom left: convert top offset to bottom offset
        const pdfX = cleanLeft / currentZoom;
        const pdfY = (overlay.clientHeight - (cleanTop + defaultHeight * currentZoom)) / currentZoom;

        const newField = {
            field_key: fieldKey,
            label: label,
            type: type,
            page_num: pageNum,
            x_coordinate: Math.round(pdfX),
            y_coordinate: Math.round(pdfY),
            width: defaultWidth,
            height: defaultHeight
        };

        fieldPlacements.push(newField);
        selectedIndex = fieldPlacements.length - 1;

        renderAllFields();
        updateMappingsUI();
        selectBox(selectedIndex);

        show_toastr('Success', `Placed ${label} box on Page ${pageNum}.`, 'success');
    }

    /**
     * Variable placement triggered from Pills
     */
    function placeFieldFromPill(pillEl) {
        let fieldKey = pillEl.getAttribute('data-key');
        let label = pillEl.getAttribute('data-label');
        const type = pillEl.getAttribute('data-type');
        const pageNum = parseInt(document.getElementById('new_page_num').value);

        if (fieldKey === 'custom_field') {
            const timestamp = Date.now().toString().slice(-4);
            fieldKey = `custom_${timestamp}`;
            label = `Custom Field ${timestamp}`;
        }

        // Avoid duplicate variables on same page
        const exists = fieldPlacements.some(f => f.field_key === fieldKey && f.page_num === pageNum);
        if (exists) {
            show_toastr('Error', `Variable "${fieldKey}" is already mapped on Page ${pageNum}`, 'error');
            return;
        }

        const defaultWidth = type === 'signature' ? 150 : 160;
        const defaultHeight = type === 'signature' ? 50 : 20;

        const newField = {
            field_key: fieldKey,
            label: label,
            type: type,
            page_num: pageNum,
            x_coordinate: 150,
            y_coordinate: 450,
            width: defaultWidth,
            height: defaultHeight
        };

        fieldPlacements.push(newField);
        selectedIndex = fieldPlacements.length - 1;

        renderAllFields();
        updateMappingsUI();
        selectBox(selectedIndex);

        show_toastr('Success', `Placed ${label} box on Page ${pageNum}. Drag it to position.`, 'success');
    }

    /**
     * Select coordinate Box & load Inspector details
     */
    function selectBox(index) {
        selectedIndex = index;
        
        // Highlight active status in boxes classes
        document.querySelectorAll('.visual-field-box').forEach((box, idx) => {
            if (idx === index) {
                box.classList.add('active-box');
            } else {
                box.classList.remove('active-box');
            }
        });

        // Load mappings items highlighting
        document.querySelectorAll('.list-group-item').forEach((item, idx) => {
            if (idx === index) {
                item.classList.add('mapped-item-active');
            } else {
                item.classList.remove('mapped-item-active');
            }
        });

        const field = fieldPlacements[index];
        if (!field) return;

        // Populate Inspector Card panel
        document.getElementById('selected-indicator-text').innerHTML = `<span class="text-primary fw-bold">${field.label}</span> Selected`;
        document.getElementById('inspector-field-key').textContent = field.field_key;
        document.getElementById('insp-key').value = field.field_key;
        document.getElementById('insp-label').value = field.label;
        document.getElementById('insp-x').value = Math.round(field.x_coordinate);
        document.getElementById('insp-y').value = Math.round(field.y_coordinate);
        document.getElementById('insp-w').value = Math.round(field.width);
        document.getElementById('insp-h').value = Math.round(field.height);
        document.getElementById('details-inspector-card').style.display = 'block';
    }

    /**
     * Update coordinate data in model from Inspector input details changes
     */
    function updateSelectedFromInspector() {
        if (selectedIndex === -1) return;

        const key = document.getElementById('insp-key').value.trim();
        const label = document.getElementById('insp-label').value.trim();
        const x = parseFloat(document.getElementById('insp-x').value) || 0;
        const y = parseFloat(document.getElementById('insp-y').value) || 0;
        const w = parseFloat(document.getElementById('insp-w').value) || 10;
        const h = parseFloat(document.getElementById('insp-h').value) || 10;

        if (key) {
            fieldPlacements[selectedIndex].field_key = key;
            document.getElementById('inspector-field-key').textContent = key;
        }

        const boxEl = document.getElementById(`visual-box-${selectedIndex}`);

        if (label) {
            fieldPlacements[selectedIndex].label = label;
            if (boxEl) {
                const labelEl = boxEl.querySelector('.box-label');
                if (labelEl) labelEl.textContent = label;
            }
        }
        fieldPlacements[selectedIndex].x_coordinate = x;
        fieldPlacements[selectedIndex].y_coordinate = y;
        fieldPlacements[selectedIndex].width = w;
        fieldPlacements[selectedIndex].height = h;

        // Note: Avoid drawing all fields on keypress to prevent cursor jump, just redraw mapping labels list
        updateMappingsUI();
        
        // update screen box coordinates tooltip directly
        if (boxEl) {
            const screenHeight = document.getElementById(`pdf-overlay-${fieldPlacements[selectedIndex].page_num}`).clientHeight;
            const boxTop = screenHeight - ((y * currentZoom) + (h * currentZoom));
            boxEl.style.left = `${x * currentZoom}px`;
            boxEl.style.top = `${boxTop}px`;
            boxEl.style.width = `${w * currentZoom}px`;
            boxEl.style.height = `${h * currentZoom}px`;
            boxEl.querySelector('.box-coords-tooltip').textContent = `(${Math.round(x)}, ${Math.round(y)})`;
        }
    }

    /**
     * Keypad nudge listener
     */
    function setupKeyboardListeners() {
        document.addEventListener('keydown', function(e) {
            if (selectedIndex === -1) return;
            
            // Do not capture keys if input fields are focused
            if (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'SELECT') {
                return;
            }

            const nudgeVal = e.shiftKey ? 10 : 1;
            let moved = false;

            if (e.key === 'ArrowUp') {
                fieldPlacements[selectedIndex].y_coordinate += nudgeVal;
                moved = true;
            } else if (e.key === 'ArrowDown') {
                fieldPlacements[selectedIndex].y_coordinate -= nudgeVal;
                moved = true;
            } else if (e.key === 'ArrowLeft') {
                fieldPlacements[selectedIndex].x_coordinate -= nudgeVal;
                moved = true;
            } else if (e.key === 'ArrowRight') {
                fieldPlacements[selectedIndex].x_coordinate += nudgeVal;
                moved = true;
            }

            if (moved) {
                e.preventDefault();
                renderAllFields();
                updateMappingsUI();
                selectBox(selectedIndex); // Refresh inspector inputs
            }
        });
    }

    function removeFieldAtIndex(index) {
        fieldPlacements.splice(index, 1);
        if (selectedIndex === index) {
            selectedIndex = -1;
            document.getElementById('details-inspector-card').style.display = 'none';
            document.getElementById('selected-indicator-text').textContent = 'No Box Selected';
        } else if (selectedIndex > index) {
            selectedIndex--;
        }
        
        renderAllFields();
        updateMappingsUI();
    }

    /**
     * UI Synchronize list display mapping list
     */
    function updateMappingsUI() {
        const listContainer = document.getElementById('active-mappings-list');
        listContainer.innerHTML = '';
        
        document.getElementById('mapping-count-badge').textContent = `${fieldPlacements.length} Mapped`;

        if (fieldPlacements.length === 0) {
            listContainer.innerHTML = `
                <div class="text-center text-muted p-4" style="font-size: 0.8rem;">
                    No variables placed on PDF. Select variables above to place.
                </div>
            `;
            return;
        }

        fieldPlacements.forEach((field, index) => {
            const item = document.createElement('div');
            item.className = `list-group-item d-flex justify-content-between align-items-center py-2 px-3 ${index === selectedIndex ? 'mapped-item-active' : ''}`;
            item.style.fontSize = '0.85rem';
            item.style.cursor = 'pointer';
            
            item.innerHTML = `
                <div onclick="selectBox(${index})">
                    <strong class="text-dark d-block">${field.label}</strong>
                    <code class="text-primary text-xs">${field.field_key}</code>
                    <span class="text-muted d-block text-xxs mt-0.5">
                        Page ${field.page_num} | Pos: (${Math.round(field.x_coordinate)}, ${Math.round(field.y_coordinate)})
                    </span>
                </div>
                <button type="button" onclick="event.stopPropagation(); removeFieldAtIndex(${index})" class="btn btn-sm btn-link text-danger p-0 border-0 bg-transparent">
                    <i class="ti ti-trash"></i>
                </button>
            `;
            
            listContainer.appendChild(item);
        });
    }

    /**
     * Submit variables coordinates array payload via AJAX
     */
    function saveAllCoordinates() {
        $.ajax({
            url: batchSaveUrl,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                fields: fieldPlacements
            },
            success: function(res) {
                if (res.success) {
                    show_toastr('Success', res.message, 'success');
                } else {
                    show_toastr('Error', res.message || 'Failed to save placements.', 'error');
                }
            },
            error: function(xhr) {
                console.error('[Save Error] ', xhr);
                show_toastr('Error', 'Server connection failure. Placements not saved.', 'error');
            }
        });
    }

    /**
     * Auto-maps CDSL ventures KYC template variables to default coordinates
     */
    function autoMapCdslFields() {
        const defaultCdslMappings = [
            { field_key: 'pan_number', label: 'PAN Number', type: 'text', page_num: 1, x_coordinate: 80, y_coordinate: 583, width: 250, height: 18 },
            { field_key: 'full_name', label: 'Full Name', type: 'text', page_num: 1, x_coordinate: 130, y_coordinate: 545, width: 380, height: 18 },
            { field_key: 'father_name', label: 'Father\'s Name', type: 'text', page_num: 1, x_coordinate: 130, y_coordinate: 507, width: 380, height: 18 },
            { field_key: 'dob', label: 'Date of Birth', type: 'text', page_num: 1, x_coordinate: 130, y_coordinate: 470, width: 150, height: 18 },
            { field_key: 'gender', label: 'Gender', type: 'text', page_num: 1, x_coordinate: 130, y_coordinate: 445, width: 100, height: 18 },
            { field_key: 'marital_status', label: 'Marital Status', type: 'text', page_num: 1, x_coordinate: 130, y_coordinate: 420, width: 100, height: 18 },
            { field_key: 'aadhar_number', label: 'Aadhaar Number', type: 'text', page_num: 1, x_coordinate: 80, y_coordinate: 320, width: 250, height: 18 },
            { field_key: 'signature', label: 'Customer Signature', type: 'signature', page_num: 1, x_coordinate: 410, y_coordinate: 45, width: 150, height: 50 }
        ];

        // Ask for confirmation
        if (fieldPlacements.length > 0) {
            if (!confirm('This will add default CDSL KYC form variables to your coordinates. Continue?')) {
                return;
            }
        }

        defaultCdslMappings.forEach(mapping => {
            // Check if key is already mapped
            const exists = fieldPlacements.some(f => f.field_key === mapping.field_key && f.page_num === mapping.page_num);
            if (!exists) {
                fieldPlacements.push(mapping);
            }
        });

        renderAllFields();
        updateMappingsUI();
        show_toastr('Success', 'Default CDSL KYC variables auto-mapped! Drag/resize them as needed and click Save Visual Placements.', 'success');
    }

    /**
     * Dynamically creates a new custom variable pill and appends it to the sidebar toolbox
     */
    function createNewCustomPill() {
        const labelInput = document.getElementById('new-custom-label');
        const keyInput = document.getElementById('new-custom-key');
        
        const label = labelInput.value.trim();
        let key = keyInput.value.trim().toLowerCase().replace(/[^a-z0-9]/g, '_');
        
        if (!label || !key) {
            show_toastr('Error', 'Please enter both Label and Key for the custom variable.', 'error');
            return;
        }

        const container = document.getElementById('created-custom-pills-container');
        const pill = document.createElement('div');
        pill.className = 'toolbox-pill';
        pill.setAttribute('data-key', key);
        pill.setAttribute('data-label', label);
        pill.setAttribute('data-type', 'text');
        pill.style.borderLeft = '4px solid #8b5cf6';
        
        pill.innerHTML = `
            <i class="ti ti-circle-dot"></i>
            <span>${label}</span>
        `;
        
        // Add draggability to the dynamically spawned custom pill
        pill.setAttribute('draggable', 'true');
        pill.addEventListener('dragstart', function(e) {
            const dragData = {
                key: this.getAttribute('data-key'),
                label: this.getAttribute('data-label'),
                type: this.getAttribute('data-type')
            };
            e.dataTransfer.setData('text/plain', JSON.stringify(dragData));
            this.style.opacity = '0.5';
            e.dataTransfer.effectAllowed = 'copy';
        });
        
        pill.addEventListener('dragend', function(e) {
            this.style.opacity = '1';
        });
        
        // Allow placement on click too
        pill.onclick = function() {
            placeFieldFromPill(this);
        };
        
        container.appendChild(pill);
        
        // Reset inputs
        labelInput.value = '';
        keyInput.value = '';
        
        show_toastr('Success', `Custom variable pill "${label}" created! Drag/drop it or click to place.`, 'success');
    }

    /* Editor Tool Controls & Click to Spawning Handlers */
    let activeTool = 'select';
    function setEditorTool(tool) {
        activeTool = tool;
        document.querySelectorAll('.tool-group button').forEach(btn => {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-light');
        });
        document.getElementById(`tool-${tool}`).classList.remove('btn-light');
        document.getElementById(`tool-${tool}`).classList.add('btn-primary');

        const overlays = document.querySelectorAll('.pdf-interaction-overlay');
        overlays.forEach(overlay => {
            if (tool === 'select') overlay.style.cursor = 'default';
            else if (tool === 'text') overlay.style.cursor = 'text';
            else if (tool === 'checkmark') overlay.style.cursor = 'cell';
            else if (tool === 'whiteout') overlay.style.cursor = 'crosshair';
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
            spawnTemplateCustomField(pageNum, clickX, clickY, 'text');
        } else if (activeTool === 'checkmark') {
            spawnTemplateCustomField(pageNum, clickX, clickY, 'checkmark');
        } else if (activeTool === 'whiteout') {
            spawnTemplateCustomField(pageNum, clickX, clickY, 'whiteout');
        }

        setEditorTool('select');
    }

    function spawnTemplateCustomField(pageNum, x, y, type) {
        const timestamp = Date.now().toString().slice(-4);
        let fieldKey = '';
        let label = '';
        let width = 120;
        let height = 20;

        if (type === 'text') {
            fieldKey = `custom_text_${timestamp}`;
            label = `Static text ${timestamp}`;
        } else if (type === 'checkmark') {
            fieldKey = `checkmark_${timestamp}`;
            label = `✓`;
            width = 20;
            height = 20;
        } else if (type === 'whiteout') {
            fieldKey = `whiteout_${timestamp}`;
            label = `Whiteout`;
            width = 80;
            height = 20;
        }

        const newField = {
            field_key: fieldKey,
            label: label,
            type: type,
            page_num: pageNum,
            x_coordinate: Math.round(x / currentZoom),
            y_coordinate: Math.round((overlayHeight(pageNum) - (y + height)) / currentZoom),
            width: width,
            height: height
        };

        fieldPlacements.push(newField);
        selectedIndex = fieldPlacements.length - 1;

        renderAllFields();
        updateMappingsUI();
        selectBox(selectedIndex);

        show_toastr('Success', `Placed custom ${type} box. Drag/resize to position.`, 'success');
    }

    function overlayHeight(pageNum) {
        const overlay = document.getElementById(`pdf-overlay-${pageNum}`);
        return overlay ? overlay.clientHeight : 841;
    }
</script>
@endpush
