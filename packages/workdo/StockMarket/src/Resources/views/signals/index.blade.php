@extends('layouts.main')

@section('page-title')
    {{ __('Stock Signals / Calls') }}
@endsection

@section('page-breadcrumb')
    {{ __('Stock Market') }}, {{ __('Signals') }}
@endsection

@push('css')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Space Grotesk', sans-serif;
            background: #f8fafc;
        }

        /* ── Premium Drawer ────────────────────────── */
        .signal-drawer-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            z-index: 2000;
            backdrop-filter: blur(8px);
            transition: all 0.3s;
        }

        .signal-drawer-overlay.open {
            display: block;
        }

        .signal-drawer {
            position: fixed;
            top: 0;
            right: -520px;
            width: 520px;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            z-index: 2010;
            box-shadow: -20px 0 60px rgba(0, 0, 0, 0.1);
            transition: right 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            border-left: 1px solid rgba(0, 0, 0, 0.05);
        }

        .signal-drawer.open {
            right: 0;
        }

        .drawer-header-modern {
            padding: 24px 28px;
            background: linear-gradient(90deg, #f8fafc 0%, #ffffff 100%);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .drawer-close-btn {
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            transition: all 0.2s;
        }

        .drawer-close-btn:hover {
            background: #e2e8f0;
            color: #1e293b;
            transform: rotate(90deg);
        }

        /* ── Premium Tabs ────────────────────────── */
        .stock-tab-bar-modern {
            background: #f1f5f9;
            border-radius: 16px;
            padding: 4px;
            display: inline-flex;
            gap: 2px;
            border: 1px solid #e2e8f0;
        }

        .stock-tab-bar-modern a {
            padding: 10px 24px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.8rem;
            color: #64748b;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .stock-tab-bar-modern a.active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        .stock-tab-bar-modern a.active.live {
            color: #10b981;
        }

        .stock-tab-bar-modern a.active.closed {
            color: #f43f5e;
        }

        /* ── Filter Pills ────────────────────────── */
        .filter-pill-modern {
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 8px 18px;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s;
            background: #fff;
            text-decoration: none;
            text-transform: uppercase;
        }

        .filter-pill-modern.active,
        .filter-pill-modern:hover {
            background: #0f172a;
            color: #fff;
            border-color: #0f172a;
            transform: scale(1.05);
        }

        /* ── Analytics Performance Card ───────────── */
        .analytics-hero-card {
            background: #0f172a;
            background-image: radial-gradient(circle at 100% 0%, #1e293b 0%, #0f172a 70%);
            border-radius: 24px;
            padding: 32px;
            color: #fff;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.25);
        }

        .analytics-hero-card::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(16, 185, 129, 0.05);
            filter: blur(80px);
            border-radius: 50%;
        }

        .hero-metric-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.1em;
            margin-bottom: 8px;
        }

        .hero-metric-value {
            font-size: 3rem;
            font-weight: 800;
            color: #10b981;
            line-height: 1;
        }

        .hero-stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-top: 24px;
        }

        .hero-stat-box {
            background: rgba(255, 255, 255, 0.05);
            padding: 16px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .hero-chart-container {
            height: 120px;
            width: 100%;
            margin-top: 20px;
        }

        /* ── Closed Group Animation ───────────────── */
        .closed-group-modern {
            background: #fff;
            border-radius: 20px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            margin-bottom: 16px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .closed-group-header {
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            user-select: none;
        }

        .closed-group-header:hover {
            background: #f8fafc;
        }

        .closed-group-body {
            max-height: 0;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0, 1, 0, 1);
            padding: 0 24px;
        }

        .closed-group-body.open {
            max-height: 2000px;
            transition: all 0.5s cubic-bezier(1, 0, 1, 0);
            padding-bottom: 24px;
        }

        .terminal-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.2s;
        }

        .terminal-row:hover {
            padding-left: 8px;
            border-bottom-color: #e2e8f0;
        }

        .terminal-row:last-child {
            border-bottom: none;
        }

        .terminal-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e293b;
        }

        .terminal-sub {
            font-size: 0.7rem;
            color: #94a3b8;
            font-weight: 600;
        }

        .terminal-val {
            font-size: 0.9rem;
            font-weight: 800;
        }

        /* ── Adj timeline ────────────────────────── */
        .adj-item {
            position: relative;
            padding-left: 28px;
            margin-bottom: 24px;
        }

        .adj-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 4px;
            width: 12px;
            height: 12px;
            background: #10b981;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 0 1px #10b981;
        }

        .adj-item::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 20px;
            bottom: -20px;
            width: 2px;
            background: #f1f5f9;
        }

        .adj-item:last-child::after {
            display: none;
        }

        /* ── Premium Detail Modal ────────────────── */
        .modal-premium {
            border-radius: 28px;
            border: none;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal-premium .modal-content {
            border: none;
            border-radius: 28px;
        }

        .modal-premium .modal-header {
            padding: 30px 40px 10px;
            border: none;
        }

        .modal-premium .modal-body {
            padding: 0 40px 40px;
        }

        .analyst-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .analyst-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .analyst-info h6 {
            margin: 0;
            font-weight: 700;
            color: #1e293b;
        }

        .analyst-info span {
            font-size: 0.75rem;
            color: #64748b;
        }

        .journey-container {
            position: relative;
            padding: 40px 0 20px;
        }

        .journey-track {
            height: 12px;
            background: #f1f5f9;
            border-radius: 10px;
            position: relative;
            overflow: visible;
        }

        .journey-progress {
            position: absolute;
            height: 100%;
            background: #10b981;
            border-radius: 10px;
            transition: width 1s ease;
        }

        .journey-marker {
            position: absolute;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 4px;
            height: 24px;
            background: #0f172a;
            z-index: 2;
        }

        .journey-marker.sl {
            background: #f43f5e;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .journey-marker.target {
            background: #10b981;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .node-info {
            position: absolute;
            top: 30px;
            transform: translateX(-50%);
            text-align: center;
            min-width: 80px;
        }

        .node-label {
            display: block;
            font-size: 0.65rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
        }

        .node-price {
            display: block;
            font-size: 0.85rem;
            font-weight: 800;
            color: #1e293b;
        }

        .circle-indicator {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            position: relative;
            border: 8px solid #f1f5f9;
        }

        .circle-indicator.complete {
            border-color: #10b981;
        }

        .circle-val {
            font-size: 1.2rem;
            font-weight: 800;
            color: #10b981;
        }

        .circle-label {
            font-size: 0.5rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
        }

        .upside-box,
        .downside-box {
            padding: 12px 20px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex: 1;
        }

        .upside-box {
            background: #f0fdf4;
            border: 1px solid #dcfce7;
        }

        .downside-box {
            background: #fef2f2;
            border: 1px solid #fee2e2;
        }

        .box-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #64748b;
        }

        .box-val {
            font-size: 0.85rem;
            font-weight: 800;
        }

        .upside-box .box-val {
            color: #10b981;
        }

        .downside-box .box-val {
            color: #f43f5e;
        }
    </style>
@endpush

@section('content')
    <div class="row g-4">
        <div class="col-12">
            {{-- Modern Navbar --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-4 mb-4">
                <div class="stock-tab-bar-modern">
                    <a href="{{ route('stock-signals.index', ['tab' => 'live']) }}"
                        class="live {{ $tab === 'live' ? 'active' : '' }}">
                        <i class="ti ti-activity me-1"></i> Live calls
                    </a>
                    <a href="{{ route('stock-signals.index', ['tab' => 'closed']) }}"
                        class="closed {{ $tab === 'closed' ? 'active' : '' }}">
                        <i class="ti ti-archive me-1"></i> Closed
                    </a>
                    <a href="{{ route('stock-signals.index', ['tab' => 'all']) }}"
                        class="{{ $tab === 'all' ? 'active' : '' }}">
                        All
                    </a>
                </div>

                <div class="d-flex align-items-center gap-2">
                    @if(\Auth::user()->type == 'company' || \Auth::user()->isAbleTo('signal edit'))
                        <form action="{{ route('stock-signals.auto-close-intraday') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm px-3 rounded-pill fw-bold"
                                onclick="return confirm('Square off all Intraday positions at CMP?')">
                                <i class="ti ti-lock me-1"></i> SQUARE OFF INTRADAY
                            </button>
                        </form>
                    @endif
                    @if(\Auth::user()->type == 'company' || \Auth::user()->isAbleTo('signal create'))
                        <a href="{{ route('stock-signals.create') }}" class="btn btn-dark px-4 py-2"
                            style="border-radius: 12px; font-weight: 700;">
                            <i class="ti ti-plus me-1"></i> PUBLISH SIGNAL
                        </a>
                    @endif
                </div>
            </div>

            {{-- Horizon & Category Filters --}}
            <div class="d-flex gap-2 mb-4 flex-wrap">
                <a href="{{ request()->fullUrlWithQuery(['hold_type' => '']) }}"
                    class="filter-pill-modern {{ !request('hold_type') ? 'active' : '' }}">All horizons</a>
                <a href="{{ request()->fullUrlWithQuery(['hold_type' => 'intraday']) }}"
                    class="filter-pill-modern {{ request('hold_type') === 'intraday' ? 'active' : '' }}">Intraday</a>
                <a href="{{ request()->fullUrlWithQuery(['hold_type' => 'longterm']) }}"
                    class="filter-pill-modern {{ request('hold_type') === 'longterm' ? 'active' : '' }}">Longterm</a>
                <div class="vr mx-2 opacity-10"></div>
                @foreach($categories as $cat)
                    <a href="{{ request()->fullUrlWithQuery(['category' => $cat->id]) }}"
                        class="filter-pill-modern {{ request('category') == $cat->id ? 'active' : '' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>

            {{-- ── LIVE TAB CONTENT ──────────────────────── --}}
            @if($tab !== 'closed')
                @if($signals->isEmpty())
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="ti ti-chart-candle text-muted opacity-20" style="font-size:5rem;"></i>
                        </div>
                        <h4 class="fw-bold text-dark">No Active Signals</h4>
                        <p class="text-muted">The markets are waiting for your next brilliant move.</p>
                        @can('signal create')
                            <a href="{{ route('stock-signals.create') }}" class="btn btn-success mt-3 px-4 rounded-pill">Create New
                                Signal</a>
                        @endcan
                    </div>
                @else
                    <div class="row g-4">
                        @foreach($signals as $signal)
                            @include('stockmarket::partials._signal_card', ['signal' => $signal])
                        @endforeach
                    </div>
                @endif

                {{-- ── CLOSED TAB CONTENT ────────────────────── --}}
            @else
                {{-- Institutional Dashboard Card --}}
                @if(!empty($performanceData) && $performanceData['total'] > 0)
                    <div class="analytics-hero-card">
                        <div class="row align-items-center">
                            <div class="col-lg-4">
                                <div class="hero-metric-label">CURRENT ACCURACY</div>
                                <div class="hero-metric-value">{{ $performanceData['percent'] }}%</div>
                                <div class="mt-2 d-flex align-items-center gap-2">
                                    <span
                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-2 py-1">
                                        {{ $performanceData['positive'] }} Successful
                                    </span>
                                    <span class="text-muted small">Out of {{ $performanceData['total'] }} calls</span>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="hero-stats-grid">
                                    <div class="hero-stat-box text-center">
                                        <div class="hero-metric-label mb-0">TOTAL CALLS</div>
                                        <div class="h3 fw-bold mb-0 text-white">{{ $performanceData['total'] }}</div>
                                    </div>
                                    <div class="hero-stat-box text-center">
                                        <div class="hero-metric-label mb-0">AVG DURATION</div>
                                        <div class="h3 fw-bold mb-0 text-white">{{ $performanceData['avg_duration'] }}d</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="hero-chart-container">
                                    <canvas id="performanceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Monthly Statistics Table --}}
                    @if(isset($performanceData['monthly']) && count($performanceData['monthly']) > 0)
                        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 20px; overflow: hidden;">
                            <div class="card-header bg-white border-0 py-3">
                                <h6 class="mb-0 fw-bold text-dark"><i class="ti ti-calendar-stats me-2 text-primary"></i>Monthly
                                    Hit/Miss Ratio</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4"
                                                    style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">
                                                    Month</th>
                                                <th class="text-center"
                                                    style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">
                                                    Total Calls</th>
                                                <th class="text-center"
                                                    style="font-size: 0.75rem; font-weight: 700; color: #10b981; text-transform: uppercase;">
                                                    Target Hit</th>
                                                <th class="text-center"
                                                    style="font-size: 0.75rem; font-weight: 700; color: #f43f5e; text-transform: uppercase;">
                                                    Stoploss Hit</th>
                                                <th class="pe-4 text-end"
                                                    style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">
                                                    Win Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($performanceData['monthly'] as $mStat)
                                                @php
                                                    $winRate = $mStat->total > 0 ? round(($mStat->target_hits / $mStat->total) * 100, 1) : 0;
                                                @endphp
                                                <tr>
                                                    <td class="ps-4 fw-bold text-dark">{{ $mStat->month }} {{ $mStat->year }}</td>
                                                    <td class="text-center fw-semibold">{{ $mStat->total }}</td>
                                                    <td class="text-center fw-bold text-success">{{ $mStat->target_hits }}</td>
                                                    <td class="text-center fw-bold text-danger">{{ $mStat->sl_hits }}</td>
                                                    <td class="pe-4 text-end">
                                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                                            <div class="progress w-50"
                                                                style="height: 6px; border-radius: 10px; background: #f1f5f9;">
                                                                <div class="progress-bar bg-success"
                                                                    style="width: {{ $winRate }}%; border-radius: 10px;"></div>
                                                            </div>
                                                            <span class="fw-bold"
                                                                style="font-size: 0.85rem; min-width: 45px;">{{ $winRate }}%</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                {{-- Date Groups --}}
                @if(empty($closedByDate))
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="ti ti-archive text-muted opacity-20" style="font-size:5rem;"></i>
                        </div>
                        <h4 class="fw-bold">No History Yet</h4>
                        <p class="text-muted">Finish your first signal to see historical performance here.</p>
                    </div>
                @else
                    <div class="row">
                        <div class="col-12">
                            @foreach($closedByDate as $date => $dSignals)
                                @php
                                    $dayPnl = 0;
                                    foreach ($dSignals as $ds) {
                                        if ($ds->exit_price && $ds->buy_price_min) {
                                            $change = $ds->type === 'buy' ? ($ds->exit_price - $ds->buy_price_min) : ($ds->buy_price_min - $ds->exit_price);
                                            $dayPnl += ($change / $ds->buy_price_min) * 100;
                                        }
                                    }
                                    $dayPnl = round($dayPnl / max(count($dSignals), 1), 2);
                                @endphp
                                <div class="closed-group-modern">
                                    <div class="closed-group-header" onclick="toggleClosedGroup(this)">
                                        <div>
                                            <span class="fw-bold text-dark me-2">{{ $date }}</span>
                                            <span class="badge bg-light text-muted fw-bold rounded-pill px-2">{{ count($dSignals) }}
                                                calls</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="fw-bold"
                                                style="color:{{ $dayPnl >= 0 ? '#10b981' : '#f43f5e' }}; font-size:1.1rem;">
                                                {{ $dayPnl >= 0 ? '+' : '' }}{{ number_format($dayPnl, 2) }}%
                                            </span>
                                            <i class="ti ti-chevron-down text-muted"></i>
                                        </div>
                                    </div>
                                    <div class="closed-group-body">
                                        @foreach($dSignals as $ds)
                                            @php
                                                $ret = 0;
                                                if ($ds->exit_price && $ds->buy_price_min) {
                                                    $change = $ds->type === 'buy' ? ($ds->exit_price - $ds->buy_price_min) : ($ds->buy_price_min - $ds->exit_price);
                                                    $ret = ($change / $ds->buy_price_min) * 100;
                                                }
                                                $days = $ds->created_at->diffInDays(now());
                                            @endphp
                                            <div class="terminal-row" onclick="openSignalDrawer({{ $ds->id }})" style="cursor:pointer;">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="p-2 bg-light rounded-3">
                                                        <i class="ti ti-{{ $ret >= 0 ? 'trending-up text-success' : 'trending-down text-danger' }}"
                                                            style="font-size: 1.2rem;"></i>
                                                    </div>
                                                    <div>
                                                        <div class="terminal-label text-uppercase">{{ $ds->title }}</div>
                                                        <div class="terminal-sub">
                                                            {{ $ds->symbol }} · {{ strtoupper($ds->type) }}
                                                            ₹{{ number_format($ds->buy_price_min, 1) }} → EXIT
                                                            ₹{{ number_format($ds->exit_price ?? $ds->target, 1) }}
                                                            @if($ds->close_reason)
                                                                <span class="badge bg-light text-muted ms-1"
                                                                    style="font-size: 0.6rem;">{{ str_replace('_', ' ', strtoupper($ds->close_reason)) }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="terminal-val {{ $ret >= 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ $ret >= 0 ? '+' : '' }}{{ number_format($ret, 2) }}%
                                                    </div>
                                                    <div class="terminal-sub">{{ $days }} days ago</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- ── PREMIUM SIGNAL DETAIL MODAL ────────────────── --}}
    <div class="modal fade" id="signalDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-premium">
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex justify-content-between w-100 align-items-center">
                        <div class="analyst-profile">
                            <img src="{{ asset('assets/images/user-avatar.png') }}" class="analyst-avatar"
                                id="modalAnalystAvatar">
                            <div class="analyst-info">
                                <h6 id="modalAnalystName">Analyst</h6>
                                <span>Expert Analyst</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small mb-1" style="font-size: 0.65rem;">Strategy Date</div>
                            <div class="fw-bold" id="modalDate" style="font-size: 0.85rem;">—</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        style="position: absolute; top: 20px; right: 20px;"></button>
                </div>
                <div class="modal-body">
                    <div id="modalLoader" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>

                    <div id="modalContent" style="display: none;">
                        <div class="row mt-4 align-items-start">
                            <div class="col-8">
                                <h3 class="fw-bold text-dark mb-1" id="modalTitle">—</h3>
                                <div class="d-flex align-items-baseline gap-3">
                                    <span class="h2 fw-bold mb-0" id="modalPrice">₹0.00</span>
                                    <span class="fw-bold" id="modalChange" style="font-size: 1.1rem;">—</span>
                                </div>
                                <div class="text-muted small mt-1" id="modalTime">—</div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="d-inline-flex flex-column align-items-center">
                                    <div class="circle-indicator" id="modalCircleIndicator">
                                        <span class="circle-val" id="modalRemainingTarget">0%</span>
                                        <span class="circle-label">REMAINING</span>
                                    </div>
                                    <div class="small fw-bold text-muted mt-2" id="modalDaysInfo">—</div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-4">
                            <div class="col-md-6">
                                <div class="upside-box">
                                    <span class="box-label">Max Upside</span>
                                    <span class="box-val text-success" id="modalMaxUpside">0%</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="downside-box">
                                    <span class="box-label">Max Downside</span>
                                    <span class="box-val text-danger" id="modalMaxDownside">0%</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-light rounded-4 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-muted small">Approx Capital Required</span>
                            <div class="h5 fw-bold mb-0" id="modalCapital">₹0</div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="fw-bold text-muted small">Status : <span id="modalStatusText"
                                    class="text-success">—</span></div>
                            <div class="d-flex gap-2">
                                @if(\Auth::user()->type == 'company' || \Auth::user()->isAbleTo('signal delete'))
                                    <button class="btn btn-outline-danger btn-sm px-3 rounded-3 fw-bold" id="btnDeleteSignal">
                                        <i class="ti ti-trash me-1"></i> DELETE
                                    </button>
                                @endif
                                @if(\Auth::user()->type == 'company' || \Auth::user()->isAbleTo('signal edit'))
                                    <button class="btn btn-dark btn-sm px-4 rounded-3 fw-bold" id="btnCloseSignal">
                                        <i class="ti ti-lock me-1"></i> CLOSE SIGNAL
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="journey-container mt-5">
                            <div class="journey-track">
                                <div class="journey-progress" id="modalJourneyProgress" style="width: 0%"></div>

                                {{-- Markers & Nodes --}}
                                <div class="journey-marker sl" style="left: 0%"></div>
                                <div class="node-info" style="left: 0%">
                                    <span class="node-label">Stop Loss</span>
                                    <span class="node-price text-danger" id="modalPriceSL">₹0</span>
                                </div>

                                <div class="journey-marker entry" id="modalEntryMarker" style="left: 30%"></div>
                                <div class="node-info text-start" id="modalEntryNode" style="left: 30%">
                                    <span class="node-label">Entry</span>
                                    <span class="node-price" id="modalPriceEntry">₹0</span>
                                </div>

                                <div class="journey-marker target" style="left: 100%"></div>
                                <div class="node-info" style="left: 100%">
                                    <span class="node-label">Target</span>
                                    <span class="node-price text-success" id="modalPriceTarget">₹0</span>
                                </div>
                                <div class="journey-marker current" id="modalCurrentMarker"
                                    style="left: 50%; opacity: 0; background: #0f172a; width: 4px; height: 24px; position: absolute; top: 50%; transform: translate(-50%, -50%); z-index: 3;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // ── Accordion (Closed calls) ─────────────────────────────
        function toggleClosedGroup(header) {
            const body = header.nextElementSibling;
            const arrow = header.querySelector('.text-muted.small');
            body.classList.toggle('open');
            if (arrow) arrow.textContent = body.classList.contains('open') ? '∧' : '∨';
        }

        // ── Signal Detail Modal ─────────────────────────────────
        var liveUpdateInterval = null;
        var currentSignalData = null;

        function openSignalDrawer(id) {
            console.log("Opening Signal Drawer for ID:", id);

            const modalElement = document.getElementById('signalDetailModal');
            if (!modalElement) {
                console.error("Modal element #signalDetailModal not found!");
                return;
            }

            // Support both BS4 and BS5
            let modal;
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                modal = new bootstrap.Modal(modalElement);
            } else if (typeof $ !== 'undefined' && $.fn.modal) {
                modal = $(modalElement).modal('show');
                // For jQuery based modal, we just return as it's already showing
                if (!bootstrap || !bootstrap.Modal) {
                    processSignalDetails(id);
                    return;
                }
            } else {
                console.error("Bootstrap or jQuery Modal not found!");
                alert("This feature requires Bootstrap. Please check if Bootstrap JS is loaded.");
                return;
            }

            if (modal && modal.show) modal.show();
            processSignalDetails(id);
        }

        window.openSignalDrawer = openSignalDrawer;

        function processSignalDetails(id) {
            const loader = document.getElementById('modalLoader');
            const content = document.getElementById('modalContent');
            if (loader) loader.style.display = 'block';
            if (content) content.style.display = 'none';

            if (liveUpdateInterval) clearInterval(liveUpdateInterval);

            fetch(`{{ url('stock-signals') }}/${id}/drawer`)
                .then(r => r.json())
                .then(data => {
                    const s = data.signal;
                    currentSignalData = s;

                    const updateText = (id, val) => {
                        const el = document.getElementById(id);
                        if (el) el.textContent = val;
                    };

                    updateText('modalAnalystName', data.creator ? data.creator.name : 'Analyst');
                    updateText('modalDate', s.date_human || (new Date(s.date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })));
                    updateText('modalTitle', s.title);
                    updateText('modalPriceSL', '₹' + parseFloat(s.stoploss || 0).toLocaleString());
                    updateText('modalPriceEntry', '₹' + parseFloat(s.buy_price_min || 0).toLocaleString());
                    updateText('modalPriceTarget', '₹' + parseFloat(s.target || 0).toLocaleString());
                    updateText('modalCapital', '₹' + parseFloat(s.min_amount || 0).toLocaleString());

                    // Position journey nodes
                    const sl = parseFloat(s.stoploss || 0);
                    const tgt = parseFloat(s.target || 0);
                    const entry = parseFloat(s.buy_price_min || 0);

                    if (tgt > sl) {
                        const entryPos = ((entry - sl) / (tgt - sl)) * 100;
                        document.getElementById('modalEntryMarker').style.left = entryPos + '%';
                        document.getElementById('modalEntryNode').style.left = entryPos + '%';
                    }

                    // Initial Price
                    document.getElementById('modalPrice').textContent = '₹' + entry.toFixed(2);

                    // Max Upside/Downside
                    const entryPrice = (parseFloat(s.buy_price_min) + parseFloat(s.buy_price_max || s.buy_price_min)) / 2;
                    const maxUpside = s.target ? (((s.target - entryPrice) / entryPrice) * 100).toFixed(2) : 0;
                    const maxDownside = s.stoploss ? (((entryPrice - s.stoploss) / entryPrice) * 100).toFixed(2) : 0;
                    document.getElementById('modalMaxUpside').textContent = maxUpside + '%';
                    document.getElementById('modalMaxDownside').textContent = maxDownside + '%';

                    loader.style.display = 'none';
                    content.style.display = 'block';

                    // Start live updates
                    updateModalLivePrice(s);
                    liveUpdateInterval = setInterval(() => updateModalLivePrice(s), 15000);

                    // Setup buttons
                    const closeBtn = document.getElementById('btnCloseSignal');
                    if (closeBtn) {
                        closeBtn.onclick = () => closeSignalManually(s.id);
                        closeBtn.style.display = s.status === 'live' ? 'block' : 'none';
                    }
                    const deleteBtn = document.getElementById('btnDeleteSignal');
                    if (deleteBtn) {
                        deleteBtn.onclick = () => deleteSignalFromModal(s.id);
                    }

                    document.getElementById('modalStatusText').textContent = s.status.toUpperCase();
                    document.getElementById('modalStatusText').className = s.status === 'live' ? 'text-primary' : (s.close_reason === 'target_hit' ? 'text-success' : 'text-danger');
                });
        }

        function updateModalLivePrice(s) {
            if (!s.symbol) return;
            fetch(`{{ route('stockmarket.equity.price') }}?symbol=${s.symbol}`)
                .then(r => r.json())
                .then(data => {
                    if (data.error) return;

                    const lp = parseFloat(data.lastPrice);
                    document.getElementById('modalPrice').textContent = '₹' + lp.toLocaleString(undefined, { minimumFractionDigits: 2 });

                    const chg = parseFloat(data.pChange || 0);
                    const chgSign = chg >= 0 ? '+' : '';
                    document.getElementById('modalChange').textContent = `(${chgSign}${chg.toFixed(2)}%)`;
                    document.getElementById('modalChange').className = chg >= 0 ? 'fw-bold text-success' : 'fw-bold text-danger';

                    document.getElementById('modalTime').textContent = 'Last Updated: ' + new Date().toLocaleTimeString();

                    // Update Journey
                    const sl = parseFloat(s.stoploss || 0);
                    const tgt = parseFloat(s.target || 0);
                    if (tgt > sl) {
                        let prog = ((lp - sl) / (tgt - sl)) * 100;
                        prog = Math.max(0, Math.min(100, prog));
                        document.getElementById('modalJourneyProgress').style.width = prog + '%';

                        const currentMarker = document.getElementById('modalCurrentMarker');
                        currentMarker.style.left = prog + '%';
                        currentMarker.style.opacity = '1';

                        // Remaining Target
                        let rem = 0;
                        if (lp < tgt) {
                            rem = (((tgt - lp) / lp) * 100).toFixed(1);
                        }
                        document.getElementById('modalRemainingTarget').textContent = rem + '%';

                        const indicator = document.getElementById('modalCircleIndicator');
                        if (lp >= tgt) {
                            indicator.classList.add('complete');
                            document.getElementById('modalRemainingTarget').textContent = 'HIT';
                            document.getElementById('modalStatusText').textContent = 'TARGET ACHIEVED';
                            document.getElementById('modalStatusText').className = 'text-success fw-bold';
                        } else {
                            indicator.classList.remove('complete');
                        }
                    }
                });
        }

        function closeSignalManually(id, defaultPrice = null) {
            let p = defaultPrice;
            const priceEl = document.getElementById('modalPrice');
            if (!p && priceEl) {
                p = priceEl.textContent.replace('₹', '').replace(',', '').trim();
            }

            Swal.fire({
                title: 'Close Position',
                html: `
                        <div class="text-start mb-3">
                            <label class="form-label fw-bold small mb-1">Exit Price</label>
                            <input type="number" id="swal-exit-price" class="form-control" value="${p || ''}" step="0.05" placeholder="Enter exit price">
                        </div>
                        <div class="text-start">
                            <label class="form-label fw-bold small mb-1">Close Reason</label>
                            <select id="swal-close-reason" class="form-select">
                                <option value="target_hit">Target Hit</option>
                                <option value="manual_close" selected>Manual / SL Close</option>
                            </select>
                        </div>
                    `,
                showCancelButton: true,
                confirmButtonText: 'Confirm Close',
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#f43f5e',
                background: '#fff',
                borderRadius: '16px',
                preConfirm: () => {
                    const price = document.getElementById('swal-exit-price').value;
                    const reason = document.getElementById('swal-close-reason').value;
                    if (!price || isNaN(price)) {
                        Swal.showValidationMessage('Please enter a valid price');
                        return false;
                    }
                    return { price: price, reason: reason };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const { price, reason } = result.value;

                    fetch(`{{ url('stock-signals') }}/${id}/close`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ exit_price: price, close_reason: reason })
                    })
                        .then(r => r.json())
                        .then(res => {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: 'Signal closed successfully',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                Swal.fire('Error', res.message || 'Failed to close signal', 'error');
                            }
                        });
                }
            });
        }

        function deleteSignalFromModal(id) {
            if (!confirm('Are you sure you want to delete this signal?')) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('stock-signals') }}/${id}`;
            form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">`;
            document.body.appendChild(form);
            form.submit();
        }

        function closeSignalDrawer() {
            // Deprecated, using BS Modal now
        }

        function openAdjustmentForm(signalId) {
            const wrap = document.getElementById('adjFormWrap-' + signalId);
            if (wrap.style.display === 'none') {
                wrap.style.display = 'block';
                wrap.innerHTML = `
                                                                    <form onsubmit="submitAdjustment(event, ${signalId})" style="background:#f9fafb; border-radius:10px; padding:14px;">
                                                                        <div class="row g-2">
                                                                            <div class="col-4"><input type="number" step="0.01" name="target" class="form-control form-control-sm" placeholder="Target"></div>
                                                                            <div class="col-4"><input type="number" step="0.01" name="stoploss" class="form-control form-control-sm" placeholder="Stoploss"></div>
                                                                            <div class="col-4"><input type="number" name="quantity" class="form-control form-control-sm" placeholder="Qty"></div>
                                                                        </div>
                                                                        <textarea name="note" class="form-control form-control-sm mt-2" rows="2" placeholder="Note / Reason for adjustment"></textarea>
                                                                        <button type="submit" class="btn btn-sm w-100 mt-2" style="background:#2db57a; color:#fff; border-radius:8px;">Save Adjustment</button>
                                                                    </form>`;
            } else {
                wrap.style.display = 'none';
            }
        }

        function submitAdjustment(e, signalId) {
            e.preventDefault();
            const form = e.target;
            const data = new FormData(form);
            data.append('_token', '{{ csrf_token() }}');

            fetch(`{{ url('stock-signals') }}/${signalId}/adjustments`, { method: 'POST', body: data })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        openSignalDrawer(signalId); // reload drawer
                    }
                }).catch(() => alert('Error saving adjustment.'));
        }

        // ── Performance Chart ────────────────────────────────────
        @if($tab === 'closed' && !empty($performanceData) && $performanceData['total'] > 0)
            const ctx = document.getElementById('performanceChart');
            if (ctx) {
                const chartData = @json($performanceData['chartData'] ?? []);
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartData.map((_, i) => 'Call ' + (i + 1)),
                        datasets: [{
                            data: chartData,
                            backgroundColor: ctx => ctx.raw >= 0 ? '#10b981' : '#f43f5e',
                            borderRadius: 6,
                            barThickness: 12,
                        }]
                    },
                    options: {
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return 'P&L: ' + context.raw + '%';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { display: false },
                            y: {
                                display: true,
                                grid: { display: false, drawBorder: false },
                                ticks: {
                                    color: '#94a3b8',
                                    font: { size: 10 },
                                    callback: v => v + '%'
                                }
                            }
                        },
                        animation: { duration: 1000, easing: 'easeOutQuart' },
                    }
                });
            }
        @endif

        @if(request()->has('open_signal'))
            document.addEventListener("DOMContentLoaded", function () {
                setTimeout(() => {
                    openSignalDrawer({{ request('open_signal') }});
                }, 300);
            });
        @endif
    </script>
@endpush