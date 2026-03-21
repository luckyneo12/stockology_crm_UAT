<?php

use Illuminate\Support\Facades\Route;
use Workdo\StockMarket\Http\Controllers\StockMarketController;
use Workdo\StockMarket\Http\Controllers\SignalController;
use Workdo\StockMarket\Http\Controllers\AdjustmentController;
use Workdo\StockMarket\Http\Controllers\StockCategoryController;
use Workdo\StockMarket\Http\Controllers\StockSettingController;
use Workdo\StockMarket\Http\Controllers\StockNotificationController;

Route::group(['middleware' => ['web', 'auth', 'verified', 'PlanModuleCheck:StockMarket']], function () {

    // Dashboard
    Route::get('dashboard/stockmarket', [StockMarketController::class, 'index'])->name('stockmarket.dashboard');

    // Live Market Data (AJAX Proxy)
    Route::get('stockmarket/proxy/market-status', [StockMarketController::class, 'getMarketStatusAjax'])->name('stockmarket.proxy.market-status');
    Route::get('stockmarket/proxy/option-chain/{symbol}', [StockMarketController::class, 'getOptionChainAjax'])->name('stockmarket.proxy.option-chain');
    Route::get('stockmarket/proxy/all-symbols', [StockMarketController::class, 'getAllSymbolsAjax'])->name('stockmarket.proxy.all-symbols');
    Route::get('stockmarket/proxy/index-names', [StockMarketController::class, 'getIndexNamesAjax'])->name('stockmarket.proxy.index-names');
    Route::get('stockmarket/live-data', [StockMarketController::class, 'liveData'])->name('stockmarket.live.data');
    Route::get('stockmarket/equity-price', [StockMarketController::class, 'equityPrice'])->name('stockmarket.equity.price');
    Route::get('stockmarket/search-equity', [StockMarketController::class, 'searchEquity'])->name('stockmarket.equity.search');

    // Option Chain View
    Route::get('stockmarket/option-chain', [StockMarketController::class, 'optionChain'])->name('stockmarket.option-chain');

    // Signals (Calls)
    Route::resource('stock-signals', SignalController::class);
    Route::post('stock-signals/auto-close-intraday', [SignalController::class, 'autoCloseIntraday'])->name('stock-signals.auto-close-intraday');
    Route::post('stock-signals/{id}/close', [SignalController::class, 'close'])->name('stock-signals.close');
    Route::get('stock-signals/{id}/drawer', [SignalController::class, 'drawerData'])->name('stock-signals.drawer');

    // Adjustments
    Route::get('stock-signals/{signalId}/adjustments', [AdjustmentController::class, 'index'])->name('stock-adjustments.index');
    Route::post('stock-signals/{signalId}/adjustments', [AdjustmentController::class, 'store'])->name('stock-adjustments.store');

    // Categories
    Route::resource('stock-categories', StockCategoryController::class)->except(['create', 'show', 'edit']);

    // Settings
    Route::get('stockmarket/settings', [StockSettingController::class, 'index'])->name('stockmarket.settings');
    Route::post('stockmarket/settings', [StockSettingController::class, 'store'])->name('stockmarket.settings.store');

    // Notifications (AJAX)
    Route::get('stockmarket/notifications/count', [StockNotificationController::class, 'unreadCount'])->name('stockmarket.notifications.count');
    Route::get('stockmarket/notifications/popup', [StockNotificationController::class, 'popup'])->name('stockmarket.notifications.popup');
    Route::post('stockmarket/notifications/read', [StockNotificationController::class, 'markRead'])->name('stockmarket.notifications.read');
});

// Test route without middleware for debugging
Route::get('test-stockmarket-option-chain/{symbol?}', [StockMarketController::class, 'testOptionChain']);
