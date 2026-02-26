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
</style>
@endpush

<div class="row align-items-center mb-3 g-2">
    <div class="col-xl-4 col-lg-5 col-md-7 col-sm-12">
        <div class="input-group input-group shadow-sm rounded">
            <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
            <input type="text" id="lead_search" class="form-control border-start-0 ps-0" placeholder="{{ __('Quick search by name or subject...') }}" value="{{ request('search') }}">
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
        <button type="button" class="btn btn-primary btn-icon shadow-sm position-relative" data-bs-toggle="modal" data-bs-target="#leadFilterModal" id="advancedFilterBtn">
            <span class="btn-inner--icon"><i class="ti ti-adjustments-horizontal me-1"></i></span>
            <span class="btn-inner--text">{{ __('Advanced Filter') }}</span>
            @php
                $activeFilterCount = 0;
                $filterKeys = ['responsible_person', 'stage_id', 'source_id', 'start_date', 'end_date', 'created_by', 'modified_by', 'duplicates'];
                foreach($filterKeys as $key) {
                    if(request()->has($key) && !empty(request($key))) $activeFilterCount++;
                }
            @endphp
            @if($activeFilterCount > 0)
                <span class="badge rounded-pill bg-danger filter-count-badge shadow-sm">{{ $activeFilterCount }}</span>
            @endif
        </button>
    </div>
    @if($activeFilterCount > 0 || request()->has('search'))
    <div class="col-auto">
        <button type="button" class="btn btn-light-danger btn-icon shadow-sm" id="clearAllFiltersHome" data-bs-toggle="tooltip" title="{{ __('Clear All Filters') }}">
            <i class="ti ti-trash-x"></i>
        </button>
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
                            <h6 class="text-muted text-uppercase fw-bold small m-0">{{ __('Lead Assignment & Timing') }}</h6>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 text-start">
                                <div class="form-group mb-0 text-start">
                                    <label class="form-label fw-bold text-dark mb-1">{{ __('Responsible Person') }}</label>
                                    {!! Form::select('responsible_person[]', $users, request('responsible_person'), ['class' => 'form-control choices-js-filter', 'multiple' => 'multiple', 'id' => 'modal_responsible_person']) !!}
                                </div>
                            </div>
                            <div class="col-md-6 text-start">
                                <div class="form-group mb-0 text-start">
                                    <label class="form-label fw-bold text-dark mb-1">{{ __('Lead Stage') }}</label>
                                    {!! Form::select('stage_id[]', $stages, request('stage_id'), ['class' => 'form-control choices-js-filter', 'multiple' => 'multiple', 'id' => 'modal_stage_id']) !!}
                                </div>
                            </div>
                            <div class="col-md-12 text-start mt-3">
                                <div class="form-group mb-0">
                                    <label class="form-label fw-bold text-dark mb-1">{{ __('Date Created Range') }}</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" name="start_date" id="modal_start_date" value="{{ request('start_date') }}">
                                        <input type="date" class="form-control" name="end_date" id="modal_end_date" value="{{ request('end_date') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category: Classification -->
                    <div class="col-12">
                        <hr class="my-2 border-light">
                        <div class="d-flex align-items-center mb-3 mt-2">
                            <span class="badge bg-soft-success p-1 me-2"><i class="ti ti-tag text-success"></i></span>
                            <h6 class="text-muted text-uppercase fw-bold small m-0">{{ __('Classification & Duplicate Detection') }}</h6>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label fw-bold text-dark mb-1">{{ __('Source') }}</label>
                                    {!! Form::select('source_id[]', $sources, request('source_id'), ['class' => 'form-control choices-js-filter', 'multiple' => 'multiple', 'id' => 'modal_source_id']) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check custom-checkbox mt-4 border p-2 rounded bg-light-secondary" style="margin-left: 12px;">
                                    <input type="checkbox" class="form-check-input ms-0" name="duplicates" id="modal_duplicates" {{ request('duplicates') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark cursor-pointer ms-2" for="modal_duplicates">{{ __('Show Duplicate Leads') }}</label>
                                    <small class="text-muted d-block ms-4" style="font-size: 0.75rem;">{{ __('Matches on Name, Email, or Phone') }}</small>
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
                                        <input type="text" class="form-control" name="custom_fields[{{ $field->id }}]" id="cf_{{ $field->id }}" value="{{ request('custom_fields')[$field->id] ?? '' }}" placeholder="{{ __('Search') }} {{ $field->name }}...">
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
                    @forelse($saved_filters as $filter)
                        <div class="badge bg-light text-primary border p-2 d-flex align-items-center gap-2 filter-badge shadow-sm" style="cursor: pointer; transition: all 0.2s;" data-filters="{{ json_encode($filter->filters) }}">
                            <span>{{ $filter->name }}</span>
                            <i class="ti ti-x text-danger delete-saved-filter" data-id="{{ $filter->id }}" style="cursor: pointer;" title="{{ __('Delete') }}"></i>
                        </div>
                    @empty
                        <p class="text-muted small m-0">{{ __('No saved filters yet.') }}</p>
                    @endforelse
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-sm btn-secondary shadow-sm rounded-pill px-3" id="clearFiltersBtn">
                    <i class="ti ti-rotate-clockwise-2 me-1"></i> {{ __('Clear All') }}
                </button>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary shadow-sm rounded-pill px-3" id="saveFilterBtn">
                        <i class="ti ti-bookmark me-1"></i> {{ __('Save Preset') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-primary shadow-sm rounded-pill px-4" id="applyFiltersBtn">
                        <i class="ti ti-check me-1"></i> {{ __('Apply Filters') }}
                    </button>
                </div>
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
                    <input type="text" id="filterName" class="form-control text-center" placeholder="{{ __('e.g., Team Alpha') }}">
                </div>
                <div class="d-flex justify-content-center gap-2 mt-4">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-sm btn-primary" id="confirmSaveFilter">{{ __('Save Now') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const modalEl = document.getElementById('leadFilterModal');
        let choicesInstances = [];

        function initChoices() {
            choicesInstances.forEach(instance => instance.destroy());
            choicesInstances = [];

            const elements = modalEl.querySelectorAll('.choices-js-filter');
            elements.forEach(el => {
                const instance = new Choices(el, {
                    removeItemButton: true,
                    placeholder: true,
                    placeholderValue: @json(__("Please Select")),
                    searchEnabled: true,
                    shouldSort: false,
                    classNames: {
                        containerOuter: 'choices w-100',
                    }
                });
                choicesInstances.push(instance);
            });
        }

        modalEl.addEventListener('shown.bs.modal', function () {
            initChoices();
        });

        // Search on Enter
        const leadSearch = document.getElementById('lead_search');
        if(leadSearch) {
            leadSearch.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    applyFilters();
                }
            });
        }

        // Home Reset
        const clearHome = document.getElementById('clearAllFiltersHome');
        if(clearHome) {
            clearHome.addEventListener('click', function() {
                window.location.href = window.location.pathname;
            });
        }

        document.getElementById('applyFiltersBtn').addEventListener('click', applyFilters);
        
        document.getElementById('clearFiltersBtn').addEventListener('click', function() {
            window.location.href = window.location.pathname;
        });

        document.getElementById('saveFilterBtn').addEventListener('click', function() {
            const saveModal = new bootstrap.Modal(document.getElementById('saveFilterNameModal'));
            saveModal.show();
        });

        document.getElementById('confirmSaveFilter').addEventListener('click', function() {
            const name = document.getElementById('filterName').value;
            if(!name) {
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
                source_id: $('#modal_source_id').val(),
                start_date: $('#modal_start_date').val(),
                end_date: $('#modal_end_date').val(),
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
                success: function(response) {
                    if(response.success) {
                        toastrs('success', response.message, 'success');
                        location.reload();
                    } else {
                        toastrs('error', response.message, 'error');
                    }
                }
            });
        });

        $(document).on('click', '.filter-badge', function(e) {
            if ($(e.target).hasClass('delete-saved-filter')) return;
            
            const filters = $(this).data('filters');
            const url = new URL(window.location.href);
            
            // Clear existing params first to avoid mess
            // Actually, we should clear specifically known params, but this is safer to start clean? 
            // Only selective clear
            
            Object.keys(filters).forEach(key => {
                if(key === 'custom_fields') {
                    // Handle recursive object for CF
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

        $(document).on('click', '.delete-saved-filter', function() {
            const id = $(this).data('id');
            const badge = $(this).closest('.filter-badge');
            
            if(confirm(@json(__("Are you sure you want to delete this filter?")))) {
                $.ajax({
                    url: '{{ route("leads.filter.delete", "_ID_") }}'.replace('_ID_', id),
                    type: 'DELETE',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if(response.success) {
                            badge.remove();
                            toastrs('success', response.message, 'success');
                        }
                    }
                });
            }
        });

    }); 

    function applyFilters() {
        const url = new URL(window.location.href);
        const searchEl = document.getElementById('lead_search');
        if(searchEl && searchEl.value) url.searchParams.set('search', searchEl.value);
        else url.searchParams.delete('search');
        
        const params = {
            'responsible_person': $('#modal_responsible_person').val(),
            'stage_id': $('#modal_stage_id').val(),
            'source_id': $('#modal_source_id').val(),
            'created_by': $('#modal_created_by').val(),
            'modified_by': $('#modal_modified_by').val()
        };

        Object.keys(params).forEach(key => url.searchParams.delete(key + '[]'));

        Object.keys(params).forEach(key => {
            if(params[key] && params[key].length > 0) {
                if (Array.isArray(params[key])) {
                    params[key].forEach(val => url.searchParams.append(key + '[]', val));
                } else {
                    url.searchParams.set(key, params[key]);
                }
            }
        });

        const startEl = document.getElementById('modal_start_date');
        const endEl = document.getElementById('modal_end_date');
        
        if(startEl && startEl.value) url.searchParams.set('start_date', startEl.value); 
        else url.searchParams.delete('start_date');
        
        if(endEl && endEl.value) url.searchParams.set('end_date', endEl.value); 
        else url.searchParams.delete('end_date');

        if($('#modal_duplicates').is(':checked')) url.searchParams.set('duplicates', '1');
        else url.searchParams.delete('duplicates');

        // Custom Fields
        const cfInputs = document.querySelectorAll('input[name^="custom_fields["]');
        cfInputs.forEach(input => {
            const name = input.name; // custom_fields[15]
            if(input.value) {
                url.searchParams.set(name, input.value);
            } else {
                url.searchParams.delete(name);
            }
        });

        window.location.href = url.href;
    }
</script>
@endpush
