@extends('layouts.main')
@section('page-title')
{{ __('Profile')}}
@endsection
@section('page-breadcrumb')
{{ __('Profile') }}
@endsection
@push('scripts')
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300
        })
    </script>
@endpush
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-xl-3">
                <div class="card sticky-top" style="top:30px">
                    <div class="list-group list-group-flush" id="useradd-sidenav">
                        <a href="#useradd-1"
                            class="list-group-item list-group-item-action border-0">{{ __('Personal Info') }} <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                        <a href="#useradd-2"
                            class="list-group-item list-group-item-action border-0">{{ __('Change Password') }} <div
                                class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                        @stack('profile_setting_sidebar')
                        @stack('jobsearch_setting_sidebar')
                    </div>
                </div>
            </div>
            <div class="col-xl-9">
                <div class="card" id="useradd-1">
                    <div class="card-header">
                        <h5>{{ __('Personal Info') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center">
                                    @if(!empty(Auth::user()->avatar))
                                        <img src="{{ get_file(Auth::user()->avatar) }}" class="rounded-circle avatar-xl" alt="profile">
                                    @else
                                        <div class="avatar-xl rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">
                                            {{ substr(Auth::user()->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="mt-3">
                                        <h4>{{ Auth::user()->name }}</h4>
                                        <p class="text-muted">{{ Auth::user()->email }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <td class="font-weight-bold">{{ __('Name') }}</td>
                                                <td>{{ Auth::user()->name }}</td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold">{{ __('Email') }}</td>
                                                <td>{{ Auth::user()->email }}</td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold">{{ __('Type') }}</td>
                                                <td>{{ ucfirst(Auth::user()->type) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold">{{ __('Created') }}</td>
                                                <td>{{ \Carbon\Carbon::parse(Auth::user()->created_at)->format('d M Y') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card" id="useradd-2">
                    <div class="card-header">
                        <h5>{{ __('Change Password') }}</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('update.password') }}">
                            @csrf
                            <div class="form-group">
                                <label for="current_password">{{ __('Current Password') }}</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                @error('current_password')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="new_password">{{ __('New Password') }}</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                @error('new_password')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="new_password_confirmation">{{ __('Confirm New Password') }}</label>
                                <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                                @error('new_password_confirmation')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Update Password') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
