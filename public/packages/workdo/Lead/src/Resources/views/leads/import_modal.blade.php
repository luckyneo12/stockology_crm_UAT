<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.4);
        --premium-red: #dc3545;
        --v11-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
    }

    #v11-modal-body-container {
        font-family: 'Outfit', sans-serif;
        background: radial-gradient(circle at top right, #fff5f5, #ffffff);
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: 15px;
        box-shadow: var(--v11-shadow);
        transition: all 0.3s ease;
    }

    .glass-card:hover {
        transform: translateY(-2px);
    }

    #v11-header-banner {
        background: linear-gradient(90deg, #dc3545, #9b1c2e) !important;
        border: none !important;
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .set_column_data.mapped {
        border: 2px solid #28a745 !important;
        background-color: #f0fff4 !important;
    }

    .set_column_data:focus {
        box-shadow: 0 0 0 0.25 margin-rgba(220, 53, 69, 0.25);
    }

    #v11-p-bar {
        transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1) !important;
        background: linear-gradient(90deg, #dc3545, #ff4d5a) !important;
        box-shadow: 0 0 10px rgba(220, 53, 69, 0.5);
    }

    .import-data-table {
        border-radius: 15px;
        box-shadow: inset 0 2px 10px rgba(0,0,0,0.05);
    }

    .table thead th {
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 700;
        color: #444;
    }
</style>

<div class="modal-body" id="v11-modal-body-container">
    <div id="v11-header-banner" style="background: #dc3545; color: white; padding: 12px; text-align: center; font-weight: 900; border-radius: 8px; margin-bottom: 20px;">
        � V11 SUPREME IMPORT CONSOLE �
    </div>
    
    <!-- Mapping Area -->
    <div id="v11-mapping-area">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card glass-card">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-dark">{{ __('Responsible Person') }}</label>
                                <select name="global_user" id="global_user" class="form-select border-0 shadow-sm" style="background: rgba(0,0,0,0.02);">
                                    <option value="">{{ __('Select Responsible Person') }}</option>
                                    @foreach($users as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-dark">{{ __('Pipeline') }}</label>
                                <select name="global_pipeline" id="global_pipeline" class="form-select border-0 shadow-sm" style="background: rgba(0,0,0,0.02);">
                                    <option value="">{{ __('Select Pipeline') }}</option>
                                    @foreach($pipelines as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-dark">{{ __('Stage') }}</label>
                                <select name="global_stage" id="global_stage" class="form-select border-0 shadow-sm" style="background: rgba(0,0,0,0.02);">
                                    <option value="">{{ __('Select Stage') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-dark">{{ __('Source') }}</label>
                                <select name="global_source" id="global_source" class="form-select border-0 shadow-sm" style="background: rgba(0,0,0,0.02);">
                                    <option value="">{{ __('Select Source') }}</option>
                                    @foreach($sources as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="glass-card p-1">
                    <div id="process_area" class="overflow-auto import-data-table" style="max-height: 400px; border: none;">
                        @if(isset($file_header) && count($file_header) > 0)
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    @foreach($file_header as $column)
                                        <th class="ps-3">{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="background: #fdfdfd;">
                                    @foreach($file_header as $key => $column)
                                        <td class="p-2">
                                            <select name="column_map[{{ $key }}]" class="form-select set_column_data shadow-sm" data-column_number="{{ $key }}" data-header-name="{{ strtolower($column) }}">
                                                <option value="">{{ __('Select Mapping') }}</option>
                                                <option value="name">{{ __('Name') }}</option>
                                                <option value="email">{{ __('Email') }}</option>
                                                <option value="phone">{{ __('Phone') }}</option>
                                            </select>
                                        </td>
                                    @endforeach
                                </tr>
                                @if(isset($file_data) && isset($file_data[0]))
                                    <tr class="table-light">
                                        @foreach($file_data[0] as $preview_val)
                                            <td class="ps-3"><small class="text-muted fw-bold">{{ $preview_val }}</small></td>
                                        @endforeach
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        @else
                            <div class="text-center p-5">
                                <i class="ti ti-file-off fs-1 text-danger"></i>
                                <h5 class="mt-3 text-danger fw-bold">{{ __('Session data lost or file expired.') }}</h5>
                                <p class="text-muted small">Please re-upload your file to continue.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Area (Hidden by default) -->
    <div id="v11-progress-area" style="display:none;" class="glass-card p-4">
        <h3 class="text-center fw-bold mb-4" style="color: var(--premium-red); letter-spacing: 2px;">V11 POWER IMPORT IN PROGRESS</h3>
        
        <div style="background: #e9ecef; height: 35px; border-radius: 20px; overflow: hidden; margin-bottom: 25px; box-shadow: inset 0 2px 5px rgba(0,0,0,0.1);">
            <div id="v11-p-bar" style="width: 0%; height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 15px; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">0%</div>
        </div>

        <div class="row text-center mb-4">
            <div class="col-6 border-end">
                <div class="text-muted small mb-1 uppercase fw-bold">Items Processed</div>
                <div style="font-size: 28px; font-weight: 900; color: #333;"><span id="v11-p-count">0</span></div>
            </div>
            <div class="col-6">
                <div class="text-muted small mb-1 uppercase fw-bold">Duplicates Found</div>
                <div style="font-size: 28px; font-weight: 900; color: var(--premium-red);"><span id="v11-p-dup-count">0</span></div>
            </div>
        </div>

        <div id="v11-p-msg" class="text-center p-3 mb-4 rounded" style="background: #f8f9fa; font-family: 'JetBrains Mono', monospace; font-size: 13px; color: #666; border-left: 4px solid #dc3545;">Initializing Supreme Sequence...</div>

        <!-- Duplicate Review Step (Hidden initially) -->
        <div id="v11-duplicate-review" style="display:none; margin-top: 20px;" class="p-3 border-top">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 text-dark fw-bold">⚠️ DUPLICATE LOG REPORT</h5>
                <button onclick="v11DownloadDuplicatesCSV()" class="btn btn-dark btn-sm rounded-pill px-3 shadow">
                    <i class="ti ti-download me-1"></i> EXPORT TO CSV
                </button>
            </div>
            <div class="table-responsive rounded shadow-sm" style="max-height: 250px; border: 1px solid #eee;">
                <table class="table table-sm table-hover mb-0 bg-white">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Row</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th class="text-danger">Issue</th>
                        </tr>
                    </thead>
                    <tbody id="v11-duplicate-table-body">
                        <!-- Rows added dynamically -->
                    </tbody>
                </table>
            </div>
        </div>

        <div id="v11-p-finish" style="display:none; margin-top: 30px; text-align: center;">
            <button onclick="location.reload()" class="btn btn-success btn-lg px-5 rounded-pill shadow-lg">✅ COMPLETE IMPORT</button>
        </div>
    </div>
</div>

<div class="modal-footer border-0" id="v11-modal-footer">
    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="button" id="v11-start-btn" class="btn btn-danger btn-lg ms-2 rounded-pill px-5 shadow-lg" disabled onclick="v11ForceStart()">START IMPORT �</button>
</div>

<script>
    console.log("V11 V3 SUPREME: ARMED");

    var allDuplicates = [];

    function v11CheckStartAbility() {
        var phone = false;
        $('.set_column_data').each(function() { 
            if ($(this).val() == 'phone') phone = true; 
        });

        if (phone) {
            $('#v11-start-btn').removeAttr('disabled').removeClass('btn-secondary').addClass('btn-danger');
            console.log("V11: START button enabled (Phone mapped)");
        } else {
            $('#v11-start-btn').attr('disabled', 'disabled');
        }
    }

    function v11AutoMap() {
        console.log("V11: Running Auto-Mapping Sequence");
        $(".set_column_data").each(function() {
            var col_num = $(this).data('column_number');
            var header = $(this).data('header-name') || '';
            var select = $(this);
            
            var rules = {
                'phone': ['phone', 'mobile', 'contact', 'number', 'whatsapp', 'cell', 'contat', 'tele'],
                'email': ['email', 'mail', 'id'],
                'name': ['name', 'full name', 'first name', 'client', 'customer', 'lead', 'fname', 'lname', 'user']
            };

            for (var key in rules) {
                var found = false;
                rules[key].forEach(function(term) {
                    if (header.includes(term)) {
                        select.val(key).addClass('mapped');
                        found = true;
                    }
                });
                if (found) break;
            }
        });
        v11CheckStartAbility();
    }

    function v11ForceStart() {
        console.log("V11 V3: Force Start Sequence Initiated");
        allDuplicates = []; 
        $('#v11-duplicate-table-body').empty();
        $('#v11-duplicate-review').hide();
        
        var pipeline = $('#global_pipeline').val();
        var stage = $('#global_stage').val();
        var user = $('#global_user').val();
        var source = $('#global_source').val();
        
        if (!pipeline) { 
            toastrs('Warning', 'Please select a Pipeline!', 'warning');
            return; 
        }

        var mapping = {};
        $(".set_column_data").each(function() {
            var v = $(this).val();
            if (v) mapping[v] = $(this).data('column_number');
        });

        if (!mapping.phone) { 
            toastrs('Warning', 'Mapping for Phone No is mandatory!', 'warning');
            return; 
        }

        $('#v11-mapping-area').hide();
        $('#v11-modal-footer').hide();
        $('#v11-header-banner').text("⚡ SUPREME PROCESSING COMMENCED ⚡").css('background', '#000');
        $('#v11-progress-area').fadeIn();

        function run(idx) {
            $.ajax({
                url: "{{ route('lead.import.data') }}?v11_v3_rev_dup=1",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    global_pipeline: pipeline, global_stage: stage, global_user: user, global_source: source,
                    name: mapping.name, email: mapping.email, phone: mapping.phone,
                    is_chunk: true, chunk_index: idx, chunk_size: 50
                },
                success: function(res) {
                    if (res.success) {
                        var p = Math.round((res.current / res.total) * 100);
                        $('#v11-p-bar').css('width', p + '%').text(p + '%');
                        $('#v11-p-count').text(res.current + ' / ' + res.total);
                        $('#v11-p-dup-count').text(res.duplicates_count);
                        $('#v11-p-msg').text(`PROCESSING BATCH AT IDX ${idx}... [${res.current}/${res.total}]`);

                        if (res.chunk_duplicates && res.chunk_duplicates.length > 0) {
                            res.chunk_duplicates.forEach(function(d) {
                                allDuplicates.push(d);
                            });
                        }

                        if (res.is_finished) {
                            $('#v11-header-banner').css('background', '#28a745').text("✨ IMPORT COMPLETE ✨");
                            $('#v11-p-msg').html(`<b>ALL DATA SYNCED.</b> Found <b>${res.duplicates_count}</b> existing leads.`).css('color', '#28a745');
                            
                            if (allDuplicates.length > 0) {
                                $('#v11-duplicate-table-body').empty();
                                let limit = Math.min(allDuplicates.length, 100);
                                for(let i=0; i<limit; i++) {
                                    let d = allDuplicates[i];
                                    $('#v11-duplicate-table-body').append(`
                                        <tr>
                                            <td class="ps-3">${d.row}</td>
                                            <td>${d.name || '-'}</td>
                                            <td>${d.phone || '-'}</td>
                                            <td class="text-danger fw-bold">${d.reason}</td>
                                        </tr>
                                    `);
                                }
                                if (allDuplicates.length > 100) {
                                    $('#v11-duplicate-table-body').append('<tr><td colspan="4" class="text-center fw-bold py-3 text-muted">... AND ' + (allDuplicates.length - 100) + ' MORE. DOWNLOAD CSV REPORT.</td></tr>');
                                }
                                $('#v11-duplicate-review').slideDown();
                            }
                            $('#v11-p-finish').fadeIn();
                        } else {
                            setTimeout(function() { run(res.current); }, 30);
                        }
                    } else {
                        toastrs('Error', res.message, 'error');
                        setTimeout(() => location.reload(), 2000);
                    }
                },
                error: function() {
                    toastrs('Error', 'Critical Server Exception during sync.', 'error');
                }
            });
        }
        run(0);
    }

    $(document).ready(function() {
        // Run Auto-Map
        setTimeout(v11AutoMap, 500);
        
        $('.set_column_data').on('change', function() {
            if ($(this).val()) $(this).addClass('mapped');
            else $(this).removeClass('mapped');
            v11CheckStartAbility();
        });

        $('#global_pipeline').on('change', function() {
            var pipeline_id = $(this).val();
            if (pipeline_id) {
                $.ajax({
                    url: '{{ route("leads.json") }}',
                    data: { pipeline_id: pipeline_id, _token: $('meta[name="csrf-token"]').attr('content') },
                    type: 'POST',
                    success: function(data) {
                        $('#global_stage').empty().append('<option value="">{{ __("Select Stage") }}</option>');
                        $.each(data, function(key, value) {
                            $('#global_stage').append('<option value="' + key + '">' + value + '</option>');
                        });
                    }
                });
            }
        });
    });

    function v11DownloadDuplicatesCSV() {
        if (allDuplicates.length === 0) return;
        let csvContent = "\ufeffRow,Name,Email,Phone,Reason\n";
        allDuplicates.forEach(function(d) {
            let rowFormatted = [
                d.row,
                `"${(d.name || '').replace(/"/g, '""')}"`,
                `"${(d.email || '').replace(/"/g, '""')}"`,
                `"${(d.phone || '').replace(/"/g, '""')}"`,
                `"${(d.reason || '').replace(/"/g, '""')}"`
            ].join(",");
            csvContent += rowFormatted + "\n";
        });
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", "supreme_duplicate_audit.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
