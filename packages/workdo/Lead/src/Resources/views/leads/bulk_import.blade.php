@extends('layouts.main')

@section('page-title')
    {{ __('Bulk Lead Import') }}
@endsection

@section('page-breadcrumb')
    {{ __('Leads') }}, {{ __('Bulk Import') }}
@endsection

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.5);
            --premium-red: #dc3545;
            --premium-gradient: linear-gradient(135deg, #ff4d5a, #b01b2e);
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.08);
        }

        .bulk-import-container {
            font-family: 'Outfit', sans-serif;
            color: #2b2b2b;
        }

        .premium-card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            box-shadow: var(--glass-shadow);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .premium-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(31, 38, 135, 0.12);
        }

        .premium-header {
            background: var(--premium-gradient);
            color: white;
            padding: 20px 24px;
            border-radius: 16px 16px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .premium-header h5 {
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
            font-size: 1.15rem;
        }

        .dropzone-container {
            border: 2px dashed #ced4da;
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            background: rgba(0, 0, 0, 0.01);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dropzone-container:hover, .dropzone-container.dragover {
            border-color: var(--premium-red);
            background: rgba(220, 53, 69, 0.02);
        }

        .dropzone-icon {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .dropzone-container:hover .dropzone-icon {
            color: var(--premium-red);
            transform: scale(1.1);
        }

        .form-select-premium {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 10px 14px;
            font-weight: 600;
            color: #4a5568;
            background-color: #fff;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .form-select-premium:focus {
            border-color: var(--premium-red);
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.15);
            outline: none;
        }

        .form-select-premium.mapped {
            border: 2px solid #28a745 !important;
            background-color: #f0fff4 !important;
            color: #198754;
        }

        .progress-bar-premium-container {
            background: #edf2f7;
            height: 28px;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.06);
            margin-bottom: 25px;
        }

        .progress-bar-premium {
            height: 100%;
            background: var(--premium-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 0.9rem;
            text-shadow: 0 1px 2px rgba(0,0,0,0.25);
            width: 0%;
            transition: width 0.4s cubic-bezier(0.1, 0.8, 0.25, 1);
            box-shadow: 0 2px 5px rgba(220, 53, 69, 0.3);
        }

        .stats-count {
            font-size: 2.2rem;
            font-weight: 800;
            color: #2d3748;
            line-height: 1.2;
        }

        .stats-count.failure {
            color: var(--premium-red);
        }

        .console-log {
            font-family: 'JetBrains Mono', monospace;
            background: #1a202c;
            color: #cbd5e0;
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 0.85rem;
            max-height: 180px;
            overflow-y: auto;
            border-left: 4px solid var(--premium-red);
            box-shadow: inset 0 2px 8px rgba(0,0,0,0.2);
        }

        .table-responsive-premium {
            border-radius: 12px;
            overflow-x: auto;
            overflow-y: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: var(--card-shadow);
            white-space: nowrap;
        }

        .table-premium {
            margin-bottom: 0;
        }

        .table-premium thead th {
            background: #f7fafc;
            color: #4a5568;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.8px;
            padding: 14px 16px;
            border-bottom: 2px solid #edf2f7;
        }

        .table-premium tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            font-size: 0.88rem;
            border-bottom: 1px solid #edf2f7;
        }

        .badge-premium-danger {
            background-color: #fff5f5;
            color: #c53030;
            border: 1px solid #feb2b2;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.78rem;
        }

        .btn-premium-action {
            background: var(--premium-gradient);
            color: white;
            font-weight: 700;
            border-radius: 30px;
            padding: 12px 36px;
            border: none;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.25);
            transition: all 0.3s ease;
        }

        .btn-premium-action:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.35);
            color: white;
        }

        .btn-premium-action:disabled {
            background: #cbd5e0;
            box-shadow: none;
            cursor: not-allowed;
        }
    </style>
@endpush

@section('content')
<div class="row bulk-import-container">
    <div class="col-12">
        <!-- Step 1: Upload Card -->
        <div id="card-upload" class="card premium-card">
            <div class="premium-header">
                <h5><i class="ti ti-file-upload me-2"></i>{{ __('Step 1: Upload CSV File') }}</h5>
                <a href="{{ route('leads.bulk.import.sample') }}" class="btn btn-sm btn-light rounded-pill px-3 shadow-sm">
                    <i class="ti ti-download me-1"></i>{{ __('Download Sample CSV') }}
                </a>
            </div>
            <div class="card-body p-4">
                <form id="form-upload-file" enctype="multipart/form-data">
                    @csrf
                    <div class="dropzone-container" id="dropzone" onclick="document.getElementById('file-input').click()">
                        <i class="ti ti-cloud-upload dropzone-icon"></i>
                        <h4 class="fw-bold mb-2">{{ __('Drag & Drop your CSV file here') }}</h4>
                        <p class="text-muted mb-3">{{ __('or click to browse your computer') }}</p>
                        <span class="badge bg-soft-danger px-3 py-2 rounded-pill text-danger fw-bold"><i class="ti ti-info-circle me-1"></i>{{ __('Only .csv files supported') }}</span>
                        <input type="file" id="file-input" name="file" accept=".csv" class="d-none" onchange="v11HandleFileUpload(this.files)">
                    </div>
                </form>
            </div>
        </div>

        <!-- Step 2: Configuration & Mapping Card (Hidden Initially) -->
        <div id="card-mapping" class="card premium-card" style="display: none;">
            <div class="premium-header">
                <h5><i class="ti ti-settings me-2"></i>{{ __('Step 2: Column Mapping & Configuration') }}</h5>
            </div>
            <div class="card-body p-4">
                <!-- Global Settings -->
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2"><i class="ti ti-world me-1 text-danger"></i>{{ __('Global Default Values') }}</h5>
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold text-muted small uppercase">{{ __('Responsible Person') }}</label>
                        <select name="global_user" id="global_user" class="form-select form-select-premium">
                            <option value="">{{ __('Select Default Owner') }}</option>
                            @foreach($users as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold text-muted small uppercase">{{ __('Pipeline') }}</label><x-required></x-required>
                        <select name="global_pipeline" id="global_pipeline" class="form-select form-select-premium">
                            <option value="">{{ __('Select Pipeline') }}</option>
                            @foreach($pipelines as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold text-muted small uppercase">{{ __('Stage') }}</label><x-required></x-required>
                        <select name="global_stage" id="global_stage" class="form-select form-select-premium">
                            <option value="">{{ __('Select Stage') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold text-muted small uppercase">{{ __('Source') }}</label>
                        <select name="global_source" id="global_source" class="form-select form-select-premium">
                            <option value="">{{ __('Select Default Source') }}</option>
                            @foreach($sources as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- CSV Field Mapping -->
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2"><i class="ti ti-table me-1 text-danger"></i>{{ __('Map CSV Columns') }}</h5>
                <p class="text-muted small mb-4">{{ __('Match the columns from your uploaded CSV file to the corresponding CRM Lead fields.') }}</p>
                
                <div class="table-responsive-premium mb-4">
                    <table class="table table-premium table-hover">
                        <thead>
                            <tr id="mapping-table-header">
                                <!-- Headers populated dynamically -->
                            </tr>
                            <tr id="mapping-table-dropdowns" style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                <!-- Dropdowns populated dynamically -->
                            </tr>
                        </thead>
                        <tbody id="mapping-table-preview-body">
                            <!-- Preview data rows populated dynamically -->
                        </tbody>
                    </table>
                </div>

                <!-- Preview Pagination Controls -->
                <div class="d-flex justify-content-between align-items-center mb-4 px-2" id="preview-pagination-container" style="display: none !important;">
                    <div class="text-muted small" id="preview-pagination-info"></div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-danger px-3" id="preview-prev-btn" onclick="v11PrevPreviewPage()"><i class="ti ti-chevron-left me-1"></i>{{ __('Previous') }}</button>
                        <button type="button" class="btn btn-sm btn-outline-danger px-3" id="preview-next-btn" onclick="v11NextPreviewPage()">{{ __('Next') }}<i class="ti ti-chevron-right ms-1"></i></button>
                    </div>
                </div>

                <div class="text-end">
                    <button type="button" onclick="v11CancelMapping()" class="btn btn-light rounded-pill px-4 me-2">{{ __('Cancel') }}</button>
                    <button type="button" id="btn-start-import" class="btn btn-premium-action" onclick="v11StartProcess()">{{ __('START BULK IMPORT') }} <i class="ti ti-rocket ms-1"></i></button>
                </div>
            </div>
        </div>

        <!-- Step 3: Progress Card (Hidden Initially) -->
        <div id="card-progress" class="card premium-card" style="display: none;">
            <div class="premium-header">
                <h5><i class="ti ti-player-play me-2"></i>{{ __('Step 3: Import Progress') }}</h5>
            </div>
            <div class="card-body p-4">
                <div class="progress-bar-premium-container">
                    <div class="progress-bar-premium" id="p-bar">0%</div>
                </div>

                <div class="row text-center mb-4">
                    <div class="col-4 border-end">
                        <div class="text-muted small uppercase fw-bold mb-1">{{ __('Total Rows') }}</div>
                        <div class="stats-count" id="stat-total">0</div>
                    </div>
                    <div class="col-4 border-end">
                        <div class="text-muted small uppercase fw-bold mb-1">{{ __('Processed') }}</div>
                        <div class="stats-count text-success" id="stat-processed">0</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small uppercase fw-bold mb-1">{{ __('Errors / Skipped') }}</div>
                        <div class="stats-count failure" id="stat-failed">0</div>
                    </div>
                </div>

                <div class="console-log mb-4" id="log-console">
                    {{ __('Initializing console logs...') }}<br>
                </div>

                <!-- Step 4: Duplicate & Errors Review Section -->
                <div id="review-errors-section" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h5 class="fw-bold text-dark mb-0"><i class="ti ti-alert-triangle me-1 text-danger"></i>{{ __('Audit Log: Skipped / Failed Leads') }}</h5>
                        <button onclick="v11DownloadFailuresCSV()" class="btn btn-sm btn-dark rounded-pill px-3 shadow-sm">
                            <i class="ti ti-download me-1"></i>{{ __('Export Failures to CSV') }}
                        </button>
                    </div>
                    
                    <div class="table-responsive-premium" style="max-height: 350px;">
                        <table class="table table-premium table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 80px;" class="ps-3">{{ __('Row') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Phone') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Error Reason') }}</th>
                                </tr>
                            </thead>
                            <tbody id="errors-table-body">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="text-center mt-4" id="progress-finish-actions" style="display: none;">
                    <a href="{{ route('leads.index') }}" class="btn btn-success btn-lg px-5 rounded-pill shadow-lg"><i class="ti ti-checkbox me-1"></i>{{ __('COMPLETE & VIEW LEADS') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var fileHeader = [];
    var fileData = [];
    var totalRows = 0;
    var failedLeads = [];
    var isUploading = false;

    // Dropzone Drag-and-Drop Handlers
    var dropzone = document.getElementById('dropzone');
    if (dropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, e => {
                e.preventDefault();
                dropzone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, e => {
                e.preventDefault();
                dropzone.classList.remove('dragover');
            }, false);
        });

        dropzone.addEventListener('drop', e => {
            var dt = e.dataTransfer;
            var files = dt.files;
            v11HandleFileUpload(files);
        }, false);
    }

    // Ajax File Upload
    function v11HandleFileUpload(files) {
        if (files.length === 0 || isUploading) return;
        var file = files[0];
        
        // Validate file extension
        var ext = file.name.split('.').pop().toLowerCase();
        if (ext !== 'csv') {
            show_toastr('Error', "{{ __('Only CSV files are allowed.') }}", 'error');
            return;
        }

        isUploading = true;
        show_toastr('Info', "{{ __('Uploading file, please wait...') }}", 'info');

        var formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: "{{ route('leads.bulk.import.upload') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                isUploading = false;
                if (res.success) {
                    fileHeader = res.file_header;
                    fileData = res.file_data;
                    totalRows = res.total_rows;
                    
                    show_toastr('Success', "{{ __('File uploaded successfully!') }}", 'success');
                    v11RenderMappingUI();
                } else {
                    show_toastr('Error', res.error, 'error');
                }
            },
            error: function() {
                isUploading = false;
                show_toastr('Error', "{{ __('Server upload exception.') }}", 'error');
            }
        });
    }

    // Render Mapping Interface
    function v11RenderMappingUI() {
        $('#card-upload').hide();
        $('#card-mapping').fadeIn();

        var headerRow = $('#mapping-table-header');
        var dropdownRow = $('#mapping-table-dropdowns');
        var previewRow = $('#mapping-table-preview');

        headerRow.empty();
        dropdownRow.empty();
        previewRow.empty();

        // Columns definitions
        var optionsHtml = `
            <option value="">{{ __('Select Mapping') }}</option>
            <option value="name">{{ __('Name') }}</option>
            <option value="email">{{ __('Email') }}</option>
            <option value="phone">{{ __('Phone') }}</option>
            <option value="date">{{ __('Created Date') }}</option>
            <option value="sources">{{ __('Source') }}</option>
            <option value="user_id">{{ __('Responsible Person') }}</option>
            <option value="team">{{ __('Team') }}</option>
            <option value="created_by">{{ __('Created By') }}</option>
            <option value="pan_number">{{ __('PAN Number') }}</option>
            <option value="aadhar_number">{{ __('Aadhar Number') }}</option>
            <option value="dp_id">{{ __('DP ID') }}</option>
            @foreach($custom_fields as $field)
                <option value="custom_field_{{ $field->id }}">{{ __('Custom: ') . $field->name }}</option>
            @endforeach
        `;

        fileHeader.forEach(function(col, idx) {
            // Populate Header cell
            headerRow.append(`<th class="ps-3">${col}</th>`);

            // Populate Dropdown cell
            dropdownRow.append(`
                <td class="p-2">
                    <select name="mapping[${idx}]" class="form-select form-select-premium set-column-mapping" data-col-idx="${idx}" data-header-name="${col.toLowerCase().trim()}">
                        ${optionsHtml}
                    </select>
                </td>
            `);
        });

        // Render paginated preview rows
        previewPage = 0;
        v11RenderPreviewRows();

        // Auto Map columns
        setTimeout(v11AutoMapColumns, 300);

        // Bind dropdown change event
        $(document).on('change', '.set-column-mapping', function() {
            if ($(this).val()) {
                $(this).addClass('mapped');
            } else {
                $(this).removeClass('mapped');
            }
            v11CheckStartEligibility();
        });
    }

    // Auto mapping logic
    function v11AutoMapColumns() {
        $('.set-column-mapping').each(function() {
            var colIdx = $(this).data('col-idx');
            var header = $(this).data('header-name') || '';
            var select = $(this);

            var rules = {
                'phone': ['phone', 'mobile', 'contact', 'number', 'whatsapp', 'cell', 'contat', 'tele'],
                'email': ['email', 'mail', 'id'],
                'name': ['name', 'full name', 'first name', 'client', 'customer', 'lead', 'fname', 'lname', 'user'],
                'date': ['date', 'created', 'created date', 'created_at', 'join date'],
                'sources': ['source', 'lead source', 'lead_source', 'medium', 'campaign'],
                'user_id': ['responsible', 'owner', 'assigned', 'user', 'responsible person', 'responsible_person', 'assignee'],
                'team': ['team', 'department', 'dept', 'group', 'team name', 'team_name'],
                'created_by': ['creator', 'created by', 'created_by'],
                'pan_number': ['pan', 'pan number', 'pan_number', 'pan card', 'pancard'],
                'aadhar_number': ['aadhar', 'aadhar number', 'aadhar_number', 'aadhaar'],
                'dp_id': ['dp id', 'dp_id', 'depository participant id', 'dpid']
            };
            @foreach($custom_fields as $field)
                rules['custom_field_{{ $field->id }}'] = ['{{ strtolower($field->name) }}'];
            @endforeach

            for (var key in rules) {
                var found = false;
                rules[key].forEach(function(term) {
                    if (header === term || header.includes(term)) {
                        select.val(key).addClass('mapped');
                        found = true;
                    }
                });
                if (found) break;
            }
        });
        v11CheckStartEligibility();
    }

    // Verify if mapping is eligible to start
    function v11CheckStartEligibility() {
        var hasPhone = false;
        $('.set-column-mapping').each(function() {
            if ($(this).val() === 'phone') hasPhone = true;
        });

        if (hasPhone) {
            $('#btn-start-import').removeAttr('disabled');
        } else {
            $('#btn-start-import').attr('disabled', 'disabled');
        }
    }

    function v11CancelMapping() {
        $('#card-mapping').hide();
        $('#card-upload').fadeIn();
        fileHeader = [];
        fileData = [];
    }

    // Process chunked import
    function v11StartProcess() {
        var pipeline = $('#global_pipeline').val();
        var stage = $('#global_stage').val();
        var user = $('#global_user').val();
        var source = $('#global_source').val();

        if (!pipeline) {
            show_toastr('Warning', "{{ __('Please select a Pipeline!') }}", 'warning');
            return;
        }
        if (!stage) {
            show_toastr('Warning', "{{ __('Please select a Stage!') }}", 'warning');
            return;
        }

        // Map selections dynamically (handles custom fields and system fields alike)
        var mapping = {};
        $('.set-column-mapping').each(function() {
            var val = $(this).val();
            var colIdx = $(this).data('col-idx');
            if (val) {
                mapping['mapping_' + val] = colIdx;
            }
        });

        $('#card-mapping').hide();
        $('#card-progress').fadeIn();
        $('#stat-total').text(totalRows);

        var logConsole = $('#log-console');
        logConsole.html("[SYSTEM] Supreme Lead Upload Sequence Armed.<br>[SYSTEM] Resolving target configurations...<br>");

        failedLeads = [];
        $('#errors-table-body').empty();
        $('#review-errors-section').hide();

        function executeChunk(idx) {
            var requestData = $.extend({
                _token: '{{ csrf_token() }}',
                global_pipeline: pipeline,
                global_stage: stage,
                global_user: user,
                global_source: source,
                chunk_index: idx,
                chunk_size: 50
            }, mapping);

            $.ajax({
                url: "{{ route('leads.bulk.import.process') }}",
                type: "POST",
                data: requestData,
                success: function(res) {
                    if (res.success) {
                        var percent = Math.round((res.current / res.total) * 100);
                        $('#p-bar').css('width', percent + '%').text(percent + '%');
                        $('#stat-processed').text(res.current);
                        $('#stat-failed').text(res.failed_count);

                        logConsole.append(`[PROCESS] Chunk processed at index ${idx}... [${res.current}/${res.total}]<br>`);
                        logConsole.scrollTop(logConsole[0].scrollHeight);

                        if (res.chunk_failures && res.chunk_failures.length > 0) {
                            res.chunk_failures.forEach(function(f) {
                                failedLeads.push(f);
                                $('#errors-table-body').append(`
                                    <tr>
                                        <td class="ps-3">${f.row}</td>
                                        <td>${f.name || '-'}</td>
                                        <td>${f.phone || '-'}</td>
                                        <td>${f.email || '-'}</td>
                                        <td><span class="badge-premium-danger">${f.reason}</span></td>
                                    </tr>
                                `);
                            });
                        }

                        if (res.is_finished) {
                            logConsole.append(`[SYSTEM] Sequence execution finished. Total failures logged: ${res.failed_count}.<br>`);
                            logConsole.scrollTop(logConsole[0].scrollHeight);

                            if (failedLeads.length > 0) {
                                $('#review-errors-section').fadeIn();
                            }
                            $('#progress-finish-actions').fadeIn();
                        } else {
                            setTimeout(function() {
                                executeChunk(res.current);
                            }, 50);
                        }
                    } else {
                        logConsole.append(`<span class="text-danger">[FATAL] ${res.message}</span><br>`);
                        show_toastr('Error', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    var errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Critical Server Exception during sync.";
                    logConsole.append(`<span class="text-danger">[FATAL] ${errorMsg}</span><br>`);
                    show_toastr('Error', errorMsg, 'error');
                }
            });
        }

        executeChunk(0);
    }

    // Export failure log to CSV in the original format
    function v11DownloadFailuresCSV() {
        if (failedLeads.length === 0) return;
        
        // Reconstruct exact original CSV columns and append "Failure Reason"
        let headers = [...fileHeader, "Failure Reason"];
        
        function escapeCsvCell(val) {
            if (val === null || val === undefined) return '';
            let str = String(val);
            if (str.includes(',') || str.includes('"') || str.includes('\n') || str.includes('\r')) {
                return '"' + str.replace(/"/g, '""') + '"';
            }
            return str;
        }

        let csvContent = "\ufeff"; // Add BOM for Excel UTF-8 support
        
        // Append header row
        csvContent += headers.map(escapeCsvCell).join(",") + "\n";
        
        // Append original data rows with failure reasons
        failedLeads.forEach(function(d) {
            let rowCells = [];
            if (d.original_row && Array.isArray(d.original_row)) {
                rowCells = [...d.original_row];
            } else {
                // Fallback matching columns count if original_row is somehow missing
                fileHeader.forEach(function() {
                    rowCells.push('');
                });
            }
            
            // Append failure reason to the end
            rowCells.push(d.reason || '');
            
            csvContent += rowCells.map(escapeCsvCell).join(",") + "\n";
        });
        
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", "bulk_import_failed_leads.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Handle pipeline change to load stages
    $(document).ready(function() {
        $('#global_pipeline').on('change', function() {
            var pipeline_id = $(this).val();
            if (pipeline_id) {
                $.ajax({
                    url: '{{ route("leads.json") }}',
                    data: { pipeline_id: pipeline_id, _token: '{{ csrf_token() }}' },
                    type: 'POST',
                    success: function(data) {
                        $('#global_stage').empty().append('<option value="">{{ __("Select Stage") }}</option>');
                        $.each(data, function(key, value) {
                            $('#global_stage').append('<option value="' + key + '">' + value + '</option>');
                        });
                    }
                });
            } else {
                $('#global_stage').empty().append('<option value="">{{ __("Select Stage") }}</option>');
            }
        });
    });

    // Preview Pagination JS Implementation
    var previewPage = 0;
    var previewPageSize = 5;

    function v11RenderPreviewRows() {
        var tbody = $('#mapping-table-preview-body');
        tbody.empty();

        if (!fileData || fileData.length === 0) {
            tbody.append('<tr><td colspan="' + fileHeader.length + '" class="text-center text-muted p-4">No preview data available</td></tr>');
            $('#preview-pagination-container').attr('style', 'display: none !important;');
            return;
        }

        var start = previewPage * previewPageSize;
        var end = Math.min(start + previewPageSize, fileData.length);
        var pageRows = fileData.slice(start, end);

        pageRows.forEach(function(row) {
            var tr = $('<tr class="table-light"></tr>');
            fileHeader.forEach(function(col, idx) {
                var cellVal = (row[idx] !== undefined) ? row[idx] : '';
                tr.append(`<td class="ps-3"><small class="text-muted fw-bold">${cellVal}</small></td>`);
            });
            tbody.append(tr);
        });

        // Update info text
        $('#preview-pagination-info').html(`Showing rows <b>${start + 1}-${end}</b> of <b>${fileData.length}</b> rows parsed for mapping check`);
        $('#preview-pagination-container').attr('style', 'display: flex !important;');

        // Update buttons state
        if (previewPage === 0) {
            $('#preview-prev-btn').attr('disabled', 'disabled');
        } else {
            $('#preview-prev-btn').removeAttr('disabled');
        }

        if (end >= fileData.length) {
            $('#preview-next-btn').attr('disabled', 'disabled');
        } else {
            $('#preview-next-btn').removeAttr('disabled');
        }
    }

    function v11PrevPreviewPage() {
        if (previewPage > 0) {
            previewPage--;
            v11RenderPreviewRows();
        }
    }

    function v11NextPreviewPage() {
        if ((previewPage + 1) * previewPageSize < fileData.length) {
            previewPage++;
            v11RenderPreviewRows();
        }
    }
</script>
@endpush
