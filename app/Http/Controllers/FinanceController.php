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
    public function getGoldPrice()
    {
        try {
            $data = $this->alphaVantageService->getGoldPrice();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Döviz kurunu getirir
     */
    public function getExchangeRate(Request $request)
    {
        try {
            $fromCurrency = strtoupper($request->get('from_currency', 'USD'));
            $toCurrency = strtoupper($request->get('to_currency', 'TRY'));
            
            // Cache key oluştur
            $cacheKey = "exchange_rate:{$fromCurrency}:{$toCurrency}";
            
            return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($fromCurrency, $toCurrency) {
                Log::info('AlphaVantage API isteği yapılıyor', [
                    'from' => $fromCurrency,
                    'to' => $toCurrency,
                    'url' => $this->baseUrl
                ]);

                $response = Http::get($this->baseUrl, [
                    'function' => 'CURRENCY_EXCHANGE_RATE',
                    'from_currency' => $fromCurrency,
                    'to_currency' => $toCurrency,
                    'apikey' => $this->apiKey
                ]);

                Log::info('API yanıtı alındı', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);

                if ($response->failed()) {
                    throw new \Exception("API isteği başarısız: HTTP {$response->status()}");
                }

                $data = $response->json();

                if (isset($data['Error Message'])) {
                    throw new \Exception($data['Error Message']);
                }

                if (!isset($data['Realtime Currency Exchange Rate'])) {
                    throw new \Exception('Geçersiz API yanıtı: Döviz kuru verisi bulunamadı');
                }

                $exchangeData = $data['Realtime Currency Exchange Rate'];
                
                return [
                    'status' => 'success',
                    'data' => [
                        'from_currency' => $exchangeData['1. From_Currency Code'],
                        'to_currency' => $exchangeData['3. To_Currency Code'],
                        'rate' => (float) $exchangeData['5. Exchange Rate'],
                        'last_updated' => $exchangeData['6. Last Refreshed'],
                        'timezone' => $exchangeData['7. Time Zone'],
                        'bid_price' => (float) ($exchangeData['8. Bid Price'] ?? 0),
                        'ask_price' => (float) ($exchangeData['9. Ask Price'] ?? 0)
                    ]
                ];
            });

        } catch (\Exception $e) {
            Log::error('Döviz kuru hatası', [
                'error' => $e->getMessage(),
                'from' => $fromCurrency ?? null,
                'to' => $toCurrency ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Döviz kuru verisi alınamadı',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 