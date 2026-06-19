@extends('layouts.main')

@section('page-title')
    {{ __('Collaborative Spreadsheets') }}
@endsection

@section('page-action')
    <div class="float-end">
        <a href="#" data-size="md" data-url="{{ route('crm.sheets.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Create Sheet') }}" data-title="{{ __('Create Spreadsheet') }}" class="btn btn-sm btn-primary" style="border-radius: 8px;">
            <i class="ti ti-plus"></i> {{ __('New Sheet') }}
        </a>
    </div>
@endsection

@section('content')
    @include('lead::layouts.anti_screenshot')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        
        :root {
            --emerald-solid: #10b981;
            --emerald-glow: rgba(16, 185, 129, 0.12);
            --indigo-solid: #6366f1;
            --indigo-glow: rgba(99, 102, 241, 0.12);
            --cyan-solid: #06b6d4;
            --cyan-glow: rgba(6, 182, 212, 0.12);
            --rose-solid: #f43f5e;
            --rose-glow: rgba(244, 63, 94, 0.12);
            --slate-border: #edf2f7;
            --slate-bg-light: #f8fafc;
            --slate-text-dark: #0f172a;
            --slate-text-muted: #64748b;
        }

        .sheet-premium-wrap {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            color: var(--slate-text-dark);
        }

        /* Stats Cards Upgrade */
        .stats-card-premium {
            border: 1px solid rgba(226, 232, 240, 0.7) !important;
            border-radius: 18px !important;
            background: #ffffff !important;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.02), 0 2px 6px -1px rgba(0, 0, 0, 0.02) !important;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
            position: relative;
            overflow: hidden;
        }
        .stats-card-premium::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: transparent;
            transition: background 0.35s ease;
        }
        
        .stats-card-premium-total::before { background: var(--emerald-solid); opacity: 0; }
        .stats-card-premium-me::before { background: var(--indigo-solid); opacity: 0; }
        .stats-card-premium-shared::before { background: var(--cyan-solid); opacity: 0; }
        .stats-card-premium-pending::before { background: var(--rose-solid); opacity: 0; }

        .stats-card-premium-total:hover {
            transform: translateY(-5px) !important;
            box-shadow: 0 12px 24px -6px var(--emerald-glow) !important;
            border-color: rgba(16, 185, 129, 0.3) !important;
        }
        .stats-card-premium-total:hover::before { opacity: 1; }

        .stats-card-premium-me:hover {
            transform: translateY(-5px) !important;
            box-shadow: 0 12px 24px -6px var(--indigo-glow) !important;
            border-color: rgba(99, 102, 241, 0.3) !important;
        }
        .stats-card-premium-me:hover::before { opacity: 1; }

        .stats-card-premium-shared:hover {
            transform: translateY(-5px) !important;
            box-shadow: 0 12px 24px -6px var(--cyan-glow) !important;
            border-color: rgba(6, 182, 212, 0.3) !important;
        }
        .stats-card-premium-shared:hover::before { opacity: 1; }

        .stats-card-premium-pending:hover {
            transform: translateY(-5px) !important;
            box-shadow: 0 12px 24px -6px var(--rose-glow) !important;
            border-color: rgba(244, 63, 94, 0.3) !important;
        }
        .stats-card-premium-pending:hover::before { opacity: 1; }

        .stats-icon-wrap {
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            font-size: 1.5rem;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .stats-card-premium:hover .stats-icon-wrap {
            transform: scale(1.12) rotate(5deg);
        }

        /* Modern Tabs (Segmented pills) */
        .sheet-tab-card {
            border: 1px solid rgba(226, 232, 240, 0.7) !important;
            border-radius: 20px !important;
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.03) !important;
            background: #ffffff !important;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .nav-tabs-custom {
            border-bottom: 1px solid #e2e8f0 !important;
            background: #f8fafc !important;
            padding: 1rem 1.5rem 0 1.5rem !important;
            gap: 0.35rem;
        }
        .nav-tabs-custom .nav-link {
            border: none !important;
            color: var(--slate-text-muted) !important;
            font-weight: 600 !important;
            padding: 0.8rem 1.35rem !important;
            border-bottom: 3px solid transparent !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.6rem !important;
            border-radius: 10px 10px 0 0 !important;
            font-size: 0.9rem !important;
            position: relative;
            background: transparent !important;
        }
        .nav-tabs-custom .nav-link:hover {
            color: var(--slate-text-dark) !important;
            background: rgba(241, 245, 249, 0.7) !important;
        }
        .nav-tabs-custom .nav-link.active {
            color: var(--emerald-solid) !important;
            background: #ffffff !important;
            border-bottom: 3px solid var(--emerald-solid) !important;
            font-weight: 700 !important;
        }
        #shared-sheets-tab.active {
            color: var(--indigo-solid) !important;
            border-bottom-color: var(--indigo-solid) !important;
        }
        #dept-sheets-tab.active {
            color: var(--cyan-solid) !important;
            border-bottom-color: var(--cyan-solid) !important;
        }
        #pending-invites-tab.active {
            color: var(--rose-solid) !important;
            border-bottom-color: var(--rose-solid) !important;
        }

        /* Filter Controls */
        .input-group-premium {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            transition: all 0.25s ease;
            overflow: hidden;
        }
        .input-group-premium:focus-within {
            border-color: var(--emerald-solid);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
        }
        .input-group-premium .form-control {
            border: none !important;
            box-shadow: none !important;
            font-size: 0.875rem;
            height: 40px;
        }
        .form-select-premium {
            border-radius: 12px !important;
            border-color: #cbd5e1 !important;
            font-size: 0.875rem;
            height: 40px;
            transition: all 0.25s ease !important;
            padding-left: 1rem;
            cursor: pointer;
        }
        .form-select-premium:focus {
            border-color: var(--emerald-solid) !important;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12) !important;
        }

        /* Table Design */
        .table-responsive {
            border-radius: 0 0 20px 20px;
        }
        table.align-middle thead th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            color: var(--slate-text-muted) !important;
            font-weight: 700;
            border-bottom: 2px solid #edf2f7;
            padding: 1rem 1.5rem;
            background: #f8fafc;
        }

        /* Table Row & Left Accent Line */
        .sheet-row-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border-bottom: 1px solid #edf2f7 !important;
            position: relative;
        }
        .sheet-row-card td {
            padding: 1.2rem 1.5rem !important;
            transition: background-color 0.3s ease;
        }
        .sheet-row-card:hover {
            background-color: #f8fafc !important;
            transform: scale(1.002);
        }
        /* Specific accent colors on hover per tab/type */
        #my-sheets .sheet-row-card:hover {
            box-shadow: inset 4px 0 0 0 var(--emerald-solid);
        }
        #shared-sheets .sheet-row-card:hover {
            box-shadow: inset 4px 0 0 0 var(--indigo-solid);
        }
        #dept-sheets .sheet-row-card:hover {
            box-shadow: inset 4px 0 0 0 var(--cyan-solid);
        }
        #pending-invites .sheet-row-card:hover {
            box-shadow: inset 4px 0 0 0 var(--rose-solid);
        }

        /* Sheet Avatar & Floating rotation */
        .sheet-avatar-circle {
            width: 44px !important;
            height: 44px !important;
            border-radius: 12px !important;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(5, 150, 105, 0.12) 100%) !important;
            color: var(--emerald-solid) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-weight: 700 !important;
            font-size: 1.35rem !important;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.05);
        }
        #shared-sheets .sheet-avatar-circle {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(79, 70, 229, 0.12) 100%) !important;
            color: var(--indigo-solid) !important;
        }
        #dept-sheets .sheet-avatar-circle {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.08) 0%, rgba(8, 145, 178, 0.12) 100%) !important;
            color: var(--cyan-solid) !important;
        }
        #pending-invites .sheet-avatar-circle {
            background: linear-gradient(135deg, rgba(244, 63, 94, 0.08) 0%, rgba(225, 29, 72, 0.12) 100%) !important;
            color: var(--rose-solid) !important;
        }

        .sheet-row-card:hover .sheet-avatar-circle {
            transform: scale(1.1) rotate(6deg) !important;
        }

        /* Avatar styling & Initials */
        .avatar-owner-wrapper {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }
        .avatar-circle-me, .avatar-circle-other, .avatar-circle-manager {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            color: #ffffff;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        .avatar-circle-me {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .avatar-circle-other {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        }
        .avatar-circle-manager {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }
        .sheet-row-card:hover .avatar-circle-me,
        .sheet-row-card:hover .avatar-circle-other,
        .sheet-row-card:hover .avatar-circle-manager {
            transform: scale(1.08);
            box-shadow: 0 6px 14px rgba(0,0,0,0.12);
        }

        /* Pill Badge Styling for Creator / Teammate / Subordinate / Host */
        .badge-premium {
            font-size: 0.72rem !important;
            font-weight: 700 !important;
            padding: 0.25rem 0.65rem !important;
            border-radius: 8px !important;
            display: inline-block;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }
        .badge-premium-creator {
            background: rgba(16, 185, 129, 0.08) !important;
            color: #059669 !important;
            border: 1px solid rgba(16, 185, 129, 0.15) !important;
        }
        .badge-premium-teammate {
            background: rgba(99, 102, 241, 0.08) !important;
            color: #4f46e5 !important;
            border: 1px solid rgba(99, 102, 241, 0.15) !important;
        }
        .badge-premium-subordinate {
            background: rgba(6, 182, 212, 0.08) !important;
            color: #0891b2 !important;
            border: 1px solid rgba(6, 182, 212, 0.15) !important;
        }
        .badge-premium-host {
            background: rgba(244, 63, 94, 0.08) !important;
            color: #e11d48 !important;
            border: 1px solid rgba(244, 63, 94, 0.15) !important;
        }

        /* Glassmorphic Action Buttons */
        .action-btn-glass {
            width: 36px;
            height: 36px;
            border-radius: 10px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: 1px solid transparent !important;
            background: transparent !important;
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
            cursor: pointer !important;
        }
        .action-btn-glass i {
            font-size: 1.25rem !important;
        }
        .action-btn-download {
            color: #3b82f6 !important;
            background: rgba(59, 130, 246, 0.06) !important;
            border-color: rgba(59, 130, 246, 0.08) !important;
        }
        .action-btn-download:hover {
            color: #ffffff !important;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important;
            transform: translateY(-2px) scale(1.06) !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25) !important;
        }
        .action-btn-share {
            color: #6366f1 !important;
            background: rgba(99, 102, 241, 0.06) !important;
            border-color: rgba(99, 102, 241, 0.08) !important;
        }
        .action-btn-share:hover {
            color: #ffffff !important;
            background: linear-gradient(135deg, #6366f1, #4338ca) !important;
            transform: translateY(-2px) scale(1.06) !important;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25) !important;
        }
        .action-btn-delete {
            color: #ef4444 !important;
            background: rgba(239, 68, 68, 0.06) !important;
            border-color: rgba(239, 68, 68, 0.08) !important;
        }
        .action-btn-delete:hover {
            color: #ffffff !important;
            background: linear-gradient(135deg, #ef4444, #b91c1c) !important;
            transform: translateY(-2px) scale(1.06) !important;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25) !important;
        }

        .badge-invite-count {
            background: var(--rose-solid) !important;
            color: white !important;
            font-size: 0.72rem !important;
            padding: 0.2rem 0.5rem !important;
            border-radius: 20px !important;
            font-weight: 700 !important;
            margin-left: 0.35rem !important;
            box-shadow: 0 2px 5px rgba(244, 63, 94, 0.3);
        }

        /* Empty states */
        .empty-state-wrap {
            padding: 4.5rem 2rem !important;
        }
        .empty-state-icon {
            width: 80px;
            height: 80px;
            background: #f1f5f9;
            color: var(--slate-text-muted);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
        }
    </style>

    <div class="sheet-premium-wrap">
        <!-- Stats Cards Row -->
        <div class="row mb-4">
            <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                <div class="card stats-card-premium stats-card-premium-total mb-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="stats-icon-wrap" style="background: rgba(16, 185, 129, 0.08); color: #10b981;">
                            <i class="ti ti-table"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold" style="color: #0f172a; font-size: 1.25rem;">{{ $mySheets->count() + $sharedSheets->count() }}</h5>
                            <span class="text-muted text-xs font-weight-semibold">{{ Auth::user()->type === 'company' || Auth::user()->type === 'super admin' ? __('Total Sheets') : __('Accessible Sheets') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                <div class="card stats-card-premium stats-card-premium-me mb-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="stats-icon-wrap" style="background: rgba(99, 102, 241, 0.08); color: #6366f1;">
                            <i class="ti ti-user"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold" style="color: #0f172a; font-size: 1.25rem;">{{ $mySheets->where('created_by', Auth::id())->count() }}</h5>
                            <span class="text-muted text-xs font-weight-semibold">{{ __('Created by Me') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                <div class="card stats-card-premium stats-card-premium-shared mb-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="stats-icon-wrap" style="background: rgba(6, 182, 212, 0.08); color: #06b6d4;">
                            <i class="ti ti-share"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold" style="color: #0f172a; font-size: 1.25rem;">{{ $sharedSheets->count() }}</h5>
                            <span class="text-muted text-xs font-weight-semibold">{{ __('Shared with Me') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card stats-card-premium stats-card-premium-pending mb-0">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="stats-icon-wrap" style="background: rgba(239, 68, 68, 0.08); color: #ef4444;">
                            <i class="ti ti-mail-opened"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold" style="color: #0f172a; font-size: 1.25rem;">{{ $pendingInvites->count() }}</h5>
                            <span class="text-muted text-xs font-weight-semibold">{{ __('Pending Invites') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card sheet-tab-card">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs nav-tabs-custom" id="sheetTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="my-sheets-tab" data-bs-toggle="tab" data-bs-target="#my-sheets" type="button" role="tab" aria-controls="my-sheets" aria-selected="true">
                                <i class="ti ti-table"></i> {{ Auth::user()->type === 'company' || Auth::user()->type === 'super admin' ? __('All Sheets') : __('My Sheets') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="shared-sheets-tab" data-bs-toggle="tab" data-bs-target="#shared-sheets" type="button" role="tab" aria-controls="shared-sheets" aria-selected="false">
                                <i class="ti ti-share"></i> {{ __('Shared with Me') }}
                            </button>
                        </li>
                        @if($isManager)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="dept-sheets-tab" data-bs-toggle="tab" data-bs-target="#dept-sheets" type="button" role="tab" aria-controls="dept-sheets" aria-selected="false">
                                    <i class="ti ti-users"></i> {{ __('Department Sheets') }}
                                </button>
                            </li>
                        @endif
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pending-invites-tab" data-bs-toggle="tab" data-bs-target="#pending-invites" type="button" role="tab" aria-controls="pending-invites" aria-selected="false">
                                <i class="ti ti-mail"></i> {{ __('Pending Invites') }}
                                @if($pendingInvites->isNotEmpty())
                                    <span class="badge-invite-count">{{ $pendingInvites->count() }}</span>
                                @endif
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="card-body p-0">
                        <div class="tab-content" id="sheetTabsContent">
                            
                            <!-- Tab 1: My Sheets / All Sheets -->
                            <div class="tab-pane fade show active" id="my-sheets" role="tabpanel" aria-labelledby="my-sheets-tab">
                                <!-- Premium Filter Controls Bar -->
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-3" style="border-bottom: 1px solid var(--slate-border); background: var(--slate-bg-light);">
                                    <div class="d-flex align-items-center gap-3 flex-grow-1" style="min-width: 280px;">
                                        <div class="input-group input-group-premium" style="max-width: 320px;">
                                            <span class="input-group-text bg-white border-0" style="padding-left: 1rem; padding-right: 0.5rem;">
                                                <i class="ti ti-search text-muted" style="font-size: 1.15rem;"></i>
                                            </span>
                                            <input type="text" id="sheet-search-input" class="form-control" placeholder="{{ __('Search by sheet name...') }}">
                                        </div>
                                        
                                        @if(Auth::user()->type === 'company' || Auth::user()->type === 'super admin')
                                            <select id="sheet-owner-filter" class="form-select form-select-premium" style="max-width: 200px;">
                                                <option value="all">{{ __('All Owners') }}</option>
                                                <option value="me">{{ __('Created by Me') }}</option>
                                                @foreach($creators as $id => $name)
                                                    <option value="{{ $id }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-light-primary text-primary px-3 py-2 font-weight-bold" style="border-radius: 8px; font-size: 0.8rem;" id="sheets-counter">
                                            {{ __('Showing') }} <span id="visible-sheets-count">{{ $mySheets->count() }}</span> {{ __('of') }} <span id="total-sheets-count">{{ $mySheets->count() }}</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table mb-0 align-middle">
                                        <thead>
                                            <tr class="text-muted font-weight-bold" style="border-bottom: 2px solid #f1f5f9; background: #fafbfd; font-size: 0.85rem;">
                                                <th style="padding: 1rem 1.5rem;">{{ __('Sheet Name') }}</th>
                                                <th>{{ __('Owner') }}</th>
                                                <th>{{ __('Last Updated') }}</th>
                                                <th width="160px" class="text-end" style="padding-right: 1.5rem;">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($mySheets as $sheet)
                                                <tr class="sheet-row-card" data-name="{{ strtolower($sheet->name) }}" data-creator-id="{{ $sheet->created_by }}" data-creator-type="{{ $sheet->created_by === Auth::id() ? 'me' : 'other' }}">
                                                    <td style="padding: 1rem 1.5rem;">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="sheet-avatar-circle">
                                                                <i class="ti ti-spreadsheet"></i>
                                                            </div>
                                                            <div>
                                                                <a href="{{ route('crm.sheets.view', $sheet->id) }}" class="font-weight-bold text-dark d-block h6 mb-0" style="transition: color 0.2s; text-decoration: none;" onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='#0f172a'">{{ $sheet->name }}</a>
                                                                <small class="text-muted" style="font-size: 0.75rem;">{{ __('Created') }}: {{ $sheet->created_at->format('M d, Y') }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="avatar-owner-wrapper">
                                                            @if($sheet->created_by === Auth::id())
                                                                <div class="avatar-circle-me" title="{{ __('You') }}">
                                                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                                                </div>
                                                                <div>
                                                                    <span class="font-weight-bold text-dark d-block text-sm">{{ __('You') }}</span>
                                                                    <span class="badge-premium badge-premium-creator">{{ __('Creator') }}</span>
                                                                </div>
                                                            @else
                                                                <div class="avatar-circle-other" title="{{ $sheet->creator ? $sheet->creator->name : __('Teammate') }}">
                                                                    {{ strtoupper(substr($sheet->creator ? $sheet->creator->name : '?', 0, 1)) }}
                                                                </div>
                                                                <div>
                                                                    <span class="font-weight-bold text-dark d-block text-sm">{{ $sheet->creator ? $sheet->creator->name : '-' }}</span>
                                                                    <span class="badge-premium badge-premium-teammate">{{ __('Teammate') }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="text-secondary font-weight-semibold" style="font-size: 0.85rem;">{{ $sheet->updated_at->diffForHumans() }}</span>
                                                    </td>
                                                    <td class="text-end" style="padding-right: 1.5rem;">
                                                        <div class="d-inline-flex gap-2">
                                                            @if(Auth::user()->type == 'company')
                                                                <a href="{{ route('crm.sheets.export', $sheet->id) }}" class="action-btn-glass action-btn-download" 
                                                                   data-bs-toggle="tooltip" title="{{ __('Export to Excel') }}">
                                                                    <i class="ti ti-download"></i>
                                                                </a>
                                                            @endif

                                                            <a href="#" class="action-btn-glass action-btn-share" 
                                                               data-url="{{ route('crm.sheets.share', $sheet->id) }}" 
                                                               data-ajax-popup="true" data-size="md" 
                                                               data-bs-toggle="tooltip" title="{{ __('Share Sheet') }}" 
                                                               data-title="{{ __('Invite Teammates') }}">
                                                                <i class="ti ti-share"></i>
                                                            </a>

                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['crm.sheets.destroy', $sheet->id], 'id' => 'delete-form-' . $sheet->id, 'class' => 'd-inline']) !!}
                                                                <a href="#" class="action-btn-glass action-btn-delete bs-pass-para show_confirm" 
                                                                   data-bs-toggle="tooltip" title="{{ __('Delete Spreadsheet') }}">
                                                                    <i class="ti ti-trash"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center empty-state-wrap text-muted">
                                                        <div class="empty-state-icon">
                                                            <i class="ti ti-table-alias"></i>
                                                        </div>
                                                        <h5 class="font-weight-bold text-dark mb-1">{{ __('No Spreadsheets Found') }}</h5>
                                                        <p class="text-xs mb-0">{{ __('Get started by creating your first collaborative spreadsheet.') }}</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab 2: Shared with Me -->
                            <div class="tab-pane fade" id="shared-sheets" role="tabpanel" aria-labelledby="shared-sheets-tab">
                                <div class="table-responsive">
                                    <table class="table mb-0 align-middle">
                                        <thead>
                                            <tr class="text-muted font-weight-bold" style="border-bottom: 2px solid #f1f5f9; background: #fafbfd; font-size: 0.85rem;">
                                                <th style="padding: 1rem 1.5rem;">{{ __('Sheet Name') }}</th>
                                                <th>{{ __('Shared By') }}</th>
                                                <th>{{ __('Last Updated') }}</th>
                                                <th width="160px" class="text-end" style="padding-right: 1.5rem;">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($sharedSheets as $sheet)
                                                <tr class="sheet-row-card">
                                                    <td style="padding: 1rem 1.5rem;">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="sheet-avatar-circle" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;">
                                                                <i class="ti ti-spreadsheet"></i>
                                                            </div>
                                                            <div>
                                                                <a href="{{ route('crm.sheets.view', $sheet->id) }}" class="font-weight-bold text-dark d-block h6 mb-0" style="transition: color 0.2s; text-decoration: none;" onmouseover="this.style.color='#6366f1'" onmouseout="this.style.color='#0f172a'">{{ $sheet->name }}</a>
                                                                <small class="text-muted" style="font-size: 0.75rem;">{{ __('Created') }}: {{ $sheet->created_at->format('M d, Y') }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="avatar-owner-wrapper">
                                                            <div class="avatar-circle-other" title="{{ $sheet->creator ? $sheet->creator->name : '-' }}">
                                                                {{ strtoupper(substr($sheet->creator ? $sheet->creator->name : '?', 0, 1)) }}
                                                            </div>
                                                            <div>
                                                                <span class="font-weight-bold text-dark d-block text-sm">{{ $sheet->creator ? $sheet->creator->name : '-' }}</span>
                                                                <span class="badge-premium badge-premium-teammate">{{ __('Teammate') }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="text-secondary font-weight-semibold" style="font-size: 0.85rem;">{{ $sheet->updated_at->diffForHumans() }}</span>
                                                    </td>
                                                    <td class="text-end" style="padding-right: 1.5rem;">
                                                        <div class="d-inline-flex gap-2">
                                                            @if(Auth::user()->type == 'company')
                                                                <a href="{{ route('crm.sheets.export', $sheet->id) }}" class="action-btn-glass action-btn-download" 
                                                                   data-bs-toggle="tooltip" title="{{ __('Export to Excel') }}">
                                                                    <i class="ti ti-download"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center empty-state-wrap text-muted">
                                                        <div class="empty-state-icon">
                                                            <i class="ti ti-share-off"></i>
                                                        </div>
                                                        <h5 class="font-weight-bold text-dark mb-1">{{ __('No Shared Sheets') }}</h5>
                                                        <p class="text-xs mb-0">{{ __('Sheets shared with you by other team members will appear here.') }}</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab 3: Department Sheets -->
                            @if($isManager)
                                <div class="tab-pane fade" id="dept-sheets" role="tabpanel" aria-labelledby="dept-sheets-tab">
                                    <div class="table-responsive">
                                        <table class="table mb-0 align-middle">
                                            <thead>
                                                <tr class="text-muted font-weight-bold" style="border-bottom: 2px solid #f1f5f9; background: #fafbfd; font-size: 0.85rem;">
                                                    <th style="padding: 1rem 1.5rem;">{{ __('Sheet Name') }}</th>
                                                    <th>{{ __('Teammate') }}</th>
                                                    <th>{{ __('Last Updated') }}</th>
                                                    <th width="160px" class="text-end" style="padding-right: 1.5rem;">{{ __('Actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($deptSheets as $sheet)
                                                    <tr class="sheet-row-card">
                                                        <td style="padding: 1rem 1.5rem;">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="sheet-avatar-circle" style="background: rgba(6, 182, 212, 0.1); color: #06b6d4;">
                                                                    <i class="ti ti-spreadsheet"></i>
                                                                </div>
                                                                <div>
                                                                    <a href="{{ route('crm.sheets.view', $sheet->id) }}" class="font-weight-bold text-dark d-block h6 mb-0" style="transition: color 0.2s; text-decoration: none;" onmouseover="this.style.color='#06b6d4'" onmouseout="this.style.color='#0f172a'">{{ $sheet->name }}</a>
                                                                    <small class="text-muted" style="font-size: 0.75rem;">{{ __('Created') }}: {{ $sheet->created_at->format('M d, Y') }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="avatar-owner-wrapper">
                                                                <div class="avatar-circle-manager" title="{{ $sheet->creator ? $sheet->creator->name : '-' }}">
                                                                    {{ strtoupper(substr($sheet->creator ? $sheet->creator->name : '?', 0, 1)) }}
                                                                </div>
                                                                <div>
                                                                    <span class="font-weight-bold text-dark d-block text-sm">{{ $sheet->creator ? $sheet->creator->name : '-' }}</span>
                                                                    <span class="badge-premium badge-premium-subordinate">{{ __('Subordinate') }}</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="text-secondary font-weight-semibold" style="font-size: 0.85rem;">{{ $sheet->updated_at->diffForHumans() }}</span>
                                                        </td>
                                                        <td class="text-end" style="padding-right: 1.5rem;">
                                                            <div class="d-inline-flex gap-2">
                                                                @if(Auth::user()->type == 'company')
                                                                    <a href="{{ route('crm.sheets.export', $sheet->id) }}" class="action-btn-glass action-btn-download" 
                                                                       data-bs-toggle="tooltip" title="{{ __('Export to Excel') }}">
                                                                        <i class="ti ti-download"></i>
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center empty-state-wrap text-muted">
                                                            <div class="empty-state-icon">
                                                                <i class="ti ti-users"></i>
                                                            </div>
                                                            <h5 class="font-weight-bold text-dark mb-1">{{ __('No Department Sheets') }}</h5>
                                                            <p class="text-xs mb-0">{{ __('Spreadsheets created by your department or team members will appear here.') }}</p>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            <!-- Tab 4: Pending Invites -->
                            <div class="tab-pane fade" id="pending-invites" role="tabpanel" aria-labelledby="pending-invites-tab">
                                <div class="table-responsive">
                                    <table class="table mb-0 align-middle">
                                        <thead>
                                            <tr class="text-muted font-weight-bold" style="border-bottom: 2px solid #f1f5f9; background: #fafbfd; font-size: 0.85rem;">
                                                <th style="padding: 1rem 1.5rem;">{{ __('Sheet Name') }}</th>
                                                <th>{{ __('Invited By') }}</th>
                                                <th>{{ __('Received At') }}</th>
                                                <th width="200px" class="text-end" style="padding-right: 1.5rem;">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($pendingInvites as $invite)
                                                <tr class="sheet-row-card">
                                                    <td style="padding: 1rem 1.5rem;">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="sheet-avatar-circle" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                                                                <i class="ti ti-mail-opened"></i>
                                                            </div>
                                                            <div>
                                                                <span class="font-weight-bold text-dark d-block h6 mb-0">{{ $invite->sheet ? $invite->sheet->name : __('Deleted Sheet') }}</span>
                                                                <small class="text-muted" style="font-size: 0.75rem;">{{ __('Invite ID') }}: {{ $invite->id }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="avatar-owner-wrapper">
                                                            <div class="avatar-circle-other" title="{{ $invite->sheet && $invite->sheet->creator ? $invite->sheet->creator->name : '-' }}">
                                                                {{ strtoupper(substr($invite->sheet && $invite->sheet->creator ? $invite->sheet->creator->name : '?', 0, 1)) }}
                                                            </div>
                                                            <div>
                                                                <span class="font-weight-bold text-dark d-block text-sm">{{ $invite->sheet && $invite->sheet->creator ? $invite->sheet->creator->name : '-' }}</span>
                                                                <span class="badge-premium badge-premium-host">{{ __('Host') }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="text-secondary font-weight-semibold" style="font-size: 0.85rem;">{{ $invite->created_at->diffForHumans() }}</span>
                                                    </td>
                                                    <td class="text-end" style="padding-right: 1.5rem;">
                                                        <div class="d-inline-flex gap-2">
                                                            <!-- Accept Form -->
                                                            {!! Form::open(['method' => 'POST', 'route' => ['crm.sheets.accept-share', $invite->id], 'class' => 'd-inline']) !!}
                                                                <button type="submit" class="btn btn-sm btn-success px-3" style="border-radius: 8px; display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600; padding: 0.45rem 1rem; box-shadow: 0 2px 4px rgba(22, 163, 74, 0.15); transition: all 0.2s;">
                                                                    <i class="ti ti-check"></i> {{ __('Accept') }}
                                                                </button>
                                                            {!! Form::close() !!}

                                                            <!-- Decline Form -->
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['crm.sheets.decline-share', $invite->id], 'class' => 'd-inline']) !!}
                                                                <button type="submit" class="btn btn-sm btn-light-danger px-3" style="border-radius: 8px; display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600; padding: 0.45rem 1rem; color: #dc2626; background: rgba(220, 38, 38, 0.05); border: 1px solid rgba(220, 38, 38, 0.1); transition: all 0.2s;" onmouseover="this.style.background='#dc2626'; this.style.color='#ffffff'" onmouseout="this.style.background='rgba(220, 38, 38, 0.05)'; this.style.color='#dc2626'">
                                                                    <i class="ti ti-x"></i> {{ __('Decline') }}
                                                                </button>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center empty-state-wrap text-muted">
                                                        <div class="empty-state-icon">
                                                            <i class="ti ti-mail-opened"></i>
                                                        </div>
                                                        <h5 class="font-weight-bold text-dark mb-1">{{ __('No Pending Invitations') }}</h5>
                                                        <p class="text-xs mb-0">{{ __('Teammates sharing their spreadsheets with you will trigger invites here.') }}</p>
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
            </div>
        </div>
    </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Client-side Interactive Search and Owner Filter Script -->
    <script>
        $(document).ready(function() {
            function filterSheets() {
                var searchQuery = $('#sheet-search-input').val().toLowerCase().trim();
                var ownerFilter = $('#sheet-owner-filter').val() || 'all';
                var visibleCount = 0;
                var totalCount = 0;

                $('#my-sheets table tbody tr.sheet-row-card').each(function() {
                    var rowName = $(this).data('name') || '';
                    var rowCreatorId = $(this).data('creator-id') || '';
                    var rowCreatorType = $(this).data('creator-type') || '';
                    
                    // Exclude helper/empty rows from calculations
                    if ($(this).attr('id') === 'sheets-empty-row') {
                        return;
                    }
                    totalCount++;

                    var matchesSearch = rowName.includes(searchQuery);
                    var matchesOwner = true;

                    if (ownerFilter === 'me') {
                        matchesOwner = (rowCreatorType === 'me');
                    } else if (ownerFilter !== 'all') {
                        matchesOwner = (rowCreatorId.toString() === ownerFilter);
                    }

                    if (matchesSearch && matchesOwner) {
                        $(this).show();
                        visibleCount++;
                    } else {
                        $(this).hide();
                    }
                });

                $('#visible-sheets-count').text(visibleCount);
                $('#total-sheets-count').text(totalCount);

                // Show/hide empty state if no sheets match
                if (visibleCount === 0 && totalCount > 0) {
                    if ($('#sheets-empty-row').length === 0) {
                        $('#my-sheets table tbody').append(`
                            <tr id="sheets-empty-row">
                                <td colspan="4" class="text-center empty-state-wrap text-muted">
                                    <div class="empty-state-icon">
                                        <i class="ti ti-search"></i>
                                    </div>
                                    <h5 class="font-weight-bold text-dark mb-1">${"{{ __('No matching sheets found') }}"}</h5>
                                    <p class="text-xs mb-0">${"{{ __('Try adjusting your search or owner filters.') }}"}</p>
                                </td>
                            </tr>
                        `);
                    }
                } else {
                    $('#sheets-empty-row').remove();
                }
            }

            $('#sheet-search-input').on('keyup input', filterSheets);
            $('#sheet-owner-filter').on('change', filterSheets);
        });
    </script>
@endsection
