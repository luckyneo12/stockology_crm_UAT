@php
if(Auth::user()->type=='super admin')
{
$titles = __('Advanced User Activity History') ;
}
else{
$titles = __('User Activity History') ;
}
@endphp
@extends('layouts.main')
@section('page-title')
{{ $titles }}
@endsection
@section('page-breadcrumb')
{{ $titles }}
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('User Activity History')}}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="mt-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="ti ti-activity me-2"></i>
                        {{ $titles }}
                    </h5>
                </div>
                <div class="card-body">
                    {{ Form::open(array('route' => array('users.activity.history'),'method'=>'get','id'=>'activity_filter_form')) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10">
                            <div class="row">
                                <!-- User Filter -->
                                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('user_id', __('User'),['class'=>'form-label']) }}
                                        {{ Form::select('user_id', $users, isset($_GET['user_id'])?$_GET['user_id']:'', array('class' => 'form-control select', 'placeholder' => __('All Users'))) }}
                                    </div>
                                </div>
                                
                                <!-- Module Filter -->
                                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('module', __('Module'),['class'=>'form-label']) }}
                                        {{ Form::select('module', $modules, isset($_GET['module'])?$_GET['module']:'', array('class' => 'form-control select', 'placeholder' => __('All Modules'))) }}
                                    </div>
                                </div>
                                
                                <!-- Activity Type Filter -->
                                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('activity_type', __('Activity Type'),['class'=>'form-label']) }}
                                        {{ Form::select('activity_type', ['' => __('All Types'), 'login' => __('Login'), 'logout' => __('Logout'), 'create' => __('Create'), 'edit' => __('Edit'), 'delete' => __('Delete'), 'view' => __('View')], isset($_GET['activity_type'])?$_GET['activity_type']:'', array('class' => 'form-control select', 'placeholder' => __('All Activities'))) }}
                                    </div>
                                </div>
                                
                                <!-- IP Address Filter -->
                                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('ip_address', __('IP Address'),['class'=>'form-label']) }}
                                        {{ Form::text('ip_address', isset($_GET['ip_address'])?$_GET['ip_address']:'', array('class' => 'form-control', 'placeholder' => __('Enter IP Address'))) }}
                                    </div>
                                </div>
                                
                                <!-- Country Filter -->
                                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('country', __('Country'),['class'=>'form-label']) }}
                                        {{ Form::text('country', isset($_GET['country'])?$_GET['country']:'', array('class' => 'form-control', 'placeholder' => __('Enter Country'))) }}
                                    </div>
                                </div>
                                
                                <!-- Date Range Filter -->
                                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('date_from', __('From Date'),['class'=>'form-label']) }}
                                        {{ Form::date('date_from', isset($_GET['date_from'])?$_GET['date_from']:'', array('class' => 'form-control')) }}
                                    </div>
                                </div>
                                
                                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('date_to', __('To Date'),['class'=>'form-label']) }}
                                        {{ Form::date('date_to', isset($_GET['date_to'])?$_GET['date_to']:'', array('class' => 'form-control')) }}
                                    </div>
                                </div>
                                
                                <!-- Search Filter -->
                                <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('search', __('Search'),['class'=>'form-label']) }}
                                        {{ Form::text('search', isset($_GET['search'])?$_GET['search']:'', array('class' => 'form-control', 'placeholder' => __('Search in description...'))) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2">
                            <div class="btn-box">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    {{ Form::submit(__('Apply Filters'), array('class' => 'btn btn-primary btn-sm')) }}
                                    <a href="{{ route('users.activity.history') }}" class="btn btn-secondary btn-sm">{{__('Reset')}}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                    
                    <!-- Statistics Cards -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $statistics['total_activities'] ?? 0 }}</h5>
                                    <p class="card-text">{{__('Total Activities')}}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $statistics['active_users'] ?? 0 }}</h5>
                                    <p class="card-text">{{__('Active Users')}}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $statistics['modules_used'] ?? 0 }}</h5>
                                    <p class="card-text">{{__('Modules Used')}}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $statistics['avg_response_time'] ?? 0 }}ms</h5>
                                    <p class="card-text">{{__('Avg Response Time')}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Export Options -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">{{__('Showing')}} {{ $activities->firstItem() ?? 0 }} {{__('to')}} {{ $activities->lastItem() ?? 0 }} {{__('of')}} {{ $activities->total() }} {{__('entries')}}</h6>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('users.activity.export') }}?{{ http_build_query(request()->query()) }}" class="btn btn-success btn-sm">
                                        <i class="ti ti-download me-1"></i>{{__('Export CSV')}}
                                    </a>
                                    <button type="button" class="btn btn-info btn-sm" onclick="refreshActivities()">
                                        <i class="ti ti-refresh me-1"></i>{{__('Refresh')}}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Activities Table -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>{{__('User')}}</th>
                                            <th>{{__('Activity Type')}}</th>
                                            <th>{{__('Module')}}</th>
                                            <th>{{__('Description')}}</th>
                                            <th>{{__('IP Address')}}</th>
                                            <th>{{__('Country')}}</th>
                                            <th>{{__('Device')}}</th>
                                            <th>{{__('Response Time')}}</th>
                                            <th>{{__('Date & Time')}}</th>
                                            <th>{{__('Actions')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($activities as $activity)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-2">
                                                            <img src="{{ asset('storage/uploads/logo/' . $activity->user->avatar ?? 'default.png') }}" alt="{{ $activity->user->name }}" class="rounded-circle">
                                                        </div>
                                                        <div>
                                                            <strong>{{ $activity->user->name }}</strong><br>
                                                            <small class="text-muted">{{ $activity->user->email }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ getActivityTypeColor($activity->activity_type) }}">
                                                        {{ ucfirst($activity->activity_type) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        {{ ucfirst($activity->module) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <small>{{ $activity->description ?? 'No description' }}</small>
                                                </td>
                                                <td>
                                                    <code>{{ $activity->ip_address }}</code>
                                                </td>
                                                <td>
                                                    <small>{{ $activity->country ?? 'N/A' }}</small>
                                                </td>
                                                <td>
                                                    <small>{{ $activity->device_type ?? 'Desktop' }}</small>
                                                </td>
                                                <td>
                                                    @if($activity->response_time_ms)
                                                        <span class="badge bg-{{ $activity->response_time_ms > 1000 ? 'warning' : 'success' }}">
                                                            {{ $activity->response_time_ms }}ms
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small>{{ \Carbon\Carbon::parse($activity->created_at)->format('M j, Y H:i:s') }}</small><br>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('users.activity.view', $activity->id) }}" class="btn btn-outline-primary btn-sm" data-bs-toggle="tooltip" title="{{__('View Details')}}">
                                                            <i class="ti ti-eye"></i>
                                                        </a>
                                                        @if(Auth::user()->type == 'super admin')
                                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteActivity({{ $activity->id }})" data-bs-toggle="tooltip" title="{{__('Delete')}}">
                                                                <i class="ti ti-trash"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center py-4">
                                                    <i class="ti ti-activity font-size-48 text-muted"></i>
                                                    <h5 class="text-muted mt-3">{{__('No Activities Found')}}</h5>
                                                    <p class="text-muted">{{__('No user activities match your current filters.')}}</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-center">
                                {{ $activities->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function getActivityColor(type) {
    const colors = {
        'login': 'success',
        'logout': 'warning',
        'create': 'primary',
        'edit': 'info',
        'delete': 'danger',
        'view': 'secondary'
    };
    return colors[type] || 'secondary';
}

function refreshActivities() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="ti ti-loader-2 me-1"></i>{{__("Refreshing...")}}';
    button.disabled = true;
    
    setTimeout(() => {
        location.reload();
    }, 1000);
}

function deleteActivity(id) {
    if(confirm('{{__("Are you sure you want to delete this activity?")}}')) {
        fetch(`/users/activity/delete/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert(data.message || '{{__("Error deleting activity")}}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{__("Error deleting activity")}}');
        });
    }
}

// Auto-refresh every 30 seconds
setInterval(() => {
    console.log('Auto-refreshing activities...');
}, 30000);

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>
@endpush
