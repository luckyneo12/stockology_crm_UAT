{{--
Stock Market Notification Bell
Include this in the main navbar (layouts/main.blade.php or navbar partial)
Usage: @include('stockmarket::partials._stock_bell')
--}}
@if(module_is_active('StockMarket') && auth()->user()->isAbleTo('stock notification manage'))

    <div class="me-2 position-relative" id="stockBellWrapper" style="display:inline-flex; align-items:center;">
        {{-- Bell Button --}}
        <button id="stockBellBtn" onclick="toggleStockPopup()" class="btn p-0" title="{{ __('Stock Signals') }}"
            style="background:none; border:none; position:relative; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; transition:background 0.2s;"
            onmouseenter="this.style.background='rgba(45,181,122,0.1)'"
            onmouseleave="if(!document.getElementById('stockPopup').classList.contains('open')) this.style.background='none'">
            <i class="ti ti-chart-candle" style="font-size:1.3rem; color:#2db57a;"></i>
            <span id="stockBellBadge"
                style="position:absolute; top:4px; right:4px; background:#e05a5a; color:#fff; border-radius:50%; font-size:0.62rem; font-weight:700; min-width:16px; height:16px; display:none; align-items:center; justify-content:center; padding:0 3px;">
                0
            </span>
        </button>

        {{-- Popup --}}
        <div id="stockPopup" style="display:none; position:absolute; top:calc(100% + 10px); right:-10px; width:360px; background:#fff;
                           border-radius:16px; box-shadow:0 8px 40px rgba(0,0,0,0.18); z-index:1055;
                           border:1px solid #eef0f4; overflow:hidden; max-height:480px; flex-direction:column;">

            {{-- Popup Header --}}
            <div
                style="padding:14px 18px 10px; border-bottom:1px solid #f0f0f0; background:#0f1e3a; display:flex; justify-content:space-between; align-items:center;">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-chart-candle text-white" style="font-size:1rem;"></i>
                    <span class="fw-bold text-white" style="font-size:0.9rem;">Stock Signals</span>
                </div>
                <button onclick="markStockRead()"
                    style="background:#2db57a; border:none; color:#fff; font-size:0.7rem; border-radius:6px; padding:3px 10px; cursor:pointer; font-weight:600;">
                    Mark all read
                </button>
            </div>

            {{-- Category Tabs --}}
            <div id="stockPopupTabs"
                style="display:flex; padding:10px 14px 0; gap:8px; overflow-x:auto; flex-shrink:0; background:#fff; border-bottom:1px solid #f0f0f0;">
                <button class="stock-popup-tab active" data-cat="all" onclick="switchStockTab('all', this)"
                    style="border:none; background:#1a1a2e; color:#fff; border-radius:20px; padding:4px 14px; font-size:0.78rem; font-weight:600; cursor:pointer; white-space:nowrap;">
                    All
                </button>
            </div>

            {{-- Popup Body --}}
            <div id="stockPopupBody" style="flex:1; overflow-y:auto; padding:10px 0;">
                <div class="text-center py-4 text-muted" id="stockPopupLoader">
                    <div class="spinner-border spinner-border-sm text-success"></div>
                    <div class="small mt-1">Loading...</div>
                </div>
                <div id="stockPopupContent"></div>
            </div>

            {{-- Footer --}}
            <div style="padding:10px 14px; border-top:1px solid #f0f0f0; text-align:center;">
                <a href="{{ route('stock-signals.index') }}"
                    style="font-size:0.8rem; color:#2db57a; font-weight:600; text-decoration:none;">
                    View All Signals →
                </a>
            </div>
        </div>
    </div>

    <style>
        .stock-popup-tab {
            transition: all 0.18s;
        }

        .stock-popup-tab:not(.active) {
            background: #f0f0f0 !important;
            color: #555 !important;
        }

        .stock-popup-tab.active {
            background: #1a1a2e !important;
            color: #fff !important;
        }

        .stock-signal-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            cursor: pointer;
            transition: background 0.15s;
            text-decoration: none;
            color: inherit;
        }

        .stock-signal-row:hover {
            background: #f9fafb;
        }

        .stock-signal-row.unread {
            background: #f0faf5;
        }
    </style>

    <script>
        let _stockPopupData = [];
        let _stockPopupLoaded = false;
        let _stockPopupOpen = false;

        function toggleStockPopup() {
            const popup = document.getElementById('stockPopup');
            _stockPopupOpen = !_stockPopupOpen;
            popup.style.display = _stockPopupOpen ? 'flex' : 'none';
            const btn = document.getElementById('stockBellBtn');
            btn.style.background = _stockPopupOpen ? 'rgba(45,181,122,0.1)' : 'none';

            if (_stockPopupOpen && !_stockPopupLoaded) {
                loadStockPopup();
            }
        }

        // Close on outside click
        document.addEventListener('click', function (e) {
            const wrapper = document.getElementById('stockBellWrapper');
            if (_stockPopupOpen && wrapper && !wrapper.contains(e.target)) {
                _stockPopupOpen = false;
                document.getElementById('stockPopup').style.display = 'none';
                document.getElementById('stockBellBtn').style.background = 'none';
            }
        });

        function loadStockPopup() {
            fetch('{{ route('stockmarket.notifications.popup') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(data => {
                    _stockPopupData = data;
                    _stockPopupLoaded = true;
                    buildPopupTabs(data);
                    renderStockSignals(data, 'all');
                    document.getElementById('stockPopupLoader').style.display = 'none';
                })
                .catch(() => {
                    document.getElementById('stockPopupLoader').innerHTML = '<p class="text-danger small">Failed to load.</p>';
                });
        }

        function buildPopupTabs(data) {
            const tabsEl = document.getElementById('stockPopupTabs');
            let html = `<button class="stock-popup-tab active" data-cat="all" onclick="switchStockTab('all',this)"
                    style="border:none; background:#1a1a2e; color:#fff; border-radius:20px; padding:4px 14px; font-size:0.78rem; font-weight:600; cursor:pointer; white-space:nowrap;">All</button>`;
            data.forEach(cat => {
                html += `<button class="stock-popup-tab" data-cat="${cat.category}" onclick="switchStockTab('${cat.category}',this)"
                        style="border:none; background:#f0f0f0; color:#555; border-radius:20px; padding:4px 14px; font-size:0.78rem; font-weight:600; cursor:pointer; white-space:nowrap;">${cat.category}</button>`;
            });
            tabsEl.innerHTML = html;
        }

        function switchStockTab(cat, btn) {
            document.querySelectorAll('.stock-popup-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            renderStockSignals(_stockPopupData, cat);
        }

        function renderStockSignals(data, filter) {
            const body = document.getElementById('stockPopupContent');
            let html = '';
            let hasAny = false;

            data.forEach(group => {
                if (filter !== 'all' && group.category !== filter) return;
                if (!group.signals || !group.signals.length) return;
                hasAny = true;

                html += `<div style="padding:6px 14px 2px; font-size:0.68rem; font-weight:700; color:#aaa; text-transform:uppercase; letter-spacing:0.5px;">${group.category}</div>`;

                group.signals.forEach(s => {
                    const typeColor = s.type === 'buy' ? '#2db57a' : '#e05a5a';
                    html += `<a href="${s.url}" class="stock-signal-row ${s.is_unread ? 'unread' : ''}" onclick="document.getElementById('stockPopup').style.display='none'; _stockPopupOpen=false;">
                            <div style="width:36px; height:36px; border-radius:10px; background:${s.type === 'buy' ? '#e8fef3' : '#fef0f0'}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <span style="font-weight:700; color:${typeColor}; font-size:0.72rem;">${s.type.toUpperCase()}</span>
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div style="font-weight:600; font-size:0.82rem; color:#1a1a2e; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    ${s.title}
                                    ${s.is_unread ? '<span style="width:6px;height:6px;background:#e05a5a;border-radius:50%;display:inline-block;margin-left:4px;"></span>' : ''}
                                </div>
                                <div style="font-size:0.72rem; color:#888;">
                                    ${s.exchange} · Buy ₹${parseFloat(s.buy_price).toFixed(1)} → Target ₹${parseFloat(s.target || 0).toFixed(1)}
                                </div>
                            </div>
                            <div style="text-align:right; flex-shrink:0;">
                                <div style="font-size:0.68rem; color:#aaa;">${s.date || ''}</div>
                                <div style="font-weight:700; color:${typeColor}; font-size:0.75rem;">${s.type.toUpperCase()}</div>
                            </div>
                        </a>`;
                });
            });

            if (!hasAny) {
                html = '<div class="text-center py-4 text-muted small">No live signals in this category.</div>';
            }

            body.innerHTML = html;
        }

        function markStockRead() {
            fetch('{{ route('stockmarket.notifications.read') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            }).then(() => {
                document.getElementById('stockBellBadge').style.display = 'none';
                // Remove unread dots
                document.querySelectorAll('.stock-signal-row.unread').forEach(el => el.classList.remove('unread'));
            });
        }

        function refreshStockBadge() {
            fetch('{{ route('stockmarket.notifications.count') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(data => {
                    const badge = document.getElementById('stockBellBadge');
                    if (data.count > 0) {
                        badge.textContent = data.count > 99 ? '99+' : data.count;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                }).catch(() => { });
        }

        refreshStockBadge();
        setInterval(refreshStockBadge, 120000); // check every 2 minutes
    </script>
@endif