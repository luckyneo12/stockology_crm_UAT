<div class="col-md-6 col-xl-4 mb-3">
    <div class="premium-signal-card" onclick="openSignalDrawer({{ $signal->id }})" data-id="{{ $signal->id }}">

        {{-- Premium Header Gradient based on Type --}}
        <div class="card-header-premium {{ $signal->type === 'buy' ? 'is-buy' : 'is-sell' }}">
            <div class="d-flex justify-content-between align-items-center">
                <span class="technical-tag">
                    <i class="ti ti-activity me-1"></i>
                    {{ $signal->category?->name ?? (count($signal->legs ?? []) > 0 ? 'F&O' : 'EQUITY') }}
                </span>
                <div class="d-flex align-items-center gap-2">
                    <span class="exchange-pill">{{ $signal->exchange ?? 'NSE' }}</span>
                </div>
            </div>
        </div>

        <div class="card-inner-content">
            {{-- Sentiment & Symbol --}}
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="symbol-container">
                    <h6 class="stock-title-modern">{{ $signal->title }}</h6>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="symbol-code">{{ $signal->symbol }}</span>
                        @if($signal->hold_duration)
                            <span class="duration-pill">
                                <i class="ti ti-clock"></i> {{ $signal->hold_duration }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="sentiment-badge {{ $signal->type === 'buy' ? 'bullish' : 'bearish' }}">
                    {{ $signal->type === 'buy' ? 'BULLISH' : 'BEARISH' }}
                </div>
            </div>

            {{-- Price & Returns Grid --}}
            <div class="stats-grid mb-4">
                <div class="stat-box">
                    <span class="stat-label">CURRENT PRICE</span>
                    <div class="value-wrap">
                        <span class="currency-symbol">₹</span>
                        <span class="live-price"
                            id="price-{{ $signal->id }}">{{ number_format((float) ($signal->buy_price_min ?? 0), 2) }}</span>
                    </div>
                    <div class="live-change-modern" id="change-{{ $signal->id }}">Refresing...</div>
                </div>
                <div class="stat-box text-end">
                    <span class="stat-label">EXPECTED ROI</span>
                    @php $est = $signal->est_returns; @endphp
                    <div class="roi-value {{ $est >= 0 ? 'pos' : 'neg' }}">
                        {{ $est >= 0 ? '+' : '' }}{{ number_format((float) $est, 1) }}%
                    </div>
                    <div class="pulse-indicator-small">
                        <span class="pulse-dot"></span> LIVE
                    </div>
                </div>
            </div>

            {{-- Progress to Target (Equity Only) --}}
            @if(empty($signal->legs) || count($signal->legs) == 0)
                @php
                    $sl = $signal->stoploss ?? 0;
                    $tgt = $signal->target ?? 0;
                    $cur = $signal->buy_price_min ?? 0;
                    $progressRaw = 0;
                    if ($sl > 0 && $tgt > 0 && $tgt > $sl) {
                        $progressRaw = (($cur - $sl) / ($tgt - $sl)) * 100;
                    }
                    $progress = max(0, min(100, $progressRaw));
                    $progressColor = $progress > 50 ? '#10b981' : ($progress > 20 ? '#f59e0b' : '#f43f5e');
                @endphp
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="stat-label mb-0" style="font-size: 0.6rem;">PROGRESS TO TARGET</span>
                        <span class="fw-bold"
                            style="font-size: 0.7rem; color: {{ $progressColor }}">{{ number_format((float) $progress, 0) }}%</span>
                    </div>
                    <div class="progress" style="height: 6px; border-radius: 10px; background-color: #f1f5f9;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                            style="width: {{ $progress }}%; background-color: {{ $progressColor }};"
                            aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
            @endif

            {{-- Professional P&L Tracker / Strategy Summary --}}
            <div class="pl-tracker mb-4">
                @if(!empty($signal->legs) && count($signal->legs) > 0)
                    {{-- Option Strategy Summary --}}
                    <div class="track-info d-flex justify-content-between mb-2">
                        <span class="label">STRATEGY STRUCTURE</span>
                        <span class="value text-primary fw-bold">{{ count($signal->legs) }} Legs Active</span>
                    </div>
                    <div class="option-legs-list mt-2">
                        @foreach(array_slice($signal->legs, 0, 4) as $leg)
                            <div class="option-leg-row">
                                <span class="leg-strike">{{ $leg['strike'] }} {{ $leg['type'] }}</span>
                                <span class="leg-action {{ $leg['action'] === 'buy' ? 'text-success' : 'text-danger' }}">
                                    {{ strtoupper($leg['action']) }} @ ₹{{ number_format((float) ($leg['entry'] ?? 0), 2) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    @if(count($signal->legs) > 4)
                        <div class="text-center mt-1">
                            <span class="text-muted small" style="font-size: 0.65rem;">+{{ count($signal->legs) - 4 }} more
                                legs...</span>
                        </div>
                    @endif
                @else
                    {{-- Equity P&L Tracker --}}
                    @php
                        $sl = $signal->stoploss ?? 0;
                        $tgt = $signal->target ?? 0;
                        $cur = $signal->buy_price_min ?? 0;
                        $pos = ($sl > 0 && $tgt > 0 && $tgt > $sl)
                            ? max(5, min(95, (($cur - $sl) / ($tgt - $sl)) * 100))
                            : 50;
                    @endphp
                    <div class="track-info d-flex justify-content-between mb-2">
                        <span class="label">P&L JOURNEY</span>
                        <span class="value">{{ number_format((float) $pos, 1) }}% Traversed</span>
                    </div>
                    <div class="premium-progress">
                        <div class="safety-track" style="width: {{ $pos }}%"></div>
                        <div class="premium-marker" style="left: {{ $pos }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <div class="price-node stoploss">
                            <span class="node-label">SL</span>
                            <span class="node-val">₹{{ number_format((float) ($signal->stoploss ?? 0), 0) }}</span>
                        </div>
                        <div class="price-node entry text-center">
                            <span class="node-label">ENTRY</span>
                            <span class="node-val">{{ str_replace(' ', '', $signal->entry_range) }}</span>
                        </div>
                        <div class="price-node target text-end">
                            <span class="node-label">TARGET</span>
                            <span class="node-val">₹{{ number_format((float) ($signal->target ?? 0), 0) }}</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer Meta --}}
            <div class="card-meta">
                <div class="author-block">
                    <div class="author-avatar">{{ substr($signal->creator?->name ?? 'A', 0, 1) }}</div>
                    <span>{{ $signal->creator?->name ?? 'Analyst' }}</span>
                </div>
                <div class="date-block">
                    <i class="ti ti-calendar"></i> {{ $signal->date->format('j M') }}
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <button class="btn btn-dark btn-sm flex-fill rounded-3 fw-bold py-2"
                    onclick="openSignalDrawer({{ $signal->id }})" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                    VIEW DETAILS
                </button>
                @if($signal->status === 'live')
                    @if(\Auth::user()->type == 'company' || \Auth::user()->id == $signal->created_by || \Auth::user()->isAbleTo('signal edit'))
                        <button class="btn btn-outline-danger btn-sm flex-fill rounded-3 fw-bold py-2"
                            onclick="event.stopPropagation(); closeSignalManually({{ $signal->id }}, '{{ $signal->buy_price_min }}')"
                            style="font-size: 0.7rem;">
                            CLOSE
                        </button>
                    @endif
                @endif
                @if(\Auth::user()->type == 'company' || \Auth::user()->id == $signal->created_by || \Auth::user()->isAbleTo('signal edit'))
                    <a href="{{ route('stock-signals.edit', $signal->id) }}"
                        onclick="event.stopPropagation();"
                        class="btn btn-outline-primary btn-sm rounded-3 fw-bold py-2" style="font-size: 0.7rem;">
                        <i class="ti ti-pencil"></i>
                    </a>
                @endif
                @if(\Auth::user()->type == 'company' || \Auth::user()->id == $signal->created_by || \Auth::user()->isAbleTo('signal delete'))
                    <form method="POST" action="{{ route('stock-signals.destroy', $signal->id) }}"
                        class="d-inline"
                        onclick="event.stopPropagation();"
                        onsubmit="return confirm('Are you sure you want to delete this signal?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-secondary btn-sm rounded-3 fw-bold py-2"
                            style="font-size: 0.7rem;">
                            <i class="ti ti-trash"></i>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap');

    .premium-signal-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.05);
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        cursor: pointer;
        position: relative;
        font-family: 'Space Grotesk', sans-serif;
    }

    .premium-signal-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.12);
        border-color: rgba(0, 0, 0, 0.1);
    }

    .card-header-premium {
        padding: 12px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
    }

    .card-header-premium.is-buy {
        background: linear-gradient(90deg, #f0fdf4 0%, #ffffff 100%);
    }

    .card-header-premium.is-sell {
        background: linear-gradient(90deg, #fff1f2 0%, #ffffff 100%);
    }

    .technical-tag {
        font-size: 0.65rem;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .exchange-pill {
        font-size: 0.6rem;
        font-weight: 800;
        background: #1e293b;
        color: #fff;
        padding: 2px 8px;
        border-radius: 20px;
    }

    .card-inner-content {
        padding: 20px;
    }

    .stock-title-modern {
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.3;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .symbol-code {
        font-size: 0.75rem;
        font-weight: 700;
        color: #3b82f6;
        background: #eff6ff;
        padding: 2px 10px;
        border-radius: 6px;
    }

    .duration-pill {
        font-size: 0.7rem;
        color: #64748b;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .sentiment-badge {
        font-size: 0.6rem;
        font-weight: 800;
        padding: 5px 12px;
        border-radius: 8px;
        letter-spacing: 0.08em;
    }

    .sentiment-badge.bullish {
        background: #10b981;
        color: #fff;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
    }

    .sentiment-badge.bearish {
        background: #f43f5e;
        color: #fff;
        box-shadow: 0 4px 12px rgba(244, 63, 94, 0.25);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 12px;
        background: #f8fafc;
        padding: 16px;
        border-radius: 16px;
    }

    .stat-label {
        font-size: 0.55rem;
        font-weight: 800;
        color: #94a3b8;
        display: block;
        margin-bottom: 4px;
        letter-spacing: 0.05em;
    }

    .value-wrap {
        display: flex;
        align-items: baseline;
        gap: 2px;
    }

    .currency-symbol {
        font-size: 0.8rem;
        font-weight: 600;
        color: #475569;
    }

    .live-price {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
    }

    .live-change-modern {
        font-size: 0.7rem;
        font-weight: 700;
        margin-top: -2px;
    }

    .roi-value {
        font-size: 1.25rem;
        font-weight: 800;
    }

    .roi-value.pos {
        color: #10b981;
    }

    .roi-value.neg {
        color: #f43f5e;
    }

    .pulse-indicator-small {
        font-size: 0.55rem;
        font-weight: 800;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        margin-top: 4px;
    }

    .pulse-dot {
        width: 6px;
        height: 6px;
        background: #10b981;
        border-radius: 50%;
        animation: pulse-dot 1.5s infinite;
    }

    @keyframes pulse-dot {
        0% {
            transform: scale(0.95);
            opacity: 1;
        }

        50% {
            transform: scale(1.1);
            opacity: 0.5;
        }

        100% {
            transform: scale(0.95);
            opacity: 1;
        }
    }

    .pl-tracker .label {
        font-size: 0.6rem;
        font-weight: 800;
        color: #94a3b8;
    }

    .pl-tracker .value {
        font-size: 0.6rem;
        font-weight: 700;
        color: #475569;
    }

    .premium-progress {
        height: 8px;
        background: #e2e8f0;
        border-radius: 10px;
        position: relative;
        overflow: hidden;
    }

    .safety-track {
        position: absolute;
        height: 100%;
        background: linear-gradient(90deg, #f43f5e33, #10b98133);
        border-right: 2px solid #0f172a;
    }

    .premium-marker {
        position: absolute;
        top: 0;
        width: 12px;
        height: 12px;
        background: #0f172a;
        border: 2px solid #fff;
        border-radius: 50%;
        transform: translate(-50%, -2px);
        z-index: 2;
    }

    .price-node {
        display: flex;
        flex-direction: column;
    }

    .node-label {
        font-size: 0.5rem;
        font-weight: 800;
        color: #94a3b8;
        text-transform: uppercase;
    }

    .node-val {
        font-size: 0.75rem;
        font-weight: 700;
        color: #1e293b;
    }

    .stoploss .node-val {
        color: #f43f5e;
    }

    .target .node-val {
        color: #10b981;
    }

    .card-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 16px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
    }

    .author-block {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.7rem;
        font-weight: 600;
        color: #475569;
    }

    .author-avatar {
        width: 24px;
        height: 24px;
        background: #f1f5f9;
        color: #3b82f6;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 0.65rem;
        font-weight: 800;
        border: 1px solid #e2e8f0;
    }

    .date-block {
        font-size: 0.65rem;
        font-weight: 700;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .action-icon-btn {
        width: 26px;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        border: none;
        background: transparent;
        color: #64748b;
        transition: all 0.2s;
        text-decoration: none !important;
    }

    .action-icon-btn:hover {
        background: rgba(0, 0, 0, 0.05);
        color: #0f172a;
    }

    .delete-btn:hover {
        background: #fee2e2;
        color: #f43f5e;
    }

    .option-legs-list {
        background: rgba(255, 255, 255, 0.5);
        border-radius: 12px;
        padding: 8px 12px;
        border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .option-leg-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
    }

    .option-leg-row:last-child {
        border-bottom: none;
    }

    .leg-strike {
        font-size: 0.75rem;
        font-weight: 700;
        color: #1e293b;
    }

    .leg-action {
        font-size: 0.65rem;
        font-weight: 800;
    }
</style>
</style>