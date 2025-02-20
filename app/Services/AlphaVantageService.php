<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AlphaVantageService
{
    private $client;
    private $apiKey;
    private $baseUrl = 'https://www.alphavantage.co/query';

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('ALPHA_VANTAGE_API_KEY');

        if (!$this->apiKey) {
            throw new \RuntimeException('ALPHA_VANTAGE_API_KEY çevre değişkeni tanımlanmamış.');
        }
    }

    /**
     * AlphaVantage API'den veri çeker
     *
     * @param array $config API isteği için yapılandırma
     * @param int $cacheMinutes Önbellek süresi (dakika)
     * @return array
     */
    public function fetchData(array $config, int $cacheMinutes = 5)
    {
        $cacheKey = 'alphavantage_' . md5(serialize($config));

        return Cache::remember($cacheKey, $cacheMinutes * 60, function () use ($config) {
            try {
                $response = $this->client->get($this->baseUrl, [
                    'query' => array_merge([
                        'apikey' => $this->apiKey
                    ], $config)
                ]);

                $data = json_decode($response->getBody(), true);

                if (isset($data['Error Message'])) {
                    throw new \Exception($data['Error Message']);
                }

                if (isset($data['Note'])) {
                    Log::warning('AlphaVantage API Limit Uyarısı: ' . $data['Note']);
                }

                return [
                    'status' => 'success',
                    'data' => $data
                ];

            } catch (\Exception $e) {
                Log::error('AlphaVantage API Hatası: ' . $e->getMessage(), [
                    'config' => $config
                ]);
                
                return [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        });
    }

    /**
     * Döviz kurunu getirir
     *
     * @param string $fromCurrency Kaynak para birimi
     * @param string $toCurrency Hedef para birimi
     * @return array
     */
    public function getExchangeRate(string $fromCurrency, string $toCurrency): array
    {
        $response = $this->fetchData([
            'function' => 'CURRENCY_EXCHANGE_RATE',
            'from_currency' => $fromCurrency,
            'to_currency' => $toCurrency
        ]);

        if ($response['status'] === 'error') {
            return $response;
        }

        return $response;
    }

    /**
     * Altın fiyatını TL cinsinden getirir
     *
     * @return array
     */
    public function getGoldPrice(): array
    {
        return $this->fetchData([
            'function' => 'CURRENCY_EXCHANGE_RATE',
            'from_currency' => 'XAU',
            'to_currency' => 'TRY'
        ]);
    }
} 