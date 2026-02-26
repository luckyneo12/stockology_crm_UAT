<li>
    <div class="dept-card-v2 position-relative" data-id="{{ $dept->id }}">
        <!-- Sub-dept add button -->
        @permission('department create')
            <a href="#" class="add-btn-v2 add-sub-dept" 
               data-url="{{ route('department.create', ['parent_id' => $dept->id]) }}" 
               data-ajax-popup="true" data-size="md" data-title="{{ __('Add Sub Department') }}"
               data-bs-toggle="tooltip" title="{{ __('Add Sub Department') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endpermission

        <div class="dept-header-v2">
            <h6 class="dept-name-v2">{{ $dept->name }}</h6>
            <i class="ti ti-star fav-icon"></i>
        </div>

        @if($dept->manager && $dept->manager->user)
            <div class="manager-info-v2 d-flex align-items-center mt-2">
                <img src="{{ check_file($dept->manager->user->avatar) ? get_file($dept->manager->user->avatar) : get_file('uploads/users-avatar/avatar.png') }}" class="manager-avatar-v2">
                <div class="ms-2 text-start">
                    <p class="manager-name-v2 mb-0">{{ $dept->manager->user->name }}</p>
                    <small class="manager-role-v2 text-muted">{{ $dept->manager->designation->name ?? __('Manager') }}</small>
                </div>
            </div>
        @else
            <div class="no-manager-v2 mt-2 py-1 bg-light-warning rounded">
                <small class="text-warning">{{ __('No Head Specified') }}</small>
            </div>
        @endif

        <div class="employees-v2 mt-3 d-flex justify-content-between align-items-center">
            <div class="emp-avatars d-flex overflow-hidden">
                @foreach($dept->employees->take(4) as $emp)
                    @if($emp->user)
                        <img src="{{ check_file($emp->user->avatar) ? get_file($emp->user->avatar) : get_file('uploads/users-avatar/avatar.png') }}" 
                             class="emp-avatar-stack" title="{{ $emp->user->name }}" data-bs-toggle="tooltip">
                    @endif
                @endforeach
                @if($dept->employees->count() > 4)
                    <div class="emp-avatar-stack more-count">+{{ $dept->employees->count() - 4 }}</div>
                @endif
            </div>
            <a href="#" class="emp-count-v2">{{ $dept->employees->count() }} {{ __('employees') }}</a>
        </div>
    </div>

    @if($dept->children->count() > 0)
        <ul>
            @foreach($dept->children as $child)
                @include('users.management.dept_node', ['dept' => $child])
            @endforeach
        </ul>
    @endif
</li>
