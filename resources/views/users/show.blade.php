@extends('layouts.main')
@section('page-title')
{{ __('User Profile') }}
@endsection
@section('page-breadcrumb')
{{ __('Users') }}
@endsection
@section('content')
<div class="row">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    @if(!empty($user->avatar))
                        <img src="{{ get_file($user->avatar) }}" class="rounded-circle avatar-xl" alt="Profile">
                    @else
                        <div class="avatar-xl rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-3">{{ $user->email }}</p>
                <div class="d-flex justify-content-center gap-2">
                    <span class="badge bg-{{ $user->active ? 'success' : 'danger' }}">
                        {{ $user->active ? __('Active') : __('Inactive') }}
                    </span>
                    <span class="badge bg-info">
                        {{ ucfirst($user->type) }}
                    </span>
                </div>
                
                <div class="mt-4 text-start">
                    <h6 class="mb-3">{{ __('Information') }}</h6>
                    <div class="mb-2">
                        <strong>{{ __('Created:') }}</strong> {{ \Carbon\Carbon::parse($user->created_at)->format('d M Y') }}
                    </div>
                    @if(!empty($user->phone))
                    <div class="mb-2">
                        <strong>{{ __('Phone:') }}</strong> {{ $user->phone }}
                    </div>
                    @endif
                    @if(!empty($user->department))
                    <div class="mb-2">
                        <strong>{{ __('Department:') }}</strong> {{ $user->department }}
                    </div>
                    @endif
                </div>

                @if(auth()->user()->id != $user->id && auth()->user()->isAbleTo('user manage'))
                <div class="mt-3">
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-pencil me-1"></i>{{ __('Edit User') }}
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Recent Activity') }}</h5>
            </div>
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="ti ti-activity text-primary" style="font-size: 48px; opacity: 0.3;"></i>
                    <h6 class="mt-3 text-muted">{{ __('User activity will be shown here') }}</h6>
                    <p class="text-muted">{{ __('This section can be extended to show user logs, tasks, and other activities.') }}</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        @if(auth()->user()->isAbleTo('user manage'))
        <div class="card mt-3">
            <div class="card-header">
                <h5>{{ __('Quick Actions') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="{{ route('messenger.index') }}" class="btn btn-outline-primary w-100">
                            <i class="ti ti-message-circle me-2"></i>{{ __('Send Message') }}
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="mailto:{{ $user->email }}" class="btn btn-outline-success w-100">
                            <i class="ti ti-mail me-2"></i>{{ __('Send Email') }}
                        </a>
                    </div>
                    @if(module_is_active('Taskly'))
                    <div class="col-md-6 mb-3">
                        <button class="btn btn-outline-info w-100" onclick="alert('{{ __('Task assignment feature coming soon') }}')">
                            <i class="ti ti-checklist me-2"></i>{{ __('Assign Task') }}
                        </button>
                    </div>
                    @endif
                    <div class="col-md-6 mb-3">
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-outline-warning w-100">
                            <i class="ti ti-pencil me-2"></i>{{ __('Edit Profile') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
.avatar-xl {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border: 4px solid #f1f1f1;
}
</style>
@endsection
