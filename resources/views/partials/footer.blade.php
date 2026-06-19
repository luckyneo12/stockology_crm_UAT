
@if (Route::currentRouteName() !== 'chatify')
    <div id="commonModal" class="modal" tabindex="-1" aria-labelledby="exampleModalLongTitle" aria-modal="true"
        role="dialog" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="body">
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="commonModalOver" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="body">
                </div>
            </div>
        </div>
    </div>
@endif
<div class="loader-wrapper d-none">
    <span class="site-loader"> </span>
</div>

<!-- Required Js -->

<script src="{{ asset('js/icons.js') }}"></script>
<script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/js/dash.js') }}"></script>
<script src="{{ asset('assets/js/plugins/simple-datatables.js') }}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap-switch-button.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/datepicker-full.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/flatpickr.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
<script src="{{ asset('js/jquery.form.js') }}"></script>
@if (!empty($company_settings['category_wise_sidemenu']) && $company_settings['category_wise_sidemenu'] == 'on')
    <script src="{{ asset('assets/js/layout-tab.js') }}"></script>
@endif



{{-- ===== NProgress: Page Load Bar on Navigation ===== --}}
<script>
    // Configure NProgress
    NProgress.configure({
        showSpinner: false,   // sirf top bar, spinner nahi
        speed: 400,
        minimum: 0.15,
        easing: 'ease',
        trickleSpeed: 150
    });

    $(document).ready(function () {

        // Sab navigation links pe NProgress start karo
        $(document).on('click', 'a', function (e) {
            var href = $(this).attr('href');

            // Ignore karo: empty, anchor, javascript, modal, tab, external links
            if (!href) return;
            if (href === '#' || href === '' || href.startsWith('#')) return;
            if (href.startsWith('javascript')) return;
            if ($(this).attr('data-bs-toggle')) return;  // bootstrap modal/tab
            if ($(this).attr('target') === '_blank') return;  // new tab
            if ($(this).hasClass('no-loader')) return;  // manually exclude karo

            // External link check
            try {
                var linkHost = new URL(href, window.location.origin).hostname;
                if (linkHost !== window.location.hostname) return;
            } catch (err) { return; }

            NProgress.start();
        });

        // Form submit pe bhi loading dkhao
        $(document).on('submit', 'form', function () {
            var method = $(this).attr('method') || 'GET';
            if (method.toUpperCase() !== 'GET') {
                // POST forms pe bhi loader show karo (optional)
            }
            NProgress.start();
        });

        // Page load hone ke baad band karo
        $(window).on('load', function () {
            NProgress.done();
        });

        // Agar AJAX navigation ho to bhi done karo
        $(document).ajaxStop(function () {
            NProgress.done();
        });
    });
</script>
{{-- ===== NProgress End ===== --}}

<script src="{{ asset('js/custom.js') }}"></script>
@if ($message = Session::get('success'))
    <script>
        toastrs('Success', '{!! $message !!}', 'success');
    </script>
@endif
@if ($message = Session::get('error'))
    <script>
        toastrs('Error', '{!! $message !!}', 'error');
    </script>
@endif
@stack('scripts')
@if (auth()->user()->type != 'super admin')
    {{-- @include('Chatify::layouts.footerLinks') --}}
    {{-- Chatify disabled to prevent conflicts with custom messenger --}}
@endif
@if (isset($admin_settings['enable_cookie']) && $admin_settings['enable_cookie'] == 'on')
    @include('layouts.cookie_consent')
@endif
</body>

</html>