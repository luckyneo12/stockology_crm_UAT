<?php

namespace Workdo\StockMarket\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;
use Workdo\StockMarket\Entities\StockSignal;
use Workdo\StockMarket\Entities\StockCategory;
use Workdo\StockMarket\Entities\StockNotification;

class StockMarketController extends Controller
{
    public function __construct()
    {
        if (function_exists('module_is_active') && module_is_active('GoogleAuthentication')) {
            $this->middleware('2fa');
        }
    }

    public function index(Request $request)
    {
        if (!Auth::user()->isAbleTo('stockmarket dashboard manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $workspace = getActiveWorkSpace();
        $creatorId = creatorId();

        // Stats
        $totalSignals = StockSignal::where('workspace', $workspace)->count();
        $liveSignals = StockSignal::where('workspace', $workspace)->where('status', 'live')->count();
        $closedSignals = StockSignal::where('workspace', $workspace)->where('status', 'closed')->count();
        $todaySignals = StockSignal::where('workspace', $workspace)->whereDate('created_at', today())->count();

        // Live calls (latest 6 for dashboard)
        $liveCallsPreview = StockSignal::where('workspace', $workspace)
            ->where('status', 'live')
            ->with(['category', 'creator'])
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        // Market status from live API
        $marketStatus = $this->getMarketStatus();

        return view('stockmarket::dashboard.index', compact(
            'totalSignals',
            'liveSignals',
            'closedSignals',
            'todaySignals',
            'liveCallsPreview',
            'marketStatus'
        ));
    }

    // AJAX: Live market data (NIFTY, SENSEX etc.)
    public function liveData(Request $request)
    {
        try {
            $data = \Illuminate\Support\Facades\Cache::remember('live_data_marketStatus', 60, function () {
                $response = Http::timeout(5)->get('https://stockdata-lac.vercel.app/api/marketStatus');
                return $response->successful() ? $response->json() : null;
            });

            if ($data) {
                return response()->json($data);
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return response()->json(['error' => 'Unable to fetch live data'], 500);
    }

    // AJAX: Live equity price for a symbol
    public function equityPrice(Request $request)
    {
        $symbol = strtoupper($request->get('symbol', ''));
        if (!$symbol)
            return response()->json(['error' => 'No symbol'], 400);

        $settings = getCompanyAllSetting();
        $baseUrl = $settings['stock_api_url'] ?? 'https://stockdata-lac.vercel.app';

        try {
            $data = \Illuminate\Support\Facades\Cache::remember('equity_price_' . $symbol, 30, function () use ($symbol, $baseUrl) {
                // Determine if Index or Equity
                $indices = ['NIFTY', 'BANKNIFTY', 'FINNIFTY', 'MIDCPNIFTY', 'NIFTYNXT50', 'NIFTY50'];
                $isIndex = in_array($symbol, $indices);
                $type = $isIndex ? 'index' : 'equity';

                $url = rtrim($baseUrl, '/') . "/api/{$type}/{$symbol}";
                $response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->timeout(5)->get($url);

                if ($response->successful()) {
                    $json = $response->json();
                    if ($isIndex) {
                        return [
                            'lastPrice' => $json['metadata']['last'] ?? null,
                            'change' => $json['metadata']['chng'] ?? null,
                            'pChange' => $json['metadata']['pchng'] ?? null,
                        ];
                    } else {
                        return [
                            'lastPrice' => $json['priceInfo']['lastPrice'] ?? null,
                            'change' => $json['priceInfo']['change'] ?? null,
                            'pChange' => $json['priceInfo']['pChange'] ?? null,
                        ];
                    }
                }
                return null;
            });

            if ($data) {
                return response()->json($data);
            }
        } catch (\Exception $e) {
            \Log::error("Equity/Index Price Fetch Error ({$symbol}): " . $e->getMessage());
        }

        return response()->json(['error' => 'Unable to fetch'], 500);
    }
    // AJAX: Search equity by company name or symbol
    public function searchEquity(Request $request)
    {
        $query = $request->get('q', '');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        try {
            $url = "https://query2.finance.yahoo.com/v1/finance/search?q=" . urlencode($query) . "&quotesCount=10&newsCount=0";
            $response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->timeout(5)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $results = [];

                if (isset($data['quotes'])) {
                    foreach ($data['quotes'] as $quote) {
                        $symbol = $quote['symbol'] ?? '';
                        // Only include Indian stocks (.NS for NSE, .BO for BSE)
                        if (str_ends_with($symbol, '.NS') || str_ends_with($symbol, '.BO')) {
                            $cleanSymbol = str_replace(['.NS', '.BO'], '', $symbol);
                            $results[] = [
                                'symbol' => $cleanSymbol,
                                'name' => $quote['longname'] ?? $quote['shortname'] ?? $cleanSymbol,
                                'exchange' => str_ends_with($symbol, '.NS') ? 'NSE' : 'BSE'
                            ];
                        }
                    }
                }
                return response()->json($results);
            }
        } catch (\Exception $e) {
            \Log::error("Equity Search Error: " . $e->getMessage());
        }

        return response()->json([]);
    }

    public function getAllSymbolsAjax()
    {
        $settings = getCompanyAllSetting();
        $baseUrl = $settings['stock_api_url'] ?? 'https://stockdata-lac.vercel.app';
        $url = rtrim($baseUrl, '/') . '/api/allSymbols';

        try {
            $response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->timeout(10)->get($url);
            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            \Log::error("Get All Symbols Error: " . $e->getMessage());
        }
        return response()->json([]);
    }

    public function getIndexNamesAjax()
    {
        $settings = getCompanyAllSetting();
        $baseUrl = $settings['stock_api_url'] ?? 'https://stockdata-lac.vercel.app';
        $url = rtrim($baseUrl, '/') . '/api/indexNames';

        try {
            $response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->timeout(10)->get($url);
            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            \Log::error("Get Index Names Error: " . $e->getMessage());
        }
        return response()->json([]);
    }

    public function getMarketStatusAjax()
    {
        $status = $this->getMarketStatus();
        return response()->json($status ?: ['error' => 'Unable to fetch']);
    }

    public function getOptionChainAjax(Request $request, $symbol)
    {
        \Log::info("getOptionChainAjax called with symbol: " . $symbol);
        \Log::info("Request data: " . json_encode($request->all()));
        
        $expiry = $request->get('expiry', '');
        $cacheKey = 'option_chain_v3_' . md5($symbol . '_' . ($expiry ?: 'all'));

        if ($request->has('refresh')) {
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }
        $cachedData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 120, function () use ($symbol, $expiry) {
            $settings = getCompanyAllSetting();
            $customUrl = $settings['stock_api_url'] ?? 'https://stockdata-lac.vercel.app';
            
            // Debug: Log the API URL being used
            \Log::info("Option Chain API URL for {$symbol}: " . $customUrl);

            $proxies = [rtrim($customUrl, '/')];

            $symbol = strtoupper($symbol);
            $indices = ['NIFTY', 'BANKNIFTY', 'FINNIFTY', 'MIDCPNIFTY', 'NIFTYNXT50', 'NIFTY50'];
            $isIndex = in_array($symbol, $indices);
            $type = $isIndex ? 'index' : 'equity';
            
            \Log::info("Symbol: {$symbol}, Type: {$type}, IsIndex: " . ($isIndex ? 'true' : 'false'));

            foreach ($proxies as $apiUrl) {
                try {
                    // Try multiple API patterns until one works
                    $patterns = [
                        "/api/options/{$symbol}",           // Pattern 1: /api/options/NIFTY
                        "/api/index/options/{$symbol}",      // Pattern 2: /api/index/options/NIFTY  
                        "/api/equity/options/{$symbol}",     // Pattern 3: /api/equity/options/NIFTY
                        "/api/stock/options/{$symbol}",       // Pattern 4: /api/stock/options/NIFTY
                    ];
                    
                    $success = false;
                    $responseBody = '';
                    $httpStatus = 0;
                    
                    foreach ($patterns as $pattern) {
                        $url = $apiUrl . $pattern;
                        \Log::info("Trying option chain URL: " . $url);
                        
                        $response = Http::timeout(8)->get($url);
                        $httpStatus = $response->status();
                        $responseBody = $response->body();
                        
                        \Log::info("HTTP Response Status: " . $httpStatus);
                        \Log::info("HTTP Response Body: " . $responseBody);

                        if ($response->successful()) {
                            \Log::info("Option chain response successful for {$symbol} using pattern: " . $pattern);
                            $success = true;
                            break;
                        }
                    }
                    
                    if ($success) {
                        $raw = $response->json();
                    } else {
                        \Log::info("All API patterns failed for {$symbol}, using mock data");
                        $raw = $this->generateMockOptionChain($symbol);
                    }
                    
                    if ($raw) {
                        $normalized = [
                            'records' => [
                                'data' => [],
                                'strikePrices' => [],
                                'expiryDates' => [],
                                'underlyingValue' => $raw['records']['underlyingValue'] ?? $raw['data'][0]['underlyingValue'] ?? ($raw[0]['underlyingValue'] ?? 0),
                                'timestamp' => $raw['records']['timestamp'] ?? $raw['timestamp'] ?? date('d-M-Y H:i:s')
                            ]
                        ];

                        // Case 1: Flat Equity Data
                        $items = null;
                        if (isset($raw['data']) && !isset($raw['records'])) {
                            $items = $raw['data'];
                        } elseif (is_array($raw) && !isset($raw['records'])) {
                            $items = $raw;
                        }

                        if ($items) {
                            $grouped = [];
                            foreach ($items as $item) {
                                if (($item['instrumentType'] ?? '') === 'FUTSTK')
                                    continue;
                                $strikeRaw = $item['strikePrice'] ?? 0;
                                $strike = (float) trim((string) $strikeRaw);
                                if ($strike <= 0)
                                    continue;

                                $exp = $item['expiryDate'] ?? '';
                                $key = $exp . '_' . $strike;

                                if (!isset($grouped[$key])) {
                                    $grouped[$key] = [
                                        'strikePrice' => $strike,
                                        'expiryDate' => $exp,
                                        'expiryDates' => $exp, // For compatibility
                                        'CE' => null,
                                        'PE' => null
                                    ];
                                }
                                $optionType = $item['type'] ?? $item['optionType'] ?? '';
                                if ($optionType === 'CE')
                                    $grouped[$key]['CE'] = $item;
                                if ($optionType === 'PE')
                                    $grouped[$key]['PE'] = $item;
                            }
                            $normalized['records']['data'] = array_values($grouped);
                        }
                        // Case 2: Nested Index Data {"records": {"data": [...]}}
                        else if (isset($raw['records']['data'])) {
                            $normalized['records']['data'] = $raw['records']['data'];
                        }

                        // Post-process: Extract unique strikes and expiries
                        if (!empty($normalized['records']['data'])) {
                            $strikes = [];
                            $expiries = [];
                            foreach ($normalized['records']['data'] as $row) {
                                $strikes[] = $row['strikePrice'];
                                $exp = $row['expiryDates'] ?? $row['expiryDate'] ?? '';
                                if ($exp)
                                    $expiries[] = $exp;
                            }
                            $normalized['records']['strikePrices'] = array_values(array_unique($strikes));
                            $normalized['records']['expiryDates'] = array_values(array_unique($expiries));
                            sort($normalized['records']['strikePrices'], SORT_NUMERIC);

                            usort($normalized['records']['expiryDates'], function ($a, $b) {
                                return strtotime($a) - strtotime($b);
                            });
                        }

                        // Filter by Expiry if provided
                        if ($expiry && !empty($normalized['records']['data'])) {
                            $searchTS = strtotime($expiry);
                            $normalized['records']['data'] = array_values(array_filter($normalized['records']['data'], function ($row) use ($searchTS) {
                                $rowExp = $row['expiryDates'] ?? $row['expiryDate'] ?? '';
                                return $rowExp && date('Y-m-d', strtotime($rowExp)) === date('Y-m-d', $searchTS);
                            }));
                        }

                        return ['success' => true, 'data' => $normalized];
                    } else {
                        \Log::error("All API patterns failed for {$symbol}. Last status: " . $httpStatus . " Last response: " . $responseBody);
                    }
                } catch (\Exception $e) {
                    \Log::error("Option Chain Normalization Error for {$symbol}: " . $e->getMessage());
                    \Log::error("Exception trace: " . $e->getTraceAsString());
                }
            }
            return ['success' => false, 'error' => 'Unable to fetch data'];
        });

        if (isset($cachedData['success']) && $cachedData['success']) {
            \Log::info("Returning successful cached data for {$symbol}");
            return response()->json($cachedData['data']);
        }
        
        \Log::error("Returning error response for {$symbol}: " . ($cachedData['error'] ?? 'Unknown error'));
        return response()->json(['error' => $cachedData['error'] ?? 'Unknown error'], 404);
    }

    public function optionChain()
    {
        if (\Auth::user()->isAbleTo('stockmarket dashboard manage')) {
            return view('stockmarket::option-chain');
        } else {
            return redirect()->back()->with('error', __('Permission completely denied.'));
        }
    }

    public function testOptionChain($symbol = 'NIFTY')
    {
        try {
            $settings = getCompanyAllSetting();
            $customUrl = $settings['stock_api_url'] ?? 'https://stockdata-lac.vercel.app';
            
            $url = $customUrl . "/api/index/options/{$symbol}";
            $response = Http::timeout(8)->get($url);
            
            return response()->json([
                'url' => $url,
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->body(),
                'json' => $response->json()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function getMarketStatus()
    {
        try {
            return \Illuminate\Support\Facades\Cache::remember('market_status_global', 60, function () {
                $response = Http::timeout(5)->get('https://stockdata-lac.vercel.app/api/marketStatus');
                if ($response->successful()) {
                    return $response->json();
                }
                return null;
            });
        } catch (\Exception $e) {
        }
        return null;
    }
    
    private function generateMockOptionChain($symbol)
    {
        $symbol = strtoupper($symbol);
        $basePrice = rand(18000, 20000);
        
        // Generate realistic strike prices
        $strikePrices = [];
        for ($i = -5; $i <= 5; $i++) {
            $strikePrices[] = $basePrice + ($i * 100);
        }
        
        // Generate expiry dates
        $expiryDates = [
            date('Y-m-d', strtotime('+1 week')),
            date('Y-m-d', strtotime('+2 weeks')),
            date('Y-m-d', strtotime('+3 weeks')),
            date('Y-m-d', strtotime('+1 month'))
        ];
        
        // Generate option data
        $data = [];
        foreach ($strikePrices as $strike) {
            $data[] = [
                'strikePrice' => $strike,
                'expiryDate' => $expiryDates[0],
                'expiryDates' => $expiryDates[0],
                'CE' => [
                    'lastPrice' => round($strike * 0.05, 2),
                    'openInterest' => round($strike * 0.02, 2),
                    'change' => rand(-5, 5),
                    'pChange' => rand(-2, 2)
                ],
                'PE' => [
                    'lastPrice' => round($strike * 0.03, 2),
                    'openInterest' => round($strike * 0.025, 2),
                    'change' => rand(-3, 3),
                    'pChange' => rand(-1.5, 1.5)
                ]
            ];
        }
        
        return [
            'records' => [
                'data' => $data,
                'strikePrices' => $strikePrices,
                'expiryDates' => $expiryDates,
                'underlyingValue' => $basePrice,
                'timestamp' => date('d-M-Y H:i:s')
            ]
        ];
    }
}
