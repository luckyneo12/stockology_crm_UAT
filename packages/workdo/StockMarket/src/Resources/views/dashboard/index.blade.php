@extends('layouts.main')

@section('page-title')
    {{ __('Stock Market Dashboard') }}
@endsection

@section('page-breadcrumb')
    {{ __('Stock Market') }}, {{ __('Dashboard') }}
@endsection

@section('content')
    <div class="row g-4">

        {{-- Market Status Banner --}}
        <div class="col-12">
            <div class="card stock-market-ticker"
                style="background: linear-gradient(135deg,#0f1e3a 0%,#1a3a5c 100%); border:none; border-radius:16px; overflow:hidden;">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-4 flex-wrap" id="marketTickerRow">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-5 fw-bold text-white">📈 Live Market</span>
                            <span class="badge bg-danger" id="marketStatusBadge">Loading...</span>
                        </div>
                        <div class="ticker-items d-flex gap-4 flex-wrap" id="tickerItems">
                            <div class="ticker-shimmer"
                                style="width:120px; height:30px; background:rgba(255,255,255,0.1); border-radius:8px;">
                            </div>
                            <div class="ticker-shimmer"
                                style="width:120px; height:30px; background:rgba(255,255,255,0.1); border-radius:8px;">
                            </div>
                            <div class="ticker-shimmer"
                                style="width:120px; height:30px; background:rgba(255,255,255,0.1); border-radius:8px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="col-6 col-md-3">
            <div class="card text-center" style="border-radius:14px; border-left:4px solid #2db57a;">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-success">{{ $liveSignals }}</div>
                    <div class="text-muted small">● Live Calls</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center" style="border-radius:14px; border-left:4px solid #e05a5a;">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-danger">{{ $closedSignals }}</div>
                    <div class="text-muted small">Closed Calls</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center" style="border-radius:14px; border-left:4px solid #3b82f6;">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold" style="color:#3b82f6;">{{ $totalSignals }}</div>
                    <div class="text-muted small">Total Signals</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center" style="border-radius:14px; border-left:4px solid #f59e0b;">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-warning">{{ $todaySignals }}</div>
                    <div class="text-muted small">Today's New</div>
                </div>
            </div>
        </div>

        {{-- Live Calls Preview --}}
        <div class="col-12">
            <div class="card" style="border-radius:16px;">
                <div class="card-header d-flex justify-content-between align-items-center"
                    style="border-bottom:1px solid #f0f0f0;">
                    <h5 class="mb-0 fw-bold">🔴 Live Calls Preview</h5>
                    <a href="{{ route('stock-signals.index') }}" class="btn btn-sm"
                        style="background:#2db57a;color:#fff;border-radius:8px;">
                        View All Signals
                    </a>
                </div>
                <div class="card-body">
                    @if($liveCallsPreview->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="ti ti-chart-candle fs-1"></i>
                            <p class="mt-2">No live signals yet. <a href="{{ route('stock-signals.create') }}">Publish first
                                    signal</a></p>
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($liveCallsPreview as $signal)
                                @include('stockmarket::partials._signal_card', ['signal' => $signal])
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Fetch live market status
        function loadMarketTicker() {
            fetch('{{ route('stockmarket.live.data') }}')
                .then(r => r.json())
                .then(data => {
                    const badge = document.getElementById('marketStatusBadge');
                    const items = document.getElementById('tickerItems');

                    if (!data.marketState) return;

                    // Check NIFTY status
                    const nifty = data.marketState.find(m => m.market === 'Capital Market');
                    if (nifty) {
                        const isOpen = nifty.marketStatus === 'Open';
                        badge.textContent = isOpen ? '● Market Open' : '● Market Closed';
                        badge.className = 'badge ' + (isOpen ? 'bg-success' : 'bg-secondary');
                    }

                    // Render ticker items
                    let html = '';
                    data.marketState.forEach(m => {
                        if (!m.last || !m.index) return;
                        const pChange = parseFloat(m.percentChange);
                        const clr = pChange >= 0 ? '#2db57a' : '#e05a5a';
                        const arrow = pChange >= 0 ? '▲' : '▼';
                        html += `<div class="d-flex flex-column">
                        <span class="text-white-50 small">${m.index}</span>
                        <span class="fw-bold" style="color:${clr};">${m.last} <span class="small">${arrow} ${Math.abs(pChange).toFixed(2)}%</span></span>
                    </div>`;
                    });

                    // GIFT NIFTY
                    if (data.giftnifty) {
                        const gn = data.giftnifty;
                        const clr = gn.DAYCHANGE >= 0 ? '#2db57a' : '#e05a5a';
                        html += `<div class="d-flex flex-column">
                        <span class="text-white-50 small">GIFT NIFTY</span>
                        <span class="fw-bold" style="color:${clr};">${gn.LASTPRICE} <span class="small">${gn.DAYCHANGE >= 0 ? '▲' : '▼'} ${Math.abs(gn.PERCHANGE).toFixed(2)}%</span></span>
                    </div>`;
                    }

                    items.innerHTML = html || '<span class="text-white-50">Data unavailable</span>';
                })
                .catch(() => {
                    document.getElementById('marketStatusBadge').textContent = 'Offline';
                });
        }

        loadMarketTicker();
        setInterval(loadMarketTicker, 30000); // refresh every 30s
    </script>
@endsection