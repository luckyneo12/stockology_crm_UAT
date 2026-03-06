@php
    $admin_settings = getAdminAllSetting();

    $company_settings = getCompanyAllSetting(creatorId());

    $color = !empty($company_settings['color']) ? $company_settings['color'] : 'theme-1';
    if (isset($company_settings['color_flag']) && $company_settings['color_flag'] == 'true') {
        $themeColor = 'custom-color';
    } else {
        $themeColor = $color;
    }

    // Disable Chatify to prevent conflicts with custom messenger
    $disable_chatify = true;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ isset($company_settings['site_rtl']) && $company_settings['site_rtl'] == 'on' ? 'rtl' : '' }}">
@include('partials.head')

<body class="{{ isset($themeColor) ? $themeColor : 'theme-1' }}">
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill">

            </div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->
    <!-- [ auth-signup ] end -->
    @include('partials.sidebar')
    @include('partials.header')
    <section class="dash-container">
        <div class="dash-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
                        <div>
                            <div class="page-header-title">
                                <h4 class="mb-2">@yield('page-title')</h4>
                            </div>
                            <ul class="breadcrumb">
                                @php
                                    if (isset(app()->view->getSections()['page-breadcrumb'])) {
                                        $breadcrumb = explode(',', app()->view->getSections()['page-breadcrumb']);
                                    } else {
                                        $breadcrumb = [];
                                    }
                                @endphp
                                @if (!empty($breadcrumb))
                                    <li class="breadcrumb-item"><a
                                            href="{{ url('/') }}">@if (isset($admin_settings['company_name']))
                                            {{ $admin_settings['company_name'] }} @else
                                            {{ config('app.name', 'Stockology') }} @endif</a>
                                    </li>
                                    @foreach ($breadcrumb as $key => $item)
                                        <li class="breadcrumb-item {{ $key == count($breadcrumb) - 1 ? 'active' : '' }}">
                                            @if ($key == count($breadcrumb) - 1)
                                                {{ $item }}
                                            @else
                                                <a href="#">{{ $item }}</a>
                                            @endif
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            @yield('page-action')
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] End -->
            <!-- [ Main Content ] start -->
            @yield('content')
            <!-- [ Main Content ] end -->
        </div>
    </section>
    @include('partials.footer')


    <!-- Global Toast Notification System -->
    <script>
        // Define show_toastr function globally if not already defined
        if (typeof show_toastr === 'undefined') {
            window.show_toastr = function (title, message, type) {
                // Try to use toastr library if available
                if (typeof toastr !== 'undefined') {
                    toastr[type](message, title);
                    return;
                }

                // Fallback: Use Bootstrap toast or alert
                if (typeof bootstrap !== 'undefined') {
                    // Create a simple toast notification
                    var toastHtml = '<div class="toast align-items-center text-white bg-' + (type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info') + ' border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">' +
                        '<div class="d-flex">' +
                        '<div class="toast-body">' +
                        '<strong>' + title + ':</strong> ' + message +
                        '</div>' +
                        '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
                        '</div>' +
                        '</div>';

                    $('body').append(toastHtml);
                    var toastElement = $('body').children().last()[0];
                    var toast = new bootstrap.Toast(toastElement);
                    toast.show();

                    // Remove toast after it's hidden
                    $(toastElement).on('hidden.bs.toast', function () {
                        $(this).remove();
                    });
                } else {
                    // Final fallback: Use browser alert
                    alert(title + ': ' + message);
                }
            };
        }

        // Global toast notification system for messenger alerts
        $(document).ready(function () {
            // CSRF Token Setup for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Only show notifications if user is authenticated
            @auth
                let notificationPolling = null;
                let lastNotificationMessageId = null; // Track last shown message ID

                // Simple notification checker using toast
                function startToastNotifications() {
                    // Clear existing polling
                    if (notificationPolling) {
                        clearInterval(notificationPolling);
                    }

                    // Start checking for new messages every 3 seconds (real-time)
                    notificationPolling = setInterval(function () {
                        checkForToastNotifications();
                    }, 3000);
                }

                // Check for new unread messages and show toast
                function checkForToastNotifications() {
                    $.get('{{ route("messenger.latest.unread") }}', function (data) {
                        if (data.unread_messages && data.unread_messages.length > 0) {
                            // Get the most recent unread message
                            const latestMessage = data.unread_messages[0];

                            // Only show notification if this is a different message than last shown
                            if (lastNotificationMessageId !== latestMessage.id) {
                                show_toastr(
                                    'New Message from ' + latestMessage.from_name,
                                    latestMessage.body.length > 30 ? latestMessage.body.substring(0, 30) + '...' : latestMessage.body,
                                    'info'
                                );

                                // Track this message ID to prevent repeated notifications
                                lastNotificationMessageId = latestMessage.id;

                                // Minimal logging - only errors
                            } else {
                                // No logging for skipped notifications
                            }
                        }
                    }).fail(function (xhr) {
                        console.log('Global toast notification check error:', xhr.status);
                    });
                }

                // Start toast notifications for all authenticated pages
                startToastNotifications();
                // Silent initialization - no console output
            @endauth
        });
    </script>
</body>

</html>