<?php

namespace Workdo\StockMarket\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\StockMarket\Entities\StockSignal;
use Workdo\StockMarket\Entities\StockCategory;
use Workdo\StockMarket\Entities\StockNotification;
use Workdo\StockMarket\Entities\StockAdjustment;
use Workdo\StockMarket\Entities\StockActivityLog;

class SignalController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->isAbleTo('signal manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $workspace = getActiveWorkSpace();

        $query = StockSignal::where('workspace', $workspace)->with(['category', 'creator']);

        // Tab filter
        $tab = $request->get('tab', 'live');
        if ($tab === 'live') {
            $query->where('status', 'live');
        } elseif ($tab === 'closed') {
            $query->where('status', 'closed');
        } elseif ($tab === 'mine') {
            $query->where('created_by', Auth::id());
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Type filter (intraday / longterm)
        if ($request->filled('hold_type')) {
            if ($request->hold_type === 'intraday') {
                $query->where('hold_duration', 'like', '%Intraday%');
            } elseif ($request->hold_type === 'longterm') {
                $query->where('hold_duration', 'not like', '%Intraday%');
            }
        }

        $signals = $query->orderBy('created_at', 'desc')->get();

        // For closed tab — group by date, calculate performance
        $closedByDate = [];
        $performanceData = [];
        if ($tab === 'closed') {
            $closedSignals = StockSignal::where('workspace', $workspace)
                ->where('status', 'closed')
                ->orderBy('exit_at', 'desc')
                ->get();

            foreach ($closedSignals as $signal) {
                $dateKey = optional($signal->exit_at)->format('d F Y') ?? $signal->date->format('d F Y');
                if (!isset($closedByDate[$dateKey])) {
                    $closedByDate[$dateKey] = [];
                }
                $closedByDate[$dateKey][] = $signal;
            }

            // Performance chart data (Total & Success)
            $totalCalls = StockSignal::where('workspace', $workspace)->where('status', 'closed')->count();

            // Success = Target Hit or Manual Close with Profit
            $positiveCalls = StockSignal::where('workspace', $workspace)->where('status', 'closed')
                ->where(function ($q) {
                    $q->where('close_reason', 'target_hit')
                        ->orWhere(function ($sq) {
                            $sq->where('type', 'buy')->whereRaw('exit_price >= buy_price_min');
                        })
                        ->orWhere(function ($sq) {
                            $sq->where('type', 'sell')->whereRaw('exit_price <= buy_price_min');
                        });
                })->count();

            $avgDuration = 0;
            if ($totalCalls > 0) {
                $durations = StockSignal::where('workspace', $workspace)
                    ->where('status', 'closed')
                    ->whereNotNull('exit_at')
                    ->selectRaw('DATEDIFF(exit_at, created_at) as duration')
                    ->pluck('duration');

                if ($durations->count() > 0) {
                    $avgDuration = round($durations->avg(), 1);
                }
            }

            // Monthly Breakdown stats
            $monthlyStats = StockSignal::where('workspace', $workspace)
                ->where('status', 'closed')
                ->selectRaw('MONTHNAME(exit_at) as month, YEAR(exit_at) as year, 
                            count(*) as total,
                            sum(case when close_reason = "target_hit" then 1 else 0 end) as target_hits,
                            sum(case when close_reason = "sl_hit" then 1 else 0 end) as sl_hits')
                ->groupBy('month', 'year')
                ->orderByRaw('MIN(exit_at) DESC')
                ->get();

            // Performance chart data (Last 20 Closed Signals P&L %)
            $chartRaw = StockSignal::where('workspace', $workspace)
                ->where('status', 'closed')
                ->whereNotNull('exit_at')
                ->orderBy('exit_at', 'asc')
                ->take(20)
                ->get();

            $chartData = $chartRaw->map(function ($s) {
                $entry = (float) $s->buy_price_min;
                $exit = (float) $s->exit_price;
                if ($entry <= 0)
                    return 0;
                $pnl = $s->type === 'buy' ? (($exit - $entry) / $entry) * 100 : (($entry - $exit) / $entry) * 100;
                return round($pnl, 2);
            })->toArray();

            $performanceData = [
                'total' => $totalCalls,
                'positive' => $positiveCalls,
                'percent' => $totalCalls > 0 ? round(($positiveCalls / $totalCalls) * 100, 2) : 0,
                'avg_duration' => $avgDuration,
                'monthly' => $monthlyStats,
                'chartData' => $chartData
            ];
        }

        $categories = StockCategory::where('workspace', $workspace)
            ->orWhere('created_by', creatorId())->get();

        return view('stockmarket::signals.index', compact(
            'signals',
            'tab',
            'categories',
            'closedByDate',
            'performanceData'
        ));
    }

    public function create()
    {
        if (!Auth::user()->isAbleTo('signal create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $workspace = getActiveWorkSpace();
        $categories = StockCategory::where('workspace', $workspace)
            ->orWhere('created_by', creatorId())->get();

        return view('stockmarket::signals.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isAbleTo('signal create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($request->has('legs') && is_string($request->legs)) {
            $request->merge(['legs' => json_decode($request->legs, true)]);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:buy,sell',
            'date' => 'required|date',
            'legs' => 'nullable|array',
        ]);

        // Basic validation for equity mode (if legs aren't present)
        if (empty($request->legs)) {
            $request->validate([
                'buy_price_min' => 'required|numeric|min:0',
            ]);
        }

        $signal = StockSignal::create([
            'title' => $request->title,
            'symbol' => strtoupper($request->symbol ?? ''),
            'exchange' => $request->exchange ?? 'NSE',
            'category_id' => $request->category_id,
            'type' => $request->type,
            'legs' => $request->legs, // JSON legs array
            'buy_price_min' => $request->buy_price_min,
            'buy_price_max' => $request->buy_price_max ?? $request->buy_price_min,
            'target' => $request->target,
            'stoploss' => $request->stoploss,
            'quantity' => $request->quantity,
            'min_amount' => $request->min_amount,
            'hold_duration' => $request->hold_duration,
            'description' => $request->description,
            'date' => $request->date,
            'expiry_date' => $request->expiry_date,
            'status' => 'live',
            'workspace' => getActiveWorkSpace(),
            'created_by' => Auth::id(),
        ]);

        // Notify all workspace users
        StockNotification::notifyWorkspaceUsers($signal->id, getActiveWorkSpace(), 'new_signal');

        StockActivityLog::create([
            'signal_id' => $signal->id,
            'user_id' => Auth::id(),
            'action' => 'Created',
            'details' => json_encode(['title' => $signal->title, 'type' => $signal->type]),
            'workspace_id' => getActiveWorkSpace(),
        ]);

        return redirect()->route('stock-signals.index')
            ->with('success', __('Stock signal published successfully!'));
    }

    public function show($id)
    {
        if (!Auth::user()->isAbleTo('signal show')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $signal = StockSignal::with(['category', 'creator', 'adjustments.creator'])->findOrFail($id);

        // Mark notifications as read for this user
        \Workdo\StockMarket\Entities\StockNotification::where('signal_id', $id)
            ->where('user_id', Auth::id())
            ->update(['is_read' => true]);

        return redirect()->route('stock-signals.index', ['open_signal' => $id]);
    }

    public function edit($id)
    {
        if (!Auth::user()->isAbleTo('signal edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $signal = StockSignal::findOrFail($id);
        $workspace = getActiveWorkSpace();
        $categories = StockCategory::where('workspace', $workspace)
            ->orWhere('created_by', creatorId())->get();

        return view('stockmarket::signals.edit', compact('signal', 'categories'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('signal edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($request->has('legs') && is_string($request->legs)) {
            $request->merge(['legs' => json_decode($request->legs, true)]);
        }

        $signal = StockSignal::findOrFail($id);

        $signal->update([
            'title' => $request->title,
            'symbol' => strtoupper($request->symbol ?? ''),
            'exchange' => $request->exchange ?? 'NSE',
            'category_id' => $request->category_id,
            'type' => $request->type,
            'buy_price_min' => $request->buy_price_min,
            'buy_price_max' => $request->buy_price_max ?? $request->buy_price_min,
            'target' => $request->target,
            'stoploss' => $request->stoploss,
            'quantity' => $request->quantity,
            'min_amount' => $request->min_amount,
            'hold_duration' => $request->hold_duration,
            'description' => $request->description,
            'date' => $request->date,
            'expiry_date' => $request->expiry_date,
            'legs' => $request->legs,
        ]);

        StockActivityLog::create([
            'signal_id' => $signal->id,
            'user_id' => Auth::id(),
            'action' => 'Updated',
            'details' => json_encode(['title' => $signal->title, 'type' => $signal->type]),
            'workspace_id' => getActiveWorkSpace(),
        ]);

        return redirect()->route('stock-signals.index')
            ->with('success', __('Signal updated successfully!'));
    }

    public function destroy($id)
    {
        if (!Auth::user()->isAbleTo('signal delete')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $signal = StockSignal::findOrFail($id);

        StockActivityLog::create([
            'signal_id' => $signal->id,
            'user_id' => Auth::id(),
            'action' => 'Deleted',
            'details' => json_encode(['title' => $signal->title, 'type' => $signal->type]),
            'workspace_id' => getActiveWorkSpace(),
        ]);

        $signal->delete();

        return redirect()->route('stock-signals.index')
            ->with('success', __('Signal deleted successfully!'));
    }

    public function autoCloseIntraday(Request $request)
    {
        if (!Auth::user()->isAbleTo('signal edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $count = StockSignal::autoCloseIntradaySignals();

        return redirect()->back()->with('success', __(':count intraday positions squared off successfully.', ['count' => $count]));
    }

    // Close a live signal
    public function close(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('signal edit')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $signal = StockSignal::findOrFail($id);
        $signal->update([
            'status' => 'closed',
            'exit_price' => $request->exit_price,
            'exit_at' => now(),
            'close_reason' => $request->close_reason ?? 'manual_close',
        ]);

        StockActivityLog::create([
            'signal_id' => $signal->id,
            'user_id' => Auth::id(),
            'action' => 'Closed',
            'details' => json_encode(['exit_price' => $request->exit_price, 'close_reason' => $request->close_reason ?? 'manual_close']),
            'workspace_id' => getActiveWorkSpace(),
        ]);

        StockNotification::notifyWorkspaceUsers($signal->id, getActiveWorkSpace(), 'closed');

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => __('Signal closed successfully!')]);
        }

        return redirect()->back()->with('success', __('Signal closed successfully!'));
    }

    // AJAX: Signal detail for drawer
    public function drawerData($id)
    {
        $workspace = getActiveWorkSpace();
        $signal = StockSignal::where('workspace', $workspace)
            ->with(['category', 'creator', 'adjustments.creator'])
            ->findOrFail($id);

        // Mark read
        StockNotification::where('signal_id', $id)
            ->where('user_id', Auth::id())
            ->update(['is_read' => true]);

        return response()->json([
            'signal' => $signal,
            'adjustments' => $signal->adjustments,
            'category' => $signal->category,
            'creator' => $signal->creator ? [
                'name' => $signal->creator->name,
                'avatar' => $signal->creator->avatar ?? null,
            ] : null,
        ]);
    }
}
