@extends('layouts.main')

@section('page-title')
    {{ __('Targets') }}
@endsection

@section('page-breadcrumb')
    {{ __('Targets') }}
@endsection

@section('page-action')
    <div class="d-flex gap-2">
        <a href="#" class="btn btn-sm btn-light-primary border" data-bs-toggle="collapse" data-bs-target="#filterCard">
            <i class="ti ti-filter"></i> {{ __('Filter') }}
        </a>
        @if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
            <a href="#" class="btn btn-sm btn-outline-primary border shadow-sm" data-ajax-popup="true" data-size="md"
                data-title="{{ __('Create Target Template') }}" data-url="{{ route('targets.templates.create') }}"
                data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create Template') }}">
                <i class="ti ti-plus"></i> {{ __('New Template') }}
            </a>
            <a href="#" class="btn btn-sm btn-primary shadow-sm" data-ajax-popup="true" data-size="md"
                data-title="{{ __('Create New Target') }}" data-url="{{ route('targets.create') }}"
                data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}">
                <i class="ti ti-plus text-white-off"></i> {{ __('New Target') }}
            </a>
        @endif
    </div>
@endsection

@section('content')
@php
    $company_settings = getCompanyAllSetting(creatorId());
    $color = !empty($company_settings['color']) ? $company_settings['color'] : 'theme-1';
    
    $themeColorHex = '5e72e4'; // Default fallback
    $colorHexMap = [
        'theme-1' => '0CAF60',
        'theme-2' => '75C251',
        'theme-3' => '584ED2',
        'theme-4' => '145388',
        'theme-5' => 'B9406B',
        'theme-6' => '008ECC',
        'theme-7' => '922C88',
        'theme-8' => 'C0A145',
        'theme-9' => '48494B',
        'theme-10' => '0C7785',
    ];
    if (isset($colorHexMap[$color])) {
        $themeColorHex = $colorHexMap[$color];
    } elseif (strpos($color, '#') === 0) {
        $themeColorHex = ltrim($color, '#');
    } elseif (isset($company_settings['color_flag']) && $company_settings['color_flag'] == 'true' && !empty($company_settings['color'])) {
        $themeColorHex = ltrim($company_settings['color'], '#');
    }

    // convert hex to rgb
    $themeColorRgb = '94, 114, 228'; // Default fallback
    $hex = str_replace('#', '', $themeColorHex);
    if (strlen($hex) == 6) {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $themeColorRgb = "$r, $g, $b";
    }
@endphp
@push('css')
    <style>
        /* Dynamic Theme Primary Color Mapping */
        :root {
            --primary-theme-color: #5e72e4;
            --primary-theme-color-rgb: 94, 114, 228;
            --primary-theme-gradient: linear-gradient(90deg, #5e72e4 0%, #825ee4 100%);
            --primary-theme-shadow: rgba(94, 114, 228, 0.15);
        }
        body.theme-1 {
            --primary-theme-color: #0CAF60;
            --primary-theme-color-rgb: 12, 175, 96;
            --primary-theme-gradient: linear-gradient(90deg, #0CAF60 0%, #0db86b 100%);
            --primary-theme-shadow: rgba(12, 175, 96, 0.15);
        }
        body.theme-2 {
            --primary-theme-color: #75C251;
            --primary-theme-color-rgb: 117, 194, 81;
            --primary-theme-gradient: linear-gradient(90deg, #75C251 0%, #86d65f 100%);
            --primary-theme-shadow: rgba(117, 194, 81, 0.15);
        }
        body.theme-3 {
            --primary-theme-color: #584ED2;
            --primary-theme-color-rgb: 88, 78, 210;
            --primary-theme-gradient: linear-gradient(90deg, #584ED2 0%, #6e64e5 100%);
            --primary-theme-shadow: rgba(88, 78, 210, 0.15);
        }
        body.theme-4 {
            --primary-theme-color: #145388;
            --primary-theme-color-rgb: 20, 83, 136;
            --primary-theme-gradient: linear-gradient(90deg, #145388 0%, #1a6ab0 100%);
            --primary-theme-shadow: rgba(20, 83, 136, 0.15);
        }
        body.theme-5 {
            --primary-theme-color: #B9406B;
            --primary-theme-color-rgb: 185, 64, 107;
            --primary-theme-gradient: linear-gradient(90deg, #B9406B 0%, #ce4c7a 100%);
            --primary-theme-shadow: rgba(185, 64, 107, 0.15);
        }
        body.theme-6 {
            --primary-theme-color: #008ECC;
            --primary-theme-color-rgb: 0, 142, 204;
            --primary-theme-gradient: linear-gradient(90deg, #008ECC 0%, #00a4eb 100%);
            --primary-theme-shadow: rgba(0, 142, 204, 0.15);
        }
        body.theme-7 {
            --primary-theme-color: #922C88;
            --primary-theme-color-rgb: 146, 44, 136;
            --primary-theme-gradient: linear-gradient(90deg, #922C88 0%, #aa379e 100%);
            --primary-theme-shadow: rgba(146, 44, 136, 0.15);
        }
        body.theme-8 {
            --primary-theme-color: #C0A145;
            --primary-theme-color-rgb: 192, 161, 69;
            --primary-theme-gradient: linear-gradient(90deg, #C0A145 0%, #d8b752 100%);
            --primary-theme-shadow: rgba(192, 161, 69, 0.15);
        }
        body.theme-9 {
            --primary-theme-color: #48494B;
            --primary-theme-color-rgb: 72, 73, 75;
            --primary-theme-gradient: linear-gradient(90deg, #48494B 0%, #5d5e61 100%);
            --primary-theme-shadow: rgba(72, 73, 75, 0.15);
        }
        body.theme-10 {
            --primary-theme-color: #0C7785;
            --primary-theme-color-rgb: 12, 119, 133;
            --primary-theme-gradient: linear-gradient(90deg, #0C7785 0%, #0e8c9c 100%);
            --primary-theme-shadow: rgba(12, 119, 133, 0.15);
        }
        body.custom-color {
            --primary-theme-color: var(--color-customColor);
            --primary-theme-color-rgb: {{ $themeColorRgb }};
            --primary-theme-gradient: linear-gradient(90deg, var(--color-customColor) 0%, rgba(var(--primary-theme-color-rgb), 0.85) 100%);
            --primary-theme-shadow: rgba(var(--primary-theme-color-rgb), 0.15);
        }

        /* Modern Glassmorphic Dashboard Styles */
        .premium-stat-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .premium-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.12);
        }

        .premium-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 100%);
            z-index: -1;
        }

        body.theme-1 .gradient-primary { background: linear-gradient(135deg, #0CAF60 0%, #0db86b 100%) !important; }
        body.theme-2 .gradient-primary { background: linear-gradient(135deg, #75C251 0%, #86d65f 100%) !important; }
        body.theme-3 .gradient-primary { background: linear-gradient(135deg, #584ED2 0%, #6e64e5 100%) !important; }
        body.theme-4 .gradient-primary { background: linear-gradient(135deg, #145388 0%, #1a6ab0 100%) !important; }
        body.theme-5 .gradient-primary { background: linear-gradient(135deg, #B9406B 0%, #ce4c7a 100%) !important; }
        body.theme-6 .gradient-primary { background: linear-gradient(135deg, #008ECC 0%, #00a4eb 100%) !important; }
        body.theme-7 .gradient-primary { background: linear-gradient(135deg, #922C88 0%, #aa379e 100%) !important; }
        body.theme-8 .gradient-primary { background: linear-gradient(135deg, #C0A145 0%, #d8b752 100%) !important; }
        body.theme-9 .gradient-primary { background: linear-gradient(135deg, #48494B 0%, #5d5e61 100%) !important; }
        body.theme-10 .gradient-primary { background: linear-gradient(135deg, #0C7785 0%, #0e8c9c 100%) !important; }
        body.custom-color .gradient-primary { background: linear-gradient(135deg, var(--color-customColor) 0%, rgba(var(--primary-theme-color-rgb), 0.85) 100%) !important; }

        .gradient-success { background: linear-gradient(135deg, #2dce89 0%, #2dcecc 100%) !important; }
        .gradient-warning { background: linear-gradient(135deg, #f5365c 0%, #f56036 100%) !important; }
        .gradient-info { background: linear-gradient(135deg, #11cdef 0%, #1171ef 100%) !important; }

        .premium-stat-card .theme-avtar {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        /* Nav Tabs Custom Styling */
        .premium-nav-tabs {
            border: none;
            background: #f1f3f9;
            padding: 4px;
            border-radius: 12px;
            display: inline-flex;
            margin-bottom: 24px;
        }

        .premium-nav-tabs .nav-link {
            border: none;
            border-radius: 10px;
            padding: 10px 24px;
            font-weight: 600;
            color: #5c6f84;
            transition: all 0.2s ease;
            background: transparent;
        }

        .premium-nav-tabs .nav-link.active {
            background: #fff;
            color: var(--primary-theme-color);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        /* Leaderboard Panel Styles */
        .leaderboard-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .leaderboard-card {
            transition: all 0.2s ease;
            border-left: 4px solid transparent !important;
        }

        .leaderboard-card:hover {
            background-color: #f8f9fa;
            transform: translateX(4px);
        }

        .leaderboard-card.rank-0 { border-left-color: #ffd700 !important; } /* Gold */
        .leaderboard-card.rank-1 { border-left-color: #c0c0c0 !important; } /* Silver */
        .leaderboard-card.rank-2 { border-left-color: #cd7f32 !important; } /* Bronze */

        /* Collapsible Tree Grid Premium Styles */
        .tree-table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            width: 100% !important;
        }
        
        .tree-table thead th {
            background-color: #f8fafc !important;
            color: #475569 !important;
            font-weight: 600 !important;
            font-size: 0.78rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            padding: 14px 18px !important;
            border-bottom: 2px solid #e2e8f0 !important;
            border-top: none !important;
        }

        .target-group-header-row {
            background: linear-gradient(90deg, #f8fafc 0%, #ffffff 100%) !important;
            transition: all 0.2s ease;
        }

        .target-group-header-row td {
            padding: 16px 24px !important;
            border-top: 1px solid #e2e8f0 !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        
        .tree-row {
            background-color: #ffffff !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .tree-row td {
            padding: 16px 18px !important;
            border-bottom: 1px solid #f1f5f9 !important;
            border-top: none !important;
            vertical-align: middle !important;
        }

        .tree-row:hover {
            background-color: #fafbfc !important;
            box-shadow: inset 4px 0 0 var(--primary-theme-color) !important;
        }

        .tree-row.sub-row {
            background-color: #fcfdfe !important;
        }
        
        /* Guide line style for nested rows */
        .tree-guide-line {
            display: inline-block;
            width: 2px;
            background-color: #cbd5e1;
            height: 32px;
            margin-right: 12px;
            vertical-align: middle;
            position: relative;
        }
        
        /* Badges Styling */
        .premium-badge {
            font-weight: 600 !important;
            font-size: 11px !important;
            padding: 6px 12px !important;
            border-radius: 8px !important;
            letter-spacing: 0.3px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .premium-badge-automated {
            background-color: rgba(45, 206, 137, 0.12) !important;
            color: #0d8a52 !important;
            border: 1px solid rgba(45, 206, 137, 0.2) !important;
        }

        .premium-badge-manual {
            background-color: rgba(94, 114, 228, 0.12) !important;
            color: #2b43b4 !important;
            border: 1px solid rgba(94, 114, 228, 0.2) !important;
        }

        .premium-badge-dept {
            background-color: rgba(17, 205, 239, 0.12) !important;
            color: #097286 !important;
            border: 1px solid rgba(17, 205, 239, 0.2) !important;
        }

        .premium-badge-team {
            background-color: rgba(255, 159, 67, 0.12) !important;
            color: #b25d08 !important;
            border: 1px solid rgba(255, 159, 67, 0.2) !important;
        }

        .premium-badge-member {
            background-color: rgba(108, 117, 125, 0.12) !important;
            color: #495057 !important;
            border: 1px solid rgba(108, 117, 125, 0.2) !important;
        }
        
        /* Status Badges */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 700 !important;
            font-size: 10px !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 5px 12px !important;
            border-radius: 20px !important;
        }
        .status-pill-completed {
            background-color: #e6fcf5 !important;
            color: #0ca678 !important;
            border: 1px solid #c3fae8 !important;
        }
        .status-pill-pending {
            background-color: #fff9db !important;
            color: #f59f00 !important;
            border: 1px solid #fff3bf !important;
        }
        .status-pill-missed {
            background-color: #fff5f5 !important;
            color: #fa5252 !important;
            border: 1px solid #ffe3e3 !important;
        }

        /* Quota values formatting */
        .quota-val-main {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
        }
        
        .quota-val-achieved {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0d8a52;
        }

        .quota-val-remaining {
            padding: 5px 10px !important;
            border-radius: 6px !important;
            font-weight: 700;
            font-size: 11px;
            display: inline-block;
        }
        
        /* Nesting Guide Spans */
        .nesting-arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            color: #94a3b8;
            margin-right: 6px;
            transition: color 0.2s ease;
        }
        .nesting-arrow:hover {
            color: var(--primary-theme-color);
        }

        /* Progress tracks styling */
        .progress-track-premium {
            height: 8px !important;
            background-color: #f1f5f9;
            border-radius: 100px;
            overflow: hidden;
            width: 100%;
            border: 1px solid #e2e8f0;
        }
        
        .progress-bar-premium {
            height: 100%;
            border-radius: 100px;
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Divide button premium hover effects */
        .btn-divide-premium {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        .btn-divide-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(var(--primary-theme-color-rgb), 0.25) !important;
            background-color: var(--primary-theme-color) !important;
            border-color: var(--primary-theme-color) !important;
            color: #ffffff !important;
        }

        .tree-toggle {
            cursor: pointer;
            transition: transform 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 4px;
        }

        .tree-toggle:hover {
            background-color: rgba(0,0,0,0.05);
        }

        .tree-toggle.collapsed i {
            transform: rotate(-90deg);
        }

        .tree-toggle i {
            transition: transform 0.2s ease;
        }

        /* Tree guides and Card styling for card list view */
        .target-item-row {
            position: relative;
            margin-bottom: 12px;
            border-radius: 12px !important;
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
            background: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.01);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .target-item-row:hover {
            transform: translateX(4px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.04) !important;
            background: #fafbfc;
        }
        .target-item-row.level-1 {
            margin-left: 0;
            border-left: 4px solid var(--primary-theme-color) !important;
        }
        .target-item-row.level-2 {
            margin-left: 28px;
            border-left: 4px solid #11cdef !important;
        }
        .target-item-row.level-3 {
            margin-left: 56px;
            border-left: 4px solid #ff9f43 !important;
        }
        .target-item-row.level-4 {
            margin-left: 84px;
            border-left: 4px solid #f5365c !important;
        }
        .target-item-row.level-5 {
            margin-left: 112px;
            border-left: 4px solid #6c757d !important;
        }

        /* Status border colors override */
        .target-item-row.status-Completed {
            border-left-color: #2dce89 !important;
        }
        .target-item-row.status-Pending {
            border-left-color: #ff9f43 !important;
        }
        .target-item-row.status-Missed {
            border-left-color: #f5365c !important;
        }

        /* Connecting branches */
        .target-item-row[class*="level-"]::before {
            content: '';
            position: absolute;
            left: -18px;
            top: -16px;
            bottom: 50%;
            width: 16px;
            border-left: 2px dashed rgba(var(--primary-theme-color-rgb), 0.25);
            border-bottom: 2px dashed rgba(var(--primary-theme-color-rgb), 0.25);
        }
        .target-item-row.level-1::before {
            display: none !important;
        }

        /* Kanban Board Styles */
        .kanban-board-container {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            padding-bottom: 12px;
            align-items: flex-start;
        }

        .kanban-column {
            flex: 1;
            min-width: 320px;
            background-color: #f4f6fa;
            border-radius: 16px;
            padding: 16px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
            transition: background-color 0.3s ease;
        }

        .kanban-column.drag-over {
            background-color: rgba(var(--primary-theme-color-rgb), 0.05);
            border: 2px dashed var(--primary-theme-color);
        }

        .kanban-column-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .kanban-column-title {
            font-weight: 700;
            font-size: 1rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .kanban-cards-container {
            min-height: 500px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .kanban-card {
            background: #fff;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 4px 6px rgba(50,50,93,.05), 0 1px 3px rgba(0,0,0,.03);
            cursor: grab;
            transition: all 0.2s ease;
            border: 1px solid #e9ecef;
        }

        .kanban-card:active {
            cursor: grabbing;
        }

        .kanban-card.dragging {
            opacity: 0.4;
            transform: scale(0.98);
        }

        .kanban-card:hover {
            box-shadow: 0 8px 16px rgba(50,50,93,.08), 0 2px 4px rgba(0,0,0,.05);
            transform: translateY(-2px);
        }

        .kanban-card-title {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 8px;
        }

        .kanban-card-meta {
            font-size: 0.75rem;
            color: #8898aa;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        /* Adjust button micro-animations */
        .adjust-btn {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50% !important;
            padding: 0 !important;
            font-weight: bold;
            transition: all 0.2s ease;
        }

        .adjust-btn:hover {
            background-color: var(--primary-theme-color) !important;
            color: #fff !important;
            border-color: var(--primary-theme-color) !important;
        }

        /* Targets Premium Overhaul Custom CSS */
        .target-card {
            border: 1px solid rgba(0,0,0,0.06) !important;
            border-left: 5px solid #ffa800 !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.02), 0 5px 10px -5px rgba(0, 0, 0, 0.01) !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
            background: #ffffff !important;
            overflow: hidden;
        }
        .target-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.08) !important;
        }
        .target-card.status-completed {
            border-left-color: #2dce89 !important;
        }
        .target-card.status-pending {
            border-left-color: #ff9f43 !important;
        }
        .target-card.status-missed {
            border-left-color: #f5365c !important;
        }

        /* High-fidelity Badge Caps */
        .badge-status-pending {
            background: linear-gradient(135deg, rgba(255, 159, 67, 0.08) 0%, rgba(255, 159, 67, 0.04) 100%) !important;
            color: #ff9f43 !important;
            border: 1.5px solid rgba(255, 159, 67, 0.15) !important;
            font-weight: 700 !important;
        }
        .badge-status-completed {
            background: linear-gradient(135deg, rgba(45, 206, 137, 0.08) 0%, rgba(45, 206, 137, 0.04) 100%) !important;
            color: #2dce89 !important;
            border: 1.5px solid rgba(45, 206, 137, 0.15) !important;
            font-weight: 700 !important;
        }
        .badge-status-missed {
            background: linear-gradient(135deg, rgba(245, 54, 92, 0.08) 0%, rgba(245, 54, 92, 0.04) 100%) !important;
            color: #f5365c !important;
            border: 1.5px solid rgba(245, 54, 92, 0.15) !important;
            font-weight: 700 !important;
        }
        .badge-type-automated {
            background: linear-gradient(135deg, rgba(var(--primary-theme-color-rgb), 0.08) 0%, rgba(var(--primary-theme-color-rgb), 0.04) 100%) !important;
            color: var(--primary-theme-color) !important;
            border: 1.5px solid rgba(var(--primary-theme-color-rgb), 0.15) !important;
            font-weight: 700 !important;
        }
        .badge-type-manual {
            background: linear-gradient(135deg, rgba(23, 162, 184, 0.08) 0%, rgba(23, 162, 184, 0.04) 100%) !important;
            color: #17a2b8 !important;
            border: 1.5px solid rgba(23, 162, 184, 0.15) !important;
            font-weight: 700 !important;
        }

        .target-card-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1e293b;
            text-transform: capitalize;
            letter-spacing: -0.3px;
        }

        /* Pipeline nested rules card */
        .pipeline-panel {
            background: linear-gradient(135deg, rgba(var(--primary-theme-color-rgb), 0.02) 0%, rgba(var(--primary-theme-color-rgb), 0.01) 100%) !important;
            border: 1px dashed rgba(var(--primary-theme-color-rgb), 0.2) !important;
            border-radius: 12px !important;
            padding: 12px !important;
            margin-bottom: 16px !important;
        }

        /* Metadata capsules */
        .metadata-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            background: #f8fafc;
            border: 1px solid rgba(0, 0, 0, 0.03);
            color: #475569;
            transition: all 0.2s;
        }
        .metadata-pill:hover {
            background: #f1f5f9;
            color: #1e293b;
        }
        .metadata-pill i {
            font-size: 0.9rem;
        }
        .metadata-pill.pill-assignee {
            background: rgba(13, 202, 240, 0.05);
            color: #0dcaf0;
            border-color: rgba(13, 202, 240, 0.1);
        }
        .metadata-pill.pill-manager {
            background: rgba(var(--primary-theme-color-rgb), 0.05);
            color: var(--primary-theme-color);
            border-color: rgba(var(--primary-theme-color-rgb), 0.1);
        }
        .metadata-pill.pill-timeline {
            background: rgba(108, 117, 125, 0.05);
            color: #6c757d;
            border-color: rgba(108, 117, 125, 0.1);
        }

        .metric-box {
            background: #ffffff !important;
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
            border-radius: 14px !important;
            padding: 12px 6px !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.01) !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        .metric-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.04) !important;
        }
        .border-accent-primary { border-bottom: 3.5px solid var(--primary-theme-color) !important; }
        .border-accent-success { border-bottom: 3.5px solid #2dce89 !important; }
        .border-accent-warning { border-bottom: 3.5px solid #ff9f43 !important; }

        .target-progress-track {
            background: #f1f5f9 !important;
            height: 8px !important;
            border-radius: 6px !important;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.01);
        }
        .target-progress-bar {
            height: 100% !important;
            border-radius: 6px !important;
            background: var(--primary-theme-gradient) !important;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .comparison-btn {
            background: linear-gradient(135deg, rgba(var(--primary-theme-color-rgb), 0.06) 0%, rgba(var(--primary-theme-color-rgb), 0.03) 100%) !important;
            border: 1px solid rgba(var(--primary-theme-color-rgb), 0.15) !important;
            color: var(--primary-theme-color) !important;
            font-weight: 700 !important;
            border-radius: 12px !important;
            transition: all 0.25s ease-in-out !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .comparison-btn:hover {
            background: var(--primary-theme-gradient) !important;
            color: #fff !important;
            border-color: transparent !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px var(--primary-theme-shadow) !important;
        }

        .rank-badge {
            background: linear-gradient(135deg, rgba(var(--primary-theme-color-rgb), 0.1) 0%, rgba(var(--primary-theme-color-rgb), 0.05) 100%) !important;
            color: var(--primary-theme-color) !important;
            font-size: 0.75rem !important;
            font-weight: 800 !important;
            padding: 4px 10px !important;
            border-radius: 8px !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(var(--primary-theme-color-rgb), 0.05);
        }

        /* Unified Choices.js styles for premium look */
        #filterCard .choices__inner {
            border-radius: 10px !important;
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            padding: 5px 12px !important;
            min-height: 43px !important;
            transition: all 0.2s ease;
        }

        #filterCard .choices__inner:hover,
        #filterCard .choices__inner:focus-within {
            border-color: var(--primary-theme-color) !important;
            box-shadow: 0 0 0 2px var(--primary-theme-shadow) !important;
        }

        #filterCard .choices__list--multiple .choices__item {
            border-radius: 6px !important;
            background-color: var(--primary-theme-color) !important;
            border: none !important;
            font-weight: 500;
            padding: 3px 10px;
            font-size: 12px;
            margin-right: 6px;
        }

        #filterCard .choices__list--dropdown {
            border-radius: 10px !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
            border: 1px solid #e2e8f0 !important;
            z-index: 1000 !important;
        }

        #filterCard .choices__list--dropdown .choices__item--selectable.is-highlighted {
            background-color: var(--primary-theme-color) !important;
            color: #ffffff !important;
        }

        /* ── Summary Metrics Widget Styles ── */
        .summary-metrics-container {
            transition: box-shadow 0.3s ease;
        }
        .sm-card {
            transition: transform 0.22s cubic-bezier(0.4,0,0.2,1), box-shadow 0.22s cubic-bezier(0.4,0,0.2,1), border-color 0.22s ease;
        }
        .sm-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(0,0,0,0.35) !important;
            border-color: #2a2a2a !important;
        }
        .sm-main-val {
            transition: opacity 0.18s ease;
        }
        .sm-toggle-btn {
            outline: none !important;
        }
        .sm-toggle-btn:focus {
            box-shadow: none !important;
        }
    </style>
@endpush

@php
    // Lakhs formatter for Summary Metrics
    $smFormatLakhs = function($val) {
        if ($val >= 10000000) { return '₹' . number_format($val / 10000000, 1) . 'Cr'; }
        if ($val >= 100000)   { $l = $val / 100000; return '₹' . (floor($l) == $l ? number_format($l, 0) : number_format($l, 1)) . 'L'; }
        if ($val >= 1000)     { return '₹' . number_format($val / 1000, 1) . 'K'; }
        return '₹' . number_format($val);
    };
@endphp
<div class="row">
    <!-- ═══ Premium "Summary Metrics" Widget ═══ -->
    <div class="col-12 mb-4">
        <div class="summary-metrics-container p-4" id="summaryMetricsWidget"
             style="background: #141414; border-radius: 18px; border: 1px solid #222;">
            {{-- Header row --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:32px;height:32px;background:rgba(99,102,241,0.12);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                    </div>
                    <h6 class="mb-0 fw-bold text-white" style="font-size:1.05rem;letter-spacing:-0.2px;">{{ __('Summary Metrics') }}</h6>
                </div>
                <div class="d-flex gap-2" id="smToggleGroup">
                    <button id="smBtnToday" onclick="smSetView('today')" class="sm-toggle-btn active"
                            style="background:#1e1e1e;color:#ffffff;border:1.5px solid #333;border-radius:10px;padding:8px 22px;font-weight:600;font-size:0.85rem;cursor:pointer;transition:all .2s;">
                        {{ __('Today') }}
                    </button>
                    <button id="smBtnMonth" onclick="smSetView('month')"
                            class="sm-toggle-btn"
                            style="background:transparent;color:#6b6b6b;border:1.5px solid #252525;border-radius:10px;padding:8px 22px;font-weight:600;font-size:0.85rem;cursor:pointer;transition:all .2s;">
                        {{ __('This month') }}
                    </button>
                </div>
            </div>

            {{-- 4 Metric Cards --}}
            <div class="row g-3">
                {{-- Card 1: Total Accounts Open --}}
                <div class="col-xl-3 col-md-6">
                    <div class="sm-card p-4" style="background:#0d0d0d;border:1px solid #1e1e1e;border-radius:14px;">
                        <div class="sm-icon-wrap mb-3"
                             style="width:46px;height:46px;background:rgba(96,165,250,0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <p class="text-muted mb-2" style="font-size:0.88rem;color:#888 !important;">{{ __('Total accounts open') }}</p>
                        <div class="sm-main-val mb-1" style="font-size:2.2rem;font-weight:800;color:#fff;letter-spacing:-1px;line-height:1;">
                            <span class="sm-val-today">{{ number_format($summaryMetrics['accounts_today']) }}</span>
                            <span class="sm-val-month d-none">{{ number_format($summaryMetrics['accounts_this_month']) }}</span>
                        </div>
                        <div class="sm-delta" style="font-size:0.82rem;font-weight:600;color:#4ade80;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2.5" style="vertical-align:middle;margin-right:3px;"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                            <span class="sm-delta-today">+{{ number_format($summaryMetrics['accounts_today']) }} {{ __('today') }}</span>
                            <span class="sm-delta-month d-none">+{{ number_format($summaryMetrics['accounts_this_month']) }} {{ __('this month') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Total FTD --}}
                <div class="col-xl-3 col-md-6">
                    <div class="sm-card p-4" style="background:#0d0d0d;border:1px solid #1e1e1e;border-radius:14px;">
                        <div class="sm-icon-wrap mb-3"
                             style="width:46px;height:46px;background:rgba(52,211,153,0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        </div>
                        <p class="text-muted mb-2" style="font-size:0.88rem;color:#888 !important;">{{ __('Total FTD') }}</p>
                        <div class="sm-main-val mb-1" style="font-size:2.2rem;font-weight:800;color:#fff;letter-spacing:-1px;line-height:1;">
                            <span class="sm-val-today">{{ number_format($summaryMetrics['ftd_today']) }}</span>
                            <span class="sm-val-month d-none">{{ number_format($summaryMetrics['ftd_this_month']) }}</span>
                        </div>
                        <div class="sm-delta" style="font-size:0.82rem;font-weight:600;color:#4ade80;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2.5" style="vertical-align:middle;margin-right:3px;"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                            <span class="sm-delta-today">+{{ number_format($summaryMetrics['ftd_today']) }} {{ __('today') }}</span>
                            <span class="sm-delta-month d-none">+{{ number_format($summaryMetrics['ftd_this_month']) }} {{ __('this month') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Total Revenue --}}
                <div class="col-xl-3 col-md-6">
                    <div class="sm-card p-4" style="background:#0d0d0d;border:1px solid #1e1e1e;border-radius:14px;">
                        <div class="sm-icon-wrap mb-3"
                             style="width:46px;height:46px;background:rgba(251,191,36,0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><line x1="12" y1="6" x2="12" y2="8"/><line x1="12" y1="16" x2="12" y2="18"/></svg>
                        </div>
                        <p class="text-muted mb-2" style="font-size:0.88rem;color:#888 !important;">{{ __('Total revenue') }}</p>
                        <div class="sm-main-val mb-1" style="font-size:2.2rem;font-weight:800;color:#fff;letter-spacing:-1px;line-height:1;">
                            <span class="sm-val-today">{{ $smFormatLakhs($summaryMetrics['revenue_today']) }}</span>
                            <span class="sm-val-month d-none">{{ $smFormatLakhs($summaryMetrics['revenue_this_month']) }}</span>
                        </div>
                        <div class="sm-delta" style="font-size:0.82rem;font-weight:600;color:#4ade80;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2.5" style="vertical-align:middle;margin-right:3px;"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                            <span class="sm-delta-today">+{{ $smFormatLakhs($summaryMetrics['revenue_today']) }} {{ __('today') }}</span>
                            <span class="sm-delta-month d-none">+{{ $smFormatLakhs($summaryMetrics['revenue_this_month']) }} {{ __('this month') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Card 4: Dormant Accounts --}}
                <div class="col-xl-3 col-md-6">
                    <div class="sm-card p-4" style="background:#0d0d0d;border:1px solid #1e1e1e;border-radius:14px;">
                        <div class="sm-icon-wrap mb-3"
                             style="width:46px;height:46px;background:rgba(248,113,113,0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                        </div>
                        <p class="text-muted mb-2" style="font-size:0.88rem;color:#888 !important;">{{ __('Dormant accounts') }}</p>
                        <div class="sm-main-val mb-1" style="font-size:2.2rem;font-weight:800;color:#fff;letter-spacing:-1px;line-height:1;">
                            {{ number_format($summaryMetrics['dormant_count']) }}
                        </div>
                        <div style="font-size:0.82rem;font-weight:600;color:#f87171;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2.5" style="vertical-align:middle;margin-right:3px;"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
                            {{ __('needs follow-up') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card Collapse -->
    <div class="col-12">
        <div class="collapse {{ request()->has('assigned_to') || request()->has('status') || request()->has('department_id') ? 'show' : '' }} mb-4" id="filterCard">
            <div class="card" style="background-color: #fdfdfd; border: 1px solid #e2e8f0; border-radius: 16px;">
                <div class="card-body">
                    {{ Form::open(['route' => ['targets.index'], 'method' => 'GET', 'id' => 'target_filter']) }}
                    <div class="row align-items-center justify-content-end g-3">
                        @if($isManager)
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('assigned_to', __('Assigned To'), ['class' => 'form-label fw-bold']) }}
                                {{ Form::select('assigned_to[]', $subordinateUsers, request('assigned_to'), ['class' => 'form-control choices', 'id' => 'assigned_to', 'multiple' => '', 'searchEnabled' => 'true', 'data-placeholder' => __('Select Assigned To')]) }}
                            </div>
                        </div>
                        @endif
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('department_id', __('Department'), ['class' => 'form-label fw-bold']) }}
                                {{ Form::select('department_id[]', $departments, request('department_id'), ['class' => 'form-control choices', 'id' => 'department_id', 'multiple' => '', 'searchEnabled' => 'true', 'data-placeholder' => __('Select Department')]) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('team_id', __('Team'), ['class' => 'form-label fw-bold']) }}
                                {{ Form::select('team_id[]', $teams, request('team_id'), ['class' => 'form-control choices', 'id' => 'team_id', 'multiple' => '', 'searchEnabled' => 'true', 'data-placeholder' => __('Select Team')]) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('status', __('Status'), ['class' => 'form-label fw-bold']) }}
                                {{ Form::select('status[]', $statuses, request('status'), ['class' => 'form-control choices', 'id' => 'status', 'multiple' => '', 'searchEnabled' => 'true', 'data-placeholder' => __('Select Status')]) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label fw-bold']) }}
                                {{ Form::text('start_date', request('start_date'), ['class' => 'form-control flatpickr-input', 'placeholder' => __('YYYY-MM-DD')]) }}
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label fw-bold']) }}
                                {{ Form::text('end_date', request('end_date'), ['class' => 'form-control flatpickr-input', 'placeholder' => __('YYYY-MM-DD')]) }}
                            </div>
                        </div>
                        <div class="col-auto float-end ms-2 d-flex gap-2">
                            <button class="btn btn-primary" type="submit" data-bs-toggle="tooltip" title="{{ __('Apply') }}">
                                <i class="ti ti-search" ></i> {{ __('Search') }}
                            </button>
                            <a href="{{ route('targets.index') }}" class="btn btn-danger" data-bs-toggle="tooltip"
                                title="{{ __('Reset Filters') }}">
                                <i class="ti ti-refresh text-white-off"></i>
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Tab bar -->
    <div class="col-12 text-center">
        <ul class="nav nav-tabs premium-nav-tabs" id="targetTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">
                    <i class="ti ti-chart-bar me-1"></i> {{ __('Dashboard & Insights') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tree-tab" data-bs-toggle="tab" data-bs-target="#tree" type="button" role="tab" aria-controls="tree" aria-selected="false">
                    <i class="ti ti-target me-1"></i> {{ __('Targets Hub') }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="comparison-tab" data-bs-toggle="tab" data-bs-target="#comparison" type="button" role="tab" aria-controls="comparison" aria-selected="false">
                    <i class="ti ti-arrows-left-right me-1"></i> {{ __('Comparison Hub') }}
                </button>
            </li>

            @if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="templates-tab" data-bs-toggle="tab" data-bs-target="#templates" type="button" role="tab" aria-controls="templates" aria-selected="false">
                    <i class="ti ti-template me-1"></i> {{ __('Target Templates') }}
                </button>
            </li>
            @endif
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="incentives-tab" data-bs-toggle="tab" data-bs-target="#incentives" type="button" role="tab" aria-controls="incentives" aria-selected="false">
                    <i class="ti ti-cash me-1"></i> {{ __('Incentive Ledger') }}
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Contents -->
    <div class="col-12">
        <div class="tab-content" id="targetTabsContent">
            
            <!-- TAB 1: OVERVIEW & ANALYTICS -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                
                <!-- Premium Dark-Themed "My Targets" Dashboard Widget -->
                @php
                    $accExists = !empty($myAccountTarget);
                    $accAchieved = $accExists ? $myAccountTarget->achieved_value : 0;
                    $accTargetVal = $accExists ? $myAccountTarget->target_value : 0;
                    $accPercent = $accTargetVal > 0 ? min(100, round(($accAchieved / $accTargetVal) * 100)) : 0;
                    $accRemaining = max(0, $accTargetVal - $accAchieved);

                    $ftdExists = !empty($myFtdTarget);
                    $ftdAchieved = $ftdExists ? $myFtdTarget->achieved_value : 0;
                    $ftdTargetVal = $ftdExists ? $myFtdTarget->target_value : 0;
                    $ftdPercent = $ftdTargetVal > 0 ? min(100, round(($ftdAchieved / $ftdTargetVal) * 100)) : 0;
                    $ftdRemaining = max(0, $ftdTargetVal - $ftdAchieved);

                    $revExists = !empty($myRevenueTarget);
                    $revAchieved = $revExists ? $myRevenueTarget->achieved_value : 0;
                    $revTargetVal = $revExists ? $myRevenueTarget->target_value : 0;
                    $revPercent = $revTargetVal > 0 ? min(100, round(($revAchieved / $revTargetVal) * 100)) : 0;
                    $revRemaining = max(0, $revTargetVal - $revAchieved);
                    
                    // Lakhs formatter helper
                    $formatLakhs = function($val) {
                        if ($val >= 100000) {
                            $lakhs = $val / 100000;
                            return '₹' . (floor($lakhs) == $lakhs ? number_format($lakhs, 0) : number_format($lakhs, 1)) . 'L';
                        }
                        return '₹' . number_format($val);
                    };
                @endphp
                <div class="my-targets-widget-container p-4 mb-4" style="background-color: #1a1a1a; border-radius: 16px; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-target me-2" style="color: #ff5e36; font-size: 24px; font-weight: bold;"></i>
                            <h5 class="text-white mb-0 fw-bold" style="letter-spacing: -0.3px; font-size: 1.25rem;">{{ __('My Targets') }}</h5>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="btn-this-month" style="background-color: transparent; color: #ffffff; border: 1.5px solid #3d3d3d; border-radius: 8px; padding: 8px 18px; font-weight: 600; font-size: 0.85rem; cursor: pointer;">
                                {{ __('This month') }}
                            </span>
                            <span class="btn-date-range" style="background-color: transparent; color: #a1a1a1; border: 1.5px solid #2d2d2d; border-radius: 8px; padding: 8px 18px; font-weight: 600; font-size: 0.85rem; cursor: pointer;">
                                {{ __('Date range') }}
                            </span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Card 1: Account Target -->
                        <div class="col-md-4 col-sm-12">
                            <div class="my-target-card p-4" style="background-color: #111111; border: 1px solid #222222; border-radius: 14px; position: relative;">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="text-muted fw-medium" style="font-size: 0.95rem; color: #a1a1a1 !important;">{{ __('Account target') }}</span>
                                    <span class="badge px-3 py-1 rounded-pill" style="background-color: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.78rem;">
                                        {{ $accPercent }}%
                                    </span>
                                </div>
                                <div class="d-flex align-items-baseline gap-1 mt-2 mb-3">
                                    <h3 class="text-white mb-0 fw-bold" style="font-size: 2.1rem; letter-spacing: -0.5px; font-family: 'Plus Jakarta Sans', sans-serif;">{{ $accAchieved }}</h3>
                                    <span class="text-muted" style="font-size: 1.25rem; color: #555555 !important;">/ {{ $accTargetVal }}</span>
                                </div>
                                <div class="progress-track" style="height: 6px; background-color: #1e1e1e; border-radius: 100px; overflow: hidden; margin-bottom: 14px;">
                                    <div class="progress-bar-fill" style="width: {{ $accPercent }}%; height: 100%; background-color: #3b82f6; border-radius: 100px;"></div>
                                </div>
                                <span class="text-muted" style="font-size: 0.85rem; color: #7f7f7f !important;">
                                    @if($accExists)
                                        @if($accRemaining > 0)
                                            {{ __(':count more needed', ['count' => $accRemaining]) }}
                                        @else
                                            {{ __('Goal Achieved!') }}
                                        @endif
                                    @else
                                        {{ __('No active target') }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        <!-- Card 2: FTD Target -->
                        <div class="col-md-4 col-sm-12">
                            <div class="my-target-card p-4" style="background-color: #111111; border: 1px solid #222222; border-radius: 14px; position: relative;">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="text-muted fw-medium" style="font-size: 0.95rem; color: #a1a1a1 !important;">{{ __('FTD target') }}</span>
                                    <span class="badge px-3 py-1 rounded-pill" style="background-color: #d1fae5; color: #065f46; font-weight: 700; font-size: 0.78rem;">
                                        {{ $ftdPercent }}%
                                    </span>
                                </div>
                                <div class="d-flex align-items-baseline gap-1 mt-2 mb-3">
                                    <h3 class="text-white mb-0 fw-bold" style="font-size: 2.1rem; letter-spacing: -0.5px; font-family: 'Plus Jakarta Sans', sans-serif;">{{ $ftdAchieved }}</h3>
                                    <span class="text-muted" style="font-size: 1.25rem; color: #555555 !important;">/ {{ $ftdTargetVal }}</span>
                                </div>
                                <div class="progress-track" style="height: 6px; background-color: #1e1e1e; border-radius: 100px; overflow: hidden; margin-bottom: 14px;">
                                    <div class="progress-bar-fill" style="width: {{ $ftdPercent }}%; height: 100%; background-color: #10b981; border-radius: 100px;"></div>
                                </div>
                                <span class="text-muted" style="font-size: 0.85rem; color: #7f7f7f !important;">
                                    @if($ftdExists)
                                        @if($ftdRemaining > 0)
                                            {{ __(':count more needed', ['count' => $ftdRemaining]) }}
                                        @else
                                            {{ __('Goal Achieved!') }}
                                        @endif
                                    @else
                                        {{ __('No active target') }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        <!-- Card 3: Revenue Target -->
                        <div class="col-md-4 col-sm-12">
                            <div class="my-target-card p-4" style="background-color: #111111; border: 1px solid #222222; border-radius: 14px; position: relative;">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="text-muted fw-medium" style="font-size: 0.95rem; color: #a1a1a1 !important;">{{ __('Revenue target') }}</span>
                                    <span class="badge px-3 py-1 rounded-pill" style="background-color: #fef3c7; color: #92400e; font-weight: 700; font-size: 0.78rem;">
                                        {{ $revPercent }}%
                                    </span>
                                </div>
                                <div class="d-flex align-items-baseline gap-1 mt-2 mb-3">
                                    <h3 class="text-white mb-0 fw-bold" style="font-size: 2.1rem; letter-spacing: -0.5px; font-family: 'Plus Jakarta Sans', sans-serif;">{{ $formatLakhs($revAchieved) }}</h3>
                                    <span class="text-muted" style="font-size: 1.25rem; color: #555555 !important;">/ {{ $formatLakhs($revTargetVal) }}</span>
                                </div>
                                <div class="progress-track" style="height: 6px; background-color: #1e1e1e; border-radius: 100px; overflow: hidden; margin-bottom: 14px;">
                                    <div class="progress-bar-fill" style="width: {{ $revPercent }}%; height: 100%; background-color: #f59e0b; border-radius: 100px;"></div>
                                </div>
                                <span class="text-muted" style="font-size: 0.85rem; color: #7f7f7f !important;">
                                    @if($revExists)
                                        @if($revRemaining > 0)
                                            {{ __(':count more needed', ['count' => $formatLakhs($revRemaining)]) }}
                                        @else
                                            {{ __('Goal Achieved!') }}
                                        @endif
                                    @else
                                        {{ __('No active target') }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Monthly Targets Overview — Premium Redesign -->
                    <div class="col-xl-8 col-lg-12 col-md-12 mb-4">
                        <div class="card h-100 shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
                            <div class="card-header border-0 bg-transparent pt-4 pb-2 px-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="mb-0 fw-bold text-dark">{{ __('Monthly Target Trends') }}</h5>
                                    <small class="text-muted">{{ date('Y') }} — {{ __('All months overview') }}</small>
                                </div>
                                <span class="badge bg-light text-secondary fw-semibold rounded-pill px-3 py-2 border" style="font-size: 0.75rem;">
                                    <i class="ti ti-calendar-stats me-1"></i>{{ date('Y') }}
                                </span>
                            </div>

                            {{-- Compact chart --}}
                            <div class="px-4 pb-0">
                                <div id="monthly-trends-chart" style="min-height: 180px;"></div>
                            </div>

                            {{-- Month cards --}}
                            <div class="card-body px-4 pt-2 pb-4">
                                @php
                                    $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                                    $hasAnyTargets = false;
                                @endphp

                                @foreach($monthNames as $mIdx => $mShort)
                                    @php
                                        $mNumber   = $mIdx + 1;
                                        $mLabel    = date('F', mktime(0,0,0,$mNumber,1));
                                        $mTargets  = $monthlyTargetsList[$mNumber] ?? [];
                                        $mTotal    = collect($mTargets)->sum('target_value');
                                        $mAchieved = collect($mTargets)->sum('achieved_value');
                                        $mRate     = $mTotal > 0 ? round(($mAchieved / $mTotal) * 100, 1) : 0;
                                        $hasTargets = count($mTargets) > 0;
                                        if ($hasTargets) $hasAnyTargets = true;
                                        $isCurrentMonth = ($mNumber == (int)date('n'));
                                        $isPast = ($mNumber < (int)date('n'));
                                        $isFuture = ($mNumber > (int)date('n'));
                                        $rateColor = $mRate >= 80 ? '#16a34a' : ($mRate >= 45 ? '#2563eb' : '#dc2626');
                                        $rateBg    = $mRate >= 80 ? 'rgba(22,163,74,0.07)' : ($mRate >= 45 ? 'rgba(37,99,235,0.07)' : 'rgba(220,38,38,0.07)');
                                        $accentColor = $isCurrentMonth ? '#198754' : ($hasTargets ? '#334155' : '#cbd5e1');
                                    @endphp

                                    <div class="month-accordion mb-2" id="month-block-{{ $mNumber }}">
                                        {{-- Month header bar --}}
                                        <button
                                            class="month-header-btn w-100 d-flex align-items-center justify-content-between px-3 py-2 border-0 text-start"
                                            style="background: {{ $isCurrentMonth ? 'linear-gradient(90deg,rgba(25,135,84,0.07) 0%,rgba(25,135,84,0.02) 100%)' : ($hasTargets ? '#f8fafc' : '#fafafa') }};
                                                   border-radius: 10px; cursor: {{ $hasTargets ? 'pointer' : 'default' }};
                                                   border: 1.5px solid {{ $isCurrentMonth ? 'rgba(25,135,84,0.2)' : ($hasTargets ? '#e2e8f0' : '#f1f5f9') }} !important;"
                                            {{ $hasTargets ? 'data-bs-toggle=collapse data-bs-target=#month-body-'.$mNumber.' aria-expanded='.($isCurrentMonth ? 'true' : 'false') : '' }}>

                                            <div class="d-flex align-items-center gap-3">
                                                {{-- Month pill --}}
                                                <span class="month-pill fw-bold"
                                                      style="width: 42px; height: 42px; border-radius: 10px; display:flex; align-items:center; justify-content:center; font-size:0.72rem; letter-spacing:0.3px;
                                                             background: {{ $isCurrentMonth ? 'rgba(25,135,84,0.12)' : ($hasTargets ? 'rgba(51,65,85,0.07)' : '#f1f5f9') }};
                                                             color: {{ $isCurrentMonth ? '#198754' : ($hasTargets ? '#334155' : '#94a3b8') }};">
                                                    {{ strtoupper($mShort) }}
                                                </span>
                                                <div>
                                                    <span class="d-block fw-bold" style="font-size: 0.92rem; color: {{ $accentColor }};">
                                                        {{ $mLabel }} {{ date('Y') }}
                                                        @if($isCurrentMonth)
                                                            <span class="badge ms-1 rounded-pill" style="font-size:0.65rem; background:rgba(25,135,84,0.1); color:#198754; border:1px solid rgba(25,135,84,0.2);">{{ __('Current') }}</span>
                                                        @endif
                                                    </span>
                                                    <span class="text-muted" style="font-size: 0.75rem;">
                                                        @if($hasTargets)
                                                            {{ count($mTargets) }} {{ count($mTargets) == 1 ? __('target') : __('targets') }}
                                                        @else
                                                            <span style="color:#cbd5e1;">{{ __('No targets') }}</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>

                                            @if($hasTargets)
                                                <div class="d-flex align-items-center gap-3">
                                                    {{-- Stats summary --}}
                                                    <div class="text-end d-none d-md-block">
                                                        <span class="d-block fw-bold text-dark" style="font-size: 0.85rem;">{{ $mTotal }}</span>
                                                        <span class="text-muted" style="font-size: 0.7rem;">{{ __('Target') }}</span>
                                                    </div>
                                                    <div class="text-end d-none d-md-block">
                                                        <span class="d-block fw-bold" style="font-size: 0.85rem; color: #198754;">{{ $mAchieved }}</span>
                                                        <span class="text-muted" style="font-size: 0.7rem;">{{ __('Achieved') }}</span>
                                                    </div>
                                                    {{-- Rate badge --}}
                                                    <span class="fw-bold px-3 py-1 rounded-pill" style="font-size: 0.8rem; background: {{ $rateBg }}; color: {{ $rateColor }}; min-width: 52px; text-align: center; border: 1px solid {{ $rateBg }};">
                                                        {{ $mRate }}%
                                                    </span>
                                                    <i class="ti ti-chevron-down text-muted" style="font-size: 0.85rem; transition: transform 0.2s;"></i>
                                                </div>
                                            @endif
                                        </button>

                                        {{-- Expanded target rows — grouped by Dept / Team / Member --}}
                                        @if($hasTargets)
                                            @php
                                                $mTargetsC = collect($mTargets);
                                                $grpDept   = $mTargetsC->filter(fn($t) => $t->department_id > 0);
                                                $grpTeam   = $mTargetsC->filter(fn($t) => $t->team_id > 0 && !($t->department_id > 0));
                                                $grpMember = $mTargetsC->filter(fn($t) => $t->assigned_to > 0 && !($t->team_id > 0) && !($t->department_id > 0));
                                                $deptGroups = []; $teamGroups = []; $memberGroups = [];
                                                foreach ($grpDept as $t) { $d=$t->department_id; if(!isset($deptGroups[$d])){$deptGroups[$d]=['name'=>$t->department?$t->department->name:'Dept#'.$d,'target'=>0,'achieved'=>0,'count'=>0];} $deptGroups[$d]['target']+=$t->target_value; $deptGroups[$d]['achieved']+=$t->achieved_value; $deptGroups[$d]['count']++; }
                                                foreach ($grpTeam as $t) { $d=$t->team_id; if(!isset($teamGroups[$d])){$teamGroups[$d]=['name'=>$t->team?$t->team->name:'Team#'.$d,'target'=>0,'achieved'=>0,'count'=>0];} $teamGroups[$d]['target']+=$t->target_value; $teamGroups[$d]['achieved']+=$t->achieved_value; $teamGroups[$d]['count']++; }
                                                foreach ($grpMember as $t) { $d=$t->assigned_to; if(!isset($memberGroups[$d])){$memberGroups[$d]=['name'=>$t->assignedToUser?$t->assignedToUser->name:'User#'.$d,'target'=>0,'achieved'=>0,'count'=>0];} $memberGroups[$d]['target']+=$t->target_value; $memberGroups[$d]['achieved']+=$t->achieved_value; $memberGroups[$d]['count']++; }
                                                $hasDrill = count($deptGroups) > 0 || count($teamGroups) > 0 || count($memberGroups) > 0;
                                                $pct = fn($a,$b) => $b > 0 ? min(round($a/$b*100,1),100) : 0;
                                                $clr = fn($r) => $r >= 80 ? ['#16a34a','rgba(22,163,74,0.1)'] : ($r >= 45 ? ['#2563eb','rgba(37,99,235,0.1)'] : ['#dc2626','rgba(220,38,38,0.1)']);
                                            @endphp

                                            <div class="collapse {{ $isCurrentMonth ? 'show' : '' }}" id="month-body-{{ $mNumber }}">
                                                <div class="pt-2 pb-1">
                                                @if($hasDrill)
                                                <div class="dn-container" id="drill-{{ $mNumber }}">

                                                {{-- ═══ LEVEL 0: Overview ═══ --}}
                                                <div class="drill-level" data-level="0" data-month="{{ $mNumber }}">
                                                    @foreach($deptGroups as $did => $dg)
                                                        @php $r=$pct($dg['achieved'],$dg['target']); [$fg,$bg]=$clr($r); @endphp
                                                        <div class="dn-card dept-row" data-drill-dept="{{ $did }}" data-month="{{ $mNumber }}">
                                                            <div class="dn-avatar-wrap" style="background:{{ $bg }};">
                                                                <span class="dn-initials" style="color:{{ $fg }};">{{ strtoupper(substr($dg['name'],0,2)) }}</span>
                                                            </div>
                                                            <div class="dn-body">
                                                                <span class="dn-name">{{ $dg['name'] }}</span>
                                                                <span class="dn-sub">{{ $dg['count'] }} {{ __('target(s)') }}</span>
                                                                <div class="dn-bar-track"><div class="dn-bar-fill" style="width:{{ $r }}%;background:{{ $fg }};"></div></div>
                                                            </div>
                                                            <div class="dn-meta">
                                                                <div class="dn-kv"><span>{{ number_format($dg['target']) }}</span><small>{{ __('Target') }}</small></div>
                                                                <div class="dn-sep-v"></div>
                                                                <div class="dn-kv green"><span>{{ number_format($dg['achieved']) }}</span><small>{{ __('Achieved') }}</small></div>
                                                                <div class="dn-pct-pill" style="background:{{ $bg }};color:{{ $fg }};">{{ $r }}%</div>
                                                            </div>
                                                            <i class="ti ti-chevron-right dn-arrow"></i>
                                                        </div>
                                                    @endforeach
                                                    @foreach($teamGroups as $tid => $tg)
                                                        @php $r=$pct($tg['achieved'],$tg['target']); [$fg,$bg]=$clr($r); @endphp
                                                        <div class="dn-card team-row" data-drill-team="{{ $tid }}" data-month="{{ $mNumber }}">
                                                            <div class="dn-avatar-wrap" style="background:rgba(245,158,11,0.1);">
                                                                <span class="dn-initials" style="color:#d97706;">{{ strtoupper(substr($tg['name'],0,2)) }}</span>
                                                            </div>
                                                            <div class="dn-body">
                                                                <span class="dn-name">{{ $tg['name'] }}</span>
                                                                <span class="dn-sub">{{ $tg['count'] }} {{ __('target(s)') }}</span>
                                                                <div class="dn-bar-track"><div class="dn-bar-fill" style="width:{{ $r }}%;background:{{ $fg }};"></div></div>
                                                            </div>
                                                            <div class="dn-meta">
                                                                <div class="dn-kv"><span>{{ number_format($tg['target']) }}</span><small>{{ __('Target') }}</small></div>
                                                                <div class="dn-sep-v"></div>
                                                                <div class="dn-kv green"><span>{{ number_format($tg['achieved']) }}</span><small>{{ __('Achieved') }}</small></div>
                                                                <div class="dn-pct-pill" style="background:{{ $bg }};color:{{ $fg }};">{{ $r }}%</div>
                                                            </div>
                                                            <i class="ti ti-chevron-right dn-arrow"></i>
                                                        </div>
                                                    @endforeach
                                                    @foreach($memberGroups as $uid => $mg)
                                                        @php $r=$pct($mg['achieved'],$mg['target']); [$fg,$bg]=$clr($r); @endphp
                                                        <div class="dn-card dn-leaf">
                                                            <div class="dn-avatar-wrap" style="background:{{ $bg }};">
                                                                <span class="dn-initials" style="color:{{ $fg }};">{{ strtoupper(substr($mg['name'],0,2)) }}</span>
                                                            </div>
                                                            <div class="dn-body">
                                                                <span class="dn-name">{{ $mg['name'] }}</span>
                                                                <span class="dn-sub">{{ $mg['count'] }} {{ __('target(s)') }}</span>
                                                                <div class="dn-bar-track"><div class="dn-bar-fill" style="width:{{ $r }}%;background:{{ $fg }};"></div></div>
                                                            </div>
                                                            <div class="dn-meta">
                                                                <div class="dn-kv"><span>{{ number_format($mg['target']) }}</span><small>{{ __('Target') }}</small></div>
                                                                <div class="dn-sep-v"></div>
                                                                <div class="dn-kv green"><span>{{ number_format($mg['achieved']) }}</span><small>{{ __('Achieved') }}</small></div>
                                                                <div class="dn-pct-pill" style="background:{{ $bg }};color:{{ $fg }};">{{ $r }}%</div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                {{-- ═══ LEVEL 1: Teams under Dept ═══ --}}
                                                @foreach($deptGroups as $did => $dg)
                                                    @php $dr=$pct($dg['achieved'],$dg['target']); [$dfg,$dbg]=$clr($dr); @endphp
                                                    <div class="drill-level" data-level="1" data-dept="{{ $did }}" data-month="{{ $mNumber }}" style="display:none;">
                                                        <div class="dn-crumb">
                                                            <button class="dn-back-btn drill-back" data-back-to="0" data-month="{{ $mNumber }}"><i class="ti ti-arrow-left"></i> {{ __('Back') }}</button>
                                                            <span class="dn-crumb-sep">›</span>
                                                            <span class="dn-crumb-cur">{{ $dg['name'] }}</span>
                                                        </div>
                                                        <div class="dn-summary-card mb-3" style="background:{{ $dbg }};border-left:3px solid {{ $dfg }};">
                                                            <div class="dn-avatar-wrap sm" style="background:{{ $dbg }};">
                                                                <span class="dn-initials" style="color:{{ $dfg }};font-size:0.65rem;">{{ strtoupper(substr($dg['name'],0,2)) }}</span>
                                                            </div>
                                                            <div style="flex:1;min-width:0;">
                                                                <span style="font-weight:700;font-size:0.85rem;color:#1e293b;display:block;">{{ $dg['name'] }}</span>
                                                                <span style="font-size:0.7rem;color:#64748b;">{{ __('Department Overview') }}</span>
                                                            </div>
                                                            <div class="dn-meta" style="gap:12px;">
                                                                <div class="dn-kv"><span>{{ number_format($dg['target']) }}</span><small>{{ __('Target') }}</small></div>
                                                                <div class="dn-kv green"><span>{{ number_format($dg['achieved']) }}</span><small>{{ __('Achieved') }}</small></div>
                                                                <div class="dn-pct-pill" style="background:{{ $dbg }};color:{{ $dfg }};border:1px solid {{ $dfg }}40;">{{ $dr }}%</div>
                                                            </div>
                                                        </div>
                                                        @forelse($teamGroups as $tid => $tg)
                                                            @php $r=$pct($tg['achieved'],$tg['target']); [$fg,$bg]=$clr($r); @endphp
                                                            <div class="dn-card team-row" data-drill-team="{{ $tid }}" data-month="{{ $mNumber }}">
                                                                <div class="dn-avatar-wrap" style="background:rgba(245,158,11,0.1);">
                                                                    <span class="dn-initials" style="color:#d97706;">{{ strtoupper(substr($tg['name'],0,2)) }}</span>
                                                                </div>
                                                                <div class="dn-body">
                                                                    <span class="dn-name">{{ $tg['name'] }}</span>
                                                                    <span class="dn-sub">{{ $tg['count'] }} {{ __('target(s)') }}</span>
                                                                    <div class="dn-bar-track"><div class="dn-bar-fill" style="width:{{ $r }}%;background:{{ $fg }};"></div></div>
                                                                </div>
                                                                <div class="dn-meta">
                                                                    <div class="dn-kv"><span>{{ number_format($tg['target']) }}</span><small>{{ __('Target') }}</small></div>
                                                                    <div class="dn-sep-v"></div>
                                                                    <div class="dn-kv green"><span>{{ number_format($tg['achieved']) }}</span><small>{{ __('Achieved') }}</small></div>
                                                                    <div class="dn-pct-pill" style="background:{{ $bg }};color:{{ $fg }};">{{ $r }}%</div>
                                                                </div>
                                                                <i class="ti ti-chevron-right dn-arrow"></i>
                                                            </div>
                                                        @empty
                                                            <div class="dn-empty-msg"><i class="ti ti-users-off"></i> {{ __('No teams found') }}</div>
                                                        @endforelse
                                                    </div>
                                                @endforeach

                                                {{-- ═══ LEVEL 2: Members of Team ═══ --}}
                                                @foreach($teamGroups as $tid => $tg)
                                                    @php $tr=$pct($tg['achieved'],$tg['target']); [$tfg,$tbg]=$clr($tr); @endphp
                                                    <div class="drill-level" data-level="2" data-team="{{ $tid }}" data-month="{{ $mNumber }}" style="display:none;">
                                                        <div class="dn-crumb">
                                                            <button class="dn-back-btn drill-back" data-back-to="0" data-month="{{ $mNumber }}"><i class="ti ti-arrow-left"></i> {{ __('Back') }}</button>
                                                            <span class="dn-crumb-sep">›</span>
                                                            <button class="dn-back-btn drill-back-to-dept" data-back-to="1" data-month="{{ $mNumber }}" style="color:#2563eb;">{{ __('Dept') }}</button>
                                                            <span class="dn-crumb-sep">›</span>
                                                            <span class="dn-crumb-cur">{{ $tg['name'] }}</span>
                                                        </div>
                                                        <div class="dn-summary-card mb-3" style="background:rgba(245,158,11,0.07);border-left:3px solid #d97706;">
                                                            <div class="dn-avatar-wrap sm" style="background:rgba(245,158,11,0.15);">
                                                                <span class="dn-initials" style="color:#d97706;font-size:0.65rem;">{{ strtoupper(substr($tg['name'],0,2)) }}</span>
                                                            </div>
                                                            <div style="flex:1;min-width:0;">
                                                                <span style="font-weight:700;font-size:0.85rem;color:#1e293b;display:block;">{{ $tg['name'] }}</span>
                                                                <span style="font-size:0.7rem;color:#64748b;">{{ __('Team Overview') }}</span>
                                                            </div>
                                                            <div class="dn-meta" style="gap:12px;">
                                                                <div class="dn-kv"><span>{{ number_format($tg['target']) }}</span><small>{{ __('Target') }}</small></div>
                                                                <div class="dn-kv green"><span>{{ number_format($tg['achieved']) }}</span><small>{{ __('Achieved') }}</small></div>
                                                                <div class="dn-pct-pill" style="background:{{ $tbg }};color:{{ $tfg }};border:1px solid {{ $tfg }}40;">{{ $tr }}%</div>
                                                            </div>
                                                        </div>
                                                        @forelse($memberGroups as $uid => $mg)
                                                            @php $r=$pct($mg['achieved'],$mg['target']); [$fg,$bg]=$clr($r); @endphp
                                                            <div class="dn-card dn-leaf">
                                                                <div class="dn-avatar-wrap" style="background:{{ $bg }};">
                                                                    <span class="dn-initials" style="color:{{ $fg }};">{{ strtoupper(substr($mg['name'],0,2)) }}</span>
                                                                </div>
                                                                <div class="dn-body">
                                                                    <span class="dn-name">{{ $mg['name'] }}</span>
                                                                    <span class="dn-sub">{{ $mg['count'] }} {{ __('target(s)') }}</span>
                                                                    <div class="dn-bar-track"><div class="dn-bar-fill" style="width:{{ $r }}%;background:{{ $fg }};"></div></div>
                                                                </div>
                                                                <div class="dn-meta">
                                                                    <div class="dn-kv"><span>{{ number_format($mg['target']) }}</span><small>{{ __('Target') }}</small></div>
                                                                    <div class="dn-sep-v"></div>
                                                                    <div class="dn-kv green"><span>{{ number_format($mg['achieved']) }}</span><small>{{ __('Achieved') }}</small></div>
                                                                    <div class="dn-pct-pill" style="background:{{ $bg }};color:{{ $fg }};">{{ $r }}%</div>
                                                                </div>
                                                            </div>
                                                        @empty
                                                            <div class="dn-empty-msg"><i class="ti ti-user-off"></i> {{ __('No members found') }}</div>
                                                        @endforelse
                                                    </div>
                                                @endforeach

                                                </div>{{-- /dn-container --}}
                                                @else
                                                    <div class="dn-empty-msg"><i class="ti ti-inbox"></i> {{ __('No targets for this period') }}</div>
                                                @endif
                                                </div>
                                            </div>

                                        @endif
                                    </div>
                                @endforeach

                                @if(!$hasAnyTargets)
                                    <div class="text-center py-5">
                                        <div style="width: 60px; height: 60px; background: rgba(100,116,139,0.07); border-radius: 16px; display:flex; align-items:center; justify-content:center; margin: 0 auto 12px;">
                                            <i class="ti ti-calendar-off" style="font-size: 1.8rem; color: #94a3b8;"></i>
                                        </div>
                                        <p class="text-muted mb-0" style="font-size: 0.85rem;">{{ __('No targets found for the current year.') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Team Comparison leaderboard -->
                    <div class="col-xl-4 col-lg-12 col-md-12 mb-4">
                        <div class="card h-100 shadow-sm border-0" style="border-radius: 16px;">
                            <div class="card-header border-0 bg-transparent py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 font-weight-bold text-dark">{{ __('Team Performance') }}</h5>
                                <span class="badge bg-light-primary text-primary">{{ __('Leaderboard') }}</span>
                            </div>
                            <div class="card-body p-0">
                                @if(isset($teamPerformance) && count($teamPerformance) > 0)
                                    <div class="list-group list-group-flush leaderboard-list px-3 pb-3">
                                        @foreach($teamPerformance as $index => $tItem)
                                            <div class="list-group-item px-3 py-3 border-0 rounded-4 mb-2 leaderboard-card rank-{{ $index }} shadow-sm bg-light" 
                                                 data-ajax-popup="true" 
                                                 data-size="lg" 
                                                 data-title="{{ $tItem['name'] }} — {{ __('Members Performance') }}" 
                                                 data-url="{{ route('targets.team.members.performance', $tItem['id']) }}" 
                                                 style="cursor: pointer;">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge rounded-circle p-2 me-2 {{ $index == 0 ? 'bg-warning text-white' : ($index == 1 ? 'bg-secondary text-white' : ($index == 2 ? 'bg-dark text-white' : 'bg-light text-dark')) }}" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">
                                                            {{ $index + 1 }}
                                                        </span>
                                                        <span class="font-weight-bold text-dark">{{ $tItem['name'] }}</span>
                                                    </div>
                                                    <span class="text-primary font-weight-bold">{{ $tItem['progress'] }}%</span>
                                                </div>
                                                <div class="progress" style="height: 8px; border-radius: 4px;">
                                                    <div class="progress-bar {{ $tItem['progress'] >= 80 ? 'bg-success' : ($tItem['progress'] >= 45 ? 'bg-primary' : 'bg-danger') }}" role="progressbar" style="width: {{ $tItem['progress'] }}%;" aria-valuenow="{{ $tItem['progress'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <div class="d-flex justify-content-between mt-1">
                                                    <small class="text-muted">{{ __('Quota') }}: <strong>{{ $tItem['target'] }}</strong></small>
                                                    <small class="text-muted">{{ __('Done') }}: <strong>{{ $tItem['achieved'] }}</strong></small>
                                                    <small class="text-muted">{{ __('Remaining') }}: <strong class="{{ ($tItem['target'] - $tItem['achieved']) > 0 ? 'text-warning' : 'text-success' }}">{{ max(0, $tItem['target'] - $tItem['achieved']) }}</strong></small>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="ti ti-users fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">{{ __('No active team targets found.') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Unit Performance breakdown -->
                    @if(count($unitPerformance) > 0)
                        <div class="col-12 mt-2">
                            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                                <div class="card-header border-0 bg-transparent py-3">
                                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('Unit Drill-Down (Departments & Teams)') }}</h5>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="row">
                                        @foreach($unitPerformance as $unit)
                                        <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                                            <div class="card border shadow-none mb-0 rounded-4" style="background-color: #fafbfc;">
                                                <div class="card-body p-3">
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <h6 class="mb-0 text-truncate" style="max-width: 140px;">{{ $unit['name'] }}</h6>
                                                        <span class="badge bg-light-secondary text-dark text-xs">{{ ucfirst($unit['type']) }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                                        <span class="text-muted text-xs">{{ __('Progress') }}</span>
                                                        <span class="font-weight-bold text-primary text-sm">{{ $unit['progress'] }}%</span>
                                                    </div>
                                                    <div class="progress my-2" style="height: 6px; border-radius: 3px;">
                                                        <div class="progress-bar {{ $unit['progress'] >= 80 ? 'bg-success' : 'bg-primary' }}" role="progressbar" style="width: {{ $unit['progress'] }}%;"></div>
                                                    </div>
                                                    <div class="d-flex justify-content-between text-xs">
                                                        <span>{{ __('Quota') }}: <strong>{{ $unit['target'] }}</strong></span>
                                                        <span>{{ __('Done') }}: <strong>{{ $unit['achieved'] }}</strong></span>
                                                        <span>{{ __('Remaining') }}: <strong class="{{ ($unit['target'] - $unit['achieved']) > 0 ? 'text-warning' : 'text-success' }}">{{ max(0, $unit['target'] - $unit['achieved']) }}</strong></span>
                                                    </div>
                                                    <div class="mt-3 d-flex gap-2">
                                                        @if($unit['type'] == 'member')
                                                            <a href="{{ route('targets.index', ['assigned_to' => [$unit['id']]]) }}" class="btn btn-xs btn-light-primary w-100 py-2 rounded-3 text-center d-flex align-items-center justify-content-center">
                                                                <i class="ti ti-filter me-1"></i> {{ __('Filter') }}
                                                            </a>
                                                        @else
                                                            @if($unit['type'] == 'team')
                                                                <button class="btn btn-xs btn-primary w-100 py-2 rounded-3" 
                                                                        data-ajax-popup="true" 
                                                                        data-size="lg" 
                                                                        data-title="{{ $unit['name'] }} — {{ __('Members Performance') }}" 
                                                                        data-url="{{ route('targets.team.members.performance', $unit['id']) }}">
                                                                    <i class="ti ti-eye me-1"></i> {{ __('Inspect') }}
                                                                </button>
                                                            @else
                                                                <button class="btn btn-xs btn-primary w-100 py-2 rounded-3" 
                                                                        data-ajax-popup="true" 
                                                                        data-size="lg" 
                                                                        data-title="{{ $unit['name'] }} — {{ __('Teams Performance') }}" 
                                                                        data-url="{{ route('targets.department.teams.performance', $unit['id']) }}">
                                                                    <i class="ti ti-eye me-1"></i> {{ __('Inspect') }}
                                                                </button>
                                                            @endif
                                                            <a href="{{ route('targets.index', [$unit['type'].'_id' => [$unit['id']]]) }}" class="btn btn-xs btn-light-primary w-100 py-2 rounded-3 text-center d-flex align-items-center justify-content-center">
                                                                <i class="ti ti-filter me-1"></i> {{ __('Filter') }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- AJAX Team Members performance drill-down -->
                    <div class="col-12 mt-4" id="team-member-drilldown-card" style="display: none;">
                        <div class="card border-0 shadow-sm animate__animated animate__fadeIn" style="border-radius: 16px; border-left: 5px solid var(--primary-theme-color) !important; background-color: #fcfdfe;">
                            <div class="card-body" id="team-member-drilldown-body">
                                <!-- Loaded dynamically via AJAX -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: TARGETS HUB (GRID & LIST) -->
            <div class="tab-pane fade" id="tree" role="tabpanel" aria-labelledby="tree-tab">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('Targets Inventory') }}</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <button class="btn btn-sm btn-danger d-none" id="btn-bulk-delete" onclick="bulkDeleteTargets()">
                            <i class="ti ti-trash me-1"></i> {{ __('Delete Selected') }} (<span id="bulk-delete-count">0</span>)
                        </button>
                        <button class="btn btn-sm btn-icon btn-light-primary border" id="btn-grid-layout" onclick="toggleTargetsLayout('grid')" data-bs-toggle="tooltip" title="{{ __('Grid View') }}">
                            <i class="ti ti-layout-grid"></i>
                        </button>
                        <button class="btn btn-sm btn-icon btn-light border" id="btn-list-layout" onclick="toggleTargetsLayout('list')" data-bs-toggle="tooltip" title="{{ __('Hierarchical List') }}">
                            <i class="ti ti-list"></i>
                        </button>
                    </div>
                </div>

                {{-- ── HIERARCHY BANNER ────────────────────────────────────────────────── --}}
                @if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
                    {{-- Company/Admin view --}}
                    <div class="alert border-0 mb-4" style="background: linear-gradient(135deg, rgba(var(--primary-theme-color-rgb),0.07) 0%, rgba(var(--primary-theme-color-rgb),0.03) 100%); border-left: 4px solid var(--primary-theme-color) !important; border-radius: 12px;">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="d-flex align-items-center gap-2">
                                <span style="width:36px; height:36px; border-radius:10px; background:rgba(var(--primary-theme-color-rgb),0.12); display:flex; align-items:center; justify-content:center;">
                                    <i class="ti ti-building text-primary fs-5"></i>
                                </span>
                                <div>
                                    <span class="text-dark fw-bold d-block" style="font-size:0.88rem;">{{ __('Company / Admin View') }}</span>
                                    <span class="text-muted" style="font-size:0.78rem;">{{ __('Assign targets to Departments → Dept Heads divide into Teams → Team Leads assign to Members') }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 ms-auto flex-wrap text-muted" style="font-size:0.78rem;">
                                <span class="badge py-1.5 px-2 rounded-pill" style="background:rgba(var(--primary-theme-color-rgb),0.08); color:var(--primary-theme-color);"><i class="ti ti-building me-1"></i>{{ __('Company') }}</span>
                                <i class="ti ti-arrow-right" style="font-size:11px;"></i>
                                <span class="badge py-1.5 px-2 rounded-pill" style="background:rgba(13,202,240,0.08); color:#0dcaf0;"><i class="ti ti-building me-1"></i>{{ __('Dept') }}</span>
                                <i class="ti ti-arrow-right" style="font-size:11px;"></i>
                                <span class="badge py-1.5 px-2 rounded-pill" style="background:rgba(255,159,67,0.08); color:#ff9f43;"><i class="ti ti-users me-1"></i>{{ __('Team') }}</span>
                                <i class="ti ti-arrow-right" style="font-size:11px;"></i>
                                <span class="badge py-1.5 px-2 rounded-pill" style="background:rgba(45,206,137,0.08); color:#2dce89;"><i class="ti ti-user me-1"></i>{{ __('Member') }}</span>
                            </div>
                        </div>
                    </div>

                @elseif($isDeptHead && $myDept)
                    {{-- Department Head view --}}
                    <div class="alert border-0 mb-4" style="background: linear-gradient(135deg, rgba(13,202,240,0.07) 0%, rgba(13,202,240,0.03) 100%); border-left: 4px solid #0dcaf0 !important; border-radius: 12px;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <span style="width:40px; height:40px; border-radius:12px; background:rgba(13,202,240,0.12); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    <i class="ti ti-building text-info fs-4"></i>
                                </span>
                                <div>
                                    <span class="text-dark fw-bold d-block" style="font-size:0.92rem;">{{ __('Your Role') }}: {{ __('Department Head') }} — <span class="text-info">{{ $myDept->name }}</span></span>
                                    @if($myDeptTarget)
                                        <span class="text-muted" style="font-size:0.8rem;">
                                            {{ __('Your dept target') }}: <strong class="text-dark">{{ $myDeptTarget->target_name }}</strong>
                                            &nbsp;|&nbsp; {{ __('Quota') }}: <strong>{{ $myDeptTarget->target_value }}</strong>
                                            &nbsp;|&nbsp; {{ __('Done') }}: <strong class="text-success">{{ $myDeptTarget->achieved_value }}</strong>
                                            &nbsp;|&nbsp; {{ __('Remaining') }}: <strong class="text-warning">{{ max(0, $myDeptTarget->target_value - $myDeptTarget->achieved_value) }}</strong>
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size:0.8rem;">{{ __('No department-level target assigned yet. Ask your admin.') }}</span>
                                    @endif
                                </div>
                            </div>
                            @if($myDeptTarget)
                                <a href="#" class="btn btn-sm btn-info text-white shadow-sm"
                                   data-url="{{ route('targets.create', ['parent_id' => $myDeptTarget->id]) }}"
                                   data-ajax-popup="true" data-size="md"
                                   data-title="{{ __('Divide into Teams') }}"
                                   style="border-radius:10px; white-space:nowrap;">
                                    <i class="ti ti-git-fork me-1"></i> {{ __('Divide into Teams') }}
                                </a>
                            @endif
                        </div>
                    </div>

                @elseif($isTeamLead && $myTeam)
                    {{-- Team Lead view --}}
                    <div class="alert border-0 mb-4" style="background: linear-gradient(135deg, rgba(255,159,67,0.07) 0%, rgba(255,159,67,0.03) 100%); border-left: 4px solid #ff9f43 !important; border-radius: 12px;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <span style="width:40px; height:40px; border-radius:12px; background:rgba(255,159,67,0.12); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    <i class="ti ti-users text-warning fs-4"></i>
                                </span>
                                <div>
                                    <span class="text-dark fw-bold d-block" style="font-size:0.92rem;">{{ __('Your Role') }}: {{ __('Team Lead') }} — <span class="text-warning">{{ $myTeam->name }}</span></span>
                                    @if($myTeamTarget)
                                        <span class="text-muted" style="font-size:0.8rem;">
                                            {{ __('Your team target') }}: <strong class="text-might">{{ $myTeamTarget->target_name }}</strong>
                                            &nbsp;|&nbsp; {{ __('Quota') }}: <strong>{{ $myTeamTarget->target_value }}</strong>
                                            &nbsp;|&nbsp; {{ __('Done') }}: <strong class="text-success">{{ $myTeamTarget->achieved_value }}</strong>
                                            &nbsp;|&nbsp; {{ __('Remaining') }}: <strong class="text-warning">{{ max(0, $myTeamTarget->target_value - $myTeamTarget->achieved_value) }}</strong>
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size:0.8rem;">{{ __('No team-level target assigned yet. Ask your department head.') }}</span>
                                    @endif
                                </div>
                            </div>
                            @if($myTeamTarget)
                                <a href="#" class="btn btn-sm btn-warning text-white shadow-sm"
                                   data-url="{{ route('targets.create', ['parent_id' => $myTeamTarget->id]) }}"
                                   data-ajax-popup="true" data-size="md"
                                   data-title="{{ __('Assign to Members') }}"
                                   style="border-radius:10px; white-space:nowrap;">
                                    <i class="ti ti-user-plus me-1"></i> {{ __('Assign to Members') }}
                                </a>
                            @endif
                        </div>
                    </div>

                @elseif($isManager)
                    {{-- Generic manager --}}
                    <div class="alert border-0 mb-4" style="background: linear-gradient(135deg, rgba(45,206,137,0.06) 0%, rgba(45,206,137,0.02) 100%); border-left: 4px solid #2dce89 !important; border-radius: 12px;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ti ti-shield-check text-success fs-5"></i>
                            <span class="text-dark fw-bold" style="font-size:0.88rem;">{{ __('You can manage targets for your subordinates. Use the') }} <i class="ti ti-git-fork text-info"></i> {{ __('button to divide targets.') }}</span>
                        </div>
                    </div>
                @endif
                {{-- ─────────────────────────────────────────────────────────────────────── --}}

                <!-- Instant Search Filter -->
                <div class="row mb-4 g-2">
                    <div class="col-md-5 col-sm-12">
                        <div class="input-group input-group-merge border rounded-3 overflow-hidden shadow-sm" style="background: #ffffff; border-color: #e2e8f0 !important;">
                            <span class="input-group-text bg-transparent border-0 px-3"><i class="ti ti-search text-muted fs-5"></i></span>
                            <input type="text" id="target-search-input" class="form-control bg-transparent border-0 ps-0 py-2.5 text-sm" placeholder="{{ __('Search targets by name, department, team or assignee...') }}">
                        </div>
                    </div>
                </div>

                <!-- GRID CARD VIEW -->
                <div id="targets-grid-layout" class="row g-4 mb-4" style="display: none;">
                    @forelse($targets as $target)
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12">
                            <div class="card shadow-sm target-card h-100 {{ $target->status == 'Completed' ? 'status-completed' : ($target->status == 'Missed' ? 'status-missed' : 'status-pending') }}">
                                <div class="card-body p-3 d-flex flex-column h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <span class="badge {{ $target->status == 'Completed' ? 'badge-status-completed' : ($target->status == 'Missed' ? 'badge-status-missed' : 'badge-status-pending') }} text-xxs px-2.5 py-1 rounded-pill">
                                                {{ __($target->status) }}
                                            </span>
                                            <span class="badge {{ $target->target_type == 'lead_stage' ? 'badge-type-automated' : 'badge-type-manual' }} text-xxs px-2.5 py-1 rounded-pill ms-1">
                                                {{ $target->target_type == 'lead_stage' ? __('Automated') : __('Manual') }}
                                            </span>
                                        </div>
                                        
                                        <!-- Actions Dropdown -->
                                        <div class="dropdown">
                                            <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="ti ti-dots-vertical fs-5"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                @if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin' || $target->assigned_by == Auth::user()->id)
                                                    <a href="#" class="dropdown-item" data-url="{{ route('targets.edit', $target->id) }}" data-ajax-popup="true" data-size="md" data-title="{{ __('Edit Target') }}">
                                                        <i class="ti ti-pencil me-1"></i> {{__('Edit')}}
                                                    </a>
                                                @endif
                                                @php
                                                    $canAssignSubGrid = false;
                                                    $subGridTitle = __('Assign Sub-Target');
                                                    
                                                    $isTargetTeamManager = false;
                                                    if ($target->team_id > 0 && module_is_active('Hrm')) {
                                                        $teamObj = \Workdo\Hrm\Entities\Department::find($target->team_id);
                                                        if ($teamObj && $teamObj->manager_id) {
                                                            $mgrEmp = \Workdo\Hrm\Entities\Employee::find($teamObj->manager_id);
                                                            if ($mgrEmp && $mgrEmp->user_id == Auth::user()->id) {
                                                                $isTargetTeamManager = true;
                                                            }
                                                        }
                                                    }

                                                    $isTargetDeptManager = false;
                                                    if ($target->department_id > 0 && module_is_active('Hrm')) {
                                                        $deptObj = \Workdo\Hrm\Entities\Department::find($target->department_id);
                                                        if ($deptObj && $deptObj->manager_id) {
                                                            $mgrEmp = \Workdo\Hrm\Entities\Employee::find($deptObj->manager_id);
                                                            if ($mgrEmp && $mgrEmp->user_id == Auth::user()->id) {
                                                                $isTargetDeptManager = true;
                                                            }
                                                        }
                                                    }

                                                    if ($target->department_id > 0 || $target->team_id > 0) {
                                                        if (Auth::user()->type == 'company' || Auth::user()->type == 'super admin') {
                                                            $canAssignSubGrid = true;
                                                        } elseif ($isManager && ($target->assigned_by == Auth::user()->id || $target->responsible_user_id == Auth::user()->id || $isTargetTeamManager || $isTargetDeptManager)) {
                                                            $canAssignSubGrid = true;
                                                        }
                                                    }
                                                    if ($target->department_id > 0) $subGridTitle = __('Divide into Teams');
                                                    if ($target->team_id > 0)       $subGridTitle = __('Assign to Members');
                                                @endphp
                                                @if($canAssignSubGrid)
                                                    <a href="#" class="dropdown-item text-info" data-url="{{ route('targets.create', ['parent_id' => $target->id]) }}" data-ajax-popup="true" data-size="md" data-title="{{ $subGridTitle }}">
                                                        <i class="ti ti-git-fork me-1"></i> {{ $subGridTitle }}
                                                    </a>
                                                @endif

                                                @if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin' || $target->assigned_by == Auth::user()->id)
                                                    <div class="dropdown-divider"></div>
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['targets.destroy', $target->id], 'id' => 'delete-form-card-'.$target->id, 'class' => 'd-inline']) !!}
                                                         <a href="#" class="dropdown-item text-danger bs-pass-para show_confirm" data-confirm="{{__('Are You Sure?')}}" data-text="{{__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="delete-form-card-{{$target->id}}">
                                                             <i class="ti ti-trash me-1"></i> {{__('Delete')}}
                                                         </a>
                                                     {!! Form::close() !!}
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="target-card-title mb-2">
                                        @if($target->parent_id)
                                            <small class="text-muted"><i class="ti ti-corner-down-right me-1"></i></small>
                                        @endif
                                        {{ $target->target_name }}
                                    </h5>

                                    @if($target->target_type == 'lead_stage')
                                         <div class="pipeline-panel text-xs">
                                             <div class="d-flex align-items-center flex-wrap gap-1 text-dark font-weight-bold mb-1">
                                                 <i class="ti ti-git-branch text-primary me-1"></i>
                                                 <span>{{ $target->pipeline ? $target->pipeline->name : __('Unknown Pipeline') }}</span>
                                                 <i class="ti ti-arrow-right text-muted mx-1" style="font-size: 10px;"></i>
                                                 <span class="badge bg-light-info text-info rounded-pill py-1 px-2">{{ $target->stage ? $target->stage->name : __('Unknown Stage') }}</span>
                                             </div>
                                             @if($target->custom_date_field && $target->custom_date_field !== 'created_at')
                                                 @php
                                                     $dateField = \DB::table('lead_custom_fields')->where('workspace_id', getActiveWorkSpace())->where('id', $target->custom_date_field)->first();
                                                 @endphp
                                                 <div class="text-xxs text-muted mt-1 d-flex align-items-center gap-1">
                                                     <i class="ti ti-calendar-event text-secondary"></i>
                                                     <span>{{ __('Date Field') }}: <strong class="text-dark font-weight-bold">{{ $dateField ? $dateField->name : __('Custom Date') }}</strong></span>
                                                 </div>
                                             @endif
                                         </div>
                                    @endif

                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                         <span class="metadata-pill pill-assignee">
                                             <i class="ti ti-user-check"></i>
                                             @if($target->assignedToUser)
                                                 {{ $target->assignedToUser->name }}
                                             @elseif($target->department)
                                                 <a href="#" data-ajax-popup="true" data-size="lg" data-title="{{ $target->department->name }} — {{ __('Teams Performance') }}" data-url="{{ route('targets.department.teams.performance', $target->department->id) }}" class="text-info fw-bold text-decoration-none">
                                                     {{ $target->department->name }}
                                                 </a>
                                             @elseif($target->team)
                                                 <a href="#" data-ajax-popup="true" data-size="lg" data-title="{{ $target->team->name }} — {{ __('Members Performance') }}" data-url="{{ route('targets.team.members.performance', $target->team->id) }}" class="text-info fw-bold text-decoration-none">
                                                     {{ $target->team->name }}
                                                 </a>
                                             @else
                                                 -
                                             @endif
                                         </span>
                                         @if($target->responsibleUser)
                                             <span class="metadata-pill pill-manager" title="{{ __('Responsible Manager') }}">
                                                 <i class="ti ti-shield"></i>
                                                 {{ $target->responsibleUser->name }}
                                             </span>
                                         @endif
                                         <span class="metadata-pill pill-timeline">
                                             <i class="ti ti-calendar"></i>
                                             {{ $target->start_date ? \Carbon\Carbon::parse($target->start_date)->format('d M') : '-' }} - {{ $target->end_date ? \Carbon\Carbon::parse($target->end_date)->format('d M Y') : '-' }}
                                         </span>
                                    </div>

                                    <div class="row g-2 text-center mb-3 mt-auto">
                                        <div class="col-4">
                                            <div class="metric-box border-accent-primary">
                                                <small class="text-muted d-block text-xxs text-uppercase">{{ __('Quota') }}</small>
                                                <span class="fw-bold text-dark text-sm">{{ $target->target_value }}</span>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="metric-box border-accent-success">
                                                <small class="text-muted d-block text-xxs text-uppercase">{{ __('Done') }}</small>
                                                <span class="fw-bold text-success text-sm">{{ $target->achieved_value }}</span>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            @php
                                                $rem = max(0, $target->target_value - $target->achieved_value);
                                            @endphp
                                            <div class="metric-box border-accent-warning">
                                                <small class="text-muted d-block text-xxs text-uppercase">{{ __('Left') }}</small>
                                                <span class="fw-bold text-warning text-sm">{{ $rem }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        @php
                                            $progps = round($target->aggregateProgress, 1);
                                            $progps = $progps > 100 ? 100 : $progps;
                                        @endphp
                                        <div class="d-flex justify-content-between align-items-center text-xs mb-1.5">
                                            <span class="text-muted font-weight-bold">{{ __('Progress') }}</span>
                                            <span class="font-weight-bold text-primary">{{ $progps }}%</span>
                                        </div>
                                        <div class="target-progress-track">
                                            <div class="target-progress-bar" style="width: {{ $progps }}%;"></div>
                                        </div>
                                    </div>

                                    {{-- Prominent DIVIDE button for team targets --}}
                                    @php
                                        $showDivideBtn = false;
                                        $divideBtnLabel = '';
                                        $divideBtnColor = '';
                                        
                                        $isTargetTeamManager = false;
                                        if ($target->team_id > 0 && module_is_active('Hrm')) {
                                            $teamObj = \Workdo\Hrm\Entities\Department::find($target->team_id);
                                            if ($teamObj && $teamObj->manager_id) {
                                                $mgrEmp = \Workdo\Hrm\Entities\Employee::find($teamObj->manager_id);
                                                if ($mgrEmp && $mgrEmp->user_id == Auth::user()->id) {
                                                    $isTargetTeamManager = true;
                                                }
                                            }
                                        }

                                        $isTargetDeptManager = false;
                                        if ($target->department_id > 0 && module_is_active('Hrm')) {
                                            $deptObj = \Workdo\Hrm\Entities\Department::find($target->department_id);
                                            if ($deptObj && $deptObj->manager_id) {
                                                $mgrEmp = \Workdo\Hrm\Entities\Employee::find($deptObj->manager_id);
                                                if ($mgrEmp && $mgrEmp->user_id == Auth::user()->id) {
                                                    $isTargetDeptManager = true;
                                                }
                                            }
                                        }

                                        if ($target->team_id > 0) {
                                            // Show to: responsible person, assigned_by, team lead, or company admin
                                            if (
                                                Auth::user()->type == 'company' || Auth::user()->type == 'super admin' ||
                                                $target->responsible_user_id == Auth::user()->id ||
                                                $target->assigned_by == Auth::user()->id ||
                                                $isTargetTeamManager
                                            ) {
                                                $showDivideBtn  = true;
                                                $divideBtnLabel = __('Assign to Members');
                                                $divideBtnColor = 'btn-warning';
                                            }
                                        } elseif ($target->department_id > 0) {
                                            // Show to: responsible person, assigned_by, dept head, or company admin
                                            if (
                                                Auth::user()->type == 'company' || Auth::user()->type == 'super admin' ||
                                                $target->responsible_user_id == Auth::user()->id ||
                                                $target->assigned_by == Auth::user()->id ||
                                                $isTargetDeptManager
                                            ) {
                                                $showDivideBtn  = true;
                                                $divideBtnLabel = __('Divide into Teams');
                                                $divideBtnColor = 'btn-info';
                                            }
                                        }
                                    @endphp
                                    @if($showDivideBtn)
                                        <div class="mt-3">
                                            <a href="#"
                                               class="btn {{ $divideBtnColor }} text-white w-100 fw-bold shadow-sm"
                                               data-url="{{ route('targets.create', ['parent_id' => $target->id]) }}"
                                               data-ajax-popup="true"
                                               data-size="md"
                                               data-title="{{ $divideBtnLabel }}"
                                               style="border-radius: 10px; font-size: 0.82rem; letter-spacing: 0.3px;">
                                                <i class="ti ti-git-fork me-1"></i> {{ $divideBtnLabel }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5 text-muted">
                            <i class="ti ti-target fs-1"></i>
                            <p class="mt-2 text-sm">{{ __('No targets found.') }}</p>
                        </div>
                    @endforelse
                </div>

                <!-- TREE LIST LAYOUT (TABLE VIEW) -->
                <div id="targets-list-layout" class="mb-4" style="display: none;">
                    <div class="card shadow-sm border border-200 rounded-4 overflow-hidden">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-items-center tree-table mb-0">
                                    <thead class="bg-light">
                                        <tr class="text-dark uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px; color: #344054 !important;">
                                            <th style="width: 45px; padding-left: 24px; vertical-align: middle;">
                                                <div class="form-check m-0">
                                                    <input type="checkbox" class="form-check-input" id="select-all-targets">
                                                </div>
                                            </th>
                                            <th style="min-width: 280px; padding-left: 12px;">{{ __('Objective') }}</th>
                                            <th style="min-width: 100px;">{{ __('Type') }}</th>
                                            <th style="min-width: 150px;">{{ __('Assigned To') }}</th>
                                            <th style="min-width: 180px;">{{ __('Responsible / Manager') }}</th>
                                            <th style="text-align: center; min-width: 90px;">{{ __('Target') }}</th>
                                            <th style="text-align: center; min-width: 80px;">{{ __('Achieved') }}</th>
                                            <th style="text-align: center; min-width: 90px;">{{ __('Remaining') }}</th>
                                            <th style="text-align: center; min-width: 90px;">{{ __('Incentive') }}</th>
                                            <th style="min-width: 180px;">{{ __('Progress') }}</th>
                                            <th style="min-width: 100px;">{{ __('Status') }}</th>
                                            <th class="text-end" style="min-width: 120px; padding-right: 24px;">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $topLevel = $targets->filter(function($t) use ($targets) {
                                                return empty($t->parent_id) || !$targets->contains('id', $t->parent_id);
                                            });
                                            $groupedTargets = $topLevel->groupBy(function($target) {
                                                if ($target->department_id > 0) {
                                                    return 'dept_' . $target->department_id;
                                                } elseif ($target->team_id > 0) {
                                                    return 'team_' . $target->team_id;
                                                } elseif ($target->assigned_to > 0) {
                                                    return 'user_' . $target->assigned_to;
                                                }
                                                return 'other';
                                            });
                                        @endphp
                                        @forelse($groupedTargets as $groupKey => $groupTargets)
                                            @php
                                                $firstTarget = $groupTargets->first();
                                                $groupName = '';
                                                $groupIcon = '';
                                                $groupBg = '';
                                                if ($groupKey === 'other') {
                                                    $groupName = __('Company / Unassigned');
                                                    $groupIcon = 'ti ti-target text-muted';
                                                    $groupBg = 'bg-light';
                                                } elseif (substr($groupKey, 0, 5) === 'dept_') {
                                                    $groupName = ($firstTarget->department->name ?? __('Unknown Department')) . ' (' . __('Dept') . ')';
                                                    $groupIcon = 'ti ti-building text-info';
                                                    $groupBg = 'bg-light-info';
                                                } elseif (substr($groupKey, 0, 5) === 'team_') {
                                                    $groupName = ($firstTarget->team->name ?? __('Unknown Team')) . ' (' . __('Team') . ')';
                                                    $groupIcon = 'ti ti-users text-warning';
                                                    $groupBg = 'bg-light-warning';
                                                } else {
                                                    $groupName = $firstTarget->assignedToUser->name ?? __('Unknown User');
                                                    $groupIcon = 'ti ti-user text-primary';
                                                    $groupBg = 'bg-light-primary';
                                                }
                                            @endphp
                                            
                                            <!-- Group Header Row -->
                                            <tr class="target-group-header-row" style="background-color: #fafbfc; border-top: 1.5px solid #edf2f7; border-bottom: 1.5px solid #edf2f7;">
                                                <td colspan="11" style="padding: 12px 24px;">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge {{ $groupBg }} text-dark rounded-circle p-1.5 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                            <i class="{{ $groupIcon }}" style="font-size: 14px;"></i>
                                                        </span>
                                                        <span class="text-dark font-weight-bold" style="font-size: 0.95rem;">{{ $groupName }}</span>
                                                        <span class="badge bg-light-secondary text-muted rounded-pill px-2.5 py-1 text-xxs font-weight-bold ms-2">{{ $groupTargets->count() }} {{ $groupTargets->count() > 1 ? __('Targets') : __('Target') }}</span>
                                                    </div>
                                                </td>
                                            </tr>

                                            @foreach($groupTargets as $target)
                                                @include('targets.tree_row', ['target' => $target, 'level' => 1, 'parentId' => $groupKey])
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center py-5 text-muted">
                                                    <i class="ti ti-sitemap fs-1"></i>
                                                    <p class="mt-2 text-sm">{{ __('No targets found.') }}</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 5: COMPARISON & RANKING HUB -->
            <div class="tab-pane fade" id="comparison" role="tabpanel" aria-labelledby="comparison-tab">
                <!-- Inner Pills navigation -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 font-weight-bold text-dark"><i class="ti ti-arrows-left-right text-primary me-2"></i>{{ __('Comparison & Ranking Hub') }}</h5>
                    <ul class="nav nav-pills bg-light p-1 rounded-3" id="comparisonSubTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-2 px-3 text-xs fw-bold" id="comp-dept-tab" data-bs-toggle="pill" data-bs-target="#comp-departments" type="button" role="tab" aria-controls="comp-departments" aria-selected="true">
                                <i class="ti ti-building me-1"></i> {{ __('Departments') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-2 px-3 text-xs fw-bold" id="comp-team-tab" data-bs-toggle="pill" data-bs-target="#comp-teams" type="button" role="tab" aria-controls="comp-teams" aria-selected="false">
                                <i class="ti ti-users me-1"></i> {{ __('Teams') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-2 px-3 text-xs fw-bold" id="comp-emp-tab" data-bs-toggle="pill" data-bs-target="#comp-employees" type="button" role="tab" aria-controls="comp-employees" aria-selected="false">
                                <i class="ti ti-user me-1"></i> {{ __('Employees') }}
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="comparisonSubTabsContent">
                    <!-- SUB TAB 1: COMPARE DEPARTMENTS -->
                    <div class="tab-pane fade show active" id="comp-departments" role="tabpanel" aria-labelledby="comp-dept-tab">
                        <div class="row g-4">
                            @php
                                $deptsOnly = array_filter($unitPerformance, function($item) { return $item['type'] == 'department'; });
                            @endphp
                            @forelse($deptsOnly as $dept)
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card border shadow-sm rounded-4 h-100" style="transition: all 0.2s ease; background: #fff; border-radius: 16px;">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="theme-avtar bg-light-primary text-primary me-3" style="width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                                        <i class="ti ti-building"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-dark font-weight-bold mb-1">{{ $dept['name'] }}</h6>
                                                        <span class="rank-badge">#{{ $loop->iteration }}</span>
                                                    </div>
                                                </div>
                                                <span class="badge bg-light-success text-success fw-bold">{{ $dept['progress'] }}%</span>
                                            </div>

                                            <div class="progress mb-3" style="height: 8px; border-radius: 4px;">
                                                <div class="progress-bar {{ $dept['progress'] >= 80 ? 'bg-success' : ($dept['progress'] >= 45 ? 'bg-primary' : 'bg-danger') }}" role="progressbar" style="width: {{ $dept['progress'] }}%;"></div>
                                            </div>

                                            <div class="d-flex justify-content-between text-xs text-muted mb-3 border-bottom pb-2">
                                                <span>{{ __('Quota') }}: <strong>{{ $dept['target'] }}</strong></span>
                                                <span>{{ __('Done') }}: <strong>{{ $dept['achieved'] }}</strong></span>
                                                <span>{{ __('Remaining') }}: <strong class="{{ ($dept['target'] - $dept['achieved']) > 0 ? 'text-warning' : 'text-success' }}">{{ max(0, $dept['target'] - $dept['achieved']) }}</strong></span>
                                            </div>

                                            <button class="btn btn-xs comparison-btn w-100 py-2 rounded-3" 
                                                    data-ajax-popup="true" 
                                                    data-size="lg" 
                                                    data-title="{{ $dept['name'] }} — {{ __('Teams Performance') }}" 
                                                    data-url="{{ route('targets.department.teams.performance', $dept['id']) }}">
                                                <i class="ti ti-eye me-1"></i> {{ __('Inspect Sub-Teams') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5 text-muted">
                                    <i class="ti ti-building fs-1"></i>
                                    <p class="mt-2 text-sm">{{ __('No departments found with target data.') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- SUB TAB 2: COMPARE TEAMS -->
                    <div class="tab-pane fade" id="comp-teams" role="tabpanel" aria-labelledby="comp-team-tab">
                        <div class="row g-4">
                            @php
                                $teamsOnly = array_filter($unitPerformance, function($item) { return $item['type'] == 'team'; });
                            @endphp
                            @forelse($teamsOnly as $team)
                                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                    <div class="card border shadow-sm rounded-4 h-100" style="transition: all 0.2s ease; background: #fff; border-radius: 16px;">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="theme-avtar bg-light-warning text-warning me-3" style="width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                                        <i class="ti ti-users"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-dark font-weight-bold mb-1">{{ $team['name'] }}</h6>
                                                        <span class="rank-badge">#{{ $loop->iteration }}</span>
                                                    </div>
                                                </div>
                                                <span class="badge bg-light-success text-success fw-bold">{{ $team['progress'] }}%</span>
                                            </div>

                                            <div class="progress mb-3" style="height: 8px; border-radius: 4px;">
                                                <div class="progress-bar {{ $team['progress'] >= 80 ? 'bg-success' : ($team['progress'] >= 45 ? 'bg-primary' : 'bg-danger') }}" role="progressbar" style="width: {{ $team['progress'] }}%;"></div>
                                            </div>

                                            <div class="d-flex justify-content-between text-xs text-muted mb-3 border-bottom pb-2">
                                                <span>{{ __('Quota') }}: <strong>{{ $team['target'] }}</strong></span>
                                                <span>{{ __('Done') }}: <strong>{{ $team['achieved'] }}</strong></span>
                                                <span>{{ __('Remaining') }}: <strong class="{{ ($team['target'] - $team['achieved']) > 0 ? 'text-warning' : 'text-success' }}">{{ max(0, $team['target'] - $team['achieved']) }}</strong></span>
                                            </div>

                                            <button class="btn btn-xs comparison-btn w-100 py-2 rounded-3" 
                                                    data-ajax-popup="true" 
                                                    data-size="lg" 
                                                    data-title="{{ $team['name'] }} — {{ __('Members Performance') }}" 
                                                    data-url="{{ route('targets.team.members.performance', $team['id']) }}">
                                                <i class="ti ti-eye me-1"></i> {{ __('Inspect Members') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5 text-muted">
                                    <i class="ti ti-users fs-1"></i>
                                    <p class="mt-2 text-sm">{{ __('No teams found with target data.') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- SUB TAB 3: COMPARE EMPLOYEES -->
                    <div class="tab-pane fade" id="comp-employees" role="tabpanel" aria-labelledby="comp-emp-tab">
                        <div class="row g-4">
                            @forelse($employeePerformance as $emp)
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                                    <div class="card border shadow-sm rounded-4 h-100 text-center position-relative" style="transition: all 0.2s ease; background: #fff; border-radius: 16px;">
                                        <span class="rank-badge position-absolute top-0 start-0 m-3">#{{ $loop->iteration }}</span>
                                        <div class="card-body p-3 d-flex flex-column align-items-center">
                                            <div class="position-relative mb-3">
                                                <img src="{{ $emp['avatar'] }}" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($emp['name']) }}&background={{ $themeColorHex }}&color=fff';" class="rounded-circle" style="width: 64px; height: 64px; object-fit: cover; border: 3px solid var(--primary-theme-color);">
                                                <span class="position-absolute bottom-0 end-0 badge rounded-circle p-1 {{ $emp['progress'] >= 80 ? 'bg-success' : ($emp['progress'] >= 45 ? 'bg-primary' : 'bg-danger') }}" style="width: 18px; height: 18px; display: inline-block;"></span>
                                            </div>
                                            
                                            <h6 class="mb-1 text-dark font-weight-bold text-truncate w-100">{{ $emp['name'] }}</h6>
                                            <small class="text-muted d-block mb-2 text-truncate w-100">{{ $emp['email'] }}</small>
                                            <span class="badge bg-light-secondary text-dark text-xxs mb-3">{{ $emp['department'] }}</span>

                                            <div class="w-100 mb-3 mt-auto">
                                                <div class="d-flex justify-content-between align-items-center text-xs mb-1">
                                                    <span class="text-muted">{{ __('Progress') }}</span>
                                                    <span class="font-weight-bold text-primary">{{ $emp['progress'] }}%</span>
                                                </div>
                                                <div class="progress" style="height: 6px; border-radius: 3px;">
                                                    <div class="progress-bar {{ $emp['progress'] >= 80 ? 'bg-success' : ($emp['progress'] >= 45 ? 'bg-primary' : 'bg-danger') }}" role="progressbar" style="width: {{ $emp['progress'] }}%;"></div>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between w-100 text-xxs text-muted border-top pt-2">
                                                <span>{{ __('Quota') }}: <strong>{{ $emp['target'] }}</strong></span>
                                                <span>{{ __('Done') }}: <strong>{{ $emp['achieved'] }}</strong></span>
                                                <span>{{ __('Left') }}: <strong class="{{ $emp['remaining'] > 0 ? 'text-warning' : 'text-success' }}">{{ $emp['remaining'] }}</strong></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5 text-muted">
                                    <i class="ti ti-user fs-1"></i>
                                    <p class="mt-2 text-sm">{{ __('No employees found with target data.') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>



            @if(Auth::user()->type == 'company' || Auth::user()->type == 'super admin')
            <!-- TAB 4: TARGET TEMPLATES -->
            <div class="tab-pane fade" id="templates" role="tabpanel" aria-labelledby="templates-tab">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 font-weight-bold text-dark">{{ __('Saved Target Templates') }}</h5>
                    <a href="#" class="btn btn-sm btn-primary shadow-sm" data-ajax-popup="true" data-size="md"
                        data-title="{{ __('Create Target Template') }}" data-url="{{ route('targets.templates.create') }}"
                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create Template') }}">
                        <i class="ti ti-plus text-white-off"></i> {{ __('Create Template') }}
                    </a>
                </div>

                <div class="row">
                    @forelse($templates as $template)
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                            <div class="card shadow-sm border-0 h-100 rounded-4 overflow-hidden" style="transition: all 0.3s ease;">
                                <div class="card-header border-0 py-3 d-flex justify-content-between align-items-center {{ $template->target_type == 'lead_stage' ? 'bg-light-primary text-primary' : 'bg-light-success text-success' }}" style="border-radius: 16px 16px 0 0;">
                                    <h6 class="mb-0 font-weight-bold text-truncate" style="max-width: 150px;">{{ $template->name }}</h6>
                                    <span class="badge {{ $template->target_type == 'lead_stage' ? 'bg-primary text-white' : 'bg-success text-white' }} text-xxs" style="font-size: 10px;">
                                        {{ $template->target_type == 'lead_stage' ? __('Automated') : __('Manual') }}
                                    </span>
                                </div>
                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                    <div class="mb-3">
                                        @if($template->target_type == 'lead_stage')
                                            <div class="d-flex flex-column gap-1 text-muted text-xs">
                                                <span><strong>{{ __('Pipeline') }}:</strong> {{ $template->pipeline ? $template->pipeline->name : __('N/A') }}</span>
                                                <span><strong>{{ __('Stage') }}:</strong> {{ $template->stage ? $template->stage->name : __('N/A') }}</span>
                                            </div>
                                        @else
                                            <p class="text-muted text-xs mb-0">{{ __('A manual target requires users to self-report their achievements.') }}</p>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between gap-2 pt-2 border-top mt-auto">
                                        <a href="{{ route('targets.templates.assign.view', $template->id) }}" class="btn btn-xs btn-primary py-2 px-3 rounded-3 w-100 text-center">
                                            <i class="ti ti-user-plus me-1"></i> {{ __('Assign') }}
                                        </a>
                                        <div class="d-flex gap-1">
                                            <a href="#" class="btn btn-xs btn-outline-info p-2 rounded-3"
                                                data-ajax-popup="true" data-size="md" data-title="{{ __('Edit Template') }}"
                                                data-url="{{ route('targets.templates.edit', $template->id) }}"
                                                data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                <i class="ti ti-pencil"></i>
                                            </a>
                                            {{ Form::open(['route' => ['targets.templates.destroy', $template->id], 'method' => 'DELETE', 'class' => 'd-inline', 'id' => 'delete-template-form-'.$template->id]) }}
                                                <button type="submit" class="btn btn-xs btn-outline-danger p-2 rounded-3 bs-pass-para"
                                                    data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                    data-confirm="{{ __('Are you sure?') }}" data-text="{{ __('This action cannot be undone. Do you want to continue?') }}">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            {{ Form::close() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                                <div class="text-center text-muted mb-3">
                                    <i class="ti ti-template fs-1"></i>
                                </div>
                                <h6 class="text-muted mt-2">{{ __('No Target Templates saved yet.') }}</h6>
                                <p class="text-muted text-xs">{{ __('Create a target template first, then assign it to your teams in one click.') }}</p>
                                <div class="mt-3">
                                    <a href="#" class="btn btn-sm btn-primary shadow-sm" data-ajax-popup="true" data-size="md"
                                        data-title="{{ __('Create Target Template') }}" data-url="{{ route('targets.templates.create') }}">
                                        <i class="ti ti-plus text-white-off"></i> {{ __('Create First Template') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
            @endif

            <!-- TAB 6: INCENTIVE LEDGER -->
            <div class="tab-pane fade" id="incentives" role="tabpanel" aria-labelledby="incentives-tab">
                <div class="row g-4 mb-4">
                    <!-- Cards showing Total Earned & Total Pending -->
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="card shadow-sm border rounded-4 bg-white p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-light-success text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: rgba(12, 175, 96, 0.1) !important;">
                                    <i class="ti ti-cash fs-3" style="color: #0CAF60 !important;"></i>
                                </div>
                                <div>
                                    <span class="text-muted text-xxs uppercase fw-bold d-block mb-1">{{ __('Total Earned Incentive') }}</span>
                                    <h4 class="fw-bold mb-0 text-success" style="font-size: 1.5rem;">{{ currency_format_with_sym($stats['earned_incentive']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="card shadow-sm border rounded-4 bg-white p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-light-warning text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: rgba(245, 159, 0, 0.1) !important;">
                                    <i class="ti ti-cash-banknote fs-3" style="color: #f59f00 !important;"></i>
                                </div>
                                <div>
                                    <span class="text-muted text-xxs uppercase fw-bold d-block mb-1">{{ __('Total Pending Incentive') }}</span>
                                    <h4 class="fw-bold mb-0 text-warning" style="font-size: 1.5rem;">{{ currency_format_with_sym($stats['pending_incentive']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Incentive Reports lists -->
                <div class="card shadow-sm border rounded-4 bg-white">
                    <div class="card-header border-0 bg-transparent pt-4 pb-0">
                        <h5 class="mb-0 font-weight-bold text-dark"><i class="ti ti-report-money me-1 text-primary"></i>{{ __('Incentive Ledger Report') }}</h5>
                        <p class="text-muted text-xs mb-0">{{ __('Details of incentives earned and pending based on target completion status.') }}</p>
                    </div>
                    <div class="card-body">
                        <!-- Department Ledger -->
                        @if(!empty($departmentLedger))
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark mb-2"><i class="ti ti-building text-info me-1"></i>{{ __('Department Level Incentives') }}</h6>
                                <div class="table-responsive border rounded-3 bg-white">
                                    <table class="table align-items-center mb-0">
                                        <thead class="bg-light">
                                            <tr class="text-xs text-muted uppercase">
                                                <th style="padding: 10px 16px;">{{ __('Department') }}</th>
                                                <th style="padding: 10px 16px;">{{ __('Manager / Department Head') }}</th>
                                                <th style="padding: 10px 16px;" class="text-end">{{ __('Pending Incentive') }}</th>
                                                <th style="padding: 10px 16px;" class="text-end">{{ __('Earned Incentive') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($departmentLedger as $dept)
                                                <tr>
                                                    <td class="fw-semibold text-dark" style="padding: 12px 16px;">{{ $dept['name'] }}</td>
                                                    <td style="padding: 12px 16px;">{{ $dept['manager'] }}</td>
                                                    <td class="text-end text-warning fw-bold" style="padding: 12px 16px;">{{ currency_format_with_sym($dept['pending']) }}</td>
                                                    <td class="text-end text-success fw-bold" style="padding: 12px 16px;">{{ currency_format_with_sym($dept['earned']) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <!-- Team Ledger -->
                        @if(!empty($teamLedger))
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark mb-2"><i class="ti ti-users text-warning me-1"></i>{{ __('Team Level Incentives') }}</h6>
                                <div class="table-responsive border rounded-3 bg-white">
                                    <table class="table align-items-center mb-0">
                                        <thead class="bg-light">
                                            <tr class="text-xs text-muted uppercase">
                                                <th style="padding: 10px 16px;">{{ __('Team') }}</th>
                                                <th style="padding: 10px 16px;">{{ __('Team Head / Lead') }}</th>
                                                <th style="padding: 10px 16px;" class="text-end">{{ __('Pending Incentive') }}</th>
                                                <th style="padding: 10px 16px;" class="text-end">{{ __('Earned Incentive') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($teamLedger as $team)
                                                <tr>
                                                    <td class="fw-semibold text-dark" style="padding: 12px 16px;">{{ $team['name'] }}</td>
                                                    <td style="padding: 12px 16px;">{{ $team['manager'] }}</td>
                                                    <td class="text-end text-warning fw-bold" style="padding: 12px 16px;">{{ currency_format_with_sym($team['pending']) }}</td>
                                                    <td class="text-end text-success fw-bold" style="padding: 12px 16px;">{{ currency_format_with_sym($team['earned']) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <!-- Member Ledger -->
                        @if(!empty($memberLedger))
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark mb-2"><i class="ti ti-user-check text-primary me-1"></i>{{ __('Individual Member Incentives') }}</h6>
                                <div class="table-responsive border rounded-3 bg-white">
                                    <table class="table align-items-center mb-0">
                                        <thead class="bg-light">
                                            <tr class="text-xs text-muted uppercase">
                                                <th style="padding: 10px 16px;">{{ __('Member') }}</th>
                                                <th style="padding: 10px 16px;" class="text-end">{{ __('Pending Incentive') }}</th>
                                                <th style="padding: 10px 16px;" class="text-end">{{ __('Earned Incentive') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($memberLedger as $member)
                                                <tr>
                                                    <td class="fw-semibold text-dark" style="padding: 12px 16px;">{{ $member['name'] }}</td>
                                                    <td class="text-end text-warning fw-bold" style="padding: 12px 16px;">{{ currency_format_with_sym($member['pending']) }}</td>
                                                    <td class="text-end text-success fw-bold" style="padding: 12px 16px;">{{ currency_format_with_sym($member['earned']) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        @if(empty($departmentLedger) && empty($teamLedger) && empty($memberLedger))
                            <div class="text-center py-5 text-muted">
                                <i class="ti ti-cash-off fs-1"></i>
                                <p class="mt-2 text-sm">{{ __('No incentives found / allocated in your scope.') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
<script>
    /* ── Summary Metrics Toggle (Today / This month) ─────────────────── */
    function smSetView(mode) {
        var isToday = mode === 'today';

        // Toggle button styles
        var btnToday = document.getElementById('smBtnToday');
        var btnMonth = document.getElementById('smBtnMonth');
        if (isToday) {
            btnToday.style.background  = '#1e1e1e';
            btnToday.style.color       = '#ffffff';
            btnToday.style.borderColor = '#333';
            btnMonth.style.background  = 'transparent';
            btnMonth.style.color       = '#6b6b6b';
            btnMonth.style.borderColor = '#252525';
        } else {
            btnMonth.style.background  = '#1e1e1e';
            btnMonth.style.color       = '#ffffff';
            btnMonth.style.borderColor = '#333';
            btnToday.style.background  = 'transparent';
            btnToday.style.color       = '#6b6b6b';
            btnToday.style.borderColor = '#252525';
        }

        // Swap value + delta spans
        document.querySelectorAll('.sm-val-today').forEach(function(el) {
            el.classList.toggle('d-none', !isToday);
        });
        document.querySelectorAll('.sm-val-month').forEach(function(el) {
            el.classList.toggle('d-none', isToday);
        });
        document.querySelectorAll('.sm-delta-today').forEach(function(el) {
            el.classList.toggle('d-none', !isToday);
        });
        document.querySelectorAll('.sm-delta-month').forEach(function(el) {
            el.classList.toggle('d-none', isToday);
        });
    }
</script>
<script>
    // Toggle hierarchy view folders
    $(document).on('click', '.tree-toggle', function(e) {
        e.stopPropagation();
        var targetId = $(this).attr('data-target-id');
        toggleRowChildren(targetId, this);
    });

    function toggleRowChildren(targetId, toggleElement) {
        $(toggleElement).toggleClass('collapsed');
        var isCollapsed = $(toggleElement).hasClass('collapsed');
        
        $('.tree-row[data-parent-id="' + targetId + '"]').each(function() {
            var childRow = $(this);
            if (isCollapsed) {
                childRow.hide();
                if (childRow.hasClass('has-sub-rows')) {
                    var childToggle = childRow.find('.tree-toggle');
                    childToggle.addClass('collapsed');
                    var childId = childRow.attr('data-row-id');
                    hideChildrenRecursive(childId);
                }
            } else {
                childRow.show();
            }
        });
    }

    function hideChildrenRecursive(parentId) {
        $('.tree-row[data-parent-id="' + parentId + '"]').each(function() {
            var childRow = $(this);
            childRow.hide();
            if (childRow.hasClass('has-sub-rows')) {
                var childToggle = childRow.find('.tree-toggle');
                childToggle.addClass('collapsed');
                var childId = childRow.attr('data-row-id');
                hideChildrenRecursive(childId);
            }
        });
    }

    // Real-time client-side search
    $(document).on('keyup', '#target-search-input', function() {
        var query = $(this).val().toLowerCase().trim();
        
        if (query === '') {
            // Restore default view (show all rows and keep expanded)
            $('.tree-row').show();
            $('.tree-toggle').removeClass('collapsed');
            return;
        }

        // Hide all rows initially
        $('.tree-row').hide();

        $('.tree-row').each(function() {
            var row = $(this);
            var targetName = row.find('td:first-child').text().toLowerCase();
            var targetAssigned = row.find('td:nth-child(3)').text().toLowerCase();
            var matchesTarget = targetName.indexOf(query) !== -1 || targetAssigned.indexOf(query) !== -1;
            
            if (matchesTarget) {
                row.show();
                var parentId = row.attr('data-parent-id');
                expandParentsRecursive(parentId);
            }
        });
    });

    function expandParentsRecursive(parentId) {
        if (!parentId) return;
        var parentRow = $('.tree-row[data-row-id="' + parentId + '"]');
        if (parentRow.length > 0) {
            parentRow.show();
            var parentToggle = parentRow.find('.tree-toggle');
            parentToggle.removeClass('collapsed');
            var nextParentId = parentRow.attr('data-parent-id');
            expandParentsRecursive(nextParentId);
        }
    }

    // ApexCharts - Monthly Trends
    $(document).ready(function() {
        var options = {
            chart: {
                height: 180,
                type: 'area',
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                sparkline: { enabled: false }
            },
            colors: ['#5e72e4', '#2dce89'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2.5 },
            series: [
                { name: "{{ __('Target Assigned') }}", data: @json($stats['monthly_target']) },
                { name: "{{ __('Target Achieved') }}", data: @json($stats['monthly_achieved']) }
            ],
            xaxis: {
                categories: @json($stats['monthly_labels']),
                labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 500 } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { colors: '#94a3b8', fontSize: '10px' } }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                padding: { top: 0, bottom: 0 }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                fontSize: '11px',
                markers: { radius: 4, width: 10, height: 10 }
            },
            tooltip: {
                theme: 'light',
                x: { show: true }
            }
        };
        var chart = new ApexCharts(document.querySelector("#monthly-trends-chart"), options);
        chart.render();

        // Chevron rotation for month accordion
        $(document).on('show.bs.collapse', '.collapse[id^="month-body-"]', function() {
            var id = $(this).attr('id').replace('month-body-', '');
            $('#month-block-' + id + ' .ti-chevron-down').css('transform', 'rotate(180deg)');
        });
        $(document).on('hide.bs.collapse', '.collapse[id^="month-body-"]', function() {
            var id = $(this).attr('id').replace('month-body-', '');
            $('#month-block-' + id + ' .ti-chevron-down').css('transform', 'rotate(0deg)');
        });
    });

    // HTML5 Drag and Drop logic
    function drag(ev) {
        ev.dataTransfer.setData("text/plain", ev.target.id);
        ev.currentTarget.classList.add('dragging');
    }
    
    function allowDrop(ev) {
        ev.preventDefault();
        ev.currentTarget.classList.add('drag-over');
    }
    
    function dragLeave(ev) {
        ev.currentTarget.classList.remove('drag-over');
    }
    
    function drop(ev) {
        ev.preventDefault();
        var column = ev.currentTarget;
        column.classList.remove('drag-over');
        var id = ev.dataTransfer.getData("text/plain");
        var card = document.getElementById(id);
        if (card && column) {
            column.querySelector('.kanban-cards-container').appendChild(card);
            card.classList.remove('dragging');
            var targetId = card.getAttribute('data-target-id');
            var newStatus = column.getAttribute('data-status');
            updateTargetStatus(targetId, newStatus, card);
        }
    }

    function updateTargetStatus(targetId, status, cardElement) {
        $.ajax({
            url: "/targets/" + targetId + "/status",
            type: 'POST',
            data: {
                status: status,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    if (response.achieved_value !== undefined) {
                        cardElement.querySelector('.achieved-val').innerText = response.achieved_value;
                        var pct = response.progress > 100 ? 100 : response.progress;
                        var bar = cardElement.querySelector('.progress-bar');
                        if (bar) {
                            bar.style.width = pct + '%';
                            bar.parentElement.previousElementSibling.querySelector('.font-weight-bold').innerText = pct + '%';
                            if (pct >= 100) {
                                bar.classList.add('bg-success');
                                bar.classList.remove('bg-primary');
                            } else {
                                bar.classList.remove('bg-success');
                                bar.classList.add('bg-primary');
                            }
                        }
                    }
                    updateColumnsCount();
                } else {
                    showToast('error', response.message);
                    location.reload();
                }
            },
            error: function(xhr) {
                showToast('error', '{{ __("Failed to update status. Permission Denied.") }}');
                location.reload();
            }
        });
    }

    function adjustProgress(targetId, amount, btnElement) {
        var card = btnElement.closest('.kanban-card');
        var valSpan = card.querySelector('.achieved-val');
        var currentVal = parseInt(valSpan.innerText);
        var targetVal = parseInt(card.getAttribute('data-target-value'));
        var newVal = currentVal + amount;
        if (newVal < 0) newVal = 0;
        
        var buttons = card.querySelectorAll('.adjust-btn');
        buttons.forEach(b => b.disabled = true);
        
        $.ajax({
            url: "/targets/" + targetId + "/progress",
            type: 'POST',
            data: {
                achieved_value: newVal,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                buttons.forEach(b => b.disabled = false);
                if (response.success) {
                    showToast('success', response.message);
                    valSpan.innerText = response.achieved_value;
                    var pct = response.progress > 100 ? 100 : response.progress;
                    var bar = card.querySelector('.progress-bar');
                    if (bar) {
                        bar.style.width = pct + '%';
                        bar.parentElement.previousElementSibling.querySelector('.font-weight-bold').innerText = pct + '%';
                        if (pct >= 100) {
                            bar.classList.add('bg-success');
                            bar.classList.remove('bg-primary');
                        } else {
                            bar.classList.remove('bg-success');
                            bar.classList.add('bg-primary');
                        }
                    }
                    
                    var currentColumn = card.closest('.kanban-column').getAttribute('data-status');
                    if (response.status !== currentColumn) {
                        document.querySelector('.kanban-column[data-status="' + response.status + '"] .kanban-cards-container').appendChild(card);
                        updateColumnsCount();
                    }
                } else {
                    showToast('error', response.message);
                }
            },
            error: function(xhr) {
                buttons.forEach(b => b.disabled = false);
                showToast('error', '{{ __("Failed to update progress. Permission Denied.") }}');
            }
        });
    }

    function updateColumnsCount() {
        $('.kanban-column').each(function() {
            var col = $(this);
            var status = col.attr('data-status');
            var count = col.find('.kanban-card').length;
            col.find('.count-badge').text(count);
        });
    }

    function showToast(type, message) {
        if (typeof toastrs === "function") {
            toastrs(type, message, type === 'success' ? 'success' : 'error');
        } else {
            var toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.style.position = 'fixed';
                toastContainer.style.top = '70px';
                toastContainer.style.right = '20px';
                toastContainer.style.zIndex = '9999';
                document.body.appendChild(toastContainer);
            }
            var toast = document.createElement('div');
            toast.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger') + ' alert-dismissible fade show shadow-lg';
            toast.style.minWidth = '250px';
            toast.innerHTML = '<strong>' + (type === 'success' ? 'Success!' : 'Error!') + '</strong> ' + message + 
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            toastContainer.appendChild(toast);
            setTimeout(function() {
                var bsAlert = new bootstrap.Alert(toast);
                bsAlert.close();
            }, 3000);
        }
    }

    function toggleTargetsLayout(layout) {
        if (layout === 'grid') {
            $('#targets-grid-layout').show();
            $('#targets-list-layout').hide();
            $('#btn-grid-layout').addClass('btn-light-primary').removeClass('btn-light');
            $('#btn-list-layout').addClass('btn-light').removeClass('btn-light-primary');
            localStorage.setItem('targets_hub_layout', 'grid');
        } else {
            $('#targets-grid-layout').hide();
            $('#targets-list-layout').show();
            $('#btn-grid-layout').addClass('btn-light').removeClass('btn-light-primary');
            $('#btn-list-layout').addClass('btn-light-primary').removeClass('btn-light');
            localStorage.setItem('targets_hub_layout', 'list');
        }
    }

    $(document).ready(function() {
        var savedLayout = localStorage.getItem('targets_hub_layout') || 'grid';
        toggleTargetsLayout(savedLayout);

        // Restore active tab
        var activeTab = localStorage.getItem('targets_active_tab');
        if (activeTab) {
            var tabBtn = $('#' + activeTab);
            if (tabBtn.length > 0) {
                // If it is Bootstrap 5 Tab
                var tab = new bootstrap.Tab(tabBtn[0]);
                tab.show();
            }
        }

        // Save active tab on click
        $('#targetTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            localStorage.setItem('targets_active_tab', e.target.id);
        });

        // Checkbox "Select All" logic
        $(document).on('change', '#select-all-targets', function() {
            var isChecked = $(this).prop('checked');
            $('.target-checkbox:not(:disabled)').prop('checked', isChecked);
            updateBulkDeleteButton();
        });

        $(document).on('change', '.target-checkbox', function() {
            var totalEnabled = $('.target-checkbox:not(:disabled)').length;
            var totalChecked = $('.target-checkbox:checked').length;
            $('#select-all-targets').prop('checked', totalEnabled === totalChecked && totalEnabled > 0);
            updateBulkDeleteButton();
        });

        function updateBulkDeleteButton() {
            var selectedCount = $('.target-checkbox:checked').length;
            if (selectedCount > 0) {
                $('#btn-bulk-delete').removeClass('d-none');
                $('#bulk-delete-count').text(selectedCount);
            } else {
                $('#btn-bulk-delete').addClass('d-none');
            }
        }
    });

    function bulkDeleteTargets() {
        var selectedIds = [];
        $('.target-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) return;

        if (confirm("{{ __('Are you sure you want to delete the selected targets?') }}")) {
            $('#btn-bulk-delete').prop('disabled', true);
            $.ajax({
                url: "{{ route('targets.bulk-destroy') }}",
                type: 'POST',
                data: {
                    ids: selectedIds,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast('success', response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showToast('error', response.message);
                        $('#btn-bulk-delete').prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    showToast('error', "{{ __('Failed to delete selected targets.') }}");
                    $('#btn-bulk-delete').prop('disabled', false);
                }
            });
        }
    }
    // ─── Drill-Down Navigation ───────────────────────────────────────────────
    var activeDeptPerMonth = {};

    $(document).on('click', '.dept-row[data-drill-dept]', function () {
        var did   = $(this).data('drill-dept');
        var month = $(this).data('month');
        var container = $('#drill-' + month);
        activeDeptPerMonth[month] = did;
        container.find('.drill-level').hide();
        container.find('.drill-level[data-level="1"][data-dept="' + did + '"]').show();
    });

    $(document).on('click', '.team-row[data-drill-team]', function () {
        var tid   = $(this).data('drill-team');
        var month = $(this).data('month');
        var container = $('#drill-' + month);
        container.find('.drill-level').hide();
        container.find('.drill-level[data-level="2"][data-team="' + tid + '"]').show();
    });

    $(document).on('click', '.dn-back-btn.drill-back[data-back-to="0"]', function (e) {
        e.stopPropagation();
        var month = $(this).data('month');
        var container = $('#drill-' + month);
        container.find('.drill-level').hide();
        container.find('.drill-level[data-level="0"]').show();
    });

    $(document).on('click', '.dn-back-btn.drill-back-to-dept', function (e) {
        e.stopPropagation();
        var month = $(this).data('month');
        var container = $('#drill-' + month);
        var did = activeDeptPerMonth[month];
        container.find('.drill-level').hide();
        if (did) {
            container.find('.drill-level[data-level="1"][data-dept="' + did + '"]').show();
        } else {
            container.find('.drill-level[data-level="0"]').show();
        }
    });
</script>

<style>
/* ═══ Drill-Down Card System ═══════════════════════════════════════════════ */
.dn-container { padding: 4px 0; }

.dn-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    margin-bottom: 6px;
    background: #fff;
    border: 1.5px solid #f1f5f9;
    border-radius: 12px;
    transition: border-color 0.18s, box-shadow 0.18s, transform 0.12s;
}
.dn-card.dept-row, .dn-card.team-row { cursor: pointer; }
.dn-card.dept-row:hover, .dn-card.team-row:hover {
    border-color: #e2e8f0;
    box-shadow: 0 4px 16px rgba(0,0,0,0.07);
    transform: translateY(-1px);
}
.dn-card.dept-row:hover { border-color: #2563eb40; }
.dn-card.team-row:hover { border-color: #d9770640; }

.dn-avatar-wrap {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.dn-avatar-wrap.sm { width: 30px; height: 30px; border-radius: 8px; }
.dn-initials { font-size: 0.82rem; font-weight: 700; line-height: 1; }

.dn-body { flex: 1; min-width: 0; }
.dn-name {
    display: block;
    font-weight: 700;
    font-size: 0.88rem;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 2px;
}
.dn-sub { font-size: 0.7rem; color: #94a3b8; display: block; margin-bottom: 5px; }
.dn-bar-track {
    height: 5px;
    background: #f1f5f9;
    border-radius: 10px;
    overflow: hidden;
    max-width: 120px;
}
.dn-bar-fill { height: 100%; border-radius: 10px; transition: width 0.4s ease; }

.dn-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}
.dn-kv { text-align: center; }
.dn-kv span { display: block; font-weight: 700; font-size: 0.9rem; color: #1e293b; }
.dn-kv.green span { color: #198754; }
.dn-kv small { font-size: 0.65rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.4px; }
.dn-sep-v { width: 1px; height: 24px; background: #e2e8f0; }
.dn-pct-pill {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 20px;
    white-space: nowrap;
    min-width: 42px;
    text-align: center;
}
.dn-arrow { font-size: 0.9rem; color: #cbd5e1; flex-shrink: 0; }

/* Breadcrumb */
.dn-crumb {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 10px;
    flex-wrap: wrap;
}
.dn-back-btn {
    background: none; border: none; padding: 4px 8px; border-radius: 6px;
    font-size: 0.75rem; font-weight: 500; color: #64748b; cursor: pointer;
    display: inline-flex; align-items: center; gap: 4px;
    transition: background 0.15s, color 0.15s;
}
.dn-back-btn:hover { background: #f1f5f9; color: #1e293b; }
.dn-crumb-sep { color: #cbd5e1; font-size: 0.85rem; }
.dn-crumb-cur { font-size: 0.75rem; font-weight: 600; color: #1e293b; }

/* Summary card (shown at top of level 1/2) */
.dn-summary-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-radius: 10px;
}

/* Empty state */
.dn-empty-msg {
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: center;
    padding: 20px;
    color: #94a3b8;
    font-size: 0.8rem;
}
.dn-empty-msg i { font-size: 1.1rem; }
</style>
@endpush
