@extends('layouts.main')

@section('page-title'){{ __('New Stock Signal') }}@endsection
@section('page-breadcrumb'){{ __('Stock Market') }}, {{ __('New Signal') }}@endsection

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary-dark: #0f172a;
            --accent-blue: #3b82f6;
            --success-green: #10b981;
            --danger-red: #ef4444;
            --glass-bg: rgba(255, 255, 255, 0.8);
            --border-subtle: rgba(0, 0, 0, 0.05);
        }

        body {
            background-color: #f8fafc;
        }

        .premium-container {
            font-family: 'Inter', sans-serif;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .signal-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--border-subtle);
            overflow: hidden;
        }

        .signal-header {
            padding: 32px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-bottom: 1px solid var(--border-subtle);
            position: relative;
        }

        .company-name {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-dark);
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .company-symbol {
            font-size: 0.9rem;
            color: #64748b;
            background: #f1f5f9;
            padding: 2px 10px;
            border-radius: 6px;
            font-weight: 600;
        }

        .badge-more {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-blue);
            font-size: 0.7rem;
            padding: 4px 10px;
            border-radius: 8px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .live-price-area {
            display: flex;
            align-items: baseline;
            gap: 16px;
            margin-top: 16px;
        }

        .live-price {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--primary-dark);
            letter-spacing: -1px;
        }

        .live-pulse {
            width: 10px;
            height: 10px;
            background: var(--success-green);
            border-radius: 50%;
            display: inline-block;
            margin-left: 10px;
            animation: pulse-green 2s infinite;
        }

        @keyframes pulse-green {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        .price-change {
            font-size: 1rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 8px;
        }

        .price-change.positive {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-green);
        }

        .price-change.negative {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-red);
        }

        .form-section {
            padding: 32px;
            border-bottom: 1px solid var(--border-subtle);
        }

        .section-label {
            font-size: 0.75rem;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-subtle);
        }

        /* ── Input Styling ─────────────────────────── */
        .premium-input {
            border: 2px solid #f1f5f9;
            border-radius: 14px;
            padding: 12px 16px;
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-dark);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            background: #fff;
            width: 100%;
        }

        .premium-input:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
            background: #fff;
        }

        .input-group-premium {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .has-icon .premium-input {
            padding-left: 44px;
        }

        /* ── Opinion Toggles ────────────────────────── */
        .opinion-group {
            display: flex;
            gap: 12px;
            margin-bottom: 32px;
        }

        .opinion-btn {
            flex: 1;
            padding: 14px;
            border-radius: 12px;
            border: 2px solid #f1f5f9;
            text-align: center;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s;
            background: #fff;
            color: #64748b;
        }

        .opinion-btn.buy.active {
            background: rgba(16, 185, 129, 0.05);
            border-color: var(--success-green);
            color: var(--success-green);
        }

        .opinion-btn.sell.active {
            background: rgba(239, 68, 68, 0.05);
            border-color: var(--danger-red);
            color: var(--danger-red);
        }

        /* ── Action Buttons ─────────────────────────── */
        .btn-premium {
            background: var(--primary-dark);
            color: #fff !important;
            padding: 16px 32px;
            border-radius: 16px;
            font-weight: 700;
            border: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
        }

        .btn-premium:hover {
            background: #1e293b;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.2);
        }

        /* ── Order Type (Market/Limit) ──────────────── */
        .order-type-tabs {
            display: flex;
            background: #f1f5f9;
            border-radius: 10px;
            padding: 4px;
            height: 48px;
        }

        .order-tab {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            border-radius: 6px;
            transition: 0.2s;
        }

        .order-tab.active {
            background: #fff;
            color: var(--primary-dark);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        /* ── Payoff/Overview Styles ─────────────────── */
        .overview-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-subtle);
        }

        .overview-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed #f1f5f9;
        }

        .overview-row:last-child {
            border-bottom: none;
        }

        .overview-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
        }

        .overview-value {
            font-weight: 800;
            color: var(--primary-dark);
        }

        .val-positive {
            color: var(--success-green);
        }

        .val-negative {
            color: var(--danger-red);
        }


        /* Hidden inputs */
        .d-none {
            display: none !important;
        }

        /* Fix header flex layout */
        .signal-header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .signal-header-badges {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Autocomplete Dropdown Styles */
        .autocomplete-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #eef0f4;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            max-height: 250px;
            overflow-y: auto;
            display: none;
        }

        .autocomplete-item {
            padding: 10px 14px;
            border-bottom: 1px solid #f9fafb;
            cursor: pointer;
            transition: background 0.2s;
            text-align: left;
        }

        .autocomplete-item:hover,
        .autocomplete-item.active {
            background: #f0fdfa;
        }

        .ac-symbol {
            font-weight: 700;
            color: #1a1b25;
            display: block;
            font-size: 0.9rem;
        }

        .ac-name {
            font-size: 0.8rem;
            color: #6b7280;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Extra Form Fields Accordion */

        .advanced-settings-toggle {
            font-size: 0.9rem;
            color: #3b82f6;
            cursor: pointer;
            font-weight: 600;
            margin-top: 16px;
            margin-bottom: 8px;
            display: inline-block;
        }

        /* F&O Builder Styles */
        .fo-builder-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #eef0f4;
            margin-bottom: 24px;
            font-family: 'Inter', sans-serif;
        }

        .fo-header {
            padding: 16px 24px;
            border-bottom: 1px solid #eef0f4;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .fo-header h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a1b25;
        }

        .bull-bear-toggle {
            display: flex;
            background: #f9fafb;
            border-radius: 20px;
            border: 1px solid #eef0f4;
            overflow: hidden;
        }

        .bull-bear-btn {
            padding: 6px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            color: #6b7280;
        }

        .bull-bear-btn.active.bullish {
            background: #10b981;
            color: white;
        }

        .bull-bear-btn.active.bearish {
            background: #ef4444;
            color: white;
        }

        /* ── Terminal Table Styles (F&O Builder) ────────── */
        .terminal-card {
            background: #0f172a;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: 24px;
        }

        .terminal-header {
            padding: 16px 24px;
            background: rgba(255, 255, 255, 0.03);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .terminal-title {
            color: #94a3b8;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .terminal-table {
            width: 100%;
            border-collapse: collapse;
            color: #fff;
        }

        .terminal-table th {
            padding: 14px 16px;
            font-size: 0.7rem;
            color: #64748b;
            text-transform: uppercase;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(0, 0, 0, 0.1);
            font-weight: 800;
        }

        .terminal-table td {
            padding: 12px 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        }

        .terminal-input, .terminal-select {
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 10px !important;
            color: #fff !important;
            padding: 8px 12px !important;
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            width: 100%;
            transition: all 0.2s;
        }

        .terminal-input:focus, .terminal-select:focus {
            border-color: var(--accent-blue) !important;
            background: rgba(255, 255, 255, 0.07) !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            outline: none;
        }

        .terminal-select option {
            background: #0f172a;
            color: #fff;
        }

        .btn-remove-leg {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 1.2rem;
            line-height: 1;
        }

        .btn-remove-leg:hover {
            background: #ef4444;
            color: #fff;
            transform: scale(1.1);
        }

        .fo-legs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .fo-legs-table th {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 600;
            text-align: center;
            padding: 12px;
            border-bottom: 1px solid #eef0f4;
        }

        .fo-legs-table td {
            padding: 12px 6px;
            vertical-align: middle;
        }

        .leg-row {
            transition: background 0.2s;
        }

        .leg-row:hover {
            background: #fcfcfd;
        }

        .btn-remove-leg {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #1a1b25;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            line-height: 1;
        }

        .leg-input {
            border: 1px solid #eef0f4;
            border-radius: 24px;
            padding: 8px 12px;
            font-size: 0.85rem;
            width: 100%;
            text-align: center;
            font-weight: 600;
            background: #fff;
        }

        .leg-input:focus {
            border-color: #3b82f6;
            outline: none;
        }

        .leg-input.ce-pe {
            cursor: pointer;
            border-color: #10b981;
            color: #10b981;
            background: #f0fdf4;
        }

        .leg-input.ce-pe.is-pe {
            border-color: #ef4444;
            color: #ef4444;
            background: #fef2f2;
        }

        .leg-input.premium {
            border-color: #3b82f6;
            color: #1e3a8a;
        }

        .payoff-graph-container {
            padding: 0;
            min-height: 300px;
            border-bottom: 1px solid #eef0f4;
            position: relative;
        }

        .strategy-overview {
            padding: 24px;
        }

        .overview-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed #eef0f4;
            font-size: 0.95rem;
            color: #6b7280;
            font-weight: 500;
        }

        .overview-row:last-child {
            border-bottom: none;
        }

        .overview-value {
            font-weight: 700;
            color: #1a1b25;
        }

        .val-positive {
            color: #10b981;
        }

        .val-negative {
            color: #ef4444;
        }

        /* Top Level Switcher */
        .type-switcher {
            display: flex;
            background: #f1f5f9;
            border-radius: 12px;
            padding: 6px;
            margin-bottom: 24px;
            gap: 8px;
        }

        .type-switch-btn {
            flex: 1;
            text-align: center;
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .type-switch-btn.active {
            background: #fff;
            color: #1a1a2e;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-color: #e2e8f0;
        }

        .type-switch-btn:hover:not(.active) {
            background: #e2e8f0;
        }
    </style>
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-9 col-xl-8">
            <form action="{{ route('stock-signals.store') }}" method="POST" id="signalForm">
                @csrf

                <input type="hidden" name="type" id="typeInput" value="{{ old('type', 'buy') }}">
                <input type="hidden" name="asset_type" id="assetTypeInput" value="equity">

                <div class="premium-container">
                    <div class="signal-card mb-4">
                        <!-- Premium Terminal Header -->
                        <div class="signal-header">
                            <div class="d-flex justify-content-between align-items-start gap-4">
                                <div class="flex-grow-1">
                                    <div class="company-name mb-1">
                                        <span id="displayTitle">Company Name</span>
                                        <span class="company-symbol" id="displaySymbol">SYMBOL</span>
                                        <span class="live-pulse"></span>
                                    </div>
                                    <div class="live-price-area">
                                        <div class="live-price" id="livePrice">₹ --.--</div>
                                        <div class="price-change" id="priceChange">
                                            <span id="priceChangeAmt">--</span> (<span id="priceChangePct">--%</span>)
                                        </div>
                                    </div>
                                    <div style="font-size: 0.8rem; color: #64748b; margin-top: 8px;" id="lastUpdated"
                                        class="fw-medium">
                                        <i class="ti ti-info-circle me-1"></i>Type an NSE Symbol to fetch live data
                                    </div>
                                </div>
                                <div class="search-box-wrapper" style="width: 380px;">
                                    <label class="section-label mb-2">Search Symbol & Type</label>
                                    <div class="d-flex gap-2">
                                        <div class="flex-grow-1 position-relative" id="symbolInputContainer">
                                            <div class="input-group-premium has-icon" id="equitySearchWrapper">
                                                <i class="ti ti-search input-icon" style="font-size: 1.2rem;"></i>
                                                <input type="text" name="symbol" id="symbolInput"
                                                    class="premium-input text-uppercase"
                                                    placeholder="Stock/Index name..." value="{{ old('symbol') }}"
                                                    autocomplete="off">
                                                <div id="searchResults" class="autocomplete-results"></div>
                                            </div>
                                            <div id="foSearchWrapper" class="d-none">
                                                <select id="foIndexSelect" class="premium-input text-uppercase fw-bold d-none">
                                                    <option value="NIFTY">NIFTY 50</option>
                                                    <option value="BANKNIFTY">BANK NIFTY</option>
                                                    <option value="FINNIFTY">FIN NIFTY</option>
                                                    <option value="MIDCPNIFTY">MIDCAP NIFTY</option>
                                                    <option value="NIFTYNXT50">NIFTY NEXT 50</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div id="foToggleWrapper" class="d-none">
                                            <select id="foAssetToggle" class="premium-input px-2 fw-bold" style="width:105px; height:50px; background:#f8fafc;">
                                                <option value="stock">STOCK</option>
                                                <option value="index">INDEX</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ── SECTION 1: SIGNAL TYPE ────────────────── -->
                        <div class="form-section">
                            <div class="section-label">Signal Execution Type</div>
                            <div class="type-switcher">
                                <div class="type-switch-btn active" id="btnTypeEquity" data-type="equity">
                                    <i class="ti ti-chart-bar-popular" style="font-size: 1.2rem;"></i>
                                    <span>Equity Spot</span>
                                </div>
                                <div class="type-switch-btn" id="btnTypeOption" data-type="option">
                                    <i class="ti ti-chart-arrows-vertical" style="font-size: 1.2rem;"></i>
                                    <span>Options (F&O)</span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="section-label mb-2">Display Headline</label>
                                <input type="text" name="title" id="titleInput" class="premium-input fw-bold"
                                    placeholder="e.g. RELIANCE INDUSTRIES BULLISH BREAKOUT" value="{{ old('title') }}"
                                    required>
                                @error('title')<div class="text-danger mt-1 small fw-bold">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4">
                                <label class="section-label mb-2">Rationale</label>
                                <textarea name="description" class="premium-input" rows="3"
                                    placeholder="Share your technical or fundamental rationale...">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        <!-- ── SECTION 2: EXECUTION DETAILS (EQUITY) ── -->
                        <div id="equityOpinionFields" class="form-section">
                            <div class="row g-4">
                                <div class="col-md-5">
                                    <div class="section-label">Recommendation</div>
                                    <div class="opinion-group">
                                        <div class="opinion-btn buy {{ old('type', 'buy') === 'buy' ? 'active' : '' }}"
                                            id="btnBuy">
                                            <i class="ti ti-trending-up me-2"></i>BUY
                                        </div>
                                        <div class="opinion-btn sell {{ old('type') === 'sell' ? 'active' : '' }}"
                                            id="btnSell">
                                            <i class="ti ti-trending-down me-2"></i>SELL
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="section-label">Entry Price & Order Type</div>
                                    <div class="d-flex gap-3 align-items-center">
                                        <div class="input-group-premium has-icon flex-grow-1">
                                            <span class="input-icon fw-bold">₹</span>
                                            <input type="number" step="0.01" name="buy_price_min" id="buyPriceMin"
                                                class="premium-input" placeholder="0.00" value="{{ old('buy_price_min') }}">
                                        </div>
                                        <div class="order-type-tabs" style="width: 200px;">
                                            <div class="order-tab" id="labelMarket" data-val="market">MARKET</div>
                                            <div class="order-tab active" id="labelLimit" data-val="limit">LIMIT</div>
                                            <input type="radio" name="order_type" value="market" id="radioMarket"
                                                class="d-none">
                                            <input type="radio" name="order_type" value="limit" id="radioLimit"
                                                class="d-none" checked>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4 mt-1">
                                <div class="col-md-3">
                                    <div class="section-label">Horizon</div>
                                    <select class="premium-input" name="hold_duration" id="targetPeriodSelect">
                                        <option value="Intraday" {{ old('hold_duration') == 'Intraday' ? 'selected' : '' }}>
                                            Intraday</option>
                                        <option value="Short Term" {{ old('hold_duration') == 'Short Term' ? 'selected' : '' }}>Short Term</option>
                                        <option value="Mid Term" {{ old('hold_duration') == 'Mid Term' ? 'selected' : '' }}>
                                            Mid Term</option>
                                        <option value="Long Term" {{ old('hold_duration') == 'Long Term' ? 'selected' : '' }}>
                                            Long Term</option>
                                    </select>
                                </div>
                                <div class="col-md-3" id="expiryDateField" style="display: none;">
                                    <div class="section-label">Target Exit Date</div>
                                    <input type="date" name="expiry_date" class="premium-input" value="{{ old('expiry_date') }}">
                                </div>
                                <div class="col-md-4">
                                    <div class="section-label">Target Zone</div>
                                    <div class="input-group-premium has-icon">
                                        <span class="input-icon">₹</span>
                                        <input type="number" step="0.01" name="target" id="targetPrice"
                                            class="premium-input" placeholder="Price" value="{{ old('target') }}">
                                        <span
                                            style="position:absolute; right:16px; top:50%; transform:translateY(-50%); font-size:0.7rem; font-weight:700; color:var(--success-green);"
                                            id="targetPct">0%</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="section-label">Risk Guard (SL)</div>
                                    <div class="input-group-premium has-icon">
                                        <span class="input-icon">₹</span>
                                        <input type="number" step="0.01" name="stoploss" id="stopLoss" class="premium-input"
                                            placeholder="Price" value="{{ old('stoploss') }}">
                                        <span
                                            style="position:absolute; right:16px; top:50%; transform:translateY(-50%); font-size:0.7rem; font-weight:700; color:var(--danger-red);"
                                            id="slPct">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- ── SECTION 3: F&O STRATEGY BUILDER ────── -->
                        <div id="optionFields" class="d-none">
                            <div class="form-section">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="section-label mb-0">Strategy Legs (F&O)</div>
                                    <button type="button" class="btn btn-sm btn-outline-primary fw-bold" id="btnAddLeg"
                                        style="border-radius: 8px;">
                                        <i class="ti ti-plus me-1"></i>ADD LEG
                                    </button>
                                </div>

                                <div class="terminal-card mb-4">
                                    <div class="terminal-header">
                                        <div class="terminal-title">Leg Execution Engine</div>
                                        <div class="d-flex gap-3 align-items-center">
                                            <div class="bull-bear-toggle" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 2px;">
                                                <div class="order-type-btn active" data-val="market" style="padding: 6px 14px; font-size: 0.7rem; cursor: pointer; border-radius: 15px; color: #fff; background: #3b82f6;">MARKET</div>
                                                <div class="order-type-btn" data-val="limit" style="padding: 6px 14px; font-size: 0.7rem; cursor: pointer; border-radius: 15px; color: #94a3b8;">LIMIT</div>
                                            </div>
                                            <select id="foExpirySelect" class="terminal-select" style="width: 140px; border-radius: 20px; font-size: 0.75rem !important;">
                                                <option value="">Select Expiry</option>
                                            </select>
                                            <div class="bull-bear-toggle">
                                                <div class="bull-bear-btn bullish active" id="btnFoBullish">BULLISH</div>
                                                <div class="bull-bear-btn bearish" id="btnFoBearish">BEARISH</div>
                                            </div>
                                            <button type="button" id="manualModeToggle" class="btn btn-sm btn-outline-warning" style="font-size: 0.7rem; padding: 4px 8px; border-radius: 12px;">
                                                <i class="ti ti-edit"></i> Manual Input
                                            </button>
                                        </div>
                                    </div>
                                    <div class="terminal-body">
                                        <table class="terminal-table">
                                            <thead>
                                                <tr>
                                                    <th width="40"></th>
                                                <th width="140">STRIKE PRICE</th>
                                                <th width="70">TYPE</th>
                                                <th width="70">B/S</th>
                                                <th width="70">LOT</th>
                                                <th width="70">LOT SIZE</th>
                                                <th width="120">ENTRY</th>
                                                <th width="110">TARGET</th>
                                                <th width="110">STOP LOSS</th>
                                                </tr>
                                            </thead>
                                            <tbody id="legsContainer">
                                                <!-- Legs will be injected here via JS -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="overview-card">
                                    <div class="section-label mb-3">Strategy Overview</div>
                                    <div class="row g-4">
                                        <div class="col-md-3 text-center">
                                            <div class="overview-label">Max Upside</div>
                                            <div class="overview-value val-positive so-upside">₹ 0.00</div>
                                        </div>
                                        <div class="col-md-3 text-center border-start">
                                            <div class="overview-label">Max Downside</div>
                                            <div class="overview-value val-negative so-downside">₹ 0.00</div>
                                        </div>
                                        <div class="col-md-3 text-center border-start">
                                            <div class="overview-label">Risk/Reward</div>
                                            <div class="overview-value so-rr">0 : 1</div>
                                        </div>
                                        <div class="col-md-3 text-center border-start">
                                            <div class="overview-label">Est. Capital</div>
                                            <div class="overview-value so-capital">₹ 0</div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="legs" id="legsJsonData">
                            </div>
                        </div>

                        <!-- Payoff Graph & Stats -->
                        <div class="row m-0" style="border-top: 1px solid #eef0f4;">
                            <div class="col-md-7 payoff-graph-container">
                                <div id="payoffChart" style="margin-top:20px;"></div>
                            </div>
                            <div class="col-md-5 strategy-overview border-start">
                                <h5 style="font-weight: 700; color: #232267; margin-bottom: 24px;">Strategy Overview
                                </h5>
                                <div class="overview-row">
                                    <span>Max Upside</span>
                                    <span class="overview-value val-positive so-upside">₹ 0.00</span>
                                </div>
                                <div class="overview-row">
                                    <span>Max Downside</span>
                                    <span class="overview-value val-negative so-downside">₹ 0.00</span>
                                </div>
                                <div class="overview-row">
                                    <span>Max RR Ratio</span>
                                    <span class="overview-value so-rr">0 : 1</span>
                                </div>
                                <div class="overview-row mt-3" style="border-top: 2px solid #eef0f4; padding-top:16px;">
                                    <span style="font-weight: 600;">Est. Capital Required</span>
                                    <span class="overview-value so-capital" style="font-size: 1.1rem;">₹
                                        0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ── SECTION 4: ADDITIONAL CONTEXT ───────── -->
                    <div class="form-section">
                        <div class="section-label">Environment & Logistics</div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="section-label mb-2">Internal Segment Tag</label>
                                <input type="text" name="segment" id="segmentInput" class="premium-input"
                                    value="{{ old('segment', $category->name ?? '') }}" placeholder="e.g. NIFTY, BANKNIFTY">
                            </div>
                            <div class="col-md-6">
                                <label class="section-label mb-2">Exchange</label>
                                <select name="exchange" class="premium-input px-2">
                                    <option value="NSE" {{ old('exchange') == 'NSE' ? 'selected' : '' }}>NSE</option>
                                    <option value="BSE" {{ old('exchange') == 'BSE' ? 'selected' : '' }}>BSE</option>
                                    <option value="MCX" {{ old('exchange') == 'MCX' ? 'selected' : '' }}>MCX</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="date" value="{{ date('Y-m-d') }}">
                    </div>

                    <!-- ── FOOTER ACTIONS ────────────────────────── -->
                    <div class="p-4" style="background: #f8fafc;">
                        <button type="submit" class="btn-premium">
                            <i class="ti ti-rocket" style="font-size: 1.4rem;"></i>
                            PUBLISH SIGNAL NOW
                        </button>
                    </div>
                </div>
        </div>
        </form>
    </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('public/assets/js/plugins/apexcharts.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            let typingTimer;
            const $symbolInput = $('#symbolInput');
            const $searchResults = $('#searchResults');
            const $typeInput = $('#typeInput');
            let foLegs = [];
            let chart = null;
            let cachedStrikes = [];
            let cachedOptionData = [];
            let allSymbolsCache = [];
            let indexNamesCache = [];

            // ─── 0. AUTOMATION & UI LOGIC ──────────────────────
            function updateHeadline() {
                let symbol = $symbolInput.val().trim().toUpperCase();
                let assetType = $('#assetTypeInput').val();

                if (assetType === 'equity') {
                    let companyName = $('#displayTitle').text().trim();
                    let type = $typeInput.val().toUpperCase();
                    let entry = $('#buyPriceMin').val();
                    if (symbol && entry) {
                        let headline = `${companyName} (${symbol}) ${type} @ ${entry}`;
                        $('#titleInput').val(headline);
                    }
                } else {
                    // F&O Branding: Symbol + Expiry + Strike + Type
                    if (foLegs.length > 0) {
                        let firstLeg = foLegs[0];
                        let expiry = $('#foExpirySelect').val() || '';
                        if (symbol && firstLeg.strike && firstLeg.type) {
                            let headline = `${symbol} ${expiry} ${firstLeg.strike} ${firstLeg.type}`;
                            $('#titleInput').val(headline);
                        }
                    }
                }
            }

            $('#targetPeriodSelect').change(function() {
                if ($(this).val() === 'Intraday') {
                    $('#expiryDateField').hide();
                } else {
                    $('#expiryDateField').show();
                }
            });

            // Trigger on input changes for headline
            $symbolInput.on('change', updateHeadline);
            $('#buyPriceMin').on('input', updateHeadline);
            
            // ─── 1. CORE MARKET DATA FUNCTIONS ─────────────────

            function fetchLivePrice(setToMarket = false) {
                let symbol = $symbolInput.val().trim().toUpperCase();
                if (!symbol) return;

                $.ajax({
                    url: '{{ route("stockmarket.equity.price") }}',
                    data: { symbol: symbol },
                    success: function (res) {
                        if (res && res.lastPrice) {
                            $('#livePrice').text(`₹ ${res.lastPrice}`);

                            let chg = parseFloat(res.change);
                            let pchg = parseFloat(res.pChange);
                            let prefix = chg > 0 ? '+' : '';
                            let cssClass = chg >= 0 ? 'positive' : 'negative';
                            let arrow = chg >= 0 ? '▲' : '▼';

                            $('#priceChange').removeClass('positive negative').addClass(cssClass);
                            $('#priceChangeAmt').html(`${arrow} ${Math.abs(chg).toFixed(2)}`);
                            $('#priceChangePct').text(`${prefix}${pchg.toFixed(2)}%`);

                            let now = new Date();
                            let timeString = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                            $('#lastUpdated').text(`Last updated: ${timeString}`);

                            if (setToMarket) {
                                $('#buyPriceMin').val(res.lastPrice);
                                updatePcts();
                            }
                        }
                    }
                });
            }

            function fetchOptionChainStrikes(symbol, expiry = '') {
                if (!symbol) return;
                $('#legsContainer').html('<tr><td colspan="8" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Loading Option Chain...</td></tr>');

                $.ajax({
                    url: `/crm/stockmarket/proxy/option-chain/${symbol}`,
                    data: { expiry: expiry },
                    success: function (res) {
                        if (res && res.records) {
                            cachedStrikes = res.records.strikePrices || [];
                            cachedOptionData = res.records.data || [];
                            
                            // Populate Expiry Dropdown if not already set or if first load
                            if (!expiry && res.records.expiryDates && res.records.expiryDates.length > 0) {
                                let $exp = $('#foExpirySelect');
                                let current = $exp.val();
                                $exp.empty().append('<option value="">Select Expiry</option>');
                                res.records.expiryDates.forEach(d => {
                                    $exp.append(`<option value="${d}" ${d === current ? 'selected' : ''}>${d}</option>`);
                                });
                                if (!current) {
                                    $exp.val(res.records.expiryDates[0]);
                                    // Re-fetch for first expiry to be precise or just filter locally if backend returned all
                                    // Since backend filters if expiry is provided, we should re-fetch if we want to be exact,
                                    // but if we just want to show the first one, we can filter locally.
                                    // Let's re-fetch once to ensure cachedOptionData is filtered.
                                    fetchOptionChainStrikes(symbol, res.records.expiryDates[0]);
                                    return;
                                }
                            }

                            refreshLegLTPs();
                            renderLegs();
                        } else {
                            $('#legsContainer').html('<tr><td colspan="8" class="text-center py-4 text-warning">No Option Chain data found for this symbol.</td></tr>');
                            // Enable manual mode when no data is found
                            enableManualMode();
                        }
                    },
                    error: function () {
                        $('#legsContainer').html('<tr><td colspan="8" class="text-center py-4 text-danger">Failed to fetch Option Chain. Please check symbol or proxy.<br><small>Enabling manual input mode...</small></td></tr>');
                        // Enable manual mode when API fails
                        enableManualMode();
                    }
                });
            }

            $(document).on('change', '#foExpirySelect', function() {
                fetchOptionChainStrikes($symbolInput.val(), $(this).val());
                updateHeadline();
            });

            function fetchAllSymbols() {
                if (allSymbolsCache.length > 0) return;
                $.ajax({
                    url: '{{ route("stockmarket.proxy.all-symbols") }}',
                    success: function (res) {
                        allSymbolsCache = res;
                    }
                });
            }

            function fetchIndexNames() {
                if (indexNamesCache.length > 0) return;
                // Pre-populate with essential defaults if API is slow
                indexNamesCache = [
                    ['NIFTY', 'NIFTY 50'],
                    ['BANKNIFTY', 'BANK NIFTY'],
                    ['FINNIFTY', 'FIN NIFTY'],
                    ['MIDCPNIFTY', 'MIDCAP NIFTY'],
                    ['NIFTYNXT50', 'NIFTY NEXT 50'],
                    ['NIFTY_MID_SELECT', 'NIFTY MID SELECT'],
                    ['SENSEX', 'BSE SENSEX'],
                    ['BANKEX', 'BSE BANKEX'],
                    ['INDIAVIX', 'INDIA VIX']
                ];
                $.ajax({
                    url: '{{ route("stockmarket.proxy.index-names") }}',
                    success: function (res) {
                        if (res && res.stn) {
                            // Merge and ensure uniqueness
                            res.stn.forEach(pair => {
                                if (!indexNamesCache.find(i => i[0] === pair[0])) {
                                    indexNamesCache.push(pair);
                                }
                            });
                        }
                    }
                });
            }

            // ─── 2. SEARCH & AUTOCOMPLETE ──────────────────────

            $symbolInput.on('keyup', function (e) {
                clearTimeout(typingTimer);
                let val = $(this).val().trim();
                let assetType = $('#assetTypeInput').val();
                let foType = $('#foAssetToggle').val();

                // Update Badge UI immediately
                if (val) $('#displaySymbol').text(`(${val.toUpperCase()})`);

                if (val.length >= 1) {
                    if (assetType === 'option' && foType === 'index') {
                        // Local filtering for indices
                        let results = indexNamesCache.filter(pair => 
                            pair[0].toLowerCase().includes(val.toLowerCase()) || 
                            pair[1].toLowerCase().includes(val.toLowerCase())
                        );
                        $searchResults.empty();
                        if (results.length > 0) {
                            results.forEach(pair => {
                                $searchResults.append(`
                                    <div class="autocomplete-item" data-symbol="${pair[0]}" data-name="${pair[1]}">
                                        <span class="ac-symbol">${pair[0]}</span>
                                        <span class="ac-name">${pair[1]}</span>
                                    </div>
                                `);
                            });
                            $searchResults.show();
                        } else {
                            $searchResults.hide();
                        }
                    } else if (val.length >= 2) {
                        typingTimer = setTimeout(() => {
                            $.ajax({
                                url: '{{ route("stockmarket.equity.search") }}',
                                data: { q: val },
                                success: function (res) {
                                    $searchResults.empty();
                                    if (res && res.length > 0) {
                                        res.forEach(item => {
                                            $searchResults.append(`
                                                    <div class="autocomplete-item" data-symbol="${item.symbol}" data-name="${item.name}">
                                                        <span class="ac-symbol">${item.symbol} <small class="text-muted">(${item.exchange})</small></span>
                                                        <span class="ac-name">${item.name}</span>
                                                    </div>
                                                `);
                                        });
                                        $searchResults.show();
                                    } else {
                                        $searchResults.hide();
                                        fetchLivePrice();
                                    }
                                }
                            });
                        }, 400);
                    }
                } else {
                    $searchResults.hide();
                }
            });

            $(document).on('click', '.autocomplete-item', function () {
                let symbol = $(this).data('symbol');
                let name = $(this).data('name');

                $symbolInput.val(symbol);
                $('#displayTitle').text(name);
                $('#displaySymbol').text(`(${symbol})`);
                $('#titleInput').val(name);
                $searchResults.hide();

                fetchLivePrice(false);
                if ($('#optionFields').is(':visible')) fetchOptionChainStrikes(symbol);
            });

            $('#foAssetToggle').change(function () {
                let type = $(this).val();
                if (type === 'index') {
                    $('#equitySearchWrapper').removeClass('d-none');
                    $('#foIndexSelect').addClass('d-none'); // Using search for indices too per request
                    $symbolInput.attr('placeholder', 'Search Index (NIFTY, etc)...');
                    fetchIndexNames();
                } else {
                    $('#foIndexSelect').addClass('d-none');
                    $('#equitySearchWrapper').removeClass('d-none');
                    $symbolInput.attr('placeholder', 'Search Stock Symbol...');
                    fetchAllSymbols();
                }
                $symbolInput.val('');
                $('#displayTitle').text('Select Symbol');
                $('#displaySymbol').text('');
            });

            $('#foIndexSelect').on('change', function () {
                let val = $(this).val();
                if (val) {
                    $symbolInput.val(val);
                    $('#displayTitle').text(val);
                    $('#displaySymbol').text('');
                    fetchLivePrice(true);
                    fetchOptionChainStrikes(val);
                }
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('.search-box-wrapper').length) $searchResults.hide();
            });

            // ─── 3. UI TOGGLES ─────────────────────────────────

            $('#btnBuy').click(function () {
                $('.opinion-btn').removeClass('active');
                $(this).addClass('active');
                $typeInput.val('buy');
                updatePcts();
                updateHeadline();
                calculatePayoff();
            });

            $('#btnSell').click(function () {
                $('.opinion-btn').removeClass('active');
                $(this).addClass('active');
                $typeInput.val('sell');
                updatePcts();
                updateHeadline();
                calculatePayoff();
            });

            $('.type-switch-btn').click(function () {
                $('.type-switch-btn').removeClass('active');
                $(this).addClass('active');

                let type = $(this).data('type');
                $('#assetTypeInput').val(type);

                if (type === 'option') {
                    $('#equityOpinionFields').addClass('d-none');
                    $('#optionFields').removeClass('d-none');
                    $('#foSearchWrapper').removeClass('d-none');
                    $('#foToggleWrapper').removeClass('d-none');
                    
                    if ($('#foAssetToggle').val() === 'index') {
                        $('#equitySearchWrapper').addClass('d-none');
                        $('#foIndexSelect').removeClass('d-none');
                    } else {
                        $('#foIndexSelect').addClass('d-none');
                        $('#equitySearchWrapper').removeClass('d-none');
                    }

                    if (foLegs.length === 0) {
                        addLeg();
                        addLeg();
                    }
                    // Enable manual mode by default for custom strike input
                    enableManualMode();
                    renderLegs();
                    fetchOptionChainStrikes($symbolInput.val());
                } else {
                    $('#equityOpinionFields').removeClass('d-none');
                    $('#optionFields').addClass('d-none');
                    $('#foSearchWrapper').addClass('d-none');
                    $('#foToggleWrapper').addClass('d-none');
                    $('#equitySearchWrapper').removeClass('d-none');
                    $('#foIndexSelect').addClass('d-none');
                }
                calculatePayoff();
            });

            $('.order-tab').click(function () {
                $('.order-tab').removeClass('active');
                $(this).addClass('active');
                let val = $(this).data('val');
                if (val === 'market') {
                    $('#radioMarket').prop('checked', true);
                    $('#buyPriceMin').prop('readonly', true).css('background', '#f8fafc');
                    fetchLivePrice(true);
                } else {
                    $('#radioLimit').prop('checked', true);
                    $('#buyPriceMin').prop('readonly', false).css('background', 'white');
                }
            });

            // ─── 4. F&O STRATEGY LOGIC ─────────────────────────

            // Manual mode flag
            let isManualMode = false;

            function enableManualMode() {
                isManualMode = true;
                // Show manual mode toggle button
                $('#manualModeToggle').show();
                // Add a visual indicator for manual mode
                if (!$('#manualModeIndicator').length) {
                    $('.terminal-header').append('<div id="manualModeIndicator" class="badge bg-warning text-dark ms-3" style="font-size: 0.7rem; font-weight: 700;">MANUAL INPUT MODE</div>');
                }
                // Generate some common strike prices based on symbol
                generateCommonStrikes();
                // Render legs with manual input enabled
                renderLegs();
            }

            function generateCommonStrikes() {
                let symbol = $symbolInput.val().trim().toUpperCase();
                cachedStrikes = [];
                
                // Generate ATM and OTM strikes based on common ranges
                if (symbol.includes('NIFTY')) {
                    let base = [19000, 19100, 19200, 19300, 19400, 19500, 19600, 19700, 19800, 19900, 20000, 20100, 20200, 20300, 20400, 20500];
                    cachedStrikes = base;
                } else if (symbol.includes('BANKNIFTY')) {
                    let base = [44000, 44100, 44200, 44300, 44400, 44500, 44600, 44700, 44800, 44900, 45000, 45100, 45200, 45300, 45400, 45500];
                    cachedStrikes = base;
                } else if (symbol.includes('FINNIFTY')) {
                    let base = [19000, 19100, 19200, 19300, 19400, 19500, 19600, 19700, 19800, 19900, 20000, 20100, 20200, 20300, 20400, 20500];
                    cachedStrikes = base;
                } else {
                    // For stocks, generate a range around common price points
                    let base = [];
                    for (let i = 100; i <= 5000; i += 100) {
                        base.push(i);
                    }
                    cachedStrikes = base;
                }
            }

            function addLeg() {
                let defaultType = (foLegs.length % 2 === 0) ? 'CE' : 'PE';
                let symbol = $symbolInput.val().trim().toUpperCase();
                let lotSize = 1;
                
                if (symbol.includes('BANKNIFTY')) lotSize = 15;
                else if (symbol.includes('NIFTY')) lotSize = 25;
                else if (symbol.includes('FINNIFTY')) lotSize = 40;
                else if (symbol.includes('MIDCPNIFTY')) lotSize = 75;

                foLegs.push({
                    id: Math.random().toString(36).substr(2, 9),
                    strike: '', type: defaultType, action: 'buy', lot: 1, lotSize: lotSize, entry: '', target: '', sl: ''
                });
                renderLegs();
            }

            window.removeLeg = function (id) {
                foLegs = foLegs.filter(l => l.id !== id);
                renderLegs();
            }

            function renderLegs() {
                const container = $('#legsContainer');
                container.empty();

                let strikesHtml = '<option value="">Select Strike</option>';
                
                // In manual mode, allow custom strike input
                if (isManualMode) {
                    strikesHtml += '<option value="custom">-- Custom Strike --</option>';
                }
                
                cachedStrikes.forEach(s => { 
                    strikesHtml += `<option value="${s}">${s}</option>`; 
                });

                foLegs.forEach(leg => {
                    let typeColor = leg.type === 'CE' ? '#10b981' : '#ef4444';
                    let actionColor = leg.action === 'buy' ? '#10b981' : '#f43f5e';
                    let currentLtp = getLTP(leg.strike, leg.type);
                    let isLimit = $('.order-type-btn[data-val="limit"]').hasClass('active');

                    // Always show text input for strike in manual mode, or show dropdown in auto mode
                    let strikeInput = '';
                    if (isManualMode) {
                        strikeInput = `<input type="number" class="terminal-input leg-input" data-id="${leg.id}" data-field="strike" placeholder="Enter Strike Price" value="${leg.strike !== 'custom' ? leg.strike : ''}" style="background: #fff; color: #000;">`;
                    } else {
                        strikeInput = `<select class="terminal-select leg-input" data-id="${leg.id}" data-field="strike">
                            ${strikesHtml.replace(`value="${leg.strike}"`, `value="${leg.strike}" selected`)}
                        </select>`;
                    }

                    container.append(`
                        <tr class="align-middle" data-leg-id="${leg.id}">
                            <td class="text-center">
                                <button type="button" class="btn-remove-leg" onclick="removeLeg('${leg.id}')">×</button>
                            </td>
                            <td>
                                ${strikeInput}
                            </td>
                            <td>
                                <select class="terminal-select leg-input" data-id="${leg.id}" data-field="type" style="color:${typeColor}">
                                    <option value="CE" ${leg.type === 'CE' ? 'selected' : ''}>CE</option>
                                    <option value="PE" ${leg.type === 'PE' ? 'selected' : ''}>PE</option>
                                </select>
                            </td>
                            <td>
                                <select class="terminal-select leg-input" data-id="${leg.id}" data-field="action" style="color:${actionColor}">
                                    <option value="buy" ${leg.action === 'buy' ? 'selected' : ''}>BUY</option>
                                    <option value="sell" ${leg.action === 'sell' ? 'selected' : ''}>SELL</option>
                                </select>
                            </td>
                            <td><input type="number" class="terminal-input leg-input" data-id="${leg.id}" data-field="lot" value="${leg.lot}"></td>
                            <td><input type="number" class="terminal-input leg-input" data-id="${leg.id}" data-field="lotSize" value="${leg.lotSize || 1}"></td>
                             <td>
                                 <div class="d-flex flex-column gap-1">
                                     <input type="number" step="0.05" class="terminal-input leg-input" data-id="${leg.id}" data-field="entry" value="${leg.entry}" style="${(isLimit || isManualMode) ? 'background: #fff; color: #000;' : 'background: rgba(255,255,255,0.05); color: #fff; pointer-events: none; border: 1px solid rgba(255,255,255,0.1);'}" ${(isLimit || isManualMode) ? '' : 'readonly'}>
                                     <div class="d-flex justify-content-between align-items-center px-1">
                                         <span class="text-muted" style="font-size: 0.65rem;">LTP: <span class="ltp-val text-primary cursor-pointer" onclick="setLegEntry('${leg.id}', ${currentLtp || 0})" style="font-weight:700;">${currentLtp ? currentLtp.toFixed(2) : (isManualMode ? 'Manual' : '-')}</span></span>
                                     </div>
                                 </div>
                             </td>
                             <td>
                                 <div class="d-flex flex-column gap-1">
                                     <input type="number" step="0.05" class="terminal-input leg-input" data-id="${leg.id}" data-field="target" value="${leg.target || ''}" ${isManualMode ? '' : ''}>
                                     <div class="text-end px-1" style="font-size: 0.65rem;">
                                         ${(function(){ 
                                             let base = parseFloat(leg.entry) || currentLtp || 0;
                                             if (leg.target && base > 0) {
                                                 let p = ((leg.target - base) / base * 100).toFixed(1);
                                                 let color = p >= 0 ? '#10b981' : '#f43f5e';
                                                 return `<span style="color:${color}; font-weight:700;">${p >= 0 ? '+' : ''}${p}%</span>`;
                                             }
                                             return '';
                                         })()}
                                     </div>
                                 </div>
                             </td>
                             <td>
                                 <div class="d-flex flex-column gap-1">
                                     <input type="number" step="0.05" class="terminal-input leg-input" data-id="${leg.id}" data-field="sl" value="${leg.sl || ''}" ${isManualMode ? '' : ''}>
                                     <div class="text-end px-1" style="font-size: 0.65rem;">
                                         ${(function(){ 
                                             let base = parseFloat(leg.entry) || currentLtp || 0;
                                             if (leg.sl && base > 0) {
                                                 let p = ((leg.sl - base) / base * 100).toFixed(1);
                                                 let color = p >= 0 ? '#10b981' : '#f43f5e';
                                                 return `<span style="color:${color}; font-weight:700;">${p >= 0 ? '+' : ''}${p}%</span>`;
                                             }
                                             return '';
                                         })()}
                                     </div>
                                 </div>
                             </td>
                         </tr>
                    `);
                });
                $('#legsJsonData').val(JSON.stringify(foLegs));
                calculatePayoff();
            }

            $(document).on('change input', '.leg-input', function () {
                let id = $(this).data('id');
                let field = $(this).data('field');
                let val = $(this).val();
                let leg = foLegs.find(l => l.id == id);
                if (leg) {
                    leg[field] = val;
                    
                    // Handle custom strike input in manual mode
                    if (field === 'strike' && isManualMode) {
                        // Direct text input, no need to handle 'custom' option
                        // Just update the leg value and continue
                    }
                    
                    if (field === 'strike' || field === 'type') {
                        let isLimit = $('.order-type-btn[data-val="limit"]').hasClass('active');
                        if (!isLimit && !isManualMode) {
                            leg.entry = getLTP(leg.strike, leg.type) || leg.entry;
                        }
                        // Only re-render if not in manual mode or if field is type (not strike)
                        if (!isManualMode || field === 'type') {
                            renderLegs();
                        } else {
                            // For manual mode strike input, just update data without re-rendering
                            $('#legsJsonData').val(JSON.stringify(foLegs));
                            calculatePayoff();
                            // Update headline if it's the first leg
                            if (foLegs.indexOf(leg) === 0) updateHeadline();
                        }
                    } else if (field === 'lot' || field === 'lotSize' || field === 'entry' || field === 'target' || field === 'sl') {
                        // For number fields, don't re-render everything to maintain focus
                        $('#legsJsonData').val(JSON.stringify(foLegs));
                        calculatePayoff();
                        // Update headline if it's the first leg
                        if (foLegs.indexOf(leg) === 0) updateHeadline();
                    } else {
                        renderLegs();
                    }
                }
            });

            $(document).on('click', '.order-type-btn', function() {
                $('.order-type-btn').removeClass('active').css('color', '#94a3b8').css('background', 'transparent');
                $(this).addClass('active').css('color', '#fff').css('background', '#3b82f6');
                
                let isLimit = $(this).data('val') === 'limit';
                if (!isLimit && !isManualMode) {
                    refreshLegLTPs();
                }
                renderLegs();
            });

            // Add manual mode toggle button functionality
            $(document).on('click', '#manualModeToggle', function() {
                if (isManualMode) {
                    // Switch back to auto mode - try to fetch data again
                    isManualMode = false;
                    $('#manualModeIndicator').remove();
                    $('#manualModeToggle').hide();
                    fetchOptionChainStrikes($symbolInput.val(), $('#foExpirySelect').val());
                } else {
                    // Switch to manual mode
                    enableManualMode();
                }
            });

            function refreshLegLTPs() {
                foLegs.forEach(leg => {
                    if (leg.strike && (!leg.entry || leg.entry == 0)) {
                        leg.entry = getLTP(leg.strike, leg.type);
                    }
                });
            }

            function getLTP(strike, type) {
                if (!strike || !type || cachedOptionData.length === 0) return 0;
                let numStrike = parseFloat(strike);
                
                // 1. Try to find the record that matches BOTH strike and type for flat data
                let flatRecord = cachedOptionData.find(i => 
                    parseFloat(i.strikePrice || i.strike_price) === numStrike && 
                    (i.optionType === type || i.type === type)
                );
                if (flatRecord) return parseFloat(flatRecord.lastPrice || flatRecord.last_price || 0);

                // 2. Fallback to grouped record (where one strike record contains both CE and PE objects)
                let groupedRecord = cachedOptionData.find(i => parseFloat(i.strikePrice || i.strike_price) === numStrike);
                if (groupedRecord && groupedRecord[type]) {
                    return parseFloat(groupedRecord[type].lastPrice || groupedRecord[type].last_price || 0);
                }

                return 0;
            }

            window.setLegEntry = function(id, val) {
                if (!val || val == 0) return;
                let leg = foLegs.find(l => l.id == id);
                if (leg) {
                    leg.entry = val;
                    renderLegs();
                }
            };

            // ─── 5. ANALYTICS & STATS ──────────────────────────

            function updatePcts() {
                let entry = parseFloat($('#buyPriceMin').val());
                let target = parseFloat($('#targetPrice').val());
                let sl = parseFloat($('#stopLoss').val());
                let type = $typeInput.val();

                if (entry > 0) {
                    if (target > 0) {
                        let tPct = ((type === 'buy' ? target - entry : entry - target) / entry) * 100;
                        $('#targetPct').text(`${tPct > 0 ? '+' : ''}${tPct.toFixed(1)}%`).css('color', tPct > 0 ? '#10b981' : '#ef4444');
                    }
                    if (sl > 0) {
                        let sPct = ((type === 'buy' ? sl - entry : entry - sl) / entry) * 100;
                        $('#slPct').text(`${sPct > 0 ? '+' : ''}${sPct.toFixed(1)}%`).css('color', sPct > 0 ? '#10b981' : '#ef4444');
                    }
                }
            }

            $('#buyPriceMin, #targetPrice, #stopLoss').on('input', updatePcts);

            function calculatePayoff() {
                let profit = 0, loss = 0, capital = 0;
                let assetType = $('#assetTypeInput').val();

                if (assetType === 'equity') {
                    let entry = parseFloat($('#buyPriceMin').val()) || 0;
                    let target = parseFloat($('#targetPrice').val()) || 0;
                    let sl = parseFloat($('#stopLoss').val()) || 0;
                    let type = $typeInput.val();

                    if (entry > 0) {
                        if (type === 'buy') {
                            profit = target > 0 ? (target - entry) : 0;
                            loss = sl > 0 ? (entry - sl) : 0;
                        } else {
                            profit = target > 0 ? (entry - target) : 0;
                            loss = sl > 0 ? (sl - entry) : 0;
                        }
                        capital = entry; // per share
                    }
                } else {
                    let validLegs = foLegs.filter(l => parseFloat(l.strike) > 0 && parseFloat(l.entry) > 0);
                    validLegs.forEach(l => {
                        let entry = parseFloat(l.entry);
                        let target = parseFloat(l.target);
                        let sl = parseFloat(l.sl);

                        let p = 0;
                        let lo = 0;

                        if (!isNaN(target)) {
                            p = (target - entry) * (l.lot || 0) * (l.lotSize || 1);
                        }
                        if (!isNaN(sl)) {
                            lo = (entry - sl) * (l.lot || 0) * (l.lotSize || 1);
                        }

                        if (l.action === 'sell') { p = -p; lo = -lo; }
                        profit += p; loss += lo;
                        capital += (l.action === 'buy' ? entry * (l.lot || 0) * (l.lotSize || 1) : 150000 * (l.lot || 0));
                    });
                }

                $('.so-upside').text(`₹${profit.toFixed(2)}`).css('color', profit >= 0 ? '#10b981' : '#f43f5e');
                $('.so-downside').text(`₹${loss.toFixed(2)}`).css('color', loss > 0 ? '#f43f5e' : (loss < 0 ? '#10b981' : '#94a3b8'));
                $('.so-capital').text(`₹${capital.toLocaleString(undefined, {minimumFractionDigits: 2})}`);
                
                let rr = '—';
                if (loss !== 0 && profit !== 0) {
                    rr = Math.abs(profit / loss).toFixed(1);
                }
                $('.so-rr').text(`1 : ${rr}`);

                // Generate Payoff Chart Data
                let chartData = [];
                if (capital > 0) {
                    // Simple payoff curve visualized: [SL, Entry, Target]
                    let entry = assetType === 'equity' ? parseFloat($('#buyPriceMin').val()) : 0;
                    if (assetType ==='equity' && entry > 0) {
                        let sl = parseFloat($('#stopLoss').val()) || entry * 0.95;
                        let target = parseFloat($('#targetPrice').val()) || entry * 1.1;
                        let type = $typeInput.val();
                        
                        // range: 10 steps from min of SL/Target to max of SL/Target
                        let min = Math.min(sl, target, entry) * 0.98;
                        let max = Math.max(sl, target, entry) * 1.02;
                        let step = (max - min) / 10;
                        for (let x = min; x <= max; x += step) {
                            let y = (type === 'buy' ? x - entry : entry - x);
                            chartData.push({x: x.toFixed(1), y: parseFloat(y.toFixed(2))});
                        }
                    } else if (foLegs.length > 0) {
                        // Options payoff is more complex, for now 11 points around center
                        let center = foLegs[0].strike ? parseFloat(foLegs[0].strike) : 0;
                        if (center > 0) {
                            for (let x = center * 0.9; x <= center * 1.1; x += center * 0.02) {
                                let y = 0;
                                foLegs.forEach(l => {
                                    let legEntry = parseFloat(l.entry);
                                    let valAtX = l.type === 'CE' ? Math.max(0, x - l.strike) : Math.max(0, l.strike - x);
                                    let pnl = (valAtX - legEntry) * (l.lot || 0) * (l.lotSize || 1);
                                    if (l.action === 'sell') pnl = -pnl;
                                    y += pnl;
                                });
                                chartData.push({x: x.toFixed(0), y: parseFloat(y.toFixed(2))});
                            }
                        }
                    }
                    if (chart) {
                        let yMax = Math.max(...chartData.map(d => d.y), 10);
                        let yMin = Math.min(...chartData.map(d => d.y), -10);
                        let range = yMax - yMin;
                        let zeroPos = range === 0 ? 50 : ((yMax / range) * 100);

                        chart.updateOptions({
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    colorStops: [
                                        { offset: 0, color: '#10b981', opacity: 0.4 },
                                        { offset: zeroPos, color: '#10b981', opacity: 0.2 },
                                        { offset: zeroPos, color: '#f43f5e', opacity: 0.2 },
                                        { offset: 100, color: '#f43f5e', opacity: 0.4 }
                                    ]
                                }
                            },
                            annotations: {
                                yaxis: [{
                                    y: 0,
                                    borderColor: 'rgba(255,255,255,0.2)',
                                    label: { text: 'Break-even', style: { color: '#fff', background: '#334155' } }
                                }]
                            }
                        });
                        chart.updateSeries([{ data: chartData }]);
                    }
                }
            }

            $('#buyPriceMin, #targetPrice, #stopLoss').on('input', calculatePayoff);

             var apexOptions = {
                series: [{ name: 'P&L', data: [] }],
                chart: { 
                    type: 'area', 
                    height: 260, 
                    toolbar: { show: false }, 
                    animations: { enabled: false },
                    background: 'transparent',
                    foreColor: '#94a3b8'
                },
                colors: ['#10b981'],
                stroke: { width: 3, curve: 'smooth' },
                xaxis: { 
                    type: 'numeric',
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: { 
                    labels: { formatter: v => '₹' + v.toFixed(0) },
                    tickAmount: 4
                },
                grid: {
                    borderColor: 'rgba(255,255,255,0.05)',
                    strokeDashArray: 4
                },
                dataLabels: { enabled: false },
                tooltip: { theme: 'dark' }
            };
            chart = new ApexCharts(document.querySelector("#payoffChart"), apexOptions);
            chart.render();

            $('#btnAddLeg').click(addLeg);

            // Init
            if ($symbolInput.val()) fetchLivePrice();
            updatePcts();
            calculatePayoff();
            $('.type-switch-btn.active').trigger('click');
            $('.order-tab.active').trigger('click');
        });
    </script>
@endpush
