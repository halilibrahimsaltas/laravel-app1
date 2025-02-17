<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CoinGeckoService
{
    private $client;
    private $baseUrl = 'https://api.coingecko.com/api/v3/';

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    /**
     * Kripto para verilerini getirir
     *
     * @param string $coinId Kripto para birimi ID'si (örn: bitcoin)
     * @param array $currencies Para birimleri (örn: ['usd', 'try'])
     * @param bool $includeMarketCap Piyasa değeri bilgisini dahil et
     * @param bool $include24hVol 24 saatlik işlem hacmini dahil et
     * @param bool $include24hChange 24 saatlik değişimi dahil et
     * @return array
     */
    public function fetchCryptoData(
        string $coinId,
        array $currencies = ['usd', 'try'],
        bool $includeMarketCap = true,
        bool $include24hVol = true,
        bool $include24hChange = true
    ) {
        $cacheKey = "coingecko_" . $coinId . "_" . implode('_', $currencies);

        return Cache::remember($cacheKey, 300, function () use (
            $coinId,
            $currencies,
            $includeMarketCap,
            $include24hVol,
            $include24hChange
        ) {
            try {
                $response = $this->client->get('simple/price', [
                    'query' => [
                        'ids' => $coinId,
                        'vs_currencies' => implode(',', $currencies),
                        'include_market_cap' => $includeMarketCap,
                        'include_24hr_vol' => $include24hVol,
                        'include_24hr_change' => $include24hChange
                    ]
                ]);

                $data = json_decode($response->getBody(), true);

                if (empty($data)) {
                    throw new \Exception('Kripto para verisi bulunamadı.');
                }

                return $data;

            } catch (\Exception $e) {
                Log::error('CoinGecko API Hatası: ' . $e->getMessage(), [
                    'coin_id' => $coinId,
                    'currencies' => $currencies
                ]);
                throw $e;
            }
        });
    }

    /**
     * Birden fazla kripto para verisi getirir
     *
     * @param array $coinIds Kripto para birimi ID'leri
     * @param array $currencies Para birimleri
     * @return array
     */
    public function fetchMultipleCryptoData(array $coinIds, array $currencies = ['usd', 'try'])
    {
        return $this->fetchCryptoData(implode(',', $coinIds), $currencies);
    }

    /**
     * Trend olan kripto paraları getirir
     *
     * @return array
     */
    public function getTrendingCoins()
    {
        return Cache::remember('coingecko_trending', 3600, function () {
            try {
                $response = $this->client->get('search/trending');
                return json_decode($response->getBody(), true);
            } catch (\Exception $e) {
                Log::error('CoinGecko Trending API Hatası: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Belirli bir kripto paranın detaylı bilgilerini getirir
     *
     * @param string $coinId Kripto para birimi ID'si
     * @return array
     */
    public function getCoinDetails(string $coinId)
    {
        $cacheKey = "coingecko_details_" . $coinId;

        return Cache::remember($cacheKey, 3600, function () use ($coinId) {
            try {
                $response = $this->client->get("coins/{$coinId}", [
                    'query' => [
                        'localization' => 'false',
                        'tickers' => 'false',
                        'market_data' => 'true',
                        'community_data' => 'true',
                        'developer_data' => 'true'
                    ]
                ]);

                return json_decode($response->getBody(), true);
            } catch (\Exception $e) {
                Log::error('CoinGecko Coin Details API Hatası: ' . $e->getMessage(), [
                    'coin_id' => $coinId
                ]);
                throw $e;
            }
        });
    }
} 