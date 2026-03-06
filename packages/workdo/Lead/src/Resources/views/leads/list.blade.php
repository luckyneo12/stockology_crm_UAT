@extends('layouts.main')

@section('page-title')
    {{ __('Manage Leads') }} @if ($pipeline)
        - {{ $pipeline->name }}
    @endif
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
    <link rel="stylesheet" href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}">
    <style>
        .bulk-action-bar {
            display: none;
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            box-shadow: 0 -5px 25px rgba(0, 0, 0, 0.15);
            padding: 15px 25px;
            border-radius: 50px;
            z-index: 9999;
            align-items: center;
            border: 1px solid rgba(0, 0, 0, 0.05);
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                bottom: -100px;
                opacity: 0;
            }

            to {
                bottom: 20px;
                opacity: 1;
            }
        }

        .bulk-action-bar .btn {
            border-radius: 20px;
            margin: 0 5px;
            display: inline-flex;
            align-items: center;
        }

        .selection-count {
            font-weight: 700;
            color: #0d6efd;
            margin-right: 15px;
            padding-right: 15px;
            border-right: 1px solid #eee;
        }
    </style>
@endpush

@section('page-breadcrumb')
    {{ __('Leads') }}
@endsection
@section('page-action')
    <div class="d-flex">
        @if ($pipeline)
            <div class="col-auto me-3">
                {{ Form::open(['id' => 'change-pipeline']) }}
                {{ Form::select('default_pipeline_id', $pipelines, $pipeline->id, ['class' => 'form-control custom-form-select mx-2', 'id' => 'default_pipeline_id']) }}
                {{ Form::close() }}
            </div>
        @endif
        <div class="col-auto pt-2" style="display: inline-table;">
            @stack('addButtonHook')
        </div>
        @permission('lead import')
        <div class="col-auto pt-2">
            <a class="btn btn-sm btn-primary btn-icon me-2" data-ajax-popup="true" data-title="{{ __('Lead Import') }}"
                data-url="{{ route('lead.file.import') }}" data-size="md" data-toggle="tooltip"
                title="{{ __('Import') }}"><i class="ti ti-file-import"></i>
            </a>
        </div>
        @endpermission
        <div class="col-auto pt-2">
            <a href="{{ route('leads.index') }}" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Kanban View') }}" class="btn btn-sm btn-primary btn-icon me-2"><i class="ti ti-table"></i>
            </a>
        </div>
        @permission('lead create')
        <div class="col-auto pt-2">
            <a class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Create Lead') }}" data-ajax-popup="true" data-size="lg" data-title="{{ __('Create Lead') }}"
                data-url="{{ route('leads.create') }}"><i class="ti ti-plus text-white"></i></a>
        </div>
        @endpermission

        <!-- Column Selection Dropdown -->
        <div class="col-auto pt-2 ms-2">
            <div class="dropdown">
                <button class="btn btn-sm btn-primary btn-icon dropdown-toggle hide-arrow" type="button"
                    data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('View Columns') }}">
                    <i class="ti ti-layout-grid"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 250px;">
                    <h6 class="dropdown-header px-0 mb-2">{{ __('Showing / Hiding Columns') }}</h6>
                    <div id="column-selector-list">
                        <!-- Will be populated by JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    @include('lead::leads.filter_bar')
    @if ($pipeline)
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body table-border-style">
                        <h5></h5>
                        <div class="table-responsive">
                            {{ $dataTable->table(['width' => '100%']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Bulk Action Bar -->
    <div class="bulk-action-bar" id="bulk-action-bar">
        <span class="selection-count"><span id="selected-count">0</span> {{ __('Selected') }}</span>

        <button class="btn btn-primary btn-sm" id="bulk-change-stage">
            <i class="ti ti-arrow-forward-up me-1"></i> {{ __('Change Stage') }}
        </button>

        <button class="btn btn-info btn-sm" id="bulk-change-owner">
            <i class="ti ti-user me-1"></i> {{ __('Change Responsible') }}
        </button>

        <button class="btn btn-danger btn-sm" id="bulk-delete">
            <i class="ti ti-trash me-1"></i> {{ __('Delete') }}
        </button>

        <button class="btn btn-warning btn-sm" id="bulk-task-reminder">
            <i class="ti ti-calendar-event me-1"></i> {{ __('Add Task/Reminder') }}
        </button>

        <button class="btn btn-light btn-sm ms-3" id="clear-selection">
            <i class="ti ti-x me-1"></i> {{ __('Clear') }}
        </button>
    </div>
@endsection
@push('scripts')
    @include('layouts.includes.datatable-js')
    {{ $dataTable->scripts() }}

    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
    <script>
        var selectedLeads = [];
        $(document).ready(function () {

            function updateBulkBar() {
                var count = selectedLeads.length;
                $('#selected-count').text(count);
                if (count > 0) {
                    $('#bulk-action-bar').css('display', 'flex');
                } else {
                    $('#bulk-action-bar').hide();
                }
            }

            // Master Checkbox
            $(document).on('change', '#checkAll', function () {
                var isChecked = $(this).prop('checked');
                $('.lead-checkbox').prop('checked', isChecked);

                $('.lead-checkbox').each(function () {
                    var id = $(this).val();
                    if (isChecked) {
                        if (!selectedLeads.includes(id)) selectedLeads.push(id);
                    } else {
                        selectedLeads = selectedLeads.filter(item => item !== id);
                    }
                });

                if (selectedLeads.length > 500) {
                    toastrs('warning', @json(__("Maximum 500 leads can be selected at once.")), 'warning');
                    selectedLeads = selectedLeads.slice(0, 500);
                    // Uncheck those beyond 500
                    $('.lead-checkbox').each(function (index) {
                        if (index >= 500) $(this).prop('checked', false);
                    });
                }

                updateBulkBar();
            });

            // Individual Checkbox
            $(document).on('change', '.lead-checkbox', function () {
                var id = $(this).val();
                if ($(this).prop('checked')) {
                    if (selectedLeads.length >= 500) {
                        $(this).prop('checked', false);
                        toastrs('warning', @json(__("Maximum 500 leads can be selected at once.")), 'warning');
                        return;
                    }
                    if (!selectedLeads.includes(id)) selectedLeads.push(id);
                } else {
                    selectedLeads = selectedLeads.filter(item => item !== id);
                    $('#checkAll').prop('checked', false);
                }
                updateBulkBar();
            });

            // Clear Selection
            $(document).on('click', '#clear-selection', function () {
                selectedLeads = [];
                $('.lead-checkbox, #checkAll').prop('checked', false);
                updateBulkBar();
            });

            // Bulk actions methods
            function executeBulkAction(type, value = null) {
                if (selectedLeads.length === 0) return;

                var message = @json(__('Are you sure you want to perform this action on selected leads?'));
                if (type === 'delete') message = @json(__('Are you sure you want to delete selected leads? This action cannot be undone.'));

                if (confirm(message)) {
                    $.ajax({
                        url: '{{ route("leads.bulk.action") }}',
                        type: 'POST',
                        data: {
                            ids: selectedLeads,
                            action: type,
                            value: value,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (data) {
                            if (data.success) {
                                toastrs('success', data.message, 'success');
                                window.LaravelDataTables["leads-table"].draw();
                                selectedLeads = [];
                                updateBulkBar();
                            } else {
                                toastrs('error', data.message, 'error');
                            }
                        }
                    });
                }
            }

            // Bulk Delete
            $('#bulk-delete').on('click', function () {
                executeBulkAction('delete');
            });

            // Change Stage Modal
            $('#bulk-change-stage').on('click', function () {
                var stages = @json($stages);
                var options = '';
                $.each(stages, function (id, name) {
                    options += `<option value="${id}">${name}</option>`;
                });

                var html = `
                        <div class="modal-body p-3" style="min-height: 200px;">
                            <div class="form-group mb-3 text-start">
                                <label class="form-label text-dark fw-bold">@json(__('Select Target Stage'))</label>
                                <select class="form-control" id="target_stage">
                                    ${options}
                                </select>
                            </div>
                            <div class="text-end mt-4 border-top pt-3">
                                <button class="btn btn-secondary me-2 px-3" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                <button class="btn btn-primary px-4" id="confirm-stage-change">{{ __('Apply Changes') }}</button>
                            </div>
                        </div>
                    `;
                $('#commonModal .modal-title').html('{{ __("Bulk Change Lead Stage") }}');
                $('#commonModal .body').html(html);
                $('#commonModal').modal('show');

                // Initialize Choices with a small delay to ensure modal is rendering
                setTimeout(function () {
                    new Choices('#target_stage', { searchEnabled: true, shouldSort: false });
                }, 100);
            });

            $(document).on('click', '#confirm-stage-change', function () {
                var stageId = $('#target_stage').val();
                if (!stageId) {
                    toastrs('error', @json(__("Please select a stage")), 'error');
                    return;
                }
                $('#commonModal').modal('hide');
                executeBulkAction('change_stage', stageId);
            });

            // Change Owner Modal
            $('#bulk-change-owner').on('click', function () {
                var users = @json($users);
                var options = '';
                $.each(users, function (id, name) {
                    options += `<option value="${id}">${name}</option>`;
                });

                var html = `
                        <div class="modal-body p-3" style="min-height: 200px;">
                            <div class="form-group mb-3 text-start">
                                <label class="form-label text-dark fw-bold">@json(__('Select Responsible Person'))</label>
                                <select class="form-control" id="target_owner">
                                    ${options}
                                </select>
                            </div>
                            <div class="text-end mt-4 border-top pt-3">
                                <button class="btn btn-secondary me-2 px-3" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                <button class="btn btn-primary px-4" id="confirm-owner-change">{{ __('Reassign Leads') }}</button>
                            </div>
                        </div>
                    `;
                $('#commonModal .modal-title').html('{{ __("Bulk Change Responsible Person") }}');
                $('#commonModal .body').html(html);
                $('#commonModal').modal('show');

                // Initialize Choices
                setTimeout(function () {
                    new Choices('#target_owner', { searchEnabled: true, shouldSort: false });
                }, 100);
            });

            $(document).on('click', '#confirm-owner-change', function () {
                var userId = $('#target_owner').val();
                if (!userId) {
                    toastrs('error', @json(__("Please select a user")), 'error');
                    return;
                }
                $('#commonModal').modal('hide');
                executeBulkAction('change_owner', userId);
            });

            // Bulk Task/Reminder Modal
            $('#bulk-task-reminder').on('click', function () {
                if (selectedLeads.length === 0) return;

                $.ajax({
                    url: '{{ route("leads.bulk.task.reminder.create") }}',
                    type: 'POST',
                    data: {
                        ids: selectedLeads,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.error) {
                            toastrs('error', response.error, 'error');
                        } else {
                            $('#commonModal .modal-title').html('{{ __("Bulk Task & Reminder Creation") }}');
                            $('#commonModal .body').html(response);
                            $('#commonModal').modal('show');
                        }
                    },
                    error: function (response) {
                        toastrs('error', response.responseJSON.error, 'error');
                    }
                });
            });
        });

        // Advanced Column Selection & Masking Logic
        $(document).on('click', '.reveal-link', function (e) {
            e.preventDefault();
            var $this = $(this);
            var url = $this.data('url');
            var target = $this.data('target');

            $.ajax({
                url: url,
                type: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    if (response.is_success) {
                        $(target).text(response.value).removeClass('masked-value');
                        $this.remove();
                    } else {
                        toastrs('error', response.error || @json(__('Failed to reveal field')), 'error');
                    }
                },
                error: function (xhr) {
                    toastrs('error', xhr.responseJSON.error || @json(__('Permission Denied')), 'error');
                }
            });
        });

        // Initialize Column Selection UI
        function initColumnSelector() {
            var table = window.LaravelDataTables["leads-table"];
            var columns = table.columns().settings()[0].aoColumns;
            var listHtml = '';

            columns.forEach(function (col, index) {
                // Skip batch, index, and action columns
                if (col.name === 'batch' || col.name === 'DT_RowIndex' || col.name === 'action' || !col.sTitle) return;

                var title = col.sTitle;
                var isVisible = table.column(index).visible();
                var checked = isVisible ? 'checked' : '';

                listHtml += `
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_toggle_${index}" data-column="${index}" ${checked}>
                            <label class="form-check-label" for="col_toggle_${index}">${title}</label>
                        </div>
                    `;
            });

            $('#column-selector-list').html(listHtml);
        }

        // Handle Column Toggle
        $(document).on('change', '.column-toggle', function () {
            var colIdx = $(this).data('column');
            var isVisible = $(this).prop('checked');
            var table = window.LaravelDataTables["leads-table"];

            table.column(colIdx).visible(isVisible);
        });

        // Re-init column selector after table init or state load
        $('#leads-table').on('init.dt', function () {
            initColumnSelector();
        });

        // If table already initialized
        if ($.fn.DataTable.isDataTable('#leads-table')) {
            initColumnSelector();
        }
    </script>
@endpush