<?php

namespace App\Http\Controllers;

use App\Services\AlphaVantageService;
use App\Services\GoldApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FinanceController extends Controller
{
    private $alphaVantageService;
    private $goldApiService;
    private string $apiKey;
    private string $baseUrl;

    public function __construct(AlphaVantageService $alphaVantageService, GoldApiService $goldApiService)
    {
        $this->alphaVantageService = $alphaVantageService;
        $this->goldApiService = $goldApiService;
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
            $currency = strtoupper($request->get('currency', 'USD'));
            $date = $request->get('date'); // YYYYMMDD formatında, opsiyonel
            
            // Eğer tarih belirtilmişse tarihsel veri getir
            if ($date) {
                return response()->json(
                    $this->goldApiService->getHistoricalPrice('XAU', $currency, $date)
                );
            }
            
            // Gerçek zamanlı veri getir
            return response()->json(
                $this->goldApiService->getRealTimePrice('XAU', $currency)
            );

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