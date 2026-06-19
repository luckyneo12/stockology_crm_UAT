@php($labels = $lead->labels())
@php($isLocked = !$lead->stagePermissions()->can_edit)
<div class="card image-matched-card shadow-sm mb-2 {{ $isLocked ? 'locked-lead' : '' }}" data-id="{{ $lead->id }}" data-locked="{{ $isLocked ? '1' : '0' }}">
    <div class="card-header border-0 pb-0 d-flex flex-column pt-2 px-2.5 background-transparent">
        <div class="d-flex align-items-center justify-content-between mb-1">
            <h6 class="mb-0 flex-grow-1" style="min-width: 0; display: flex; align-items: center;">
                <a href="@permission('lead show')@if ($lead->is_active){{ route('leads.show', $lead->id) }}@else#@endif @else#@endpermission" 
                   class="text-dark-grey fw-bold text-decoration-none text-truncate" 
                   style="font-size: 0.85rem; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: 1.25; max-width: calc(100% - 20px);">
                    {{ $lead->name }}
                </a>
                @if($isLocked)
                    <i class="ti ti-lock text-danger ms-1" style="font-size: 0.9rem;" data-bs-toggle="tooltip" title="{{ __('Stage Locked: You do not have permission to edit leads in this stage.') }}"></i>
                @endif
            </h6>
            <div class="d-flex align-items-center gap-1.5 flex-shrink-0 ms-2">
                @if($lead->phone)
                    <a href="javascript:void(0)" 
                       class="call-btn-enhanced click-to-call" 
                       data-phone="{{ $lead->phone }}"
                       data-bs-toggle="tooltip" 
                       title="{{__('Click to Call')}}">
                        <i class="ti ti-phone-call f-11"></i>
                    </a>
                @endif
                <div class="dropdown">
                    @if (!$lead->is_active)
                        <i class="fas fa-lock text-muted f-12"></i>
                    @else
                        <button type="button" class="btn btn-sm dropdown-toggle p-0 shadow-none border-0 d-flex align-items-center no-caret" 
                                data-bs-toggle="dropdown" data-bs-offset="0,6" aria-haspopup="true" aria-expanded="false">
                            <i class="ti ti-dots-vertical f-15 text-muted"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-lg border-1 border-light py-1.5 rounded-3" 
                             style="z-index: 3500; min-width: 140px; position: absolute;">
                            @permission('lead edit')
                                @if($lead->stagePermissions()->can_edit)
                                    <a data-url="{{ URL::to('leads/' . $lead->id . '/labels') }}" data-ajax-popup="true" data-title="{{ __('Labels') }}" class="dropdown-item py-1.5 px-3">
                                        <i class="ti ti-bookmark text-success"></i>{{ __('Labels') }}
                                    </a>
                                @endif
                            @endpermission
                            @permission('lead show')
                                @if($lead->is_active)
                                    <a href="{{route('leads.show',$lead->id)}}" class="dropdown-item py-1.5 px-3">
                                        <i class="ti ti-eye text-primary"></i>{{ __('View') }}
                                    </a>
                                @endif
                            @endpermission
                            @permission('lead edit')
                                @if($lead->stagePermissions()->can_edit)
                                    <a data-url="{{ URL::to('leads/' . $lead->id . '/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{ __('Edit') }}" class="dropdown-item py-1.5 px-3">
                                        <i class="ti ti-pencil text-warning"></i>{{ __('Edit') }}
                                    </a>
                                @endif
                            @endpermission
                            @permission('lead delete')
                                {!! Form::open(['method' => 'DELETE', 'route' => ['leads.destroy', $lead->id], 'id' => 'delete-form-' . $lead->id]) !!}
                                <a class="dropdown-item show_confirm text-danger py-1.5 px-3" data-confirm="{{ __('Are You Sure?') }}" data-text="{{ __('This action can not be undone.') }}">
                                    <i class="ti ti-trash"></i>{{ __('Delete') }}
                                </a>
                                {!! Form::close() !!}
                            @endpermission
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-1 mb-1">
            @if ($labels)
                @foreach ($labels as $label)
                    <span class="badge badge-pill bg-{{ $label->color }} p-0.5 px-1.5" style="font-size: 7.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; border-radius: 3px; opacity: 0.9;">
                        {{ $label->name }}
                    </span>
                @endforeach
            @endif
        </div>
    </div>
    
    <div class="card-body p-2 pt-1 pb-1.5">
        <div class="d-flex align-items-center justify-content-between mb-1.5 image-matched-brand-color">
            <div class="d-flex align-items-center py-0.5 px-1.5 rounded bg-light-success-soft" data-bs-toggle="tooltip" title="{{__('Tasks')}}">
                <i class="ti ti-list me-1 f-12"></i>
                <span class="fw-bold" style="font-size: 10px;">{{ count($lead->complete_tasks) }}/{{ count($lead->tasks) }}</span>
            </div>

            <div class="d-flex align-items-center py-0.5 px-1.5 rounded bg-light-danger-soft" data-bs-toggle="tooltip" title="{{__('Reminders (Today/Total)')}}">
                <i class="ti ti-bell me-1 f-12 {{ $lead->getTodayRemindersCount() > 0 ? 'text-danger' : '' }}"></i>
                <span class="fw-bold" style="font-size: 10px;">{{ $lead->getTodayRemindersCount() }}/{{ $lead->getFilteredReminders()->count() }}</span>
            </div>
            
            @if (isset($lead->date) && !empty($lead->date))
                <div class="d-flex align-items-center py-0.5 px-1.5 rounded bg-light-primary-soft" data-bs-toggle="tooltip" title="{{__('Created At')}}">
                    <i class="ti ti-calendar-event me-1 f-12"></i>
                    <span class="fw-bold" style="font-size: 10px;">{{ company_date_formate($lead->date) }}</span>
                </div>
            @endif
        </div>

        <hr class="my-1.5" style="opacity: 0.05;">

        <div class="d-flex align-items-center justify-content-between pt-0.5">
            <div class="d-flex gap-2.5 image-matched-brand-color">
                @if($lead->isResponsible())
                    <div class="d-flex align-items-center" data-bs-toggle="tooltip" title="{{__('KYC Comments')}}">
                        @if(\Auth::user()->isAbleTo('lead kyc comment'))
                            <a href="#!" data-url="{{ route('leads.discussions.create', $lead->id) }}?is_kyc=1" data-ajax-popup="true" data-title="{{__('Add KYC Comment')}}" data-size="md" class="text-inherit action-icon-hover">
                                <i class="ti ti-shield-check me-1 f-14"></i>
                            </a>
                        @else
                            <i class="ti ti-shield-check me-1 f-14"></i>
                        @endif
                        <span class="fw-bold" style="font-size: 11px;">{{ $lead->discussions->where('is_kyc', 1)->count() }}</span>
                    </div>
                @endif
                <div class="d-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Sources')}}">
                    <i class="ti ti-circles me-1 f-14"></i>
                    <span class="fw-bold" style="font-size: 11px;">{{ count($lead->sources()) }}</span>
                </div>
            </div>
            
            <div class="user-group d-flex flex-column align-items-end">
                <?php
                    $user = $lead->owner ?? $lead->users->first();
                ?>
                @if($user)
                    <?php
                        static $employeeDeptCache = [];
                        if (isset($employeeDeptCache[$user->id])) {
                            $tName = $employeeDeptCache[$user->id];
                        } else {
                            $tName = '';
                            if (function_exists('module_is_active') && module_is_active('Hrm')) {
                                if ($user->relationLoaded('employee')) {
                                    $tName = ($user->employee && $user->employee->department) ? $user->employee->department->name : '';
                                } else if (class_exists('\Workdo\Hrm\Entities\Employee')) {
                                    $emp = \Workdo\Hrm\Entities\Employee::where('user_id', $user->id)->first();
                                    $tName = $emp && $emp->department ? $emp->department->name : '';
                                }
                            }
                            $employeeDeptCache[$user->id] = $tName;
                        }
                    ?>
                    <div class="d-flex flex-wrap align-items-center justify-content-end gap-1" style="max-width: 100%;">
                        <span class="badge rounded-pill bg-white border d-flex align-items-center shadow-sm px-1.5" 
                              style="border-color: #e2e8f0; height: 20px; max-width: 110px;"
                              data-bs-toggle="tooltip" title="{{ __('Responsible: ') . $user->name }}">
                            <i class="ti ti-user text-muted me-1" style="font-size: 9px;"></i>
                            <span class="text-truncate" style="color: #475569; font-weight: 600; font-size: 10px;">{{ $user->name }}</span>
                        </span>
                        @if(!empty($tName))
                            <span class="badge rounded-pill d-flex align-items-center shadow-sm px-1.5"
                                  style="background: #f8fafc; border: 1px solid #e2e8f0; height: 20px; max-width: 90px;"
                                  data-bs-toggle="tooltip" title="{{ __('Team: ') . $tName }}">
                                <i class="ti ti-users text-muted me-1" style="font-size: 9px;"></i>
                                <span class="text-truncate" style="color: #64748b; font-weight: 500; font-size: 9px; text-transform: uppercase; letter-spacing: 0.3px;">{{ $tName }}</span>
                            </span>
                        @endif
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>


<style>
    :root {
        --image-matched-green: #00B388;
        --soft-green: rgba(0, 179, 136, 0.06);
        --soft-danger: rgba(220, 53, 69, 0.06);
        --soft-primary: rgba(13, 110, 253, 0.06);
        --dark-grey: #2d3748;
    }
    .image-matched-card {
        border-radius: 10px !important;
        border: 1px solid rgba(0,0,0,0.06) !important;
        transition: all 0.2s ease;
        background: #fff !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02) !important;
        position: relative;
        z-index: 10;
        margin-bottom: 0 !important; /* Gap handled by container */
    }
    .image-matched-card:hover {
        z-index: 15; /* Above other leads but below sticky header (20) */
        transform: translateY(-1.5px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.05) !important;
        border-color: rgba(0, 179, 136, 0.35) !important;
    }
    .no-caret::after {
        display: none !important;
    }
    .text-dark-grey { color: var(--dark-grey) !important; }
    .image-matched-brand-color {
        color: var(--image-matched-green) !important;
    }
    .bg-light-success-soft { background-color: var(--soft-green) !important; color: #008f6d !important; }
    .bg-light-danger-soft { background-color: var(--soft-danger) !important; color: #dc3545 !important; }
    .bg-light-primary-soft { background-color: var(--soft-primary) !important; color: #0d6efd !important; }
    
    .call-btn-enhanced {
        background: var(--soft-green);
        color: var(--image-matched-green);
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }
    .call-btn-enhanced:hover {
        background: var(--image-matched-green);
        color: white;
        transform: scale(1.08) rotate(3deg);
    }
    .action-icon-hover:hover {
        color: var(--image-matched-green) !important;
        transform: scale(1.08);
    }
    .background-transparent {
        background: transparent !important;
    }
    .overlap-avatars .avatar-sm {
        transition: transform 0.2s ease;
        background: #fff;
    }
    .overlap-avatars .avatar-sm:hover {
        transform: scale(1.08);
        z-index: 10;
    }
    .dropdown-item {
        font-size: 12px;
        font-weight: 500;
        padding: 0.5rem 1rem;
        color: #4a5568 !important;
        display: flex;
        align-items: center;
        transition: all 0.15s ease;
    }
    .dropdown-item i {
        font-size: 14px;
        margin-right: 10px;
        opacity: 0.8;
    }
    .dropdown-item:hover {
        background-color: var(--soft-green);
        color: var(--image-matched-green) !important;
    }
    .f-11 { font-size: 11px !important; }
    .f-12 { font-size: 12px !important; }
    .f-14 { font-size: 14px !important; }
    .f-15 { font-size: 15px !important; }
    .f-16 { font-size: 16px !important; }
</style>
