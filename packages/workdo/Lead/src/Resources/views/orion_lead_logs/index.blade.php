@extends('layouts.main')

@section('page-title')
    {{ __('Orion EKYC & Modification API Logs') }}
@endsection

@section('page-breadcrumb')
    {{ __('CRM') }},
    {{ __('Orion API Logs') }}
@endsection

@section('content')
    <div class="row" style="font-family: 'Outfit', sans-serif;">
        <div class="col-sm-12">
            <div class="card orion-logs-card shadow-sm border-0">
                <div class="card-body p-4">
                    <!-- Header Actions -->
                    <div class="row align-items-center mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h5 class="mb-1 fw-bold text-dark d-flex align-items-center">
                                <i class="ti ti-api text-primary me-2 fs-3"></i> {{ __('Orion EKYC Integration Logs') }}
                            </h5>
                            <p class="text-xs text-muted mb-0" id="search-status">{{ __('Showing all Orion API transaction activities') }}</p>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 justify-content-md-end">
                                <!-- Quick Search -->
                                <div class="input-group search-container glass-effect shadow-sm flex-grow-1" style="max-width: 400px;">
                                    <span class="input-group-text bg-transparent border-0 pe-1">
                                        <i class="ti ti-search text-muted"></i>
                                    </span>
                                    <input type="text" id="orion-search" class="form-control bg-transparent border-0 ps-1 py-2" placeholder="{{ __('Quick search by Client Code, Lead, Status...') }}">
                                    <button class="btn btn-outline-secondary border-0" type="button" id="clear-search">
                                        <i class="ti ti-circle-x text-muted f-16"></i>
                                    </button>
                                </div>
                                <!-- Filter Collapse Toggle -->
                                <button class="btn btn-light-primary border-0 p-0 d-flex align-items-center justify-content-center" type="button" data-bs-toggle="collapse" data-bs-target="#advanced-filter-card" aria-expanded="false" aria-controls="advanced-filter-card" style="border-radius: 12px; width: 43px; height: 43px;" title="{{ __('Advanced Filter') }}">
                                    <i class="ti ti-filter text-primary animate-hover-bounce" style="font-size: 1.15rem;"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Collapsible Advanced Filter Section -->
                    <div class="collapse {{ request()->has('start_date') || request()->has('status') || request()->has('api_type') ? 'show' : '' }} mb-4" id="advanced-filter-card">
                        <div class="card bg-light border-0 shadow-none mb-0" style="border-radius: 12px; background-color: #f8fafc !important;">
                            <div class="card-body p-3">
                                {{ Form::open(['route' => ['orion-lead-logs.index'], 'method' => 'GET', 'id' => 'orion_filter_form']) }}
                                <div class="row g-3 align-items-end">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="form-group mb-0">
                                            {{ Form::label('api_type', __('API Type'), ['class' => 'form-label fw-bold text-dark text-xs mb-1']) }}
                                            {{ Form::select('api_type', ['' => __('All API Types'), 'fetch_details' => __('Fetch Details (GET)'), 'post_ekyc' => __('Post EKYC (POST)'), 'post_modify' => __('Post Modification (POST)')], request('api_type'), ['class' => 'form-select form-control-sm', 'id' => 'api_type']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="form-group mb-0">
                                            {{ Form::label('status', __('Status'), ['class' => 'form-label fw-bold text-dark text-xs mb-1']) }}
                                            {{ Form::select('status', ['' => __('All Statuses'), 'pending' => __('Pending'), 'success' => __('Success'), 'failed' => __('Failed')], request('status'), ['class' => 'form-select form-control-sm', 'id' => 'status']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="form-group mb-0">
                                            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label fw-bold text-dark text-xs mb-1']) }}
                                            {{ Form::text('start_date', request('start_date'), ['class' => 'form-control form-control-sm flatpickr-input', 'placeholder' => __('YYYY-MM-DD')]) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="form-group mb-0">
                                            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label fw-bold text-dark text-xs mb-1']) }}
                                            {{ Form::text('end_date', request('end_date'), ['class' => 'form-control form-control-sm flatpickr-input', 'placeholder' => __('YYYY-MM-DD')]) }}
                                        </div>
                                    </div>
                                    <div class="col-auto d-flex gap-1">
                                        <button class="btn btn-sm btn-primary p-2 d-flex align-items-center justify-content-center" type="submit" data-bs-toggle="tooltip" title="{{ __('Apply Filters') }}" style="border-radius: 8px; width: 34px; height: 34px; background-color: #6f42c1 !important; border-color: #6f42c1 !important;">
                                            <i class="ti ti-filter" style="font-size: 1rem;"></i>
                                        </button>
                                        <a href="{{ route('orion-lead-logs.index') }}" class="btn btn-sm btn-danger text-white p-2 d-flex align-items-center justify-content-center" data-bs-toggle="tooltip" title="{{ __('Reset Filters') }}" style="border-radius: 8px; width: 34px; height: 34px;">
                                            <i class="ti ti-refresh" style="font-size: 1rem;"></i>
                                        </a>
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>

                    <!-- Logs Table -->
                    <div class="table-responsive">
                        <table class="table orion-logs-table mb-0" id="orion-logs-table-el">
                            <thead>
                                <tr>
                                    <th>{{ __('Client Code') }}</th>
                                    <th>{{ __('CRM Lead') }}</th>
                                    <th>{{ __('API Type') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Timestamp') }}</th>
                                    <th class="text-end" width="120px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $company_settings = getCompanyAllSetting();
                                    $timezone = !empty($company_settings['defult_timezone']) ? $company_settings['defult_timezone'] : 'UTC';
                                @endphp
                                @forelse($logs as $data)
                                    @php
                                        $lead = $data->lead;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge bg-light-purple text-purple border border-purple border-opacity-10 py-1.5 px-3 rounded-pill fw-bold" style="font-size: 0.8rem;">
                                                <i class="ti ti-hash me-1"></i>
                                                {{ $data->client_code ?? __('N/A') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($lead)
                                                @php
                                                    $name = $lead->name ?? __('Unknown');
                                                    $initials = 'NA';
                                                    if (!empty($name) && $name !== __('N/A')) {
                                                        $words = explode(' ', $name);
                                                        $initials = strtoupper(substr($words[0], 0, 1));
                                                        if (count($words) > 1) {
                                                            $initials .= strtoupper(substr($words[1], 0, 1));
                                                        } else {
                                                            $initials .= strtoupper(substr($words[0], 1, 1));
                                                        }
                                                    }
                                                    $colors = ['bg-purple-soft text-purple', 'bg-success-soft text-success', 'bg-warning-soft text-warning', 'bg-danger-soft text-danger', 'bg-info-soft text-info'];
                                                    $colorIndex = (ord(substr($name, 0, 1)) + ord(substr($name, -1))) % count($colors);
                                                    $avatarClass = $colors[$colorIndex];
                                                @endphp
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-3 {{ $avatarClass }}">
                                                        {{ $initials }}
                                                    </div>
                                                    <div>
                                                        <a href="{{ route('leads.show', $lead->id) }}" class="d-block text-dark fw-bold hover-underline-purple" style="font-size: 0.9rem;">
                                                            {{ $name }}
                                                        </a>
                                                        <span class="text-muted d-block" style="font-size: 0.78rem;">
                                                            @if($lead->email)
                                                                <i class="ti ti-mail me-1 text-muted" style="font-size: 0.85rem;"></i>{{ $lead->email }}
                                                            @endif
                                                            @if($lead->phone)
                                                                <i class="ti ti-phone ms-2 me-1 text-muted" style="font-size: 0.85rem;"></i>{{ $lead->phone }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted fst-italic">{{ __('Lead Deleted') }} (ID: {{ $data->lead_id }})</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $apiLabel = ucwords(str_replace('_', ' ', $data->api_type));
                                                $apiIcon = $data->api_type == 'fetch_details' ? 'ti-download' : 'ti-upload';
                                            @endphp
                                            <span class="fw-semibold text-xs text-dark py-1.5 px-2.5 rounded bg-light border d-inline-flex align-items-center gap-1.5">
                                                <i class="ti {{ $apiIcon }} text-purple" style="font-size: 0.9rem;"></i>
                                                {{ $apiLabel }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($data->status == 'success')
                                                <span class="badge-status badge-status-success">{{ __('Success') }}</span>
                                            @elseif($data->status == 'pending')
                                                <span class="badge-status badge-status-pending">{{ __('Pending') }}</span>
                                            @else
                                                <span class="badge-status badge-status-failed" data-bs-toggle="tooltip" title="{{ $data->error_reason ?? __('API error encountered.') }}">{{ __('Failed') }}</span>
                                                @if($data->error_reason)
                                                    <div class="mt-2 text-danger fw-semibold d-flex align-items-center gap-1" style="font-size: 0.72rem; line-height: 1.2; max-width: 250px; white-space: normal;">
                                                        <i class="ti ti-alert-triangle f-14 text-danger animate-pulse"></i>
                                                        <span>{{ Str::limit($data->error_reason, 120) }}</span>
                                                    </div>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $dt = new \DateTime($data->created_at, new \DateTimeZone('UTC'));
                                                $dt->setTimezone(new \DateTimeZone($timezone));
                                            @endphp
                                            <div class="d-flex flex-column">
                                                <span class="text-dark fw-semibold" style="font-size: 0.85rem;">
                                                    <i class="ti ti-calendar-event me-1 text-muted" style="font-size: 0.9rem;"></i>
                                                    {{ $dt->format('d-m-Y') }}
                                                </span>
                                                <span class="text-muted" style="font-size: 0.75rem; margin-left: 17px;">
                                                    <i class="ti ti-clock me-1 text-muted" style="font-size: 0.75rem;"></i>
                                                    {{ $dt->format('h:i A') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            @permission('crm manage')
                                                <a href="#" class="btn-action btn-action-view" data-url="{{ route('orion-lead-logs.payload', $data->id) }}" data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip" title="{{__('View JSON Payload')}}" data-title="{{__('Orion Transmission Payloads')}}">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            @endpermission
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex align-items-center justify-content-center mb-3 mx-auto rounded-circle"
                                                 style="width: 64px; height: 64px; background: rgba(111,66,193,0.08);">
                                                <i class="ti ti-database-off text-purple" style="font-size: 28px;"></i>
                                            </div>
                                            <h6 class="text-dark fw-bold mb-1">{{ __('No Orion log records found') }}</h6>
                                            <p class="text-xs text-muted mb-0">{{ __('All manual and automatic Orion API executions will be logged here.') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($logs instanceof \Illuminate\Pagination\LengthAwarePaginator && $logs->hasPages())
                        <div class="card-footer bg-transparent border-0 d-flex flex-column flex-md-row align-items-center justify-content-between pt-4 pb-0 px-0 gap-3">
                            <div class="text-xs text-muted">
                                {{ __('Showing') }} <span class="fw-semibold text-dark">{{ $logs->firstItem() }}</span> {{ __('to') }} <span class="fw-semibold text-dark">{{ $logs->lastItem() }}</span> {{ __('of') }} <span class="fw-semibold text-dark">{{ $logs->total() }}</span> {{ __('entries') }}
                            </div>
                            <div class="pagination-wrapper">
                                {!! $logs->appends(request()->query())->links('pagination::bootstrap-5') !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const $searchInput = $('#orion-search');
        const $tableBody = $('#orion-logs-table-el tbody');
        const $rows = $tableBody.find('tr');
        const $statusText = $('#search-status');
        const $clearBtn = $('#clear-search');

        $searchInput.on('keyup', function() {
            const value = $(this).val().toLowerCase().trim();
            let visibleCount = 0;

            if(value.length > 0) {
                $clearBtn.fadeIn(200);
            } else {
                $clearBtn.fadeOut(200);
            }

            $rows.each(function() {
                // Ignore no-records row
                if ($(this).find('td[colspan]').length > 0) return;
                
                const text = $(this).text().toLowerCase();
                if (text.indexOf(value) > -1) {
                    $(this).removeClass('d-none').css('opacity', '1');
                    visibleCount++;
                } else {
                    $(this).addClass('d-none');
                }
            });

            if (value === "") {
                $statusText.text("{{ __('Showing all Orion API transaction activities') }}");
            } else {
                $statusText.html(`{{ __('Found') }} <strong>${visibleCount}</strong> {{ __('results for') }} "${value}"`);
            }
        });

        $clearBtn.on('click', function() {
            $searchInput.val('');
            $searchInput.trigger('keyup');
        });

        if (typeof flatpickr !== 'undefined') {
            flatpickr(".flatpickr-input", {
                dateFormat: "Y-m-d",
            });
        }
    });
</script>

<style>
    .orion-logs-card {
        border-radius: 16px;
        background: #ffffff;
        overflow: hidden;
    }
    .search-container {
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .search-container:focus-within {
        background: #ffffff;
        border-color: #6f42c1;
        box-shadow: 0 4px 15px rgba(111, 66, 193, 0.1) !important;
    }
    .search-container .form-control:focus {
        box-shadow: none;
    }
    #orion-logs-table-el tbody tr {
        transition: all 0.25s ease;
    }
    #orion-logs-table-el tbody tr:hover {
        background-color: #fcfaff;
    }
    #clear-search { display: none; }
    
    .orion-logs-table thead th {
        background: #fdfcff;
        color: #475569;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 14px 20px;
        border-bottom: 2px solid #e8e2f0;
        border-top: none;
    }
    .orion-logs-table tbody td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f0f5;
        vertical-align: middle;
        color: #334155;
    }
    
    .bg-light-purple { background-color: rgba(111, 66, 193, 0.08) !important; }
    .text-purple { color: #6f42c1 !important; }
    .hover-underline-purple { text-decoration: none; color: inherit; transition: color 0.2s; }
    .hover-underline-purple:hover { color: #6f42c1 !important; text-decoration: underline !important; }
    
    .bg-purple-soft { background-color: rgba(111, 66, 193, 0.08) !important; color: #6f42c1 !important; }
    .bg-success-soft { background-color: rgba(25, 135, 84, 0.08) !important; color: #198754 !important; }
    .bg-warning-soft { background-color: rgba(245, 158, 11, 0.08) !important; color: #d97706 !important; }
    .bg-danger-soft { background-color: rgba(220, 53, 69, 0.08) !important; color: #dc3545 !important; }
    .bg-info-soft { background-color: rgba(13, 202, 240, 0.08) !important; color: #0dcaf0 !important; }
    
    .avatar-circle {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        transition: transform 0.2s ease;
    }
    .avatar-circle:hover {
        transform: scale(1.05);
    }
    
    .badge-status {
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        display: inline-block;
        text-align: center;
        border: 1px solid transparent;
    }
    .badge-status-success {
        background: rgba(25, 135, 84, 0.08);
        color: #198754;
        border-color: rgba(25, 135, 84, 0.15);
    }
    .badge-status-pending {
        background: rgba(245, 158, 11, 0.08);
        color: #d97706;
        border-color: rgba(245, 158, 11, 0.15);
    }
    .badge-status-failed {
        background: rgba(220, 53, 69, 0.08);
        color: #dc3545;
        border-color: rgba(220, 53, 69, 0.15);
    }
    
    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        border: 1px solid transparent;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        text-decoration: none;
    }
    .btn-action-view {
        background: rgba(111, 66, 193, 0.08);
        color: #6f42c1;
        border-color: rgba(111, 66, 193, 0.15);
    }
    .btn-action-view:hover {
        background: rgba(111, 66, 193, 0.2);
        color: #5931a7;
        transform: translateY(-1px);
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    .animate-hover-bounce {
        transition: transform 0.2s ease;
    }
    .btn-light-primary:hover .animate-hover-bounce {
        transform: scale(1.1) rotate(10deg);
    }

    /* Modern Pagination styling override */
    .pagination-wrapper .pagination {
        margin-bottom: 0;
        gap: 4px;
        flex-wrap: wrap;
    }
    .pagination-wrapper .page-item:first-child .page-link,
    .pagination-wrapper .page-item:last-child .page-link {
        border-radius: 8px;
    }
    .pagination-wrapper .page-link {
        border: 1px solid #e2e8f0;
        color: #475569;
        padding: 6px 12px;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    .pagination-wrapper .page-link:hover {
        background-color: #f1f5f9;
        color: #1e293b;
        border-color: #cbd5e1;
    }
    .pagination-wrapper .page-item.active .page-link {
        background-color: #6f42c1;
        border-color: #6f42c1;
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(111, 66, 193, 0.15);
    }
    .pagination-wrapper .page-item.disabled .page-link {
        background-color: transparent;
        color: #94a3b8;
        border-color: #f1f5f9;
    }
</style>
@endpush
