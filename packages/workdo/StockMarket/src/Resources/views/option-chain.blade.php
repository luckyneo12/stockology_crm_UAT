@extends('layouts.main')

@section('page-title')
    {{ __('Option Chain') }}
@endsection

@section('page-breadcrumb')
    {{ __('Stock Market') }}, {{ __('Option Chain') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{ __('NSE Option Chain (Live)') }}</h5>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="input-group input-group-sm w-auto">
                            <span class="input-group-text bg-light"><i class="ti ti-search"></i></span>
                            <select id="indexSelect" class="form-select" style="min-width: 120px;">
                                <optgroup label="Indices">
                                    <option value="NIFTY">NIFTY</option>
                                    <option value="BANKNIFTY">BANKNIFTY</option>
                                    <option value="FINNIFTY">FINNIFTY</option>
                                    <option value="MIDCPNIFTY">MIDCPNIFTY</option>
                                </optgroup>
                                <optgroup label="Popular Stocks">
                                    <option value="RELIANCE">RELIANCE</option>
                                    <option value="HDFCBANK">HDFCBANK</option>
                                    <option value="ICICIBANK">ICICIBANK</option>
                                    <option value="INFY">INFY</option>
                                    <option value="TCS">TCS</option>
                                </optgroup>
                                <option value="CUSTOM">-- Custom Symbol --</option>
                            </select>
                        </div>
                        
                        <div id="customSymbolContainer" class="d-none">
                            <input type="text" id="customSymbol" class="form-control form-control-sm" placeholder="Enter Symbol (e.g. SBIN)" style="width: 150px; text-transform: uppercase;">
                        </div>

                        <select id="expirySelect" class="form-select w-auto">
                            <option value="">{{ __('Loading Expiry...') }}</option>
                        </select>
                        
                        <button id="refreshDataBtn" class="btn btn-primary btn-sm">
                            <i class="ti ti-refresh"></i> {{ __('Refresh') }}
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div id="loader" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">{{ __('Fetching live option chain data...') }}</p>
                    </div>

                    <div id="errorAlert" class="alert alert-danger d-none"></div>

                    <div id="dataContainer" class="d-none">
                        <div class="d-flex justify-content-between mb-3">
                            <p class="mb-0 text-muted"><strong>Underlying Value: </strong> <span
                                    id="underlyingValue"></span></p>
                            <p class="mb-0 text-muted"><strong>Timestamp: </strong> <span id="timestamp"></span></p>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center table-hover" id="optionChainTable">
                                <thead class="table-light">
                                    <tr class="text-uppercase">
                                        <th colspan="10" class="bg-success text-white">{{ __('CALLS') }}</th>
                                        <th class="bg-dark text-white">{{ __('STRIKE') }}</th>
                                        <th colspan="10" class="bg-danger text-white">{{ __('PUTS') }}</th>
                                    </tr>
                                    <tr class="small-header">
                                        <!-- Calls -->
                                        <th>OI</th>
                                        <th>Chng OI</th>
                                        <th>Vol</th>
                                        <th>IV</th>
                                        <th>LTP</th>
                                        <th>Chng</th>
                                        <th>B Qty</th>
                                        <th>Bid</th>
                                        <th>Ask</th>
                                        <th>A Qty</th>

                                        <!-- Strike -->
                                        <th class="bg-dark text-white">Strike</th>

                                        <!-- Puts -->
                                        <th>B Qty</th>
                                        <th>Bid</th>
                                        <th>Ask</th>
                                        <th>A Qty</th>
                                        <th>Chng</th>
                                        <th>LTP</th>
                                        <th>IV</th>
                                        <th>Vol</th>
                                        <th>Chng OI</th>
                                        <th>OI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Rows populated via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <style>
        .itm-call {
            background-color: rgba(40, 167, 69, 0.08) !important;
        }

        .itm-put {
            background-color: rgba(220, 53, 69, 0.08) !important;
        }

        .strike-cell {
            font-weight: bold;
            background-color: #343a40 !important;
            color: #fff !important;
        }

        #optionChainTable th,
        #optionChainTable td {
            font-size: 11px;
            padding: 4px 2px;
            vertical-align: middle;
        }

        .small-header th {
            font-weight: 600;
            background: #f1f3f5;
        }

        .text-success {
            color: #28a745 !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }
    </style>
    <script>
        let fullData = null;
        let currentExpiry = null;

        function fetchOptionChain(symbol, expiry = null) {
            $('#loader').removeClass('d-none');
            $('#dataContainer').addClass('d-none');
            $('#errorAlert').addClass('d-none');

            let url = `/crm/stockmarket/proxy/option-chain/${symbol}`;
            if (expiry) {
                url += `?expiry=${expiry}`;
            }

            $.ajax({
                url: url,
                method: 'GET',
                success: function (response) {
                    if (response.error) {
                        $('#loader').addClass('d-none');
                        $('#errorAlert').removeClass('d-none').text(response.error);
                        return;
                    }

                    fullData = response;
                    const records = response.records || response;

                    $('#underlyingValue').text(records.underlyingValue || '-');
                    $('#timestamp').text(records.timestamp || '-');

                    const expiries = records.expiryDates || records.expiry || [];
                    const select = $('#expirySelect');

                    // If no data returned for this specific expiry, it might be an API limitation
                    const dataCount = (records.data ? records.data.length : 0);
                    console.log(`Option Chain: Received ${dataCount} rows for ${expiry || 'default'}`);

                    // Only rebuild dropdown if we don't have an expiry selected (Initial load or symbol change)
                    if (!expiry && expiries.length > 0) {
                        select.empty();
                        expiries.forEach((date, index) => {
                            select.append(new Option(date, date, index === 0, index === 0));
                        });
                        currentExpiry = expiries[0];
                    } else if (expiry) {
                        currentExpiry = expiry;
                    }
                    
                    if (dataCount === 0 && expiry) {
                        console.warn("No data returned for selected expiry. This might be an API limitation for this proxy.");
                    }

                    renderTable();
                    $('#loader').addClass('d-none');
                    $('#dataContainer').removeClass('d-none');
                },
                error: function (xhr) {
                    $('#loader').addClass('d-none');
                    const errorMsg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Error connecting to market server.';
                    $('#errorAlert').removeClass('d-none').text(errorMsg);
                }
            });
        }

        function renderTable() {
            const tbody = $('#optionChainTable tbody');
            tbody.empty();

            if (!fullData || !currentExpiry) {
                tbody.append('<tr><td colspan="21" class="text-center py-4">Waiting for data selection...</td></tr>');
                return;
            }

            const records = fullData.records || fullData;
            function parseNum(val) {
                if (!val || val === '-') return 0;
                return parseFloat(val.toString().replace(/,/g, '')) || 0;
            }

            const underlyingValue = parseNum(records.underlyingValue);
            let data = records.data || fullData.data || [];

            // Sort by strike price
            data.sort((a, b) => parseNum(a.strikePrice) - parseNum(b.strikePrice));

            if (data.length === 0) {
                const msg = currentExpiry ? `No strike data available for expiry: ${currentExpiry}.` : 'No strike data available.';
                tbody.append(`<tr><td colspan="21" class="text-center py-4 text-danger font-weight-bold">${msg}<br><small>The API proxy might not support this expiry date yet.</small></td></tr>`);
                return;
            }

            // Detect ATM
            let nearestDiff = Infinity;
            let nearestStrike = null;
            data.forEach(item => {
                const diff = Math.abs(parseNum(item.strikePrice) - underlyingValue);
                if (diff < nearestDiff) {
                    nearestDiff = diff;
                    nearestStrike = item.strikePrice;
                }
            });

            data.forEach(row => {
                const strike = parseNum(row.strikePrice);
                const CE = row.CE || {};
                const PE = row.PE || {};

                const isCallITM = strike < underlyingValue;
                const isPutITM = strike > underlyingValue;

                const ceClass = isCallITM ? 'itm-call' : '';
                const peClass = isPutITM ? 'itm-put' : '';
                const atmStyle = strike === nearestStrike ? 'style="border: 2px solid #333; font-weight:bold; background:#fffdec !important;"' : '';

                const tr = `
                                <tr ${atmStyle}>
                                    <!-- CALLS -->
                                    <td class="${ceClass}">${CE.openInterest || 0}</td>
                                    <td class="${ceClass} ${CE.changeinOpenInterest > 0 ? 'text-success' : 'text-danger'}">${CE.changeinOpenInterest || 0}</td>
                                    <td class="${ceClass}">${CE.totalTradedVolume || 0}</td>
                                    <td class="${ceClass}">${CE.impliedVolatility || 0}</td>
                                    <td class="${ceClass} fw-bold">${CE.lastPrice || 0}</td>
                                    <td class="${ceClass} ${CE.change > 0 ? 'text-success' : 'text-danger'}">${CE.change ? CE.change.toFixed(2) : '0.00'}</td>
                                    <td class="${ceClass}">${CE.bidQty || 0}</td>
                                    <td class="${ceClass}">${CE.bidprice || CE.bidPrice || 0}</td>
                                    <td class="${ceClass}">${CE.askPrice || CE.askprice || 0}</td>
                                    <td class="${ceClass}">${CE.askQty || 0}</td>

                                    <!-- STRIKE -->
                                    <td class="strike-cell">${strike}</td>

                                    <!-- PUTS -->
                                    <td class="${peClass}">${PE.bidQty || 0}</td>
                                    <td class="${peClass}">${PE.bidprice || PE.bidPrice || 0}</td>
                                    <td class="${peClass}">${PE.askPrice || PE.askprice || 0}</td>
                                    <td class="${peClass}">${PE.askQty || 0}</td>
                                    <td class="${peClass} ${PE.change > 0 ? 'text-success' : 'text-danger'}">${PE.change ? PE.change.toFixed(2) : '0.00'}</td>
                                    <td class="${peClass} fw-bold">${PE.lastPrice || 0}</td>
                                    <td class="${peClass}">${PE.impliedVolatility || 0}</td>
                                    <td class="${peClass}">${PE.totalTradedVolume || 0}</td>
                                    <td class="${peClass} ${PE.changeinOpenInterest > 0 ? 'text-success' : 'text-danger'}">${PE.changeinOpenInterest || 0}</td>
                                    <td class="${peClass}">${PE.openInterest || 0}</td>
                                </tr>
                            `;
                tbody.append(tr);
            });
        }

        function getSelectedSymbol() {
            const selectVal = $('#indexSelect').val();
            if (selectVal === 'CUSTOM') {
                return $('#customSymbol').val().toUpperCase().trim();
            }
            return selectVal;
        }

        $(document).ready(function () {
            // Initial fetch
            fetchOptionChain('NIFTY');

            // Symbol change
            $('#indexSelect').on('change', function () {
                const val = $(this).val();
                if (val === 'CUSTOM') {
                    $('#customSymbolContainer').removeClass('d-none');
                    $('#customSymbol').focus();
                } else {
                    $('#customSymbolContainer').addClass('d-none');
                    fetchOptionChain(val);
                }
            });

            // Handle custom symbol typing (with debounce)
            let typingTimer;
            $('#customSymbol').on('keyup', function (e) {
                clearTimeout(typingTimer);
                const sym = $(this).val().toUpperCase().trim();
                
                if (e.key === 'Enter') {
                    if (sym) fetchOptionChain(sym);
                } else {
                    typingTimer = setTimeout(() => {
                        if (sym && sym.length >= 2) fetchOptionChain(sym);
                    }, 1000);
                }
            });

            // Expiry change
            $('#expirySelect').on('change', function () {
                const symbol = getSelectedSymbol();
                const expiry = $(this).val();
                if (symbol) fetchOptionChain(symbol, expiry);
            });

            // Refresh button
            $('#refreshDataBtn').on('click', function () {
                const symbol = getSelectedSymbol();
                const expiry = $('#expirySelect').val();
                if (symbol) fetchOptionChain(symbol, expiry);
            });
        });
    </script>
@endpush