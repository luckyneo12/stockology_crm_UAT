@extends('layouts.main')

@section('page-title'){{ __('Stock Market Settings') }}@endsection
@section('page-breadcrumb'){{ __('Stock Market') }}, {{ __('System Setup') }}@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card" style="border-radius:18px;">
                <div class="card-header fw-bold py-3 px-4"
                    style="background:linear-gradient(135deg,#0f1e3a,#1a3a5c); border-radius:18px 18px 0 0; color:#fff;">
                    <i class="ti ti-settings me-2"></i>{{ __('Stock Market — System Setup') }}
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('stockmarket.settings.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-semibold">{{ __('Enable Stock Notifications') }}</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="stock_notification_enabled" value="1"
                                    {{ ($settings['stock_notification_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label text-muted small">
                                    Notify all workspace users when a new signal is published
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">{{ __('Default Signal Type') }}</label>
                            <select name="stock_default_signal_type" class="form-select">
                                <option value="buy" {{ ($settings['stock_default_signal_type'] ?? 'buy') === 'buy' ? 'selected' : '' }}>🟢 BUY</option>
                                <option value="sell" {{ ($settings['stock_default_signal_type'] ?? '') === 'sell' ? 'selected' : '' }}>🔴 SELL</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">{{ __('Market Data Refresh Interval (seconds)') }}</label>
                            <input type="number" name="stock_refresh_interval" class="form-control"
                                value="{{ $settings['stock_refresh_interval'] ?? 30 }}" min="10" max="300">
                            <div class="form-text">How often live prices auto-refresh on dashboard (10–300 seconds)</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">{{ __('Stock Data API URL') }}</label>
                            <input type="url" name="stock_api_url" class="form-control"
                                value="{{ $settings['stock_api_url'] ?? 'https://stockdata-lac.vercel.app' }}">
                            <div class="form-text">Base URL for NSE live data API</div>
                        </div>

                        <button type="submit" class="btn fw-bold"
                            style="background:#2db57a; color:#fff; border-radius:10px; padding:10px 28px;">
                            <i class="ti ti-check me-1"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection