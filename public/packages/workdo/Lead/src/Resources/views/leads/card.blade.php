@php($labels = $lead->labels())
<div class="card image-matched-card shadow-sm mb-3" data-id="{{ $lead->id }}">
    <div class="card-header border-0 pb-0 d-flex align-items-center justify-content-between pt-3 px-3 background-transparent">
        <div class="d-flex flex-wrap gap-1">
            @if ($labels)
                @foreach ($labels as $label)
                    <span class="badge badge-pill bg-{{ $label->color }} p-1 px-2" style="font-size: 9px; opacity: 0.8;">
                        {{ $label->name }}
                    </span>
                @endforeach
            @endif
        </div>
        <div class="card-header-right">
            <div class="btn-group card-option">
                @if (!$lead->is_active)
                    <i class="fas fa-lock text-muted"></i>
                @else
                    <button type="button" class="btn btn-sm dropdown-toggle p-0 shadow-none border-0" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ti ti-dots-vertical f-18 text-muted"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm">
                        @permission('lead edit')
                            <a data-url="{{ URL::to('leads/' . $lead->id . '/labels') }}" data-ajax-popup="true" data-title="{{ __('Labels') }}" class="dropdown-item">
                                <i class="ti ti-bookmark me-1 text-success"></i>{{ __('Labels') }}
                            </a>
                        @endpermission
                        @permission('lead show')
                            @if($lead->is_active)
                                <a href="{{route('leads.show',$lead->id)}}" class="dropdown-item">
                                    <i class="ti ti-eye me-1 text-primary"></i>{{ __('View') }}
                                </a>
                            @endif
                        @endpermission
                        @permission('lead edit')
                            <a data-url="{{ URL::to('leads/' . $lead->id . '/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{ __('Edit') }}" class="dropdown-item">
                                <i class="ti ti-pencil me-1 text-warning"></i>{{ __('Edit') }}
                            </a>
                        @endpermission
                        @permission('lead delete')
                            {!! Form::open(['method' => 'DELETE', 'route' => ['leads.destroy', $lead->id], 'id' => 'delete-form-' . $lead->id]) !!}
                            <a class="dropdown-item show_confirm text-danger" data-confirm="{{ __('Are You Sure?') }}" data-text="{{ __('This action can not be undone.') }}">
                                <i class="ti ti-trash me-1"></i>{{ __('Delete') }}
                            </a>
                            {!! Form::close() !!}
                        @endpermission
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="card-body p-3 pt-2">
        <h5 class="mb-3">
            <a href="@permission('lead show')@if ($lead->is_active){{ route('leads.show', $lead->id) }}@else#@endif @else#@endpermission" 
               class="image-matched-brand-color fw-bold text-decoration-none" style="font-size: 1.1rem; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                {{ $lead->name }}
            </a>
        </h5>
        
        <div class="d-flex align-items-center justify-content-between mb-3 image-matched-brand-color">
            <div class="d-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Tasks')}}">
                <i class="ti ti-list me-2 f-18"></i>
                <span class="fw-bold" style="font-size: 14px;">{{ count($lead->complete_tasks) }}/{{ count($lead->tasks) }}</span>
            </div>

            <div class="d-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Reminders (Today/Total)')}}">
                <i class="ti ti-bell me-2 f-18 {{ $lead->getTodayRemindersCount() > 0 ? 'text-danger' : '' }}"></i>
                <span class="fw-bold" style="font-size: 14px;">{{ $lead->getTodayRemindersCount() }}/{{ $lead->getFilteredReminders()->count() }}</span>
            </div>
            
            @if (isset($lead->date) && !empty($lead->date))
                <div class="d-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Created At')}}">
                    <i class="ti ti-calendar-event me-2 f-18"></i>
                    <span class="fw-bold" style="font-size: 14px;">{{ company_date_formate($lead->date) }}</span>
                </div>
            @endif
        </div>

        <hr class="my-3" style="opacity: 0.08;">

        <div class="d-flex align-items-center justify-content-between pt-1">
            <div class="d-flex gap-3 image-matched-brand-color">
                <div class="d-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Products')}}">
                    <i class="ti ti-shopping-cart-plus me-1 f-18"></i>
                    <span class="fw-bold" style="font-size: 14px;">{{ count($lead->products()) }}</span>
                </div>
                <div class="d-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Sources')}}">
                    <i class="ti ti-circles me-1 f-18"></i>
                    <span class="fw-bold" style="font-size: 14px;">{{ count($lead->sources()) }}</span>
                </div>
            </div>
            
            <div class="user-group overlap-avatars">
                @foreach ($lead->users->take(3) as $user)
                    <div class="avatar-sm rounded-circle border-2 border-white overflow-hidden shadow-sm d-inline-block" style="width: 28px; height: 28px; margin-left: -10px;">
                        <img alt="{{ $user->name }}" data-bs-toggle="tooltip" title="{{ $user->name }}"
                             src="{{ $user->avatar ? get_file($user->avatar) : get_file('avatar.png') }}"
                             class="h-100 w-100 object-fit-cover">
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --image-matched-green: #00B388;
    }
    .image-matched-card {
        border-radius: 16px !important;
        border: none !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        background: #fff !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
    }
    .image-matched-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 20px rgba(0,0,0,0.08) !important;
    }
    .image-matched-brand-color {
        color: var(--image-matched-green) !important;
    }
    .background-transparent {
        background: transparent !important;
    }
    .overlap-avatars :first-child {
        margin-left: 0 !important;
    }
    .f-18 { font-size: 18px !important; }
</style>
