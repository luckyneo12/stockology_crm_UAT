@php
    $remaining = max(0, $target->target_value - $target->achieved_value);
    $progps = round($target->aggregateProgress, 1);
    $progps = $progps > 100 ? 100 : $progps;
    $hasSubs = $target->subTargets->count() > 0;
@endphp

<div class="list-group-item py-3 px-4 target-item-row level-{{ $level }} status-{{ $target->status }} {{ $hasSubs ? 'has-sub-rows' : '' }}" 
     data-row-id="{{ $target->id }}"
     @if($level > 1) data-parent-id="{{ $parentId }}" style="display: none;" @endif>
     
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <!-- Left Section: Toggle, Title, Type, and Dates -->
        <div class="d-flex align-items-start gap-2 flex-grow-1" style="min-width: 250px;">
            @if($hasSubs)
                <button class="btn btn-sm btn-icon btn-link text-muted p-0 tree-toggle me-1 collapsed" data-target-id="{{ $target->id }}">
                    <i class="ti ti-chevron-down fs-6 transition-transform"></i>
                </button>
            @else
                <span class="me-1" style="width: 24px; display: inline-block;"></span>
            @endif

            <div class="d-flex flex-column">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h6 class="mb-0 text-dark font-weight-bold" style="font-size: 0.95rem;">{{ $target->target_name }}</h6>
                    
                    <span class="badge {{ $target->target_type == 'lead_stage' ? 'badge-type-automated' : 'badge-type-manual' }} text-xxs px-2 py-0.5 rounded-pill">
                        {{ $target->target_type == 'lead_stage' ? __('Automated') : __('Manual') }}
                    </span>
                    
                    @if($target->status == 'Completed')
                        <span class="badge badge-status-completed text-xxs px-2 py-0.5 rounded-pill">{{ __($target->status) }}</span>
                    @elseif($target->status == 'Pending')
                        <span class="badge badge-status-pending text-xxs px-2 py-0.5 rounded-pill">{{ __($target->status) }}</span>
                    @else
                        <span class="badge badge-status-missed text-xxs px-2 py-0.5 rounded-pill">{{ __($target->status) }}</span>
                    @endif
                </div>

                <!-- Timeline / Dates -->
                <small class="text-muted text-xxs mt-1">
                    <i class="ti ti-calendar me-1"></i>
                    {{ $target->start_date ? \Carbon\Carbon::parse($target->start_date)->format('d M') : '-' }} - {{ $target->end_date ? \Carbon\Carbon::parse($target->end_date)->format('d M Y') : '-' }}
                </small>

                <!-- Automated rules details -->
                @if($target->target_type == 'lead_stage')
                    <small class="text-xxs text-muted mt-1 d-flex align-items-center gap-1">
                        <i class="ti ti-git-branch text-primary text-xxs"></i>
                        {{ $target->pipeline ? $target->pipeline->name : __('Unknown Pipeline') }} &rarr; 
                        <span class="badge bg-light-info text-info py-0 px-1" style="font-size: 9px;">{{ $target->stage ? $target->stage->name : __('Unknown Stage') }}</span>
                    </small>
                @endif
            </div>
        </div>

        <!-- Middle Section: Quota Breakdown & Progress Bar -->
        <div class="d-flex align-items-center gap-4 flex-grow-1" style="max-width: 450px; min-width: 200px;">
            <!-- Quota numbers -->
            <div class="d-flex align-items-center gap-3 text-xxs text-muted" style="min-width: 130px;">
                <div>
                    <span>{{ __('Quota') }}:</span> <strong class="text-dark">{{ $target->target_value }}</strong>
                </div>
                <div class="border-end" style="height: 12px;"></div>
                <div>
                    <span>{{ __('Done') }}:</span> <strong class="text-success">{{ $target->achieved_value }}</strong>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="d-flex flex-column flex-grow-1">
                <div class="d-flex justify-content-between align-items-center text-xxs mb-1">
                    <span class="text-muted">{{ __('Progress') }}</span>
                    <span class="font-weight-bold text-primary">{{ $progps }}%</span>
                </div>
                <div class="target-progress-track" style="height: 6px !important;">
                    <div class="target-progress-bar" style="width: {{ $progps }}%; background: {{ $progps >= 100 ? 'linear-gradient(90deg, #2dce89 0%, #2dcecc 100%) !important' : 'var(--primary-theme-gradient) !important' }};"></div>
                </div>
                @if($hasSubs)
                    <small class="text-muted" style="font-size: 9px; margin-top: 2px;"><i class="ti ti-users text-primary"></i> {{ __('Aggregate') }}</small>
                @endif
            </div>
        </div>

        <!-- Right Section: Assigned To & Actions -->
        <div class="d-flex align-items-center justify-content-between justify-content-md-end gap-3" style="min-width: 180px;">
            <!-- Assigned To Pill -->
            <div class="text-start text-md-end">
                @if($level > 1)
                    @if($target->assignedToUser)
                        <span class="badge bg-light-primary text-primary py-1 px-2 rounded-pill text-xxs"><i class="ti ti-user me-1"></i>{{ $target->assignedToUser->name }}</span>
                    @elseif($target->department)
                        <span class="badge bg-light-info text-info py-1 px-2 rounded-pill text-xxs"><i class="ti ti-building me-1"></i>{{ $target->department->name }} ({{ __('Dept') }})</span>
                    @elseif($target->team)
                        <span class="badge bg-light-warning text-warning py-1 px-2 rounded-pill text-xxs"><i class="ti ti-users me-1"></i>{{ $target->team->name }} ({{ __('Team') }})</span>
                    @endif
                @else
                    <span class="badge bg-light-secondary text-dark py-1 px-2 rounded-pill text-xxs"><i class="ti ti-target me-1"></i>{{ __('Main Objective') }}</span>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-1">
                @if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin' || $target->assigned_by == Auth::user()->id)
                    <a href="#" class="btn btn-sm btn-light-primary btn-icon" style="padding: 4px 8px;" data-url="{{ route('targets.edit', $target->id) }}" data-ajax-popup="true" data-size="md" data-title="{{ __('Edit Target') }}" data-bs-toggle="tooltip" title="{{__('Edit')}}">
                        <i class="ti ti-pencil fs-6"></i>
                    </a>
                @endif

                {{-- Prominent labeled divide button --}}
                @php
                    $canDivideList = false;
                    $divideTitleList = '';
                    $divideClsList = '';
                    
                    $isTargetTeamManager = false;
                    if ($target->team_id > 0 && module_is_active('Hrm')) {
                        $teamObj = \Workdo\Hrm\Entities\Department::find($target->team_id);
                        if ($teamObj && $teamObj->manager_id) {
                            $mgrEmp = \Workdo\Hrm\Entities\Employee::find($teamObj->manager_id);
                            if ($mgrEmp && $mgrEmp->user_id == Auth::user()->id) {
                                $isTargetTeamManager = true;
                            }
                        }
                    }

                    $isTargetDeptManager = false;
                    if ($target->department_id > 0 && module_is_active('Hrm')) {
                        $deptObj = \Workdo\Hrm\Entities\Department::find($target->department_id);
                        if ($deptObj && $deptObj->manager_id) {
                            $mgrEmp = \Workdo\Hrm\Entities\Employee::find($deptObj->manager_id);
                            if ($mgrEmp && $mgrEmp->user_id == Auth::user()->id) {
                                $isTargetDeptManager = true;
                            }
                        }
                    }

                    if ($target->team_id > 0) {
                        if (
                            Auth::user()->type == 'company' || Auth::user()->type == 'super admin' ||
                            $target->responsible_user_id == Auth::user()->id ||
                            $target->assigned_by == Auth::user()->id ||
                            $isTargetTeamManager
                        ) {
                            $canDivideList   = true;
                            $divideTitleList = __('Assign to Members');
                            $divideClsList   = 'btn-warning';
                        }
                    } elseif ($target->department_id > 0) {
                        if (
                            Auth::user()->type == 'company' || Auth::user()->type == 'super admin' ||
                            $target->responsible_user_id == Auth::user()->id ||
                            $target->assigned_by == Auth::user()->id ||
                            $isTargetDeptManager
                        ) {
                            $canDivideList   = true;
                            $divideTitleList = __('Divide into Teams');
                            $divideClsList   = 'btn-info';
                        }
                    }
                @endphp
                @if($canDivideList)
                    @php
                        $divideBgList = $target->team_id > 0 ? 'rgba(255, 159, 67, 0.08)' : 'rgba(13, 202, 240, 0.08)';
                        $divideBorderList = $target->team_id > 0 ? 'rgba(255, 159, 67, 0.2)' : 'rgba(13, 202, 240, 0.2)';
                        $divideColorList = $target->team_id > 0 ? '#ff9f43' : '#0dcaf0';
                    @endphp
                    <a href="#"
                       class="btn btn-sm fw-bold px-3"
                       style="border-radius: 8px; font-size: 0.76rem; white-space: nowrap; height: 32px; display: inline-flex; align-items: center; justify-content: center; background-color: {{ $divideBgList }} !important; border: 1px solid {{ $divideBorderList }} !important; color: {{ $divideColorList }} !important;"
                       data-url="{{ route('targets.create', ['parent_id' => $target->id]) }}"
                       data-ajax-popup="true" data-size="md"
                       data-title="{{ $divideTitleList }}"
                       data-bs-toggle="tooltip" title="{{ $divideTitleList }}">
                        <i class="ti ti-git-fork me-1"></i> {{ $divideTitleList }}
                    </a>
                @endif

                @if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin' || $target->assigned_by == Auth::user()->id)
                    {!! Form::open(['method' => 'DELETE', 'route' => ['targets.destroy', $target->id], 'id' => 'delete-form-list-'.$target->id, 'class' => 'd-inline']) !!}
                        <a href="#" class="btn btn-sm btn-light-danger btn-icon bs-pass-para show_confirm" style="padding: 4px 8px;" data-confirm="{{__('Are You Sure?')}}" data-text="{{__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="delete-form-list-{{$target->id}}" data-bs-toggle="tooltip" title="{{__('Delete')}}">
                            <i class="ti ti-trash fs-6"></i>
                        </a>
                    {!! Form::close() !!}
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Render Sub-targets recursively --}}
@if($hasSubs)
    @foreach($target->subTargets as $sub)
        @include('targets.card_list_row', ['target' => $sub, 'level' => $level + 1, 'parentId' => $target->id])
    @endforeach
@endif
