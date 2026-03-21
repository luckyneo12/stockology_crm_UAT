@extends('layouts.main')

@section('page-title')
    {{ __('Click to Call Manager') }}
@endsection

@section('page-breadcrumb')
    {{ __('Setup') }},
    {{ __('Click to Call Manager') }}
@endsection

@push('css')
    <style>
        .mapping-override {
            background-color: rgba(0, 123, 255, 0.05) !important;
        }

        .tab-pane {
            padding-top: 20px;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-3 col-12">
            @include('lead::layouts.system_setup')
        </div>
        <div class="col-xl-9">
            <div class="card">
                {{ Form::open(['route' => 'lead.call.manager.save', 'method' => 'POST', 'id' => 'call-manager-form']) }}
                <div class="card-header border-bottom-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5>{{ __('Click to Call Manager') }}</h5>
                            <small class="text-muted">{{ __('Configure Base URLs for departments and users.') }}</small>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-sm btn-icon" data-bs-toggle="tooltip"
                                title="{{ __('Save Changes') }}">
                                <i class="ti ti-device-floppy"></i> {{ __('Save Configuration') }}
                            </button>
                        </div>
                    </div>

                    <ul class="nav nav-tabs mt-4" id="managerTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="config-tab" data-bs-toggle="tab" data-bs-target="#config"
                                type="button" role="tab">{{ __('Global Settings') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="dept-tab" data-bs-toggle="tab" data-bs-target="#dept" type="button"
                                role="tab">{{ __('Departments') }} @if(count($departments) > 0) <span
                                class="badge bg-primary ms-1">{{ count($departments) }}</span> @endif</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="user-tab" data-bs-toggle="tab" data-bs-target="#user" type="button"
                                role="tab">{{ __('Individual Users') }} @if(count($users) > 0) <span
                                class="badge bg-primary ms-1">{{ count($users) }}</span> @endif</button>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content" id="managerTabsContent">
                        <!-- Tab 1: Global Config -->
                        <div class="tab-pane fade show active" id="config" role="tabpanel">
                            <div class="row">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                                {{ __('The priority for Calling URLs is: Individual User URL > Department URL > Global Default URL.') }}
                                            </div>
                                        </div>
                                        
                                        <!-- API 1 Slot -->
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                {{ Form::label('global_calling_api_1_name', __('Global API 1 Name'), ['class' => 'form-label font-weight-bold']) }}
                                                {{ Form::text('global_calling_api_1_name', !empty($settings['global_calling_api_1_name']) ? $settings['global_calling_api_1_name'] : '', ['class' => 'form-control', 'placeholder' => 'Airtel']) }}
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group mb-3">
                                                {{ Form::label('global_calling_api_1_url', __('Global API 1 URL'), ['class' => 'form-label font-weight-bold']) }}
                                                {{ Form::text('global_calling_api_1_url', !empty($settings['global_calling_api_1_url']) ? $settings['global_calling_api_1_url'] : '', ['class' => 'form-control', 'placeholder' => 'https://api.airtel.com/call?ext={ext}&num={num}']) }}
                                            </div>
                                        </div>

                                        <!-- API 2 Slot -->
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                {{ Form::label('global_calling_api_2_name', __('Global API 2 Name'), ['class' => 'form-label font-weight-bold']) }}
                                                {{ Form::text('global_calling_api_2_name', !empty($settings['global_calling_api_2_name']) ? $settings['global_calling_api_2_name'] : '', ['class' => 'form-control', 'placeholder' => 'Jio']) }}
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group mb-3">
                                                {{ Form::label('global_calling_api_2_url', __('Global API 2 URL'), ['class' => 'form-label font-weight-bold']) }}
                                                {{ Form::text('global_calling_api_2_url', !empty($settings['global_calling_api_2_url']) ? $settings['global_calling_api_2_url'] : '', ['class' => 'form-control', 'placeholder' => 'https://api.jio.com/call?ext={ext}&num={num}']) }}
                                            </div>
                                        </div>

                                        <!-- API 3 Slot -->
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                {{ Form::label('global_calling_api_3_name', __('Global API 3 Name'), ['class' => 'form-label font-weight-bold']) }}
                                                {{ Form::text('global_calling_api_3_name', !empty($settings['global_calling_api_3_name']) ? $settings['global_calling_api_3_name'] : '', ['class' => 'form-control', 'placeholder' => 'Zoiper']) }}
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group mb-3">
                                                {{ Form::label('global_calling_api_3_url', __('Global API 3 URL'), ['class' => 'form-label font-weight-bold']) }}
                                                {{ Form::text('global_calling_api_3_url', !empty($settings['global_calling_api_3_url']) ? $settings['global_calling_api_3_url'] : '', ['class' => 'form-control', 'placeholder' => 'zoiper:']) }}
                                            </div>
                                        </div>
                                    </div>
                            </div>
                        </div>

                        <!-- Tab 2: Departments -->
                        <div class="tab-pane fade" id="dept" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                                        <input type="text" id="dept-search" class="form-control"
                                            placeholder="{{ __('Search Departments...') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="dept-list">
                                @forelse($departments as $dept)
                                    @php $deptUrl = !empty($settings['dept_calling_url_' . $dept->id]) ? $settings['dept_calling_url_' . $dept->id] : ''; @endphp
                                    <div class="col-md-6 mb-3 dept-item" data-name="{{ strtolower($dept->name) }}">
                                        <div
                                            class="card border shadow-none mb-0 {{ !empty($deptUrl) ? 'mapping-override' : '' }}">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <label class="form-label mb-0"><strong>{{ $dept->name }}</strong></label>
                                                    <div class="form-check form-switch custom-switch-v1">
                                                        <input type="checkbox" name="click_to_call_enabled_dept_{{ $dept->id }}"
                                                            class="form-check-input input-primary"
                                                            id="click_to_call_enabled_dept_{{ $dept->id }}" {{ (isset($settings['click_to_call_enabled_dept_' . $dept->id]) && $settings['click_to_call_enabled_dept_' . $dept->id] == 'on') ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="click_to_call_enabled_dept_{{ $dept->id }}"></label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    @for($i=1; $i<=2; $i++)
                                                    <div class="col-6">
                                                        <div class="form-group mb-1">
                                                            <input type="text" name="dept_api_{{ $i }}_name_{{ $dept->id }}" 
                                                                value="{{ $settings['dept_api_'.$i.'_name_'.$dept->id] ?? '' }}" 
                                                                class="form-control form-control-sm" placeholder="API {{ $i }} Name">
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group mb-1">
                                                            <input type="text" name="dept_api_{{ $i }}_url_{{ $dept->id }}" 
                                                                value="{{ $settings['dept_api_'.$i.'_url_'.$dept->id] ?? '' }}" 
                                                                class="form-control form-control-sm" placeholder="API {{ $i }} URL">
                                                        </div>
                                                    </div>
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center text-muted py-4">
                                        {{ __('No departments found.') }}
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Tab 3: Users -->
                        <div class="tab-pane fade" id="user" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                                        <input type="text" id="user-search" class="form-control"
                                            placeholder="{{ __('Search Users by Name or Email...') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="user-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('User Info') }}</th>
                                            <th>{{ __('API Configuration Overrides') }}</th>
                                            <th class="text-center">{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="user-list">
                                        @forelse($users as $user)
                                            @php $userUrl = !empty($settings['click_to_call_url_user_' . $user->id]) ? $settings['click_to_call_url_user_' . $user->id] : ''; @endphp
                                            <tr class="user-item {{ !empty($userUrl) ? 'mapping-override' : '' }}"
                                                data-search="{{ strtolower($user->name . ' ' . $user->email) }}">
                                                <td class="align-middle">
                                                    <div class="d-flex align-items-center">
                                                        <div class="ms-2">
                                                            <div class="text-dark font-weight-bold">{{ $user->name }}</div>
                                                            <small class="text-muted">{{ $user->email }}</small>
                                                            <span class="badge bg-light-secondary text-secondary ms-1"
                                                                style="font-size: 10px;">{{ ucfirst($user->type) }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="row g-2">
                                                        @for($i=1; $i<=2; $i++)
                                                        <div class="col-md-6">
                                                            <input type="text" name="user_api_{{ $i }}_name_{{ $user->id }}" 
                                                                value="{{ $settings['user_api_'.$i.'_name_'.$user->id] ?? '' }}" 
                                                                class="form-control form-control-sm" placeholder="API {{ $i }} Name">
                                                            <input type="text" name="user_api_{{ $i }}_url_{{ $user->id }}" 
                                                                value="{{ $settings['user_api_'.$i.'_url_'.$user->id] ?? '' }}" 
                                                                class="form-control form-control-sm mt-1" placeholder="API {{ $i }} URL">
                                                        </div>
                                                        @endfor
                                                    </div>
                                                </td>
                                                <td class="align-middle text-center">
                                                    @if(!empty($userUrl))
                                                        <span class="badge bg-success" data-bs-toggle="tooltip"
                                                            title="{{ __('Overridden') }}"><i class="ti ti-check"></i></span>
                                                    @else
                                                        <span class="badge bg-light text-muted" data-bs-toggle="tooltip"
                                                            title="{{ __('Using Defaults') }}"><i class="ti ti-minus"></i></span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">{{ __('No users found.') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Real-time Department Search
            $('#dept-search').on('keyup', function () {
                var value = $(this).val().toLowerCase();
                $('.dept-item').filter(function () {
                    $(this).toggle($(this).data('name').indexOf(value) > -1);
                });
            });

            // Real-time User Search
            $('#user-search').on('keyup', function () {
                var value = $(this).val().toLowerCase();
                $('#user-list tr').filter(function () {
                    $(this).toggle($(this).data('search').indexOf(value) > -1);
                });
            });

            // Highlight changed rows/cards locally (Optional visual hint)
            $('input[type="text"]').on('change', function () {
                if ($(this).val() !== "") {
                    $(this).closest('.card, tr').addClass('mapping-override');
                } else {
                    $(this).closest('.card, tr').removeClass('mapping-override');
                }
            });
        });
    </script>
@endpush