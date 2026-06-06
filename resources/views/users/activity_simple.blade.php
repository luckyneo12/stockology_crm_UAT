@extends('layouts.main')
@section('page-title')
{{ __('User Activity') }}
@endsection
@section('page-breadcrumb')
{{ __('User Activity') }}
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('User Activity')}}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('User Activity Tracking') }}</h5>
                <div class="card-action">
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary">
                        <i class="ti ti-arrow-left me-1"></i> {{ __('Back to Users') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(isset($error))
                    <div class="alert alert-warning">
                        <h6>{{ __('Setup Required') }}</h6>
                        <p>{{ $error }}</p>
                        <hr>
                        <h6>{{ __('Quick Setup Steps:') }}</h6>
                        <ol>
                            <li>{{ __('Run SQL: CREATE TABLE user_activity_logs (...)') }}</li>
                            <li>{{ __('Clear application cache') }}</li>
                            <li>{{ __('Refresh this page') }}</li>
                        </ol>
                        <a href="/create_table.php" class="btn btn-primary" target="_blank">
                            {{ __('Create Table Now') }}
                        </a>
                    </div>
                @else
                    <div class="alert alert-info">
                        <h6>{{ __('User Activity Tracking System') }}</h6>
                        <p>{{ __('Comprehensive user activity monitoring with location tracking and advanced filtering.') }}</p>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h4>{{ $activities->count() }}</h4>
                                    <small>{{ __('Total Activities') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h4>{{ $activities->pluck('user_id')->unique()->count() }}</h4>
                                    <small>{{ __('Active Users') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h4>{{ $activities->pluck('module')->unique()->count() }}</h4>
                                    <small>{{ __('Modules Used') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h4>{{ $activities->pluck('ip_address')->unique()->count() }}</h4>
                                    <small>{{ __('Unique IPs') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activities -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Activity') }}</th>
                                    <th>{{ __('Module') }}</th>
                                    <th>{{ __('IP Address') }}</th>
                                    <th>{{ __('Time') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activities->take(10) as $activity)
                                    <tr>
                                        <td>{{ $activity->user->name ?? 'Unknown' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $activity->activity_type }}</span>
                                        </td>
                                        <td>{{ $activity->module }}</td>
                                        <td><code>{{ $activity->ip_address }}</code></td>
                                        <td>{{ $activity->created_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <p class="text-muted">{{ __('No activities recorded yet. The system will start tracking user activities automatically.') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($activities->count() > 0)
                        <div class="text-center mt-3">
                            <a href="/users/activity/history" class="btn btn-primary">
                                {{ __('View Full Activity Dashboard') }}
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Manual Test Section -->
<div class="row mt-3">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h6>{{ __('Manual Testing') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>{{ __('Test Links:') }}</h6>
                        <ul>
                            <li><a href="/test_activity.php" target="_blank">{{ __('System Test') }}</a></li>
                            <li><a href="/create_table.php" target="_blank">{{ __('Create Database Table') }}</a></li>
                            <li><a href="/debug_500_error.php" target="_blank">{{ __('Debug Issues') }}</a></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>{{ __('Quick Actions:') }}</h6>
                        <ul>
                            <li><a href="/users/logs/history" target="_blank">{{ __('Old Login Logs') }}</a></li>
                            <li><a href="/users/activity/export" target="_blank">{{ __('Export Activities') }}</a></li>
                            <li><a href="#" onclick="window.location.reload()">{{ __('Refresh Page') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-refresh every 30 seconds if no activities
setTimeout(function() {
    if (document.querySelector('td[colspan="5"]')) {
        window.location.reload();
    }
}, 30000);
</script>
@endpush
