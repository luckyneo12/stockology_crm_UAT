@extends('layouts.main')

@section('page-title')
    {{ __('Global Lead Tasks') }}
@endsection

@push('css')
    @include('layouts.includes.datatable-css')
    <link rel="stylesheet" href="{{ asset('css/ui_premium.css') }}">
@endpush

@section('page-breadcrumb')
    {{ __('Lead Tasks') }}
@endsection

@section('page-action')
    <div class="d-flex">
        <a href="#" class="btn btn-sm btn-danger btn-icon m-1 d-none" id="bulk_delete_tasks" data-bs-toggle="tooltip" 
           title="{{__('Bulk Delete')}}">
            <i class="ti ti-trash text-white"></i>
        </a>
        <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-ajax-popup="true" data-url="{{ route('lead_tasks.create') }}" data-title="{{__('Create New Task / Reminder')}}">
            <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="filter-toolbar mb-4">
                <div class="row align-items-center g-3">
                    <div class="col-md-auto pe-4 border-end">
                        <h5 class="mb-0 text-primary"><i class="ti ti-filter me-1"></i>{{ __('Filters') }}</h5>
                    </div>
                    <div class="col">
                        <div class="row g-2">
                             <div class="col-md-2">
                                {{ Form::select('user_id', $users, request('user_id'), ['class' => 'form-control choices', 'id' => 'filter_user_id', 'placeholder' => __('Select User')]) }}
                            </div>
                             <div class="col-md-2">
                                {{ Form::select('lead_id', $leads, request('lead_id'), ['class' => 'form-control choices', 'id' => 'filter_lead_id', 'placeholder' => __('Select Lead')]) }}
                            </div>
                            <div class="col-md-2">
                                 <select class="form-control choices" id="filter_status">
                                    <option value="">{{ __('Select Status') }}</option>
                                    <option value="pending">{{ __('Pending') }}</option>
                                    <option value="in_progress">{{ __('In Progress') }}</option>
                                    <option value="done">{{ __('Done') }}</option>
                                    <option value="overdue">{{ __('Overdue') }}</option>
                                </select>
                            </div>
                             <div class="col-md-2">
                                 <select class="form-control choices" id="filter_priority" searchEnabled="true">
                                    <option value="">{{ __('Select Priority') }}</option>
                                    <option value="1">{{ __('Low') }}</option>
                                    <option value="2">{{ __('Medium') }}</option>
                                    <option value="3">{{ __('High') }}</option>
                                </select>
                            </div>
                             <div class="col-md-2">
                                <input type="date" class="form-control" id="filter_start_date" placeholder="{{ __('Start Date') }}">
                            </div>
                            <div class="col-md-2">
                                 <input type="date" class="form-control" id="filter_end_date" placeholder="{{ __('End Date') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        {{ $dataTable->table(['width' => '100%']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('layouts.includes.datatable-js')
    {{ $dataTable->scripts() }}

    <script>
        $(document).ready(function() {
             function drawTable() {
                var table = window.LaravelDataTables["lead-tasks-table"];
                
                table.on('preXhr.dt', function (e, settings, data) {
                    data.user_id = $('#filter_user_id').val();
                    data.lead_id = $('#filter_lead_id').val();
                    data.status = $('#filter_status').val();
                    data.priority = $('#filter_priority').val();
                    data.start_date = $('#filter_start_date').val();
                    data.end_date = $('#filter_end_date').val();
                });
                
                table.draw();
            }

            $('#filter_user_id, #filter_lead_id, #filter_status, #filter_priority, #filter_start_date, #filter_end_date').on('change', function() {
                drawTable();
            });

            // Bulk Delete Logic
            $(document).on('change', '#checkAllTasks', function() {
                $('.task-checkbox').prop('checked', $(this).prop('checked'));
                toggleBulkDeleteButton();
            });

            $(document).on('change', '.task-checkbox', function() {
                if ($('.task-checkbox:checked').length == $('.task-checkbox').length) {
                    $('#checkAllTasks').prop('checked', true);
                } else {
                    $('#checkAllTasks').prop('checked', false);
                }
                toggleBulkDeleteButton();
            });

            function toggleBulkDeleteButton() {
                if ($('.task-checkbox:checked').length > 0) {
                    $('#bulk_delete_tasks').removeClass('d-none');
                } else {
                    $('#bulk_delete_tasks').addClass('d-none');
                }
            }

            $(document).on('click', '#bulk_delete_tasks', function(e) {
                e.preventDefault();
                var ids = [];
                $('.task-checkbox:checked').each(function() {
                    ids.push($(this).val());
                });

                if (ids.length > 0) {
                    const swalWithBootstrapButtons = Swal.mixin({
                        customClass: {
                            confirmButton: 'btn btn-success',
                            cancelButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    })
                    swalWithBootstrapButtons.fire({
                        title: '{{__('Are you sure?')}}',
                        text: '{{__('This action cannot be undone. Do you want to continue?')}}',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: '{{__('Yes, delete them!')}}',
                        cancelButtonText: '{{__('No, cancel!')}}',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '{{ route('lead_tasks.bulk_destroy') }}',
                                type: 'POST',
                                data: {
                                    ids: ids,
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(data) {
                                    if (data.success) {
                                        toastrs('Success', data.message, 'success');
                                        window.LaravelDataTables["lead-tasks-table"].draw();
                                        $('#bulk_delete_tasks').addClass('d-none');
                                    } else {
                                        toastrs('Error', data.message, 'error');
                                    }
                                }
                            });
                        }
                    })
                }
            });
        });
    </script>
@endpush
