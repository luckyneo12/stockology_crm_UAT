@extends(request()->has('layout') && request('layout') == 'iframe' ? 'layouts.iframe' : 'layouts.main')

@section('page-title')
    {{ $lead->name }}
@endsection
@push('css')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

        /* Apply premium typography system */
        #general, #useradd-sidenav, .hero-gradient, .page-title, .card-modern, .premium-card, .bento-card, .standard-card, .section-title, .alert, .badge, .list-group-item, table {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }

        :root {
            --primary-emerald: #059669;
            --primary-emerald-hover: #047857;
            --primary-emerald-light: rgba(5, 150, 105, 0.07);
            --primary-emerald-glow: rgba(5, 150, 105, 0.2);
            
            --theme-emerald: #059669;
            --theme-mint: #10b981;
            --theme-slate-dark: #0f172a;
            --theme-slate-muted: #64748b;
            --theme-slate-light: #f8fafc;
            
            --glass-border: rgba(255, 255, 255, 0.18);
            --glass-bg: rgba(255, 255, 255, 0.12);
            --glass-bg-dark: rgba(15, 23, 42, 0.03);
            
            --shadow-sm: 0 4px 12px rgba(15, 23, 42, 0.03);
            --shadow-md: 0 10px 30px rgba(15, 23, 42, 0.06);
            --shadow-lg: 0 20px 48px rgba(15, 23, 42, 0.08);
            --shadow-glow-emerald: 0 12px 30px rgba(5, 150, 105, 0.15);
            
            --border-light: rgba(15, 23, 42, 0.06);
            --border-emerald-glow: rgba(5, 150, 105, 0.15);
            --border-blue-glow: rgba(59, 130, 246, 0.15);
            
            --gradient-emerald: linear-gradient(135deg, #022c22 0%, #064e3b 50%, #047857 100%);
            --gradient-card-bento: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(244, 252, 246, 0.95) 100%);
            --gradient-card-premium: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(240, 247, 255, 0.95) 100%);
        }

        .nav-tabs .nav-link-tabs.active {
            background: none;
        }

        /* Bento Grid Layout Styles */
        .bento-card {
            border: 1px solid var(--border-emerald-glow) !important;
            background: var(--gradient-card-bento) !important;
            backdrop-filter: blur(12px);
            border-radius: 16px !important;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
            position: relative;
            overflow: hidden;
            border-left: 4px solid var(--theme-emerald) !important;
            min-height: 90px;
            box-shadow: var(--shadow-sm) !important;
        }
        .bento-card:hover {
            transform: translateY(-5px) scale(1.01) !important;
            border-left-color: var(--theme-mint) !important;
            box-shadow: var(--shadow-glow-emerald) !important;
            border-color: rgba(16, 185, 129, 0.28) !important;
        }
        .bento-card-large {
            background: linear-gradient(135deg, rgba(236, 253, 245, 0.95) 0%, rgba(255, 255, 255, 0.95) 100%) !important;
            border-left: 4px solid #047857 !important;
        }
        .bento-card-large:hover {
            box-shadow: 0 16px 36px rgba(4, 120, 87, 0.16) !important;
        }
        .bento-icon-container {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: var(--primary-emerald-light);
            color: var(--theme-emerald);
            transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .bento-card:hover .bento-icon-container {
            transform: scale(1.18) rotate(8deg);
            background: var(--theme-emerald);
            color: #fff;
        }

        .editable-field {
            cursor: pointer;
            border-bottom: 1.5px dashed rgba(5, 150, 105, 0.35) !important;
            padding-bottom: 2px;
            display: inline-block;
            transition: all 0.2s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
        }
        .editable-field:hover {
            color: var(--theme-emerald) !important;
            border-bottom-color: var(--theme-emerald) !important;
        }
        .editable-field::after {
            content: " ✎";
            font-size: 0.72rem;
            opacity: 0.25;
            transition: opacity 0.2s ease, transform 0.2s ease;
            color: var(--theme-emerald);
            margin-left: 4px;
            display: inline-block;
        }
        .editable-field:hover::after {
            opacity: 1;
            transform: scale(1.15) rotate(5deg);
        }

        /* Modern UI Enhancements */
        .fade-in-up {
            animation: fadeInUp 0.6s cubic-bezier(0.25, 0.8, 0.25, 1) forwards;
            opacity: 0;
            transform: translateY(24px);
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .delay-100 { animation-delay: 0.08s; }
        .delay-200 { animation-delay: 0.16s; }
        .delay-300 { animation-delay: 0.24s; }

        .hero-gradient {
            background: var(--gradient-emerald) !important;
            position: relative;
            overflow: hidden;
            border-radius: 20px !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: var(--shadow-lg) !important;
        }
        
        /* Modern Green Theme Overrides */
        .text-primary, .text-success { color: var(--theme-emerald) !important; }
        .bg-primary, .bg-success { background-color: var(--theme-emerald) !important; }
        .btn-primary { 
            background-color: var(--theme-emerald) !important; 
            border-color: var(--theme-emerald) !important;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.15) !important;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
        }
        .btn-primary:hover {
            background-color: var(--primary-emerald-hover) !important;
            border-color: var(--primary-emerald-hover) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 16px rgba(5, 150, 105, 0.25) !important;
        }
        .btn-info { 
            background-color: #0ea5e9 !important; 
            border-color: #0ea5e9 !important;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.15) !important;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
        }
        .btn-info:hover {
            background-color: #0284c7 !important;
            border-color: #0284c7 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 16px rgba(14, 165, 233, 0.25) !important;
        }
        .bg-info-subtle { background-color: rgba(16, 185, 129, 0.08) !important; color: #10b981 !important; }
        .text-info { color: #10b981 !important; }
        
        .badge.bg-primary { background-color: var(--theme-emerald) !important; }
        .badge.bg-info { background-color: #10b981 !important; }

        .form-check-input:checked {
            background-color: var(--theme-emerald);
            border-color: var(--theme-emerald);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
        }
        
        /* Task & Section Styles */
        .section-title {
            font-size: 0.85rem !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 1.5px !important;
            color: var(--theme-emerald) !important;
        }
        
        .task-item {
            transition: all 0.25s ease;
            border-left: 4px solid transparent;
        }
        .task-item:hover {
            background-color: rgba(5, 150, 105, 0.03) !important;
            border-left-color: var(--theme-emerald) !important;
        }
        .task-checkbox {
            width: 1.25em;
            height: 1.25em;
            border-radius: 50%;
            cursor: pointer;
        }
        
        .hero-pattern::before {
            content: '';
            position: absolute;
            top: 0; right: 0; bottom: 0; left: 0;
            background-image: radial-gradient(circle at 15% 25%, rgba(255,255,255,0.06) 0%, transparent 60%);
            pointer-events: none;
        }

        .card-modern {
            border: 1px solid var(--border-light) !important;
            background: #ffffff;
            border-radius: 18px !important;
            box-shadow: var(--shadow-sm) !important;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
        }
        .card-modern:hover {
            transform: translateY(-4px) !important;
            box-shadow: var(--shadow-md) !important;
            border-color: rgba(5, 150, 105, 0.08) !important;
        }

        .icon-shape-lg {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            font-size: 28px;
            transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        .card-modern:hover .icon-shape-lg {
            transform: scale(1.12) rotate(6deg);
            filter: brightness(1.06);
        }

        .stat-label {
            letter-spacing: 0.6px;
            text-transform: uppercase;
            font-size: 0.72rem;
            font-weight: 700;
            opacity: 0.55;
        }
        
        .progress-modern {
            height: 10px;
            background-color: #edf2f7;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);
            margin-top: 8px;
        }
        .progress-bar-modern {
            height: 100%;
            background: linear-gradient(90deg, var(--theme-emerald) 0%, var(--theme-mint) 100%);
            border-radius: 10px;
            position: relative;
            box-shadow: 0 2px 6px rgba(5, 150, 105, 0.2);
        }
        .progress-bar-modern::after {
            content: '';
            position: absolute;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent);
            background-size: 1rem 1rem;
            animation: progress-bar-stripes 2s linear infinite;
            opacity: 0.3;
        }
        
        @keyframes progress-bar-stripes {
            from { background-position: 1rem 0; }
            to { background-position: 0 0; }
        }

        .hover-glow:hover {
            box-shadow: 0 12px 35px rgba(5, 150, 105, 0.16) !important;
        }

        .responsible-glow {
            background: rgba(255, 255, 255, 0.95) !important;
            border-radius: 16px !important;
            border: 1px solid rgba(255, 193, 7, 0.25) !important;
            box-shadow: 0 8px 20px rgba(255, 193, 7, 0.08) !important;
            transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
        }
        .responsible-glow:hover {
            transform: translateY(-4px) scale(1.02) !important;
            box-shadow: 0 15px 30px rgba(255, 193, 7, 0.18) !important;
            border-color: rgba(255, 193, 7, 0.5) !important;
        }
        
        .list-group-item-action {
            border-radius: 10px !important;
            margin-bottom: 4px;
            border: 1px solid transparent;
        }
        .list-group-item-action.active {
            background: linear-gradient(90deg, rgba(5, 150, 105, 0.08), transparent) !important;
            border-left: 4px solid var(--theme-emerald) !important;
            color: var(--theme-emerald) !important;
            font-weight: 700;
        }
        /* Timeline CSS */
        .timeline-vertical {
            position: relative;
            padding-left: 2rem;
            border-left: 2px solid #e2e8f0;
            margin-left: 10px;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
        }
        .timeline-dot {
            position: absolute;
            left: -33px;
            top: 2px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #fff;
            border: 4px solid var(--theme-emerald);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
        }
        
        /* Sidebar Styling */
        #useradd-sidenav {
            background: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(15, 23, 42, 0.05);
            border-radius: 18px;
            padding: 16px !important;
            box-shadow: var(--shadow-sm);
        }
        #useradd-sidenav .list-group-item {
            border-radius: 12px !important;
            margin-bottom: 0.5rem;
            padding: 12px 16px !important;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            color: #475569;
            font-weight: 600;
            border: 1px solid transparent;
            background: transparent;
        }
        #useradd-sidenav .list-group-item:hover {
            background-color: var(--primary-emerald-light);
            color: var(--theme-emerald);
            transform: translateX(6px);
            border-color: rgba(5, 150, 105, 0.08);
        }
        #useradd-sidenav .list-group-item.active {
            background: linear-gradient(135deg, var(--theme-emerald) 0%, #047857 100%) !important;
            color: #fff !important;
            border-color: transparent;
            box-shadow: var(--shadow-glow-emerald);
            transform: translateX(4px);
        }
        #useradd-sidenav .list-group-item.active .ti {
            color: #fff !important;
        }
        #useradd-sidenav .list-group-item .ti {
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }
        #useradd-sidenav .list-group-item:hover .ti {
            transform: scale(1.18) rotate(4deg);
        }
        .bg-success { background-color: var(--theme-emerald) !important; }
        .bg-danger { background-color: #ef4444 !important; }
        .bg-warning { background-color: #f59e0b !important; }
        
        .stat-card-accent {
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            border-radius: 4px 0 0 4px;
        }

        /* Section Layout Enhancements */
        .section-layout-standard {
            border-left: 4px solid var(--theme-slate-muted) !important;
        }
        .section-layout-card {
            border-left: 4px solid #3b82f6 !important;
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.06) !important;
        }
        .section-layout-bento {
            border-left: 4px solid var(--theme-emerald) !important;
            background: radial-gradient(circle, rgba(5, 150, 105, 0.02) 1px, transparent 1px) #fff;
            background-size: 24px 24px;
            box-shadow: 0 10px 25px rgba(5, 150, 105, 0.06) !important;
        }

        /* Premium Card Styles (for fields) */
        .premium-card {
            border: 1px solid var(--border-blue-glow) !important;
            background: var(--gradient-card-premium) !important;
            border-radius: 16px !important;
            border-top: 4px solid #3b82f6 !important;
            min-height: 90px;
            box-shadow: var(--shadow-sm) !important;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
            position: relative;
            overflow: hidden;
        }
        .premium-card:hover {
            transform: translateY(-5px) !important;
            box-shadow: 0 15px 35px rgba(59, 130, 246, 0.12) !important;
            border-color: rgba(59, 130, 246, 0.3) !important;
        }
        .premium-card-large {
            background: linear-gradient(135deg, rgba(239, 246, 255, 0.95) 0%, #ffffff 100%) !important;
            border-top: 4px solid #1d4ed8 !important;
        }
        .premium-card-large:hover {
            box-shadow: 0 16px 36px rgba(29, 78, 216, 0.16) !important;
        }
        .premium-icon-container {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: rgba(59, 130, 246, 0.08);
            color: #3b82f6;
            transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .premium-card:hover .premium-icon-container {
            transform: scale(1.18) rotate(-8deg);
            background: #3b82f6;
            color: #fff;
        }

        /* Standard Card Styles (for fields) */
        .standard-card {
            border: 1px solid var(--border-light) !important;
            background: var(--theme-slate-light) !important;
            border-radius: 12px !important;
            min-height: 80px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
            position: relative;
            overflow: hidden;
        }
        .standard-card:hover {
            border-color: #cbd5e1 !important;
            box-shadow: var(--shadow-sm) !important;
            background: #f1f5f9 !important;
            transform: translateY(-2px) !important;
        }
        .standard-card-large {
            background: #f1f5f9 !important;
            border-left: 4px solid #64748b !important;
        }
        .standard-icon-container {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(148, 163, 184, 0.12);
            color: #64748b;
            transition: all 0.3s ease;
        }
        .standard-card:hover .standard-icon-container {
            background: rgba(148, 163, 184, 0.25);
            color: #0f172a;
        }

        /* ===== COMPREHENSIVE UI/UX POLISH ===== */

        /* File / Document Upload Card */
        .file-upload-card {
            border: 2px dashed rgba(5, 150, 105, 0.2) !important;
            background: rgba(248, 252, 250, 0.9) !important;
            border-radius: 14px !important;
            min-height: 85px;
            transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        .file-upload-card:hover {
            border-color: var(--theme-emerald) !important;
            background: rgba(5, 150, 105, 0.03) !important;
            transform: translateY(-3px) scale(1.005) !important;
            box-shadow: 0 8px 24px rgba(5, 150, 105, 0.10) !important;
        }
        .file-upload-card:hover .file-icon-container {
            background: var(--theme-emerald) !important;
            color: #fff !important;
            transform: scale(1.1) rotate(5deg);
        }
        .file-icon-container {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: rgba(5, 150, 105, 0.08);
            color: var(--theme-emerald);
            transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1);
            flex-shrink: 0;
        }
        .file-has-value {
            border: 1.5px solid rgba(5, 150, 105, 0.15) !important;
            background: rgba(240, 253, 244, 0.8) !important;
            border-style: solid !important;
        }
        .file-has-value:hover {
            box-shadow: 0 8px 20px rgba(5, 150, 105, 0.12) !important;
        }

        /* Section title pills & badges */
        .section-badge-percentage {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, var(--theme-emerald), #047857) !important;
            color: #fff !important;
            border-radius: 20px !important;
            font-size: 0.7rem !important;
            font-weight: 700 !important;
            padding: 3px 10px !important;
            letter-spacing: 0.3px;
            box-shadow: 0 3px 8px rgba(5, 150, 105, 0.25);
        }

        /* Stats grid card enhancements */
        .stat-quick-number {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, var(--theme-slate-dark), #334155);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: block;
            margin-bottom: 4px;
        }
        .stat-divider-v {
            width: 1px;
            background: linear-gradient(to bottom, transparent, #e2e8f0, transparent);
            height: 60%;
            align-self: center;
        }

        /* Contact info card enhancements */
        .contact-info-row {
            border-radius: 12px;
            padding: 12px 14px;
            transition: all 0.25s ease;
            border: 1px solid transparent;
        }
        .contact-info-row:hover {
            background: rgba(5, 150, 105, 0.03);
            border-color: rgba(5, 150, 105, 0.1);
        }

        /* Lead Name display tweak */
        .lead-hero-name {
            font-size: clamp(1.5rem, 3vw, 2.5rem);
            font-weight: 800;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        /* Field label row in card layout */
        .field-label-col {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            min-width: 130px;
            flex-shrink: 0;
        }
        .field-value-col {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
        }

        /* Activity & Task feed item */
        .feed-item {
            border-left: 3px solid #e2e8f0;
            padding-left: 16px;
            position: relative;
            margin-bottom: 20px;
            transition: border-color 0.3s ease;
        }
        .feed-item:hover {
            border-left-color: var(--theme-emerald);
        }
        .feed-item::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 4px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #e2e8f0;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #e2e8f0;
            transition: all 0.3s ease;
        }
        .feed-item:hover::before {
            background: var(--theme-emerald);
            box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2);
        }

        /* Section card border-left accent colors per layout */
        .section-layout-card { border-left: 4px solid #3b82f6 !important; }
        .section-layout-bento { border-left: 4px solid var(--theme-emerald) !important; }
        .section-layout-standard { border-left: 4px solid #94a3b8 !important; }

        /* Card modern: remove hover lift on mobile */
        @media (max-width: 768px) {
            .card-modern:hover { transform: none !important; }
            .bento-card:hover { transform: none !important; }
        }

        /* Overall smooth page transitions */
        * { scroll-behavior: smooth; }
        a, button { transition: color 0.2s ease, background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease; }

        /* Premium Dropdown Stage Changer Styles */
        .dropdown-stage-changer {
            position: relative;
            user-select: none;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
        }
        .dropdown-stage-changer:hover {
            transform: translateY(-2px) scale(1.02);
            background: rgba(255, 255, 255, 0.12) !important;
            border-color: rgba(255, 255, 255, 0.22) !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.18) !important;
        }
        .dropdown-stage-changer:active {
            transform: translateY(0) scale(1.0);
        }
        .dropdown-stage-changer:hover .rounded-circle {
            transform: scale(1.1) rotate(15deg);
            filter: brightness(1.1);
        }
        .fade-in-dropdown {
            animation: fadeInDropdown 0.25s cubic-bezier(0.25, 0.8, 0.25, 1) forwards;
            transform-origin: top right;
        }
        @keyframes fadeInDropdown {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-8px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        .dropdown-menu.border-emerald-light {
            border: 1px solid rgba(5, 150, 105, 0.12) !important;
            box-shadow: 0 15px 35px rgba(15, 23, 42, 0.12) !important;
        }
        .dropdown-item.btn-change-stage:hover {
            background-color: var(--primary-emerald-light) !important;
            color: var(--theme-emerald) !important;
            transform: translateX(4px);
            font-weight: 600;
        }
        .dropdown-item.btn-change-stage:hover .hover-slide-icon {
            transform: translateX(4px);
            opacity: 1 !important;
            color: var(--theme-emerald) !important;
        }
        .dropdown-item.btn-change-stage {
            transition: all 0.25s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
        }

        /* Style "Not Provided" placeholders as beautiful, clean, dashed pill badges */
        span.text-muted.fw-normal[style*="opacity: 0.55"], 
        span.text-muted.fw-normal.fst-italic {
            background: rgba(15, 23, 42, 0.03) !important;
            padding: 3px 10px !important;
            border-radius: 20px !important;
            border: 1.5px dashed rgba(15, 23, 42, 0.12) !important;
            font-size: 0.72rem !important;
            color: #64748b !important;
            font-style: normal !important;
            opacity: 0.75 !important;
            display: inline-flex !important;
            align-items: center !important;
            letter-spacing: 0.2px;
            font-weight: 600 !important;
            transition: all 0.3s ease;
        }
        span.text-muted.fw-normal:hover {
            background: rgba(5, 150, 105, 0.03) !important;
            border-color: rgba(5, 150, 105, 0.25) !important;
            color: var(--theme-emerald) !important;
        }

        /* Dashed connectors for clean card fields key-value rows */
        .py-3.px-2.border-bottom {
            border-bottom: 1.5px dashed #edf2f7 !important;
            transition: all 0.25s ease;
        }
        .py-3.px-2.border-bottom:hover {
            border-bottom-color: rgba(5, 150, 105, 0.15) !important;
            background-color: rgba(5, 150, 105, 0.01) !important;
        }

        /* ===== STAGE PROGRESSION STEPPER ===== */
        .stepper-track {
            display: flex;
            flex-direction: row;
            align-items: center;
            flex-wrap: nowrap;
            overflow-x: auto;
            gap: 0;
            width: 100%;
            padding-bottom: 12px;
            margin-bottom: -6px;
            scrollbar-width: thin;
            scrollbar-color: var(--theme-emerald) rgba(0, 0, 0, 0.05);
        }
        .stepper-track::-webkit-scrollbar {
            height: 6px;
        }
        .stepper-track::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.03);
            border-radius: 4px;
        }
        .stepper-track::-webkit-scrollbar-thumb {
            background: rgba(5, 150, 105, 0.25);
            border-radius: 4px;
        }
        .stepper-track::-webkit-scrollbar-thumb:hover {
            background: var(--theme-emerald);
        }
        .stepper-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            flex: 0 0 115px;
            min-width: 115px;
        }
        .stepper-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 13px;
            left: calc(50% + 13px);
            right: calc(-50% + 13px);
            height: 2px;
            background: linear-gradient(90deg, #e2e8f0 0%, #e2e8f0 100%);
            z-index: 0;
            transition: background 0.4s ease;
        }
        .stepper-item.completed:not(:last-child)::after {
            background: linear-gradient(90deg, var(--theme-emerald), rgba(5,150,105,0.3));
        }
        .stepper-node {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            position: relative;
            z-index: 1;
            border: 2px solid #e2e8f0;
            background: #fff;
            color: #94a3b8;
            cursor: default;
            transition: all 0.3s cubic-bezier(0.25,0.8,0.25,1);
            flex-shrink: 0;
        }
        .stepper-node.active {
            background: linear-gradient(135deg, var(--theme-emerald), #047857);
            border-color: var(--theme-emerald);
            color: #fff;
            box-shadow: 0 0 0 4px rgba(5,150,105,0.18), 0 4px 12px rgba(5,150,105,0.25);
        }
        .stepper-node.completed {
            background: var(--theme-emerald);
            border-color: var(--theme-emerald);
            color: #fff;
        }
        .stepper-node.can-move {
            cursor: pointer;
        }
        .stepper-node.can-move:hover {
            transform: scale(1.22);
            box-shadow: 0 6px 20px rgba(5,150,105,0.28);
            background: linear-gradient(135deg, var(--theme-emerald), #047857);
            border-color: var(--theme-emerald);
            color: #fff;
        }
        .stepper-node.locked {
            background: #f1f5f9;
            border-color: #e2e8f0;
            color: #cbd5e1;
            cursor: not-allowed;
        }
        .stepper-node.locked:hover .lock-overlay { opacity: 1; }
        .lock-overlay {
            position: absolute;
            inset: 0;
            background: rgba(239,246,255,0.85);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease;
            font-size: 9px;
            color: #64748b;
        }
        .stepper-label {
            font-size: 9px;
            font-weight: 700;
            text-align: center;
            margin-top: 6px;
            line-height: 1.2;
            max-width: 100%;
            padding: 0 4px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
            color: #64748b;
            transition: color 0.3s ease;
            letter-spacing: 0.1px;
            height: 22px;
        }
        .stepper-item.active .stepper-label { color: var(--theme-emerald); font-weight: 800; }
        .stepper-item.completed .stepper-label { color: var(--theme-emerald); }
        .stepper-card {
            border-radius: 14px !important;
            border: 1px solid rgba(5,150,105,0.08) !important;
            background: #fff !important;
            box-shadow: 0 4px 16px rgba(5,150,105,0.05), 0 1px 4px rgba(15,23,42,0.04) !important;
        }

        /* Responsive Overhaul for Iframe Detail Drawer on Mobile (<768px) */
        @media (max-width: 767px) {
            body {
                padding: 4px !important;
            }
            .container-fluid {
                padding-left: 8px !important;
                padding-right: 8px !important;
            }
            #useradd-sidenav {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                overflow-x: auto !important;
                background: #ffffff !important;
                border: 1px solid rgba(15, 23, 42, 0.08) !important;
                box-shadow: var(--shadow-sm) !important;
                padding: 10px !important;
                margin-bottom: 18px !important;
                gap: 8px !important;
                border-radius: 14px !important;
                position: sticky !important;
                top: 0px;
                z-index: 1000;
                scrollbar-width: none !important;
            }
            #useradd-sidenav::-webkit-scrollbar {
                display: none !important;
            }
            #useradd-sidenav .list-group-item {
                flex: 0 0 auto !important;
                margin-bottom: 0 !important;
                padding: 8px 16px !important;
                border-radius: 20px !important;
                white-space: nowrap !important;
                background: #f8fafc !important;
                border: 1px solid rgba(15, 23, 42, 0.05) !important;
                transform: none !important;
                font-size: 0.78rem !important;
                color: #475569 !important;
            }
            #useradd-sidenav .list-group-item:hover {
                color: var(--theme-emerald) !important;
                background-color: var(--primary-emerald-light) !important;
                transform: none !important;
            }
            #useradd-sidenav .list-group-item.active {
                background: linear-gradient(135deg, var(--theme-emerald) 0%, #047857 100%) !important;
                color: #ffffff !important;
                border-color: transparent !important;
                box-shadow: var(--shadow-glow-emerald) !important;
            }
            #useradd-sidenav .list-group-item .ti-chevron-right,
            #useradd-sidenav .list-group-item .float-end {
                display: none !important;
            }
            #useradd-sidenav .list-group-item .ti {
                font-size: 1rem !important;
                margin-right: 6px !important;
            }
            .card.card-modern {
                margin-bottom: 16px !important;
            }
            .card.card-modern .card-body {
                padding: 16px !important;
            }
            .card.hero-gradient {
                border-radius: 14px !important;
            }
            .card.hero-gradient .card-body {
                padding: 20px !important;
            }
            .lead-hero-name {
                font-size: 1.8rem !important;
            }
            .stepper-card {
                padding: 12px !important;
            }
            .col-12 {
                padding-left: 6px !important;
                padding-right: 6px !important;
            }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}">
    <link rel="stylesheet" href="{{ asset('packages/workdo/Lead/src/Resources/assets/css/dropzone.min.css') }}">
@endpush

@include('lead::layouts.anti_screenshot')

@php
    $lead->activities = $lead->activities->load('user');
    $lead->discussions = $lead->discussions->load('user');
    $lead->calls = $lead->calls->load('getLeadCallUser');
@endphp

@push('scripts')
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300
        });

        // Auto-scroll mobile sticky tab bar when ScrollSpy changes
        $(window).on('activate.bs.scrollspy', function (e) {
            var activeItem = $('#useradd-sidenav .list-group-item.active');
            if (activeItem.length > 0 && window.innerWidth < 768) {
                var container = $('#useradd-sidenav');
                var scrollLeft = activeItem.position().left + container.scrollLeft() - (container.width() / 2) + (activeItem.width() / 2);
                container.stop().animate({ scrollLeft: scrollLeft }, 250);
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                var activeNode = $('.stepper-item.active');
                if (activeNode.length > 0) {
                    var track = $('.stepper-track');
                    if (track.length > 0) {
                        var trackWidth = track.outerWidth();
                        var activeLeft = activeNode.position().left;
                        var activeWidth = activeNode.outerWidth();
                        var scrollLeft = track.scrollLeft() + activeLeft - (trackWidth / 2) + (activeWidth / 2);
                        track.animate({ scrollLeft: scrollLeft }, 600);
                    }
                }
            }, 300);
        });
    </script>
    <script src="{{ asset('packages/workdo/Lead/src/Resources/assets/js/dropzone.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>

    <script>
        @if (!Auth::user()->hasRole('client'))
            Dropzone.autoDiscover = false;


            if ($("#dropzonewidget2").length > 0) {
                myDropzone2 = new Dropzone("#dropzonewidget2", {
                    maxFiles: 20,
                    maxFilesize: 20,
                    parallelUploads: 1,
                    acceptedFiles: ".jpeg,.jpg,.png,.pdf,.doc,.txt",
                    url: "{{ route('leads.file.upload', $lead->id) }}",
                    success: function(file, response) {
                        if (response.is_success) {
                            dropzoneBtn(file, response);
                        } else {
                            myDropzone2.removeFile(file);
                            toastrs('Error', response.error, 'error');
                        }
                    },
                    error: function(file, response) {
                        myDropzone2.removeFile(file);
                        if (response.error) {
                            toastrs('Error', response.error, 'error');
                        } else {
                            toastrs('Error', response, 'error');
                        }
                    }
                });
                myDropzone2.on("sending", function(file, xhr, formData) {
                    formData.append("_token", $('meta[name="csrf-token"]').attr('content'));
                    formData.append("lead_id", {{ $lead->id }});
                });
            }

            function dropzoneBtn(file, response) {
                var download = document.createElement('a');
                download.setAttribute('href', response.download);
                download.setAttribute('class', "btn btn-sm btn-primary m-1");
                download.setAttribute('data-toggle', "tooltip");
                download.setAttribute('download', file.name);
                download.setAttribute('data-original-title', "{{ __('Download') }}");
                download.innerHTML = "<i class='ti ti-download'></i>";

                var del = document.createElement('a');
                del.setAttribute('href', response.delete);
                del.setAttribute('class', "btn btn-sm btn-danger mx-1");
                del.setAttribute('data-toggle', "tooltip");
                del.setAttribute('data-original-title', "{{ __('Delete') }}");
                del.innerHTML = "<i class='ti ti-trash'></i>";

                del.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (confirm("Are you sure ?")) {
                        var btn = $(this);
                        $.ajax({
                            url: btn.attr('href'),
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            type: 'DELETE',
                            success: function(response) {
                                if (response.is_success) {
                                    btn.closest('.dz-image-preview').remove();
                                    btn.closest('.dz-file-preview').remove();
                                    toastrs('Success', response.success, 'success');
                                } else {
                                    toastrs('Error', response.error, 'error');
                                }
                            },
                            error: function(response) {
                                response = response.responseJSON;
                                if (response.error) {
                                    toastrs('Error', response.error, 'error');
                                } else {
                                    toastrs('Error', response, 'error');
                                }
                            }
                        })
                    }
                });

                var html = document.createElement('div');
                html.appendChild(download);
                @if (!Auth::user()->hasRole('client'))
                    @permission('lead edit')
                        html.appendChild(del);
                    @endpermission
                @endif

                file.previewTemplate.appendChild(html);
            }

            if (typeof myDropzone2 !== 'undefined') {
                @foreach ($lead->files as $file)

                    // Create the mock file:
                    var mockFile2 = {
                        name: "{{ $file->file_name }}",
                        size: "{{ get_size(get_file($file->file_path)) }}"
                    };
                    // Call the default addedfile event handler
                    myDropzone2.emit("addedfile", mockFile2);
                    // And optionally show the thumbnail of the file:
                    myDropzone2.emit("thumbnail", mockFile2, "{{ get_file($file->file_path) }}");
                    myDropzone2.emit("complete", mockFile2);

                    dropzoneBtn(mockFile2, {
                        download: "{{ get_file($file->file_path) }}",
                        delete: "{{ route('leads.file.delete', [$lead->id, $file->id]) }}"
                    });
                @endforeach
            }
        @endif

        @permission('lead task edit')
            $(document).on("click", ".task-checkbox", function() {
                var chbox = $(this);
                var lbl = chbox.parent().parent().find('label');

                $.ajax({
                    url: chbox.attr('data-url'),
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        status: chbox.val()
                    },
                    type: 'PUT',
                    success: function(response) {
                        if (response.is_success) {
                            chbox.val(response.status);
                            if (response.status == 'done') {
                                lbl.addClass('strike');
                                lbl.find('.badge').removeClass('bg-warning bg-danger').addClass('bg-success');
                            } else if (response.status == 'overdue') {
                                lbl.removeClass('strike');
                                lbl.find('.badge').removeClass('bg-success bg-warning').addClass('bg-danger');
                            } else {
                                lbl.removeClass('strike');
                                lbl.find('.badge').removeClass('bg-success bg-danger').addClass('bg-warning');
                            }
                            lbl.find('.badge').html(response.status_label);

                            toastrs('Success', response.success, 'success');
                        } else {
                            toastrs('Error', response.error, 'error');
                        }
                    },
                    error: function(response) {
                        response = response.responseJSON;
                        if (response.is_success) {
                            toastrs('Error', response.error, 'error');
                        } else {
                            toastrs('Error', response, 'error');
                        }
                    }
                })
            });
        @endpermission

        $(document).ready(function() {
            var tab = 'general';
            @if ($tab = Session::get('status'))
                var tab = '{{ $tab }}';
            @endif
            $("#myTab2 .nav-link-tabs[href='#" + tab + "']").trigger("click");
        });
    </script>

    @if (Laratrust::hasPermission('lead edit'))
        <script>
            $(document).ready(function() {
                $('.summernote').on('summernote.blur', function() {
                    $.ajax({
                        url: "{{ route('leads.note.store', $lead->id) }}",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            notes: $(this).val()
                        },
                        type: 'POST',
                        success: function(response) {
                            if (response.is_success) {} else {
                                toastrs('Error', response.error, 'error');
                            }
                        },
                        error: function(response) {
                            response = response.responseJSON;
                            if (response.is_success) {
                                toastrs('Error', response.error, 'error');
                            } else {
                                toastrs('Error', response, 'error');
                            }
                        }
                    })
                });
            });
        </script>
    @else
        <script>
            $('.summernote').hide('disable');
        </script>
    @endif
    <script>
        if ($(".summernote").length > 0) {
            $('.summernote').summernote({
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'strikethrough']],
                    ['list', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'unlink']],
                ],
                height: 230,
            });
        }
    </script>
    {{-- Custom field description --}}
    <script>
        document.querySelectorAll('.description-container').forEach(function(container) {
            container.addEventListener('click', function() {
                var shortDescription = container.querySelector('.shortDescription');
                var fullDescription = container.querySelector('.fullDescription');

                if (shortDescription.style.display === 'block' || shortDescription.style.display === '') {
                    shortDescription.style.display = 'none';
                    fullDescription.style.display = 'block';
                } else {
                    shortDescription.style.display = 'block';
                    fullDescription.style.display = 'none';
                }
            });
        });

        // REVEAL FIELD SCRIPT
        $(document).on('click', '.reveal-link', function(e) {
            e.preventDefault();
            var btn = $(this);
            var url = btn.data('url');
            var targetId = btn.data('target');
            var target = $(targetId);
            
            $.ajax({
                url: url,
                success: function(res) {
                        if(res.is_success) {
                            target.text(res.value);
                            target.removeClass('masked-value');
                            btn.remove();
                        } else {
                            toastrs('Error', res.error, 'error');
                        }
                },
                error: function() {
                    toastrs('Error', 'Permission Denied', 'error');
                }
            });
        });

        // ===== STEPPER: Move Stage =====
        $(document).on('click', '.btn-move-stage', function() {
            var btn       = $(this);
            var leadId    = btn.data('lead-id');
            var stageId   = btn.data('stage-id');
            var stageName = btn.data('stage-name');

            Swal.fire({
                title: '{{ __("Move to stage?") }}',
                html: '<div style="font-size:0.9rem;color:#64748b;">{{ __("Move this lead to stage:") }}<br><strong style="color:#059669;">' + stageName + '</strong></div>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '{{ __("Yes, Move") }}',
                cancelButtonText: '{{ __("Cancel") }}',
                confirmButtonColor: '#059669',
                cancelButtonColor: '#e2e8f0',
                buttonsStyling: true,
                reverseButtons: true,
                backdrop: 'rgba(15,23,42,0.35)',
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("leads.order") }}',
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            id: leadId,
                            lead_order: stageId,
                        },
                        beforeSend: function() {
                            btn.css('opacity', '0.6');
                            btn.prop('disabled', true);
                        },
                        success: function(res) {
                            if (res.is_success || res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '{{ __("Stage Updated!") }}',
                                    text: '{{ __("Lead moved to") }} ' + stageName,
                                    timer: 1500,
                                    showConfirmButton: false,
                                    backdrop: 'rgba(15,23,42,0.25)',
                                }).then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('{{ __("Error") }}', res.error || '{{ __("Could not update stage") }}', 'error');
                                btn.css('opacity', '1').prop('disabled', false);
                            }
                        },
                        error: function() {
                            Swal.fire('{{ __("Error") }}', '{{ __("Server error while updating stage.") }}', 'error');
                            btn.css('opacity', '1').prop('disabled', false);
                        }
                    });
                }
            });
        });

        // Activate sidebar scroll-spy highlighting
        var stepperScrollSpy = document.getElementById('useradd-sidenav');
        if (stepperScrollSpy) {
            document.querySelectorAll('#useradd-sidenav .list-group-item').forEach(function(item) {
                item.addEventListener('click', function() {
                    document.querySelectorAll('#useradd-sidenav .list-group-item').forEach(function(i) {
                        i.classList.remove('active');
                        i.querySelector('.ti-chevron-right') && i.querySelector('.ti-chevron-right').classList.remove('text-white');
                    });
                    this.classList.add('active');
                });
            });
        }
    </script>

@endpush

@section('page-breadcrumb')
    {{ __('Leads') }},
    {{ $lead->name }}
@endsection


@section('page-action')
    <div class="d-flex">
        <a href="{{ route('leads.index') }}" class="btn btn-sm btn-primary btn-icon me-2" data-bs-toggle="tooltip" title="{{__('Back')}}">
            <i class="ti ti-arrow-left text-white"></i>
        </a>
        @stack('addButtonHook')
        @php
            $orionSettings = \Workdo\Lead\Http\Controllers\OrionIntegrationController::getOrionSettings();
            $orionRules = $orionSettings['rules'] ?? [];
            $activeOrionRule = collect($orionRules)->first(function($r) use ($lead) {
                return ($r['stage_id'] ?? null) == $lead->stage_id;
            });
            $clientCodeValue = '';
            if ($activeOrionRule) {
                // 1. Try to get value mapped to ClientCode first
                $mapping = $activeOrionRule['field_mapping'] ?? [];
                $clientCodeKey = $mapping['ClientCode'] ?? null;
                if ($clientCodeKey) {
                    if ($clientCodeKey === 'dp_id') {
                        $clientCodeValue = $lead->dp_id;
                    } elseif (strpos($clientCodeKey, 'custom_') === 0) {
                        $cfId = substr($clientCodeKey, 7);
                        $clientCodeValue = $leadCustomFieldValues[$cfId] ?? '';
                    }
                }

                // 2. If empty, search custom field named 'CLIENT CODE' or 'CLIENT_CODE'
                if (empty($clientCodeValue)) {
                    $customFieldsList = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', getActiveWorkSpace())->get();
                    $cfClientCode = $customFieldsList->first(function($f) {
                        $name = strtoupper(trim($f->name));
                        return $name === 'CLIENT CODE' || $name === 'CLIENT_CODE';
                    });
                    if ($cfClientCode && !empty($leadCustomFieldValues[$cfClientCode->id])) {
                        $clientCodeValue = $leadCustomFieldValues[$cfClientCode->id];
                    }
                }

                // 3. If empty, try to get value mapped to PanNo
                if (empty($clientCodeValue)) {
                    $panNoKey = $mapping['PanNo'] ?? null;
                    if ($panNoKey) {
                        if ($panNoKey === 'pan_number') {
                            $clientCodeValue = $lead->pan_number;
                        } elseif (strpos($panNoKey, 'custom_') === 0) {
                            $cfId = substr($panNoKey, 7);
                            $clientCodeValue = $leadCustomFieldValues[$cfId] ?? '';
                        }
                    }
                }

                // 4. If empty, search custom field named 'PANCARD NUMBER' / 'PAN CARD' / 'PAN NUMBER'
                if (empty($clientCodeValue)) {
                    if (!isset($customFieldsList)) {
                        $customFieldsList = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', getActiveWorkSpace())->get();
                    }
                    $cfPan = $customFieldsList->first(function($f) {
                        $name = strtoupper(trim($f->name));
                        return $name === 'PANCARD NUMBER' || $name === 'PAN CARD' || $name === 'PAN NUMBER' || $name === 'PAN_NUMBER';
                    });
                    if ($cfPan && !empty($leadCustomFieldValues[$cfPan->id])) {
                        $clientCodeValue = $leadCustomFieldValues[$cfPan->id];
                    }
                }

                // 5. If empty, try lead attributes (dp_id, pan_number)
                if (empty($clientCodeValue)) {
                    $clientCodeValue = $lead->dp_id ?? $lead->pan_number ?? '';
                }

                // 6. Ultimate fallback to phone/mobile number
                if (empty($clientCodeValue)) {
                    $clientCodeValue = !empty($lead->phone) ? preg_replace('/[^0-9]/', '', $lead->phone) : '';
                }
            }
        @endphp
        @if($activeOrionRule)
            <a href="javascript:void(0)" class="btn btn-sm btn-icon orion-pulse-btn me-2" id="btn-orion-ekyc-fetch" data-client-code="{{ $clientCodeValue }}" data-rule-id="{{ $activeOrionRule['id'] }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Fetch Orion EKYC')}}">
                <i class="ti ti-cloud-download text-white"></i>
            </a>
        @endif
        @permission('lead edit')
            <a class="btn btn-sm btn-primary btn-icon me-2" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Labels') }}" data-ajax-popup="true" data-size="md" data-title="{{ __('Label') }}"
                data-url="{{ URL::to('leads/' . $lead->id . '/labels') }}"><i class="ti ti-tag text-white"></i></a>
            <a class="btn btn-sm btn-info btn-icon me-2" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Edit') }}" data-ajax-popup="true" data-size="lg" data-title="{{ __('Edit Lead') }}"
                data-url="{{ URL::to('leads/' . $lead->id . '/edit') }}"><i class="ti ti-pencil text-white"></i></a>
        @endpermission

        @permission('lead to deal convert')
            @if (!empty($deal))
                <a href="@permission('deal show') @if ($deal->is_active) {{ route('deals.show', $deal->id) }} @else # @endif @else # @endpermission"
                    class="btn btn-sm btn-primary btn-icon " data-bs-toggle="tooltip" data-bs-placement="top"
                    title="{{ __('Already Converted To Deal') }}"><i class="ti ti-exchange text-white"></i></a>
            @else
                <a class="btn btn-sm btn-primary btn-icon " data-bs-toggle="tooltip" data-bs-placement="top"
                    title="{{ __('Convert To Deal') }}" data-ajax-popup="true" data-size="md"
                    data-title="{{ __('Convert [') . $lead->subject . '] To Deal' }}"
                    data-url="{{ URL::to('leads/' . $lead->id . '/show_convert') }}"><i class="ti ti-exchange text-white"></i></a>
            @endif
        @endpermission
    </div>
@endsection

@section('content')
    {{-- ===== STAGE PROGRESSION STEPPER ===== --}}
    @php
        $user          = Auth::user();
        $allStages     = \Workdo\Lead\Entities\LeadStage::where('pipeline_id', $lead->pipeline_id)
                            ->orderBy('order')
                            ->get();
        $visibleStages = $allStages->filter(fn($s) => $s->permissions($user)->can_view);
        $currentOrder  = $lead->stage ? $lead->stage->order : 0;
        $canMoveCurrent = $lead->stage ? $lead->stage->permissions($user)->can_move : false;
    @endphp
    @if($visibleStages->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card stepper-card p-3">
                <div class="d-flex align-items-center justify-content-between mb-2 px-1">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,var(--theme-emerald),#047857);display:flex;align-items:center;justify-content:center;">
                            <i class="ti ti-route text-white" style="font-size:14px;"></i>
                        </div>
                        <div>
                            <div style="font-size:0.68rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:#94a3b8;">{{ __('Lead Journey') }}</div>
                            <div style="font-size:0.78rem;font-weight:700;color:#1e293b;line-height:1.1;">{{ $lead->pipeline->name ?? 'Pipeline' }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span style="font-size:0.68rem;color:#94a3b8;font-weight:600;">{{ $lead->stage->name ?? '—' }}</span>
                        <span class="badge rounded-pill text-white" style="background:linear-gradient(135deg,var(--theme-emerald),#047857);font-size:0.65rem;padding:3px 10px;">{{ $visibleStages->count() }} {{ __('stages') }}</span>
                    </div>
                </div>
                <div class="stepper-track px-1">
                    @foreach($visibleStages as $stage)
                        @php
                            $perm     = $stage->permissions($user);
                            $isActive = ($stage->id == $lead->stage_id);
                            $isDone   = ($stage->order < $currentOrder);
                            $canMove  = ($perm->can_move && $canMoveCurrent && !$isActive);
                            $stageClass = $isActive ? 'active' : ($isDone ? 'completed' : '');
                            $nodeClass  = $isActive ? 'active' : ($isDone ? 'completed' : ($canMove ? 'can-move' : 'locked'));
                        @endphp
                        <div class="stepper-item {{ $stageClass }}">
                            @if($canMove)
                                <div class="stepper-node {{ $nodeClass }} btn-move-stage"
                                     data-lead-id="{{ $lead->id }}"
                                     data-stage-id="{{ $stage->id }}"
                                     data-stage-name="{{ $stage->name }}"
                                     data-bs-toggle="tooltip"
                                     title="{{ __('Move to: ') }}{{ $stage->name }}">
                                    <i class="ti ti-{{ $isDone ? 'check' : 'arrow-right' }}"></i>
                                </div>
                            @elseif($isActive)
                                <div class="stepper-node active" data-bs-toggle="tooltip" title="{{ __('Current Stage') }}">
                                    <i class="ti ti-map-pin"></i>
                                </div>
                            @else
                                <div class="stepper-node {{ $nodeClass }}" data-bs-toggle="tooltip" title="{{ $perm->can_move ? $stage->name : __('No permission to move here') }}">
                                    {{ $isDone ? '' : '' }}
                                    @if(!$perm->can_move)
                                        <i class="ti ti-lock"></i>
                                        <div class="lock-overlay"><i class="ti ti-lock"></i></div>
                                    @elseif($isDone)
                                        <i class="ti ti-check"></i>
                                    @else
                                        <i class="ti ti-circle"></i>
                                    @endif
                                </div>
                            @endif
                            <div class="stepper-label" title="{{ $stage->name }}">{{ $stage->name }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-12 mb-3">
            <div class="row">
                <div class="col-xl-3 col-lg-4 col-md-4 col-12">
                    <div class="card sticky-top border-0 shadow-sm" style="top:30px">
                        <div class="list-group list-group-flush rounded-3 p-3" id="useradd-sidenav">
                            <a class="list-group-item list-group-item-action border-0 d-flex align-items-center justify-content-between px-3 py-2 active" href="#general">
                                <span class="d-flex align-items-center"><i class="ti ti-info-circle me-2"></i> {{ __('General') }}</span>
                                <i class="ti ti-chevron-right text-white" style="font-size: 0.8rem;"></i>
                            </a>

                            @if(isset($leadSections))
                                @foreach($leadSections as $section)
                                    @php
                                        $secVisibility = !empty($section->visible_stages) ? ($section->visible_stages[$lead->stage_id] ?? 'visible') : 'visible';
                                    @endphp
                                    @if($secVisibility !== 'hidden')
                                        <a class="list-group-item list-group-item-action border-0 d-flex align-items-center justify-content-between px-3 py-2"
                                            href="#section-{{ $section->id }}">
                                            <span class="d-flex align-items-center"><i class="ti ti-folder me-2"></i> {{ ucwords(strtolower(trim($section->name))) }}</span>
                                            <div class="float-end"><i class="ti ti-chevron-right" style="font-size: 0.8rem;"></i></div>
                                        </a>
                                    @endif
                                @endforeach
                            @endif

                            @if (!Auth::user()->hasRole('client'))
                                <a class="list-group-item list-group-item-action border-0 d-flex align-items-center justify-content-between px-3 py-2"
                                    href="#tasks">
                                    <span class="d-flex align-items-center"><i class="ti ti-list-check me-2"></i> {{ __('Tasks') }}</span>
                                    <div class="float-end"><i class="ti ti-chevron-right" style="font-size: 0.8rem;"></i></div>
                                </a>
                                <a class="list-group-item list-group-item-action border-0 d-flex align-items-center justify-content-between px-3 py-2"
                                    href="#reminders">
                                    <span class="d-flex align-items-center"><i class="ti ti-bell me-2"></i> {{ __('Reminders') }}</span>
                                    <div class="float-end"><i class="ti ti-chevron-right" style="font-size: 0.8rem;"></i></div>
                                </a>
                                <a class="list-group-item list-group-item-action border-0 d-flex align-items-center justify-content-between px-3 py-2"
                                    href="#calls">
                                    <span class="d-flex align-items-center"><i class="ti ti-phone-call me-2"></i> {{ __('Calls') }}</span>
                                    <div class="float-end"><i class="ti ti-chevron-right" style="font-size: 0.8rem;"></i></div>
                                </a>
                                <a class="list-group-item list-group-item-action border-0 d-flex align-items-center justify-content-between px-3 py-2"
                                    href="#activity">
                                    <span class="d-flex align-items-center"><i class="ti ti-activity me-2"></i> {{ __('Activity') }}</span>
                                    <div class="float-end"><i class="ti ti-chevron-right" style="font-size: 0.8rem;"></i></div>
                                </a>

                            @endif
                             @if(isset($activeOrionRule) && $activeOrionRule && Auth::user()->type == 'company')
                                <a class="list-group-item list-group-item-action border-0 d-flex align-items-center justify-content-between px-3 py-2"
                                    href="#orion-sync-logs">
                                    <span class="d-flex align-items-center"><i class="ti ti-refresh me-2"></i> {{ __('Sync Logs') }}</span>
                                    <div class="d-flex align-items-center">
                                        @if(isset($orionLogs) && $orionLogs->count() > 0)
                                            <span class="badge bg-warning text-dark rounded-pill me-1" style="font-size: 0.65rem;">{{ $orionLogs->count() }}</span>
                                        @endif
                                        <i class="ti ti-chevron-right" style="font-size: 0.8rem;"></i>
                                    </div>
                                </a>
                            @endif
                            @stack('indiamart_tab')

                        </div>
                    </div>
                </div>

                <div class="col-xl-9 col-lg-8 col-md-8 col-12">
                    @php
                        $kycComments = $lead->discussions->where('is_kyc', 1);
                        $latestKyc = $kycComments->first();
                        
                        // Use the model helper to check if I am responsible
                        $isResponsible = $lead->isResponsible();
                        
                        // Was the last comment made by someone other than the owner, creator, or assigned users?
                        // (Meaning it's an external/system alert for the responsible team)
                        $lastCommentByResponsible = $latestKyc ? $lead->isResponsible($latestKyc->user) : false;

                        // Show Alert ONLY if:
                        // 1. There are KYC comments
                        // 2. I am a responsible person (so I should care)
                        // 3. The last comment was NOT by a responsible person (meaning it needs attention)
                        $showAlert = ($kycComments->count() > 0 && $isResponsible && !$lastCommentByResponsible);
                    @endphp

                    @if($showAlert)
                        <div class="alert alert-important alert-warning alert-dismissible fade show mb-3 shadow-sm border-0" role="alert" style="border-left: 5px solid #ffa21d !important;">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-shield-alert me-3 fs-3 text-warning"></i>
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="text-dark">{{ __('KYC Alert!') }}</strong>
                                        <small class="text-muted">{{ $latestKyc->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="text-dark mb-1">
                                        {{ __('Latest KYC Comment by') }} <strong>{{ $latestKyc->user->name }}</strong>:
                                        <span class="ms-1 italic text-muted">"{{ $latestKyc->comment }}"</span>
                                    </div>
                                    <a href="#kyc-discussions" class="btn btn-xs btn-warning text-white rounded-pill mt-1" style="font-size: 0.75rem; padding: 2px 10px;">{{ __('Review All') }} ({{ $kycComments->count() }})</a>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($overdueTasksCount > 0 || $todayRemindersCount > 0)
                        <div class="row">
                            <div class="col-12">
                                @if($overdueTasksCount > 0)
                                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-2" role="alert">
                                        <i class="ti ti-alert-triangle me-2"></i>
                                        <div>
                                            <strong>{{ __('Overdue Tasks!') }}</strong> {{ __('You have') }} {{ $overdueTasksCount }} {{ __('overdue task(s) for this lead.') }}
                                            <a href="#tasks" class="alert-link ms-2">{{ __('View Tasks') }}</a>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                @if($todayRemindersCount > 0)
                                    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center mb-2" role="alert">
                                        <i class="ti ti-bell me-2"></i>
                                        <div>
                                            <strong>{{ __('Upcoming Reminders!') }}</strong> {{ __('You have') }} {{ $todayRemindersCount }} {{ __('reminder(s) scheduled for today.') }}
                                            <a href="#reminders" class="alert-link ms-2">{{ __('View Reminders') }}</a>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    <div id="general">
                        <?php
                        // Use $tasks passed from controller to respect visibility scope
                        $products = $lead->products();
                        $sources = $lead->sources();
                        $calls = $lead->calls;
                        $emails = $lead->emails;

                        $user = Auth::user();
                        $pipelineStages = \Workdo\Lead\Entities\LeadStage::where('pipeline_id', $lead->pipeline_id)
                            ->where('created_by', $lead->created_by)
                            ->orderBy('order')
                            ->get();
                        
                        $visibleStages = $pipelineStages->filter(function($st) use ($user) {
                            return $st->permissions($user)->can_view;
                        });

                        $currentStage = $lead->stage;
                        $canMoveFromCurrent = $currentStage ? $currentStage->permissions($user)->can_move : true;
                        ?>

                        <!-- Hero Header -->
                        <div class="card hero-gradient text-white mb-4 shadow-lg border-0 fade-in-up">
                            <div class="card-body hero-pattern p-4">
                                <div class="row align-items-center position-relative" style="z-index: 1;">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="px-3 py-1 rounded-pill bg-white-10 border border-white-20 backdrop-blur d-flex align-items-center shadow-sm">
                                                <i class="ti ti-hash text-warning me-2 f-12"></i>
                                                <span class="text-white fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">{{ '#' . $lead->id }}</span>
                                            </div>
                                            <div class="ms-3 h-px-20 border-start border-white-20"></div>
                                            <div class="ms-3 badge rounded-pill bg-white-10 text-white border border-white-10 backdrop-blur" style="font-size: 0.7rem; font-weight: 500; height: 26px; display: inline-flex; align-items: center;">
                                                <i class="ti ti-timeline me-1"></i> {{ $lead->pipeline->name ?? __('Pipeline') }}
                                            </div>
                                        </div>
                                        <h1 class="text-white mb-2 fw-800 display-5" style="letter-spacing: -1px; text-shadow: 0 2px 4px rgba(0,0,0,0.1); font-weight: 800;">{{ $lead->name }}</h1>
                                        <div class="d-flex align-items-center text-white-50">
                                            <div class="d-flex align-items-center me-4">
                                                <i class="ti ti-calendar-event me-2 opacity-50"></i>
                                                <span class="text-xs fw-500">{{ __('Created') }}: <span class="text-white">{{ company_date_formate($lead->created_at) }}</span></span>
                                            </div>
                                            @if($lead->owner)
                                                <div class="d-flex align-items-center">
                                                    <i class="ti ti-user-check me-2 opacity-50"></i>
                                                    <span class="text-xs fw-500">{{ __('Creator') }}: <span class="text-white">{{ $lead->createdBy->name ?? '-' }}</span></span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mt-4 mt-md-0 d-flex justify-content-md-end align-items-center gap-3 flex-wrap">
                                         <!-- Stage Dropdown Selector -->
                                         <div class="dropdown p-3 rounded-4 bg-white-10 backdrop-blur border border-white-10 shadow-sm text-start position-relative dropdown-stage-changer" style="min-width: 170px; background: rgba(255, 255, 255, 0.08) !important; border: 1px solid rgba(255, 255, 255, 0.12) !important; cursor: pointer; transition: all 0.3s ease;" data-bs-toggle="dropdown" aria-expanded="false" data-bs-offset="0,8">
                                            <label class="text-white-50 text-uppercase fw-800 d-block mb-1.5" style="font-size: 0.62rem; letter-spacing: 1px; cursor: pointer; font-weight: 800;">
                                                {{ __('Current Stage') }} <i class="ti ti-chevron-down ms-1 fs-10 text-white-50" style="vertical-align: middle;"></i>
                                            </label>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 bg-warning text-white rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; transition: all 0.3s ease;">
                                                    <i class="ti ti-target f-16"></i>
                                                </div>
                                                <h5 class="text-white mb-0 ms-2 fw-bold text-truncate" style="font-size: 0.95rem; max-width: 120px;" data-bs-toggle="tooltip" title="{{ $lead->stage?->name ?? '-' }}">{{ $lead->stage?->name ?? '-' }}</h5>
                                            </div>
                                            
                                            <!-- Dropdown Menu -->
                                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 py-2 border-emerald-light fade-in-dropdown" style="min-width: 260px; max-height: 380px; overflow-y: auto; background: #ffffff; border: 1px solid rgba(5, 150, 105, 0.1) !important; z-index: 1050;">
                                                <li class="px-3 py-2 border-bottom mb-1 bg-light d-flex align-items-center">
                                                    <i class="ti ti-route text-success me-2 fs-5"></i>
                                                    <span class="text-uppercase fw-800 text-dark" style="font-size: 0.72rem; letter-spacing: 0.5px;">{{ __('Change Lead Stage') }}</span>
                                                </li>
                                                @foreach($visibleStages as $st)
                                                    @php
                                                        $isCurrent = ($st->id == $lead->stage_id);
                                                        $canMoveTo = ($canMoveFromCurrent && $st->permissions($user)->can_move && !$isCurrent);
                                                    @endphp
                                                    <li class="px-1">
                                                        @if($canMoveTo)
                                                            <a class="dropdown-item d-flex align-items-center justify-content-between px-3 py-2 rounded-3 btn-change-stage" href="javascript:void(0);" data-stage-id="{{ $st->id }}" data-stage-name="{{ $st->name }}" style="transition: all 0.2s ease;">
                                                                <span class="d-flex align-items-center">
                                                                    <span class="rounded-circle me-2 d-inline-block bg-primary-subtle" style="width: 6px; height: 6px;"></span>
                                                                    <span class="text-dark fw-500" style="font-size: 0.85rem;">{{ $st->name }}</span>
                                                                </span>
                                                                <i class="ti ti-arrow-right text-primary opacity-50 fs-6 hover-slide-icon"></i>
                                                            </a>
                                                        @elseif($isCurrent)
                                                            <a class="dropdown-item d-flex align-items-center justify-content-between px-3 py-2 active bg-success-subtle text-success disabled rounded-3" href="javascript:void(0);" style="cursor: default; pointer-events: none;">
                                                                <span class="d-flex align-items-center">
                                                                    <i class="ti ti-circle-check-filled text-success me-2 fs-5"></i>
                                                                    <span class="fw-bold" style="font-size: 0.85rem;">{{ $st->name }}</span>
                                                                </span>
                                                                <span class="badge bg-success text-white px-2 py-0.5 rounded-pill" style="font-size: 0.65rem;">{{ __('Current') }}</span>
                                                            </a>
                                                        @else
                                                            <a class="dropdown-item d-flex align-items-center justify-content-between px-3 py-2 text-muted disabled rounded-3" href="javascript:void(0);" style="opacity: 0.55; cursor: not-allowed; pointer-events: none;">
                                                                <span class="d-flex align-items-center">
                                                                    <i class="ti ti-lock me-2 text-muted fs-6"></i>
                                                                    <span style="font-size: 0.85rem;">{{ $st->name }}</span>
                                                                </span>
                                                                <span class="text-danger fw-bold" style="font-size: 0.68rem;"><i class="ti ti-circle-x me-0.5"></i>{{ __('Locked') }}</span>
                                                            </a>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                         </div>

                                         <!-- Responsible Person Highlight -->
                                         <div class="p-3 rounded-4 bg-white shadow-lg text-start position-relative overflow-hidden responsible-glow" style="min-width: 220px; transition: all 0.35s ease;">
                                            <div class="position-absolute top-0 end-0 p-1 opacity-5">
                                                <i class="ti ti-crown fs-1" style="transform: rotate(15deg); color: #ffc107;"></i>
                                            </div>
                                            <label class="text-success text-uppercase fw-800 d-block mb-2" style="font-size: 0.65rem; letter-spacing: 1px; opacity: 0.85; color: var(--theme-emerald) !important;">{{ __('Responsible Person') }}</label>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-group d-flex align-items-center me-2">
                                                    @if($lead->owner)
                                                        <div class="position-relative" data-bs-toggle="tooltip" title="{{ $lead->owner->name }} ({{ __('Primary Owner') }})">
                                                            <img src="{{ get_file(!empty($lead->owner->avatar) ? $lead->owner->avatar : 'uploads/users-avatar/avatar.png') }}" 
                                                                 class="rounded-circle border border-2 border-warning shadow-sm" 
                                                                 style="width: 36px; height: 36px; z-index: 50; position: relative;">
                                                            <div class="position-absolute bottom-0 end-0 bg-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 14px; height: 14px; border: 2px solid #fff; z-index: 51;">
                                                                <i class="ti ti-crown text-white" style="font-size: 7px;"></i>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                @if($lead->owner)
                                                    <div class="ms-1 overflow-hidden">
                                                        <span class="text-dark fw-800 d-block text-truncate" style="font-size: 11px; max-width: 120px; font-weight: 700;">{{ explode(' ', $lead->owner->name)[0] }}</span>
                                                        <span class="text-muted d-block" style="font-size: 9px;">{{ __('Lead Owner') }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted text-xs ms-1">{{ __('Unassigned') }}</span>
                                                @endif
                                            </div>
                                         </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Grid -->
                        <div class="row mb-4 g-3">
                            <!-- Contact Card -->
                            <div class="col-md-6 col-lg-4 fade-in-up delay-100">
                                <div class="card card-modern h-100 border-0" style="box-shadow: 0 4px 20px rgba(15,23,42,0.05);">
                                    <div class="card-body p-3 position-relative overflow-hidden">
                                        <div class="stat-card-accent bg-success"></div>
                                        <div class="d-flex align-items-start mb-3 ms-2">
                                            <h6 class="mb-0 fw-800 text-uppercase" style="font-size: 0.68rem; letter-spacing: 1px; color: #94a3b8;">{{ __('Contact Info') }}</h6>
                                        </div>
                                        <div class="contact-info-row d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0 icon-shape-lg bg-success-subtle text-success me-3" style="width:42px;height:42px;font-size:18px;border-radius:12px;">
                                                <i class="ti ti-mail"></i>
                                            </div>
                                            <div class="overflow-hidden">
                                                <div class="stat-label text-success mb-0.5">{{ __('Email') }}</div>
                                                <div class="fw-700 text-dark text-truncate" style="font-size:0.85rem;max-width:180px;">{!! \Workdo\Lead\Entities\LeadUtility::getFieldDisplay($lead, 'email', $lead->email) !!}</div>
                                            </div>
                                        </div>
                                        <div class="contact-info-row d-flex align-items-center">
                                            <div class="flex-shrink-0 icon-shape-lg bg-danger-subtle text-danger me-3" style="width:42px;height:42px;font-size:18px;border-radius:12px;">
                                                <i class="ti ti-phone"></i>
                                            </div>
                                            <div class="overflow-hidden">
                                                <div class="stat-label text-danger mb-0.5">{{ __('Phone') }}</div>
                                                <div class="fw-700 text-dark d-flex align-items-center gap-2" style="font-size:0.85rem;">
                                                    {{ $lead->phone ?: __('—') }}
                                                    @if($lead->phone)
                                                        <a href="javascript:void(0)" class="text-primary click-to-call" data-phone="{{$lead->phone}}" data-bs-toggle="tooltip" title="{{ __('Call') }}"><i class="ti ti-phone-call"></i></a>
                                                        <a href="{{ route('whatsapp.chat.index', ['lead_id' => $lead->id]) }}" class="text-success" data-bs-toggle="tooltip" title="{{ __('WhatsApp') }}"><i class="ti ti-brand-whatsapp"></i></a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Progress Card -->
                            <div class="col-md-6 col-lg-4 fade-in-up delay-200">
                                <div class="card card-modern h-100 border-0" style="box-shadow: 0 4px 20px rgba(15,23,42,0.05);">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <h6 class="mb-0 fw-800 text-uppercase" style="font-size: 0.68rem; letter-spacing: 1px; color: #94a3b8;">{{ __('Conversion Probability') }}</h6>
                                            <div class="icon-shape-lg bg-primary-subtle text-primary" style="width:36px;height:36px;font-size:16px;border-radius:10px;">
                                                <i class="ti ti-chart-pie"></i>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-end justify-content-between mt-1 mb-2">
                                            <span class="stat-quick-number" style="font-size:2.5rem;">{{ $percentage }}<span style="font-size:1rem;font-weight:600;color:#64748b;">%</span></span>
                                            <span class="badge rounded-pill text-white" style="background: linear-gradient(135deg,var(--theme-emerald),#047857);font-size:0.7rem;padding:5px 12px;">
                                                @if($percentage >= 70) 🔥 High
                                                @elseif($percentage >= 40) ⚡ Medium
                                                @else 🧊 Low
                                                @endif
                                            </span>
                                        </div>
                                        <div class="progress-modern w-100 mb-1">
                                            <div class="progress-bar-modern" role="progressbar" style="width: {{ $percentage }}%;"></div>
                                        </div>
                                        <small class="text-muted" style="font-size:0.68rem;">{{ __('Based on completed tasks and stage') }}</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Quick Stats -->
                            <div class="col-md-12 col-lg-4 fade-in-up delay-300">
                                <div class="card card-modern h-100 border-0" style="box-shadow: 0 4px 20px rgba(15,23,42,0.05);">
                                    <div class="card-body p-3">
                                        <h6 class="mb-3 fw-800 text-uppercase" style="font-size: 0.68rem; letter-spacing: 1px; color: #94a3b8;">{{ __('Quick Stats') }}</h6>
                                        <div class="d-flex align-items-stretch justify-content-around h-100" style="min-height: 100px;">
                                            <div class="text-center flex-fill">
                                                <div class="icon-shape-lg bg-warning-subtle text-warning mx-auto mb-2" style="width:44px;height:44px;font-size:20px;border-radius:12px;">
                                                    <i class="ti ti-social"></i>
                                                </div>
                                                <span class="stat-quick-number">{{ count($sources) }}</span>
                                                <span class="stat-label text-muted d-block" style="font-size:0.7rem;">{{ __('Sources') }}</span>
                                            </div>
                                            <div class="stat-divider-v mx-3"></div>
                                            <div class="text-center flex-fill">
                                                <div class="icon-shape-lg bg-success-subtle text-success mx-auto mb-2" style="width:44px;height:44px;font-size:20px;border-radius:12px;">
                                                    <i class="ti ti-phone"></i>
                                                </div>
                                                <span class="stat-quick-number">{{ count($calls ?? []) }}</span>
                                                <span class="stat-label text-muted d-block" style="font-size:0.7rem;">{{ __('Calls') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                                               @if(isset($leadSections) && $leadSections->count() > 0)
                            @foreach($leadSections as $section)
                                @php
                                    $secVisibility = !empty($section->visible_stages) ? ($section->visible_stages[$lead->stage_id] ?? 'visible') : 'visible';
                                @endphp
                                @if($secVisibility === 'hidden')
                                    @continue
                                @endif
                                @php
                                    $layoutType = $section->layout_type ?? 'section';
                                    $sectionClass = 'section-layout-standard';
                                    $iconShapeClass = 'bg-secondary-subtle text-secondary';
                                    $iconClass = 'ti-folder';
                                    if ($layoutType === 'card') {
                                        $sectionClass = 'section-layout-card';
                                        $iconShapeClass = 'bg-primary-subtle text-primary';
                                        $iconClass = 'ti-id-badge';
                                    } elseif ($layoutType === 'bento') {
                                        $sectionClass = 'section-layout-bento';
                                        $iconShapeClass = 'bg-success-subtle text-success';
                                        $iconClass = 'ti-layout-grid';
                                    }

                                    // Dynamic percentage calculation for fields in this section
                                    $totalFields = 0;
                                    $filledFields = 0;
                                    $hasApi = false;
                                    foreach($section->fields as $f) {
                                        if (!empty($f->visible_stages) && !in_array($lead->stage_id, $f->visible_stages)) { continue; }
                                        if (!empty($f->visible_roles)) {
                                            $userRoleIds = Auth::user()->roles->pluck('id')->toArray();
                                            if (empty(array_intersect($userRoleIds, $f->visible_roles))) { continue; }
                                        }
                                        $totalFields++;
                                        $val = $f->is_system ? $lead->{$f->system_field_id} : ($leadCustomFieldValues[$f->id] ?? '');
                                        if ($val !== null && $val !== '' && $val !== '-') {
                                            $filledFields++;
                                        }
                                        if (!empty($f->api_url)) {
                                            $hasApi = true;
                                        }
                                    }
                                    $sectionPercentage = $totalFields > 0 ? round(($filledFields / $totalFields) * 100) : 0;
                                @endphp
                                <div class="card card-modern mb-4 shadow-sm border-0 fade-in-up {{ $sectionClass }}" id="section-{{ $section->id }}">
                                    <div class="card-body p-4">
                                        <h5 class="mb-4 d-flex align-items-center section-title w-100">
                                            <span class="icon-shape {{ $iconShapeClass }} rounded-circle me-3" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                                <i class="ti {{ $iconClass }}"></i>
                                            </span>
                                            <span class="fw-bold">{{ ucwords(strtolower(trim($section->name))) }}</span>
                                            @if($hasApi)
                                                <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-light-success ms-2 sync-section-api-btn" 
                                                   data-section-id="{{ $section->id }}" 
                                                   data-lead-id="{{ $lead->id }}"
                                                   data-bs-toggle="tooltip" 
                                                   title="{{ __('Sync API Data') }}"
                                                   style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; padding: 0;">
                                                    <i class="ti ti-refresh text-success fs-5"></i>
                                                </a>
                                            @endif
                                            @if(stripos($section->name, 'client summary') !== false && $activeOrionRule)
                                                <button type="button" class="btn btn-xs btn-light-primary ms-auto d-inline-flex align-items-center btn-orion-ekyc-fetch-trigger me-2" 
                                                   data-client-code="{{ $clientCodeValue }}" 
                                                   data-rule-id="{{ $activeOrionRule['id'] }}"
                                                   data-bs-toggle="tooltip" 
                                                   title="{{ __('Fetch Orion EKYC Data') }}"
                                                   style="border-radius: 6px; font-size: 0.72rem; font-weight: 600; padding: 4px 10px;">
                                                    <i class="ti ti-cloud-download me-1.5 fs-6 text-primary"></i>
                                                    <span class="text-primary">{{ __('Fetch') }}</span>
                                                </button>
                                            @endif
                                            @if($layoutType === 'card')
                                                <i class="ti ti-circle-check-filled text-primary fs-4 ms-2" data-bs-toggle="tooltip" title="Verified"></i>
                                                @if(stripos($section->name, 'basic') !== false)
                                                    <span class="badge bg-light-primary text-primary ms-3 border border-primary border-opacity-25 rounded-pill px-3 py-1 text-capitalize" style="font-size: 0.75rem; font-weight: 500;">Existing and Valid. Aadhaar Seeding is Successful.</span>
                                                @elseif(stripos($section->name, 'address') !== false)
                                                    <span class="badge bg-light-primary text-primary ms-3 border border-primary border-opacity-25 rounded-pill px-3 py-1 text-capitalize" style="font-size: 0.75rem; font-weight: 500;">Address Verification is Successful.</span>
                                                @endif
                                                <span class="badge bg-success {{ stripos($section->name, 'client summary') !== false ? 'ms-2' : 'ms-auto' }} rounded-pill px-3 py-1" style="font-size: 0.75rem; font-weight: 600;">{{ $sectionPercentage }}%</span>
                                            @endif
                                        </h5>
                                        <div class="row g-3">
                                            @foreach($section->fields as $field)
                                                @php
                                                    // VISIBILITY CHECKS
                                                    if (!empty($field->visible_stages) && !in_array($lead->stage_id, $field->visible_stages)) { continue; }
                                                    if (!empty($field->visible_roles)) {
                                                        $userRoleIds = Auth::user()->roles->pluck('id')->toArray();
                                                        if (empty(array_intersect($userRoleIds, $field->visible_roles))) { continue; }
                                                    }
                                                    
                                                    // DYNAMIC BENTO GRID WIDTH CALCULATION
                                                    $sectionCols = $section->columns > 0 ? $section->columns : 3;
                                                    $fieldWidth = $field->width > 0 ? $field->width : 1;
                                                    $calculatedGridCols = min(12, (12 / $sectionCols) * $fieldWidth);
                                                    $colClass = 'col-md-'.(int)$calculatedGridCols . ' col-sm-12';
                                                    
                                                    // Determine bento card style based on size
                                                    $isLargeCard = $calculatedGridCols >= 8;
                                                    
                                                    // Determine layout classes
                                                    if ($layoutType === 'card') {
                                                        $cardClass = 'premium-card' . ($isLargeCard ? ' premium-card-large' : '');
                                                        $iconContainerClass = 'premium-icon-container';
                                                    } elseif ($layoutType === 'bento') {
                                                        $cardClass = 'bento-card' . ($isLargeCard ? ' bento-card-large' : '');
                                                        $iconContainerClass = 'bento-icon-container';
                                                    } else {
                                                        $cardClass = 'standard-card' . ($isLargeCard ? ' standard-card-large' : '');
                                                        $iconContainerClass = 'standard-icon-container';
                                                    }
                                                @endphp
                                                <div class="{{ $colClass }}">
                                                    @if($layoutType === 'card')
                                                        {{-- Clean row layout for premium card mode --}}
                                                        <div class="py-3 px-2 border-bottom d-flex align-items-center justify-content-between flex-wrap" style="border-color: #edf2f7 !important; min-height: 52px;">
                                                            <small class="text-muted fw-bold text-xs text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem; min-width: 140px;">{{ $field->name }}</small>
                                                            <div class="d-flex align-items-center text-end flex-grow-1 justify-content-end">
                                                                @php
                                                                    $canEditInline = (!$field->is_system || in_array($field->system_field_id, ['email', 'phone', 'pan_number', 'aadhar_number'])) && Auth::user()->isAbleTo('lead edit');
                                                                    $rawVal = $field->is_system ? $lead->{$field->system_field_id} : ($leadCustomFieldValues[$field->id] ?? '');
                                                                    
                                                                    // Add custom green class for premium style matched fields or values containing X
                                                                    $textClass = 'text-dark';
                                                                    if (strpos(strtoupper($rawVal), 'XXXX') !== false || in_array(strtolower($field->name), ['full name', 'name as per it site', 'name as per esign', 'political relation'])) {
                                                                        $textClass = 'text-success';
                                                                    }
                                                                @endphp
                                                                
                                                                @php
                                                                    $isEyeToggle = ($secVisibility === 'eye_toggle');
                                                                    $hasRealValue = ($rawVal !== null && $rawVal !== '' && $rawVal !== '-' && strtolower($rawVal) !== 'not provided');
                                                                @endphp
                                                                
                                                                @if($isEyeToggle && $hasRealValue)
                                                                    <span class="secure-reveal-wrapper d-inline-flex align-items-center justify-content-end w-100">
                                                                        <span class="revealed-value d-none w-100 text-end">
                                                                @endif
                                                                
                                                                @if($canEditInline)
                                                                    <span class="fs-6 fw-bold {{ $textClass }} text-break editable-field w-100"
                                                                          data-name="{{ $field->is_system ? $field->system_field_id : $field->id }}"
                                                                          data-system="{{ $field->is_system ? 1 : 0 }}"
                                                                          data-type="{{ $field->type }}"
                                                                          data-options="{{ $field->options ?? '' }}"
                                                                          data-value="{{ $rawVal }}">
                                                                @else
                                                                    <span class="fs-6 fw-bold {{ $textClass }} text-break">
                                                                @endif
                                 
                                                                    @if($field->is_system)
                                                                        @switch($field->system_field_id)
                                                                            @case('email') 
                                                                                @if($lead->email)
                                                                                    <a href="mailto:{{ $lead->email }}" class="text-primary hover-underline">{{ $lead->email }}</a>
                                                                                @else
                                                                                    <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                                @endif
                                                                            @break
                                                                            @case('phone') 
                                                                                @if($lead->phone)
                                                                                    {{ $lead->phone }}
                                                                                    <a href="javascript:void(0)" class="ms-1 text-primary click-to-call" data-phone="{{$lead->phone}}" data-bs-toggle="tooltip" title="{{ __('Call') }}">
                                                                                        <i class="ti ti-phone-call"></i>
                                                                                    </a>
                                                                                    <a href="{{ route('whatsapp.chat.index', ['lead_id' => $lead->id]) }}" class="ms-1 text-success" data-bs-toggle="tooltip" title="{{ __('WhatsApp Chat') }}">
                                                                                        <i class="ti ti-brand-whatsapp"></i>
                                                                                    </a>
                                                                                @else
                                                                                    <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                                @endif
                                                                            @break
                                                                            @case('pipeline') {{ $lead->pipeline->name ?? '-' }} @break
                                                                            @case('stage') {{ $lead->stage?->name ?? '-' }} @break
                                                                            @case('created_at') {{ company_date_formate($lead->created_at) }} @break
                                                                            @case('percentage') {{ $percentage }}% @break
                                                                            @case('pan_number') 
                                                                                @if($lead->pan_number)
                                                                                    {{ $lead->pan_number }}
                                                                                @else
                                                                                    <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                                @endif
                                                                            @break
                                                                            @case('aadhar_number') 
                                                                                @if($lead->aadhar_number)
                                                                                    {{ $lead->aadhar_number }}
                                                                                @else
                                                                                    <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                                @endif
                                                                            @break
                                                                            @default -
                                                                        @endswitch
                                                                    @else
                                                                        @php $value = $leadCustomFieldValues[$field->id] ?? ''; @endphp
                                                                        @if($value === '-' || empty($value))
                                                                            <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                        @elseif($field->type == 'multi_select')
                                                                            @foreach(explode(',', $value) as $item)
                                                                                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-1 me-1">{{ $item }}</span>
                                                                            @endforeach
                                                                        @elseif($field->type == 'file')
                                                                            <a href="{{ asset('storage/uploads/custom_fields/'.$value) }}" download class="btn btn-xs btn-outline-success rounded-pill">
                                                                                <i class="ti ti-download me-1"></i> {{ __('Download') }}
                                                                            </a>
                                                                        @else
                                                                            {{ $value }}
                                                                        @endif
                                                                    @endif
                                                                </span>
                                                                @if($isEyeToggle && $hasRealValue)
                                                                        </span>
                                                                        <span class="masked-value text-muted fw-bold">••••••••</span>
                                                                        <button type="button" class="btn btn-xs btn-link p-0 ms-2 toggle-secure-reveal-btn text-primary" title="{{ __('Reveal') }}">
                                                                            <i class="ti ti-eye fs-5"></i>
                                                                        </button>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        {{-- Original Card layouts (Bento/Standard) --}}
                                                        @php
                                                            $isFileCard = ($field->type == 'file' || $field->type == 'attachment');
                                                            $fileCardClass = $isFileCard ? 'file-upload-card' : $cardClass;
                                                            $rawValForFile = $field->is_system ? $lead->{$field->system_field_id} : ($leadCustomFieldValues[$field->id] ?? '');
                                                            if ($isFileCard && !empty($rawValForFile) && $rawValForFile !== '-') {
                                                                $fileCardClass .= ' file-has-value';
                                                            }
                                                        @endphp
                                                        <div class="p-3 {{ $isFileCard ? $fileCardClass : $cardClass }} d-flex flex-column justify-content-between">
                                                            <div>
                                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                                    <small class="text-muted fw-bold text-xs text-uppercase" style="letter-spacing: 0.5px;">{{ $field->name }}</small>
                                                                    <div class="{{ $isFileCard ? 'file-icon-container' : $iconContainerClass }}">
                                                                        @if($isFileCard)
                                                                            <i class="ti ti-file-upload fs-6"></i>
                                                                        @else
                                                                            <i class="ti ti-{{ $field->icon ?? 'circle-dot' }} fs-6"></i>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                 <div class="d-flex align-items-center mt-2 w-100">
                                                                    @php
                                                                        $canEditInline = (!$field->is_system || in_array($field->system_field_id, ['email', 'phone', 'pan_number', 'aadhar_number'])) && Auth::user()->isAbleTo('lead edit');
                                                                        $rawVal = $field->is_system ? $lead->{$field->system_field_id} : ($leadCustomFieldValues[$field->id] ?? '');
                                                                        $isEyeToggle = ($secVisibility === 'eye_toggle');
                                                                        $hasRealValue = ($rawVal !== null && $rawVal !== '' && $rawVal !== '-' && strtolower($rawVal) !== 'not provided');
                                                                    @endphp
                                                                    
                                                                    @if($isEyeToggle && $hasRealValue)
                                                                        <span class="secure-reveal-wrapper d-inline-flex align-items-center justify-content-end w-100">
                                                                            <span class="revealed-value d-none w-100 text-end">
                                                                    @endif
                                                                    
                                                                    @if($canEditInline)
                                                                        <span class="{{ $isLargeCard ? 'fs-5' : 'fs-6' }} fw-bold text-dark text-break editable-field w-100"
                                                                              data-name="{{ $field->is_system ? $field->system_field_id : $field->id }}"
                                                                              data-system="{{ $field->is_system ? 1 : 0 }}"
                                                                              data-type="{{ $field->type }}"
                                                                              data-options="{{ $field->options ?? '' }}"
                                                                              data-value="{{ $rawVal }}">
                                                                    @else
                                                                        <span class="{{ $isLargeCard ? 'fs-5' : 'fs-6' }} fw-bold text-dark text-break">
                                                                    @endif
                                     
                                                                        @if($field->is_system)
                                                                            @switch($field->system_field_id)
                                                                                @case('email') 
                                                                                    @if($lead->email)
                                                                                        <a href="mailto:{{ $lead->email }}" class="text-primary hover-underline">{{ $lead->email }}</a>
                                                                                    @else
                                                                                        <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                                    @endif
                                                                                @break
                                                                                @case('phone') 
                                                                                    @if($lead->phone)
                                                                                        {{ $lead->phone }}
                                                                                        <a href="javascript:void(0)" class="ms-1 text-primary click-to-call" data-phone="{{$lead->phone}}" data-bs-toggle="tooltip" title="{{ __('Call') }}">
                                                                                            <i class="ti ti-phone-call"></i>
                                                                                        </a>
                                                                                        <a href="{{ route('whatsapp.chat.index', ['lead_id' => $lead->id]) }}" class="ms-1 text-success" data-bs-toggle="tooltip" title="{{ __('WhatsApp Chat') }}">
                                                                                            <i class="ti ti-brand-whatsapp"></i>
                                                                                        </a>
                                                                                    @else
                                                                                        <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                                    @endif
                                                                                @break
                                                                                @case('pipeline') {{ $lead->pipeline->name ?? '-' }} @break
                                                                                @case('stage') {{ $lead->stage?->name ?? '-' }} @break
                                                                                @case('created_at') {{ company_date_formate($lead->created_at) }} @break
                                                                                @case('percentage') {{ $percentage }}% @break
                                                                                @case('pan_number') 
                                                                                    @if($lead->pan_number)
                                                                                        {{ $lead->pan_number }}
                                                                                    @else
                                                                                        <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                                    @endif
                                                                                @break
                                                                                @case('aadhar_number') 
                                                                                    @if($lead->aadhar_number)
                                                                                        {{ $lead->aadhar_number }}
                                                                                    @else
                                                                                        <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                                    @endif
                                                                                @break
                                                                                @default -
                                                                            @endswitch
                                                                        @else
                                                                            @php $value = $leadCustomFieldValues[$field->id] ?? ''; @endphp
                                                                            @if($value === '-' || empty($value))
                                                                                <span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __('Not Provided') }}</span>
                                                                            @elseif($field->type == 'multi_select')
                                                                                @foreach(explode(',', $value) as $item)
                                                                                    <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-1 me-1">{{ $item }}</span>
                                                                                @endforeach
                                                                            @elseif($field->type == 'file')
                                                                                <a href="{{ asset('storage/uploads/custom_fields/'.$value) }}" download class="btn btn-xs btn-outline-success rounded-pill">
                                                                                    <i class="ti ti-download me-1"></i> {{ __('Download') }}
                                                                                </a>
                                                                                @else
                                                                                {{ $value }}
                                                                            @endif
                                                                        @endif
                                                                    </span>
                                                                    @if($isEyeToggle && $hasRealValue)
                                                                        </span>
                                                                        <span class="masked-value text-muted fw-bold">••••••••</span>
                                                                        <button type="button" class="btn btn-xs btn-link p-0 ms-2 toggle-secure-reveal-btn text-primary" title="{{ __('Reveal') }}">
                                                                            <i class="ti ti-eye fs-5"></i>
                                                                        </button>
                                                                    </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                         <div class="col-12 text-center">{{ __('No Layout Configured. Please run the seeder or configure the builder.') }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($lead->isResponsible())
                            <div id="kyc-discussions" class="mb-4 mt-4">
                                <div class="card card-modern border-0 shadow-sm">
                                    <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center pt-4 px-4">
                                        <h5 class="mb-0 section-title"><i class="ti ti-shield-check me-2"></i> {{ __('KYC Comments') }}</h5>
                                        @permission('lead kyc comment')
                                            <a href="#" class="btn btn-sm btn-success rounded-pill shadow-sm" data-url="{{ route('leads.discussions.create', $lead->id) }}?is_kyc=1" data-ajax-popup="true" data-title="{{__('Add KYC Comment')}}" data-size="md">
                                                <i class="ti ti-plus text-white"></i> {{__('Add Comment')}}
                                            </a>
                                        @endpermission
                                    </div>
                                    <div class="card-body p-4 pt-0">
                                        <ul class="list-group list-group-flush mt-3">
                                            @forelse ($kycComments as $discussion)
                                                <li class="list-group-item px-0 py-3 border-0 border-bottom">
                                                    <div class="d-flex align-items-start">
                                                        @php
                                                            $avatar = 'uploads/users-avatar/avatar.png';
                                                            if(!empty($discussion->user->avatar) && check_file($discussion->user->avatar)) {
                                                                $avatar = $discussion->user->avatar;
                                                            }
                                                        @endphp
                                                        <img src="{{ get_file($avatar) }}" 
                                                             class="rounded-circle me-3" style="width: 40px; height: 40px;" alt="avatar">
                                                        <div class="w-100">
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <h6 class="mb-0 fw-bold">{{ $discussion->user->name }}</h6>
                                                                <small class="text-muted">{{ $discussion->created_at->diffForHumans() }}</small>
                                                            </div>
                                                            <p class="text-sm text-dark mb-0">{{ $discussion->comment }}</p>
                                                        </div>
                                                    </div>
                                                </li>
                                            @empty
                                                <div class="text-center text-muted py-4">
                                                    <i class="ti ti-message-off display-6 d-block mb-3 opacity-25"></i>
                                                    <small>{{ __('No KYC comments found') }}</small>
                                                </div>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif
                    <div class="row">
                        @if (!Auth::user()->hasRole('client'))


                            <div id="tasks">
                                <div class="card card-modern border-0 shadow-sm mb-4">
                                    <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center pt-4 px-4">
                                        <h5 class="mb-0 section-title"><i class="ti ti-list-check me-2"></i> {{ __('Tasks Checklist') }}</h5>
                                        @permission('lead task create')
                                            <a class="btn btn-sm btn-success rounded-pill shadow-sm"
                                                data-bs-toggle="tooltip" 
                                                title="{{ __('Create') }}"
                                                data-url="{{ route('leads.tasks.create', $lead->id) }}"
                                                data-ajax-popup="true" data-title="{{ __('Create Task') }}"
                                                data-size="md">
                                                <i class="ti ti-plus text-white"></i> {{ __('Add Task') }}
                                            </a>
                                        @endpermission
                                    </div>
                                    <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        @forelse ($tasks as $task)
                                            <div class="list-group-item px-4 py-3 task-item border-0 border-bottom d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-start">
                                                        @permission('lead task edit')
                                                            <div class="form-check form-switch me-3 mt-1">
                                                                <input type="checkbox" class="form-check-input task-checkbox" role="switch" id="task_{{ $task->id }}" 
                                                                    @if ($task->status == 'done') checked="checked" @endif 
                                                                    value="{{ $task->status }}" 
                                                                    data-url="{{ route('leads.tasks.update.status', [$lead->id, $task->id]) }}"/>
                                                            </div>
                                                        @endpermission
                                                        <div>
                                                            <h6 class="mb-1 fw-bold {{ $task->status == 'done' ? 'text-muted text-decoration-line-through' : 'text-dark' }}">
                                                                {{ $task->name }}
                                                            </h6>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="badge rounded-pill bg-{{ $task->status == 'done' ? 'success' : ($task->status == 'overdue' ? 'danger' : 'warning') }}-subtle text-{{ $task->status == 'done' ? 'success' : ($task->status == 'overdue' ? 'danger' : 'warning') }} border border-{{ $task->status == 'done' ? 'success' : ($task->status == 'overdue' ? 'danger' : 'warning') }} border-opacity-25">
                                                                    {{ __(Workdo\Lead\Entities\LeadTask::$status[$task->status]) }}
                                                                </span>
                                                                <small class="text-muted"><i class="ti ti-calendar me-1"></i> {{ company_datetime_formate($task->date . ' ' . $task->time) }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="dropdown">
                                                         @permission('lead task edit')
                                                            <a href="#" class="action-btn btn btn-sm btn-light-secondary" 
                                                                data-url="{{ route('leads.tasks.edit', [$lead->id, $task->id]) }}"
                                                                data-ajax-popup="true" data-title="{{ __('Edit Task') }}">
                                                                <i class="ti ti-pencil"></i>
                                                            </a>
                                                         @endpermission
                                                         @permission('lead task delete')
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['leads.tasks.destroy', $lead->id, $task->id], 'id' => 'delete-form-' . $task->id, 'class' => 'd-inline']) !!}
                                                                <a href="#!" class="action-btn btn btn-sm btn-light-danger show_confirm ms-1">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                         @endpermission
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="p-4 text-center text-muted">
                                                    <i class="ti ti-check-list display-6 d-block mb-3 opacity-25"></i>
                                                    {{ __('No tasks scheduled') }}
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="reminders">
                                <div class="card card-modern border-0 shadow-sm mb-4">
                                    <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center pt-4 px-4">
                                        <h5 class="mb-0 section-title"><i class="ti ti-bell me-2"></i> {{ __('Reminders') }}</h5>
                                        <a class="btn btn-sm btn-success rounded-pill shadow-sm"
                                            data-bs-toggle="tooltip" 
                                            title="{{ __('Create') }}"
                                            data-url="{{ route('leads.reminders.create', $lead->id) }}"
                                            data-ajax-popup="true" data-title="{{ __('Create Reminder') }}"
                                            data-size="md">
                                            <i class="ti ti-plus text-white"></i> {{ __('Add Reminder') }}
                                        </a>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="list-group list-group-flush">
                                            @forelse ($lead->reminders as $reminder)
                                                @php
                                                    $remindAt = \Carbon\Carbon::parse($reminder->remind_at);
                                                    $now = now();
                                                    $colorClass = 'bg-success-subtle text-success border border-success border-opacity-25';
                                                    if($remindAt->lt($now)) {
                                                        $colorClass = 'bg-danger-subtle text-danger border border-danger border-opacity-25';
                                                    } elseif($remindAt->diffInHours($now) < 24) {
                                                        $colorClass = 'bg-warning-subtle text-warning border border-warning border-opacity-25';
                                                    }
                                                @endphp
                                                <div class="list-group-item px-4 py-3 border-0 border-bottom d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3 text-center">
                                                            <div class="badge {{ $colorClass }} rounded p-2">
                                                                <span class="d-block fw-bold display-6" style="line-height:1;">{{ $remindAt->format('d') }}</span>
                                                                <span class="text-xs text-uppercase">{{ $remindAt->format('M') }}</span>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1 fw-bold text-dark">{{ $reminder->title }}</h6>
                                                            <div class="text-xs text-muted">
                                                                <span class="me-2"><i class="ti ti-clock me-1"></i> {{ $remindAt->format('H:i A') }}</span>
                                                                <span><i class="ti ti-user me-1"></i> {{ $reminder->user->name ?? '-' }}</span>
                                                                <span class="ms-2 badge bg-secondary-subtle text-secondary rounded-pill">{{ __(ucfirst($reminder->type)) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="dropdown">
                                                        <a href="#" class="action-btn btn btn-sm btn-light-secondary" 
                                                            data-url="{{ route('leads.reminders.edit', [$lead->id, $reminder->id]) }}"
                                                            data-ajax-popup="true" data-title="{{ __('Edit Reminder') }}">
                                                            <i class="ti ti-pencil"></i>
                                                        </a>
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['leads.reminders.destroy', $lead->id, $reminder->id], 'id' => 'delete-reminder-form-' . $reminder->id, 'class' => 'd-inline']) !!}
                                                            <a href="#!" class="action-btn btn btn-sm btn-light-danger show_confirm ms-1">
                                                                <i class="ti ti-trash"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                </div>
                                            @empty
                                                 <div class="p-4 text-center text-muted">
                                                    <i class="ti ti-bell-off display-6 d-block mb-3 opacity-25"></i>
                                                    {{ __('No active reminders') }}
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="calls">
                                <div class="card card-modern border-0 shadow-sm mb-4">
                                    <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center pt-4 px-4">
                                        <h5 class="mb-0 section-title"><i class="ti ti-phone me-2"></i> {{ __('Call Logs') }}</h5>
                                        @permission('lead call create')
                                            <a class="btn btn-sm btn-success rounded-pill shadow-sm"
                                                data-bs-toggle="tooltip" 
                                                title="{{ __('Create') }}"
                                                data-url="{{ route('leads.calls.create', $lead->id) }}"
                                                data-ajax-popup="true" data-title="{{ __('Create Call') }}"
                                                data-size="md">
                                                <i class="ti ti-plus text-white"></i> {{ __('Log Call') }}
                                            </a>
                                        @endpermission
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="list-group list-group-flush">
                                            @forelse ($lead->calls as $call)
                                                <div class="list-group-item px-4 py-3 border-0 border-bottom d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon-shape bg-info-subtle text-info rounded-circle me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="ti ti-phone-call"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1 fw-bold text-dark">{{ $call->subject }}</h6>
                                                            <div class="text-xs text-muted">
                                                                <span class="me-3"><i class="ti ti-clock me-1"></i> {{ company_datetime_formate($call->duration) }}</span>
                                                                <span class="text-success">{{ $call->call_result }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="dropdown">
                                                         @permission('lead call edit')
                                                            <a href="#" class="action-btn btn btn-sm btn-light-secondary" 
                                                                data-url="{{ route('leads.calls.edit', [$lead->id, $call->id]) }}"
                                                                data-ajax-popup="true" data-title="{{ __('Edit Call') }}">
                                                                <i class="ti ti-pencil"></i>
                                                            </a>
                                                         @endpermission
                                                         @permission('lead call delete')
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['leads.calls.destroy', $lead->id, $call->id], 'id' => 'delete-form-' . $call->id, 'class' => 'd-inline']) !!}
                                                                <a href="#!" class="action-btn btn btn-sm btn-light-danger show_confirm ms-1">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                         @endpermission
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="p-4 text-center text-muted">
                                                    <i class="ti ti-phone-off display-6 d-block mb-3 opacity-25"></i>
                                                    {{ __('No calls logged') }}
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="activity">
                                <div class="card card-modern border-0 shadow-sm mb-4">
                                    <div class="card-header bg-transparent border-bottom-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                                        <h5 class="mb-0 section-title">
                                            <i class="ti ti-activity me-2"></i> {{ __('Activity Timeline') }}
                                        </h5>
                                        @if($lead->activities->count() > 0)
                                            <span class="badge bg-success-subtle text-success rounded-pill" style="font-size: 0.7rem; padding: 4px 10px;">
                                                {{ $lead->activities->count() }} {{ __('entries') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="card-body p-4 pt-2">
                                        @php
                                            $activityIconMap = [
                                                'Lead Created'    => ['icon' => 'ti-circle-plus',       'bg' => '#198754', 'light' => 'rgba(25,135,84,0.1)'],
                                                'Move'            => ['icon' => 'ti-arrows-right-left',  'bg' => '#fd7e14', 'light' => 'rgba(253,126,20,0.1)'],
                                                'Lead Updated'    => ['icon' => 'ti-pencil',              'bg' => '#0d6efd', 'light' => 'rgba(13,110,253,0.1)'],
                                                'Lead Transferred'=> ['icon' => 'ti-switch-horizontal',  'bg' => '#6f42c1', 'light' => 'rgba(111,66,193,0.1)'],
                                                'Lead Imported'   => ['icon' => 'ti-file-upload',         'bg' => '#20c997', 'light' => 'rgba(32,201,151,0.1)'],
                                                'Upload File'     => ['icon' => 'ti-file',                'bg' => '#6c757d', 'light' => 'rgba(108,117,125,0.1)'],
                                                'Add Product'     => ['icon' => 'ti-package',             'bg' => '#198754', 'light' => 'rgba(25,135,84,0.1)'],
                                                'Update Sources'  => ['icon' => 'ti-source-code',         'bg' => '#0dcaf0', 'light' => 'rgba(13,202,240,0.1)'],
                                                'Create Lead Call'=> ['icon' => 'ti-phone',               'bg' => '#198754', 'light' => 'rgba(25,135,84,0.1)'],
                                                'Create Lead Email'=> ['icon' => 'ti-mail',               'bg' => '#0d6efd', 'light' => 'rgba(13,110,253,0.1)'],
                                                'Create Task'     => ['icon' => 'ti-list-check',          'bg' => '#fd7e14', 'light' => 'rgba(253,126,20,0.1)'],
                                                'Create Reminder' => ['icon' => 'ti-bell',                'bg' => '#ffc107', 'light' => 'rgba(255,193,7,0.1)'],
                                            ];
                                        @endphp

                                        @forelse ($lead->activities as $activity)
                                            @php
                                                $ai = $activityIconMap[$activity->log_type] ?? ['icon' => 'ti-point', 'bg' => '#adb5bd', 'light' => 'rgba(173,181,189,0.1)'];
                                                $actUser = $activity->user;
                                                $actAvatar = (!empty($actUser) && !empty($actUser->avatar) && check_file($actUser->avatar))
                                                    ? get_file($actUser->avatar)
                                                    : get_file('uploads/users-avatar/avatar.png');
                                            @endphp
                                            <div class="d-flex align-items-start py-3 px-2 rounded-3 mb-1" style="transition: background 0.2s; border-left: 3px solid {{ $ai['bg'] }}; padding-left: 12px !important;"
                                                 onmouseover="this.style.background='{{ $ai['light'] }}'" onmouseout="this.style.background='transparent'">
                                                {{-- Icon --}}
                                                <div class="flex-shrink-0 me-3 d-flex align-items-center justify-content-center rounded-circle shadow-sm"
                                                     style="width: 36px; height: 36px; background: {{ $ai['light'] }}; border: 2px solid {{ $ai['bg'] }};">
                                                    <i class="ti {{ $ai['icon'] }}" style="color: {{ $ai['bg'] }}; font-size: 14px;"></i>
                                                </div>
                                                {{-- Text --}}
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <div class="d-flex align-items-center flex-wrap gap-1 mb-1">
                                                        <span class="badge rounded-pill px-2 py-1" style="background: {{ $ai['light'] }}; color: {{ $ai['bg'] }}; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.3px;">
                                                            {{ __($activity->log_type) }}
                                                        </span>
                                                        @if($actUser)
                                                            <span class="d-flex align-items-center ms-1">
                                                                <img src="{{ $actAvatar }}" class="rounded-circle me-1" style="width: 16px; height: 16px; border: 1px solid #dee2e6;">
                                                                <small class="text-muted fw-600" style="font-size: 0.7rem;">{{ $actUser->name }}</small>
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <p class="mb-0 text-dark fw-500" style="font-size: 0.85rem; line-height: 1.4;">{!! $activity->getLeadRemark() !!}</p>
                                                    <div class="d-flex align-items-center mt-1 gap-2">
                                                        <small class="text-muted" style="font-size: 0.7rem;">
                                                            <i class="ti ti-clock me-1"></i>{{ $activity->created_at->diffForHumans() }}
                                                        </small>
                                                        <small class="text-muted opacity-50" style="font-size: 0.65rem;">
                                                            · {{ $activity->created_at->format('d M Y, h:i A') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5">
                                                <div class="d-flex align-items-center justify-content-center mb-3 mx-auto rounded-circle"
                                                     style="width: 64px; height: 64px; background: rgba(25,135,84,0.08);">
                                                    <i class="ti ti-activity-heartbeat text-success" style="font-size: 28px;"></i>
                                                </div>
                                                <p class="text-muted mb-1" style="font-size: 0.85rem;">{{ __('No activity yet') }}</p>
                                                <small class="text-muted opacity-50">{{ __('Actions on this lead will appear here') }}</small>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>


                            {{-- Orion EKYC Sync Logs Section - Only visible to company owner --}}
                            @if(isset($activeOrionRule) && $activeOrionRule && Auth::user()->type == 'company')
                            <div id="orion-sync-logs">
                                <div class="card card-modern border-0 shadow-sm mb-4">
                                    <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center pt-4 px-4">
                                        <h5 class="mb-0 section-title d-flex align-items-center">
                                            <i class="ti ti-refresh me-2"></i> {{ __('Orion Sync Logs') }}
                                        </h5>
                                        <div class="d-flex align-items-center gap-2">
                                            @if(isset($orionLogs) && $orionLogs->count() > 0)
                                                @php
                                                    $successCount = $orionLogs->where('status', 'success')->count();
                                                    $failedCount = $orionLogs->where('status', 'failed')->count();
                                                    $pendingCount = $orionLogs->where('status', 'pending')->count();
                                                @endphp
                                                @if($successCount > 0)
                                                    <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill" style="font-size: 0.68rem;">
                                                        <i class="ti ti-check me-1"></i>{{ $successCount }}
                                                    </span>
                                                @endif
                                                @if($failedCount > 0)
                                                    <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 rounded-pill" style="font-size: 0.68rem;">
                                                        <i class="ti ti-x me-1"></i>{{ $failedCount }}
                                                    </span>
                                                @endif
                                                @if($pendingCount > 0)
                                                    <span class="badge bg-warning-subtle text-warning border border-warning border-opacity-25 rounded-pill" style="font-size: 0.68rem;">
                                                        <i class="ti ti-clock me-1"></i>{{ $pendingCount }}
                                                    </span>
                                                @endif
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size: 0.65rem;">
                                                    {{ $orionLogs->count() }} {{ __('total') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="list-group list-group-flush">
                                            @forelse($orionLogs ?? collect() as $log)
                                                @php
                                                    $logStatusColor = match($log->status) {
                                                        'success' => ['bg' => 'bg-success', 'text' => 'text-success', 'subtle' => 'bg-success-subtle', 'icon' => 'ti-circle-check', 'border' => 'border-success', 'glow' => 'rgba(25,135,84,0.15)'],
                                                        'failed'  => ['bg' => 'bg-danger',  'text' => 'text-danger',  'subtle' => 'bg-danger-subtle',  'icon' => 'ti-circle-x',     'border' => 'border-danger',  'glow' => 'rgba(220,53,69,0.15)'],
                                                        default   => ['bg' => 'bg-warning', 'text' => 'text-warning', 'subtle' => 'bg-warning-subtle', 'icon' => 'ti-clock',         'border' => 'border-warning', 'glow' => 'rgba(255,193,7,0.15)'],
                                                    };
                                                    $apiLabel = match($log->api_type) {
                                                        'fetch_details' => 'Fetch Details',
                                                        'post_ekyc'     => 'Post EKYC',
                                                        'post_modify'   => 'Post Modify',
                                                        default         => ucfirst(str_replace('_', ' ', $log->api_type)),
                                                    };
                                                    $apiIcon = match($log->api_type) {
                                                        'fetch_details' => 'ti-download',
                                                        'post_ekyc'     => 'ti-upload',
                                                        'post_modify'   => 'ti-edit',
                                                        default         => 'ti-refresh',
                                                    };
                                                @endphp
                                                <div class="list-group-item orion-log-row px-4 py-3 border-0 border-bottom" style="border-left: 3px solid {{ $logStatusColor['glow'] }} !important; transition: all 0.3s ease;">
                                                    <div class="d-flex align-items-start justify-content-between">
                                                        {{-- Left: Icon + Details --}}
                                                        <div class="d-flex align-items-start flex-grow-1">
                                                            <div class="flex-shrink-0 me-3 d-flex align-items-center justify-content-center rounded-circle shadow-sm"
                                                                 style="width: 40px; height: 40px; background: {{ $logStatusColor['glow'] }}; border: 2px solid var(--bs-{{ str_replace('border-', '', $logStatusColor['border']) }});">
                                                                <i class="ti {{ $logStatusColor['icon'] }} {{ $logStatusColor['text'] }}" style="font-size: 16px;"></i>
                                                            </div>
                                                            <div class="flex-grow-1 overflow-hidden">
                                                                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                                                    {{-- API Type Badge --}}
                                                                    <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary border-opacity-25" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.3px;">
                                                                        <i class="ti {{ $apiIcon }} me-1"></i>{{ __($apiLabel) }}
                                                                    </span>
                                                                    {{-- Status Badge --}}
                                                                    <span class="badge rounded-pill {{ $logStatusColor['subtle'] }} {{ $logStatusColor['text'] }} {{ $logStatusColor['border'] }} border border-opacity-25" style="font-size: 0.65rem; font-weight: 700;">
                                                                        {{ __(ucfirst($log->status)) }}
                                                                    </span>
                                                                    {{-- Client Code --}}
                                                                    @if($log->client_code)
                                                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size: 0.6rem;">
                                                                            <i class="ti ti-id me-1"></i>{{ $log->client_code }}
                                                                        </span>
                                                                    @endif
                                                                </div>

                                                                {{-- Error Reason --}}
                                                                @if($log->status === 'failed' && $log->error_reason)
                                                                    <div class="d-flex align-items-start rounded-3 py-2 px-3 mb-2 mt-1" style="font-size: 0.78rem; background: rgba(239, 68, 68, 0.06); border: 1px solid rgba(239, 68, 68, 0.15); color: #dc2626;">
                                                                        <i class="ti ti-alert-triangle me-2 mt-0.5" style="font-size: 1rem; flex-shrink: 0; color: #dc2626;"></i>
                                                                        <div class="flex-grow-1">
                                                                            <strong style="color: #dc2626;">{{ __('Error:') }}</strong>
                                                                            <span style="color: #4b5563;">{{ Str::limit($log->error_reason, 200) }}</span>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                {{-- Timestamp + Creator --}}
                                                                <div class="d-flex align-items-center gap-3 mt-1">
                                                                    <small class="text-muted" style="font-size: 0.7rem;">
                                                                        <i class="ti ti-clock me-1"></i>{{ $log->created_at->diffForHumans() }}
                                                                    </small>
                                                                    <small class="text-muted opacity-50" style="font-size: 0.65rem;">
                                                                        · {{ $log->created_at->format('d M Y, h:i A') }}
                                                                    </small>
                                                                    @if($log->creator)
                                                                        <small class="text-muted" style="font-size: 0.7rem;">
                                                                            <i class="ti ti-user me-1"></i>{{ $log->creator->name }}
                                                                        </small>
                                                                    @endif
                                                                </div>

                                                                {{-- Payload Toggle Buttons --}}
                                                                <div class="d-flex align-items-center gap-2 mt-2">
                                                                    @if($log->request_payload)
                                                                        <button class="btn btn-xs rounded-pill px-3 py-1 orion-log-toggle-btn" type="button"
                                                                                data-bs-toggle="collapse" data-bs-target="#orion-req-{{ $log->id }}"
                                                                                style="font-size: 0.68rem; font-weight: 600; background: rgba(13,110,253,0.08); color: #0d6efd; border: 1px solid rgba(13,110,253,0.2); transition: all 0.2s;">
                                                                            <i class="ti ti-code me-1"></i>{{ __('Request') }}
                                                                        </button>
                                                                    @endif
                                                                    @if($log->response_payload)
                                                                        <button class="btn btn-xs rounded-pill px-3 py-1 orion-log-toggle-btn" type="button"
                                                                                data-bs-toggle="collapse" data-bs-target="#orion-res-{{ $log->id }}"
                                                                                style="font-size: 0.68rem; font-weight: 600; background: rgba(25,135,84,0.08); color: #198754; border: 1px solid rgba(25,135,84,0.2); transition: all 0.2s;">
                                                                            <i class="ti ti-code me-1"></i>{{ __('Response') }}
                                                                        </button>
                                                                    @endif
                                                                </div>

                                                                {{-- Collapsible Request Payload --}}
                                                                @if($log->request_payload)
                                                                    <div class="collapse mt-2" id="orion-req-{{ $log->id }}">
                                                                        <div class="position-relative rounded-3 border" style="background: #1e293b;">
                                                                            <div class="d-flex align-items-center justify-content-between px-3 py-2" style="background: rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.08);">
                                                                                <span class="text-white-50" style="font-size: 0.68rem; font-weight: 600; letter-spacing: 0.5px;">
                                                                                    <i class="ti ti-arrow-up-right me-1"></i>REQUEST PAYLOAD
                                                                                </span>
                                                                                <button class="btn btn-xs text-white-50 orion-copy-btn p-0 border-0 bg-transparent" type="button"
                                                                                        data-payload="{{ htmlspecialchars(json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES) }}"
                                                                                        title="{{ __('Copy to Clipboard') }}" style="font-size: 0.7rem;">
                                                                                    <i class="ti ti-copy me-1"></i>{{ __('Copy') }}
                                                                                </button>
                                                                            </div>
                                                                            <pre class="mb-0 p-3 text-white" style="font-size: 0.72rem; max-height: 250px; overflow: auto; white-space: pre-wrap; word-break: break-all; font-family: 'JetBrains Mono', 'Fira Code', monospace;">{{ json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                                        </div>
                                                                    </div>
                                                                @endif

                                                                {{-- Collapsible Response Payload --}}
                                                                @if($log->response_payload)
                                                                    <div class="collapse mt-2" id="orion-res-{{ $log->id }}">
                                                                        <div class="position-relative rounded-3 border" style="background: #1e293b;">
                                                                            <div class="d-flex align-items-center justify-content-between px-3 py-2" style="background: rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.08);">
                                                                                <span class="text-white-50" style="font-size: 0.68rem; font-weight: 600; letter-spacing: 0.5px;">
                                                                                    <i class="ti ti-arrow-down-left me-1"></i>RESPONSE PAYLOAD
                                                                                </span>
                                                                                <button class="btn btn-xs text-white-50 orion-copy-btn p-0 border-0 bg-transparent" type="button"
                                                                                        data-payload="{{ htmlspecialchars(json_encode($log->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES) }}"
                                                                                        title="{{ __('Copy to Clipboard') }}" style="font-size: 0.7rem;">
                                                                                    <i class="ti ti-copy me-1"></i>{{ __('Copy') }}
                                                                                </button>
                                                                            </div>
                                                                            <pre class="mb-0 p-3 text-white" style="font-size: 0.72rem; max-height: 250px; overflow: auto; white-space: pre-wrap; word-break: break-all; font-family: 'JetBrains Mono', 'Fira Code', monospace;">{{ json_encode($log->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-center py-5">
                                                    <div class="d-flex align-items-center justify-content-center mb-3 mx-auto rounded-circle"
                                                         style="width: 64px; height: 64px; background: rgba(245, 158, 11, 0.08);">
                                                        <i class="ti ti-refresh" style="font-size: 28px; color: #f59e0b;"></i>
                                                    </div>
                                                    <p class="text-muted mb-1" style="font-size: 0.85rem;">{{ __('No sync history yet') }}</p>
                                                    <small class="text-muted opacity-50">{{ __('Use the') }} <i class="ti ti-cloud-download text-warning"></i> {{ __('Fetch Orion EKYC button to sync data. All API requests & responses will be logged here.') }}</small>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                        @endif
                    </div>
                    @stack('indiamart_div')
                </div>
            </div>
        </div>
    </div>

    <!-- Orion Fullscreen Loading Overlay -->
    @if($activeOrionRule)
    <div class="orion-fetch-overlay d-none position-fixed top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center" style="background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(12px); z-index: 999999; color: #ffffff;">
        <div class="orion-spinner-ring mb-4"></div>
        <span class="fw-bold text-sm animate-pulse-text" style="color: #ffffff; letter-spacing: 0.5px; font-weight: 700;">{{ __('Fetching details from Orion API...') }}</span>
        <span class="text-xs mt-1 status-msg" style="color: rgba(255, 255, 255, 0.65); min-height: 18px;">{{ __('Please wait, querying secure channels') }}</span>
    </div>
    @endif
 
@endsection

@push('scripts')
    @include('lead::leads.click_to_call_script')
    
    <script>
        $(document).ready(function() {
            // Click to change stage via stepper
            $(document).on('click', '.btn-change-stage', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var stageId = $btn.data('stage-id');
                var stageName = $btn.data('stage-name');
                
                Swal.fire({
                    title: '{{ __("Move Stage?") }}',
                    text: '{{ __("Are you sure you want to move this lead to") }} "' + stageName + '"?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '{{ __("Yes, Move") }}',
                    cancelButtonText: '{{ __("Cancel") }}',
                    customClass: {
                        confirmButton: 'btn btn-primary px-4 py-2 rounded-pill shadow-sm me-2',
                        cancelButton: 'btn btn-danger px-4 py-2 rounded-pill shadow-sm'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show overlay loading if available
                        if ($('.orion-fetch-overlay').length > 0) {
                            $('.orion-fetch-overlay').removeClass('d-none');
                            $('.orion-fetch-overlay .status-msg').text('{{ __("Moving lead to stage") }} "' + stageName + '"...');
                        }
                        
                        $.ajax({
                            url: "{{ route('leads.order') }}",
                            type: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                lead_id: "{{ $lead->id }}",
                                stage_id: stageId
                            },
                            success: function(response) {
                                if (response.success) {
                                    toastrs('Success', response.success, 'success');
                                    setTimeout(function() {
                                        location.reload();
                                    }, 800);
                                } else {
                                    if ($('.orion-fetch-overlay').length > 0) {
                                        $('.orion-fetch-overlay').addClass('d-none');
                                    }
                                    toastrs('Error', response.error || 'Failed to move stage', 'error');
                                }
                            },
                            error: function(xhr) {
                                if ($('.orion-fetch-overlay').length > 0) {
                                    $('.orion-fetch-overlay').addClass('d-none');
                                }
                                var err = xhr.responseJSON ? xhr.responseJSON.error : 'Network error';
                                toastrs('Error', err, 'error');
                            }
                        });
                    }
                });
            });
            // Click to edit
            $(document).on('click', '.editable-field', function(e) {
                // Ignore if clicked on buttons inside editor or if already editing
                if ($(e.target).closest('.inline-edit-container').length > 0 || $(this).find('.inline-edit-container').length > 0) {
                    return;
                }
                
                var $span = $(this);
                var originalHTML = $span.html();
                var fieldName = $span.data('name');
                var isSystem = $span.data('system');
                var fieldType = $span.data('type');
                var rawValue = $span.attr('data-value') !== undefined ? $span.attr('data-value') : '';
                var optionsStr = $span.data('options') || '';
                
                // Prevent links / click-to-call during edit trigger
                e.preventDefault();
                e.stopPropagation();
                
                var inputHTML = '';
                if (fieldType === 'select' && optionsStr) {
                    var options = optionsStr.split(',');
                    inputHTML = '<select class="form-select form-select-sm inline-edit-input">';
                    options.forEach(function(opt) {
                        opt = opt.trim();
                        var selected = (opt === rawValue) ? 'selected' : '';
                        inputHTML += '<option value="' + opt + '" ' + selected + '>' + opt + '</option>';
                    });
                    inputHTML += '</select>';
                } else if (fieldType === 'multi_select' && optionsStr) {
                    var options = optionsStr.split(',');
                    var selectedOpts = rawValue ? rawValue.split(',') : [];
                    selectedOpts = selectedOpts.map(function(item) { return item.trim(); });
                    inputHTML += '<div class="w-100 select2-container-inline">';
                    inputHTML += '<select class="form-select form-select-sm inline-edit-input select2-modal-inline" multiple style="min-width: 150px;">';
                    options.forEach(function(opt) {
                        opt = opt.trim();
                        var selected = (selectedOpts.indexOf(opt) !== -1) ? 'selected' : '';
                        inputHTML += '<option value="' + opt + '" ' + selected + '>' + opt + '</option>';
                    });
                    inputHTML += '</select></div>';
                } else if (fieldType === 'textarea') {
                    inputHTML = '<textarea class="form-control form-control-sm inline-edit-input" rows="2">' + rawValue + '</textarea>';
                } else if (fieldType === 'date') {
                    inputHTML = '<input type="date" class="form-control form-control-sm inline-edit-input" value="' + rawValue + '">';
                } else if (fieldType === 'number') {
                    inputHTML = '<input type="number" class="form-control form-control-sm inline-edit-input" value="' + rawValue + '">';
                } else if (fieldType === 'file') {
                    inputHTML = '<input type="file" class="form-control form-control-sm inline-edit-input">';
                } else {
                    inputHTML = '<input type="text" class="form-control form-control-sm inline-edit-input" value="' + rawValue + '">';
                }
                
                var containerHTML = '<div class="inline-edit-container d-flex align-items-center w-100 mt-1">' +
                    inputHTML +
                    '<button class="btn btn-sm btn-success p-1 ms-2 btn-inline-save" type="button"><i class="ti ti-check text-white fs-6"></i></button>' +
                    '<button class="btn btn-sm btn-danger p-1 ms-1 btn-inline-cancel" type="button"><i class="ti ti-x text-white fs-6"></i></button>' +
                    '</div>';
                    
                $span.data('original-html', originalHTML);
                $span.html(containerHTML);
                $span.find('.inline-edit-input').focus();
            });
            
            // Cancel inline edit
            $(document).on('click', '.btn-inline-cancel', function(e) {
                e.stopPropagation();
                var $span = $(this).closest('.editable-field');
                $span.html($span.data('original-html'));
            });
            
            // Save inline edit
            $(document).on('click', '.btn-inline-save', function(e) {
                e.stopPropagation();
                var $btn = $(this);
                var $span = $btn.closest('.editable-field');
                var fieldName = $span.data('name');
                var isSystem = $span.data('system');
                var fieldType = $span.data('type');
                
                var $input = $span.find('.inline-edit-input');
                var fieldValue = $input.val();
                
                var formData = new FormData();
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                formData.append('field_name', fieldName);
                formData.append('is_system', isSystem);
                
                if (fieldType === 'file') {
                    if ($input[0].files.length > 0) {
                        formData.append('field_value', $input[0].files[0]);
                    } else {
                        $span.html($span.data('original-html'));
                        return;
                    }
                } else if (fieldType === 'multi_select') {
                    var selectedVals = $input.val() || [];
                    selectedVals.forEach(function(val) {
                        formData.append('field_value[]', val);
                    });
                } else {
                    formData.append('field_value', fieldValue);
                }
                
                $btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm text-white"></i>');
                
                $.ajax({
                    url: "{{ route('leads.inline-update', $lead->id) }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.is_success) {
                            var displayVal = res.value !== undefined ? res.value : fieldValue;
                            $span.attr('data-value', displayVal);
                            
                            if (!displayVal || displayVal === '-') {
                                $span.html('<span class="text-muted fw-normal fst-italic" style="opacity: 0.55;">{{ __("Not Provided") }}</span>');
                            } else if (fieldType === 'multi_select') {
                                var items = displayVal.split(',');
                                var badgesHTML = '';
                                items.forEach(function(item) {
                                    badgesHTML += '<span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2 py-1 me-1">' + item.trim() + '</span>';
                                });
                                $span.html(badgesHTML);
                            } else if (fieldType === 'file') {
                                $span.html('<a href="{{ asset("storage/uploads/custom_fields") }}/' + displayVal + '" download class="btn btn-xs btn-outline-success rounded-pill"><i class="ti ti-download me-1"></i> {{ __("Download") }}</a>');
                            } else if (fieldName === 'email' && displayVal) {
                                $span.html('<a href="mailto:' + displayVal + '" class="text-primary hover-underline">' + displayVal + '</a>');
                            } else if (fieldName === 'phone' && displayVal) {
                                $span.html(displayVal + ' <a href="javascript:void(0)" class="ms-1 text-primary click-to-call" data-phone="' + displayVal + '" data-bs-toggle="tooltip" title="{{ __("Call") }}"><i class="ti ti-phone-call"></i></a> <a href="/whatsapp-chats?lead_id={{ $lead->id }}" class="ms-1 text-success" data-bs-toggle="tooltip" title="{{ __("WhatsApp Chat") }}"><i class="ti ti-brand-whatsapp"></i></a>');
                            } else {
                                $span.text(displayVal);
                            }
                            
                            toastrs('Success', res.message || "{{ __('Field updated successfully.') }}", 'success');
                        } else {
                            toastrs('Error', res.error || "{{ __('Failed to update field.') }}", 'error');
                            $span.html($span.data('original-html'));
                        }
                    },
                    error: function(xhr) {
                        var err = xhr.responseJSON ? xhr.responseJSON.error : "{{ __('Failed to update field.') }}";
                        toastrs('Error', err, 'error');
                        $span.html($span.data('original-html'));
                    }
                });
            });

            // AJAX trigger for Section API Sync
            $(document).on('click', '.sync-section-api-btn', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var $icon = $btn.find('i');
                var sectionId = $btn.data('section-id');
                var leadId = $btn.data('lead-id');

                $icon.removeClass('ti-refresh').addClass('ti-loader animate-spin');
                $btn.addClass('disabled');

                $.ajax({
                    url: '{{ route("leads.sync-section-api") }}',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        lead_id: leadId,
                        section_id: sectionId
                    },
                    success: function(response) {
                        if (response.success) {
                            toastrs('Success', response.success, 'success');
                            if (response.values) {
                                $.each(response.values, function(fieldId, value) {
                                    var $fieldSpan = $('[data-name="' + fieldId + '"]');
                                    if ($fieldSpan.length > 0) {
                                        $fieldSpan.attr('data-value', value);
                                        $fieldSpan.text(value);
                                    }
                                });
                                // Reload to update fields properly
                                setTimeout(function() {
                                    location.reload();
                                }, 800);
                            }
                        } else {
                            toastrs('Error', response.error || 'Sync failed', 'error');
                        }
                    },
                    error: function(xhr) {
                        var err = xhr.responseJSON ? xhr.responseJSON.error : 'Network error';
                        toastrs('Error', err, 'error');
                    },
                    complete: function() {
                        $icon.removeClass('ti-loader animate-spin').addClass('ti-refresh');
                        $btn.removeClass('disabled');
                    }
                });
            });

            // Secure Reveal Toggle
            $(document).on('click', '.toggle-secure-reveal-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $wrapper = $(this).closest('.secure-reveal-wrapper');
                var $revealed = $wrapper.find('.revealed-value');
                var $masked = $wrapper.find('.masked-value');
                var $icon = $(this).find('i');
                
                if ($revealed.hasClass('d-none')) {
                    $revealed.removeClass('d-none');
                    $masked.addClass('d-none');
                    $icon.removeClass('ti-eye').addClass('ti-eye-off');
                } else {
                    $revealed.addClass('d-none');
                    $masked.removeClass('d-none');
                    $icon.removeClass('ti-eye-off').addClass('ti-eye');
                }
            });
        });
    </script>
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin {
            display: inline-block;
            animation: spin 1.5s linear infinite;
        }
        .toggle-secure-reveal-btn {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            color: var(--theme-emerald) !important;
            padding: 2px !important;
            display: inline-flex;
            align-items: center;
        }
        .toggle-secure-reveal-btn:hover {
            color: var(--primary-emerald-hover) !important;
            transform: scale(1.1);
        }
        /* Orion Loader styles */
        .orion-spinner-ring {
            width: 52px;
            height: 52px;
            border: 3px solid rgba(245, 158, 11, 0.15);
            border-top: 3px solid #f59e0b;
            border-right: 3px solid #f59e0b;
            border-radius: 50%;
            animation: spin 0.8s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.35);
        }
        .animate-pulse-text {
            animation: pulse-text 1.5s ease-in-out infinite;
        }
        @keyframes pulse-text {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.75; transform: scale(0.97); }
        }
        .orion-pulse-btn {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            border: none !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            animation: orion-pulse 2s infinite;
        }
        .orion-pulse-btn:hover {
            transform: translateY(-2px) scale(1.08) !important;
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.6) !important;
            background: linear-gradient(135deg, #fbbf24 0%, #ea580c 100%) !important;
        }
        @keyframes orion-pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.6), 0 4px 10px rgba(245, 158, 11, 0.3);
            }
            70% {
                box-shadow: 0 0 0 8px rgba(245, 158, 11, 0), 0 4px 10px rgba(245, 158, 11, 0.3);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0), 0 4px 10px rgba(245, 158, 11, 0.3);
            }
        }
        .orion-log-row:hover {
            background-color: rgba(248, 250, 252, 0.9) !important;
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }
        .orion-log-row {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .orion-log-toggle-btn {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        .orion-log-toggle-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }
        .orion-log-toggle-btn[data-bs-target*="req"]:hover {
            background: #0d6efd !important;
            color: #fff !important;
            border-color: #0d6efd !important;
        }
        .orion-log-toggle-btn[data-bs-target*="res"]:hover {
            background: #198754 !important;
            color: #fff !important;
            border-color: #198754 !important;
        }
        .orion-copy-btn {
            transition: all 0.2s ease;
        }
        .orion-copy-btn:hover {
            color: #ffffff !important;
            transform: scale(1.05);
        }
        .orion-copy-btn:active {
            transform: scale(0.95);
        }
    </style>

    @if($activeOrionRule)
    <script>
        $(document).ready(function() {
            $('#btn-orion-ekyc-fetch, .btn-orion-ekyc-fetch-trigger').on('click', function(e) {
                e.preventDefault();
                
                const clientCode = $(this).attr('data-client-code');
                const ruleId = $(this).attr('data-rule-id');
                
                if (!clientCode) {
                    toastrs('Error', '{{ __("Client Code / Mobile / PAN is required to fetch.") }}', 'error');
                    return;
                }
                
                // Show fullscreen loading overlay
                $('.orion-fetch-overlay').removeClass('d-none');
                
                // Start status log cycle
                const messages = [
                    "Establishing connection to Orion gateway...",
                    "Authenticating secure API credentials...",
                    "Querying client details by PAN/Mobile...",
                    "Retrieving backoffice details...",
                    "Verifying financial years records...",
                    "Mapping response fields to CRM schema..."
                ];
                let msgIdx = 0;
                $('.orion-fetch-overlay .status-msg').text(messages[0]);
                const statusInterval = setInterval(() => {
                    msgIdx++;
                    if (msgIdx < messages.length) {
                        $('.orion-fetch-overlay .status-msg').fadeOut(150, function() {
                            $(this).text(messages[msgIdx]).fadeIn(150);
                        });
                    }
                }, 1200);

                // Disable the trigger button
                const btn = $(this);
                const origHtml = btn.html();
                btn.html('<i class="ti ti-loader animate-spin"></i>').addClass('disabled');

                $.ajax({
                    url: '{{ route("leads.orion-fetch", $lead->id) }}',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        client_code: clientCode,
                        rule_id: ruleId
                    },
                    success: function(response) {
                        clearInterval(statusInterval);
                        // Hide loading overlay
                        $('.orion-fetch-overlay').addClass('d-none');
                        btn.html(origHtml).removeClass('disabled');

                        if (response.success) {
                            // Compile updated fields into a premium HTML view
                            let fieldsHtml = '<div class="text-start mt-2" style="text-align: left; max-height: 250px; overflow-y: auto; font-family: inherit;">';
                            fieldsHtml += '<p class="text-muted small mb-2">' + 'The following lead fields were successfully synced from Orion:' + '</p>';
                            fieldsHtml += '<div class="list-group list-group-flush border rounded-3 overflow-hidden shadow-sm" style="font-size: 0.82rem;">';
                            
                            const fieldLabels = {
                                'name': 'Lead Name',
                                'email': 'Email Address',
                                'phone': 'Phone Number',
                                'pan_number': 'PAN Card Number',
                                'aadhar_number': 'Aadhar Card Number',
                                'dp_id': 'DP ID',
                                'custom_31': 'CLIENT CODE',
                                'custom_32': 'DP NO.',
                                'custom_42': 'Father\'s Name',
                                'custom_43': 'Mother\'s Name',
                                'custom_44': 'Gender',
                                'custom_45': 'Date of Birth',
                                'custom_46': 'Marital Status',
                                'custom_70': 'PANCARD NUMBER'
                            };

                            let hasFields = false;
                            if (response.updated_fields) {
                                for (let key in response.updated_fields) {
                                    let label = fieldLabels[key] || key;
                                    let val = response.updated_fields[key] || '-';
                                    fieldsHtml += '<div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 bg-light">';
                                    fieldsHtml += '<span class="text-secondary fw-semibold">' + label + '</span>';
                                    fieldsHtml += '<span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-2.5 py-1 fw-bold rounded-pill">' + val + '</span>';
                                    fieldsHtml += '</div>';
                                    hasFields = true;
                                }
                            }
                            
                            if (!hasFields) {
                                fieldsHtml += '<div class="list-group-item text-center text-muted py-3">All fields were already up-to-date.</div>';
                            }
                            fieldsHtml += '</div></div>';

                            // Show premium SweetAlert2 dialog
                            Swal.fire({
                                title: 'Orion Sync Completed!',
                                html: fieldsHtml,
                                icon: 'success',
                                confirmButtonText: 'Refresh Page',
                                customClass: {
                                    confirmButton: 'btn btn-primary px-4 py-2 rounded-pill shadow-sm',
                                    popup: 'rounded-4'
                                },
                                buttonsStyling: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Fetch Failed',
                                text: response.message || 'No records found.',
                                icon: 'error',
                                confirmButtonText: 'Okay',
                                customClass: {
                                    confirmButton: 'btn btn-danger px-4 py-2 rounded-pill',
                                    popup: 'rounded-4'
                                },
                                buttonsStyling: false
                            });
                        }
                    },
                    error: function(xhr) {
                        clearInterval(statusInterval);
                        const err = xhr.responseJSON ? xhr.responseJSON.message : 'Connection failed';
                        $('.orion-fetch-overlay').addClass('d-none');
                        btn.html(origHtml).removeClass('disabled');

                        Swal.fire({
                            title: 'Orion Fetch Failed',
                            text: err,
                            icon: 'error',
                            confirmButtonText: 'Understood',
                            customClass: {
                                confirmButton: 'btn btn-danger px-4 py-2 rounded-pill',
                                popup: 'rounded-4'
                            },
                            buttonsStyling: false
                        });
                    }
                });
            });
        });
    </script>
    @endif

    {{-- Orion Sync Logs - Copy to Clipboard & Hover Effects --}}
    <script>
        $(document).ready(function() {
            // Copy payload to clipboard
            $(document).on('click', '.orion-copy-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var payload = $(this).attr('data-payload');
                var btn = $(this);
                var origHTML = btn.html();

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(payload).then(function() {
                        btn.html('<i class="ti ti-check me-1"></i>{{ __("Copied!") }}');
                        btn.css('color', '#22c55e');
                        setTimeout(function() {
                            btn.html(origHTML);
                            btn.css('color', '');
                        }, 2000);
                    });
                } else {
                    // Fallback for older browsers
                    var textarea = document.createElement('textarea');
                    textarea.value = payload;
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);

                    btn.html('<i class="ti ti-check me-1"></i>{{ __("Copied!") }}');
                    btn.css('color', '#22c55e');
                    setTimeout(function() {
                        btn.html(origHTML);
                        btn.css('color', '');
                    }, 2000);
                }
            });

            // Toggle button active state
            $(document).on('click', '.orion-log-toggle-btn', function() {
                $(this).toggleClass('orion-log-btn-active');
            });
        });
    </script>
    <style>
        /* Orion Sync Logs - Hover & Toggle Styles */
        .orion-log-toggle-btn:hover {
            filter: brightness(0.92);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .orion-log-btn-active {
            filter: brightness(0.85) !important;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1) !important;
        }
        .orion-copy-btn:hover {
            color: #fff !important;
            text-decoration: underline;
        }
        #orion-sync-logs .list-group-item:hover {
            background: var(--primary-emerald-light) !important;
        }
        /* Custom scrollbar for dark code blocks */
        #orion-sync-logs pre::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        #orion-sync-logs pre::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.05);
            border-radius: 3px;
        }
        #orion-sync-logs pre::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.15);
            border-radius: 3px;
        }
        #orion-sync-logs pre::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.25);
        }
    </style>
@endpush