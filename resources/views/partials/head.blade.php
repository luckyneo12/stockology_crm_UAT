@php
    $favicon = isset($company_settings['favicon']) ? $company_settings['favicon'] : (isset($admin_settings['favicon']) ? $admin_settings['favicon'] : 'uploads/logo/favicon.png');
@endphp

<head>

    <title>@yield('page-title') |
        {{ !empty($company_settings['title_text']) ? $company_settings['title_text'] : (!empty($admin_settings['title_text']) ? $admin_settings['title_text'] : 'Stockology') }}
    </title>

    <meta name="title"
        content="{{ !empty($admin_settings['meta_title']) ? $admin_settings['meta_title'] : 'Stockology CRM' }}">
    <meta name="keywords"
        content="{{ !empty($admin_settings['meta_keywords']) ? $admin_settings['meta_keywords'] : 'Stockology, CRM, Securities, Trading, Finance' }}">
    <meta name="description"
        content="{{ !empty($admin_settings['meta_description']) ? $admin_settings['meta_description'] : 'Empower your financial growth with Stockology - The most efficient CRM for securities.'}}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:title"
        content="{{ !empty($admin_settings['meta_title']) ? $admin_settings['meta_title'] : 'Stockology CRM' }}">
    <meta property="og:description"
        content="{{ !empty($admin_settings['meta_description']) ? $admin_settings['meta_description'] : 'Empower your financial growth with Stockology - The most efficient CRM for securities.'}} ">
    <meta property="og:image"
        content="{{ get_file((!empty($admin_settings['meta_image'])) ? (check_file($admin_settings['meta_image'])) ? $admin_settings['meta_image'] : 'uploads/meta/meta_image.png' : 'uploads/meta/meta_image.png') }}{{'?' . time() }}">

    <!-- Twitter -->
    <meta name="user-id"
        content="{{ \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::user()->id : '' }}">
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ env('APP_URL') }}">
    <meta property="twitter:title"
        content="{{ !empty($admin_settings['meta_title']) ? $admin_settings['meta_title'] : 'Stockology CRM' }}">
    <meta property="twitter:description"
        content="{{ !empty($admin_settings['meta_description']) ? $admin_settings['meta_description'] : 'Empower your financial growth with Stockology - The most efficient CRM for securities.'}} ">
    <meta property="twitter:image"
        content="{{ get_file((!empty($admin_settings['meta_image'])) ? (check_file($admin_settings['meta_image'])) ? $admin_settings['meta_image'] : 'uploads/meta/meta_image.png' : 'uploads/meta/meta_image.png') }}{{'?' . time() }}">

    <meta name="author" content="Stockology">

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    {{--
    <meta name="url" content="{{ url('').'/'.config('chatify.routes.prefix') }}" data-user="{{ Auth::user()->id }}">
    --}}
    {{-- Chatify disabled to prevent conflicts with custom messenger --}}

    {{--
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" /> --}}

    <!-- Favicon icon -->
    <link rel="icon"
        href="{{ check_file($favicon) ? get_file($favicon) : get_file('uploads/logo/favicon.png')  }}{{'?' . time()}}"
        type="image/x-icon" />

    <!-- Font Preloading for Performance -->
    <link rel="preload" href="{{ asset('assets/fonts/tabler-icons.min.css') }}" as="style">
    <link rel="preload" href="{{ asset('assets/fonts/feather.css') }}" as="style">

    <!-- font css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css')}}">

    <!-- vendor css -->
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/style.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-switch-button.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/datepicker-bs5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custome.css') }}">
    <style>
        :root {
            --color-customColor:
                <?= $color ?>
            ;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('css/custom-color.css') }}">
    @if ((isset($company_settings['site_rtl']) ? $company_settings['site_rtl'] : 'off') == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
    @endif

    @if ((isset($company_settings['cust_darklayout']) ? $company_settings['cust_darklayout'] : 'off') == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}" id="main-style-link">
    @endif
    @if ((isset($company_settings['site_rtl']) ? $company_settings['site_rtl'] : 'off') != 'on' && (isset($company_settings['cust_darklayout']) ? $company_settings['cust_darklayout'] : 'off') != 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    @else
        <link rel="stylesheet" href="" id="main-style-link">
    @endif

    @stack('css')
    @stack('availabilitylink')
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/nprogress.css') }}">
    <script src="{{ asset('assets/js/nprogress.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
</head>