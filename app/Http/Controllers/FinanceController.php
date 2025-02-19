<?php

namespace App\Http\Controllers;

use App\Services\AlphaVantageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FinanceController extends Controller
{
    private $alphaVantageService;
    private string $apiKey;
    private string $baseUrl;

    public function __construct(AlphaVantageService $alphaVantageService)
    {
        $this->alphaVantageService = $alphaVantageService;
        $this->apiKey = config('services.alphavantage.key');
        $this->baseUrl = config('services.alphavantage.base_url');

        if (empty($this->apiKey)) {
            Log::error('AlphaVantage API anahtarı bulunamadı');
            throw new \RuntimeException('API anahtarı yapılandırması eksik');
        }
    }

    /**
     * Altın fiyatını getirir
     */
    public function getGoldPrice(Request $request)
    {
        try {
            $toCurrency = strtoupper($request->get('currency', 'USD'));
            
            // Cache key oluştur
            $cacheKey = "gold_price_{$toCurrency}";
            
            // Cache'den veriyi kontrol et
            if (Cache::has($cacheKey)) {
                return response()->json(Cache::get($cacheKey));
            }
            
            // GoldAPI.io API'sini kullanarak altın fiyatını alalım
            $response = Http::withHeaders([
                'x-access-token' => config('services.goldapi.key'),
                'Content-Type' => 'application/json'
            ])->get("https://www.goldapi.io/api/XAU/{$toCurrency}");

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['price'])) {
                    $troyOuncePrice = $data['price'];
                    $gramPrice = $troyOuncePrice / 31.1034768;
                    
                    $result = [
                        'status' => 'success',
                        'data' => [
                            'troy_ounce' => [
                                'price' => (float) $troyOuncePrice,
                                'unit' => 'troy ounce'
                            ],
                            'gram' => [
                                'price' => (float) number_format($gramPrice, 2, '.', ''),
                                'unit' => 'gram'
                            ],
                            'from_currency' => 'GOLD',
                            'to_currency' => $toCurrency,
                            'last_updated' => now()->toIso8601String(),
                            'source' => 'goldapi'
                        ]
                    ];
                    
                    // Sonucu 5 dakika cache'le
                    Cache::put($cacheKey, $result, now()->addMinutes(5));
                    
                    return response()->json($result);
                }
            }

            throw new \Exception('API yanıtı geçerli veri içermiyor');

        } catch (\Exception $e) {
            Log::error('Altın fiyatı alma hatası', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Altın fiyatı alınamadı'
            ], 500);
        }
    }

    /**
     * Döviz kurunu getirir
     */
    public function getExchangeRate(Request $request)
    {
        try {
            $fromCurrency = $request->get('from_currency', 'USD');
            $toCurrency = $request->get('to_currency', 'TRY');

            $response = Http::get('https://www.alphavantage.co/query', [
                'function' => 'CURRENCY_EXCHANGE_RATE',
                'from_currency' => strtoupper($fromCurrency),
                'to_currency' => strtoupper($toCurrency),
                'apikey' => config('services.alphavantage.key')
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['Realtime Currency Exchange Rate'])) {
                    $exchangeData = $data['Realtime Currency Exchange Rate'];
                    
                    return response()->json([
                        'status' => 'success',
                        'data' => [
                            'from' => [
                                'code' => $exchangeData['1. From_Currency Code'],
                                'name' => $exchangeData['2. From_Currency Name']
                            ],
                            'to' => [
                                'code' => $exchangeData['3. To_Currency Code'],
                                'name' => $exchangeData['4. To_Currency Name']
                            ],
                            'rate' => (float) $exchangeData['5. Exchange Rate'],
                            'last_updated' => $exchangeData['6. Last Refreshed'],
                            'timezone' => $exchangeData['7. Time Zone'],
                            'bid_price' => (float) $exchangeData['8. Bid Price'],
                            'ask_price' => (float) $exchangeData['9. Ask Price']
                        ]
                    ]);
                }
            }

            throw new \Exception('API yanıtı geçerli veri içermiyor');

        } catch (\Exception $e) {
            Log::error('Döviz kuru alma hatası', [
                'error' => $e->getMessage(),
                'from' => $fromCurrency,
                'to' => $toCurrency
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Döviz kuru alınamadı'
            ], 500);
        }
    }
} 