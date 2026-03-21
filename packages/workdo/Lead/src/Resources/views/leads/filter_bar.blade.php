@push('css')
    <style>
        .filter-badge:hover {
            background-color: #e9ecef !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        }

        .choices__inner {
            border-radius: 8px !important;
            background-color: #f8f9fa !important;
            border: 1px solid #ced4da !important;
            padding: 4px 10px !important;
            min-height: 40px !important;
        }

        .choices__list--multiple .choices__item {
            border-radius: 4px !important;
            background-color: #5e72e4 !important;
            border: none !important;
            font-weight: 500;
            padding: 2px 8px;
        }

        #leadFilterModal .modal-content {
            border-radius: 12px !important;
            overflow: hidden;
        }

        #leadFilterModal .modal-header {
            background: linear-gradient(to right, #f8f9fa, #ffffff);
            border-bottom: 1px solid #eee;
        }

        .filter-count-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 10px;
            padding: 3px 6px;
            border: 2px solid #fff;
        }

        .bg-primary-light {
            background-color: rgba(94, 114, 228, 0.1);
        }

        .filter-card {
            transition: all 0.3s ease;
            border: 1px solid transparent;
            position: relative;
            z-index: 1;
        }

        .filter-card:hover,
        .filter-card:focus-within {
            background-color: #fff;
            border-color: #e9ecef;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
            z-index: 20;
        }

        .choices__list--dropdown {
            background-color: #fff !important;
            z-index: 100 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        }

        .filter-section-title {
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .search-settings-btn {
            border: none;
            background: transparent;
            color: #adb5bd;
            transition: all 0.2s;
        }

        .search-settings-btn:hover {
            color: #5e72e4;
        }

        .search-settings-dropdown {
            min-width: 250px;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .locked-filter {
            opacity: 0.7;
            pointer-events: none;
            background-color: #f8f9fa !important;
        }

        .filter-section-card {
            border-left: 4px solid #5e72e4 !important;
        }
    </style>
@endpush

<div class="row align-items-center mb-3 g-2">
    <div class="col-xl-4 col-lg-5 col-md-7 col-sm-12">
        <div class="input-group input-group shadow-sm rounded position-relative">
            <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
            <input type="text" id="lead_search" class="form-control border-start-0 ps-0"
                placeholder="{{ __('Quick search...') }}" value="{{ request('search') }}">
            <button class="input-group-text bg-white border-start-0" type="button" data-bs-toggle="modal"
                data-bs-target="#searchSettingsModal" title="{{ __('Search Settings') }}">
                <i class="ti ti-settings text-muted"></i>
            </button>
        </div>
    </div>
    <div class="col-auto">
        <div class="input-group shadow-sm rounded">
            <span class="input-group-text bg-white border-end-0"><i class="ti ti-list-numbers text-muted"></i></span>
            <select class="form-select border-start-0 ps-0" id="entries_per_page" style="width: 80px;">
                <option value="10" {{ request('length') == 10 ? 'selected' : '' }}>10</option>
                <option value="25" {{ request('length') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('length') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('length') == 100 ? 'selected' : '' }}>100</option>
                <option value="500" {{ request('length') == 500 ? 'selected' : '' }}>500</option>
            </select>
        </div>
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-primary btn-icon shadow-sm position-relative" data-bs-toggle="modal"
            data-bs-target="#leadFilterModal" id="advancedFilterBtn">
            <span class="btn-inner--icon"><i class="ti ti-adjustments-horizontal me-1"></i></span>
            <span class="btn-inner--text">{{ __('Advanced Filter') }}</span>
            @php
                $activeFilterCount = 0;
                $filterKeys = ['responsible_person', 'stage_id', 'source_id', 'start_date', 'end_date', 'created_by', 'modified_by', 'duplicates', 'department_id', 'designation_id', 'modified_start_date', 'modified_end_date'];
                foreach ($filterKeys as $key) {
                    if (request()->has($key) && !empty(request($key)))
                        $activeFilterCount++;
                    elseif (request()->has($key . '[]') && !empty(request($key . '[]')))
                        $activeFilterCount++;
                }
            @endphp
            @if($activeFilterCount > 0)
                <span class="badge rounded-pill bg-danger filter-count-badge shadow-sm">{{ $activeFilterCount }}</span>
            @endif
        </button>
    </div>
    @if($activeFilterCount > 0 || request()->has('search'))
        <div class="col-auto">
            <button type="button" class="btn btn-light-danger btn-icon shadow-sm" id="clearAllFiltersHome"
                data-bs-toggle="tooltip" title="{{ __('Clear All Filters') }}">
                <i class="ti ti-trash-x"></i>
            </button>
        </div>
    @endif
    @php
        $user = Auth::user();
    @endphp
    @if(!empty($user->extension_1) || !empty($user->extension_2))
        <div class="col-auto">
            <div class="dropdown shadow-sm rounded">
                <button class="btn btn-primary d-flex align-items-center dropdown-toggle" type="button"
                    id="activeExtensionDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                    style="padding: 0.55rem 1rem;">
                    <i class="ti ti-phone-call me-2"></i>
                    <span class="d-none d-sm-inline">Ext {{ $user->active_extension == 1 ? '1' : '2' }}: </span>
                    <strong
                        class="ms-1">{{ $user->active_extension == 1 ? $user->extension_1 : $user->extension_2 }}</strong>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="activeExtensionDropdown"
                    style="border-radius: 12px;">
                    <li class="px-3 py-2 border-bottom mb-2">
                        <small class="text-muted text-uppercase fw-bold">{{ __('Active Extension') }}</small>
                    </li>
                    @if(!empty($user->extension_1))
                        <li>
                            <a class="dropdown-item switch-extension-btn d-flex align-items-center justify-content-between {{ $user->active_extension == 1 ? 'active bg-primary text-white' : '' }}"
                                href="javascript:void(0)" data-index="1">
                                <span>Ext 1: <strong>{{ $user->extension_1 }}</strong></span>
                                @if($user->active_extension == 1) <i class="ti ti-check ms-2"></i> @endif
                            </a>
                        </li>
                    @endif
                    @if(!empty($user->extension_2))
                        <li>
                            <a class="dropdown-item switch-extension-btn d-flex align-items-center justify-content-between {{ $user->active_extension == 2 ? 'active bg-primary text-white' : '' }}"
                                href="javascript:void(0)" data-index="2">
                                <span>Ext 2: <strong>{{ $user->extension_2 }}</strong></span>
                                @if($user->active_extension == 2) <i class="ti ti-check ms-2"></i> @endif
                            </a>
                        </li>
                    @endif
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center text-primary" href="javascript:void(0)"
                            id="manualExtensionPrompt">
                            <i class="ti ti-pencil me-2"></i>{{ __('Manage Call Settings') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    @endif
    
    @php
        $settings = getCompanyAllSetting($user->id, $user->workspace_id);
        
        $availableApis = [];
        // 1. User
        for($i=1; $i<=2; $i++) {
            if(!empty($settings['user_api_'.$i.'_url_'.$user->id])) {
                $availableApis[] = ['id' => 'user_'.$i, 'name' => $settings['user_api_'.$i.'_name_'.$user->id] ?: 'User API '.$i];
            }
        }
        // 2. Dept
        if(empty($availableApis) && module_is_active('Hrm', $user->workspace_id)) {
            $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $user->id)->first();
            if($employee && $employee->department_id) {
                for($i=1; $i<=2; $i++) {
                    if(!empty($settings['dept_api_'.$i.'_url_'.$employee->department_id])) {
                        $availableApis[] = ['id' => 'dept_'.$i, 'name' => $settings['dept_api_'.$i.'_name_'.$employee->department_id] ?: 'Dept API '.$i];
                    }
                }
            }
        }
        // 3. Global
        if(empty($availableApis)) {
            for($i=1; $i<=3; $i++) {
                if(!empty($settings['global_calling_api_'.$i.'_url'])) {
                    $availableApis[] = ['id' => 'global_'.$i, 'name' => $settings['global_calling_api_'.$i.'_name'] ?: 'Global API '.$i];
                }
            }
        }

        $activeExtIdx = $user->active_extension == 2 ? 2 : 1;
        $mappedApiId = $settings['user_ext_'.$activeExtIdx.'_api_id_'.$user->id] ?? '';
        
        $activeApiName = '';
        foreach($availableApis as $api) {
            if((string)$api['id'] === (string)$mappedApiId) {
                $activeApiName = $api['name'];
                break;
            }
        }
    @endphp

    @if(!empty($availableApis))
        <div class="col-auto">
            <div class="dropdown shadow-sm rounded">
                <button class="btn btn-primary d-flex align-items-center dropdown-toggle" type="button"
                    id="activeApiDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                    style="padding: 0.55rem 1rem; background-color: #28a745; border-color: #28a745;">
                    <i class="ti ti-server me-2"></i>
                    <span class="d-none d-sm-inline">{{ __('API') }}: </span>
                    <strong class="ms-1">{{ $activeApiName ?: __('Default') }}</strong>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="activeApiDropdown"
                    style="border-radius: 12px;">
                    <li class="px-3 py-2 border-bottom mb-2">
                        <small class="text-muted text-uppercase fw-bold">{{ __('Select API for Ext') }} {{ $activeExtIdx }}</small>
                    </li>
                    @foreach($availableApis as $api)
                        <li>
                            <a class="dropdown-item switch-api-btn d-flex align-items-center justify-content-between {{ (string)$mappedApiId === (string)$api['id'] ? 'active bg-success text-white' : '' }}"
                                href="javascript:void(0)" data-id="{{ $api['id'] }}">
                                <span><strong>{{ $api['name'] }}</strong></span>
                                @if((string)$mappedApiId === (string)$api['id']) <i class="ti ti-check ms-2"></i> @endif
                            </a>
                        </li>
                    @endforeach
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center text-success" href="javascript:void(0)"
                            id="manualExtensionPrompt2">
                            <i class="ti ti-pencil me-2"></i>{{ __('Manage Call Settings') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    @endif
</div>

<!-- Advanced Filter Modal -->
<div class="modal fade" id="leadFilterModal" tabindex="-1" aria-labelledby="leadFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary-light p-2 rounded me-3 text-primary">
                        <i class="ti ti-adjustments-horizontal fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold" id="leadFilterModalLabel">{{ __('Advanced Lead Filters') }}</h5>
                        <p class="text-muted small m-0">{{ __('Refine your lead list with specific criteria') }}</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <!-- Category: Lead Assignment -->
                    <div class="col-12">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-soft-primary p-1 me-2"><i class="ti ti-users text-primary"></i></span>
                            <h6 class="text-muted text-uppercase fw-bold small m-0">{{ __('Lead Assignment & Timing') }}
                            </h6>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 text-start">
                                <div class="form-group mb-0 text-start">
                                    <label
                                        class="form-label fw-bold text-dark mb-1">{{ __('Responsible Person') }}</label>
                                    {!! Form::select('responsible_person[]', $users, request('responsible_person'), ['class' => 'form-control choices-js-filter', 'multiple' => 'multiple', 'id' => 'modal_responsible_person']) !!}
                                </div>
                            </div>
                            <div class="col-md-6 text-start">
                                <div class="form-group mb-0 text-start">
                                    <label class="form-label fw-bold text-dark mb-1">{{ __('Lead Stage') }}</label>
                                    {!! Form::select('stage_id[]', $stages, request('stage_id'), ['class' => 'form-control choices-js-filter', 'multiple' => 'multiple', 'id' => 'modal_stage_id']) !!}
                                </div>
                            </div>
                            <div class="col-md-6 text-start mt-3">
                                <div class="form-group mb-0">
                                    <label
                                        class="form-label fw-bold text-dark mb-1">{{ __('Date Created Range') }}</label>
                                    <div class="input-group shadow-sm border rounded overflow-hidden">
                                        <input type="text" class="form-control border-0 flatpickr-filter"
                                            name="start_date" id="modal_start_date" value="{{ request('start_date') }}"
                                            placeholder="YYYY-MM-DD">
                                        <span class="input-group-text bg-white border-0"><i
                                                class="ti ti-arrow-right text-muted"></i></span>
                                        <input type="text" class="form-control border-0 flatpickr-filter"
                                            name="end_date" id="modal_end_date" value="{{ request('end_date') }}"
                                            placeholder="YYYY-MM-DD">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-start mt-3">
                                <div class="form-group mb-0">
                                    <label
                                        class="form-label fw-bold text-dark mb-1">{{ __('Date Modified Range') }}</label>
                                    <div class="input-group shadow-sm border rounded overflow-hidden">
                                        <input type="text" class="form-control border-0 flatpickr-filter"
                                            name="modified_start_date" id="modal_modified_start_date"
                                            value="{{ request('modified_start_date') }}" placeholder="YYYY-MM-DD">
                                        <span class="input-group-text bg-white border-0"><i
                                                class="ti ti-arrow-right text-muted"></i></span>
                                        <input type="text" class="form-control border-0 flatpickr-filter"
                                            name="modified_end_date" id="modal_modified_end_date"
                                            value="{{ request('modified_end_date') }}" placeholder="YYYY-MM-DD">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $user = Auth::user();
                        $isCompany = $user->type == 'company';
                        $vis = $user->visibility_level;

                        $deptDisabled = (!$isCompany && in_array($vis, ['team', 'self', null]) && empty($user->accessible_departments));
                        $teamDisabled = (!$isCompany && $vis == 'self');
                    @endphp

                    <!-- Category: Department -->
                    <div class="col-12">
                        <div
                            class="p-3 bg-light rounded-3 border border-light filter-card filter-section-card {{ $deptDisabled ? 'locked-filter' : '' }}">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-primary p-2 rounded-circle me-2"><i
                                        class="ti ti-building text-white"></i></span>
                                <h6 class="text-dark fw-bold mb-0 filter-section-title">{{ __('Department') }}</h6>
                                @if($deptDisabled)
                                    <small class="ms-auto text-muted"><i
                                            class="ti ti-lock me-1"></i>{{ __('Restricted') }}</small>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        {!! Form::select('department_id[]', $departments, request('department_id'), ['class' => 'form-control choices-js-filter', 'multiple' => 'multiple', 'id' => 'modal_department_id', 'data-placeholder' => __('Select Department'), 'disabled' => $deptDisabled]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category: Team -->
                    <div class="col-12">
                        <div
                            class="p-3 bg-light rounded-3 border border-light filter-card filter-section-card {{ $teamDisabled ? 'locked-filter' : '' }}">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-info p-2 rounded-circle me-2"><i
                                        class="ti ti-sitemap text-white"></i></span>
                                <h6 class="text-dark fw-bold mb-0 filter-section-title">{{ __('Team') }}</h6>
                                @if($teamDisabled)
                                    <small class="ms-auto text-muted"><i
                                            class="ti ti-lock me-1"></i>{{ __('Restricted') }}</small>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        {!! Form::select('designation_id[]', $designations, request('designation_id'), ['class' => 'form-control choices-js-filter', 'multiple' => 'multiple', 'id' => 'modal_designation_id', 'data-placeholder' => __('Select Team'), 'disabled' => $teamDisabled]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category: Classification -->
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-3 border border-light filter-card">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-success p-2 rounded-circle me-2"><i
                                        class="ti ti-tag text-white"></i></span>
                                <h6 class="text-dark fw-bold mb-0 filter-section-title">{{ __('Classification') }}</h6>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label
                                            class="form-label fw-bold text-muted small mb-1">{{ __('Source') }}</label>
                                        {!! Form::select('source_id[]', $sources, request('source_id'), ['class' => 'form-control choices-js-filter', 'multiple' => 'multiple', 'id' => 'modal_source_id']) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check custom-checkbox mt-4 border p-2 rounded bg-white shadow-sm">
                                        <input type="checkbox" class="form-check-input ms-0" name="duplicates"
                                            id="modal_duplicates" {{ request('duplicates') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark cursor-pointer ms-2"
                                            for="modal_duplicates">{{ __('Show Duplicate Leads') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category: Audit -->
                    <div class="col-12">
                        <hr class="my-2 border-light">
                        <div class="d-flex align-items-center mb-3 mt-2">
                            <span class="badge bg-soft-info p-1 me-2"><i class="ti ti-history text-info"></i></span>
                            <h6 class="text-muted text-uppercase fw-bold small m-0">{{ __('Audit Information') }}</h6>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label fw-bold text-dark mb-1">{{ __('Created By') }}</label>
                                    {!! Form::select('created_by[]', $creators, request('created_by'), ['class' => 'form-control choices-js-filter', 'multiple' => 'multiple', 'id' => 'modal_created_by']) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label fw-bold text-dark mb-1">{{ __('Modified By') }}</label>
                                    {!! Form::select('modified_by[]', $modifiers, request('modified_by'), ['class' => 'form-control choices-js-filter', 'multiple' => 'multiple', 'id' => 'modal_modified_by']) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category: Custom Fields -->
                    @php
                        $filterableFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', getActiveWorkSpace())
                            ->where('is_filterable', 1)
                            ->get();
                    @endphp
                    @if($filterableFields->count() > 0)
                        <div class="col-12">
                            <hr class="my-2 border-light">
                            <div class="d-flex align-items-center mb-3 mt-2">
                                <span class="badge bg-soft-warning p-1 me-2"><i class="ti ti-star text-warning"></i></span>
                                <h6 class="text-muted text-uppercase fw-bold small m-0">{{ __('Custom Fields') }}</h6>
                            </div>
                            <div class="row g-3">
                                @foreach($filterableFields as $field)
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label class="form-label fw-bold text-dark mb-1">
                                                @if(!empty($field->icon))
                                                    <i class="" data-feather="{{ $field->icon }}"></i>
                                                @endif
                                                {{ $field->name }}
                                            </label>
                                            <input type="text" class="form-control" name="custom_fields[{{ $field->id }}]"
                                                id="cf_{{ $field->id }}"
                                                value="{{ request('custom_fields')[$field->id] ?? '' }}"
                                                placeholder="{{ __('Search') }} {{ $field->name }}...">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>

                <hr class="my-4 opacity-50">

                <!-- Saved Filters Area -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <label class="form-label fw-bold text-dark m-0">{{ __('Quick Load Saved Filter') }}</label>
                </div>
                <div class="d-flex flex-wrap gap-2" id="saved_filters_container">
                    @if(isset($saved_filters) && count($saved_filters) > 0)
                        @foreach($saved_filters as $filter)
                            <div class="badge bg-light text-primary border p-2 d-flex align-items-center gap-2 filter-badge shadow-sm"
                                style="cursor: pointer; transition: all 0.2s;"
                                data-filters="{{ json_encode($filter->filters) }}">
                                <span>{{ $filter->name }}</span>
                                <i class="ti ti-x text-danger delete-saved-filter" data-id="{{ $filter->id }}"
                                    style="cursor: pointer;" title="{{ __('Delete') }}"></i>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted small m-0">{{ __('No saved filters yet.') }}</p>
                    @endif
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-sm btn-secondary shadow-sm rounded-pill px-3" id="clearFiltersBtn">
                    <i class="ti ti-rotate-clockwise-2 me-1"></i> {{ __('Clear All') }}
                </button>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary shadow-sm rounded-pill px-3"
                        id="saveFilterBtn">
                        <i class="ti ti-bookmark me-1"></i> {{ __('Save Preset') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-primary shadow-sm rounded-pill px-4"
                        id="applyFiltersBtn">
                        <i class="ti ti-check me-1"></i> {{ __('Apply Filters') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Settings Modal -->
<div class="modal fade" id="searchSettingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg border-0" style="border-radius: 15px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">{{ __('Searchable Fields') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <p class="text-muted small mb-3">{{ __('Select fields to include in quick search.') }}</p>
                <form id="searchSettingsForm">
                    @php
                        $userSettings = Auth::user()->search_settings ?? ['name', 'subject'];
                        $systemFields = [
                            'name' => __('Name'),
                            'subject' => __('Subject'),
                            'email' => __('Email'),
                            'phone' => __('Phone'),
                            'pan_number' => __('PAN Number'),
                            'aadhar_number' => __('Aadhar Number'),
                        ];
                        $customFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', getActiveWorkSpace())
                            ->where('is_filterable', 1)
                            ->whereIn('type', ['text', 'number'])
                            ->get();
                    @endphp
                    <div class="list-group list-group-flush">
                        {{-- System Fields --}}
                        @foreach($systemFields as $key => $label)
                            <label
                                class="list-group-item border-0 d-flex justify-content-between align-items-center px-0 py-2 cursor-pointer">
                                <span class="text-dark fw-medium">{{ $label }}</span>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="search_fields[]"
                                        value="{{ $key }}" {{ in_array($key, $userSettings) ? 'checked' : '' }}>
                                </div>
                            </label>
                        @endforeach

                        {{-- Custom Fields --}}
                        @if($customFields->count() > 0)
                            <div class="py-2 border-top mt-2">
                                <small class="text-muted text-uppercase fw-bold">{{ __('Custom Fields') }}</small>
                            </div>
                            @foreach($customFields as $field)
                                <label
                                    class="list-group-item border-0 d-flex justify-content-between align-items-center px-0 py-2 cursor-pointer">
                                    <span class="text-dark fw-medium">{{ $field->name }}</span>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" name="search_fields[]"
                                            value="custom_{{ $field->id }}" {{ in_array('custom_' . $field->id, $userSettings) ? 'checked' : '' }}>
                                    </div>
                                </label>
                            @endforeach
                        @endif
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill w-100 mb-2"
                    data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary rounded-pill w-100"
                    id="saveSearchSettingsBtn">{{ __('Save Configuration') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for naming the filter -->
<div class="modal fade" id="saveFilterNameModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-body p-4 text-center">
                <h6 class="fw-bold mb-3">{{ __('Save Filter Preset') }}</h6>
                <div class="form-group">
                    <input type="text" id="filterName" class="form-control text-center"
                        placeholder="{{ __('e.g., Team Alpha') }}">
                </div>
                <div class="d-flex justify-content-center gap-2 mt-4">
                    <button type="button" class="btn btn-sm btn-light"
                        data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-sm btn-primary"
                        id="confirmSaveFilter">{{ __('Save Now') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            const modalEl = document.getElementById('leadFilterModal');
            let choicesInstances = [];

            function initFlatpickr() {
                $(".flatpickr-filter").flatpickr({
                    dateFormat: "Y-m-d",
                    allowInput: true,
                    onOpen: function (selectedDates, dateStr, instance) {
                        $(instance.element).closest('.input-group').addClass('border-primary').removeClass('border-light');
                    },
                    onClose: function (selectedDates, dateStr, instance) {
                        $(instance.element).closest('.input-group').removeClass('border-primary').addClass('border-light');
                    }
                });
            }

            const choicesOptions = {
                removeItemButton: true,
                placeholder: true,
                placeholderValue: @json(__("Please Select")),
                searchEnabled: true,
                shouldSort: false,
                classNames: {
                    containerOuter: 'choices w-100',
                }
            };

            function initChoices() {
                // Only destroy/init if not already valid? 
                // Better to clean slate to ensure no duplicates.
                choicesInstances.forEach(instance => {
                    // Check if element still exists
                    if (instance.passedElement && instance.passedElement.element) {
                        try {
                            instance.destroy();
                        } catch (e) { }
                    }
                });
                choicesInstances = [];

                const elements = modalEl.querySelectorAll('.choices-js-filter');
                elements.forEach(el => {
                    const instance = new Choices(el, choicesOptions);
                    choicesInstances.push(instance);
                });
            }

            modalEl.addEventListener('shown.bs.modal', function () {
                initChoices();
                initFlatpickr();
            });

            // Search on Enter
            const leadSearch = document.getElementById('lead_search');
            if (leadSearch) {
                leadSearch.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        applyFilters();
                    }
                });
            }

            // Home Reset
            const clearHome = document.getElementById('clearAllFiltersHome');
            if (clearHome) {
                clearHome.addEventListener('click', function () {
                    window.location.href = window.location.pathname;
                });
            }

            document.getElementById('applyFiltersBtn').addEventListener('click', applyFilters);

            document.getElementById('clearFiltersBtn').addEventListener('click', function () {
                window.location.href = window.location.pathname;
            });

            document.getElementById('saveFilterBtn').addEventListener('click', function () {
                const saveModal = new bootstrap.Modal(document.getElementById('saveFilterNameModal'));
                saveModal.show();
            });

            document.getElementById('confirmSaveFilter').addEventListener('click', function () {
                const name = document.getElementById('filterName').value;
                if (!name) {
                    toastrs('error', @json(__("Please enter a filter name")), 'error');
                    return;
                }

                const cfInputs = document.querySelectorAll('input[name^="custom_fields["]');
                let cfData = {};
                cfInputs.forEach(input => {
                    let id = input.id.replace('cf_', '');
                    cfData[id] = input.value;
                });

                const filters = {
                    responsible_person: $('#modal_responsible_person').val(),
                    stage_id: $('#modal_stage_id').val(),
                    // duplicate keys removed
                    department_id: $('#modal_department_id').val(),
                    designation_id: $('#modal_designation_id').val(),
                    source_id: $('#modal_source_id').val(),
                    start_date: $('#modal_start_date').val(),
                    end_date: $('#modal_end_date').val(),
                    modified_start_date: $('#modal_modified_start_date').val(),
                    modified_end_date: $('#modal_modified_end_date').val(),
                    created_by: $('#modal_created_by').val(),
                    modified_by: $('#modal_modified_by').val(),
                    search: $('#lead_search').val(),
                    duplicates: $('#modal_duplicates').is(':checked') ? 1 : '',
                    custom_fields: cfData
                };

                $.ajax({
                    url: '{{ route("leads.filter.save") }}',
                    type: 'POST',
                    data: {
                        name: name,
                        filters: filters,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.success) {
                            toastrs('success', response.message, 'success');
                            location.reload();
                        } else {
                            toastrs('error', response.message, 'error');
                        }
                    }
                });
            });

            $(document).on('click', '.filter-badge', function (e) {
                if ($(e.target).hasClass('delete-saved-filter')) return;

                const filters = $(this).data('filters');
                const url = new URL(window.location.href);

                Object.keys(filters).forEach(key => {
                    if (key === 'custom_fields') {
                        Object.keys(filters[key]).forEach(cfId => {
                            url.searchParams.delete('custom_fields[' + cfId + ']');
                            url.searchParams.set('custom_fields[' + cfId + ']', filters[key][cfId]);
                        });
                    } else {
                        url.searchParams.delete(key + '[]');
                        url.searchParams.delete(key);
                        if (Array.isArray(filters[key])) {
                            filters[key].forEach(val => url.searchParams.append(key + '[]', val));
                        } else if (filters[key]) {
                            url.searchParams.set(key, filters[key]);
                        }
                    }
                });
                window.location.href = url.href;
            });

            $(document).on('click', '.delete-saved-filter', function () {
                const id = $(this).data('id');
                const badge = $(this).closest('.filter-badge');

                if (confirm(@json(__("Are you sure you want to delete this filter?")))) {
                    $.ajax({
                        url: '{{ route("leads.filter.delete", "_ID_") }}'.replace('_ID_', id),
                        type: 'DELETE',
                        data: {
                            id: id,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            if (response.success) {
                                badge.remove();
                                toastrs('success', response.message, 'success');
                            }
                        }
                    });
                }
            });

            // Dynamic Team loading based on Department
            $(document).on('change', '#modal_department_id', function () {
                var department_id = $(this).val();
                getDesignation(department_id);
            });

            // Dynamic User loading based on Team (Designation)
            $(document).on('change', '#modal_designation_id', function () {
                var designation_id = $(this).val();
                var department_id = $('#modal_department_id').val();

                // Reverse Auto-selection: If a team is selected but its department isn't, 
                // we should try to figure out the department and select it.
                if (designation_id && designation_id.length > 0 && (!department_id || department_id.length === 0)) {
                    autoSelectDepartment(designation_id);
                }

                getUsers(designation_id, department_id);
            });

            function autoSelectDepartment(designation_ids) {
                $.ajax({
                    url: '{{ route("lead.json.designation") }}', // Reuse same route but with designation_id to get parent dept
                    type: 'POST',
                    data: {
                        "designation_id": designation_ids,
                        "get_parent": 1,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function (data) {
                        if (data && data.department_ids) {
                            var deptSelect = document.getElementById('modal_department_id');
                            var inst = choicesInstances.find(i => i.passedElement && i.passedElement.element === deptSelect);
                            if (inst) {
                                inst.setChoiceByValue(data.department_ids);
                            }
                        }
                    }
                });
            }

            function getDesignation(did) {
                $.ajax({
                    url: '{{ route("lead.json.designation") }}',
                    type: 'POST',
                    data: {
                        "department_id": did,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function (data) {
                        // Destroy existing Choices instance for designation
                        var designationSelect = document.getElementById('modal_designation_id');
                        var outputInstance = choicesInstances.find(i => i.passedElement && i.passedElement.element === designationSelect);

                        if (outputInstance) {
                            outputInstance.destroy();
                            choicesInstances = choicesInstances.filter(i => i !== outputInstance);
                        }

                        $('#modal_designation_id').empty();
                        var emp_selct = ``;
                        // choices.js handles placeholder via data-placeholder or config, but empty option is good for native fallback
                        $.each(data, function (key, value) {
                            emp_selct += `<option value="${value.id}">${value.name}</option>`;
                        });
                        $('#modal_designation_id').html(emp_selct);

                        // Re-init Choices for this element
                        const newInstance = new Choices(designationSelect, choicesOptions);
                        choicesInstances.push(newInstance);

                        // Also refresh users based on new department (if no specific team selected yet)
                        getUsers(null, did);
                    }
                });
            }

            function getUsers(uid, did) {
                $.ajax({
                    url: '{{ route("lead.json.user") }}',
                    type: 'POST',
                    data: {
                        "designation_id": uid,
                        "department_id": did,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function (data) {
                        // Destroy existing Choices instance for responsible_person
                        var userSelect = document.getElementById('modal_responsible_person');
                        var outputInstance = choicesInstances.find(i => i.passedElement && i.passedElement.element === userSelect);

                        if (outputInstance) {
                            outputInstance.destroy();
                            choicesInstances = choicesInstances.filter(i => i !== outputInstance);
                        }

                        $('#modal_responsible_person').empty();
                        var emp_selct = ``;
                        $.each(data, function (key, value) {
                            emp_selct += `<option value="${key}">${value}</option>`;
                        });
                        $('#modal_responsible_person').html(emp_selct);

                        // Re-init Choices for this element
                        const newInstance = new Choices(userSelect, choicesOptions);
                        choicesInstances.push(newInstance);
                    }
                });
            }

            function applyFilters() {
                const url = new URL(window.location.href);
                const searchEl = document.getElementById('lead_search');
                if (searchEl && searchEl.value) url.searchParams.set('search', searchEl.value);
                else url.searchParams.delete('search');

                const params = {
                    'responsible_person': $('#modal_responsible_person').val(),
                    'stage_id': $('#modal_stage_id').val(),

                    'department_id': $('#modal_department_id').val(),
                    'designation_id': $('#modal_designation_id').val(),
                    'source_id': $('#modal_source_id').val(),
                    'created_by': $('#modal_created_by').val(),
                    'modified_by': $('#modal_modified_by').val()
                };

                Object.keys(params).forEach(key => url.searchParams.delete(key + '[]'));

                Object.keys(params).forEach(key => {
                    if (params[key] && params[key].length > 0) {
                        if (Array.isArray(params[key])) {
                            params[key].forEach(val => url.searchParams.append(key + '[]', val));
                        } else {
                            url.searchParams.set(key, params[key]);
                        }
                    }
                });

                const startEl = document.getElementById('modal_start_date');
                const endEl = document.getElementById('modal_end_date');

                if (startEl && startEl.value) url.searchParams.set('start_date', startEl.value);
                else url.searchParams.delete('start_date');

                if (endEl && endEl.value) url.searchParams.set('end_date', endEl.value);
                else url.searchParams.delete('end_date');

                const modStartEl = document.getElementById('modal_modified_start_date');
                const modEndEl = document.getElementById('modal_modified_end_date');

                if (modStartEl && modStartEl.value) url.searchParams.set('modified_start_date', modStartEl.value);
                else url.searchParams.delete('modified_start_date');

                if (modEndEl && modEndEl.value) url.searchParams.set('modified_end_date', modEndEl.value);
                else url.searchParams.delete('modified_end_date');

                if ($('#modal_duplicates').is(':checked')) url.searchParams.set('duplicates', '1');
                else url.searchParams.delete('duplicates');

                // Custom Fields
                const cfInputs = document.querySelectorAll('input[name^="custom_fields["]');
                cfInputs.forEach(input => {
                    const name = input.name;
                    if (input.value) {
                        url.searchParams.set(name, input.value);
                    } else {
                        url.searchParams.delete(name);
                    }
                });

                // Update URL and Reload DataTable via AJAX
                window.history.pushState({}, '', url.href);

                if (window.LaravelDataTables && window.LaravelDataTables["leads-table"]) {
                    window.LaravelDataTables["leads-table"].ajax.reload();
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('leadFilterModal')).hide();

                    // Update active filter count badge if it exists
                    updateFilterCountBadge();
                } else {
                    // Fallback for non-datatable views or if it fails
                    window.location.href = url.href;
                }
            }

            function updateFilterCountBadge() {
                const urlParams = new URLSearchParams(window.location.search);
                let count = 0;
                const filterKeys = ['responsible_person', 'stage_id', 'source_id', 'start_date', 'end_date', 'modified_start_date', 'modified_end_date', 'created_by', 'modified_by', 'duplicates', 'department_id', 'designation_id'];

                filterKeys.forEach(key => {
                    if (urlParams.get(key) || urlParams.get(key + '[]')) count++;
                });

                const badge = document.querySelector('.filter-count-badge');
                const btn = document.getElementById('advancedFilterBtn');

                if (count > 0) {
                    if (badge) {
                        badge.innerText = count;
                        badge.style.display = 'inline-block';
                    } else if (btn) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'badge rounded-pill bg-danger filter-count-badge shadow-sm';
                        newBadge.innerText = count;
                        btn.appendChild(newBadge);
                    }
                } else if (badge) {
                    badge.style.display = 'none';
                }
            }

            // Search Settings Save
            const saveSearchSettingsBtn = document.getElementById('saveSearchSettingsBtn');
            if (saveSearchSettingsBtn) {
                saveSearchSettingsBtn.addEventListener('click', function () {
                    const formData = new FormData(document.getElementById('searchSettingsForm'));
                    const selectedFields = [];
                    formData.forEach((value, key) => {
                        if (key === 'search_fields[]') selectedFields.push(value);
                    });

                    $.ajax({
                        url: '{{ route("leads.search.settings.save") }}',
                        type: 'POST',
                        data: {
                            fields: selectedFields,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            if (response.success) {
                                show_toastr('Success', response.success, 'success');
                                bootstrap.Modal.getInstance(document.getElementById('searchSettingsModal')).hide();
                            } else {
                                show_toastr('Error', response.error, 'error');
                            }
                        }
                    });
                });
            }
        });
    </script>
@endpush