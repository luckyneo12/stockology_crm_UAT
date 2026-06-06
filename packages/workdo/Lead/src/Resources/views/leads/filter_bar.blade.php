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
        
        /* Filter Badges Styles */
        .filter-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            margin: 2px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            cursor: default;
        }
        
        .filter-badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .filter-badge .btn-close {
            margin-left: 6px;
            font-size: 0.75rem;
            opacity: 0.8;
            transition: opacity 0.2s ease;
            cursor: pointer;
        }
        
        .filter-badge .btn-close:hover {
            opacity: 1;
        }
        
        .filter-badge.permanent {
            background: linear-gradient(45deg, #28a745, #20c997) !important;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }
        
        .filter-badge.permanent::before {
            content: 'pin';
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ffc107;
            color: #000;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            z-index: 1;
        }
        
        /* Team filter special styling */        .filter-badge[data-filter-type="department_id"] { background-color: #6c757d !important; color: white !important; }
        .filter-badge[data-filter-type="team_id"] { background-color: #20c997 !important; color: white !important; }
        
        /* Permanent styling specific overrides */
        .filter-badge[data-filter-type="search"].permanent { background-color: #0b4e85 !important; }
        .filter-badge[data-filter-type="department_id"].permanent { background-color: #5c636a !important; }
        .filter-badge[data-filter-type="team_id"].permanent { background-color: #1aa179 !important; }
        
        .filter-badge[data-filter-type="stage_id"] {
            background: linear-gradient(45deg, #fd7e14, #e85d04) !important;
            border-left: 3px solid #d35400;
        }
        
        .filter-badge[data-filter-type="stage_id"].permanent {
            background: linear-gradient(45deg, #28a745, #20c997) !important;
        }
        
        #filterBadgesContainer {
            background: rgba(94, 114, 228, 0.05);
            border: 1px solid rgba(94, 114, 228, 0.1);
            border-radius: 10px;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-check-input:checked {
            background-color: #28a745;
            border-color: #28a745;
        }
        
        .form-check-input:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
    </style>
@endpush

<div class="row align-items-center mb-3 g-2 leads-filter-bar-row d-none">
</div>

<!-- Applied Filter Badges -->
<div class="row mb-3" id="filterBadgesContainer" style="display: none;">
    <div class="col-12">
        <div class="d-flex align-items-center flex-wrap gap-2 p-3 bg-light rounded-3 border">
            <span class="text-muted me-2 fw-bold">{{ __('Active Filters:') }}</span>
            <div id="filterBadges"></div>
            <div class="ms-auto d-flex gap-2">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="permanentFilter" onchange="togglePermanentFilter()">
                    <label class="form-check-label" for="permanentFilter">
                        {{ __('Permanent Filter') }}
                    </label>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearAllFilters()">
                    <i class="ti ti-x"></i> {{ __('Clear All') }}
                </button>
            </div>
        </div>
    </div>
</div>
<!-- End Filter Badges -->

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
                                        {!! Form::select('team_id[]', $teams, request('team_id'), ['class' => 'form-control choices-js-filter', 'multiple' => 'multiple', 'id' => 'modal_team_id', 'data-placeholder' => __('Select Team'), 'disabled' => $teamDisabled]) !!}
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
        // Global choices instances array
        let choicesInstances = [];

        $(document).ready(function () {
            const modalEl = document.getElementById('leadFilterModal');

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

            // Restore filter values from the current URL into the modal fields
            function restoreFiltersFromURL() {
                const urlParams = new URLSearchParams(window.location.search);
                
                // Map of URL param keys -> modal element IDs  
                const arrayFilters = {
                    'responsible_person': 'modal_responsible_person',
                    'stage_id': 'modal_stage_id',
                    'department_id': 'modal_department_id',
                    'team_id': 'modal_team_id',
                    'source_id': 'modal_source_id',
                    'created_by': 'modal_created_by',
                    'modified_by': 'modal_modified_by'
                };
                
                // Restore multi-select Choices.js filters EXCEPT the dependent ones (responsible_person, department_id, team_id)
                // which we will restore sequentially below to prevent race conditions or overwriting options.
                const dependentFilters = ['department_id', 'team_id', 'responsible_person'];
                Object.keys(arrayFilters).forEach(key => {
                    if (dependentFilters.includes(key)) return;
                    const values = urlParams.getAll(key + '[]');
                    if (values.length > 0) {
                        const el = document.getElementById(arrayFilters[key]);
                        if (el) {
                            const instance = choicesInstances.find(i => i.passedElement && i.passedElement.element === el);
                            if (instance) {
                                try {
                                    instance.setChoiceByValue(values);
                                } catch(e) {
                                    console.log('Could not restore filter', key, e);
                                }
                            }
                        }
                    }
                });
                
                // Restore date fields and handle flatpickr resets
                const dateFields = {
                    'start_date': 'modal_start_date',
                    'end_date': 'modal_end_date',
                    'modified_start_date': 'modal_modified_start_date',
                    'modified_end_date': 'modal_modified_end_date'
                };
                
                Object.keys(dateFields).forEach(key => {
                    const val = urlParams.get(key);
                    const el = document.getElementById(dateFields[key]);
                    if (el) {
                        el.value = val || '';
                        // Update flatpickr if initialized
                        if (el._flatpickr) {
                            if (val) {
                                el._flatpickr.setDate(val, false);
                            } else {
                                el._flatpickr.clear();
                            }
                        }
                    }
                });
                
                // Restore duplicates checkbox
                const dupVal = urlParams.get('duplicates');
                $('#modal_duplicates').prop('checked', dupVal === '1');
                
                // Restore custom fields
                const cfInputs = document.querySelectorAll('input[name^="custom_fields["]');
                cfInputs.forEach(input => {
                    const val = urlParams.get(input.name);
                    input.value = val || '';
                });

                // Sequentially restore dependent dropdowns (Department -> Team -> User)
                const restoredDept = urlParams.getAll('department_id[]');
                const restoredTeam = urlParams.getAll('team_id[]');
                const restoredUser = urlParams.getAll('responsible_person[]');

                if (restoredDept.length > 0) {
                    const deptEl = document.getElementById('modal_department_id');
                    const deptInstance = choicesInstances.find(i => i.passedElement && i.passedElement.element === deptEl);
                    if (deptInstance) {
                        try { deptInstance.setChoiceByValue(restoredDept); } catch(e) {}
                    }
                    getDesignation(restoredDept, restoredTeam, restoredUser);
                } else if (restoredTeam.length > 0) {
                    const teamEl = document.getElementById('modal_team_id');
                    const teamInstance = choicesInstances.find(i => i.passedElement && i.passedElement.element === teamEl);
                    if (teamInstance) {
                        try { teamInstance.setChoiceByValue(restoredTeam); } catch(e) {}
                    }
                    getUsers(restoredTeam, null, restoredUser);
                } else if (restoredUser.length > 0) {
                    const userEl = document.getElementById('modal_responsible_person');
                    const userInstance = choicesInstances.find(i => i.passedElement && i.passedElement.element === userEl);
                    if (userInstance) {
                        try { userInstance.setChoiceByValue(restoredUser); } catch(e) {}
                    }
                }
            }

            modalEl.addEventListener('shown.bs.modal', function () {
                initChoices();
                initFlatpickr();
                restoreFiltersFromURL();
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
                    team_id: $('#modal_team_id').val(),
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
                if (!filters) return; // Only process saved preset badges

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

            // Dynamic User loading based on Team
            $(document).on('change', '#modal_team_id', function () {
                var team_id = $(this).val();
                var department_id = $('#modal_department_id').val();

                // Reverse Auto-selection: If a team is selected but its department isn't, 
                // we should try to figure out the department and select it.
                if (team_id && team_id.length > 0 && (!department_id || department_id.length === 0)) {
                    autoSelectDepartment(team_id);
                }

                getUsers(team_id, department_id);
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

            function getDesignation(did, selectedTeams = null, selectedUsers = null) {
                $.ajax({
                    url: '{{ route("lead.json.designation") }}',
                    type: 'POST',
                    data: {
                        "department_id": did,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function (data) {
                        console.log("getDesignation raw response data:", data);
                        // Destroy existing Choices instance for team
                        var teamSelect = document.getElementById('modal_team_id');
                        var outputInstance = choicesInstances.find(i => i.passedElement && i.passedElement.element === teamSelect);

                        if (outputInstance) {
                            outputInstance.destroy();
                            choicesInstances = choicesInstances.filter(i => i !== outputInstance);
                        }

                        $('#modal_team_id').empty();
                        var emp_selct = ``;
                        // choices.js handles placeholder via data-placeholder or config, but empty option is good for native fallback
                        $.each(data, function (key, value) {
                            let optionId = key;
                            let optionName = value;
                            
                            if (value && typeof value === 'object') {
                                optionId = value.id !== undefined ? value.id : (value.value !== undefined ? value.value : key);
                                optionName = value.name !== undefined ? value.name : (value.label !== undefined ? value.label : JSON.stringify(value));
                            }
                            emp_selct += `<option value="${optionId}">${optionName}</option>`;
                        });
                        $('#modal_team_id').html(emp_selct);

                        // Re-init Choices for this element
                        const newInstance = new Choices(teamSelect, choicesOptions);
                        choicesInstances.push(newInstance);

                        if (selectedTeams && selectedTeams.length > 0) {
                            try {
                                newInstance.setChoiceByValue(selectedTeams);
                            } catch (e) {
                                console.log('Could not set restored teams', e);
                            }
                        }

                        // Also refresh users based on new department (if no specific team selected yet)
                        const currentTeams = selectedTeams || $('#modal_team_id').val() || [];
                        getUsers(currentTeams.length > 0 ? currentTeams : null, did, selectedUsers);
                    }
                });
            }

            function getUsers(uid, did, selectedUsers = null) {
                $.ajax({
                    url: '{{ route("lead.json.user") }}',
                    type: 'POST',
                    data: {
                        "designation_id": uid, // Keeping as designation_id payload to preserve json route compatibility while sending team IDs
                        "department_id": did,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function (data) {
                        console.log("getUsers raw response data:", data);
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
                            let optionId = key;
                            let optionName = value;
                            
                            if (value && typeof value === 'object') {
                                optionId = value.id !== undefined ? value.id : (value.value !== undefined ? value.value : key);
                                optionName = value.name !== undefined ? value.name : (value.label !== undefined ? value.label : JSON.stringify(value));
                            }
                            emp_selct += `<option value="${optionId}">${optionName}</option>`;
                        });
                        $('#modal_responsible_person').html(emp_selct);

                        // Re-init Choices for this element
                        const newInstance = new Choices(userSelect, choicesOptions);
                        choicesInstances.push(newInstance);

                        if (selectedUsers && selectedUsers.length > 0) {
                            try {
                                newInstance.setChoiceByValue(selectedUsers);
                            } catch (e) {
                                console.log('Could not set restored users', e);
                            }
                        }
                    }
                });
            }

            // Helper function to get values from Choices.js instances
            function getChoicesValue(elementId) {
                const element = document.getElementById(elementId);
                if (!element) {
                    console.log(`Element not found: ${elementId}`);
                    return [];
                }
                
                // Try to find Choices.js instance
                const instance = choicesInstances.find(i => i.passedElement && i.passedElement.element === element);
                
                if (instance) {
                    // Get values from Choices.js instance
                    const values = instance.getValue(true) || []; // true returns array of values
                    console.log(`Choices.js values for ${elementId}:`, values);
                    return values;
                } else {
                    // Fallback to jQuery val()
                    const values = $(element).val() || [];
                    console.log(`jQuery values for ${elementId}:`, values);
                    return values;
                }
            }

            function applyFilters() {
                const url = new URL(window.location.href);
                const searchEl = document.getElementById('lead_search');
                if (searchEl && searchEl.value) url.searchParams.set('search', searchEl.value);
                else url.searchParams.delete('search');

                // Get values from Choices.js instances or fallback to jQuery
                const params = {
                    'responsible_person': $('#modal_responsible_person').val() || [],
                    'stage_id': $('#modal_stage_id').val() || [],
                    'department_id': $('#modal_department_id').val() || [],
                    'team_id': $('#modal_team_id').val() || [],
                    'source_id': $('#modal_source_id').val() || [],
                    'created_by': $('#modal_created_by').val() || [],
                    'modified_by': $('#modal_modified_by').val() || []
                };
                
                console.log('All filter params:', params);

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

                // Force show/hide Modified Date column based on URL parameters
                var hasModFilter = url.searchParams.has("modified_start_date") || url.searchParams.has("modified_end_date");
                if (window.LaravelDataTables && window.LaravelDataTables["leads-table"]) {
                    var table = window.LaravelDataTables["leads-table"];
                    var columns = table.settings()[0].aoColumns;
                    var modColIndex = -1;
                    columns.forEach(function(col, idx) {
                        if (col.name === "updated_at" || col.data === "updated_at") {
                            modColIndex = idx;
                        }
                    });
                    if (modColIndex !== -1) {
                        table.column(modColIndex).visible(hasModFilter);
                        var toggleSwitch = document.getElementById("col_toggle_" + modColIndex);
                        if (toggleSwitch) toggleSwitch.checked = hasModFilter;
                    }
                }

                if (window.LaravelDataTables && window.LaravelDataTables["leads-table"]) {
                    window.LaravelDataTables["leads-table"].ajax.reload();
                    let filterModalEl = document.getElementById('leadFilterModal');
                    if (filterModalEl) {
                        let filterModal = bootstrap.Modal.getInstance(filterModalEl);
                        if (!filterModal) {
                            filterModal = new bootstrap.Modal(filterModalEl);
                        }
                        filterModal.hide();
                    }

                    // Update active filter count badge if it exists
                    updateFilterCountBadge();
                    
                    // Manually trigger badge update after a short delay
                    setTimeout(() => {
                        updateFilterBadges();
                    }, 500);
                } else {
                    // Fallback for non-datatable views or if it fails
                    window.location.href = url.href;
                }
            }

            function updateFilterCountBadge() {
                const urlParams = new URLSearchParams(window.location.search);
                let count = 0;
                const filterKeys = ['responsible_person', 'stage_id', 'source_id', 'start_date', 'end_date', 'modified_start_date', 'modified_end_date', 'created_by', 'modified_by', 'duplicates', 'department_id', 'team_id'];

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

            // Advanced Filter Badges and Persistent Filters
            $(document).ready(function() {
                // Initialize filter badges
                initFilterBadges();
            });

            function initFilterBadges() {
                // Load saved filters on page load
                loadSavedFilters();
                
                // Update filter badges when URL changes
                updateFilterBadges();
                
                // Listen for URL changes (after filter application)
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'href') {
                            updateFilterBadges();
                        }
                    });
                });
                
                // Observe URL changes
                observer.observe(document.querySelector('link[href*="leads"]') || document.body, { 
                    attributes: true, 
                    subtree: true, 
                    attributeFilter: ['href'] 
                });
                
                // Add event delegation for dynamic badge removal
                $(document).on('click', '.filter-badge .remove-filter-btn', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Remove button clicked!');
                    const badge = $(this).closest('.filter-badge');
                    const type = badge.data('filter-type');
                    const value = badge.data('filter-value');
                    console.log('Type:', type, 'Value:', value);
                    if (typeof window.removeFilter === 'function') {
                        window.removeFilter(type, value);
                    } else {
                        removeFilter(type, value);
                    }
                });
                
                // Add event delegation for permanent filter toggle
                $(document).on('change', '#permanentFilter', function() {
                    togglePermanentFilter();
                });
                
                // Add event delegation for clear all filters
                $(document).on('click', '[onclick*="clearAllFilters"]', function(e) {
                    e.preventDefault();
                    clearAllFilters();
                });
                
                // Add event handlers for advanced filter modal buttons
                $(document).on('click', '#applyFiltersBtn', function(e) {
                    e.preventDefault();
                    applyFilters();
                });
                
                $(document).on('click', '#clearFiltersBtn', function(e) {
                    e.preventDefault();
                    clearAdvancedFilters();
                });
            }
            
            // Clear advanced filter modal fields
            function clearAdvancedFilters() {
                // Clear Choices.js instances
                const filterFields = ['modal_responsible_person', 'modal_stage_id', 'modal_department_id', 'modal_team_id', 'modal_source_id', 'modal_created_by', 'modal_modified_by'];
                
                filterFields.forEach(fieldId => {
                    const element = document.getElementById(fieldId);
                    if (element) {
                        const instance = choicesInstances.find(i => i.passedElement && i.passedElement.element === element);
                        if (instance) {
                            instance.removeActiveItems();
                        } else {
                            $(element).val([]);
                        }
                    }
                });
                
                // Clear date fields and flatpickr instances
                $('#modal_start_date, #modal_end_date, #modal_modified_start_date, #modal_modified_end_date').each(function() {
                    if (this._flatpickr) {
                        this._flatpickr.clear();
                    } else {
                        $(this).val('');
                    }
                });
                
                // Clear checkbox
                $('#modal_duplicates').prop('checked', false);
                
                // Clear custom fields
                $('input[name^="custom_fields["]').val('');
            } 

            // Update filter badges display
            function updateFilterBadges() {
                const badges = [];
                const container = $('#filterBadgesContainer');
                const badgesDiv = $('#filterBadges');
                
                // Check if elements exist
                if (!container.length || !badgesDiv.length) {
                    return;
                }
                
                // Get current URL parameters
                const urlParams = new URLSearchParams(window.location.search);
                
                // Create badges for active filters
                const filterMappings = {
                    'search': '{{ __("Search") }}',
                    'responsible_person': '{{ __("Responsible Person") }}',
                    'stage_id': '{{ __("Stage") }}',
                    'source_id': '{{ __("Source") }}',
                    'start_date': '{{ __("Start Date") }}',
                    'end_date': '{{ __("End Date") }}',
                    'modified_start_date': '{{ __("Modified Start Date") }}',
                    'modified_end_date': '{{ __("Modified End Date") }}',
                    'created_by': '{{ __("Created By") }}',
                    'modified_by': '{{ __("Modified By") }}',
                    'department_id': '{{ __("Department") }}',
                    'team_id': '{{ __("Team") }}',
                    'duplicates': '{{ __("Duplicates") }}'
                };
                
                // Process each filter
                if (filterMappings && typeof filterMappings === 'object') {
                    Object.keys(filterMappings).forEach(key => {
                        const values = urlParams.getAll(key + '[]');
                        const singleValue = urlParams.get(key);
                        
                        if (values.length > 0) {
                            values.forEach(value => {
                                badges.push(createBadge(key, filterMappings[key], value));
                            });
                        } else if (singleValue) {
                            badges.push(createBadge(key, filterMappings[key], singleValue));
                        }
                    });
                }
                
                // Display badges or hide container
                if (badges.length > 0) {
                    badgesDiv.html(badges.join(''));
                    container.show();
                } else {
                    badgesDiv.empty();
                    container.hide();
                }
            }

            // Extract human-readable text for a filter value from the corresponding select dropdown
            function getOptionText(type, value) {
                var selectId = '#modal_' + type;
                var $select = $(selectId);
                
                if ($select.length) {
                    var $option = $select.find('option[value="' + value + '"]');
                    if ($option.length && $option.text().trim() !== '') {
                        return $option.text().trim();
                    }
                }
                
                return value; // fallback to the raw value
            }

            // Create individual filter badge
            function createBadge(type, label, value) {
                const displayValue = getOptionText(type, value);
                const permanentClass = $('#permanentFilter').is(':checked') ? 'permanent' : '';
                return `
                    <span class="badge bg-primary filter-badge ${permanentClass}" data-filter-type="${type}" data-filter-value="${value}">
                        ${label}: ${displayValue}
                        <button type="button" class="btn-close btn-close-white ms-1 remove-filter-btn"></button>
                    </span>
                `;
            }

            // Remove individual filter
            function removeFilter(type, value) {
                console.log('Removing filter:', type, 'value:', value);
                const url = new URL(window.location.href);
                
                // Handle array parameters
                if (type.includes('person') || type.includes('stage_id') || type.includes('source_id') || 
                    type.includes('created_by') || type.includes('modified_by') || type.includes('department_id') || 
                    type.includes('team_id')) {
                    
                    const values = url.searchParams.getAll(type + '[]');
                    console.log('Current values for', type, ':', values);
                    const newValues = values.filter(v => String(v) !== String(value));
                    console.log('New values after removal:', newValues);
                    
                    url.searchParams.delete(type + '[]');
                    newValues.forEach(v => url.searchParams.append(type + '[]', v));
                } else {
                    url.searchParams.delete(type);
                }
                
                console.log('Final URL:', url.toString());
                
                // Update URL without page reload
                window.history.pushState({}, '', url.toString());
                
                // Update saved filters in localStorage if permanent filter is enabled
                saveFilters(url);

                // Force show/hide Modified Date column based on URL parameters
                var hasModFilter = url.searchParams.has("modified_start_date") || url.searchParams.has("modified_end_date");
                if (window.LaravelDataTables && window.LaravelDataTables["leads-table"]) {
                    var table = window.LaravelDataTables["leads-table"];
                    var columns = table.settings()[0].aoColumns;
                    var modColIndex = -1;
                    columns.forEach(function(col, idx) {
                        if (col.name === "updated_at" || col.data === "updated_at") {
                            modColIndex = idx;
                        }
                    });
                    if (modColIndex !== -1) {
                        table.column(modColIndex).visible(hasModFilter);
                        var toggleSwitch = document.getElementById("col_toggle_" + modColIndex);
                        if (toggleSwitch) toggleSwitch.checked = hasModFilter;
                    }
                }
                
                // Update badges dynamically
                setTimeout(() => {
                    updateFilterBadges();
                }, 100);
                
                // Reload DataTable if exists
                if (window.LaravelDataTables && window.LaravelDataTables["leads-table"]) {
                    window.LaravelDataTables["leads-table"].ajax.reload();
                } else {
                    window.location.href = url.toString();
                }
            }
            window.removeFilter = removeFilter;
            window.clearAllFilters = clearAllFilters;

            // Clear all filters
            function clearAllFilters() {
                const url = new URL(window.location.href);
                const filterKeys = ['search', 'responsible_person', 'stage_id', 'source_id', 'start_date', 'end_date', 
                                    'created_by', 'modified_by', 'department_id', 'team_id', 'duplicates', 'modified_start_date', 'modified_end_date'];
                
                filterKeys.forEach(key => {
                    url.searchParams.delete(key);
                    url.searchParams.delete(key + '[]');
                });
                
                // Clear saved filters
                localStorage.removeItem('permanentLeadsFilters');
                $('#permanentFilter').prop('checked', false);
                
                // Update URL and reload
                window.location.href = url.href;
            }

            // Toggle permanent filter mode
            function togglePermanentFilter() {
                const isPermanent = $('#permanentFilter').is(':checked');
                
                if (isPermanent) {
                    saveFilters();
                    show_toastr('{{ __("Success") }}', '{{ __("Filter saved permanently") }}', 'success');
                } else {
                    localStorage.removeItem('permanentLeadsFilters');
                    show_toastr('{{ __("Info") }}', '{{ __("Permanent filter disabled") }}', 'info');
                }
                
                updateFilterBadges();
            }

            // Save filters to localStorage
            function saveFilters(urlObj = null) {
                const url = urlObj || new URL(window.location.href);
                const filters = {};
                
                // Save all filter parameters
                for (const key of url.searchParams.keys()) {
                    if (key !== 'page' && key !== 'length') {
                        if (key.endsWith('[]')) {
                            const cleanKey = key.slice(0, -2);
                            filters[cleanKey] = url.searchParams.getAll(cleanKey + '[]');
                        } else {
                            filters[key] = url.searchParams.get(key);
                        }
                    }
                }
                
                if (Object.keys(filters).length > 0) {
                    filters.permanent = true;
                    localStorage.setItem('permanentLeadsFilters', JSON.stringify(filters));
                } else {
                    localStorage.removeItem('permanentLeadsFilters');
                }
            }
            window.saveFilters = saveFilters;

            // Load saved filters from localStorage
            function loadSavedFilters() {
                const savedFilters = localStorage.getItem('permanentLeadsFilters');
                
                if (savedFilters) {
                    try {
                        const filters = JSON.parse(savedFilters);
                        
                        if (filters && filters.permanent) {
                            // Check if current URL has filters, if not, apply saved ones
                            const currentParams = new URLSearchParams(window.location.search);
                            let hasFilters = false;
                            
                            for (const key in filters) {
                                if (key !== 'permanent' && filters[key]) {
                                    hasFilters = true;
                                    break;
                                }
                            }
                            
                            if (!hasFilters) {
                                // Apply saved filters
                                const url = new URL(window.location.search);
                                
                                for (const key in filters) {
                                    if (key !== 'permanent' && filters[key]) {
                                        if (Array.isArray(filters[key])) {
                                            url.searchParams.delete(key);
                                            filters[key].forEach(v => url.searchParams.append(key + '[]', v));
                                        } else {
                                            url.searchParams.set(key, filters[key]);
                                        }
                                    }
                                }
                                
                                // Set permanent filter checkbox
                                $('#permanentFilter').prop('checked', true);
                                
                                // Redirect to apply filters
                                if (url.toString() !== window.location.href) {
                                    window.location.href = url.toString();
                                }
                            } else {
                                // Set permanent filter checkbox if filters exist
                                $('#permanentFilter').prop('checked', true);
                            }
                            
                            // Show notification
                            show_toastr('{{ __("Info") }}', '{{ __("Permanent filters applied") }}', 'info');
                        }
                    } catch (e) {
                        console.error('Error loading saved filters:', e);
                    }
                }
            }

            // Auto-apply filters on page navigation
            window.addEventListener('beforeunload', function() {
                if ($('#permanentFilter').is(':checked')) {
                    saveFilters();
                }
            });

            // Auto-refresh DataTable for real-time updates
            let autoRefreshInterval;
            
            function startAutoRefresh() {
                // Clear existing interval
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                }
                
                // Set new interval to refresh every 30 seconds
                autoRefreshInterval = setInterval(function() {
                    if (window.LaravelDataTables && window.LaravelDataTables["leads-table"]) {
                        console.log('Auto-refreshing DataTable for real-time updates...');
                        window.LaravelDataTables["leads-table"].ajax.reload(null, false); // false = no loading indicator
                    }
                }, 30000); // 30 seconds
            }
            
            function stopAutoRefresh() {
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                    autoRefreshInterval = null;
                }
            }
            
            // Start auto-refresh when page loads and filters are active
            $(document).ready(function() {
                const urlParams = new URLSearchParams(window.location.search);
                let hasActiveFilters = false;
                
                // Check if any filters are active
                ['responsible_person', 'stage_id', 'source_id', 'start_date', 'end_date', 'created_by', 'modified_by', 'department_id', 'team_id', 'duplicates', 'search'].forEach(key => {
                    if (urlParams.get(key) || urlParams.get(key + '[]')) {
                        hasActiveFilters = true;
                    }
                });
                
                if (hasActiveFilters) {
                    console.log('Active filters detected, starting auto-refresh...');
                    startAutoRefresh();
                }
                
                // Stop auto-refresh when user leaves the page
                window.addEventListener('beforeunload', function() {
                    stopAutoRefresh();
                });
                
                // Stop auto-refresh when modal is open (to prevent conflicts)
                $('#leadFilterModal').on('show.bs.modal', function() {
                    stopAutoRefresh();
                });
                
                // Restart auto-refresh when modal is closed
                $('#leadFilterModal').on('hidden.bs.modal', function() {
                    const urlParams = new URLSearchParams(window.location.search);
                    let hasActiveFilters = false;
                    
                    ['responsible_person', 'stage_id', 'source_id', 'start_date', 'end_date', 'created_by', 'modified_by', 'department_id', 'team_id', 'duplicates', 'search'].forEach(key => {
                        if (urlParams.get(key) || urlParams.get(key + '[]')) {
                            hasActiveFilters = true;
                        }
                    });
                    
                    if (hasActiveFilters) {
                        startAutoRefresh();
                    }
                });
            });
        
        }); // Missing closing brace for $(document).ready
        
        // Global function for filter badges update
        window.updateFilterBadges = function() {
            const badges = [];
            const container = $('#filterBadgesContainer');
            const badgesDiv = $('#filterBadges');
            
            // Check if elements exist
            if (!container.length || !badgesDiv.length) {
                return;
            }
            
            // Get current URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            
            // Create badges for active filters
            const filterMappings = {
                'search': '{{ __("Search") }}',
                'responsible_person': '{{ __("Responsible Person") }}',
                'stage_id': '{{ __("Stage") }}',
                'source_id': '{{ __("Source") }}',
                'start_date': '{{ __("Start Date") }}',
                'end_date': '{{ __("End Date") }}',
                'created_by': '{{ __("Created By") }}',
                'modified_by': '{{ __("Modified By") }}',
                'department_id': '{{ __("Department") }}',
                'team_id': '{{ __("Team") }}',
                'duplicates': '{{ __("Duplicates") }}'
            };
            
            // Process each filter
            if (filterMappings && typeof filterMappings === 'object') {
                Object.keys(filterMappings).forEach(key => {
                    const values = urlParams.getAll(key + '[]');
                    const singleValue = urlParams.get(key);
                    
                    if (values.length > 0) {
                        values.forEach(value => {
                            badges.push(window.createBadge(key, filterMappings[key], value));
                        });
                    } else if (singleValue) {
                        badges.push(window.createBadge(key, filterMappings[key], singleValue));
                    }
                });
            }
            
            // Display badges or hide container
            if (badges.length > 0) {
                badgesDiv.html(badges.join(''));
                container.show();
            } else {
                badgesDiv.empty();
                container.hide();
            }
        };
        
        // Global function for badge creation
        window.createBadge = function(type, label, value) {
            const permanentClass = $('#permanentFilter').is(':checked') ? 'permanent' : '';
            
            // Get display name for the value
            let displayValue = value;
            
            // Try to get name from select options
            const selectElement = document.getElementById('modal_' + type);
            if (selectElement) {
                const option = selectElement.querySelector(`option[value="${value}"]`);
                if (option) {
                    displayValue = option.textContent.trim();
                }
            }
            
            // Special mappings for common filters
            const nameMappings = {
                'stage_id': {
                    '1': 'New',
                    '2': 'Interested', 
                    '3': 'Follow Up',
                    '4': 'Proposal',
                    '5': 'Negotiation',
                    '6': 'Won',
                    '7': 'Lost',
                    '8': 'Interested'
                },
                'team_id': {},
                'department_id': {
                    '1': 'Bhopal Branch',
                    '2': 'Indore Branch',
                    '3': 'Mumbai Branch',
                    '4': 'Delhi Branch',
                    '5': 'Pune Branch'
                },
                'source_id': {
                    '1': 'Website',
                    '2': 'Facebook',
                    '3': 'LinkedIn',
                    '4': 'Referral',
                    '5': 'Google',
                    '6': 'Email Campaign',
                    '7': 'Cold Call'
                },
                'responsible_person': {
                    '1': 'John Smith',
                    '2': 'Sarah Johnson',
                    '3': 'Mike Wilson',
                    '4': 'Emily Davis',
                    '5': 'Robert Brown',
                    '6': 'Lisa Anderson',
                    '7': 'David Miller',
                    '8': 'Jennifer Taylor'
                },
                'created_by': {
                    '1': 'Admin User',
                    '2': 'Manager',
                    '3': 'Sales Team'
                },
                'modified_by': {
                    '1': 'Admin User',
                    '2': 'Manager',
                    '3': 'Sales Team'
                }
            };
            
            // Use mapping if available
            if (nameMappings[type] && nameMappings[type][value]) {
                displayValue = nameMappings[type][value];
            }
            
            return `
                <span class="badge bg-primary filter-badge ${permanentClass}" data-filter-type="${type}" data-filter-value="${value}">
                    ${label}: ${displayValue}
                    <button type="button" class="btn-close btn-close-white ms-1 remove-filter-btn"></button>
                </span>
            `;
        };
        
        // Global function for filter removal
        window.removeFilter = function(type, value) {
            console.log('Removing filter:', type, 'value:', value);
            
            // Get current URL and remove parameter
            const currentUrl = window.location.href;
            const url = new URL(currentUrl);
            
            // Handle array parameters (for multi-select filters)
            if (type.includes('person') || type.includes('stage_id') || type.includes('source_id') || 
                type.includes('created_by') || type.includes('modified_by') || type.includes('department_id') || 
                type.includes('team_id')) {
                
                // Get current values for this filter type
                const currentValues = url.searchParams.getAll(type + '[]');
                console.log('Current values for', type, ':', currentValues);
                
                // Remove the specific value
                const newValues = currentValues.filter(v => String(v) !== String(value));
                console.log('New values after removal:', newValues);
                
                // Clear all existing values
                url.searchParams.delete(type + '[]');
                
                // Add back the remaining values
                newValues.forEach(v => url.searchParams.append(type + '[]', v));
            } else {
                // Handle single value parameters
                url.searchParams.delete(type);
            }
            
            console.log('Final URL:', url.toString());
            
            // Clear the corresponding filter in advanced filter modal
            clearAdvancedFilterValue(type, value);
            
            // Update saved filters in localStorage if permanent filter is enabled
            if (typeof window.saveFilters === 'function') {
                window.saveFilters(url);
            }
            
            // Update badges immediately
            setTimeout(() => {
                window.updateFilterBadges();
                updateFilterCountBadge();
            }, 50);
            
            // Reload DataTable if exists
            if (window.LaravelDataTables && window.LaravelDataTables["leads-table"]) {
                // Update URL and reload DataTable
                window.history.pushState({}, '', url.toString());
                window.LaravelDataTables["leads-table"].ajax.reload();
            } else {
                // Fallback: reload the page
                window.location.href = url.toString();
            }
        };
        
        // Clear specific filter value in advanced filter modal
        function clearAdvancedFilterValue(type, value) {
            console.log('Clearing advanced filter:', type, 'value:', value);
            
            const modalElementId = 'modal_' + type;
            const element = document.getElementById(modalElementId);
            
            if (element) {
                // Check if it's a Choices.js instance
                const instance = choicesInstances.find(i => i.passedElement && i.passedElement.element === element);
                
                if (instance) {
                    // Remove specific value from Choices.js
                    const currentValues = instance.getValue(true) || [];
                    const newValues = currentValues.filter(v => String(v) !== String(value));
                    instance.removeActiveItems();
                    newValues.forEach(v => instance.setChoiceByValue(v));
                    console.log('Choices.js updated:', newValues);
                } else {
                    // Fallback to jQuery for regular select
                    const currentValues = $(element).val() || [];
                    const newValues = currentValues.filter(v => String(v) !== String(value));
                    $(element).val(newValues);
                    console.log('jQuery select updated:', newValues);
                }
            }
        }
        </script>
    @endpush