<?php

namespace Workdo\StockMarket\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Workdo\StockMarket\Entities\StockSignal;
use Carbon\Carbon;

class AutomationCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stockmarket:check-signals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks live signals for target/stop-loss hit, intraday expiry, or target date expiry.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Starting Stock Signal Automation Check...");

        $liveSignals = StockSignal::where('status', 'live')->get();
        if ($liveSignals->isEmpty()) {
            $this->info("No live signals to process.");
            return 0;
        }

        $now = Carbon::now('Asia/Kolkata');
        $isMarketClosingTime = $now->format('H:i') >= '15:30';

        foreach ($liveSignals as $signal) {
            $this->line("Processing Signal: {$signal->title} ({$signal->symbol})");

            // 1. Check Intraday Expiry (3:30 PM)
            if ($signal->hold_duration === 'Intraday' && $isMarketClosingTime) {
                $this->closeSignal($signal, 'intraday_expiry', 'Market closing time reached (3:30 PM).');
                continue;
            }

            // 2. Check Target Date Expiry (Close at 3:30 PM on the selected date)
            if ($signal->expiry_date) {
                if (
                    $now->toDateString() > $signal->expiry_date->toDateString() ||
                    ($now->toDateString() == $signal->expiry_date->toDateString() && $isMarketClosingTime)
                ) {
                    $this->closeSignal($signal, 'date_expiry', 'Target exit date reached (Market closing time).');
                    continue;
                }
            }

            // 3. Fetch Live Price for Target/SL Monitoring
            $priceData = $this->getLivePrice($signal->symbol);
            if (!$priceData) {
                $this->error("Could not fetch live price for {$signal->symbol}. Skipping...");
                continue;
            }

            $currentPrice = $priceData['lastPrice'];

            // Check Target Hit
            if ($this->isTargetHit($signal, $currentPrice)) {
                $this->closeSignal($signal, 'target_hit', "Target price hit at ₹{$currentPrice}.", $currentPrice);
                continue;
            }

            // Check Stop-Loss Hit
            if ($this->isStopLossHit($signal, $currentPrice)) {
                $this->closeSignal($signal, 'sl_hit', "Stop-loss hit at ₹{$currentPrice}.", $currentPrice);
                continue;
            }
        }

        $this->info("Automation Check Completed.");
        return 0;
    }

    protected function getLivePrice($symbol)
    {
        try {
            $response = Http::timeout(5)->get("https://stockdata-lac.vercel.app/api/equity/{$symbol}");
            if ($response->successful()) {
                $json = $response->json();
                return [
                    'lastPrice' => $json['priceInfo']['lastPrice'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            \Log::error("AutomationCheck Error fetching price for {$symbol}: " . $e->getMessage());
        }
        return null;
    }

    protected function isTargetHit($signal, $price)
    {
        if (!$signal->target)
            return false;

        if ($signal->type === 'buy') {
            return $price >= $signal->target;
        } else {
            return $price <= $signal->target;
        }
    }

    protected function isStopLossHit($signal, $price)
    {
        if (!$signal->stoploss)
            return false;

        if ($signal->type === 'buy') {
            return $price <= $signal->stoploss;
        } else {
            return $price >= $signal->stoploss;
        }
    }

    protected function closeSignal($signal, $reason, $logMsg, $exitPrice = null)
    {
        $signal->status = 'closed';
        $signal->close_reason = $reason;
        $signal->exit_at = Carbon::now();
        if ($exitPrice) {
            $signal->exit_price = $exitPrice;
        }
        $signal->save();

        $this->warn("Signal Closed: [{$reason}] {$logMsg}");
        \Log::info("StockMarket Automation: Signal #{$signal->id} ({$signal->symbol}) closed. Reason: {$reason}. Info: {$logMsg}");
    }
}
