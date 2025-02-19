<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoldApiService
{
    private string $apiKey;
    private string $baseUrl;
    
    public function __construct()
    {
        $this->apiKey = config('services.goldapi.key');
        $this->baseUrl = 'https://www.goldapi.io/api';
        
        if (empty($this->apiKey)) {
            Log::error('GoldAPI.io API anahtarı bulunamadı');
            throw new \RuntimeException('API anahtarı yapılandırması eksik');
        }
    }
    
    /**
     * Gerçek zamanlı altın fiyatını getirir
     * 
     * @param string $symbol Sembol (XAU for Gold)
     * @param string $currency Para birimi (USD, EUR, TRY vb.)
     * @return array
     */
    public function getRealTimePrice(string $symbol = 'XAU', string $currency = 'USD'): array
    {
        try {
            // Cache key oluştur
            $cacheKey = "gold_price_{$symbol}_{$currency}";
            
            // Önbellekte varsa getir
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }
            
            // API'den veriyi çek
            $response = Http::withHeaders([
                'x-access-token' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->get("{$this->baseUrl}/{$symbol}/{$currency}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Troy ons fiyatını al
                $troyOuncePrice = $data['price'] ?? 0;
                
                // Gram fiyatını hesapla (1 troy ounce = 31.1034768 gram)
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
                        'from_currency' => $symbol,
                        'to_currency' => $currency,
                        'last_updated' => now()->toIso8601String(),
                        'source' => 'goldapi',
                        'raw_data' => $data // Ham veriyi de saklayalım
                    ]
                ];
                
                // 5 dakika önbellekle
                Cache::put($cacheKey, $result, now()->addMinutes(5));
                
                return $result;
            }
            
            throw new \Exception('API yanıtı geçerli veri içermiyor');
            
        } catch (\Exception $e) {
            Log::error('GoldAPI.io fiyat alma hatası', [
                'error' => $e->getMessage(),
                'symbol' => $symbol,
                'currency' => $currency
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Altın fiyatı alınamadı: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Belirli bir tarihteki altın fiyatını getirir
     * 
     * @param string $symbol Sembol (XAU for Gold)
     * @param string $currency Para birimi
     * @param string $date Tarih (YYYYMMDD formatında)
     * @return array
     */
    public function getHistoricalPrice(string $symbol = 'XAU', string $currency = 'USD', string $date = null): array
    {
        try {
            if (!$date) {
                $date = now()->format('Ymd');
            }
            
            $response = Http::withHeaders([
                'x-access-token' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->get("{$this->baseUrl}/{$symbol}/{$currency}/{$date}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'status' => 'success',
                    'data' => [
                        'price' => $data['price'] ?? 0,
                        'currency' => $currency,
                        'date' => $date,
                        'raw_data' => $data
                    ]
                ];
            }
            
            throw new \Exception('Tarihsel veri alınamadı');
            
        } catch (\Exception $e) {
            Log::error('GoldAPI.io tarihsel veri alma hatası', [
                'error' => $e->getMessage(),
                'symbol' => $symbol,
                'currency' => $currency,
                'date' => $date
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Tarihsel veri alınamadı: ' . $e->getMessage()
            ];
        }
    }
} 