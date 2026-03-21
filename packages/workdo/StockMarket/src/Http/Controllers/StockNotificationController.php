<?php

namespace Workdo\StockMarket\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\StockMarket\Entities\StockNotification;
use Workdo\StockMarket\Entities\StockSignal;
use Workdo\StockMarket\Entities\StockCategory;

class StockNotificationController extends Controller
{
    // AJAX: unread count for navbar bell
    public function unreadCount()
    {
        $count = StockNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->where('workspace', getActiveWorkSpace())
            ->count();

        return response()->json(['count' => $count]);
    }

    // AJAX: popup data — signals grouped by category
    public function popup()
    {
        $workspace = getActiveWorkSpace();

        $categories = StockCategory::where(function ($q) use ($workspace) {
            $q->where('workspace', $workspace)->orWhere('created_by', creatorId());
        })->get();

        $popupData = [];

        foreach ($categories as $category) {
            $signals = StockSignal::where('workspace', $workspace)
                ->where('category_id', $category->id)
                ->where('status', 'live')
                ->with('creator')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($s) {
                    $unread = StockNotification::where('signal_id', $s->id)
                        ->where('user_id', Auth::id())
                        ->where('is_read', false)
                        ->exists();
                    return [
                        'id' => $s->id,
                        'title' => $s->title,
                        'symbol' => $s->symbol,
                        'exchange' => $s->exchange,
                        'type' => $s->type,
                        'buy_price' => $s->buy_price_min,
                        'target' => $s->target,
                        'stoploss' => $s->stoploss,
                        'date' => $s->created_at->diffForHumans(),
                        'is_unread' => $unread,
                        'creator' => $s->creator ? $s->creator->name : 'Unknown',
                        'url' => route('stock-signals.show', $s->id),
                    ];
                });

            if ($signals->isNotEmpty()) {
                $popupData[] = [
                    'category' => $category->name,
                    'type' => $category->type,
                    'signals' => $signals,
                ];
            }
        }

        return response()->json($popupData);
    }

    // AJAX: mark all as read
    public function markRead()
    {
        StockNotification::where('user_id', Auth::id())
            ->where('workspace', getActiveWorkSpace())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}
