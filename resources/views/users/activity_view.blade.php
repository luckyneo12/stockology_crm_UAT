@extends('layouts.main')
@section('page-title')
{{ __('Activity Details') }}
@endsection
@section('page-breadcrumb')
{{ __('Activity Details') }}
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item"><a href="{{route('users.activity.history')}}">{{__('Activity History')}}</a></li>
<li class="breadcrumb-item">{{__('Activity Details')}}</li>
@endsection

@push('styles')
<style>
.activity-detail-card {
    border-left: 4px solid #007bff;
    transition: all 0.3s ease;
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .activity-detail-card {
        margin-bottom: 0.5rem;
        border-radius: 10px;
    }
    
    .user-avatar-large {
        width: 60px;
        height: 60px;
        font-size: 20px;
    }
    
    .activity-badge {
        font-size: 0.75rem;
        padding: 0.4rem 0.8rem;
    }
    
    .info-section {
        padding: 1rem;
        margin-bottom: 0.5rem;
    }
}
.activity-detail-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}
.user-avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: bold;
    color: white;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    transition: all 0.3s ease;
}
.user-avatar-large:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 25px rgba(102, 126, 234, 0.4);
}
.activity-badge {
    font-size: 0.85rem;
    padding: 0.6rem 1.2rem;
    border-radius: 25px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}
.activity-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
}
.device-info {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 1.5rem;
    border: 1px solid #dee2e6;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}
.location-info {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 8px 20px rgba(240, 147, 251, 0.3);
}
.response-time-badge {
    font-size: 0.9rem;
    font-weight: 700;
    padding: 0.8rem 1.2rem;
    border-radius: 20px;
    animation: pulse 2s infinite;
}
.url-link {
    word-break: break-all;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1rem;
    border-radius: 10px;
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
}
.url-link:hover {
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    transform: translateY(-2px);
}
.pre-scrollable {
    background: #1e1e1e;
    color: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    font-family: 'Courier New', monospace;
    border: 1px solid #444;
}
.info-section {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    border: 1px solid #e9ecef;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
}
.info-section:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
    100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
}
.lead-assignment-card {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border-radius: 15px;
    padding: 1.5rem;
    margin-top: 1rem;
    box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
}
.timeline-item {
    position: relative;
    padding-left: 2rem;
    border-left: 3px solid #007bff;
}
.timeline-item::before {
    content: '';
    position: absolute;
    left: -8px;
    top: 0;
    width: 13px;
    height: 13px;
    border-radius: 50%;
    background: #007bff;
    border: 3px solid white;
}
</style>
@endpush

@section('content')
<div id="print-content">
<div class="row">
    <div class="col-md-8">
        <div class="card activity-detail-card">
            <div class="card-header bg-primary bg-gradient text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Activity Details') }}</h5>
                    <span class="badge bg-light text-dark activity-badge">
                        <i class="ti ti-clock me-1"></i>
                        {{ $activity->created_at }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __('User Information') }}</label>
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="user-avatar-large me-3">
                                    {{ strtoupper(substr($activity->user_name ?? 'U', 0, 1)) }}
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1 text-primary">{{ $activity->user_name ?? 'Unknown User' }}</h5>
                                    <p class="mb-0 text-muted">
                                        <i class="ti ti-mail me-1"></i>{{ $activity->user_email ?? 'Unknown Email' }}
                                    </p>
                                    @if($activity->module == 'leads')
                                        <div class="mt-2">
                                            <span class="badge bg-success activity-badge">
                                                <i class="ti ti-users me-1"></i>
                                                {{ __('Lead Management') }}
                                            </span>
                                        </div>
                                        
                                        <!-- Lead Assignment Details -->
                                        <div class="lead-assignment-card mt-3">
                                            <h6 class="text-white mb-3">
                                                <i class="ti ti-user-check me-2"></i>
                                                {{ __('Lead Assignment Status') }}
                                            </h6>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="ti ti-check-circle text-success me-2"></i>
                                                        <div>
                                                            <strong>{{ __('Access Granted') }}:</strong> 
                                                            <span class="text-white-50">{{ __('User has access to lead management system') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="ti ti-shield-check text-info me-2"></i>
                                                        <div>
                                                            <strong>{{ __('Permission Level') }}:</strong> 
                                                            <span class="text-white-50">{{ __('Can view and manage assigned leads') }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <i class="ti ti-clock text-warning me-2"></i>
                                                        <div>
                                                            <strong>{{ __('Last Activity') }}:</strong> 
                                                            <span class="text-white-50">{{ __('Recent lead access detected') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">{{ __('Activity Details') }}</label>
                            <div class="d-flex gap-2 mb-2">
                                <span class="badge bg-{{ getActivityTypeColor($activity->activity_type) }} activity-badge">
                                    <i class="ti ti-activity me-1"></i>
                                    {{ ucfirst($activity->activity_type) }}
                                </span>
                                <span class="badge bg-info activity-badge">
                                    <i class="ti ti-package me-1"></i>
                                    {{ ucfirst($activity->module) }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">{{ __('Date & Time') }}</label>
                            <div class="d-flex align-items-center">
                                <i class="ti ti-calendar text-primary me-2"></i>
                                <div>
                                    <strong>{{ $activity->created_at }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $activity->created_at }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Network Information') }}</label>
                            <div class="device-info">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="ti ti-world text-primary me-2"></i>
                                    <div>
                                        <strong>{{ __('IP Address') }}:</strong> 
                                        <code class="bg-dark text-white px-2 py-1 rounded">{{ $activity->ip_address }}</code>
                                    </div>
                                </div>
                                @if($activity->city || $activity->country)
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-map-pin text-danger me-2"></i>
                                    <div>
                                        <strong>{{ __('Location') }}:</strong> 
                                        {{ $activity->city }}, {{ $activity->country }}
                                        @if($activity->latitude && $activity->longitude)
                                            <br>
                                            <small class="text-muted">
                                                <i class="ti ti-compass me-1"></i>
                                                {{ $activity->latitude }}, {{ $activity->longitude }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                                @else
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-map-pin-off text-muted me-2"></i>
                                    <div>
                                        <strong>{{ __('Location') }}:</strong> 
                                        <span class="text-muted">{{ __('Unknown') }}</span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">{{ __('Device & Browser') }}</label>
                            <div class="device-info">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="ti ti-device-laptop text-success me-2"></i>
                                    <div>
                                        <strong>{{ __('Device Type') }}:</strong> 
                                        <span class="badge bg-light text-dark">{{ ucfirst($activity->device_type) }}</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="ti ti-brand-chrome text-warning me-2"></i>
                                    <div>
                                        <strong>{{ __('Browser') }}:</strong> 
                                        {{ $activity->browser }} {{ $activity->browser_version }}
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-brand-windows text-info me-2"></i>
                                    <div>
                                        <strong>{{ __('Operating System') }}:</strong> 
                                        {{ $activity->os }} {{ $activity->os_version }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">{{ __('Performance') }}</label>
                            <div class="d-flex align-items-center">
                                <i class="ti ti-gauge text-primary me-2"></i>
                                <div>
                                    @if($activity->response_time_ms)
                                        <span class="badge bg-{{ $activity->response_time_ms > 1000 ? 'warning' : 'success' }} response-time-badge">
                                            <i class="ti ti-clock me-1"></i>
                                            {{ $activity->response_time_ms }}ms
                                        </span>
                                        @if($activity->response_time_ms > 1000)
                                            <small class="text-warning ms-2">
                                                <i class="ti ti-alert-triangle me-1"></i>
                                                {{ __('Slow Response') }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary response-time-badge">
                                            <i class="ti ti-clock-off me-1"></i>
                                            {{ __('N/A') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">{{ __('Description') }}</label>
                    <p class="form-control-static">
                        {{ $activity->description }}
                    </p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">{{ __('Accessed URL') }}</label>
                    <div class="url-link">
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti ti-link text-primary me-2"></i>
                            <strong>{{ __('URL') }}:</strong>
                        </div>
                        <a href="{{ $activity->url }}" target="_blank" class="d-block p-2 text-decoration-none">
                            <i class="ti ti-external-link me-1"></i>
                            {{ $activity->url }}
                        </a>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">{{ __('HTTP Method') }}</label>
                    <div class="d-flex align-items-center">
                        <i class="ti ti-api text-info me-2"></i>
                        <span class="badge bg-{{ getHttpMethodColor($activity->method) }} activity-badge">
                            {{ $activity->method }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('users.activity.history') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-arrow-left me-1"></i> {{ __('Back') }}
                        </a>
                        
                        @permission('user delete')
                        {!! Form::open(['route' => ['users.activity.destroy', $activity->id], 'class' => 'd-inline']) !!}
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm show_confirm">
                            <i class="ti ti-trash me-1"></i> {{ __('Delete') }}
                        </button>
                        {!! Form::close() !!}
                        @endpermission
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if($activity->module == 'leads')
                            <a href="{{ route('leads.index') }}" class="btn btn-primary btn-sm">
                                <i class="ti ti-users me-1"></i> {{ __('All Leads') }}
                            </a>
                        @endif
                        <button onclick="printActivity()" class="btn btn-info btn-sm">
                            <i class="ti ti-printer me-1"></i> {{ __('Print') }}
                        </button>
                        <button onclick="window.location.reload()" class="btn btn-outline-warning btn-sm">
                            <i class="ti ti-refresh me-1"></i> {{ __('Refresh') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Activity Timeline -->
        <div class="card mb-3">
            <div class="card-header bg-gradient bg-primary text-white">
                <h6 class="mb-0">
                    <i class="ti ti-timeline me-2"></i>
                    {{ __('Activity Timeline') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="timeline-item mb-3">
                    <div class="info-section">
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti ti-calendar text-primary me-2"></i>
                            <div>
                                <strong>{{ __('Activity Time') }}</strong>
                                <div class="text-muted small">{{ $activity->created_at }}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti ti-user text-success me-2"></i>
                            <div>
                                <strong>{{ __('Performed By') }}</strong>
                                <div>{{ $activity->user_name }}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti ti-activity text-info me-2"></i>
                            <div>
                                <strong>{{ __('Action Type') }}</strong>
                                <div>{{ ucfirst($activity->activity_type) }} on {{ ucfirst($activity->module) }}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="ti ti-world text-warning me-2"></i>
                            <div>
                                <strong>{{ __('From Location') }}</strong>
                                <div>{{ $activity->ip_address }} ({{ $activity->city ?? 'Unknown' }})</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Request Details -->
        @if($activity->request_data)
        <div class="card mb-3">
            <div class="card-header bg-gradient bg-secondary text-white">
                <h6 class="mb-0">
                    <i class="ti ti-upload me-2"></i>
                    {{ __('Request Details') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="info-section">
                    <div class="d-flex align-items-center mb-2">
                        <i class="ti ti-database text-primary me-2"></i>
                        <div>
                            <strong>{{ __('Data Size') }}</strong>
                            <div>{{ strlen(json_encode($activity->request_data)) }} bytes</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="ti ti-file-text text-info me-2"></i>
                        <div>
                            <strong>{{ __('Request Data') }}</strong>
                            <div class="mt-2">
                                <pre class="pre-scrollable" style="max-height: 200px; font-size: 11px;">{{ json_encode($activity->request_data, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Response Details -->
        @if($activity->response_data)
        <div class="card mb-3">
            <div class="card-header bg-gradient bg-success text-white">
                <h6 class="mb-0">
                    <i class="ti ti-download me-2"></i>
                    {{ __('Response Details') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="info-section">
                    <div class="d-flex align-items-center mb-2">
                        <i class="ti ti-speedometer text-warning me-2"></i>
                        <div>
                            <strong>{{ __('Response Status') }}</strong>
                            <div>
                                <span class="badge bg-{{ ($activity->response_data['status_code'] ?? 200) == 200 ? 'success' : 'danger' }}">
                                    {{ $activity->response_data['status_code'] ?? 200 }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="ti ti-file-description text-info me-2"></i>
                        <div>
                            <strong>{{ __('Response Size') }}</strong>
                            <div>{{ $activity->response_data['size'] ?? 'N/A' }} bytes</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="ti ti-code text-primary me-2"></i>
                        <div>
                            <strong>{{ __('Full Response') }}</strong>
                            <div class="mt-2">
                                <pre class="pre-scrollable" style="max-height: 200px; font-size: 11px;">{{ json_encode($activity->response_data, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- User Agent -->
        @if($activity->user_agent)
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">{{ __('User Agent') }}</h6>
            </div>
            <div class="card-body">
                <p class="form-control-static text-break" style="font-size: 12px;">
                    {{ $activity->user_agent }}
                </p>
            </div>
        </div>
        @endif
        
        <!-- Session Information -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">{{ __('Session Information') }}</h6>
            </div>
            <div class="card-body">
                <p class="form-control-static">
                    <strong>{{ __('Session ID') }}:</strong> <code>{{ $activity->session_id }}</code><br>
                    <strong>{{ __('Workspace') }}:</strong> {{ $activity->workspace }}<br>
                    <strong>{{ __('Created By') }}:</strong> {{ $activity->created_by }}
                </p>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="card">
            <div class="card-body">
                <a href="{{ route('users.activity.history') }}" class="btn btn-secondary btn-sm me-2">
                    <i class="ti ti-arrow-left me-1"></i> {{ __('Back to List') }}
                </a>
                
                @permission('user delete')
                {!! Form::open(['route' => ['users.activity.destroy', $activity->id], 'class' => 'd-inline']) !!}
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm show_confirm">
                    <i class="ti ti-trash me-1"></i> {{ __('Delete') }}
                </button>
                {!! Form::close() !!}
                @endpermission
            </div>
        </div>
    </div>
</div>

<!-- Location Map (if coordinates are available) -->
@if($activity->latitude && $activity->longitude)
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">{{ __('Location Map') }}</h6>
            </div>
            <div class="card-body">
                <div id="activity-map" style="height: 400px; width: 100%;"></div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
@if($activity->latitude && $activity->longitude)
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the map
    var map = L.map('activity-map').setView([{{ $activity->latitude }}, {{ $activity->longitude }}], 13);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Add a marker for the activity location
    var marker = L.marker([{{ $activity->latitude }}, {{ $activity->longitude }}]).addTo(map);
    
    // Add popup with location information
    marker.bindPopup('<b>{{ $activity->user_name ?? 'Unknown User' }}</b><br>{{ $activity->city ?? 'Unknown' }}, {{ $activity->country ?? 'Unknown' }}<br>{{ $activity->created_at }}').openPopup();
});
</script>
@endif

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

function getHttpMethodColor(method) {
    const colors = {
        'GET': 'info',
        'POST': 'success',
        'PUT': 'warning',
        'PATCH': 'warning',
        'DELETE': 'danger'
    };
    return colors[method] || 'secondary';
}

// Interactive features
document.addEventListener('DOMContentLoaded', function() {
    // Add copy to clipboard functionality
    const copyButtons = document.querySelectorAll('.copy-btn');
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const textToCopy = this.getAttribute('data-copy');
            navigator.clipboard.writeText(textToCopy).then(() => {
                // Show success message
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="ti ti-check me-1"></i> Copied!';
                this.classList.add('btn-success');
                this.classList.remove('btn-outline-secondary');
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.classList.remove('btn-success');
                    this.classList.add('btn-outline-secondary');
                }, 2000);
            });
        });
    });
    
    // Add expand/collapse functionality for JSON sections
    const expandButtons = document.querySelectorAll('.expand-json');
    expandButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                const isExpanded = targetElement.style.maxHeight !== '50px';
                targetElement.style.maxHeight = isExpanded ? '50px' : '500px';
                this.innerHTML = isExpanded ? 
                    '<i class="ti ti-chevron-down me-1"></i> Expand' : 
                    '<i class="ti ti-chevron-up me-1"></i> Collapse';
            }
        });
    });
    
    // Add hover effects for cards
    const cards = document.querySelectorAll('.info-section');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Add smooth scroll behavior
    const scrollableElements = document.querySelectorAll('.pre-scrollable');
    scrollableElements.forEach(element => {
        element.scrollTop = 0;
    });
});

// Print functionality with better formatting
function printActivity() {
    const printContent = document.getElementById('print-content').innerHTML;
    const originalContent = document.body.innerHTML;
    
    document.body.innerHTML = `
        <div style="padding: 20px; font-family: Arial, sans-serif;">
            <h1>Activity Details Report</h1>
            <hr>
            ${printContent}
        </div>
    `;
    
    window.print();
    document.body.innerHTML = originalContent;
}
</script>
@php
function getHttpMethodColor($method) {
    $colors = [
        'GET' => 'info',
        'POST' => 'success',
        'PUT' => 'warning',
        'PATCH' => 'warning',
        'DELETE' => 'danger'
    ];
    return $colors[$method] ?? 'secondary';
}
@endphp
@endpush
