<?php

namespace Workdo\StockMarket\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\StockMarket\Entities\StockSignal;
use Workdo\StockMarket\Entities\StockAdjustment;
use Workdo\StockMarket\Entities\StockNotification;

class AdjustmentController extends Controller
{
    public function index($signalId)
    {
        if (!Auth::user()->isAbleTo('adjustment manage')) {
            return response()->json(['error' => 'Permission denied'], 403);
        }

        $adjustments = StockAdjustment::where('signal_id', $signalId)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($adjustments);
    }

    public function store(Request $request, $signalId)
    {
        if (!Auth::user()->isAbleTo('adjustment create')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'target' => 'nullable|numeric|min:0',
            'stoploss' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
        ]);

        $signal = StockSignal::findOrFail($signalId);

        $adjustment = StockAdjustment::create([
            'signal_id' => $signalId,
            'target' => $request->target ?? $signal->target,
            'stoploss' => $request->stoploss ?? $signal->stoploss,
            'quantity' => $request->quantity ?? $signal->quantity,
            'note' => $request->note,
            'created_by' => Auth::id(),
        ]);

        // Update the signal with the latest values
        if ($request->filled('target') || $request->filled('stoploss') || $request->filled('quantity')) {
            $signal->update(array_filter([
                'target' => $request->target,
                'stoploss' => $request->stoploss,
                'quantity' => $request->quantity,
            ]));
        }

        // Notify workspace users about adjustment
        StockNotification::notifyWorkspaceUsers($signalId, getActiveWorkSpace(), 'adjustment');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Adjustment added successfully!'),
                'adjustment' => $adjustment->load('creator'),
            ]);
        }

        return redirect()->back()->with('success', __('Adjustment added successfully!'));
    }
}
