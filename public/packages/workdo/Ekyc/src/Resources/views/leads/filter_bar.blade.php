@push('css')
<style>
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
</style>
@endpush

<div class="row align-items-center mb-3 g-2">
    <div class="col-xl-4 col-lg-5 col-md-7 col-sm-12">
        <div class="input-group input-group shadow-sm rounded">
            <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
            <input type="text" id="lead_search" class="form-control border-start-0 ps-0" placeholder="{{ __('Quick search by name, email or phone...') }}" value="{{ request('search') }}">
        </div>
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-primary btn-icon shadow-sm position-relative" data-bs-toggle="modal" data-bs-target="#leadFilterModal" id="advancedFilterBtn">
            <span class="btn-inner--icon"><i class="ti ti-adjustments-horizontal me-1"></i></span>
            <span class="btn-inner--text">{{ __('Advanced Filter') }}</span>
            @php
                $activeFilterCount = 0;
                $filterKeys = ['status', 'assigned_user', 'start_date', 'end_date'];
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
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header py-3">
                <h5 class="modal-title fw-bold" id="leadFilterModalLabel">{{ __('Filter eKYC Leads') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold">{{ __('Status') }}</label>
                            <select name="status" id="modal_status" class="form-control">
                                <option value="">{{ __('Select Status') }}</option>
                                <option value="fresh" {{ request('status') == 'fresh' ? 'selected' : '' }}>{{ __('Fresh') }}</option>
                                <option value="in-progress" {{ request('status') == 'in-progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>{{ __('Verified') }}</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold">{{ __('Assigned User') }}</label>
                            @php
                                $users = \App\Models\User::where('workspace_id', getActiveWorkSpace())->get()->pluck('name', 'id');
                            @endphp
                            {{ Form::select('assigned_user', ['' => __('Select User')] + $users->toArray(), request('assigned_user'), ['class' => 'form-control', 'id' => 'modal_assigned_user']) }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold">{{ __('Start Date') }}</label>
                            <input type="date" class="form-control" name="start_date" id="modal_start_date" value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label fw-bold">{{ __('End Date') }}</label>
                            <input type="date" class="form-control" name="end_date" id="modal_end_date" value="{{ request('end_date') }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-sm btn-secondary shadow-sm rounded-pill px-3" id="clearFiltersBtn">
                    <i class="ti ti-rotate-clockwise-2 me-1"></i> {{ __('Clear All') }}
                </button>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-primary shadow-sm rounded-pill px-4" id="applyFiltersBtn">
                        <i class="ti ti-check me-1"></i> {{ __('Apply Filters') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // Search on Enter
        const leadSearch = document.getElementById('lead_search');
        if(leadSearch) {
            leadSearch.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    applyFilters();
                }
            });
        }

        // Clear buttons
        const clearHome = document.getElementById('clearAllFiltersHome');
        if(clearHome) {
            clearHome.addEventListener('click', function() {
                window.location.href = window.location.pathname;
            });
        }
        document.getElementById('clearFiltersBtn').addEventListener('click', function() {
            window.location.href = window.location.pathname;
        });

        // Apply
        document.getElementById('applyFiltersBtn').addEventListener('click', applyFilters);
    });

    function applyFilters() {
        const url = new URL(window.location.href);
        const searchEl = document.getElementById('lead_search');
        
        if(searchEl && searchEl.value) url.searchParams.set('search', searchEl.value);
        else url.searchParams.delete('search');
        
        const params = {
            'status': document.getElementById('modal_status').value,
            'assigned_user': document.getElementById('modal_assigned_user').value,
            'start_date': document.getElementById('modal_start_date').value,
            'end_date': document.getElementById('modal_end_date').value
        };

        Object.keys(params).forEach(key => {
            if(params[key]) {
                 url.searchParams.set(key, params[key]);
            } else {
                 url.searchParams.delete(key);
            }
        });

        window.location.href = url.href;
    }
</script>
@endpush
