@php($labels = $lead->labels())
<div class="card image-matched-card shadow-sm mb-2" data-id="{{ $lead->id }}">
    <div class="card-header border-0 pb-0 d-flex flex-column pt-2 px-2 background-transparent">
        <div class="d-flex align-items-center justify-content-between mb-1">
            <h6 class="mb-0 flex-grow-1" style="min-width: 0;">
                <a href="@permission('lead show')@if ($lead->is_active){{ route('leads.show', $lead->id) }}@else#@endif @else#@endpermission" 
                   class="text-dark-grey fw-bold text-decoration-none" 
                   style="font-size: 0.95rem; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; line-height: 1.2;">
                    {{ $lead->name }}
                </a>
            </h6>
            <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-2">
                @if($lead->phone)
                    <a href="javascript:void(0)" 
                       class="call-btn-enhanced click-to-call" 
                       data-phone="{{ $lead->phone }}"
                       data-bs-toggle="tooltip" 
                       title="{{__('Click to Call')}}">
                        <i class="ti ti-phone-call f-14"></i>
                    </a>
                @endif
                <div class="dropdown">
                    @if (!$lead->is_active)
                        <i class="fas fa-lock text-muted f-16"></i>
                    @else
                        <button type="button" class="btn btn-sm dropdown-toggle p-0 shadow-none border-0 d-flex align-items-center no-caret" 
                                data-bs-toggle="dropdown" data-bs-offset="0,12" aria-haspopup="true" aria-expanded="false">
                            <i class="ti ti-dots-vertical f-20 text-muted"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-lg border-1 border-light py-2 rounded-3" 
                             style="z-index: 3500; min-width: 160px; position: absolute;">
                            @permission('lead edit')
                                <a data-url="{{ URL::to('leads/' . $lead->id . '/labels') }}" data-ajax-popup="true" data-title="{{ __('Labels') }}" class="dropdown-item">
                                    <i class="ti ti-bookmark text-success"></i>{{ __('Labels') }}
                                </a>
                            @endpermission
                            @permission('lead show')
                                @if($lead->is_active)
                                    <a href="{{route('leads.show',$lead->id)}}" class="dropdown-item">
                                        <i class="ti ti-eye text-primary"></i>{{ __('View') }}
                                    </a>
                                @endif
                            @endpermission
                            @permission('lead edit')
                                <a data-url="{{ URL::to('leads/' . $lead->id . '/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{ __('Edit') }}" class="dropdown-item">
                                    <i class="ti ti-pencil text-warning"></i>{{ __('Edit') }}
                                </a>
                            @endpermission
                            @permission('lead delete')
                                {!! Form::open(['method' => 'DELETE', 'route' => ['leads.destroy', $lead->id], 'id' => 'delete-form-' . $lead->id]) !!}
                                <a class="dropdown-item show_confirm text-danger" data-confirm="{{ __('Are You Sure?') }}" data-text="{{ __('This action can not be undone.') }}">
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
                    <span class="badge badge-pill bg-{{ $label->color }} p-1 px-2" style="font-size: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; border-radius: 4px; opacity: 0.9;">
                        {{ $label->name }}
                    </span>
                @endforeach
            @endif
        </div>
    </div>
    
    <div class="card-body p-2 pt-1">

        
        <div class="d-flex align-items-center justify-content-between mb-2 image-matched-brand-color">
            <div class="d-flex align-items-center p-1 px-2 rounded-2 bg-light-success-soft" data-bs-toggle="tooltip" title="{{__('Tasks')}}">
                <i class="ti ti-list me-1 f-14"></i>
                <span class="fw-bold" style="font-size: 11px;">{{ count($lead->complete_tasks) }}/{{ count($lead->tasks) }}</span>
            </div>

            <div class="d-flex align-items-center p-1 px-2 rounded-2 bg-light-danger-soft" data-bs-toggle="tooltip" title="{{__('Reminders (Today/Total)')}}">
                <i class="ti ti-bell me-1 f-14 {{ $lead->getTodayRemindersCount() > 0 ? 'text-danger' : '' }}"></i>
                <span class="fw-bold" style="font-size: 11px;">{{ $lead->getTodayRemindersCount() }}/{{ $lead->getFilteredReminders()->count() }}</span>
            </div>
            
            @if (isset($lead->date) && !empty($lead->date))
                <div class="d-flex align-items-center p-1 px-2 rounded-2 bg-light-primary-soft" data-bs-toggle="tooltip" title="{{__('Created At')}}">
                    <i class="ti ti-calendar-event me-1 f-14"></i>
                    <span class="fw-bold" style="font-size: 11px;">{{ company_date_formate($lead->date) }}</span>
                </div>
            @endif
        </div>

        <hr class="my-2" style="opacity: 0.05;">

        <div class="d-flex align-items-center justify-content-between pt-1">
            <div class="d-flex gap-3 image-matched-brand-color">
                @if($lead->isResponsible())
                    <div class="d-flex align-items-center" data-bs-toggle="tooltip" title="{{__('KYC Comments')}}">
                        @if(\Auth::user()->isAbleTo('lead kyc comment'))
                            <a href="#!" data-url="{{ route('leads.discussions.create', $lead->id) }}?is_kyc=1" data-ajax-popup="true" data-title="{{__('Add KYC Comment')}}" data-size="md" class="text-inherit action-icon-hover">
                                <i class="ti ti-shield-check me-1 f-16"></i>
                            </a>
                        @else
                            <i class="ti ti-shield-check me-1 f-16"></i>
                        @endif
                        <span class="fw-bold" style="font-size: 12px;">{{ $lead->discussions->where('is_kyc', 1)->count() }}</span>
                    </div>
                @endif
                <div class="d-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Sources')}}">
                    <i class="ti ti-circles me-1 f-16"></i>
                    <span class="fw-bold" style="font-size: 12px;">{{ count($lead->sources()) }}</span>
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
                            $emp = \Workdo\Hrm\Entities\Employee::where('user_id', $user->id)->first();
                            $tName = $emp && $emp->department ? $emp->department->name : '';
                            $employeeDeptCache[$user->id] = $tName;
                        }
                    ?>
                    <div class="d-flex flex-wrap align-items-center justify-content-end mb-1 gap-1" style="max-width: 100%;">
                        <span class="badge rounded-pill bg-white border d-flex align-items-center shadow-sm px-2 mb-1" 
                              style="border-color: #e2e8f0; height: 24px; max-width: 120px;"
                              data-bs-toggle="tooltip" title="{{ __('Responsible: ') . $user->name }}">
                            <i class="ti ti-user text-muted me-1" style="font-size: 10px;"></i>
                            <span class="text-truncate" style="color: #475569; font-weight: 600; font-size: 11px;">{{ $user->name }}</span>
                        </span>
                        @if(!empty($tName))
                            <span class="badge rounded-pill d-flex align-items-center shadow-sm px-2 mb-1"
                                  style="background: #f8fafc; border: 1px solid #e2e8f0; height: 24px; max-width: 100px;"
                                  data-bs-toggle="tooltip" title="{{ __('Team: ') . $tName }}">
                                <i class="ti ti-users text-muted me-1" style="font-size: 10px;"></i>
                                <span class="text-truncate" style="color: #64748b; font-weight: 500; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;">{{ $tName }}</span>
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
        border-radius: 12px !important;
        border: 1px solid rgba(0,0,0,0.06) !important;
        transition: all 0.25s ease;
        background: #fff !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03) !important;
        position: relative;
        z-index: 10;
        margin-bottom: 0 !important; /* Gap handled by container */
    }
    .image-matched-card:hover {
        z-index: 15; /* Above other leads but below sticky header (20) */
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.06) !important;
        border-color: rgba(0, 179, 136, 0.4) !important;
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
        width: 26px;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }
    .call-btn-enhanced:hover {
        background: var(--image-matched-green);
        color: white;
        transform: scale(1.1) rotate(5deg);
    }
    .action-icon-hover:hover {
        color: var(--image-matched-green) !important;
        transform: scale(1.1);
    }
    .background-transparent {
        background: transparent !important;
    }
    .overlap-avatars .avatar-sm {
        transition: transform 0.2s ease;
        background: #fff;
    }
    .overlap-avatars .avatar-sm:hover {
        transform: scale(1.1);
        z-index: 10;
    }
    .dropdown-item {
        font-size: 13px;
        font-weight: 500;
        padding: 0.7rem 1.2rem;
        color: #4a5568 !important;
        display: flex;
        align-items: center;
        transition: all 0.15s ease;
    }
    .dropdown-item i {
        font-size: 16px;
        margin-right: 12px;
        opacity: 0.8;
    }
    .dropdown-item:hover {
        background-color: var(--soft-green);
        color: var(--image-matched-green) !important;
    }
    .f-14 { font-size: 14px !important; }
    .f-16 { font-size: 16px !important; }
    .f-18 { font-size: 18px !important; }
    .f-20 { font-size: 20px !important; }
</style>
