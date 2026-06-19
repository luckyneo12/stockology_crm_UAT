<style>
    @media print {
        body, html, #app, .container, .wrapper, .main-content {
            display: none !important;
        }
    }
    
    /* Global blur classes when protection is active */
    body.anti-screenshot-active {
        overflow: hidden !important;
    }
    
    body.anti-screenshot-active .main-content,
    body.anti-screenshot-active .container-fluid,
    body.anti-screenshot-active .row,
    body.anti-screenshot-active #spreadsheet,
    body.anti-screenshot-active .jexcel_container,
    body.anti-screenshot-active .card {
        filter: blur(25px) !important;
        user-select: none !important;
        pointer-events: none !important;
        transition: filter 0.2s ease !important;
    }

    .anti-screenshot-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        z-index: 99999999;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        text-align: center;
    }
    
    body.anti-screenshot-active .anti-screenshot-overlay {
        display: flex;
    }

    .anti-screenshot-box {
        background: rgba(30, 41, 59, 0.85);
        padding: 3rem 2.5rem;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        max-width: 440px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        animation: antiScreenshotPopup 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        backdrop-filter: blur(5px);
    }

    @keyframes antiScreenshotPopup {
        from { transform: scale(0.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
</style>

<script>
    (function() {
        /*
        // Create lock overlay dynamically
        var overlay = document.createElement('div');
        overlay.className = 'anti-screenshot-overlay';
        overlay.innerHTML = `
            <div class="anti-screenshot-box">
                <div style="width: 70px; height: 70px; background: rgba(239, 68, 68, 0.15); color: #f87171; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; border: 1px solid rgba(239, 68, 68, 0.3);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye-off" width="36" height="36" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                       <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                       <path d="M10.585 10.587a2 2 0 0 0 2.829 2.828"></path>
                       <path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87"></path>
                       <path d="M3 3l18 18"></path>
                    </svg>
                </div>
                <h3 style="font-weight: 700; margin-bottom: 0.75rem; font-size: 1.5rem; letter-spacing: -0.02em; color: #ffffff;">Screen Protection Active</h3>
                <p style="font-size: 0.9rem; color: #94a3b8; line-height: 1.5; margin-bottom: 0;">For database and sheet security, page content is automatically hidden when window focus is lost.</p>
            </div>
        `;
        document.body.appendChild(overlay);

        // Detect window blur
        window.addEventListener('blur', function() {
            document.body.classList.add('anti-screenshot-active');
        });

        // Detect window focus
        window.addEventListener('focus', function() {
            setTimeout(function() {
                document.body.classList.remove('anti-screenshot-active');
            }, 250);
        });
        */

        // Block Ctrl+P (Print) and Ctrl+Shift+S
        window.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'p' || e.keyCode === 80)) {
                e.preventDefault();
                e.stopImmediatePropagation();
                showWarningToastr();
            }
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === 's' || e.key === 'S' || e.keyCode === 83)) {
                e.preventDefault();
                e.stopImmediatePropagation();
                showWarningToastr();
            }
        });

        // Detect PrintScreen key
        window.addEventListener('keyup', function(e) {
            if (e.key === 'PrintScreen' || e.keyCode === 44) {
                // Clear clipboard or replace with notice if browser permissions allow
                if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                    navigator.clipboard.writeText("Data export restricted.");
                }
                showWarningToastr();
            }
        });

        function showWarningToastr() {
            if (typeof show_toastr === 'function') {
                show_toastr('Security Alert', 'Screenshots and printing are strictly restricted on this page.', 'warning');
            } else {
                alert('Screenshots and printing are restricted on this page.');
            }
        }
    })();
</script>
