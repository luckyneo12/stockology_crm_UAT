@extends('layouts.main')
@section('page-title')
{{ __('Dashboard')}}
@endsection
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Welcome to Dashboard') }}</h5>
            </div>
            <div class="card-body">
                <p>{{ __('You are logged in to the CRM system. Use the sidebar to navigate to different sections.') }}</p>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('Users') }}</h5>
                                <p class="card-text">{{ __('Manage users and permissions') }}</p>
                                <a href="{{ route('users.index') }}" class="btn btn-primary">{{ __('Manage Users') }}</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('Roles') }}</h5>
                                <p class="card-text">{{ __('Manage user roles and permissions') }}</p>
                                <a href="{{ route('roles.index') }}" class="btn btn-primary">{{ __('Manage Roles') }}</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('Settings') }}</h5>
                                <p class="card-text">{{ __('Configure system settings') }}</p>
                                <a href="{{ route('settings.index') }}" class="btn btn-primary">{{ __('Settings') }}</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title">{{ __('Invoices') }}</h5>
                                <p class="card-text">{{ __('Manage invoices and billing') }}</p>
                                <a href="{{ route('invoice.index') }}" class="btn btn-primary">{{ __('Invoices') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
