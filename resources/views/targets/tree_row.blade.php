<tr class="tree-row {{ $target->subTargets->count() > 0 ? 'has-sub-rows' : 'sub-row' }} level-{{ $level }}" 
    data-row-id="{{ $target->id }}" 
    data-parent-id="{{ $parentId }}">
    @php
        $canDeleteTarget = (Auth::user()->type == 'company' || Auth::user()->type == 'super admin' || $target->assigned_by == Auth::user()->id);
    @endphp
    <!-- Checkbox column -->
    <td style="width: 45px; padding-left: 24px; vertical-align: middle;">
        <div class="form-check m-0">
            <input type="checkbox" class="form-check-input target-checkbox" name="selected_targets[]" value="{{ $target->id }}" {{ !$canDeleteTarget ? 'disabled' : '' }}>
        </div>
    </td>

    <!-- Objective Name and Path -->
    <td style="padding-left: 12px; vertical-align: middle;">
        <div style="padding-left: {{ ($level - 1) * 24 }}px;" class="d-flex align-items-center">
            @if($target->subTargets->count() > 0)
                <button class="btn btn-sm btn-icon btn-link text-muted p-0 tree-toggle me-2" data-target-id="{{ $target->id }}">
                    <i class="ti ti-chevron-down fs-6 transition-transform"></i>
                </button>
            @else
                @if($level > 1)
                    <span class="nesting-arrow"><i class="ti ti-corner-down-right" style="font-size: 14px;"></i></span>
                @else
                    <span class="me-1" style="width: 24px; display: inline-block;"></span>
                @endif
            @endif
            
            <div class="d-flex flex-column">
                @if($level == 1)
                    <span class="text-dark fw-bold" style="font-size: 0.95rem; color: #0f172a !important; font-weight: 700; line-height: 1.2;">{{ $target->target_name }}</span>
                @else
                    <span class="text-muted fw-semibold" style="font-size: 0.875rem; color: #475569 !important; font-weight: 600; line-height: 1.2;">{{ $target->target_name }}</span>
                @endif
                
                @if($target->target_type == 'lead_stage')
                    <span class="mt-1 d-flex align-items-center flex-wrap gap-1" style="font-size: 11px; color: #475569; font-weight: 500;">
                        <i class="ti ti-git-branch text-primary" style="font-size: 12px;"></i>
                        <span>{{ $target->pipeline ? $target->pipeline->name : __('Unknown Pipeline') }}</span>
                        <i class="ti ti-arrow-right text-muted mx-0.5" style="font-size: 9px;"></i>
                        <span class="badge rounded-pill py-0.5 px-2" style="font-size: 10px; font-weight: 600; background-color: #e0f2fe !important; color: #0369a1 !important;">{{ $target->stage ? $target->stage->name : __('Unknown Stage') }}</span>
                        @if($target->custom_date_field && $target->custom_date_field !== 'created_at')
                            @php
                                $dateField = \DB::table('lead_custom_fields')->where('workspace_id', getActiveWorkSpace())->where('id', $target->custom_date_field)->first();
                            @endphp
                            <span class="ms-1 fw-bold text-dark" style="font-size: 10px; color: #1e293b !important;">({{ $dateField ? $dateField->name : __('Custom Date') }})</span>
                        @endif
                    </span>
                @endif
            </div>
        </div>
    </td>

    <!-- Target Type Badge -->
    <td style="vertical-align: middle;">
        <span class="premium-badge {{ $target->target_type == 'lead_stage' ? 'premium-badge-automated' : 'premium-badge-manual' }}">
            <i class="{{ $target->target_type == 'lead_stage' ? 'ti ti-cpu' : 'ti ti-user-edit' }} me-0.5" style="font-size: 12px;"></i>
            {{ $target->target_type == 'lead_stage' ? __('Automated') : __('Manual') }}
        </span>
    </td>

    <!-- Assigned To badge -->
    <td style="vertical-align: middle;">
        @if($target->assignedToUser)
            <span class="premium-badge premium-badge-member"><i class="ti ti-user me-1" style="font-size: 12px;"></i>{{ $target->assignedToUser->name }}</span>
        @elseif($target->department)
            <span class="premium-badge premium-badge-dept"><i class="ti ti-building me-1" style="font-size: 12px;"></i>{{ $target->department->name }}</span>
        @elseif($target->team)
            <span class="premium-badge premium-badge-team"><i class="ti ti-users me-1" style="font-size: 12px;"></i>{{ $target->team->name }}</span>
        @else
            <span class="text-muted fw-semibold" style="font-size: 11px;">-</span>
        @endif
    </td>

    <!-- Responsible manager -->
    <td style="vertical-align: middle;">
        <span class="text-dark fw-bold" style="font-size: 13px; color: #334155 !important;">
            <i class="ti ti-shield text-primary me-1" style="font-size: 15px;"></i>
            {{ !empty($target->responsibleUser) ? $target->responsibleUser->name : __('No Responsible Person') }}
        </span>
        @if($target->can_edit)
            <i class="ti ti-lock-open text-success ms-1" data-bs-toggle="tooltip" title="{{ __('Has Edit Rights') }}"></i>
        @endif
    </td>

    <!-- Target Quota -->
    <td class="text-center" style="vertical-align: middle;">
        <span class="quota-val-main">{{ $target->target_value }}</span>
    </td>

    <!-- Achieved value -->
    <td class="text-center" style="vertical-align: middle;">
        <span class="quota-val-achieved text-success">{{ $target->achieved_value }}</span>
    </td>

    <!-- Remaining -->
    <td class="text-center" style="vertical-align: middle;">
        @php
            $remaining = max(0, $target->target_value - $target->achieved_value);
        @endphp
        @if($remaining > 0)
            <span class="quota-val-remaining" style="background-color: #ffedd5 !important; color: #c2410c !important;">{{ $remaining }}</span>
        @else
            <span class="quota-val-remaining" style="background-color: #d1fae5 !important; color: #065f46 !important;">0</span>
        @endif
    </td>

    <!-- Incentive -->
    <td class="text-center" style="vertical-align: middle;">
        <span class="quota-val-main" style="color: #6366f1 !important; font-weight: 700;">
            {{ !empty($target->incentive) && $target->incentive > 0 ? currency_format_with_sym($target->incentive) : '-' }}
        </span>
    </td>

    <!-- Progress Column -->
    <td style="vertical-align: middle;">
        @php
            $progps = round($target->aggregateProgress, 1);
            $progps = $progps > 100 ? 100 : $progps;
        @endphp
        <div class="d-flex flex-column justify-content-center" style="width: 100%; min-width: 150px;">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted fw-semibold" style="font-size: 11px; color: #64748b !important;">{{ __('Progress') }}</span>
                <span class="fw-bold text-primary" style="font-size: 12px; color: var(--primary-theme-color) !important; font-weight: 700;">{{ $progps }}%</span>
            </div>
            <div class="progress-track-premium">
                <div class="progress-bar-premium" style="width: {{ $progps }}%; background: {{ $progps >= 100 ? 'linear-gradient(90deg, #16a34a 0%, #2dcce8 100%) !important' : 'linear-gradient(90deg, var(--primary-theme-color) 0%, #7386f7 100%) !important' }};"></div>
            </div>
            @if($target->subTargets->count() > 0)
                <small class="text-muted fw-semibold mt-1" style="font-size: 10px;"><i class="ti ti-users text-primary me-1"></i>{{ __('Aggregate') }}</small>
            @endif
        </div>
    </td>

    <!-- Status badge -->
    <td style="vertical-align: middle;">
        @if($target->status == 'Completed')
            <span class="status-pill status-pill-completed"><i class="ti ti-circle-check fs-6"></i>{{ __($target->status) }}</span>
        @elseif($target->status == 'Pending')
            <span class="status-pill status-pill-pending"><i class="ti ti-clock fs-6"></i>{{ __($target->status) }}</span>
        @else
            <span class="status-pill status-pill-missed"><i class="ti ti-circle-x fs-6"></i>{{ __($target->status) }}</span>
        @endif
    </td>

    <!-- Action Buttons -->
    <td class="text-end" style="padding-right: 24px; vertical-align: middle;">
        <div class="d-flex gap-1 justify-content-end align-items-center">
            @if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin' || $target->assigned_by == Auth::user()->id)
                <a href="#" class="btn btn-sm btn-light-primary btn-icon" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; padding: 0;" data-url="{{ route('targets.edit', $target->id) }}" data-ajax-popup="true" data-size="md" data-title="{{ __('Edit Target') }}" data-bs-toggle="tooltip" title="{{__('Edit')}}">
                    <i class="ti ti-pencil fs-6"></i>
                </a>
            @endif

            {{-- Hierarchy divide button — prominent, labeled --}}
            @php
                $canDivide   = false;
                $divideTitle = '';
                $divideCls   = '';
                
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
                        $canDivide   = true;
                        $divideTitle = __('Assign to Members');
                        $divideCls   = 'btn-warning';
                    }
                } elseif ($target->department_id > 0) {
                    if (
                        Auth::user()->type == 'company' || Auth::user()->type == 'super admin' ||
                        $target->responsible_user_id == Auth::user()->id ||
                        $target->assigned_by == Auth::user()->id ||
                        $isTargetDeptManager
                    ) {
                        $canDivide   = true;
                        $divideTitle = __('Divide into Teams');
                        $divideCls   = 'btn-info';
                    }
                }
            @endphp
            @if($canDivide)
                @php
                    $divideBg = $target->team_id > 0 ? 'rgba(255, 159, 67, 0.08)' : 'rgba(13, 202, 240, 0.08)';
                    $divideBorder = $target->team_id > 0 ? 'rgba(255, 159, 67, 0.2)' : 'rgba(13, 202, 240, 0.2)';
                    $divideColor = $target->team_id > 0 ? '#ff9f43' : '#0dcaf0';
                @endphp
                <a href="#"
                   class="btn btn-sm fw-bold px-3"
                   style="border-radius: 8px; font-size: 0.76rem; white-space: nowrap; height: 32px; display: inline-flex; align-items: center; justify-content: center; background-color: {{ $divideBg }} !important; border: 1px solid {{ $divideBorder }} !important; color: {{ $divideColor }} !important;"
                   data-url="{{ route('targets.create', ['parent_id' => $target->id]) }}"
                   data-ajax-popup="true" data-size="md"
                   data-title="{{ $divideTitle }}"
                   data-bs-toggle="tooltip" title="{{ $divideTitle }}">
                    <i class="ti ti-git-fork me-1"></i> {{ $divideTitle }}
                </a>
            @endif

            @if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin' || $target->assigned_by == Auth::user()->id)
                {!! Form::open(['method' => 'DELETE', 'route' => ['targets.destroy', $target->id], 'id' => 'delete-form-'.$target->id, 'class' => 'd-inline']) !!}
                    <a href="#" class="btn btn-sm btn-light-danger btn-icon bs-pass-para show_confirm" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; padding: 0;" data-confirm="{{__('Are You Sure?')}}" data-text="{{__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="delete-form-{{$target->id}}" data-bs-toggle="tooltip" title="{{__('Delete')}}">
                        <i class="ti ti-trash fs-6"></i>
                    </a>
                {!! Form::close() !!}
            @endif
        </div>
    </td>
</tr>

{{-- Render Sub-targets recursively --}}
@if($target->subTargets->count() > 0)
    @foreach($target->subTargets as $sub)
        @include('targets.tree_row', ['target' => $sub, 'level' => $level + 1, 'parentId' => $target->id])
    @endforeach
@endif
