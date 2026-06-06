<nav>
  <div class="dash-sidebar light-sidebar {{ empty($company_settings['site_transparent']) || $company_settings['site_transparent'] == 'on' ? 'transprent-bg' : '' }}">
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle d-md-none" onclick="toggleSidebar()">
        <i class="ti ti-menu-2"></i>
    </button>
    
    <div class="navbar-wrapper">
      <div class="m-header main-logo">
        <a href="{{ route('home') }}" class="b-brand">
          <!-- ========   change your logo hear   ============ -->
          <img src="{{ get_file(sidebar_logo()) }}{{ '?' . time() }}" alt="" class="logo logo-lg" width="170"
            height="50" />
          {{-- <img src="{{ get_file(sidebar_logo()) }}{{ '?' . time() }}" alt="" class="logo logo-sm" /> --}}
        </a>
      </div>
    </div>
    @if(!empty($company_settings['category_wise_sidemenu']) && $company_settings['category_wise_sidemenu'] == 'on')
      <div class="tab-container">
        <div class="tab-sidemenu">
          <ul class="dash-tab-link nav flex-column" role="tablist" id="dash-layout-submenus">
          </ul>
        </div>
        <div class="tab-link">
          <div class="navbar-content">



            <div class="tab-content" id="dash-layout-tab">
            </div>
            <ul class="dash-navbar">
              {!! getMenu() !!}
              @stack('custom_side_menu')
            </ul>
          </div>
        </div>
      </div>
    @else
      <div class="navbar-content">
        <ul class="dash-navbar">
          {!! getMenu() !!}
          @stack('custom_side_menu')
        </ul>
      </div>
    @endif

  </div>
</nav>

@push('scripts')
<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.dash-sidebar');
    if (sidebar) {
        sidebar.classList.toggle('show');
        
        // Add overlay for mobile
        let overlay = document.querySelector('.sidebar-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 999;
                display: none;
            `;
            document.body.appendChild(overlay);
        }
        
        overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
        
        // Close sidebar when clicking overlay
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.style.display = 'none';
        });
    }
}

// Close sidebar on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const sidebar = document.querySelector('.dash-sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        if (sidebar && sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
            if (overlay) overlay.style.display = 'none';
        }
    }
});

// Auto-close on window resize (desktop)
window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.dash-sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    if (window.innerWidth > 768) {
        if (sidebar) sidebar.classList.remove('show');
        if (overlay) overlay.style.display = 'none';
    }
});

// Add active menu highlighting
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    const menuLinks = document.querySelectorAll('.dash-tab-link');
    
    menuLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href.replace(/^\//, ''))) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
});
</script>
@endpush