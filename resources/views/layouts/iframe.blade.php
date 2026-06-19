@php
    $admin_settings = getAdminAllSetting();
    $company_settings = getCompanyAllSetting(creatorId());
    $color = !empty($company_settings['color']) ? $company_settings['color'] : 'theme-1';
    if (isset($company_settings['color_flag']) && $company_settings['color_flag'] == 'true') {
        $themeColor = 'custom-color';
    } else {
        $themeColor = $color;
    }
    $disable_chatify = true;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ isset($company_settings['site_rtl']) && $company_settings['site_rtl'] == 'on' ? 'rtl' : '' }}">
@include('partials.head')

<body class="{{ isset($themeColor) ? $themeColor : 'theme-1' }} bg-white overflow-x-hidden">
    <div class="container-fluid py-3">
        @yield('content')
    </div>
    @include('partials.footer')
    
    <!-- Custom styling inside iframe to hide any header/footer margins -->
    <style>
        .dash-container {
            margin-left: 0 !important;
            padding-top: 0 !important;
        }
        .page-header {
            display: none !important;
        }
        .dash-header {
            display: none !important;
        }
        .dash-sidebar {
            display: none !important;
        }
    </style>
</body>
</html>
