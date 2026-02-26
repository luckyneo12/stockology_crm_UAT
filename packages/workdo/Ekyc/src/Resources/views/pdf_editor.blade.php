@extends('layouts.main')

@section('page-title')
    {{ __('PDF Template Editor') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('ekyc.dashboard') }}">{{ __('eKYC') }}</a></li>
    <li class="breadcrumb-item">{{ __('PDF Template Editor') }}</li>
@endsection

@section('content')
@php
    $company_settings = getCompanyAllSetting();
@endphp

@push('css')
    <link href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}" rel="stylesheet">
    <style>
        .note-editor.note-frame { border: 1px solid #ebf1f6; }
        .note-toolbar { background: #f8f9fa; }
        .placeholder-card { position: sticky; top: 20px; }
    </style>
@endpush

<div class="row">
    <div class="col-xl-9">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>{{ __('Document Templates') }}</h5>
                <div>
                    <button type="button" class="btn btn-sm btn-primary" id="add-pdf-template">
                        <i class="ti ti-plus me-1"></i>{{ __('Add New Template') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="reset-to-standard">
                        <i class="ti ti-refresh me-1"></i>{{ __('Reset to Standard') }}
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="pdf-templates-form" method="POST" action="{{ route('ekyc.settings.save') }}">
                    @csrf
                    <input type="hidden" name="ekyc_pdf_templates" id="ekyc_pdf_templates_input">
                    <div id="pdf-templates-container">
                        @php
                            $templates = !empty($company_settings['ekyc_pdf_templates']) ? json_decode($company_settings['ekyc_pdf_templates'], true) : [];
                        @endphp

                        @foreach($templates as $index => $template)
                            <div class="pdf-template-block card mb-4 border" data-id="{{ $template['id'] ?? uniqid() }}">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                    <h6 class="mb-0 text-primary"><i class="ti ti-file-text me-2"></i>{{ $template['name'] }}</h6>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-info preview-template me-1" title="Preview"><i class="ti ti-eye me-1"></i>{{ __('Preview') }}</button>
                                        <button type="button" class="btn btn-sm btn-link text-danger remove-template" title="Delete"><i class="ti ti-trash"></i></button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-9 mb-3">
                                            <label class="form-label">{{ __('Template Name') }}</label>
                                            <input type="text" class="form-control template-name" value="{{ $template['name'] ?? '' }}" placeholder="e.g. Account Opening Form">
                                        </div>
                                        <div class="col-md-3 mb-3 text-end">
                                            <div class="form-check form-switch d-inline-block mt-4 me-3">
                                                <input type="checkbox" class="form-check-input template-enabled" {{ ($template['is_enabled'] ?? 'on') == 'on' ? 'checked' : '' }}>
                                                <label class="form-check-label">{{ __('Active') }}</label>
                                            </div>
                                            <div class="form-check form-switch d-inline-block mt-4">
                                                <input type="checkbox" class="form-check-input template-esign" {{ ($template['require_esign'] ?? 'on') == 'on' ? 'checked' : '' }}>
                                                <label class="form-check-label">{{ __('Require e-Sign') }}</label>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">{{ __('Document Content') }}</label>
                                            <textarea class="form-control summernote template-content" rows="10">{{ $template['content'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('ekyc.settings') }}" class="btn btn-secondary px-4 me-2">{{ __('Back to Settings') }}</a>
                        <button type="submit" class="btn btn-primary px-5">{{ __('Save Templates') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-3">
        <div class="card placeholder-card">
            <div class="card-header">
                <h5>{{ __('Placeholders') }}</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">{{ __('Click to insert dynamic data into the active document.') }}</p>
                <div class="accordion" id="placeholderAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#userPlaceholders">
                                {{ __('User Info') }}
                            </button>
                        </h2>
                        <div id="userPlaceholders" class="accordion-collapse collapse show">
                            <div class="accordion-body p-2">
                                @foreach(['full_name', 'pan_number', 'aadhaar_number', 'dob', 'gender', 'marital_status', 'mobile_number', 'email', 'user_address', 'application_no', 'client_code'] as $ph)
                                    <button class="btn btn-sm btn-light w-100 text-start mb-1 insert-ph" data-ph="{{ '{' . $ph . '}' }}">{{ str_replace('_', ' ', ucwords($ph)) }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#bankPlaceholders">
                                {{ __('Bank & Financial') }}
                            </button>
                        </h2>
                        <div id="bankPlaceholders" class="accordion-collapse collapse">
                            <div class="accordion-body p-2">
                                @foreach(['bank_account_number', 'bank_ifsc', 'bank_name', 'occupation', 'annual_income', 'trading_experience', 'networth', 'networth_date'] as $ph)
                                    <button class="btn btn-sm btn-light w-100 text-start mb-1 insert-ph" data-ph="{{ '{' . $ph . '}' }}">{{ str_replace('_', ' ', ucwords($ph)) }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#companyPlaceholders">
                                {{ __('Company Info') }}
                            </button>
                        </h2>
                        <div id="companyPlaceholders" class="accordion-collapse collapse">
                            <div class="accordion-body p-2">
                                @foreach(['company_name', 'company_address', 'company_logo', 'auth_sign'] as $ph)
                                    <button class="btn btn-sm btn-light w-100 text-start mb-1 insert-ph" data-ph="{{ '{' . $ph . '}' }}">{{ str_replace('_', ' ', ucwords($ph)) }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#assetsPlaceholders">
                                {{ __('Biometrics & Assets') }}
                            </button>
                        </h2>
                        <div id="assetsPlaceholders" class="accordion-collapse collapse">
                            <div class="accordion-body p-2">
                                @foreach(['signature', 'selfie', 'current_date'] as $ph)
                                    <button class="btn btn-sm btn-light w-100 text-start mb-1 insert-ph" data-ph="{{ '{' . $ph . '}' }}">{{ str_replace('_', ' ', ucwords($ph)) }}</button>
                                @endforeach
                                <button class="btn btn-sm btn-warning w-100 text-start mb-1 insert-ph" data-ph="<div class='page-break'></div>">{{ __('PAGE BREAK') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title" id="previewModalLabel text-primary"><i class="ti ti-eye me-2"></i>{{ __('Live Document Preview') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light p-4">
                <div class="preview-paper shadow-sm mx-auto" id="previewContent" style="background: white; padding: 50px; min-height: 29.7cm; width: 21cm; margin: 0 auto; color: #333; font-size: 14px; line-height: 1.6;">
                    <!-- Content will be injected here -->
                </div>
                <div class="text-center mt-3 text-muted small">
                    <i class="ti ti-info-circle me-1"></i>{{ __('This is a simulated preview with sample data.') }}
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close Preview') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
<script>
    const standardTemplates = [
        {
            name: 'Master E-Sign Registration Form',
            content: `<style>
    .master-esign { font-family: sans-serif; color: #333; line-height: 1.4; font-size: 11px; }
    .page-box { padding: 20px; border: 1px solid #eee; margin-bottom: 20px; }
    .title-header { background: #f8f9fa; border: 1px solid #ddd; padding: 15px; text-align: center; }
    .title-header h1 { margin: 0; font-size: 20px; text-transform: uppercase; }
    .info-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    .info-table th, .info-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    .info-table th { background: #f2f2f2; width: 25%; }
    .section-hl { background: #10b981; color: white; padding: 5px 10px; font-weight: bold; margin: 15px 0 10px 0; }
</style>
<div class="master-esign">
    <div class="page-box">
        <div style="text-align: center;">
            {company_logo}
            <h2>{company_name}</h2>
            <p>Member : National Stock Exchange of India Ltd.</p>
        </div>
        <div class="title-header">
            <h1 style="color: #333;">Client Registration Form</h1>
            <p>Individual / Sole Proprietor</p>
        </div>
        <table class="info-table">
            <tr><th>Form No.:</th><td>{application_no}</td><th>Date:</th><td>{current_date}</td></tr>
            <tr><th>Client Name:</th><td colspan="3"><b>{full_name}</b></td></tr>
            <tr><th>Client Code:</th><td>{client_code}</td><th>BOID:</th><td>{boid}</td></tr>
        </table>
    </div>
    <div class="page-break"></div>
    <div class="section-hl">KYC INDIVIDUAL APPLICATION FORM</div>
    <table class="info-table">
        <tr>
            <th>Name:</th><td>{full_name}</td>
            <td rowspan="4" style="text-align:center; width:120px; border: 1px solid #ddd;">{selfie}<br><small>Photo</small></td>
        </tr>
        <tr><th>Father's Name:</th><td>{father_name}</td></tr>
        <tr><th>Mother's Name:</th><td>{mother_name}</td></tr>
        <tr><th>DOB:</th><td>{dob}</td></tr>
        <tr><th>Gender:</th><td>{gender}</td><th>Marital Status:</th><td>{marital_status}</td></tr>
    </table>
    <div class="section-hl">IDENTITY & ADDRESS</div>
    <table class="info-table">
        <tr><th>Aadhaar:</th><td>{aadhaar_number}</td><th>PAN:</th><td>{pan_number}</td></tr>
        <tr><th>Address:</th><td colspan="3">{user_address}</td></tr>
        <tr><th>City/State:</th><td>{city} / {state}</td><th>PIN:</th><td>{pin_code}</td></tr>
    </table>
    <div class="section-hl">BANK & FINANCIAL DETAILS</div>
    <table class="info-table">
        <tr><th>A/C No.:</th><td>{bank_account_number}</td><th>IFSC:</th><td>{bank_ifsc}</td></tr>
        <tr><th>Bank Name:</th><td colspan="3">{bank_name}</td></tr>
        <tr><th>Income:</th><td>{annual_income}</td><th>Networth:</th><td>{networth}</td></tr>
    </table>
    <div style="margin-top: 20px; border: 1px solid #ddd; padding: 15px;">
        <p><b>DECLARATION:</b> I hereby declare that the details furnished above are true and correct to the best of my knowledge and belief and I undertake to inform you of any changes therein, immediately.</p>
        <div style="text-align: right; margin-top: 30px;">
            {signature}<br><b>(Applicant Signature)</b>
        </div>
    </div>
</div>`
        },
        {
            name: 'DDPI (Demat Debit and Pledge Instruction)',
            content: `<div style="text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px;">
    <div style="float: left;">{company_logo}</div>
    <div style="display: inline-block;">
        <h2 style="margin: 0;">DDPI FOR DEMAT ACCOUNT settlement</h2>
        <p style="margin: 5px 0;">{company_name}</p>
        <p style="font-size: 10px; margin: 0;">{company_address}</p>
    </div>
    <div style="clear: both;"></div>
</div>
<p>To,<br><b>{company_name}</b></p>
<p>I/We hold a beneficiary account no. (BO ID) with you. I/We hereby give this Demat Debit and Pledge Instruction (DDPI) to you for the following purposes:</p>
<table border="1" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
    <thead>
        <tr style="background: #f2f2f2;">
            <th style="padding: 10px; width: 50px;">Sr.</th>
            <th style="padding: 10px;">Purpose</th>
            <th style="padding: 10px; width: 150px;">Signature</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="padding: 10px; text-align: center;">1</td>
            <td style="padding: 10px;">Transfer of securities held in beneficial owner accounts of the client towards Stock Exchange related deliveries / settlement obligations arising out of trades executed by clients on the Stock Exchange through the same stock broker</td>
            <td style="padding: 10px; text-align: center;">{signature}</td>
        </tr>
        <tr>
            <td style="padding: 10px; text-align: center;">2</td>
            <td style="padding: 10px;">Pledging / re-pledging of securities in favor of the Trading Member (TM) / Clearing Member (CM) for the purpose of meeting margin requirements of the clients.</td>
            <td style="padding: 10px; text-align: center;">{signature}</td>
        </tr>
    </tbody>
</table>`
        },
        {
            name: 'Account Opening Form (AOF)',
            content: `<div style="text-align: center; border-bottom: 2px solid #10b981; padding-bottom: 10px; margin-bottom: 20px;">
    {company_logo}
    <h1 style="color: #10b981; margin: 10px 0;">KYC & ACCOUNT OPENING FORM</h1>
    <p style="margin: 0;">{company_name}</p>
</div>
<div style="margin-bottom: 30px;">
    <h3 style="background: #10b981; color: white; padding: 5px 10px;">1. PERSONAL DETAILS</h3>
    <p><b>Name:</b> {full_name} | <b>PAN:</b> {pan_number}</p>
</div>`
        }
    ];

    $(document).ready(function() {
        const sampleData = {
            'full_name': 'John Doe Kumar',
            'pan_number': 'ABCDE1234F',
            'aadhaar_number': '1234 5678 9012',
            'dob': '01-01-1990',
            'gender': 'Male',
            'marital_status': 'Single',
            'mobile_number': '+91 9876543210',
            'email': 'johndoe@example.com',
            'user_address': '123, Wealth Street, Financial Hub, Mumbai, Maharashtra - 400001',
            'city': 'Mumbai',
            'state': 'Maharashtra',
            'pin_code': '400001',
            'application_no': 'EKYC-9999',
            'client_code': 'HO09999',
            'boid': '1208160000123456',
            'client_id': '9999',
            'dp_id': 'IN300001',
            'introducer_name': 'HO (Main Branch)',
            'bank_account_number': '919012345678',
            'bank_ifsc': 'UTIB0000123',
            'bank_name': 'Axis Bank Ltd',
            'occupation': 'Business / Professional',
            'annual_income': '5 - 10 Lakhs',
            'trading_experience': '5 Years',
            'networth': '15,00,000',
            'networth_date': '01-02-2026',
            'current_date': new Date().toLocaleDateString('en-GB').replace(/\//g, '-'),
            'company_name': '{{ $company_settings["ekyc_company_name"] ?? "Antigravity Wealth Management" }}',
            'company_address': '{{ $company_settings["ekyc_company_address"] ?? "1st Floor, Tech Park, City Center" }}',
            'company_logo': '<img src="{{ $company_settings["ekyc_company_logo"] ?? "" }}" style="max-height: 60px;">',
            'auth_sign': '<img src="{{ $company_settings["ekyc_company_auth_sign"] ?? "" }}" style="max-height: 60px;">',
            'signature': '<div style="border: 1px dashed #ccc; padding: 10px; width: 150px; height: 60px; display: inline-block; vertical-align: middle; text-align: center; color: #999;">[Applicant Signature]</div>',
            'selfie': '<div style="border: 1px solid #ddd; width: 100px; height: 100px; background: #eee; display: flex; align-items: center; justify-content: center; color: #666; font-size: 10px;">User Photo</div>'
        };

        initSummernote();

        $('#add-pdf-template').on('click', function() {
            addTemplateBlock('New Template', '');
        });

        $('#reset-to-standard').on('click', function() {
            if(confirm('This will load standard templates. Existing templates will be kept until you save.')) {
                standardTemplates.forEach(t => {
                    addTemplateBlock(t.name, t.content);
                });
            }
        });

        $(document).on('click', '.remove-template', function() {
            if(confirm('Are you sure you want to delete this template?')) {
                $(this).closest('.pdf-template-block').fadeOut(function() { $(this).remove(); });
            }
        });

        $(document).on('click', '.preview-template', function() {
            const block = $(this).closest('.pdf-template-block');
            let content = block.find('.template-content').summernote('code');
            
            // Replace placeholders in content
            Object.keys(sampleData).forEach(key => {
                const regex = new RegExp('{' + key + '}', 'g');
                content = content.replace(regex, sampleData[key]);
            });

            $('#previewContent').html(content);
            const myModal = new bootstrap.Modal(document.getElementById('previewModal'));
            myModal.show();
        });

        $(document).on('click', '.insert-ph', function(e) {
            e.preventDefault();
            const val = $(this).data('ph');
            // Find active summernote (last focused or first available)
            const $editor = $('.summernote').last(); 
            $editor.summernote('editor.insertText', val);
        });

        $('#pdf-templates-form').on('submit', function(e) {
            const templates = [];
            $('.pdf-template-block').each(function() {
                templates.push({
                    id: $(this).data('id'),
                    name: $(this).find('.template-name').val(),
                    is_enabled: $(this).find('.template-enabled').is(':checked') ? 'on' : 'off',
                    require_esign: $(this).find('.template-esign').is(':checked') ? 'on' : 'off',
                    content: $(this).find('.template-content').summernote('code')
                });
            });
            $('#ekyc_pdf_templates_input').val(JSON.stringify(templates));
        });

        function addTemplateBlock(name, content) {
            const id = 'tmp_' + Date.now() + Math.floor(Math.random() * 1000);
            const html = `
                <div class="pdf-template-block card mb-4 border" data-id="${id}">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0 text-primary"><i class="ti ti-file-text me-2"></i>\${name}</h6>
                        <div>
                            <button type="button" class="btn btn-sm btn-info preview-template me-1" title="Preview"><i class="ti ti-eye me-1"></i>{{ __('Preview') }}</button>
                            <button type="button" class="btn btn-sm btn-link text-danger remove-template" title="Delete"><i class="ti ti-trash"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-9 mb-3">
                                <label class="form-label">{{ __('Template Name') }}</label>
                                <input type="text" class="form-control template-name" value="\${name}" placeholder="e.g. KYC Form">
                            </div>
                            <div class="col-md-3 mb-3 text-end">
                                <div class="form-check form-switch d-inline-block mt-4 me-3">
                                    <input type="checkbox" class="form-check-input template-enabled" checked>
                                    <label class="form-check-label">{{ __('Active') }}</label>
                                </div>
                                <div class="form-check form-switch d-inline-block mt-4">
                                    <input type="checkbox" class="form-check-input template-esign" checked>
                                    <label class="form-check-label">{{ __('Require e-Sign') }}</label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">{{ __('Document Content') }}</label>
                                <textarea class="form-control summernote template-content" rows="10">\${content}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#pdf-templates-container').prepend(html);
            initSummernote();
        }

        function initSummernote() {
            $('.summernote').summernote({
                height: 400,
                dialogsInBody: true,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        }
    });
</script>
@endpush
