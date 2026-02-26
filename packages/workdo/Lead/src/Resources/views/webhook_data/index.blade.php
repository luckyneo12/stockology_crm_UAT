@extends('layouts.main')

@section('page-title')
    {{ __('Incoming Webhook Data') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="row align-items-center mb-4">
                        <div class="col-md-6">
                            <h5 class="mb-0">{{ __('Webhook Logs') }}</h5>
                            <p class="text-xs text-muted mb-0" id="search-status">{{ __('Showing all incoming data') }}</p>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group search-container glass-effect shadow-sm">
                                <span class="input-group-text bg-transparent border-0 pe-1">
                                    <i class="ti ti-search text-muted"></i>
                                </span>
                                <input type="text" id="webhook-search" class="form-control bg-transparent border-0 ps-1" placeholder="{{ __('Quick search by source, name, email or status...') }}">
                                <button class="btn btn-outline-secondary border-0" type="button" id="clear-search">
                                    <i class="ti ti-circle-x text-muted f-16"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="webhook-data">
                            <thead>
                                <tr>
                                    <th>{{ __('Webhook Source') }}</th>
                                    <th>{{ __('Data Subject/Name (Extracted)') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Assigned To') }}</th>
                                    <th>{{ __('Received At') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($webhookDataList as $data)
                                    <tr>
                                        <td>{{ $data->endpoint ? $data->endpoint->name : __('Unknown') }}</td>
                                        <td>
                                            @php
                                                $name = $data->payload['name'] ?? $data->payload['title'] ?? $data->payload['subject'] ?? __('N/A');
                                                $email = $data->payload['email'] ?? '';
                                            @endphp
                                            {{ $name }} <br><small class="text-muted">{{ $email }}</small>
                                        </td>
                                        <td>
                                            @if($data->status == 'pending')
                                                <span class="badge bg-warning p-2 px-3 rounded">{{ ucfirst($data->status) }}</span>
                                            @elseif($data->status == 'converted')
                                                <span class="badge bg-success p-2 px-3 rounded">{{ ucfirst($data->status) }}</span>
                                            @else
                                                <span class="badge bg-danger p-2 px-3 rounded">{{ ucfirst($data->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $data->assignedUser ? $data->assignedUser->name : '-' }}</td>
                                        <td>{{ company_datetime_formate($data->created_at) }}</td>
                                        <td class="Action">
                                            <span>
                                                @permission('crm manage')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('webhook-data.payload', $data->id) }}" data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip" title="{{__('View Payload')}}" data-title="{{__('Payload Info')}}">
                                                            <i class="ti ti-eye text-white"></i>
                                                        </a>
                                                    </div>
                                                @endpermission

                                                @if($data->status == 'pending')
                                                    @permission('crm manage')
                                                        <div class="action-btn bg-primary ms-2">
                                                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('webhook-data.transfer-modal', $data->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Transfer')}}" data-title="{{__('Transfer Webhook Data')}}">
                                                                <i class="ti ti-arrows-right-left text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission

                                                    @permission('crm manage')
                                                        <div class="action-btn bg-success ms-2">
                                                            {!! Form::open(['method' => 'POST', 'route' => ['webhook-data.convert', $data->id], 'id' => 'convert-form-' . $data->id]) !!}
                                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center bs-pass-para show_confirm" data-bs-toggle="tooltip" title="{{__('Convert to Lead')}}">
                                                                    <i class="ti ti-check text-white"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endpermission
                                                @endif
                                            </span>
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
    });
</script>

<style>
    .search-container {
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    .search-container:focus-within {
        border-color: #5e72e4;
        box-shadow: 0 4px 12px rgba(94, 114, 228, 0.15) !important;
        transform: translateY(-1px);
    }
    .search-container .form-control:focus {
        box-shadow: none;
    }
    #webhook-data tbody tr {
        transition: opacity 0.2s ease;
    }
    #clear-search { display: none; }
    .glass-effect {
        box-shadow: 0 2px 15px rgba(0,0,0,0.02);
    }
    .bg-light-success-soft {
        background-color: rgba(0, 179, 136, 0.06) !important;
        color: #008f6d !important;
    }
</style>
@endpush
