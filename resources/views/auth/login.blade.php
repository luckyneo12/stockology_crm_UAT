@extends('layouts.auth')
@section('page-title')
    {{ __('Login') }}
@endsection
@section('language-bar')
    <div class="lang-dropdown-only-desk">
        <li class="dropdown dash-h-item drp-language">
            <a class="dash-head-link dropdown-toggle btn" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> {{ Str::upper($lang) }}
                </span>
            </a>
            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                @foreach (languages() as $key => $language)
                    <a href="{{ route('login', $key) }}"
                        class="dropdown-item @if ($lang == $key) text-primary @endif">
                        <span>{{ Str::ucfirst($language) }}</span>
                    </a>
                @endforeach
            </div>
        </li>
    </div>
@endsection
@php
    $admin_settings = getAdminAllSetting();
@endphp

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="">
                <h2 class="mb-3 f-w-600">{{ __('Login') }}</h2>
            </div>
            <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate="" id="form_data">
                @csrf
                <div>
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Email') }}</label>
                        <input id="email" type="email" class="form-control  @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" placeholder="{{ __('E-Mail Address') }}" required
                            autofocus>
                        @error('email')
                            <span class="error invalid-email text-danger" role="alert">
                                <small>{{ $message }}</small>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">{{ __('Password') }}</label>
                        <input id="password" type="password" class="form-control  @error('password') is-invalid @enderror"
                            name="password" placeholder="{{ __('Password') }}" required>
                        @error('password')
                            <span class="error invalid-password text-danger" role="alert">
                                <small>{{ $message }}</small>
                            </span>
                        @enderror
                        @if (Route::has('password.request'))
                            <div class="mt-2">
                                <a href="{{ route('password.request', $lang) }}"
                                    class="small text-primary text-underline--dashed border-primar">{{ __('Forgot Your Password?') }}</a>
                            </div>
                        @endif
                    </div>
                    @stack('recaptcha_field')

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-block mt-2 login_button"
                            tabindex="4">{{ __('Login') }}</button>

                        @stack('SigninButton')
                    </div>
                    @if (empty($admin_settings['signup']) || (isset($admin_settings['signup']) ? $admin_settings['signup'] : 'off') == 'on')
                        <p class="my-3 text-center">{{ __("Don't have an account?") }}
                            <a href="{{ route('register', $lang) }}" class="my-4 text-primary">{{ __('Register') }}</a>
                        </p>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection
@push('custom-scripts')
<style>
    /* ===== Login Card Fade-in on page load ===== */
    .custom-login .card {
        animation: loginFadeUp 0.5s ease both;
    }
    @keyframes loginFadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ===== Full-screen loading overlay ===== */
    #login-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(255,255,255,0.75);
        backdrop-filter: blur(4px);
        z-index: 9999;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 14px;
    }
    #login-overlay .login-spinner {
        width: 52px;
        height: 52px;
        border: 5px solid #e0e0e0;
        border-top-color: var(--bs-primary, #405189);
        border-radius: 50%;
        animation: spin 0.75s linear infinite;
    }
    #login-overlay p {
        font-size: 15px;
        font-weight: 600;
        color: var(--bs-primary, #405189);
        margin: 0;
        letter-spacing: 0.3px;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* ===== Button spinner ===== */
    .btn-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2.5px solid rgba(255,255,255,0.5);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
        vertical-align: middle;
        margin-right: 6px;
    }
</style>
@endpush

@push('script')
    {{-- Login Loading Overlay HTML --}}
    <div id="login-overlay">
        <div class="login-spinner"></div>
        <p>{{ __('Logging in, please wait...') }}</p>
    </div>

    <script>
        $(document).ready(function () {
            $("#form_data").on("submit", function (e) {
                // Validate: agar fields empty hain to loader mat dkhao
                var email    = $("#email").val().trim();
                var password = $("#password").val().trim();
                if (!email || !password) return;

                // Button mein spinner + text change
                $(".login_button")
                    .prop("disabled", true)
                    .html('<span class="btn-spinner"></span> {{ __("Logging in...") }}');

                // Full-screen overlay show karo
                $("#login-overlay").css("display", "flex");

                // Safety: 15 sec baad unlock (agar server slow ho)
                setTimeout(function () {
                    $(".login_button")
                        .prop("disabled", false)
                        .html('{{ __("Login") }}');
                    $("#login-overlay").hide();
                }, 15000);
            });

            // Agar validation error aaya (page reload) to overlay hide karo
            if ($(".is-invalid").length > 0) {
                $("#login-overlay").hide();
                $(".login_button")
                    .prop("disabled", false)
                    .html('{{ __("Login") }}');
            }
        });
    </script>
@endpush
