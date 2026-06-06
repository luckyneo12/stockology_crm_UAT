@extends('layouts.main')

@section('page-title')
    {{ __('User Management Hub') }}
@endsection

@section('page-breadcrumb')
    {{ __('User Management') }}
@endsection

@section('page-action')
    <div class="d-flex gap-2 align-items-center">
        <div class="action-btn action-btn-users" style="{{ $activeTab != 'users' ? 'display: none;' : '' }}">
            @permission('user create')
                <a href="#" class="btn btn-sm btn-primary px-3" data-ajax-popup="true" data-size="md"
                    data-title="{{ __('Create New User') }}" data-url="{{ route('users.create') }}"
                    data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}">
                    <i class="ti ti-user-plus me-1"></i> {{ __('New User') }}
                </a>
            @endpermission
        </div>

        <div class="action-btn action-btn-branches" style="{{ ($activeTab != 'branches' && $activeTab != 'org-chart') ? 'display: none;' : '' }}">
            @permission('branch create')
                <a href="#" class="btn btn-sm btn-primary px-3" data-ajax-popup="true" data-size="md"
                    data-title="{{ __('Create New Branch') }}" data-url="{{ route('branch.create') }}"
                    data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create Branch') }}">
                    <i class="ti ti-building me-1"></i> {{ __('New Branch') }}
                </a>
            @endpermission
        </div>

        <div class="action-btn action-btn-departments" style="{{ ($activeTab != 'departments' && $activeTab != 'teams' && $activeTab != 'org-chart') ? 'display: none;' : '' }}">
            @permission('department create')
                <a href="#" class="btn btn-sm btn-primary px-3" data-ajax-popup="true" data-size="md"
                    data-title="{{ __('Create New Department') }}" data-url="{{ route('department.create') }}"
                    data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create Department') }}">
                    <i class="ti ti-sitemap me-1"></i> {{ __('New Department') }}
                </a>
            @endpermission
        </div>
    </div>
@endsection

@section('content')

{{-- ===== HERO STATS BAR ===== --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="umh-stat-card umh-stat-users">
            <div class="umh-stat-icon"><i class="ti ti-users"></i></div>
            <div class="umh-stat-body">
                <div class="umh-stat-value">{{ $users->total() }}</div>
                <div class="umh-stat-label">{{ __('Total Users') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="umh-stat-card umh-stat-active">
            <div class="umh-stat-icon"><i class="ti ti-circle-check"></i></div>
            <div class="umh-stat-body">
                <div class="umh-stat-value">{{ $users->getCollection()->where('is_disable', 0)->count() }}</div>
                <div class="umh-stat-label">{{ __('Active') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="umh-stat-card umh-stat-dept">
            <div class="umh-stat-icon"><i class="ti ti-sitemap"></i></div>
            <div class="umh-stat-body">
                <div class="umh-stat-value">{{ count($departments) }}</div>
                <div class="umh-stat-label">{{ __('Departments') }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="umh-stat-card umh-stat-teams">
            <div class="umh-stat-icon"><i class="ti ti-topology-star"></i></div>
            <div class="umh-stat-body">
                <div class="umh-stat-value">{{ count($teams) }}</div>
                <div class="umh-stat-label">{{ __('Teams') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ===== MAIN CARD ===== --}}
<div class="row">
    <div class="col-sm-12">
        <div class="umh-main-card">

            {{-- TAB NAV --}}
            <div class="umh-tab-nav">
                <ul class="nav umh-tabs" id="pills-tab" role="tablist">
                    <li role="presentation">
                        <button class="nav-link umh-tab-btn {{ $activeTab == 'users' ? 'active' : '' }}" id="pills-users-tab"
                            data-bs-toggle="pill" data-bs-target="#pills-users" type="button" role="tab"
                            aria-controls="pills-users" aria-selected="{{ $activeTab == 'users' ? 'true' : 'false' }}">
                            <i class="ti ti-users"></i>
                            <span>{{ __('Users') }}</span>
                            <span class="umh-tab-count">{{ $users->total() }}</span>
                        </button>
                    </li>
                    <li role="presentation">
                        <button class="nav-link umh-tab-btn {{ $activeTab == 'branches' ? 'active' : '' }}" id="pills-branches-tab"
                            data-bs-toggle="pill" data-bs-target="#pills-branches" type="button" role="tab"
                            aria-controls="pills-branches" aria-selected="{{ $activeTab == 'branches' ? 'true' : 'false' }}">
                            <i class="ti ti-building"></i>
                            <span>{{ __('Branches') }}</span>
                            <span class="umh-tab-count">{{ count($branches) }}</span>
                        </button>
                    </li>
                    <li role="presentation">
                        <button class="nav-link umh-tab-btn {{ $activeTab == 'departments' ? 'active' : '' }}" id="pills-departments-tab"
                            data-bs-toggle="pill" data-bs-target="#pills-departments" type="button" role="tab"
                            aria-controls="pills-departments" aria-selected="{{ $activeTab == 'departments' ? 'true' : 'false' }}">
                            <i class="ti ti-sitemap"></i>
                            <span>{{ __('Departments') }}</span>
                            <span class="umh-tab-count">{{ count($departments) }}</span>
                        </button>
                    </li>
                    <li role="presentation">
                        <button class="nav-link umh-tab-btn {{ $activeTab == 'teams' ? 'active' : '' }}" id="pills-teams-tab"
                            data-bs-toggle="pill" data-bs-target="#pills-teams" type="button" role="tab"
                            aria-controls="pills-teams" aria-selected="{{ $activeTab == 'teams' ? 'true' : 'false' }}">
                            <i class="ti ti-topology-star"></i>
                            <span>{{ __('Teams') }}</span>
                            <span class="umh-tab-count">{{ count($teams) }}</span>
                        </button>
                    </li>
                    <li role="presentation">
                        <button class="nav-link umh-tab-btn {{ $activeTab == 'org-chart' ? 'active' : '' }}" id="pills-org-chart-tab"
                            data-bs-toggle="pill" data-bs-target="#pills-org-chart" type="button" role="tab"
                            aria-controls="pills-org-chart" aria-selected="{{ $activeTab == 'org-chart' ? 'true' : 'false' }}">
                            <i class="ti ti-hierarchy"></i>
                            <span>{{ __('Org Chart') }}</span>
                        </button>
                    </li>
                    <li role="presentation">
                        <button class="nav-link umh-tab-btn {{ $activeTab == 'permissions' ? 'active' : '' }}" id="pills-permissions-tab"
                            data-bs-toggle="pill" data-bs-target="#pills-permissions" type="button" role="tab"
                            aria-controls="pills-permissions" aria-selected="{{ $activeTab == 'permissions' ? 'true' : 'false' }}">
                            <i class="ti ti-shield-lock"></i>
                            <span>{{ __('Permissions') }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="umh-tab-content">
                <div class="tab-content" id="pills-tabContent">

                    {{-- ========== USERS TAB ========== --}}
                    <div class="tab-pane fade {{ $activeTab == 'users' ? 'show active' : '' }}" id="pills-users" role="tabpanel">

                        {{-- Search & Filter Bar --}}
                        <div class="umh-filter-bar mb-4">
                            {{ Form::open(['url' => route('user.management.index'), 'method' => 'GET', 'id' => 'user_filter_form', 'class' => 'd-flex flex-wrap gap-2 align-items-end']) }}
                            <input type="hidden" name="tab" value="users">

                            <div class="umh-filter-group">
                                <label class="umh-filter-label">{{ __('Role') }}</label>
                                {{ Form::select('role', $roles->prepend(__('All Roles'), ''), request('role'), ['class' => 'umh-filter-select', 'id' => 'filterRole']) }}
                            </div>

                            <div class="umh-filter-group">
                                <label class="umh-filter-label">{{ __('Status') }}</label>
                                {{ Form::select('status', ['' => __('All Status'), 'active' => __('Active'), 'inactive' => __('Inactive')], request('status'), ['class' => 'umh-filter-select', 'id' => 'filterStatus']) }}
                            </div>

                            <div class="umh-filter-group umh-filter-search">
                                <label class="umh-filter-label">{{ __('Search') }}</label>
                                <div class="umh-search-wrap">
                                    <i class="ti ti-search"></i>
                                    {{ Form::text('search', request('search'), ['class' => 'umh-filter-input', 'placeholder' => __('Name or Email...'), 'id' => 'filterSearch']) }}
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="umh-btn-apply">
                                    <i class="ti ti-filter me-1"></i>{{ __('Apply') }}
                                </button>
                                <a href="{{ route('user.management.index', ['tab' => 'users']) }}" class="umh-btn-reset">
                                    <i class="ti ti-refresh"></i>
                                </a>
                            </div>
                            {{ Form::close() }}
                        </div>

                        {{-- Active Filter Badges --}}
                        <div id="filterBadgesContainer" style="display:none;" class="mb-3">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <span class="text-muted small"><i class="ti ti-filter me-1"></i>{{ __('Active Filters:') }}</span>
                                <div id="filterBadges"></div>
                                <button type="button" class="umh-clear-filters ms-auto" onclick="clearAllFilters()">
                                    <i class="ti ti-x me-1"></i>{{ __('Clear All') }}
                                </button>
                            </div>
                        </div>

                        {{-- View toggle --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="text-muted mb-0 small">
                                {{ __('Showing') }} <strong>{{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }}</strong> {{ __('of') }} <strong>{{ $users->total() }}</strong> {{ __('users') }}
                            </p>
                            <div class="umh-view-toggle">
                                <button class="umh-view-btn active" id="viewGrid" onclick="setView('grid')"><i class="ti ti-layout-grid"></i></button>
                                <button class="umh-view-btn" id="viewTable" onclick="setView('table')"><i class="ti ti-list"></i></button>
                            </div>
                        </div>

                        {{-- GRID VIEW --}}
                        <div id="userGridView">
                            <div class="row g-3">
                                @forelse ($users as $user)
                                    @php
                                        $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $user->id)->with(['department', 'manager'])->first();
                                    @endphp
                                    <div class="col-xl-3 col-lg-4 col-md-6">
                                        <div class="umh-user-card {{ $user->is_disable ? 'umh-user-inactive' : '' }}">
                                            <div class="umh-user-card-top">
                                                <div class="umh-user-avatar-wrap">
                                                    <img src="{{ check_file($user->avatar) ? get_file($user->avatar) : get_file('uploads/users-avatar/avatar.png') }}" class="umh-user-avatar" alt="{{ $user->name }}">
                                                    <span class="umh-user-status-dot {{ $user->is_disable ? 'offline' : 'online' }}"></span>
                                                </div>
                                                <div class="umh-user-actions-top">
                                                    @permission('user edit')
                                                        <a href="#!" data-url="{{ route('users.edit', $user->id) }}" data-ajax-popup="true" data-size="md" class="umh-icon-btn umh-icon-edit" data-title="{{ __('Edit User') }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}"><i class="ti ti-pencil"></i></a>
                                                    @endpermission
                                                    @permission('user delete')
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id], 'id' => 'delete-form-'.$user->id]) !!}
                                                            <a href="#!" class="umh-icon-btn umh-icon-del bs-pass-para show_confirm" data-confirm="{{ __('Are You Sure?') }}" data-text="{{ __('This action can not be undone. Do you want to continue?') }}" data-confirm-yes="delete-form-{{ $user->id }}" data-bs-toggle="tooltip" title="{{ __('Delete') }}"><i class="ti ti-trash"></i></a>
                                                        {!! Form::close() !!}
                                                    @endpermission
                                                </div>
                                            </div>

                                            <div class="umh-user-info">
                                                <h6 class="umh-user-name">{{ $user->name }}</h6>
                                                <p class="umh-user-email">{{ $user->email }}</p>
                                                <div class="umh-user-badges">
                                                    <span class="umh-role-badge">{{ ucfirst($user->type) }}</span>
                                                    @if($user->is_disable == 0)
                                                        <span class="umh-status-badge umh-status-active">{{ __('Active') }}</span>
                                                    @else
                                                        <span class="umh-status-badge umh-status-inactive">{{ __('Inactive') }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="umh-user-meta">
                                                @if($employee && $employee->department)
                                                    <div class="umh-meta-item">
                                                        <i class="ti ti-sitemap"></i>
                                                        <span>{{ $employee->department->name }}</span>
                                                    </div>
                                                @endif
                                                @if($employee && $employee->manager)
                                                    <div class="umh-meta-item">
                                                        <i class="ti ti-user-check"></i>
                                                        <span>{{ $employee->manager->name }}</span>
                                                    </div>
                                                @endif
                                                @if(!$employee || (!$employee->department && !$employee->manager))
                                                    <div class="umh-meta-item text-muted">
                                                        <i class="ti ti-info-circle"></i>
                                                        <span>{{ __('No dept assigned') }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="umh-empty-state">
                                            <i class="ti ti-users-group"></i>
                                            <h5>{{ __('No users found') }}</h5>
                                            <p>{{ __('Try adjusting your filters or create a new user.') }}</p>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- TABLE VIEW --}}
                        <div id="userTableView" style="display:none;">
                            <div class="table-responsive umh-table-wrap">
                                <table class="table umh-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>{{ __('User') }}</th>
                                            <th>{{ __('Dept / Team') }}</th>
                                            <th>{{ __('Reporting To') }}</th>
                                            <th>{{ __('Role') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="text-end">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $user)
                                            @php
                                                $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $user->id)->with(['department', 'manager'])->first();
                                            @endphp
                                            <tr class="umh-table-row {{ $user->is_disable ? 'umh-row-inactive' : '' }}">
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="umh-table-avatar-wrap">
                                                            <img src="{{ check_file($user->avatar) ? get_file($user->avatar) : get_file('uploads/users-avatar/avatar.png') }}" class="umh-table-avatar">
                                                            <span class="umh-user-status-dot {{ $user->is_disable ? 'offline' : 'online' }}"></span>
                                                        </div>
                                                        <div>
                                                            <div class="fw-600 text-sm">{{ $user->name }}</div>
                                                            <div class="text-muted" style="font-size:0.78rem;">{{ $user->email }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $employee && $employee->department ? $employee->department->name : '-' }}</td>
                                                <td>{{ $employee && $employee->manager ? $employee->manager->name : '-' }}</td>
                                                <td><span class="umh-role-badge">{{ ucfirst($user->type) }}</span></td>
                                                <td>
                                                    @if($user->is_disable == 0)
                                                        <span class="umh-status-badge umh-status-active">{{ __('Active') }}</span>
                                                    @else
                                                        <span class="umh-status-badge umh-status-inactive">{{ __('Inactive') }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end gap-1">
                                                        @permission('user edit')
                                                            <a href="#!" data-url="{{ route('users.edit', $user->id) }}" data-ajax-popup="true" data-size="md" class="umh-icon-btn umh-icon-edit" data-title="{{ __('Edit User') }}"><i class="ti ti-pencil"></i></a>
                                                        @endpermission
                                                        @permission('user delete')
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id], 'id' => 'delete-form-tbl-'.$user->id]) !!}
                                                                <a href="#!" class="umh-icon-btn umh-icon-del bs-pass-para show_confirm" data-confirm="{{ __('Are You Sure?') }}" data-text="{{ __('This action can not be undone. Do you want to continue?') }}" data-confirm-yes="delete-form-tbl-{{ $user->id }}"><i class="ti ti-trash"></i></a>
                                                            {!! Form::close() !!}
                                                        @endpermission
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            {!! $users->links() !!}
                        </div>
                    </div>

                    {{-- ========== BRANCHES TAB ========== --}}
                    <div class="tab-pane fade {{ $activeTab == 'branches' ? 'show active' : '' }}" id="pills-branches" role="tabpanel">
                        @if(count($branches) === 0)
                            <div class="umh-empty-state">
                                <i class="ti ti-building"></i>
                                <h5>{{ __('No branches yet') }}</h5>
                                <p>{{ __('Create your first branch to organize your teams geographically.') }}</p>
                            </div>
                        @else
                            <div class="row g-3">
                                @foreach ($branches as $branch)
                                    <div class="col-xl-3 col-lg-4 col-md-6">
                                        <div class="umh-entity-card">
                                            <div class="umh-entity-icon" style="--ec-color: #6366f1;">
                                                <i class="ti ti-building"></i>
                                            </div>
                                            <div class="umh-entity-body">
                                                <h6 class="umh-entity-name">{{ $branch->name }}</h6>
                                                <span class="umh-entity-type-badge">{{ __('Branch') }}</span>
                                            </div>
                                            <div class="umh-entity-actions">
                                                @permission('branch edit')
                                                    <a href="#!" data-url="{{ route('branch.edit', $branch->id) }}" data-ajax-popup="true" data-size="md" class="umh-icon-btn umh-icon-edit" data-title="{{ __('Edit Branch') }}"><i class="ti ti-pencil"></i></a>
                                                @endpermission
                                                @permission('branch delete')
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['branch.destroy', $branch->id], 'id' => 'delete-branch-'.$branch->id]) !!}
                                                        <a href="#!" class="umh-icon-btn umh-icon-del bs-pass-para show_confirm" data-confirm-yes="delete-branch-{{ $branch->id }}"><i class="ti ti-trash"></i></a>
                                                    {!! Form::close() !!}
                                                @endpermission
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- ========== DEPARTMENTS TAB ========== --}}
                    <div class="tab-pane fade {{ $activeTab == 'departments' ? 'show active' : '' }}" id="pills-departments" role="tabpanel">
                        @if(count($departments) === 0)
                            <div class="umh-empty-state">
                                <i class="ti ti-sitemap"></i>
                                <h5>{{ __('No departments yet') }}</h5>
                                <p>{{ __('Create departments to structure your organization.') }}</p>
                            </div>
                        @else
                            <div class="row g-3">
                                @foreach ($departments as $dept)
                                    <div class="col-xl-3 col-lg-4 col-md-6">
                                        <div class="umh-entity-card">
                                            <div class="umh-entity-icon" style="--ec-color: #10b981;">
                                                @if(!empty($dept->logo))
                                                    <img src="{{ get_file($dept->logo) }}" alt="" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                                @else
                                                    <i class="ti ti-sitemap"></i>
                                                @endif
                                            </div>
                                            <div class="umh-entity-body">
                                                <h6 class="umh-entity-name">{{ $dept->name }}</h6>
                                                @if($dept->branch)
                                                    <span class="umh-entity-meta"><i class="ti ti-building me-1"></i>{{ $dept->branch->name }}</span>
                                                @endif
                                                @if($dept->parent)
                                                    <span class="umh-entity-meta"><i class="ti ti-git-branch me-1"></i>{{ $dept->parent->name }}</span>
                                                @endif
                                            </div>
                                            <div class="umh-entity-actions">
                                                <a href="#!" class="umh-icon-btn umh-icon-convert convert-team" data-id="{{ $dept->id }}" data-bs-toggle="tooltip" title="{{ __('Convert to Team') }}"><i class="ti ti-exchange"></i></a>
                                                @permission('department edit')
                                                    <a href="#!" data-url="{{ route('department.edit', $dept->id) }}" data-ajax-popup="true" data-size="md" class="umh-icon-btn umh-icon-edit" data-title="{{ __('Edit Department') }}"><i class="ti ti-pencil"></i></a>
                                                @endpermission
                                                @permission('department delete')
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['department.destroy', $dept->id], 'id' => 'delete-dept-'.$dept->id]) !!}
                                                        <a href="#!" class="umh-icon-btn umh-icon-del bs-pass-para show_confirm" data-confirm-yes="delete-dept-{{ $dept->id }}"><i class="ti ti-trash"></i></a>
                                                    {!! Form::close() !!}
                                                @endpermission
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- ========== TEAMS TAB ========== --}}
                    <div class="tab-pane fade {{ $activeTab == 'teams' ? 'show active' : '' }}" id="pills-teams" role="tabpanel">
                        @if(count($teams) === 0)
                            <div class="umh-empty-state">
                                <i class="ti ti-topology-star"></i>
                                <h5>{{ __('No teams yet') }}</h5>
                                <p>{{ __('Convert departments to teams or create new ones.') }}</p>
                            </div>
                        @else
                            <div class="row g-3">
                                @foreach ($teams as $team)
                                    @php
                                        $leversUser = $team->levers_user_id ? \App\Models\User::find($team->levers_user_id) : null;
                                    @endphp
                                    <div class="col-xl-4 col-lg-6">
                                        <div class="umh-team-card">
                                            <div class="umh-team-card-head">
                                                <div class="umh-team-icon">
                                                    @if(!empty($team->logo))
                                                        <img src="{{ get_file($team->logo) }}" alt="" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                                    @else
                                                        <i class="ti ti-topology-star"></i>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="umh-team-name">{{ $team->name }}</h6>
                                                    <div class="umh-team-meta">
                                                        @if($team->branch) <span><i class="ti ti-building"></i> {{ $team->branch->name }}</span> @endif
                                                        @if($team->parent) <span><i class="ti ti-git-branch"></i> {{ $team->parent->name }}</span> @endif
                                                    </div>
                                                </div>
                                                <div class="umh-entity-actions">
                                                    @permission('department edit')
                                                        <a href="#!" data-url="{{ route('department.edit', $team->id) }}" data-ajax-popup="true" data-size="md" class="umh-icon-btn umh-icon-edit" data-title="{{ __('Edit Team') }}"><i class="ti ti-pencil"></i></a>
                                                    @endpermission
                                                    @permission('department delete')
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['department.destroy', $team->id], 'id' => 'delete-team-'.$team->id]) !!}
                                                            <a href="#!" class="umh-icon-btn umh-icon-del bs-pass-para show_confirm" data-confirm-yes="delete-team-{{ $team->id }}"><i class="ti ti-trash"></i></a>
                                                        {!! Form::close() !!}
                                                    @endpermission
                                                </div>
                                            </div>
                                            <div class="umh-team-levers">
                                                <span class="umh-levers-label" data-bs-toggle="tooltip" title="{{ __('This account automatically receives all leads when any team member is inactivated or deleted.') }}">
                                                    <i class="ti ti-archive"></i> {{ __('Leavers Account') }}
                                                </span>
                                                @if($leversUser)
                                                    <div class="umh-levers-user">
                                                        <span class="umh-levers-name">{{ $leversUser->name }}</span>
                                                        @permission('user edit')
                                                            <a href="#!" data-url="{{ route('users.edit', $leversUser->id) }}" data-ajax-popup="true" data-size="md" class="umh-icon-btn umh-icon-edit py-0 px-1" data-title="{{ __('Edit Leavers Account') }}"><i class="ti ti-pencil" style="font-size:0.75rem;"></i></a>
                                                        @endpermission
                                                    </div>
                                                @else
                                                    <span class="umh-levers-none">{{ __('Not configured') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- ========== ORG CHART TAB ========== --}}
                    <div class="tab-pane fade {{ $activeTab == 'org-chart' ? 'show active' : '' }}" id="pills-org-chart" role="tabpanel">
                        <div class="umh-org-controls mb-3 d-flex justify-content-end gap-2">
                            <button class="umh-org-btn zoom-in" title="{{ __('Zoom In') }}"><i class="ti ti-zoom-in"></i></button>
                            <button class="umh-org-btn zoom-out" title="{{ __('Zoom Out') }}"><i class="ti ti-zoom-out"></i></button>
                            <button class="umh-org-btn zoom-reset" title="{{ __('Reset') }}"><i class="ti ti-refresh"></i></button>
                        </div>

                        <div class="dept-org-chart-wrapper" style="background: #f8fafc; border-radius: 16px; height: 680px; overflow: hidden; position: relative; cursor: grab; border: 1px solid #e2e8f0;">
                            <div class="org-grid-bg" style="position: absolute; top:0; left:0; right:0; bottom:0; background-size: 28px 28px; background-image: linear-gradient(to right, rgba(0, 0, 0, 0.03) 1px, transparent 1px), linear-gradient(to bottom, rgba(0, 0, 0, 0.03) 1px, transparent 1px);"></div>
                            <div class="org-panzoom-content" style="transform-origin: 0 0; transition: transform 0.2s ease-out; position: absolute; top:0; left:0; padding: 40px; min-width: 100%; min-height: 100%;">
                                <div class="org-tree dept-tree text-center position-relative">
                                    <ul>
                                        <li>
                                            <div class="dept-card-v2 company-node">
                                                @if($companyUser)
                                                    <div class="dept-header-v2">
                                                        <h6 class="dept-name-v2">{{ $companyUser->name }}</h6>
                                                        <span class="badge bg-primary ms-2 text-white">{{ __('Company') }}</span>
                                                    </div>
                                                    <div class="manager-info-v2 d-flex align-items-center mt-2">
                                                        <img src="{{ check_file($companyUser->avatar) ? get_file($companyUser->avatar) : get_file('uploads/users-avatar/avatar.png') }}" class="manager-avatar-v2">
                                                        <div class="ms-2 text-start">
                                                            <p class="manager-name-v2 mb-0">{{ $companyUser->name }}</p>
                                                            <small class="manager-role-v2 text-muted">{{ __('CEO / Owner') }}</small>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="dept-header-v2">
                                                        <h6 class="dept-name-v2">{{ __('Company') }}</h6>
                                                    </div>
                                                @endif
                                            </div>
                                            <ul>
                                                @foreach($rootDepartments as $dept)
                                                    @include('users.management.dept_node', ['dept' => $dept])
                                                @endforeach
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ========== PERMISSIONS TAB ========== --}}
                    <div class="tab-pane fade {{ $activeTab == 'permissions' ? 'show active' : '' }}" id="pills-permissions" role="tabpanel">
                        <div class="umh-perm-info mb-4">
                            <i class="ti ti-shield-check me-2"></i>
                            {{ __('Set data visibility levels for users. "Self" shows only assigned data, "Department" shows all data within their department.') }}
                        </div>
                        <div class="table-responsive umh-table-wrap">
                            <table class="table umh-table align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ __('User') }}</th>
                                        <th>{{ __('Role') }}</th>
                                        <th>{{ __('Visibility Level') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr class="umh-table-row">
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="umh-table-avatar-wrap">
                                                        <img src="{{ check_file($user->avatar) ? get_file($user->avatar) : get_file('uploads/users-avatar/avatar.png') }}" class="umh-table-avatar">
                                                        <span class="umh-user-status-dot {{ $user->is_disable ? 'offline' : 'online' }}"></span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-600 text-sm">{{ $user->name }}</div>
                                                        <div class="text-muted" style="font-size:0.78rem;">{{ $user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="umh-role-badge">{{ ucfirst($user->type) }}</span></td>
                                            <td>
                                                <select class="umh-visibility-select visibility-select" data-user-id="{{ $user->id }}">
                                                    <option value="self" {{ $user->visibility_level == 'self' ? 'selected' : '' }}>{{ __('Self (Assigned Only)') }}</option>
                                                    <option value="department" {{ $user->visibility_level == 'department' ? 'selected' : '' }}>{{ __('Department (Entire Dept)') }}</option>
                                                    <option value="all" {{ $user->visibility_level == 'all' ? 'selected' : '' }}>{{ __('All (Entire Organization)') }}</option>
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 d-flex justify-content-end">
                            {!! $users->links() !!}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Department Detail Modal --}}
<div class="modal fade" id="deptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content umh-modal-content">
            <div class="modal-header umh-modal-header">
                <h5 class="modal-title" id="deptModalTitle">{{ __('Department Details') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body umh-modal-body" id="deptModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('css')
<style>
/* =============================================
   UMH - User Management Hub - Light Theme
   ============================================= */

/* --- STAT CARDS --- */
.umh-stat-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 18px 20px;
    border-radius: 14px;
    background: #fff;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    overflow: hidden;
    position: relative;
}
.umh-stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 14px 14px 0 0;
}
.umh-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}
.umh-stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.umh-stat-value {
    font-size: 1.75rem;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -0.5px;
}
.umh-stat-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #8a92a6;
    margin-top: 3px;
    font-weight: 500;
}
.umh-stat-users::before { background: linear-gradient(90deg, #18bf6b, #3ecf8e); }
.umh-stat-users .umh-stat-icon { background: rgba(24,191,107,0.12); color: #18bf6b; }
.umh-stat-users .umh-stat-value { color: #18bf6b; }

.umh-stat-active::before { background: linear-gradient(90deg, #06b6d4, #18bf6b); }
.umh-stat-active .umh-stat-icon { background: rgba(6,182,212,0.12); color: #0891b2; }
.umh-stat-active .umh-stat-value { color: #0891b2; }

.umh-stat-dept::before { background: linear-gradient(90deg, #a78bfa, #6366f1); }
.umh-stat-dept .umh-stat-icon { background: rgba(99,102,241,0.12); color: #6366f1; }
.umh-stat-dept .umh-stat-value { color: #6366f1; }

.umh-stat-teams::before { background: linear-gradient(90deg, #f59e0b, #ef4444); }
.umh-stat-teams .umh-stat-icon { background: rgba(245,158,11,0.12); color: #d97706; }
.umh-stat-teams .umh-stat-value { color: #d97706; }

/* --- MAIN CARD --- */
.umh-main-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 16px rgba(0,0,0,0.06);
}

/* --- TAB NAV --- */
.umh-tab-nav {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 0 20px;
    overflow-x: auto;
    scrollbar-width: none;
}
.umh-tab-nav::-webkit-scrollbar { display: none; }
.umh-tabs {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-wrap: nowrap;
    gap: 0;
    min-width: max-content;
}
.umh-tab-btn, .umh-tab-btn.nav-link {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 13px 18px;
    background: transparent !important;
    border: none !important;
    border-bottom: 2px solid transparent !important;
    border-radius: 0 !important;
    color: #6c757d !important;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: color 0.2s ease, border-color 0.2s ease, background 0.2s ease;
    white-space: nowrap;
    line-height: 1.4;
    outline: none;
    box-shadow: none !important;
}
.umh-tab-btn i { font-size: 1rem; flex-shrink: 0; }
.umh-tab-btn:hover, .umh-tab-btn.nav-link:hover {
    color: #18bf6b !important;
    background: rgba(24,191,107,0.06) !important;
}
.umh-tab-btn.active, .umh-tab-btn.nav-link.active {
    color: #18bf6b !important;
    border-bottom-color: #18bf6b !important;
    font-weight: 600;
}
.umh-tab-count {
    background: rgba(24,191,107,0.15);
    color: #18bf6b;
    font-size: 0.68rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 20px;
    min-width: 20px;
    text-align: center;
    line-height: 1.6;
}
.umh-tab-btn.active .umh-tab-count {
    background: rgba(24,191,107,0.25);
}

/* --- TAB CONTENT --- */
.umh-tab-content { padding: 20px; }

/* --- FILTER BAR --- */
.umh-filter-bar {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 14px 18px;
}
.umh-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.umh-filter-search { flex: 1; min-width: 200px; }
.umh-filter-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #8a92a6;
    font-weight: 600;
}
.umh-filter-select {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    color: #333;
    padding: 8px 12px;
    font-size: 0.875rem;
    outline: none;
    transition: border-color 0.2s;
    min-width: 150px;
}
.umh-filter-select:focus { border-color: #18bf6b; box-shadow: 0 0 0 3px rgba(24,191,107,0.1); }
.umh-search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.umh-search-wrap i {
    position: absolute;
    left: 10px;
    color: #adb5bd;
    font-size: 0.95rem;
}
.umh-filter-input {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    color: #333;
    padding: 8px 12px 8px 34px;
    font-size: 0.875rem;
    outline: none;
    width: 100%;
    transition: border-color 0.2s;
}
.umh-filter-input:focus { border-color: #18bf6b; box-shadow: 0 0 0 3px rgba(24,191,107,0.1); }
.umh-filter-input::placeholder { color: #adb5bd; }
.umh-btn-apply {
    background: #18bf6b;
    border: none;
    color: #fff;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 8px 18px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    align-self: flex-end;
}
.umh-btn-apply:hover { transform: translateY(-1px); background: #15a85e; box-shadow: 0 4px 12px rgba(24,191,107,0.3); }
.umh-btn-reset {
    background: #fff;
    border: 1px solid #dee2e6;
    color: #6c757d;
    padding: 8px 12px;
    border-radius: 8px;
    transition: all 0.2s;
    align-self: flex-end;
    display: flex;
    align-items: center;
    text-decoration: none;
}
.umh-btn-reset:hover { background: #f8f9fa; color: #333; }

/* --- FILTER BADGES --- */
.umh-clear-filters {
    background: rgba(220,53,69,0.08);
    border: 1px solid rgba(220,53,69,0.2);
    color: #dc3545;
    font-size: 0.78rem;
    padding: 4px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex; align-items: center;
}
.umh-clear-filters:hover { background: rgba(220,53,69,0.15); }

/* --- VIEW TOGGLE --- */
.umh-view-toggle {
    display: flex;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 3px;
}
.umh-view-btn {
    background: none;
    border: none;
    color: #adb5bd;
    padding: 5px 9px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.2s;
}
.umh-view-btn.active {
    background: #fff;
    color: #18bf6b;
    box-shadow: 0 1px 4px rgba(0,0,0,0.1);
}

/* --- USER CARDS (GRID) --- */
.umh-user-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 14px;
    padding: 18px;
    transition: all 0.25s ease;
    position: relative;
    overflow: hidden;
    height: 100%;
    box-shadow: 0 1px 6px rgba(0,0,0,0.05);
}
.umh-user-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, #18bf6b, #3ecf8e);
    border-radius: 14px 14px 0 0;
    opacity: 0;
    transition: opacity 0.3s;
}
.umh-user-card:hover {
    border-color: #a7f3d0;
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(24,191,107,0.12);
}
.umh-user-card:hover::before { opacity: 1; }
.umh-user-card.umh-user-inactive { opacity: 0.65; }

.umh-user-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}
.umh-user-avatar-wrap { position: relative; display: inline-block; }
.umh-user-avatar {
    width: 56px; height: 56px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid #e9ecef;
}
.umh-user-status-dot {
    position: absolute;
    bottom: -2px; right: -2px;
    width: 13px; height: 13px;
    border-radius: 50%;
    border: 2px solid #fff;
}
.umh-user-status-dot.online { background: #22c55e; }
.umh-user-status-dot.offline { background: #adb5bd; }

.umh-user-actions-top {
    display: flex;
    gap: 5px;
    opacity: 0;
    transform: translateY(-4px);
    transition: all 0.2s;
}
.umh-user-card:hover .umh-user-actions-top {
    opacity: 1;
    transform: translateY(0);
}
.umh-icon-btn {
    width: 32px; height: 32px;
    border-radius: 7px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.82rem;
    transition: all 0.2s;
    cursor: pointer;
    border: 1px solid transparent;
}
.umh-icon-edit {
    background: rgba(99,102,241,0.08);
    color: #6366f1;
    border-color: rgba(99,102,241,0.15);
}
.umh-icon-edit:hover { background: rgba(99,102,241,0.18); color: #4f46e5; }
.umh-icon-del {
    background: rgba(220,53,69,0.08);
    color: #dc3545;
    border-color: rgba(220,53,69,0.15);
}
.umh-icon-del:hover { background: rgba(220,53,69,0.18); color: #b02a37; }
.umh-icon-convert {
    background: rgba(245,158,11,0.08);
    color: #d97706;
    border-color: rgba(245,158,11,0.15);
}
.umh-icon-convert:hover { background: rgba(245,158,11,0.18); color: #b45309; }

.umh-user-name {
    font-size: 0.92rem;
    font-weight: 700;
    margin-bottom: 2px;
    color: #212529;
}
.umh-user-email {
    font-size: 0.76rem;
    color: #8a92a6;
    margin-bottom: 10px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.umh-user-badges {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
    margin-bottom: 10px;
}
.umh-role-badge {
    background: rgba(99,102,241,0.08);
    color: #6366f1;
    border: 1px solid rgba(99,102,241,0.15);
    font-size: 0.7rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 20px;
}
.umh-status-badge {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 20px;
}
.umh-status-active {
    background: rgba(34,197,94,0.1);
    color: #16a34a;
    border: 1px solid rgba(34,197,94,0.2);
}
.umh-status-inactive {
    background: rgba(107,114,128,0.1);
    color: #6b7280;
    border: 1px solid rgba(107,114,128,0.2);
}
.umh-user-meta {
    border-top: 1px solid #f1f3f5;
    padding-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.umh-meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.76rem;
    color: #6c757d;
}
.umh-meta-item i { font-size: 0.82rem; color: #18bf6b; }

/* --- TABLE --- */
.umh-table-wrap {
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e9ecef;
}
.umh-table { margin-bottom: 0; }
.umh-table thead tr { background: #f8f9fa; }
.umh-table thead th {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #8a92a6;
    font-weight: 600;
    border-bottom: 1px solid #e9ecef;
    padding: 12px 16px;
    border-top: none;
}
.umh-table-row td {
    border-color: #f1f3f5;
    padding: 10px 16px;
    font-size: 0.875rem;
    color: #333;
    vertical-align: middle;
}
.umh-table-row:hover td { background: #f8fff5; }
.umh-row-inactive td { opacity: 0.55; }
.umh-table-avatar-wrap { position: relative; display: inline-block; }
.umh-table-avatar {
    width: 36px; height: 36px;
    border-radius: 8px;
    object-fit: cover;
    border: 1.5px solid #e9ecef;
}
.umh-table-avatar-wrap .umh-user-status-dot {
    bottom: -2px; right: -2px;
    width: 10px; height: 10px;
    border: 2px solid #fff;
}

/* --- ENTITY CARDS (Branches, Departments) --- */
.umh-entity-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.22s ease;
    box-shadow: 0 1px 6px rgba(0,0,0,0.04);
}
.umh-entity-card:hover {
    border-color: #c3e6ab;
    background: #f8fff5;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(111,217,67,0.1);
}
.umh-entity-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
    background: rgba(99,102,241,0.1);
    color: #6366f1;
    border: 1px solid rgba(99,102,241,0.15);
}
.umh-entity-body { flex: 1; min-width: 0; }
.umh-entity-name {
    font-size: 0.88rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.umh-entity-meta {
    display: block;
    font-size: 0.74rem;
    color: #8a92a6;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.umh-entity-type-badge {
    display: inline-block;
    font-size: 0.67rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 20px;
    background: #f0f1f3;
    color: #6c757d;
}
.umh-entity-actions { display: flex; gap: 5px; flex-shrink: 0; }

/* --- TEAM CARDS --- */
.umh-team-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 14px;
    overflow: hidden;
    transition: all 0.22s ease;
    box-shadow: 0 1px 6px rgba(0,0,0,0.04);
}
.umh-team-card:hover {
    border-color: #fde68a;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245,158,11,0.12);
}
.umh-team-card-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid #f1f3f5;
}
.umh-team-icon {
    width: 40px; height: 40px;
    background: rgba(245,158,11,0.1);
    border: 1px solid rgba(245,158,11,0.2);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    color: #d97706;
    flex-shrink: 0;
}
.umh-team-name {
    font-size: 0.92rem;
    font-weight: 700;
    color: #212529;
    margin: 0 0 2px;
}
.umh-team-meta { display: flex; gap: 10px; flex-wrap: wrap; }
.umh-team-meta span {
    font-size: 0.74rem;
    color: #8a92a6;
    display: flex;
    align-items: center;
    gap: 3px;
}
.umh-team-meta i { font-size: 0.78rem; }
.umh-team-levers {
    padding: 10px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fafafa;
}
.umh-levers-label {
    font-size: 0.74rem;
    color: #8a92a6;
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: help;
}
.umh-levers-label i { font-size: 0.82rem; color: #18bf6b; }
.umh-levers-user { display: flex; align-items: center; gap: 6px; }
.umh-levers-name {
    font-size: 0.78rem;
    font-weight: 600;
    color: #18bf6b;
    background: rgba(24,191,107,0.1);
    padding: 2px 10px;
    border-radius: 20px;
    border: 1px solid rgba(24,191,107,0.2);
}
.umh-levers-none {
    font-size: 0.76rem;
    color: #d97706;
    background: rgba(245,158,11,0.08);
    padding: 2px 10px;
    border-radius: 20px;
    border: 1px solid rgba(245,158,11,0.15);
}

/* --- ORG CHART BUTTONS --- */
.umh-org-btn {
    width: 32px; height: 32px;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 7px;
    color: #6c757d;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    font-size: 0.92rem;
    transition: all 0.2s;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}
.umh-org-btn:hover { background: #f8f9fa; color: #333; border-color: #adb5bd; }
.dept-org-chart-wrapper { cursor: grab; }
.dept-org-chart-wrapper:active { cursor: grabbing; }
.org-panzoom-content { will-change: transform; user-select: none; }

/* --- ORG TREE --- */
.dept-tree ul { padding-top: 20px; position: relative; transition: all 0.5s; display: flex; justify-content: center; }
.dept-tree li { float: left; text-align: center; list-style-type: none; position: relative; padding: 20px 5px 0 5px; transition: all 0.5s; }
.dept-tree li::before, .dept-tree li::after { content: ''; position: absolute; top: 0; right: 50%; border-top: 2px solid #cbd5e1; width: 50%; height: 20px; }
.dept-tree li::after { right: auto; left: 50%; border-left: 2px solid #cbd5e1; }
.dept-tree li:only-child::after, .dept-tree li:only-child::before { display: none; }
.dept-tree li:only-child { padding-top: 0; }
.dept-tree li:first-child::before, .dept-tree li:last-child::after { border: 0 none; }
.dept-tree li:last-child::before { border-right: 2px solid #cbd5e1; border-radius: 0 5px 0 0; }
.dept-tree li:first-child::after { border-radius: 5px 0 0 0; }
.dept-tree ul ul::before { content: ''; position: absolute; top: 0; left: 50%; border-left: 2px solid #cbd5e1; width: 0; height: 20px; }
.dept-card-v2 { background: #fff; border: 1px solid #e9ecef; padding: 14px; display: inline-block; border-radius: 12px; color: #212529; cursor: pointer; transition: 0.25s; min-width: 220px; box-shadow: 0 2px 10px rgba(0,0,0,0.07); }
.dept-card-v2:hover { background: #f8fff5; transform: translateY(-4px); border-color: #a7f3d0; box-shadow: 0 8px 24px rgba(24,191,107,0.12); }
.dept-card-v2.company-node { background: linear-gradient(135deg, #eef2ff, #f0f9ff); border: 1px solid #c7d2fe; }
.dept-header-v2 { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; border-bottom: 1px solid #f1f3f5; padding-bottom: 7px; }
.dept-name-v2 { font-weight: 700; margin: 0; font-size: 0.95rem; color: #18bf6b; }
.manager-avatar-v2 { width: 40px; height: 40px; border-radius: 9px; object-fit: cover; border: 2px solid #e9ecef; }
.manager-name-v2 { font-weight: 600; font-size: 0.88rem; color: #212529; }
.manager-role-v2 { font-size: 0.76rem; color: #8a92a6; }
.emp-avatar-stack { width: 24px; height: 24px; border-radius: 50%; border: 2px solid #fff; margin-left: -8px; transition: 0.2s; }
.emp-avatar-stack:first-child { margin-left: 0; }
.emp-avatar-stack:hover { transform: translateY(-3px); z-index: 10; }
.more-count { background: #dee2e6; color: #495057; font-size: 0.68rem; display: flex; align-items: center; justify-content: center; font-weight: 700; }
.emp-count-v2 { font-size: 0.77rem; color: #18bf6b; text-decoration: none; transition: 0.2s; font-weight: 500; }
.emp-count-v2:hover { text-decoration: underline; color: #15a85e; }
.add-btn-v2 { position: absolute; bottom: -14px; left: 50%; transform: translateX(-50%); width: 28px; height: 28px; background: #18bf6b; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.95rem; opacity: 0; transition: 0.3s; z-index: 5; }
.dept-card-v2:hover .add-btn-v2 { opacity: 1; bottom: -18px; }
.fav-icon:hover { color: #f59e0b; }

/* --- PERMISSIONS --- */
.umh-perm-info {
    background: rgba(99,102,241,0.07);
    border: 1px solid rgba(99,102,241,0.15);
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 0.875rem;
    color: #6366f1;
    display: flex;
    align-items: center;
}
.umh-visibility-select {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    color: #333;
    padding: 6px 10px;
    font-size: 0.82rem;
    outline: none;
    min-width: 180px;
    transition: border-color 0.2s;
}
.umh-visibility-select:focus { border-color: #18bf6b; }

/* --- EMPTY STATE --- */
.umh-empty-state {
    text-align: center;
    padding: 50px 20px;
    color: #adb5bd;
}
.umh-empty-state i { font-size: 3rem; color: #dee2e6; display: block; margin-bottom: 14px; }
.umh-empty-state h5 { color: #6c757d; margin-bottom: 6px; }
.umh-empty-state p { font-size: 0.875rem; color: #adb5bd; }

/* --- MODAL --- */
.umh-modal-content {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 14px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
.umh-modal-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 14px 18px;
    border-radius: 14px 14px 0 0;
}
.umh-modal-header .modal-title { color: #212529; font-weight: 700; }
.umh-modal-body { padding: 18px; }

/* --- FILTER BADGE --- */
.filter-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.76rem;
    font-weight: 500;
    background: rgba(99,102,241,0.08);
    color: #6366f1;
    border: 1px solid rgba(99,102,241,0.15);
}
.filter-badge .btn-close { margin-left: 5px; font-size: 0.6rem; opacity: 0.6; cursor: pointer; }
.filter-badge .btn-close:hover { opacity: 1; }

/* --- ANIMATIONS --- */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.umh-user-card, .umh-entity-card, .umh-team-card { animation: fadeInUp 0.3s ease both; }
.col-xl-3:nth-child(1) .umh-user-card { animation-delay: 0.04s; }
.col-xl-3:nth-child(2) .umh-user-card { animation-delay: 0.08s; }
.col-xl-3:nth-child(3) .umh-user-card { animation-delay: 0.12s; }
.col-xl-3:nth-child(4) .umh-user-card { animation-delay: 0.16s; }

/* --- RESPONSIVE --- */
@media (max-width: 768px) {
    .umh-tab-content { padding: 14px; }
    .umh-filter-bar { flex-direction: column; }
    .umh-filter-group, .umh-filter-select, .umh-filter-input { width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
// ---- View Toggle (Grid / Table) ----
function setView(mode) {
    if (mode === 'grid') {
        document.getElementById('userGridView').style.display = '';
        document.getElementById('userTableView').style.display = 'none';
        document.getElementById('viewGrid').classList.add('active');
        document.getElementById('viewTable').classList.remove('active');
        localStorage.setItem('umhView', 'grid');
    } else {
        document.getElementById('userGridView').style.display = 'none';
        document.getElementById('userTableView').style.display = '';
        document.getElementById('viewGrid').classList.remove('active');
        document.getElementById('viewTable').classList.add('active');
        localStorage.setItem('umhView', 'table');
    }
}
$(document).ready(function() {
    const savedView = localStorage.getItem('umhView') || 'grid';
    setView(savedView);

    // Check if URL has a tab parameter. If not, restore from localStorage.
    var activeTabId = '{{ $activeTab }}';
    if (activeTabId === 'users') {
        const urlParams = new URLSearchParams(window.location.search);
        if (!urlParams.has('tab')) {
            activeTabId = localStorage.getItem('umhActiveTab') || 'users';
        }
    }

    var tabMap = {
        'users': 'pills-users-tab',
        'branches': 'pills-branches-tab',
        'departments': 'pills-departments-tab',
        'teams': 'pills-teams-tab',
        'org-chart': 'pills-org-chart-tab',
        'permissions': 'pills-permissions-tab'
    };
    var targetTabId = tabMap[activeTabId] || 'pills-users-tab';
    var targetTabEl = document.getElementById(targetTabId);
    if (targetTabEl && typeof bootstrap !== 'undefined') {
        var tabInstance = new bootstrap.Tab(targetTabEl);
        tabInstance.show();
    }
});

// ---- Visibility Update ----
$(document).on('change', '.visibility-select', function() {
    var userId = $(this).data('user-id');
    var level = $(this).val();
    $.ajax({
        url: '{{ route('user.visibility.update') }}',
        type: 'POST',
        data: { user_id: userId, visibility_level: level, _token: '{{ csrf_token() }}' },
        success: function(data) {
            if (data.success) show_toastr('Success', data.message, 'success');
            else show_toastr('Error', data.message, 'error');
        }
    });
});

// ---- Dept Card Click (Org Chart) ----
$(document).on('click', '.add-sub-dept', function(e) { e.stopPropagation(); });
$(document).on('click', '.dept-card-v2:not(.company-node)', function() {
    var deptId = $(this).data('id');
    var deptName = $(this).find('.dept-name-v2').text();
    $('#deptModalTitle').text(deptName + ' - {{ __("Users") }}');
    $('#deptModal').modal('show');
    $.ajax({
        url: '{{ url("department-users") }}/' + deptId,
        success: function(html) { $('#deptModalBody').html(html); }
    });
});

// ---- Convert to Team ----
$(document).on('click', '.convert-team', function() {
    var deptId = $(this).data('id');
    if (confirm('{{ __("Are you sure you want to convert this department to a team?") }}')) {
        $.ajax({
            url: '{{ route("department.convert", "") }}/' + deptId,
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(data) { show_toastr('Success', data.message, 'success'); location.reload(); },
            error: function(data) { show_toastr('Error', data.responseJSON.message || 'Error occurred', 'error'); }
        });
    }
});

// ---- Action Button Toggle on Tab Switch ----
$('button[data-bs-toggle="pill"]').on('shown.bs.tab', function(e) {
    var target = $(e.target).attr("id");

    // Save active tab to localStorage
    var tabMapRev = {
        'pills-users-tab': 'users',
        'pills-branches-tab': 'branches',
        'pills-departments-tab': 'departments',
        'pills-teams-tab': 'teams',
        'pills-org-chart-tab': 'org-chart',
        'pills-permissions-tab': 'permissions'
    };
    var activeTab = tabMapRev[target] || 'users';
    localStorage.setItem('umhActiveTab', activeTab);

    $('.action-btn').hide();
    if (target === 'pills-users-tab') $('.action-btn-users').show();
    else if (target === 'pills-branches-tab') $('.action-btn-branches').show();
    else if (target === 'pills-departments-tab') $('.action-btn-departments').show();
    else if (target === 'pills-teams-tab') $('.action-btn-departments').show();
    else if (target === 'pills-org-chart-tab') { $('.action-btn-branches').show(); $('.action-btn-departments').show(); setTimeout(centerOrgChart, 100); }
});

// ---- Org Chart Pan & Zoom ----
let scale = 1, translateX = 0, translateY = 0, isDragging = false, startX, startY;
const wrapper = document.querySelector('.dept-org-chart-wrapper');
const content = document.querySelector('.org-panzoom-content');
function updateTransform() { if (content) content.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`; }
function centerOrgChart() {
    if (!wrapper || !content) return;
    const currentScale = scale;
    content.style.transition = 'none';
    content.style.transform = 'none';
    const wrapperRect = wrapper.getBoundingClientRect();
    const contentRect = content.getBoundingClientRect();
    content.style.transition = 'transform 0.2s ease-out';
    scale = currentScale;
    translateX = Math.max(20, (wrapperRect.width - contentRect.width * scale) / 2);
    translateY = 20;
    updateTransform();
}
$('.zoom-in').on('click', function() { scale = Math.min(scale + 0.1, 2); updateTransform(); });
$('.zoom-out').on('click', function() { scale = Math.max(scale - 0.1, 0.3); updateTransform(); });
$('.zoom-reset').on('click', function() { scale = 1; centerOrgChart(); });
if (wrapper) {
    wrapper.addEventListener('mousedown', (e) => {
        if (e.target.closest('.dept-card-v2') || e.target.closest('button') || e.target.closest('a')) return;
        isDragging = true; startX = e.clientX - translateX; startY = e.clientY - translateY;
    });
    window.addEventListener('mousemove', (e) => { if (!isDragging) return; translateX = e.clientX - startX; translateY = e.clientY - startY; updateTransform(); });
    window.addEventListener('mouseup', () => { isDragging = false; });
    wrapper.addEventListener('touchstart', (e) => {
        if (e.target.closest('.dept-card-v2') || e.target.closest('button') || e.target.closest('a')) return;
        if (e.touches.length === 1) { isDragging = true; startX = e.touches[0].clientX - translateX; startY = e.touches[0].clientY - translateY; }
    });
    wrapper.addEventListener('touchmove', (e) => {
        if (!isDragging || e.touches.length !== 1) return;
        translateX = e.touches[0].clientX - startX; translateY = e.touches[0].clientY - startY; updateTransform();
    });
    wrapper.addEventListener('touchend', () => { isDragging = false; });
}
$(document).ready(function() {
    if ($('#pills-org-chart-tab').hasClass('active')) setTimeout(centerOrgChart, 500);
});

// ---- Filter Badges ----
if (typeof show_toastr === 'undefined') {
    window.show_toastr = function(title, message, type) {
        if (typeof toastr !== 'undefined') { toastr[type](message, title); return; }
        if (typeof bootstrap !== 'undefined') {
            var toastHtml = '<div class="toast align-items-center text-white bg-' + (type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info') + ' border-0" role="alert" data-bs-delay="5000" style="position:fixed;top:20px;right:20px;z-index:9999;">' +
                '<div class="d-flex"><div class="toast-body"><strong>' + title + ':</strong> ' + message + '</div>' +
                '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
            $('body').append(toastHtml);
            var el = $('body').children().last()[0];
            var t = new bootstrap.Toast(el); t.show();
            $(el).on('hidden.bs.toast', function() { $(this).remove(); });
        } else alert(title + ': ' + message);
    };
}

$(document).ready(function() {
    loadSavedFilters();
    $('#filterRole, #filterStatus, #filterSearch').on('input change', updateFilterBadges);
    updateFilterBadges();
});

function updateFilterBadges() {
    const role = $('#filterRole').val(), status = $('#filterStatus').val(), search = $('#filterSearch').val();
    const badges = [];
    if (role) badges.push(createBadge('role', $('#filterRole option:selected').text(), role));
    if (status) badges.push(createBadge('status', $('#filterStatus option:selected').text(), status));
    if (search) badges.push(createBadge('search', search, search));
    if (badges.length > 0) { $('#filterBadges').html(badges.join('')); $('#filterBadgesContainer').show(); }
    else { $('#filterBadges').empty(); $('#filterBadgesContainer').hide(); }
}
function createBadge(type, label, value) {
    return `<span class="badge filter-badge" data-filter-type="${type}">${getFilterLabel(type)}: ${label}<button type="button" class="btn-close btn-close-white ms-1" onclick="removeFilter('${type}')"></button></span>`;
}
function getFilterLabel(type) { return {'role':'{{ __("Role") }}','status':'{{ __("Status") }}','search':'{{ __("Search") }}'}[type] || type; }
function removeFilter(type) {
    const map = { role: '#filterRole', status: '#filterStatus', search: '#filterSearch' };
    if (map[type]) { if (type === 'search') $(map[type]).val('').trigger('input'); else $(map[type]).val('').trigger('change'); }
    updateFilterBadges();
    $('#user_filter_form').submit();
}
function clearAllFilters() {
    $('#filterRole, #filterStatus, #filterSearch').val('');
    updateFilterBadges();
    localStorage.removeItem('permanentUserFilters');
    $('#user_filter_form').submit();
}
function saveFilters() {
    localStorage.setItem('permanentUserFilters', JSON.stringify({ role: $('#filterRole').val(), status: $('#filterStatus').val(), search: $('#filterSearch').val(), permanent: true }));
}
function loadSavedFilters() {
    try {
        const f = JSON.parse(localStorage.getItem('permanentUserFilters') || '{}');
        if (f.permanent) {
            if (f.role) $('#filterRole').val(f.role);
            if (f.status) $('#filterStatus').val(f.status);
            if (f.search) $('#filterSearch').val(f.search);
            updateFilterBadges();
        }
    } catch(e) {}
}
</script>
@endpush
