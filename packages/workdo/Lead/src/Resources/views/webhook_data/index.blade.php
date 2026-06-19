@extends('layouts.main')

@section('page-title')
    {{ __('Incoming Webhook Data') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card webhook-logs-card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="row align-items-center mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h5 class="mb-1 fw-bold text-dark">{{ __('Webhook Logs') }}</h5>
                            <p class="text-xs text-muted mb-0" id="search-status">{{ __('Showing all incoming data') }}</p>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2 justify-content-md-end">
                                <div class="input-group search-container glass-effect shadow-sm flex-grow-1" style="max-width: 400px;">
                                    <span class="input-group-text bg-transparent border-0 pe-1">
                                        <i class="ti ti-search text-muted"></i>
                                    </span>
                                    <input type="text" id="webhook-search" class="form-control bg-transparent border-0 ps-1 py-2" placeholder="{{ __('Quick search...') }}">
                                    <button class="btn btn-outline-secondary border-0" type="button" id="clear-search">
                                        <i class="ti ti-circle-x text-muted f-16"></i>
                                    </button>
                                </div>
                                <button class="btn btn-light-primary border-0 p-0 d-flex align-items-center justify-content-center" type="button" data-bs-toggle="collapse" data-bs-target="#advanced-filter-card" aria-expanded="false" aria-controls="advanced-filter-card" style="border-radius: 12px; width: 43px; height: 43px;" title="{{ __('Advanced Filter') }}">
                                    <i class="ti ti-filter text-primary animate-hover-bounce" style="font-size: 1.15rem;"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Collapsible Advanced Filter Section -->
                    <div class="collapse {{ request()->has('start_date') || request()->has('status') || request()->has('webhook_endpoint_id') || request()->has('assigned_user_id') ? 'show' : '' }} mb-4" id="advanced-filter-card">
                        <div class="card bg-light border-0 shadow-none mb-0" style="border-radius: 12px; background-color: #f8fafc !important;">
                            <div class="card-body p-3">
                                {{ Form::open(['route' => ['webhook-data.index'], 'method' => 'GET', 'id' => 'webhook_filter_form']) }}
                                <div class="row g-3 align-items-end">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="form-group mb-0">
                                            {{ Form::label('webhook_endpoint_id', __('Webhook Source'), ['class' => 'form-label fw-bold text-dark text-xs mb-1']) }}
                                            {{ Form::select('webhook_endpoint_id', ['' => __('All Sources')] + $endpoints->toArray(), request('webhook_endpoint_id'), ['class' => 'form-select form-control-sm', 'id' => 'webhook_endpoint_id']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="form-group mb-0">
                                            {{ Form::label('status', __('Status'), ['class' => 'form-label fw-bold text-dark text-xs mb-1']) }}
                                            {{ Form::select('status', ['' => __('All Statuses'), 'pending' => __('Pending'), 'converted' => __('Converted'), 'failed' => __('Failed')], request('status'), ['class' => 'form-select form-control-sm', 'id' => 'status']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="form-group mb-0">
                                            {{ Form::label('assigned_user_id', __('Assigned To'), ['class' => 'form-label fw-bold text-dark text-xs mb-1']) }}
                                            {{ Form::select('assigned_user_id', ['' => __('All Users')] + $users->toArray(), request('assigned_user_id'), ['class' => 'form-select form-control-sm', 'id' => 'assigned_user_id']) }}
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
                                        <button class="btn btn-sm btn-primary p-2 d-flex align-items-center justify-content-center" type="submit" data-bs-toggle="tooltip" title="{{ __('Apply Filters') }}" style="border-radius: 8px; width: 34px; height: 34px;">
                                            <i class="ti ti-filter" style="font-size: 1rem;"></i>
                                        </button>
                                        <a href="{{ route('webhook-data.index') }}" class="btn btn-sm btn-danger text-white p-2 d-flex align-items-center justify-content-center" data-bs-toggle="tooltip" title="{{ __('Reset Filters') }}" style="border-radius: 8px; width: 34px; height: 34px;">
                                            <i class="ti ti-refresh" style="font-size: 1rem;"></i>
                                        </a>
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table webhook-logs-table mb-0" id="webhook-data">
                            <thead>
                                <tr>
                                    <th>{{ __('Webhook Source') }}</th>
                                    <th>{{ __('Data Subject/Name (Extracted)') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Assigned To') }}</th>
                                    <th>{{ __('Received At') }}</th>
                                    <th class="text-end" width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $company_settings = getCompanyAllSetting();
                                    $timezone = !empty($company_settings['defult_timezone']) ? $company_settings['defult_timezone'] : 'UTC';
                                @endphp
                                @foreach($webhookDataList as $data)
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-dark border border-secondary border-opacity-10 py-1.5 px-3 rounded-pill fw-semibold" style="font-size: 0.8rem;">
                                                <i class="ti ti-link text-muted me-1" style="font-size: 0.85rem;"></i>
                                                {{ $data->endpoint ? $data->endpoint->name : __('Unknown') }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $name = $data->payload['name'] ?? $data->payload['title'] ?? $data->payload['subject'] ?? __('N/A');
                                                $email = $data->payload['email'] ?? '';
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
                                                $colors = ['bg-primary-soft text-primary', 'bg-success-soft text-success', 'bg-warning-soft text-warning', 'bg-danger-soft text-danger', 'bg-info-soft text-info'];
                                                $colorIndex = (ord(substr($name, 0, 1)) + ord(substr($name, -1))) % count($colors);
                                                $avatarClass = $colors[$colorIndex];
                                            @endphp
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-3 {{ $avatarClass }}">
                                                    {{ $initials }}
                                                </div>
                                                <div>
                                                    <span class="d-block text-dark fw-bold" style="font-size: 0.9rem;">{{ $name }}</span>
                                                    @if($email)
                                                        <span class="text-muted d-block" style="font-size: 0.78rem;">
                                                            <i class="ti ti-mail me-1 text-muted" style="font-size: 0.85rem;"></i>{{ $email }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($data->status == 'pending')
                                                <span class="badge-status badge-status-pending">{{ ucfirst($data->status) }}</span>
                                            @elseif($data->status == 'converted')
                                                <span class="badge-status badge-status-converted">{{ ucfirst($data->status) }}</span>
                                            @else
                                                <span class="badge-status badge-status-failed" data-bs-toggle="tooltip" title="{{ $data->payload['_error_reason'] ?? __('Failed') }}">{{ ucfirst($data->status) }}</span>
                                                @if(isset($data->payload['_error_reason']))
                                                    <div class="mt-2 text-danger fw-semibold d-flex align-items-center gap-1" style="font-size: 0.72rem; line-height: 1.2;">
                                                        <i class="ti ti-alert-triangle f-14 text-danger animate-pulse"></i>
                                                        <span>{{ $data->payload['_error_reason'] }}</span>
                                                    </div>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                                                    <i class="ti ti-user text-secondary" style="font-size: 0.8rem;"></i>
                                                </div>
                                                <span class="fw-semibold text-dark" style="font-size: 0.85rem;">{{ $data->assignedUser ? $data->assignedUser->name : '-' }}</span>
                                            </div>
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
                                            <div class="d-flex gap-1 justify-content-end align-items-center">
                                                @permission('crm manage')
                                                    <a href="#" class="btn-action btn-action-view" data-url="{{ route('webhook-data.payload', $data->id) }}" data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip" title="{{__('View Payload')}}" data-title="{{__('Payload Info')}}">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                @endpermission

                                                @if($data->status != 'converted')
                                                    @permission('crm manage')
                                                        <a href="#" class="btn-action btn-action-transfer" data-url="{{ route('webhook-data.transfer-modal', $data->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Transfer')}}" data-title="{{__('Transfer Webhook Data')}}">
                                                            <i class="ti ti-arrows-right-left"></i>
                                                        </a>
                                                    @endpermission

                                                    @permission('crm manage')
                                                        {!! Form::open(['method' => 'POST', 'route' => ['webhook-data.convert', $data->id], 'id' => 'convert-form-' . $data->id, 'class' => 'd-inline']) !!}
                                                            <a href="javascript:void(0)" onclick="document.getElementById('convert-form-{{ $data->id }}').submit();" class="btn-action btn-action-convert" data-bs-toggle="tooltip" title="{{__('Convert to Lead')}}">
                                                                <i class="ti ti-check"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    @endpermission
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const $searchInput = $('#webhook-search');
        const $tableBody = $('#webhook-data tbody');
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
                const text = $(this).text().toLowerCase();
                if (text.indexOf(value) > -1) {
                    $(this).removeClass('d-none').css('opacity', '1');
                    visibleCount++;
                } else {
                    $(this).addClass('d-none');
                }
            });

            if (value === "") {
                $statusText.text("{{ __('Showing all incoming data') }}");
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
    .webhook-logs-card {
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
        border-color: #198754;
        box-shadow: 0 4px 15px rgba(25, 135, 84, 0.1) !important;
    }
    .search-container .form-control:focus {
        box-shadow: none;
    }
    #webhook-data tbody tr {
        transition: all 0.25s ease;
    }
    #webhook-data tbody tr:hover {
        background-color: #f8fafc;
    }
    #clear-search { display: none; }
    
    .webhook-logs-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 14px 20px;
        border-bottom: 2px solid #e2e8f0;
        border-top: none;
    }
    .webhook-logs-table tbody td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #334155;
    }
    
    /* Soft color classes for avatar circles */
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.08) !important; color: #0d6efd !important; }
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
    
    /* Soft status badges */
    .badge-status {
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        display: inline-block;
        text-align: center;
        border: 1px solid transparent;
    }
    .badge-status-converted {
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
    
    /* Action buttons styling */
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
        background: rgba(13, 202, 240, 0.08);
        color: #0dcaf0;
        border-color: rgba(13, 202, 240, 0.15);
    }
    .btn-action-view:hover {
        background: rgba(13, 202, 240, 0.2);
        color: #0bacd0;
        transform: translateY(-1px);
    }
    .btn-action-transfer {
        background: rgba(111, 66, 193, 0.08);
        color: #6f42c1;
        border-color: rgba(111, 66, 193, 0.15);
    }
    .btn-action-transfer:hover {
        background: rgba(111, 66, 193, 0.2);
        color: #5a32a3;
        transform: translateY(-1px);
    }
    .btn-action-convert {
        background: rgba(25, 135, 84, 0.08);
        color: #198754;
        border-color: rgba(25, 135, 84, 0.15);
    }
    .btn-action-convert:hover {
        background: rgba(25, 135, 84, 0.2);
        color: #146c43;
        transform: translateY(-1px);
    }
    
    /* Animations */
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
</style>
@endpush
