<div class="card border-0 shadow-none mb-0" style="background-color: transparent;">
    <div class="card-header border-0 bg-transparent px-0 py-2 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 font-weight-bold text-dark"><i class="ti ti-users me-1 text-primary"></i>{{ $team->name }} — {{ __('Members Performance') }}</h6>
        <span class="badge bg-light-primary text-primary">{{ count($memberData) }} {{ __('Employees') }}</span>
    </div>
    
    <div class="row g-3">
        @forelse($memberData as $member)
            <div class="col-md-6 col-sm-12">
                <div class="card border shadow-sm rounded-4 mb-0" style="background-color: #ffffff; transition: all 0.2s ease;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ $member['avatar'] }}" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($member['name']) }}&background=5e72e4&color=fff';" class="rounded-circle me-3" style="width: 44px; height: 44px; object-fit: cover; border: 2px solid #5e72e4;">
                            <div class="overflow-hidden">
                                <h6 class="mb-0 text-dark font-weight-bold text-truncate">{{ $member['name'] }}</h6>
                                <small class="text-muted text-truncate d-block">{{ $member['email'] }}</small>
                            </div>
                            <div class="ms-auto text-end">
                                <span class="badge bg-light-secondary text-dark text-xxs mb-1 d-block">{{ $member['targets_count'] }} {{ __('Active Targets') }}</span>
                                <span class="badge bg-light-success text-success text-xxs d-block">{{ $member['completed'] }} {{ __('Completed') }}</span>
                            </div>
                        </div>

                        <!-- Progress Section -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center text-xs mb-1">
                                <span class="text-muted">{{ __('Aggregate Progress') }}</span>
                                <span class="font-weight-bold text-primary">{{ $member['progress'] }}%</span>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 4px;">
                                <div class="progress-bar {{ $member['progress'] >= 80 ? 'bg-success' : ($member['progress'] >= 45 ? 'bg-primary' : 'bg-danger') }}" role="progressbar" style="width: {{ $member['progress'] }}%;"></div>
                            </div>
                            <div class="d-flex justify-content-between text-xs text-muted mt-1">
                                <span>{{ __('Total Quota') }}: <strong>{{ $member['total_target'] }}</strong></span>
                                <span>{{ __('Total Done') }}: <strong>{{ $member['total_achieved'] }}</strong></span>
                            </div>
                        </div>

                        <!-- Targets Drilldown List -->
                        @if(count($member['targets_list']) > 0)
                            <div class="border-top pt-2">
                                <small class="text-muted fw-bold d-block mb-2">{{ __('Targets Breakdown') }}:</small>
                                <div class="d-flex flex-column gap-2" style="max-height: 120px; overflow-y: auto;">
                                    @foreach($member['targets_list'] as $t)
                                        <div class="bg-light p-2 rounded-3 d-flex flex-column gap-1 text-xs">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="text-truncate me-2" style="max-width: 180px;">
                                                    <span class="fw-bold text-dark">{{ $t['name'] }}</span>
                                                    <span class="d-block text-xxs text-muted">{{ $t['type'] }}</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2 ms-auto">
                                                    <span class="badge {{ $t['status'] == 'Completed' ? 'bg-light-success text-success' : ($t['status'] == 'Missed' ? 'bg-light-danger text-danger' : 'bg-light-warning text-warning') }} text-xxs">{{ $t['status'] }}</span>
                                                    <span class="text-dark font-weight-bold">{{ $t['achieved'] }} / {{ $t['target'] }}</span>
                                                </div>
                                            </div>
                                            @if(!empty($t['pipeline_name']) && !empty($t['stage_name']))
                                                <div class="text-xxs text-muted d-flex align-items-center flex-wrap gap-1">
                                                    <i class="ti ti-git-branch text-primary"></i>
                                                    {{ $t['pipeline_name'] }} &rarr; 
                                                    <span class="badge bg-light-info text-info py-0 px-1">{{ $t['stage_name'] }}</span>
                                                    @if(!empty($t['custom_date_field_name']))
                                                        <span class="text-xxs text-dark font-weight-bold">({{ $t['custom_date_field_name'] }})</span>
                                                    @endif
                                                </div>
                                            @endif
                                            @php
                                                $remVal = max(0, $t['target'] - $t['achieved']);
                                            @endphp
                                            <div class="d-flex justify-content-between text-xxs border-top pt-1 mt-1 text-muted">
                                                <span>{{ __('Remaining') }}: <strong class="{{ $remVal > 0 ? 'text-warning' : 'text-success' }}">{{ $remVal }}</strong></span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="border-top pt-2 text-center py-2 text-muted text-xs">
                                <i class="ti ti-target me-1"></i>{{ __('No individual targets assigned.') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-4 text-muted">
                <i class="ti ti-users fs-2"></i>
                <p class="mt-2 text-sm">{{ __('No members found in this team with active targets.') }}</p>
            </div>
        @endforelse
    </div>
</div>
