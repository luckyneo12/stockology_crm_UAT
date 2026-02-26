@extends('layouts.main')

@section('page-title')
    {{ __('User Management Hub') }}
@endsection

@section('page-breadcrumb')
    {{ __('User Management') }}
@endsection

@section('page-action')
    <div class="d-flex gap-2">
        <div class="action-btn action-btn-users" style="{{ $activeTab != 'users' ? 'display: none;' : '' }}">
            @permission('user create')
                <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md"
                    data-title="{{ __('Create New User') }}" data-url="{{ route('users.create') }}"
                    data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}">
                    <i class="ti ti-plus"></i> {{ __('New User') }}
                </a>
            @endpermission
            
            @permission('messenger group create')
                <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createGroupModal"
                    data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create Group Chat') }}">
                    <i class="ti ti-users-group"></i> {{ __('Create Group') }}
                </a>
            @endpermission
        </div>

        <div class="action-btn action-btn-branches" style="{{ ($activeTab != 'branches' && $activeTab != 'org-chart') ? 'display: none;' : '' }}">
            @permission('branch create')
                <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md"
                    data-title="{{ __('Create New Branch') }}" data-url="{{ route('branch.create') }}"
                    data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create Branch') }}">
                    <i class="ti ti-plus"></i> {{ __('New Branch') }}
                </a>
            @endpermission
        </div>

        <div class="action-btn action-btn-departments" style="{{ ($activeTab != 'departments' && $activeTab != 'teams' && $activeTab != 'org-chart') ? 'display: none;' : '' }}">
            @permission('department create')
                <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md"
                    data-title="{{ __('Create New Department') }}" data-url="{{ route('department.create') }}"
                    data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create Department') }}">
                    <i class="ti ti-plus"></i> {{ __('New Department') }}
                </a>
            @endpermission
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card glass-card">
            <div class="card-header border-bottom">
                <ul class="nav nav-pills nav-fill card-header-pills" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab == 'users' ? 'active' : '' }}" id="pills-users-tab" data-bs-toggle="pill" data-bs-target="#pills-users" type="button" role="tab">{{ __('Users') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab == 'branches' ? 'active' : '' }}" id="pills-branches-tab" data-bs-toggle="pill" data-bs-target="#pills-branches" type="button" role="tab">{{ __('Branches') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab == 'departments' ? 'active' : '' }}" id="pills-departments-tab" data-bs-toggle="pill" data-bs-target="#pills-departments" type="button" role="tab">{{ __('Departments') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab == 'teams' ? 'active' : '' }}" id="pills-teams-tab" data-bs-toggle="pill" data-bs-target="#pills-teams" type="button" role="tab">{{ __('Teams') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab == 'org-chart' ? 'active' : '' }}" id="pills-org-chart-tab" data-bs-toggle="pill" data-bs-target="#pills-org-chart" type="button" role="tab">{{ __('Departmental Org Chart') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab == 'permissions' ? 'active' : '' }}" id="pills-permissions-tab" data-bs-toggle="pill" data-bs-target="#pills-permissions" type="button" role="tab">{{ __('Access Permissions') }}</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="pills-tabContent">
                    
                    <!-- Users Tab -->
                    <div class="tab-pane fade {{ $activeTab == 'users' ? 'show active' : '' }}" id="pills-users" role="tabpanel">
                        
                        <!-- Filter Card -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-primary text-white mb-0 shadow-none" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2);">
                                    <div class="card-body">
                                        <h5 class="card-title text-white mb-3">{{ __('Advanced Filter') }}</h5>
                                        {{ Form::open(['url' => route('user.management.index'), 'method' => 'GET', 'id' => 'user_filter_form']) }}
                                        <input type="hidden" name="tab" value="users">
                                        <div class="row align-items-end">
                                            <div class="col-md-3 mb-3">
                                                {{ Form::label('role', __('Role'), ['class' => 'form-label text-white']) }}
                                                {{ Form::select('role', $roles->prepend(__('Select Role'), ''), request('role'), ['class' => 'form-select']) }}
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                {{ Form::label('status', __('Status'), ['class' => 'form-label text-white']) }}
                                                {{ Form::select('status', ['' => __('Select Status'), 'active' => __('Active'), 'inactive' => __('Inactive')], request('status'), ['class' => 'form-select']) }}
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                {{ Form::label('search', __('Search'), ['class' => 'form-label text-white']) }}
                                                {{ Form::text('search', request('search'), ['class' => 'form-control', 'placeholder' => __('Search by Name or Email')]) }}
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <button type="submit" class="btn btn-light w-100 me-2" data-bs-toggle="tooltip" title="{{ __('Apply') }}"><i class="ti ti-search"></i></button>
                                                <a href="{{ route('user.management.index', ['tab' => 'users']) }}" class="btn btn-outline-light w-100 mt-2" data-bs-toggle="tooltip" title="{{ __('Reset') }}"><i class="ti ti-refresh"></i></a>
                                            </div>
                                        </div>
                                        {{ Form::close() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Filter Card -->

                        <div class="table-responsive">
                            <table class="table align-items-center">
                                <thead>
                                    <tr>
                                        <th>{{ __('Avatar') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Email') }}</th>
                                        <th>{{ __('Dept/Team') }}</th>
                                        <th>{{ __('Reporting To') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Role') }}</th>
                                        <th class="text-end">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        @php
                                            $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $user->id)->with(['department', 'manager'])->first();
                                        @endphp
                                        <tr>
                                            <td>
                                                <img src="{{ check_file($user->avatar) ? get_file($user->avatar) : get_file('uploads/users-avatar/avatar.png') }}" class="wid-40 rounded-circle">
                                            </td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $employee && $employee->department ? $employee->department->name : '-' }}</td>
                                            <td>{{ $employee && $employee->manager ? $employee->manager->name : '-' }}</td>
                                            <td>
                                                @if($user->is_disable == 0)
                                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                                @endif
                                            </td>
                                            <td><span class="badge bg-primary">{{ ucfirst($user->type) }}</span></td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    @permission('user edit')
                                                        <a href="#!" data-url="{{ route('users.edit', $user->id) }}" data-ajax-popup="true" data-size="md" class="btn btn-sm btn-info" data-title="{{ __('Edit User') }}"><i class="ti ti-pencil"></i></a>
                                                    @endpermission
                                                    @permission('user delete')
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id], 'id' => 'delete-form-'.$user->id]) !!}
                                                            <a href="#!" class="btn btn-sm btn-danger bs-pass-para show_confirm" data-confirm="{{__('Are You Sure?')}}" data-text="{{__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="delete-form-{{$user->id}}"><i class="ti ti-trash"></i></a>
                                                        {!! Form::close() !!}
                                                    @endpermission
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {!! $users->links() !!}
                        </div>
                    </div>

                    <!-- Branches Tab -->
                    <div class="tab-pane fade {{ $activeTab == 'branches' ? 'show active' : '' }}" id="pills-branches" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th class="text-end">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($branches as $branch)
                                        <tr>
                                            <td>{{ $branch->name }}</td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    @permission('branch edit')
                                                        <a href="#!" data-url="{{ route('branch.edit', $branch->id) }}" data-ajax-popup="true" data-size="md" class="btn btn-sm btn-info" data-title="{{ __('Edit Branch') }}"><i class="ti ti-pencil"></i></a>
                                                    @endpermission
                                                    @permission('branch delete')
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['branch.destroy', $branch->id], 'id' => 'delete-branch-'.$branch->id]) !!}
                                                            <a href="#!" class="btn btn-sm btn-danger bs-pass-para show_confirm" data-confirm-yes="delete-branch-{{$branch->id}}"><i class="ti ti-trash"></i></a>
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

                    <!-- Departments Tab -->
                    <div class="tab-pane fade {{ $activeTab == 'departments' ? 'show active' : '' }}" id="pills-departments" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Branch') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Parent') }}</th>
                                        <th class="text-end">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($departments as $dept)
                                        <tr>
                                            <td>{{ $dept->branch->name ?? '-' }}</td>
                                            <td>{{ $dept->name }}</td>
                                            <td>{{ $dept->parent->name ?? __('None') }}</td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="#!" class="btn btn-sm btn-warning convert-team" data-id="{{ $dept->id }}" data-bs-toggle="tooltip" title="{{ __('Convert to Team') }}">
                                                        <i class="ti ti-exchange"></i>
                                                    </a>
                                                    @permission('department edit')
                                                        <a href="#!" data-url="{{ route('department.edit', $dept->id) }}" data-ajax-popup="true" data-size="md" class="btn btn-sm btn-info" data-title="{{ __('Edit Department') }}"><i class="ti ti-pencil"></i></a>
                                                    @endpermission
                                                    @permission('department delete')
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['department.destroy', $dept->id], 'id' => 'delete-dept-'.$dept->id]) !!}
                                                            <a href="#!" class="btn btn-sm btn-danger bs-pass-para show_confirm" data-confirm-yes="delete-dept-{{$dept->id}}"><i class="ti ti-trash"></i></a>
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

                    <!-- Teams Tab -->
                    <div class="tab-pane fade {{ $activeTab == 'teams' ? 'show active' : '' }}" id="pills-teams" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Branch') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Parent Department') }}</th>
                                        <th class="text-end">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($teams as $team)
                                        <tr>
                                            <td>{{ $team->branch->name ?? '-' }}</td>
                                            <td>{{ $team->name }}</td>
                                            <td>{{ $team->parent->name ?? __('None') }}</td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    @permission('department edit')
                                                        <a href="#!" data-url="{{ route('department.edit', $team->id) }}" data-ajax-popup="true" data-size="md" class="btn btn-sm btn-info" data-title="{{ __('Edit Team') }}"><i class="ti ti-pencil"></i></a>
                                                    @endpermission
                                                    @permission('department delete')
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['department.destroy', $team->id], 'id' => 'delete-team-'.$team->id]) !!}
                                                            <a href="#!" class="btn btn-sm btn-danger bs-pass-para show_confirm" data-confirm-yes="delete-team-{{$team->id}}"><i class="ti ti-trash"></i></a>
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

                    <!-- Org Chart Tab -->
                    <div class="tab-pane fade {{ $activeTab == 'org-chart' ? 'show active' : '' }}" id="pills-org-chart" role="tabpanel">
                        <div class="org-control-panel mb-3 d-flex justify-content-end gap-2">
                            <button class="btn btn-sm btn-light-secondary zoom-in" title="{{ __('Zoom In') }}"><i class="ti ti-zoom-in"></i></button>
                            <button class="btn btn-sm btn-light-secondary zoom-out" title="{{ __('Zoom Out') }}"><i class="ti ti-zoom-out"></i></button>
                            <button class="btn btn-sm btn-light-secondary zoom-reset" title="{{ __('Reset Zoom') }}"><i class="ti ti-refresh"></i></button>
                        </div>
                        
                        <div class="dept-org-chart-wrapper" style="background: #1e1e24; border-radius: 12px; height: 650px; overflow: hidden; position: relative; cursor: grab;">
                            <!-- Grid Background -->
                            <div class="org-grid-bg" style="position: absolute; top:0; left:0; right:0; bottom:0; background-size: 30px 30px; background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px), linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);"></div>
                            
                            <div class="org-panzoom-content" style="transform-origin: 0 0; transition: transform 0.2s ease-out; position: absolute; top:0; left:0; padding: 40px; min-width: 100%; min-height: 100%;">
                                 <div class="org-tree dept-tree text-center position-relative">
                                <ul>
                                    <li>
                                        <!-- Company Root Node -->
                                        <div class="dept-card-v2 company-node">
                                            @if($companyUser)
                                                <div class="dept-header-v2">
                                                    <h6 class="dept-name-v2 text-white">{{ $companyUser->name }}</h6>
                                                    <span class="badge bg-primary ms-2">{{ __('Company') }}</span>
                                                </div>
                                                <div class="manager-info-v2 d-flex align-items-center mt-2">
                                                    <img src="{{ check_file($companyUser->avatar) ? get_file($companyUser->avatar) : get_file('uploads/users-avatar/avatar.png') }}" class="manager-avatar-v2">
                                                    <div class="ms-2 text-start">
                                                        <p class="manager-name-v2 mb-0 text-white">{{ $companyUser->name }}</p>
                                                        <small class="manager-role-v2 text-white-50">{{ __('CEO / Owner') }}</small>
                                                    </div>
                                                </div>
                                                <div class="employees-v2 mt-3 text-start">
                                                    <small class="text-white-50">{{ __('Structure Overview') }}</small>
                                                </div>
                                            @else
                                                <div class="dept-header-v2">
                                                    <h6 class="dept-name-v2 text-white">{{ __('Company') }}</h6>
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

                <!-- Access Permissions Tab -->
                <div class="tab-pane fade {{ $activeTab == 'permissions' ? 'show active' : '' }}" id="pills-permissions" role="tabpanel">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle"></i> {{ __('Set data visibility levels for users. "Self" shows only assigned data, "Department" shows all data within their department.') }}
                    </div>
                    <div class="table-responsive">
                        <table class="table align-items-center">
                            <thead>
                                <tr>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Role') }}</th>
                                    <th>{{ __('Visibility Level') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ check_file($user->avatar) ? get_file($user->avatar) : get_file('uploads/users-avatar/avatar.png') }}" class="wid-30 rounded-circle me-2">
                                                <div>
                                                    <h6 class="mb-0">{{ $user->name }}</h6>
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ ucfirst($user->type) }}</td>
                                        <td>
                                            <select class="form-select form-select-sm visibility-select" data-user-id="{{ $user->id }}">
                                                <option value="self" {{ $user->visibility_level == 'self' ? 'selected' : '' }}>{{ __('Self (Assigned Only)') }}</option>
                                                <!-- <option value="team" {{ $user->visibility_level == 'team' ? 'selected' : '' }}>{{ __('Team (Direct Subordinates)') }}</option> -->
                                                <option value="department" {{ $user->visibility_level == 'department' ? 'selected' : '' }}>{{ __('Department (Entire Dept)') }}</option>
                                                <option value="all" {{ $user->visibility_level == 'all' ? 'selected' : '' }}>{{ __('All (Entire Organization)') }}</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {!! $users->links() !!}
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
</div>

<!-- Department Detail Modal -->
<div class="modal fade" id="deptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content glass">
            <div class="modal-header">
                <h5 class="modal-title" id="deptModalTitle">Department Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="deptModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Group Creation Modal -->
<div class="modal fade" id="createGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Create Group Chat') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createGroupForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">{{ __('Group Name') }}</label><x-required></x-required>
                            <input type="text" name="name" class="form-control" placeholder="{{ __('Enter Group Name') }}" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">{{ __('Description') }}</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="{{ __('Enter Group Description') }}"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">{{ __('Group Avatar') }}</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12 mt-2">
                            <h6>{{ __('Select Members') }}</h6>
                            <hr class="my-2">
                            <div id="groupMembersList" style="max-height: 250px; overflow-y: auto; padding: 10px; border: 1px solid #eee; border-radius: 8px;">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <p class="small mt-2 mb-0">{{ __('Loading users...') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Create Group') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('css')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 15px;
    }
    
    .nav-pills .nav-link {
        color: #fff;
        border-radius: 10px;
        transition: 0.3s;
    }
    
    .nav-pills .nav-link.active {
        background: #6fd943;
        color: #fff;
        box-shadow: 0 4px 15px rgba(111, 217, 67, 0.3);
    }
    
    .dept-tree ul {
        padding-top: 20px; position: relative;
        transition: all 0.5s;
        display: flex; justify-content: center;
    }
    .dept-tree li {
        float: left; text-align: center;
        list-style-type: none;
        position: relative;
        padding: 20px 5px 0 5px;
        transition: all 0.5s;
    }
    .dept-tree li::before, .dept-tree li::after{
        content: '';
        position: absolute; top: 0; right: 50%;
        border-top: 2px solid #555;
        width: 50%; height: 20px;
    }
    .dept-tree li::after{
        right: auto; left: 50%;
        border-left: 2px solid #555;
    }
    .dept-tree li:only-child::after, .dept-tree li:only-child::before {
        display: none;
    }
    .dept-tree li:only-child{ padding-top: 0;}
    .dept-tree li:first-child::before, .dept-tree li:last-child::after{
        border: 0 none;
    }
    .dept-tree li:last-child::before{
        border-right: 2px solid #555;
        border-radius: 0 5px 0 0;
    }
    .dept-tree li:first-child::after{
        border-radius: 5px 0 0 0;
    }
    .dept-tree ul ul::before{
        content: '';
        position: absolute; top: 0; left: 50%;
        border-left: 2px solid #555;
        width: 0; height: 20px;
    }
    
    /* V2 Card Styles */
    .dept-card-v2 {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        padding: 15px;
        display: inline-block;
        border-radius: 12px;
        color: #fff;
        cursor: pointer;
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        min-width: 260px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        backdrop-filter: blur(8px);
    }
    
    .dept-card-v2:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-5px);
        border-color: #6fd943;
    }
    
    .dept-card-v2.company-node {
        background: linear-gradient(135deg, rgba(0, 123, 255, 0.2), rgba(0, 123, 255, 0.1));
        border: 1px solid rgba(0, 123, 255, 0.3);
    }

    .dept-header-v2 {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 8px;
    }

    .dept-name-v2 {
        font-weight: 700;
        margin: 0;
        font-size: 1.1rem;
        color: #6fd943;
    }

    .manager-avatar-v2 {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        object-fit: cover;
        border: 2px solid rgba(255, 255, 255, 0.2);
    }

    .manager-name-v2 {
        font-weight: 600;
        font-size: 0.95rem;
    }

    .manager-role-v2 {
        font-size: 0.8rem;
    }

    .emp-avatar-stack {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 2px solid #222;
        margin-left: -8px;
        transition: 0.2s;
    }
    .emp-avatar-stack:first-child { margin-left: 0; }
    .emp-avatar-stack:hover { transform: translateY(-3px); z-index: 10; }

    .more-count {
        background: #444;
        color: #fff;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    .emp-count-v2 {
        font-size: 0.8rem;
        color: #6fd943;
        text-decoration: none;
        transition: 0.2s;
    }
    .emp-count-v2:hover { text-decoration: underline; color: #fff; }

    .add-btn-v2 {
        position: absolute;
        bottom: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 30px;
        height: 30px;
        background: #6fd943;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1rem;
        opacity: 0;
        transition: 0.3s;
        z-index: 5;
    }

    .dept-card-v2:hover .add-btn-v2 {
        opacity: 1;
        bottom: -20px;
    }

    .fav-icon:hover { color: #ffc107; }

    /* Panzoom Specific Styles */
    .dept-org-chart-wrapper:active {
        cursor: grabbing;
    }
    
    .org-panzoom-content {
        will-change: transform;
        user-select: none;
    }

    .org-control-panel {
        z-index: 10;
        position: relative;
    }
    
    @media (max-width: 768px) {
        .dept-card-v2 {
            min-width: 200px;
            padding: 10px;
        }
        .dept-name-v2 {
            font-size: 0.95rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).on('change', '.visibility-select', function() {
        var userId = $(this).data('user-id');
        var level = $(this).val();
        
        $.ajax({
            url: '{{ route('user.visibility.update') }}',
            type: 'POST',
            data: {
                user_id: userId,
                visibility_level: level,
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                if(data.success) {
                    show_toastr('Success', data.message, 'success');
                } else {
                    show_toastr('Error', data.message, 'error');
                }
            }
        });
    });

    $(document).on('click', '.add-sub-dept', function(e) {
        e.stopPropagation();
    });

    $(document).on('click', '.dept-card-v2:not(.company-node)', function() {
        var deptId = $(this).data('id');
        var deptName = $(this).find('.dept-name-v2').text();
        $('#deptModalTitle').text(deptName + ' - {{ __("Users") }}');
        $('#deptModal').modal('show');
        
        // AJAX to load users of this department
        $.ajax({
            url: '{{ url("department-users") }}/' + deptId,
            success: function(html) {
                $('#deptModalBody').html(html);
            }
        });
    });

    $(document).on('click', '.convert-team', function() {
        var deptId = $(this).data('id');
        if(confirm('{{ __("Are you sure you want to convert this department to a team?") }}')) {
            $.ajax({
                url: '{{ route("department.convert", "") }}/' + deptId,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(data) {
                    show_toastr('Success', data.message, 'success');
                    location.reload();
                },
                error: function(data) {
                    show_toastr('Error', data.responseJSON.message || 'Error occurred', 'error');
                }
            });
        }
    });

    // Toggle action buttons based on active tab
    $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("id");
        $('.action-btn').hide();

        if (target === 'pills-users-tab') {
            $('.action-btn-users').show();
        } else if (target === 'pills-branches-tab') {
            $('.action-btn-branches').show();
        } else if (target === 'pills-departments-tab') {
             $('.action-btn-departments').show();
        } else if (target === 'pills-teams-tab') {
             // Teams share department actions or have none, for now keeping Departments as primary create point
             $('.action-btn-departments').show(); 
        } else if (target === 'pills-org-chart-tab') {
             $('.action-btn-branches').show();
             $('.action-btn-departments').show();
             // Center the chart on first view
             setTimeout(centerOrgChart, 100);
        }
    });

    // Zoom and Pan Logic
    let scale = 1;
    let translateX = 0;
    let translateY = 0;
    let isDragging = false;
    let startX, startY;

    const wrapper = document.querySelector('.dept-org-chart-wrapper');
    const content = document.querySelector('.org-panzoom-content');

    function updateTransform() {
        if (!content) return;
        content.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`;
    }

    function centerOrgChart() {
        if (!wrapper || !content) return;
        
        // Reset scale briefly to get natural size
        const currentScale = scale;
        content.style.transition = 'none';
        content.style.transform = 'none';
        
        const wrapperRect = wrapper.getBoundingClientRect();
        const contentRect = content.getBoundingClientRect();
        
        content.style.transition = 'transform 0.2s ease-out';
        scale = currentScale;

        // Try to center horizontally
        translateX = (wrapperRect.width - contentRect.width * scale) / 2;
        if (translateX < 20) translateX = 20; // Maintain small margin
        
        translateY = 20; 
        
        updateTransform();
    }

    $('.zoom-in').on('click', function() {
        scale = Math.min(scale + 0.1, 2);
        updateTransform();
    });

    $('.zoom-out').on('click', function() {
        scale = Math.max(scale - 0.1, 0.3);
        updateTransform();
    });

    $('.zoom-reset').on('click', function() {
        scale = 1;
        centerOrgChart();
    });

    if (wrapper) {
        wrapper.addEventListener('mousedown', (e) => {
            if (e.target.closest('.dept-card-v2') || e.target.closest('button') || e.target.closest('a')) return;
            isDragging = true;
            startX = e.clientX - translateX;
            startY = e.clientY - translateY;
        });

        window.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            translateX = e.clientX - startX;
            translateY = e.clientY - startY;
            updateTransform();
        });

        window.addEventListener('mouseup', () => {
            isDragging = false;
        });

        // Touch support
        wrapper.addEventListener('touchstart', (e) => {
            if (e.target.closest('.dept-card-v2') || e.target.closest('button') || e.target.closest('a')) return;
            if (e.touches.length === 1) {
                isDragging = true;
                startX = e.touches[0].clientX - translateX;
                startY = e.touches[0].clientY - translateY;
            }
        });

        wrapper.addEventListener('touchmove', (e) => {
            if (!isDragging || e.touches.length !== 1) return;
            translateX = e.touches[0].clientX - startX;
            translateY = e.touches[0].clientY - startY;
            updateTransform();
        });

        wrapper.addEventListener('touchend', () => {
            isDragging = false;
        });
    }

    $(document).ready(function() {
        if ($('#pills-org-chart-tab').hasClass('active')) {
            setTimeout(centerOrgChart, 500);
        }
    });

// Toast notification system for messenger alerts
// Define show_toastr function if not already defined
if (typeof show_toastr === 'undefined') {
    window.show_toastr = function(title, message, type) {
        // Try to use toastr library if available
        if (typeof toastr !== 'undefined') {
            toastr[type](message, title);
            return;
        }
        
        // Fallback: Use Bootstrap toast or alert
        if (typeof bootstrap !== 'undefined') {
            // Create a simple toast notification
            var toastHtml = '<div class="toast align-items-center text-white bg-' + (type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info') + ' border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">' +
                '<div class="d-flex">' +
                    '<div class="toast-body">' +
                        '<strong>' + title + ':</strong> ' + message +
                    '</div>' +
                    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
                '</div>' +
            '</div>';
            
            $('body').append(toastHtml);
            var toastElement = $('body').children().last()[0];
            var toast = new bootstrap.Toast(toastElement);
            toast.show();
            
            // Remove toast after it's hidden
            $(toastElement).on('hidden.bs.toast', function() {
                $(this).remove();
            });
        } else {
            // Final fallback: Use browser alert
            alert(title + ': ' + message);
        }
    };
}

// Start toast notifications for messenger alerts on users page
$(document).ready(function() {
    let notificationPolling = null;

    // Simple notification checker using toast
    function startToastNotifications() {
        // Clear existing polling
        if (notificationPolling) {
            clearInterval(notificationPolling);
        }

        // Start checking for new messages every 10 seconds
        notificationPolling = setInterval(function() {
            checkForToastNotifications();
        }, 10000);
    }

    // Check for new unread messages and show toast
    function checkForToastNotifications() {
        $.get('{{ route("messenger.latest.unread") }}', function(data) {
            if (data.unread_messages && data.unread_messages.length > 0) {
                // Show toast for the latest unread message
                const latestMessage = data.unread_messages[0];
                show_toastr(
                    'New Message from ' + latestMessage.from_name,
                    latestMessage.body.length > 30 ? latestMessage.body.substring(0, 30) + '...' : latestMessage.body,
                    'info'
                );
                
                console.log('🔔 Toast notification shown for message from:', latestMessage.from_name);
            }
        }).fail(function(xhr) {
            console.log('Toast notification check error:', xhr.status);
        });
    }

    // Start toast notifications
    startToastNotifications();
});

// Group creation functionality
function loadUsersForGroup() {
    $.get('{{ route("messenger.users") }}', function(response) {
        const users = response.users || response;
        let html = '';
        
        users.forEach(user => {
            html += `
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="group_members[]" value="${user.id}" id="user_${user.id}">
                    <label class="form-check-label" for="user_${user.id}">
                        <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=6366f1&color=fff&size=24" class="rounded-circle me-2" style="width: 24px; height: 24px;">
                        ${user.name}
                    </label>
                </div>
            `;
        });
        
        $('#groupMembersList').html(html);
    });
}

// Load users when modal opens
$('#createGroupModal').on('show.bs.modal', function() {
    loadUsersForGroup();
});

// Handle group creation form submission
$('#createGroupForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const members = [];
    $('input[name="group_members[]"]:checked').each(function() {
        members.push($(this).val());
    });
    
    if (members.length === 0) {
        show_toastr('Error', 'Please select at least one member', 'error');
        return;
    }
    
    formData.append('members', JSON.stringify(members));
    
    $.ajax({
        url: '{{ route("messenger.groups.create") }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                show_toastr('Success', 'Group created successfully', 'success');
                $('#createGroupModal').modal('hide');
                $('#createGroupForm')[0].reset();
            } else {
                show_toastr('Error', response.error || 'Failed to create group', 'error');
            }
        },
        error: function() {
            show_toastr('Error', 'Failed to create group', 'error');
        }
    });
});

</script>
@endpush
