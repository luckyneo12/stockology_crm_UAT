@extends('layouts.main')

@section('page-title')
    {{ __('Collaborative Spreadsheet') }}
@endsection

@section('page-action')
    <div class="float-end d-flex align-items-center gap-2">
        <span id="save-status" class="badge bg-light-success text-success px-3 py-2" style="border-radius: 8px; font-weight: 600; font-size: 0.85rem; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.08); display: inline-flex; align-items: center;">
            <i class="ti ti-cloud-check me-1" style="font-size: 1.1rem;"></i> {{ __('Saved to CRM') }}
        </span>
        <a href="#" class="btn btn-sm btn-primary" 
           data-url="{{ route('crm.sheets.share', $sheet->id) }}" 
           data-ajax-popup="true" data-size="md" 
           data-title="{{ __('Invite Teammates') }}"
           style="border-radius: 8px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2); font-weight: 600; transition: all 0.2s;">
            <i class="ti ti-share"></i> {{ __('Share') }}
        </a>
        <a href="{{ route('crm.sheets.index') }}" class="btn btn-sm btn-secondary" style="border-radius: 8px; font-weight: 600; background: #64748b; border: none; box-shadow: 0 4px 10px rgba(100, 116, 139, 0.15); transition: all 0.2s;">
            <i class="ti ti-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>
@endsection

@section('content')
    @include('lead::layouts.anti_screenshot')
    <!-- Load jSpreadsheet CE, jSuites, Material Icons, and SheetJS from CDN -->
    <link rel="stylesheet" href="https://unpkg.com/jspreadsheet-ce@4.15.0/dist/jspreadsheet.css" type="text/css" />
    <link rel="stylesheet" href="https://unpkg.com/jsuites@4.17.7/dist/jsuites.css" type="text/css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    @php
        $isCompanyOrAdmin = in_array(Auth::user()->type, ['company', 'super admin']);
        $isDeptHead = false;
        $isTeamLead = false;
        if (!$isCompanyOrAdmin && module_is_active('Hrm')) {
            $employee = \Workdo\Hrm\Entities\Employee::where('user_id', Auth::id())->first();
            if ($employee) {
                $managedDepts = \Workdo\Hrm\Entities\Department::where('manager_id', $employee->id)
                    ->where('workspace', getActiveWorkSpace())
                    ->get();
                foreach ($managedDepts as $d) {
                    if ($d->type == 'department') {
                        $isDeptHead = true;
                    } elseif ($d->type == 'team') {
                        $isTeamLead = true;
                    }
                }
            }
        }
        $canCopyPasteNormally = $isCompanyOrAdmin || $isDeptHead || $isTeamLead;

        $data = $sheet->data;
        $minCols = 26;
        $minRows = 100;
        if (is_array($data) && count($data) > 0) {
            $data = array_values($data);
            // Pad rows to at least minRows
            while (count($data) < $minRows) {
                $data[] = array_fill(0, $minCols, '');
            }
            foreach ($data as &$row) {
                if (is_array($row)) {
                    $row = array_values($row);
                    // Pad columns to at least minCols
                    while (count($row) < $minCols) {
                        $row[] = '';
                    }
                } else {
                    $row = array_fill(0, $minCols, '');
                }
            }
        } else {
            $data = array_fill(0, $minRows, array_fill(0, $minCols, ''));
        }
    @endphp

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&display=swap');

        .gs-container {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            margin-bottom: 2rem;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Fullscreen Spreadsheet Overrides */
        .gs-container.gs-fullscreen {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 1040 !important;
            border-radius: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }
        .gs-container.gs-fullscreen .jexcel_content {
            height: calc(100vh - 165px) !important;
            max-height: calc(100vh - 165px) !important;
        }
        
        /* Google Sheets Header & Menu Bar */
        .gs-header {
            background: #ffffff;
            padding: 0.9rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .gs-logo-title-group {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }
        .gs-logo-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.22);
        }
        .gs-title-and-menu {
            display: flex;
            flex-direction: column;
        }
        .gs-sheet-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            padding: 0.15rem 0.5rem;
            border-radius: 6px;
            border: 1px solid transparent;
            transition: all 0.2s;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .gs-sheet-title:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }
        .gs-menu-bar {
            display: flex;
            gap: 0.35rem;
            margin-top: 0.35rem;
        }
        .gs-menu-btn {
            background: transparent;
            border: none;
            color: #475569;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .gs-menu-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        .gs-menu-btn::after {
            display: none !important;
        }
        
        /* Collaborative Avatars */
        .gs-active-users {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .gs-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
            color: #ffffff;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            position: relative;
            cursor: default;
            user-select: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .gs-user-avatar:hover {
            transform: scale(1.15) translateY(-2px);
            z-index: 10;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        /* Custom Dropdown Animations */
        .dropdown-menu {
            border: 1px solid #cbd5e1 !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
            border-radius: 10px !important;
            animation: gsDropdownFade 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            padding: 6px 0 !important;
        }
        @keyframes gsDropdownFade {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .dropdown-item {
            font-size: 0.85rem !important;
            padding: 8px 16px !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            color: #334155 !important;
            font-weight: 500 !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.5rem;
        }
        .dropdown-item:hover {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
        }

        .gs-grid-body {
            background: #f1f5f9;
            padding: 0;
            border-bottom: 1px solid #cbd5e1;
        }
        .gs-grid-scroll-pane {
            background: #f1f5f9;
            border: none;
            border-radius: 0;
            overflow: hidden;
        }
        
        /* Style adjustments to jSpreadsheet toolbar and headers */
        .jexcel_container {
            border: none !important;
            background-color: #f1f5f9 !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            width: 100% !important;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }
        .jexcel_container input, .jexcel_container textarea, .jexcel td.editing {
            user-select: text !important;
            -webkit-user-select: text !important;
            -moz-user-select: text !important;
            -ms-user-select: text !important;
        }
        .jexcel_content {
            background-color: #f1f5f9 !important;
            border: none !important;
        }
        
        /* Sleek custom scrollbars for jSpreadsheet content */
        .jexcel_content::-webkit-scrollbar {
            width: 12px;
            height: 12px;
        }
        .jexcel_content::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .jexcel_content::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 6px;
            border: 3px solid #f1f5f9;
        }
        .jexcel_content::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .jexcel_toolbar {
            background: #f8fafc !important;
            border-bottom: 1px solid #cbd5e1 !important;
            padding: 8px 16px !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
        }
        .jexcel_toolbar i.material-icons {
            font-size: 20px !important;
            color: #475569 !important;
        }
        .jexcel_toolbar > div {
            border-radius: 6px !important;
            padding: 6px !important;
            transition: all 0.2s !important;
            cursor: pointer;
        }
        .jexcel_toolbar > div:hover {
            background: #e2e8f0 !important;
        }
        .jexcel_toolbar select {
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            padding: 4px 8px !important;
            font-size: 0.8rem !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            color: #334155 !important;
            background: #ffffff !important;
            outline: none !important;
            cursor: pointer;
        }
        .jexcel {
            border-collapse: collapse;
            background-color: #ffffff;
        }
        
        /* Column and Row Headers styling */
        .jexcel > thead > tr > td {
            position: relative !important;
            background-color: #f8fafc !important;
            color: #475569 !important;
            font-weight: 600 !important;
            font-size: 0.78rem !important;
            border-right: 1px solid #cbd5e1 !important;
            border-bottom: 1px solid #cbd5e1 !important;
            border-top: none !important;
            border-left: none !important;
            padding: 8px 20px 8px 12px !important;
            text-align: center !important;
            user-select: none;
        }
        .jexcel .jexcel_row {
            background: #f8fafc !important;
            color: #475569 !important;
            font-weight: 600 !important;
            font-size: 0.78rem !important;
            border-right: 1px solid #cbd5e1 !important;
            border-bottom: 1px solid #cbd5e1 !important;
            border-top: none !important;
            border-left: none !important;
            padding: 8px 6px !important;
            text-align: center !important;
            user-select: none !important;
        }
        .jexcel td.jexcel_selectall {
            background: #f8fafc !important;
            border-right: 1px solid #cbd5e1 !important;
            border-bottom: 1px solid #cbd5e1 !important;
            border-top: none !important;
            border-left: none !important;
        }
        
        /* Cell design */
        .jexcel > tbody > tr > td {
            background-color: #ffffff;
            border: 1px solid #cbd5e1 !important;
            padding: 6px 10px !important;
            font-size: 0.825rem !important;
            color: #334155 !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .jexcel > tbody > tr > td.readonly {
            background-color: #f8fafc !important;
            color: #64748b !important;
        }
        
        /* Excel Selection Outline (Green theme) */
        .jexcel .highlight {
            background-color: rgba(16, 185, 129, 0.05) !important;
        }
        .jexcel .highlight-left {
            border-left: 2px solid #059669 !important;
        }
        .jexcel .highlight-right {
            border-right: 2px solid #059669 !important;
        }
        .jexcel .highlight-top {
            border-top: 2px solid #059669 !important;
        }
        .jexcel .highlight-bottom {
            border-bottom: 2px solid #059669 !important;
        }
        
        /* Selected header styling */
        .jexcel thead td.selected, .jexcel tbody td.jexcel_row.selected {
            background-color: #d1fae5 !important;
            color: #059669 !important;
            font-weight: 700 !important;
        }
        
        /* Corner handler drag selection square */
        .jexcel_corner {
            background-color: #059669 !important;
            border: 1px solid #ffffff !important;
            width: 7px !important;
            height: 7px !important;
        }
        
        /* Excel Formula Bar Styling */
        .gs-formula-bar {
            display: flex;
            align-items: center;
            background: #ffffff;
            border-bottom: 1px solid #cbd5e1;
            padding: 8px 16px;
            gap: 10px;
        }
        .gs-formula-address {
            min-width: 60px;
            text-align: center;
            font-weight: 700;
            font-size: 0.85rem;
            color: #475569;
            background: #f8fafc;
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            user-select: none;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .gs-formula-fx {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            cursor: pointer;
            user-select: none;
            transition: all 0.2s;
        }
        .gs-formula-fx:hover {
            background: #f1f5f9;
        }
        .gs-formula-input-wrapper {
            flex-grow: 1;
            display: flex;
        }
        .gs-formula-input-wrapper input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 0.85rem;
            font-family: 'Fira Code', 'Consolas', monospace !important;
            color: #0f172a;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .gs-formula-input-wrapper input:focus {
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }

        /* Polish the Excel-like inline filter search row */
        .jexcel > thead > tr.jexcel_filter {
            background-color: #ffffff !important;
        }
        .jexcel > thead > tr.jexcel_filter > td {
            background-color: #ffffff !important;
            border-bottom: 1px solid #cbd5e1 !important;
            border-right: 1px solid #cbd5e1 !important;
            border-top: none !important;
            border-left: none !important;
            padding: 4px 24px 4px 6px !important;
            vertical-align: middle !important;
            height: 32px !important;
            position: relative !important;
            box-sizing: border-box !important;
        }
        
        /* Customize the filter select box inside the cell */
        .jexcel > thead > tr.jexcel_filter > td.jdropdown {
            background-color: #ffffff !important;
        }
        .jexcel > thead > tr.jexcel_filter > td .jdropdown-header {
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 4px !important;
            padding: 0 20px 0 6px !important;
            font-size: 0.75rem !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            color: #334155 !important;
            height: 24px !important;
            display: flex !important;
            align-items: center !important;
            background-position: center right 4px !important;
            background-size: 14px !important;
            box-sizing: border-box !important;
            transition: border-color 0.15s, background-color 0.15s;
        }
        .jexcel > thead > tr.jexcel_filter > td.jdropdown-focus .jdropdown-header {
            border-color: #059669 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.1) !important;
            outline: none !important;
        }
        
        /* Floating filter dropdown popup styling */
        .jdropdown-container {
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            background-color: #ffffff !important;
            padding: 4px 0 !important;
            animation: gsDropdownFade 0.15s ease-out;
        }
        .jdropdown-content {
            padding: 0 !important;
        }
        .jdropdown-item {
            font-size: 0.825rem !important;
            padding: 6px 16px !important;
            color: #334155 !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .jdropdown-item:hover, .jdropdown-cursor {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
        }
        .jdropdown-selected {
            background-color: #d1fae5 !important;
            color: #065f46 !important;
            font-weight: 600 !important;
        }

        .jexcel > thead > tr.jexcel_filter > td.jexcel_column_filter {
            background-repeat: no-repeat !important;
            background-position: center right 6px !important;
        }
    </style>

    <div class="row">
        <div class="col-sm-12">
            <div class="gs-container">
                <!-- Top Google Sheets Header -->
                <div class="gs-header">
                    <div class="gs-logo-title-group">
                        <div class="gs-logo-icon">
                            <i class="ti ti-table"></i>
                        </div>
                        <div class="gs-title-and-menu">
                            <h5 class="gs-sheet-title">{{ $sheet->name }}</h5>
                            
                            <!-- Menu Options -->
                            <div class="gs-menu-bar">
                                <div class="dropdown">
                                    <button class="gs-menu-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ __('File') }}
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if(Auth::user()->type == 'company')
                                            <li><a class="dropdown-item" href="#" onclick="exportExcel()"><i class="ti ti-file-analytics me-2"></i>{{ __('Export as Excel (.xlsx)') }}</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="spreadsheet.download()"><i class="ti ti-download me-2"></i>{{ __('Download as CSV') }}</a></li>
                                        @endif
                                        <li><a class="dropdown-item" href="#" onclick="$('#import_excel_file').click()"><i class="ti ti-upload me-2"></i>{{ __('Import Excel (.xlsx)') }}</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#" onclick="saveData()"><i class="ti ti-cloud-upload me-2"></i>{{ __('Save to CRM') }}</a></li>
                                    </ul>
                                </div>
                                <div class="dropdown">
                                    <button class="gs-menu-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ __('Edit') }}
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="spreadsheet.undo()"><i class="ti ti-arrow-back-up me-2"></i>{{ __('Undo') }}</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="spreadsheet.redo()"><i class="ti ti-arrow-forward-up me-2"></i>{{ __('Redo') }}</a></li>
                                    </ul>
                                </div>
                                <div class="dropdown">
                                    <button class="gs-menu-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ __('Insert') }}
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="spreadsheet.insertRow()"><i class="ti ti-row-insert-bottom me-2"></i>{{ __('Insert Row') }}</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="spreadsheet.insertColumn()"><i class="ti ti-column-insert-right me-2"></i>{{ __('Insert Column') }}</a></li>
                                    </ul>
                                </div>
                                <div class="dropdown">
                                    <button class="gs-menu-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ __('Delete') }}
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="spreadsheet.deleteRow()"><i class="ti ti-trash me-2"></i>{{ __('Delete Selected Row') }}</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="spreadsheet.deleteColumn()"><i class="ti ti-trash me-2"></i>{{ __('Delete Selected Column') }}</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side Connected Users Avatar List & Actions -->
                    <div class="d-flex align-items-center gap-2">
                        <div class="gs-active-users" id="gs-avatar-container">
                            <div class="gs-user-avatar" id="self-avatar" style="background: #10b981; border-color: #10b981;" data-bs-toggle="tooltip" title="{{ Auth::user()->name }} (You)">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-icon btn-light" id="gs-fullscreen-btn" data-bs-toggle="tooltip" title="{{ __('Toggle Fullscreen') }}" style="border-radius: 8px; width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; background: #f1f5f9; border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.2s;">
                            <i class="ti ti-maximize" id="fs-icon" style="font-size: 1.2rem; color: #475569;"></i>
                        </button>
                    </div>
                </div>

                <!-- Excel Formula Bar -->
                <div class="gs-formula-bar">
                    <div class="gs-formula-address" id="gs-formula-address" title="{{ __('Selected Cell') }}">A1</div>
                    <div class="gs-formula-fx" title="{{ __('Insert Function') }}">
                        <i class="ti ti-math-function" style="font-size: 1.2rem; color: #5f6368;"></i>
                    </div>
                    <div class="gs-formula-input-wrapper">
                        <input type="text" id="gs-formula-input" placeholder="{{ __('Formula or Cell Content') }}" autocomplete="off" />
                    </div>
                </div>

                <!-- Interactive Grid Pane -->
                <div class="gs-grid-body">
                    <div class="gs-grid-scroll-pane">
                        <div id="spreadsheet"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Input for File Uploading -->
    <input type="file" id="import_excel_file" accept=".xlsx, .xls" style="display: none;" />

    <!-- Load CDN Scripts -->
    <script src="https://unpkg.com/jsuites@4.17.7/dist/jsuites.js"></script>
    <script src="https://unpkg.com/jspreadsheet-ce@4.15.0/dist/index.js"></script>
    <script src="https://unpkg.com/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>

    <script>
        var sheetId = {{ $sheet->id }};
        var currentUserId = {{ Auth::id() }};
        var currentUsername = "{{ Auth::user()->name }}";
        var initialData = {!! json_encode($data) !!};
        var spreadsheet = null;
        var socket = null;
        var socketUrl = "{{ env('SHEETS_NODE_URL', 'http://localhost:3001') }}";
        var saveTimeout = null;
        var ignoreSocketEmit = false;
        var activeCol = 0;
        var activeRow = 0;
        var canCopyPasteNormally = @json($canCopyPasteNormally);
        var crmCurrentSelection = null;
        var crmCopiedData = "";

        function getCellNameFromCoords(col, row) {
            var jspreadsheetFn = (typeof jspreadsheet !== 'undefined') ? jspreadsheet : ((typeof jexcel !== 'undefined') ? jexcel : null);
            if (jspreadsheetFn && jspreadsheetFn.helpers && typeof jspreadsheetFn.helpers.getCellNameFromCoords === 'function') {
                return jspreadsheetFn.helpers.getCellNameFromCoords(col, row);
            }
            if (jspreadsheetFn && jspreadsheetFn.helpers && typeof jspreadsheetFn.helpers.getColumnNameFromCoords === 'function') {
                return jspreadsheetFn.helpers.getColumnNameFromCoords(col, row);
            }
            if (jspreadsheetFn && typeof jspreadsheetFn.getColumnNameFromCoords === 'function') {
                return jspreadsheetFn.getColumnNameFromCoords(col, row);
            }
            var getColLetter = function(c) {
                var letter = "";
                while (c >= 0) {
                    letter = String.fromCharCode((c % 26) + 65) + letter;
                    c = Math.floor(c / 26) - 1;
                }
                return letter;
            };
            return getColLetter(col) + (row + 1);
        }

        // Custom cursors and colors mappings
        var collaboratorCursors = {};
        var collaboratorColors = ['#1a73e8', '#ea4335', '#f9ab00', '#137333', '#a142f4', '#00acc1', '#ff6d00'];

        $(document).ready(function() {
            var jspreadsheetFn = (typeof jspreadsheet !== 'undefined') ? jspreadsheet : ((typeof jexcel !== 'undefined') ? jexcel : null);

            if (!jspreadsheetFn) {
                console.error("jSpreadsheet core library failed to load.");
                return;
            }

            // Formula Input Handler
            $(document).on('keyup change', '#gs-formula-input', function() {
                if (spreadsheet) {
                    var val = $(this).val();
                    ignoreSocketEmit = false;
                    spreadsheet.setValueFromCoords(activeCol, activeRow, val);
                }
            });

            // Generate column definitions dynamically to ensure filters are enabled on every column
            var columnsConfig = [];
            var maxCols = 100; // Pre-configure up to 100 columns to support insertions and imports
            for (var i = 0; i < maxCols; i++) {
                columnsConfig.push({
                    type: 'text',
                    filter: true
                });
            }

            // 1. Initialize spreadsheet grid with formatting toolbar
            spreadsheet = jspreadsheetFn(document.getElementById('spreadsheet'), {
                data: initialData,
                columns: columnsConfig,
                minDimensions: [26, 100], // A to Z columns, 100 rows
                defaultColWidth: 100, // Standard Excel column width
                tableOverflow: true,
                tableHeight: 'calc(100vh - 290px)', // Fixed scroll window dynamically sized
                tableWidth: '100%',
                toolbar: [
                    {
                        type: 'i',
                        content: 'undo',
                        onclick: function() { spreadsheet.undo(); }
                    },
                    {
                        type: 'i',
                        content: 'redo',
                        onclick: function() { spreadsheet.redo(); }
                    },
                    {
                        type: 'i',
                        content: 'save',
                        onclick: function() { saveData(); }
                    },
                    {
                        type: 'select',
                        k: 'font-family',
                        v: ['Arial','Verdana','Courier New','Georgia','Trebuchet MS','Outfit','Inter']
                    },
                    {
                        type: 'select',
                        k: 'font-size',
                        v: ['10px','11px','12px','13px','14px','16px','18px']
                    },
                    {
                        type: 'i',
                        content: 'format_bold',
                        k: 'font-weight',
                        v: 'bold'
                    },
                    {
                        type: 'i',
                        content: 'format_italic',
                        k: 'font-style',
                        v: 'italic'
                    },
                    {
                        type: 'i',
                        content: 'format_color_text',
                        k: 'color',
                        v: '#ef4444'
                    },
                    {
                        type: 'i',
                        content: 'format_color_fill',
                        k: 'background-color',
                        v: '#fef08a'
                    },
                    {
                        type: 'i',
                        content: 'format_align_left',
                        k: 'text-align',
                        v: 'left'
                    },
                    {
                        type: 'i',
                        content: 'format_align_center',
                        k: 'text-align',
                        v: 'center'
                    },
                    {
                        type: 'i',
                        content: 'format_align_right',
                        k: 'text-align',
                        v: 'right'
                    }
                ],
                columnResize: true,
                rowResize: true,
                columnDrag: true,
                rowDrag: true,
                filters: true,
                selectionCopy: true,
                onchange: function(instance, cell, col, row, value) {
                    if (ignoreSocketEmit) return;

                    // Update formula input if active cell has changed
                    if (parseInt(col) === activeCol && parseInt(row) === activeRow) {
                        $('#gs-formula-input').val(value);
                    }

                    // Broadcast cell changes to collaborators
                    if (socket && socket.connected) {
                        socket.emit('cell_edit', {
                            sheet_id: sheetId,
                            col: parseInt(col),
                            row: parseInt(row),
                            value: value,
                            user_id: currentUserId,
                            username: currentUsername
                        });
                    }

                    // Trigger debounced background auto-save
                    triggerAutoSave();
                },
                onselection: function(instance, col1, row1, col2, row2) {
                    activeCol = parseInt(col1);
                    activeRow = parseInt(row1);

                    crmCurrentSelection = {
                        col1: parseInt(col1),
                        row1: parseInt(row1),
                        col2: parseInt(col2),
                        row2: parseInt(row2)
                    };

                    // Update cell address box
                    var cellName = getCellNameFromCoords(activeCol, activeRow);
                    $('#gs-formula-address').text(cellName);

                    // Get raw cell value (e.g. formula) from spreadsheet options data
                    var rawValue = '';
                    if (spreadsheet && spreadsheet.options && spreadsheet.options.data && spreadsheet.options.data[activeRow] && spreadsheet.options.data[activeRow][activeCol] !== undefined) {
                        rawValue = spreadsheet.options.data[activeRow][activeCol];
                    }
                    $('#gs-formula-input').val(rawValue);

                    if (socket && socket.connected) {
                        socket.emit('cursor_move', {
                            sheet_id: sheetId,
                            col: activeCol,
                            row: activeRow,
                            user_id: currentUserId,
                            username: currentUsername
                        });
                    }
                },
                oninsertrow: function() { triggerAutoSave(); },
                ondeleterow: function() { triggerAutoSave(); },
                oninsertcolumn: function() { triggerAutoSave(); },
                ondeletecolumn: function() { triggerAutoSave(); }
            });

            // Initialize bootstrap tooltip helper
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Toggle Fullscreen spreadsheet mode
            $(document).on('click', '#gs-fullscreen-btn', function() {
                var container = $('.gs-container');
                container.toggleClass('gs-fullscreen');
                var icon = $('#fs-icon');
                if (container.hasClass('gs-fullscreen')) {
                    icon.removeClass('ti-maximize').addClass('ti-minimize');
                } else {
                    icon.removeClass('ti-minimize').addClass('ti-maximize');
                }
            });

            // Click delegation to trigger filter dropdown when clicking the funnel icon area (cell padding)
            var lastFilterToggle = 0;
            $(document).on('click mousedown', '.jexcel td.jexcel_column_filter', function(e) {
                if (Date.now() - lastFilterToggle < 150) {
                    return;
                }
                
                if (e.target === this || $(e.target).closest('.jdropdown-header').length > 0 || $(e.target).hasClass('jdropdown-header')) {
                    if (this.dropdown) {
                        lastFilterToggle = Date.now();
                        
                        var isOpened = false;
                        if (typeof this.dropdown.isOpened === 'function') {
                            isOpened = this.dropdown.isOpened();
                        } else {
                            isOpened = $(this).find('.jdropdown-header').hasClass('jdropdown-focus') || $(this).hasClass('jdropdown-focus');
                        }
                        
                        if (isOpened) {
                            this.dropdown.close();
                        } else {
                            this.dropdown.open();
                        }
                        e.preventDefault();
                        e.stopPropagation();
                    }
                }
            });


            // 2. Establish connection to collaborative socket server
            initSocket();

            // Intercept Copy Event
            document.addEventListener('copy', function(e) {
                if (canCopyPasteNormally) {
                    return;
                }

                if (!crmCurrentSelection) {
                    e.preventDefault();
                    return;
                }

                var startCol = Math.min(crmCurrentSelection.col1, crmCurrentSelection.col2);
                var endCol = Math.max(crmCurrentSelection.col1, crmCurrentSelection.col2);
                var startRow = Math.min(crmCurrentSelection.row1, crmCurrentSelection.row2);
                var endRow = Math.max(crmCurrentSelection.row1, crmCurrentSelection.row2);

                var isMultiple = (startCol !== endCol || startRow !== endRow);

                if (isMultiple) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (typeof show_toastr === 'function') {
                        show_toastr('Security Restrict', 'Only Team Heads and Department Heads can copy multiple cells.', 'warning');
                    } else {
                        alert('Only Team Heads and Department Heads can copy multiple cells.');
                    }
                    return;
                }

                var cellValue = spreadsheet.getValueFromCoords(startCol, startRow);
                crmCopiedData = cellValue;

                e.clipboardData.setData('text/plain', 'Copy-paste restricted outside CRM');
                e.preventDefault();
                e.stopPropagation();

                if (typeof show_toastr === 'function') {
                    show_toastr('Security Notice', 'Cell copied (paste restricted to CRM sheet).', 'info');
                }
            });

            // Intercept Paste Event (Capture phase)
            document.addEventListener('paste', function(e) {
                if (canCopyPasteNormally) {
                    return;
                }

                var clipboardText = e.clipboardData.getData('text/plain');
                var textToPaste = "";
                if (clipboardText === 'Copy-paste restricted outside CRM') {
                    textToPaste = crmCopiedData;
                } else {
                    textToPaste = clipboardText;
                }

                if (!textToPaste) {
                    e.preventDefault();
                    e.stopPropagation();
                    return;
                }

                if (spreadsheet) {
                    var rows = textToPaste.split(/\r?\n/);
                    if (rows.length > 1 && rows[rows.length - 1] === '') {
                        rows.pop();
                    }

                    ignoreSocketEmit = false;
                    for (var r = 0; r < rows.length; r++) {
                        var cols = rows[r].split('\t');
                        for (var c = 0; c < cols.length; c++) {
                            var targetCol = activeCol + c;
                            var targetRow = activeRow + r;
                            spreadsheet.setValueFromCoords(targetCol, targetRow, cols[c]);
                        }
                    }
                }

                e.preventDefault();
                e.stopPropagation();
            }, true);
        });

        // Export as real Excel (.xlsx) using SheetJS
        function exportExcel() {
            @if(Auth::user()->type !== 'company')
                return;
            @endif
            var data = spreadsheet.getData();
            var worksheet = XLSX.utils.aoa_to_sheet(data);
            var workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Sheet1");
            var rawName = "{{ $sheet->name }}";
            var fileName = rawName.replace(/[^a-z0-9]/gi, '_').toLowerCase();
            XLSX.writeFile(workbook, fileName + ".xlsx");
        }

        // Import Excel (.xlsx) using SheetJS
        $(document).on('change', '#import_excel_file', function(e) {
            var files = e.target.files;
            if (!files.length) return;
            var file = files[0];
            
            var reader = new FileReader();
            reader.onload = function(e) {
                try {
                    var data = new Uint8Array(e.target.result);
                    var workbook = XLSX.read(data, {type: 'array'});
                    var firstSheetName = workbook.SheetNames[0];
                    var worksheet = workbook.Sheets[firstSheetName];
                    var arrData = XLSX.utils.sheet_to_json(worksheet, {header: 1});
                    
                    if (arrData && arrData.length > 0) {
                        // Ensure layout format consistency
                        while (arrData.length < 30) {
                            arrData.push(new Array(15).fill(""));
                        }
                        ignoreSocketEmit = true;
                        spreadsheet.setData(arrData);
                        ignoreSocketEmit = false;
                        
                        // Clear input value
                        $('#import_excel_file').val('');
                        
                        if (typeof showSuccessFeedback === 'function') {
                            showSuccessFeedback();
                        } else if (typeof show_toastr === 'function') {
                            show_toastr('{{ __("Success") }}', '{{ __("Excel imported successfully!") }}', 'success');
                        }
                        
                        triggerAutoSave();
                    }
                } catch(err) {
                    console.error(err);
                    if (typeof show_toastr === 'function') {
                        show_toastr('{{ __("Error") }}', '{{ __("Failed to read Excel file.") }}', 'error');
                    }
                }
            };
            reader.readAsArrayBuffer(file);
        });

        function initSocket() {
            try {
                socket = io(socketUrl, {
                    transports: ['websocket', 'polling']
                });

                socket.on('connect', function() {
                    console.log('Connected to Sheets Socket.io');
                    socket.emit('join_sheet', {
                        sheet_id: sheetId,
                        user_id: currentUserId,
                        username: currentUsername
                    });
                });

                // Render collaborator avatar lists
                socket.on('active_users', function(users) {
                    var container = $('#gs-avatar-container');
                    container.find('.gs-user-avatar:not(#self-avatar)').remove();

                    var colorIdx = 0;
                    $.each(users, function(index, user) {
                        if (user.user_id != currentUserId) {
                            var color = collaboratorColors[colorIdx % collaboratorColors.length];
                            colorIdx++;

                            collaboratorCursors[user.user_id] = collaboratorCursors[user.user_id] || {};
                            collaboratorCursors[user.user_id].color = color;

                            var initials = user.username.substring(0, 1).toUpperCase();
                            var avatarHtml = `
                                <div class="gs-user-avatar" id="avatar-${user.user_id}" 
                                     style="background: ${color}; border-color: ${color};" 
                                     data-bs-toggle="tooltip" title="${user.username}">
                                    ${initials}
                                </div>
                            `;
                            container.append(avatarHtml);
                        }
                    });
                    
                    // Re-initialize tooltips
                    $('[data-bs-toggle="tooltip"]').tooltip();
                });

                // Receive real-time cell edits from collaborators
                socket.on('cell_edit', function(data) {
                    if (data.sheet_id == sheetId && data.user_id != currentUserId) {
                        ignoreSocketEmit = true;
                        spreadsheet.setValueFromCoords(data.col, data.row, data.value, true);
                        ignoreSocketEmit = false;

                        // Flash cells green on update
                        var cellElement = spreadsheet.getCell(getCellNameFromCoords(data.col, data.row));
                        if (cellElement) {
                            cellElement.style.backgroundColor = 'rgba(15, 157, 88, 0.15)';
                            setTimeout(function() {
                                cellElement.style.backgroundColor = '';
                            }, 1000);
                        }
                    }
                });

                // Receive collaborator cursor moves
                socket.on('cursor_move', function(data) {
                    if (data.sheet_id == sheetId && data.user_id != currentUserId) {
                        var col = data.col;
                        var row = data.row;
                        var color = collaboratorCursors[data.user_id] ? collaboratorCursors[data.user_id].color : '#94a3b8';

                        // Remove previous borders
                        var prevCell = collaboratorCursors[data.user_id] ? collaboratorCursors[data.user_id].cellName : null;
                        if (prevCell) {
                            var prevCellEl = spreadsheet.getCell(prevCell);
                            if (prevCellEl) {
                                prevCellEl.style.outline = '';
                                prevCellEl.style.boxShadow = '';
                                prevCellEl.removeAttribute('title');
                            }
                        }

                        // Outline focused cell
                        var cellName = getCellNameFromCoords(col, row);
                        var cellEl = spreadsheet.getCell(cellName);
                        if (cellEl) {
                            cellEl.style.outline = '2px solid ' + color;
                            cellEl.style.outlineOffset = '-2px';
                            cellEl.setAttribute('title', data.username + ' is editing this');
                            collaboratorCursors[data.user_id].cellName = cellName;
                        }
                    }
                });

                // Remove disconnected cursors
                socket.on('user_disconnected', function(userId) {
                    var cellName = collaboratorCursors[userId] ? collaboratorCursors[userId].cellName : null;
                    if (cellName) {
                        var cellEl = spreadsheet.getCell(cellName);
                        if (cellEl) {
                            cellEl.style.outline = '';
                            cellEl.removeAttribute('title');
                        }
                    }
                    delete collaboratorCursors[userId];
                    $('#avatar-' + userId).remove();
                });

            } catch (e) {
                console.warn('Real-time connection to Sheets WebSocket server failed.', e);
            }
        }

        function triggerAutoSave() {
            $('#save-status').html('<i class="ti ti-cloud-upload me-1"></i> Saving...').removeClass('bg-light-success text-success').addClass('bg-light-warning text-warning');
            
            if (saveTimeout) clearTimeout(saveTimeout);
            saveTimeout = setTimeout(saveData, 1500);
        }

        function saveData() {
            var dataPayload = spreadsheet.getData();
            
            $.ajax({
                url: '{{ route('crm.sheets.update-data', $sheet->id) }}',
                type: 'POST',
                data: {
                    data: dataPayload,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if (res.success) {
                        $('#save-status').html('<i class="ti ti-cloud-check me-1"></i> Saved to CRM').removeClass('bg-light-warning text-warning').addClass('bg-light-success text-success');
                    } else {
                        $('#save-status').html('<i class="ti ti-alert-triangle me-1"></i> Save error').removeClass('bg-light-warning text-warning').addClass('bg-light-danger text-danger');
                    }
                },
                error: function(err) {
                    console.error('Save failed:', err);
                    $('#save-status').html('<i class="ti ti-alert-triangle me-1"></i> Offline / Save failed').removeClass('bg-light-warning text-warning').addClass('bg-light-danger text-danger');
                }
            });
        }
    </script>
@endsection
