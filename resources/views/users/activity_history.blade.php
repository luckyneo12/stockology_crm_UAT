@php
if(Auth::user()->type=='super admin')
{
$titles = __('Comprehensive User Activity History') ;
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

@push('styles')
<style>
/* Activity History Enhanced Styles */
.activity-filter-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
    border-radius: 20px !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    border: 1px solid #e9ecef !important;
    transition: all 0.3s ease !important;
}
.activity-filter-card:hover {
    transform: translateY(-5px) !important;
    box-shadow: 0 15px 40px rgba(0,0,0,0.15) !important;
}
.filter-section {
    background: rgba(255,255,255,0.9) !important;
    border-radius: 15px !important;
    padding: 1.5rem !important;
    margin-bottom: 1rem !important;
    border: 1px solid #e9ecef !important;
    backdrop-filter: blur(10px) !important;
}
.filter-label {
    font-weight: 600 !important;
    color: #495057 !important;
    margin-bottom: 0.5rem !important;
    display: flex !important;
    align-items: center !important;
}
.filter-label i {
    margin-right: 0.5rem !important;
    color: #007bff !important;
}
.form-control.select {
    border-radius: 10px !important;
    border: 2px solid #e9ecef !important;
    transition: all 0.3s ease !important;
    background: white !important;
}
.form-control.select:focus {
    border-color: #007bff !important;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.1) !important;
}
.form-control.text, .form-control.date {
    border-radius: 10px !important;
    border: 2px solid #e9ecef !important;
    transition: all 0.3s ease !important;
}
.form-control.text:focus, .form-control.date:focus {
    border-color: #007bff !important;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.1) !important;
}
.action-buttons {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    border-radius: 15px !important;
    padding: 1rem !important;
    box-shadow: 0 8px 25px rgba(0,123,255,0.3) !important;
}
.action-buttons .btn {
    border-radius: 10px !important;
    margin: 0.25rem !important;
    transition: all 0.3s ease !important;
}
.action-buttons .btn:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2) !important;
}
.quick-filters {
    display: flex !important;
    gap: 0.5rem !important;
    flex-wrap: wrap !important;
    margin-bottom: 1rem !important;
}
.quick-filter-btn {
    border-radius: 20px !important;
    font-size: 0.85rem !important;
    padding: 0.5rem 1rem !important;
    border: 2px solid #e9ecef !important;
    background: white !important;
    transition: all 0.3s ease !important;
}
.quick-filter-btn:hover {
    background: #007bff !important;
    color: white !important;
    border-color: #007bff !important;
    transform: translateY(-2px) !important;
}
.quick-filter-btn.active {
    background: #007bff !important;
    color: white !important;
    border-color: #007bff !important;
}
.stat-card-primary, .stat-card-success, .stat-card-info, .stat-card-warning {
    transition: all 0.3s ease !important;
    border-radius: 15px !important;
    overflow: hidden !important;
    border: 1px solid #e9ecef !important;
}
.stat-card-primary:hover, .stat-card-success:hover, .stat-card-info:hover, .stat-card-warning:hover {
    transform: translateY(-5px) !important;
    box-shadow: 0 15px 30px rgba(0,0,0,0.15) !important;
}
.avatar-lg {
    width: 80px !important;
    height: 80px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 50% !important;
}
.font-size-32 {
    font-size: 32px !important;
}
.counter {
    font-weight: 700 !important;
    color: #2c3e50 !important;
}
@media (max-width: 768px) {
    .filter-section {
        padding: 1rem !important;
        margin-bottom: 0.5rem !important;
    }
    .action-buttons {
        padding: 0.5rem !important;
    }
    .quick-filters {
        margin-bottom: 0.5rem !important;
    }
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="mt-2">
            <div class="card activity-filter-card">
                <div class="card-header bg-gradient bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="ti ti-filter me-2"></i>
                        {{ __('Advanced Activity Filters') }}
                    </h5>
                </div>
                <div class="card-body">
                    {{ Form::open(array('route' => array('users.activity.history'),'method'=>'get','id'=>'activity_filter_form')) }}
                    
                    <!-- Quick Filters -->
                    <div class="quick-filters">
                        <button type="button" class="btn quick-filter-btn" onclick="setQuickFilter('today')">
                            <i class="ti ti-calendar me-1"></i>{{ __('Today') }}
                        </button>
                        <button type="button" class="btn quick-filter-btn" onclick="setQuickFilter('yesterday')">
                            <i class="ti ti-calendar-off me-1"></i>{{ __('Yesterday') }}
                        </button>
                        <button type="button" class="btn quick-filter-btn" onclick="setQuickFilter('week')">
                            <i class="ti ti-calendar-event me-1"></i>{{ __('This Week') }}
                        </button>
                        <button type="button" class="btn quick-filter-btn" onclick="setQuickFilter('month')">
                            <i class="ti ti-calendar-month me-1"></i>{{ __('This Month') }}
                        </button>
                        <button type="button" class="btn quick-filter-btn" onclick="setQuickFilter('login')">
                            <i class="ti ti-login me-1"></i>{{ __('Login Activities') }}
                        </button>
                        <button type="button" class="btn quick-filter-btn" onclick="setQuickFilter('leads')">
                            <i class="ti ti-users me-1"></i>{{ __('Lead Activities') }}
                        </button>
                    </div>
                    
                    <div class="row">
                        <!-- User Filter -->
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="filter-section">
                                {{ Form::label('user_id', __('User'),['class'=>'filter-label']) }}
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-user"></i></span>
                                    {{ Form::select('user_id', $users, isset($_GET['user_id'])?$_GET['user_id']:'', array('class' => 'form-control select', 'placeholder' => __('All Users'))) }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Module Filter -->
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="filter-section">
                                {{ Form::label('module', __('Module'),['class'=>'filter-label']) }}
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-package"></i></span>
                                    {{ Form::select('module', $modules, isset($_GET['module'])?$_GET['module']:'', array('class' => 'form-control select', 'placeholder' => __('All Modules'))) }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Activity Type Filter -->
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="filter-section">
                                {{ Form::label('activity_type', __('Activity Type'),['class'=>'filter-label']) }}
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-activity"></i></span>
                                    {{ Form::select('activity_type', $activityTypes, isset($_GET['activity_type'])?$_GET['activity_type']:'', array('class' => 'form-control select', 'placeholder' => __('All Activities'))) }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Country Filter -->
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="filter-section">
                                {{ Form::label('country', __('Country'),['class'=>'filter-label']) }}
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-world"></i></span>
                                    {{ Form::select('country', $countries, isset($_GET['country'])?$_GET['country']:'', array('class' => 'form-control select', 'placeholder' => __('All Countries'))) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <!-- Date From -->
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="filter-section">
                                {{ Form::label('date_from', __('Date From'),['class'=>'filter-label']) }}
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                    {{ Form::date('date_from', isset($_GET['date_from'])?$_GET['date_from']:'', array('class' => 'form-control date')) }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Date To -->
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="filter-section">
                                {{ Form::label('date_to', __('Date To'),['class'=>'filter-label']) }}
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-calendar-off"></i></span>
                                    {{ Form::date('date_to', isset($_GET['date_to'])?$_GET['date_to']:'', array('class' => 'form-control date')) }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- IP Address -->
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="filter-section">
                                {{ Form::label('ip_address', __('IP Address'),['class'=>'filter-label']) }}
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-network"></i></span>
                                    {{ Form::text('ip_address', isset($_GET['ip_address'])?$_GET['ip_address']:'', array('class' => 'form-control text', 'placeholder' => __('Search by IP'))) }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Search -->
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="filter-section">
                                {{ Form::label('search', __('Search'),['class'=>'filter-label']) }}
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                                    {{ Form::text('search', isset($_GET['search'])?$_GET['search']:'', array('class' => 'form-control text', 'placeholder' => __('Search...'))) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="action-buttons d-flex justify-content-between align-items-center">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-search me-1"></i>{{ __('Apply Filters') }}
                                    </button>
                                    <a href="{{route('users.activity.history')}}" class="btn btn-outline-danger">
                                        <i class="ti ti-refresh me-1"></i>{{ __('Reset') }}
                                    </a>
                                    <button type="button" class="btn btn-outline-info" onclick="exportActivities()">
                                        <i class="ti ti-download me-1"></i>{{ __('Export') }}
                                    </button>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline-warning" onclick="toggleAdvancedFilters()">
                                        <i class="ti ti-settings me-1"></i>{{ __('Advanced') }}
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="saveFilters()">
                                        <i class="ti ti-bookmark me-1"></i>{{ __('Save Filters') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Statistics Cards with Charts -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="text-muted mb-1">{{__('Total Activities')}}</h5>
                        <h3 class="mb-0 counter">{{ $activities->total() }}</h3>
                        <div class="mt-2">
                            <small class="text-success">
                                <i class="ti ti-trending-up me-1"></i>
                                {{ __('Last 24 hours') }}: {{ $activities->where('created_at', '>=', now()->subDay())->count() }}
                            </small>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-success" style="width: {{ min(100, ($activities->where('created_at', '>=', now()->subDay())->count() / max(1, $activities->total())) * 100) }}%"></div>
                        </div>
                    </div>
                    <div class="avatar-lg bg-primary bg-gradient rounded">
                        <i class="ti ti-activity font-size-32 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="text-muted mb-1">{{__('Unique Users')}}</h5>
                        <h3 class="mb-0 counter">{{ $activities->pluck('user_id')->unique()->count() }}</h3>
                        <div class="mt-2">
                            <small class="text-info">
                                <i class="ti ti-user-check me-1"></i>
                                {{ __('Active Today') }}: {{ $activities->where('created_at', '>=', now()->subDay())->pluck('user_id')->unique()->count() }}
                            </small>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-info" style="width: {{ min(100, ($activities->where('created_at', '>=', now()->subDay())->pluck('user_id')->unique()->count() / max(1, $activities->pluck('user_id')->unique()->count())) * 100) }}%"></div>
                        </div>
                    </div>
                    <div class="avatar-lg bg-success bg-gradient rounded">
                        <i class="ti ti-users font-size-32 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="text-muted mb-1">{{__('Modules Used')}}</h5>
                        <h3 class="mb-0 counter">{{ $activities->pluck('module')->unique()->count() }}</h3>
                        <div class="mt-2">
                            <small class="text-warning">
                                <i class="ti ti-package me-1"></i>
                                {{ __('Most Used') }}: {{ $activities->groupBy('module')->map->count()->sortDesc()->keys()->first() ?? 'N/A' }}
                            </small>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-warning" style="width: 85%"></div>
                        </div>
                    </div>
                    <div class="avatar-lg bg-info bg-gradient rounded">
                        <i class="ti ti-package font-size-32 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="text-muted mb-1">{{__('Unique IPs')}}</h5>
                        <h3 class="mb-0 counter">{{ $activities->pluck('ip_address')->unique()->count() }}</h3>
                        <div class="mt-2">
                            <small class="text-danger">
                                <i class="ti ti-map-pin me-1"></i>
                                {{ __('Countries') }}: {{ $activities->pluck('country')->unique()->filter()->count() }}
                            </small>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-danger" style="width: {{ min(100, ($activities->pluck('country')->unique()->filter()->count() / max(1, $activities->pluck('ip_address')->unique()->count())) * 100) }}%"></div>
                        </div>
                    </div>
                    <div class="avatar-lg bg-warning bg-gradient rounded">
                        <i class="ti ti-world font-size-32 text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Timeline Chart -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-gradient bg-primary text-white">
                <h5 class="mb-0">
                    <i class="ti ti-chart-line me-2"></i>
                    {{ __('Activity Timeline') }}
                </h5>
            </div>
            <div class="card-body">
                <canvas id="activityChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-gradient bg-success text-white">
                <h5 class="mb-0">
                    <i class="ti ti-chart-pie me-2"></i>
                    {{ __('Activity Distribution') }}
                </h5>
            </div>
            <div class="card-body">
                <canvas id="activityPieChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Activity Table -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table mb-0 pc-dt-simple" id="activity_table">
                        <thead>
                            <tr>
                                @if(Auth::user()->type == 'super admin' || Auth::user()->type == 'company')
                                <th>{{ __('User') }}</th>
                                @endif
                                <th>{{ __('Date & Time') }}</th>
                                <th>{{ __('Activity') }}</th>
                                <th>{{ __('Module') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('IP Address') }}</th>
                                <th>{{ __('Location') }}</th>
                                <th>{{ __('Device') }}</th>
                                <th>{{ __('Response Time') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($activities as $activity)
                            <tr>
                                @if(Auth::user()->type == 'super admin' || Auth::user()->type == 'company')
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">{{ $activity->user->name ?? 'Unknown' }}</h6>
                                            <small class="text-muted">{{ $activity->user->email ?? 'Unknown' }}</small>
                                        </div>
                                    </div>
                                </td>
                                @endif
                                <td>
                                    <div>
                                        <p class="mb-0">{{ company_datetime_formate($activity->created_at) }}</p>
                                        <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ getActivityTypeColor($activity->activity_type) }} p-2">
                                        {{ ucfirst($activity->activity_type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark p-2">
                                        {{ ucfirst($activity->module) }}
                                    </span>
                                </td>
                                <td>
                                    <p class="mb-0 text-truncate" style="max-width: 200px;" title="{{ $activity->description }}">
                                        {{ Str::limit($activity->description, 50) }}
                                    </p>
                                </td>
                                <td>
                                    <code>{{ $activity->ip_address }}</code>
                                </td>
                                <td>
                                    <small>{{ $activity->location }}</small>
                                </td>
                                <td>
                                    <div>
                                        <small class="d-block">{{ $activity->device_type }}</small>
                                        <small class="text-muted">{{ $activity->browser }}</small>
                                    </div>
                                </td>
                                <td>
                                    <small>
                                        @if($activity->response_time_ms)
                                            {{ $activity->response_time_ms }}ms
                                        @else
                                            -
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <div class="action-btn me-2">
                                        <a href="#" class="mx-3 btn btn-sm align-items-center bg-info" data-size="lg" data-url="{{ route('users.activity.view', [$activity->id]) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="" data-title="{{ __('View Activity Details') }}" data-bs-original-title="{{ __('View') }}">
                                            <i class="ti ti-eye text-white"></i>
                                        </a>
                                    </div>
                                    @permission('user delete')
                                    <div class="action-btn">
                                        {{ Form::open(['route' => ['users.activity.destroy', $activity->id], 'class' => 'm-0']) }}
                                        @method('DELETE')
                                        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger" data-bs-toggle="tooltip" title="" data-bs-original-title="{{__('Delete')}}" aria-label="{{__('Delete')}}" data-confirm-yes="delete-form-{{ $activity->id }}"><i class="ti ti-trash text-white text-white"></i></a>
                                        {{ Form::close() }}
                                    </div>
                                    @endpermission
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <small class="text-muted">
                            Showing {{ $activities->firstItem() }} to {{ $activities->lastItem() }} of {{ $activities->total() }} entries
                        </small>
                    </div>
                    <div>
                        {!! $activities->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daily Activity Summary Modal -->
<div class="modal fade" id="dailyActivityModal" tabindex="-1" aria-labelledby="dailyActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dailyActivityModalLabel">{{__('Daily Activity Summary')}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="dailyActivityContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function getActivityTypeColor(type) {
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

// Initialize Charts
function initializeCharts() {
    // Activity Timeline Chart
    const ctx1 = document.getElementById('activityChart').getContext('2d');
    const activityChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Activities',
                data: [12, 19, 3, 5, 2, 3, 8],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Activity Distribution Pie Chart
    const ctx2 = document.getElementById('activityPieChart').getContext('2d');
    const activityPieChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Login', 'View', 'Create', 'Edit', 'Delete'],
            datasets: [{
                data: [30, 25, 20, 15, 10],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Quick Filter Functions
function setQuickFilter(type) {
    const form = document.getElementById('activity_filter_form');
    const today = new Date();
    
    // Reset all quick filter buttons
    document.querySelectorAll('.quick-filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Set active button
    event.target.classList.add('active');
    
    switch(type) {
        case 'today':
            form.querySelector('[name="date_from"]').value = formatDate(today);
            form.querySelector('[name="date_to"]').value = formatDate(today);
            break;
        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            form.querySelector('[name="date_from"]').value = formatDate(yesterday);
            form.querySelector('[name="date_to"]').value = formatDate(yesterday);
            break;
        case 'week':
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() - today.getDay());
            form.querySelector('[name="date_from"]').value = formatDate(weekStart);
            form.querySelector('[name="date_to"]').value = formatDate(today);
            break;
        case 'month':
            const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
            form.querySelector('[name="date_from"]').value = formatDate(monthStart);
            form.querySelector('[name="date_to"]').value = formatDate(today);
            break;
        case 'login':
            form.querySelector('[name="activity_type"]').value = 'login';
            break;
        case 'leads':
            form.querySelector('[name="module"]').value = 'leads';
            break;
    }
    
    form.submit();
}

function formatDate(date) {
    return date.toISOString().split('T')[0];
}

// Export Activities
function exportActivities() {
    const form = document.getElementById('activity_filter_form');
    const exportUrl = '{{ route("users.activity.export") }}?' + new URLSearchParams(new FormData(form)).toString();
    window.open(exportUrl, '_blank');
}

// Save Filters to LocalStorage
function saveFilters() {
    const form = document.getElementById('activity_filter_form');
    const formData = new FormData(form);
    const filters = {};
    
    for (let [key, value] of formData.entries()) {
        if (value) {
            filters[key] = value;
        }
    }
    
    localStorage.setItem('activity_filters', JSON.stringify(filters));
    showNotification('Filters saved successfully!', 'success');
}

// Show Notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function showDailySummary(userId, date) {
    $.get('{{ route("users.activity.summary") }}', {
        user_id: userId,
        date: date
    }, function(data) {
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <h6>User: ${data.user}</h6>
                    <p class="mb-1">Date: ${data.date}</p>
                    <p class="mb-1">Total Activities: ${data.total_activities}</p>
                    <p class="mb-1">Modules Worked: ${data.modules_worked}</p>
                    <p class="mb-1">First Activity: ${data.first_activity || 'N/A'}</p>
                    <p class="mb-1">Last Activity: ${data.last_activity || 'N/A'}</p>
                </div>
                <div class="col-md-6">
                    <h6>Hourly Breakdown:</h6>
                    <div class="activity-timeline">
        `;
        
        for (const [hour, activities] of Object.entries(data.hourly_activities)) {
            html += `
                <div class="mb-2">
                    <strong>${hour}</strong> - ${activities.length} activities
                </div>
            `;
        }
        
        html += `
                    </div>
                </div>
            </div>
        `;
        
        $('#dailyActivityContent').html(html);
        $('#dailyActivityModal').modal('show');
    }).fail(function() {
        $('#dailyActivityContent').html('<p class="text-danger">Error loading activity summary</p>');
        $('#dailyActivityModal').modal('show');
    });
}

// Counter Animation
function animateCounters() {
    const counters = document.querySelectorAll('.counter');
    
    counters.forEach(counter => {
        const target = parseInt(counter.textContent);
        const duration = 2000; // 2 seconds
        const increment = target / (duration / 16); // 60fps
        let current = 0;
        
        const updateCounter = () => {
            current += increment;
            if (current < target) {
                counter.textContent = Math.floor(current).toLocaleString();
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target.toLocaleString();
            }
        };
        
        updateCounter();
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    animateCounters();
    initializeCharts();
    
    // Add real-time search functionality
    const searchInput = document.querySelector('[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 3 || this.value.length === 0) {
                    document.getElementById('activity_filter_form').submit();
                }
            }, 500);
        });
    }
    
    // Add date validation
    const dateFrom = document.querySelector('[name="date_from"]');
    const dateTo = document.querySelector('[name="date_to"]');
    
    if (dateFrom && dateTo) {
        dateFrom.addEventListener('change', function() {
            if (dateTo.value && this.value > dateTo.value) {
                dateTo.value = this.value;
            }
        });
        
        dateTo.addEventListener('change', function() {
            if (dateFrom.value && this.value < dateFrom.value) {
                dateFrom.value = this.value;
            }
        });
    }
    
    // Add table row hover effects
    const tableRows = document.querySelectorAll('#activity_table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(0,123,255,0.05)';
            this.style.transform = 'scale(1.01)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
            this.style.transform = 'scale(1)';
        });
    });
});

// Auto-refresh every 30 seconds
setInterval(function() {
    if (!$('[data-bs-toggle="tooltip"]').length) {
        location.reload();
    }
}, 30000);
</script>
@push('styles')
<style>
.stat-card-primary, .stat-card-success, .stat-card-info, .stat-card-warning {
    transition: all 0.3s ease !important;
    border-radius: 15px !important;
    overflow: hidden !important;
    border: 1px solid #e9ecef !important;
}
.stat-card-primary:hover, .stat-card-success:hover, .stat-card-info:hover, .stat-card-warning:hover {
    transform: translateY(-5px) !important;
    box-shadow: 0 15px 30px rgba(0,0,0,0.15) !important;
}
.avatar-lg {
    width: 80px !important;
    height: 80px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 50% !important;
}
.font-size-32 {
    font-size: 32px !important;
}
.counter {
    font-weight: 700 !important;
    color: #2c3e50 !important;
}

/* Enhanced Table Styles */
#activity_table {
    border-radius: 10px !important;
    overflow: hidden !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08) !important;
}
#activity_table thead {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    color: white !important;
}
#activity_table thead th {
    border: none !important;
    padding: 1rem !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    font-size: 0.85rem !important;
    letter-spacing: 0.5px !important;
}
#activity_table tbody tr {
    transition: all 0.3s ease !important;
    border-bottom: 1px solid #f8f9fa !important;
}
#activity_table tbody tr:hover {
    background-color: rgba(0,123,255,0.05) !important;
    transform: scale(1.01) !important;
    box-shadow: 0 2px 8px rgba(0,123,255,0.1) !important;
}
#activity_table tbody td {
    padding: 0.75rem 1rem !important;
    vertical-align: middle !important;
}

/* Chart Container Styles */
.card-body canvas {
    max-height: 300px !important;
}

/* Progress Bar Enhancement */
.progress {
    background-color: #e9ecef !important;
    border-radius: 10px !important;
    overflow: hidden !important;
}
.progress-bar {
    transition: width 1.5s ease-in-out !important;
}

/* Badge Enhancement */
.badge {
    font-size: 0.75rem !important;
    padding: 0.5rem 0.75rem !important;
    border-radius: 20px !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}

/* Activity Type Badge Colors */
.bg-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
}
.bg-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
}
.bg-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
}
.bg-warning {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
}
.bg-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
}
.bg-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #545b62 100%) !important;
}

/* Enhanced Pagination */
.pagination .page-link {
    border-radius: 8px !important;
    margin: 0 2px !important;
    border: 1px solid #dee2e6 !important;
    transition: all 0.3s ease !important;
}
.pagination .page-link:hover {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    color: white !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 10px rgba(0,123,255,0.3) !important;
}
.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    border-color: #007bff !important;
}

/* Mobile Responsive Enhancements */
@media (max-width: 768px) {
    .stat-card-primary, .stat-card-success, .stat-card-info, .stat-card-warning {
        margin-bottom: 1rem !important;
    }
    
    #activity_table {
        font-size: 0.85rem !important;
    }
    
    #activity_table thead th {
        padding: 0.5rem !important;
        font-size: 0.75rem !important;
    }
    
    #activity_table tbody td {
        padding: 0.5rem !important;
    }
    
    .badge {
        font-size: 0.7rem !important;
        padding: 0.25rem 0.5rem !important;
    }
}
</style>
@endpush
@endpush

@php
function getActivityTypeColor($type) {
    $colors = [
        'login' => 'success',
        'logout' => 'warning',
        'create' => 'primary',
        'edit' => 'info',
        'delete' => 'danger',
        'view' => 'secondary'
    ];
    return $colors[$type] ?? 'secondary';
}
@endphp
