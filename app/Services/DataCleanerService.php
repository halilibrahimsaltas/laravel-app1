<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class DataCleanerService
{
    /**
     * Finansal veriyi temizler ve doğrular
     */
    public function cleanFinancialData(array $data): array
    {
        try {
            Log::info('Veri temizleme başladı', ['data' => $data]);

            // Veri tipine göre temizleme
            if (isset($data['troy_ounce'])) {
                return $this->cleanGoldData($data);
            } elseif (isset($data['from'], $data['to'], $data['rate'])) {
                return $this->cleanForexData($data);
            }

            throw new \Exception('Bilinmeyen veri formatı: ' . json_encode($data));
        } catch (\Exception $e) {
            Log::error('Veri temizleme hatası', [
                'error' => $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Altın verisini temizler
     */
    private function cleanGoldData(array $data): array
    {
        // Sayısal değerleri kontrol et
        $troyOuncePrice = $data['troy_ounce']['price'] ?? 0;
        $gramPrice = $data['gram']['price'] ?? 0;

        if (!is_numeric($troyOuncePrice) || !is_numeric($gramPrice)) {
            throw new \Exception('Geçersiz fiyat değeri');
        }

        // Negatif değerleri kontrol et
        if ($troyOuncePrice < 0 || $gramPrice < 0) {
            throw new \Exception('Negatif fiyat değeri');
        }

        // Para birimlerini kontrol et
        if (empty($data['to_currency'])) {
            throw new \Exception('Para birimi belirtilmemiş');
        }

        return [
            'troy_ounce' => [
                'price' => (float) $troyOuncePrice,
                'unit' => 'troy ounce'
            ],
            'gram' => [
                'price' => (float) $gramPrice,
                'unit' => 'gram'
            ],
            'from_currency' => $data['from_currency'] ?? 'GOLD',
            'to_currency' => $data['to_currency'],
            'last_updated' => $data['last_updated'] ?? now()->toIso8601String(),
            'source' => $data['source'] ?? 'unknown'
        ];
    }

    /**
     * Döviz verisini temizler
     */
    private function cleanForexData(array $data): array
    {
        // Sayısal değerleri kontrol et
        $rate = $data['rate'] ?? 0;
        $bidPrice = $data['bid_price'] ?? $rate;
        $askPrice = $data['ask_price'] ?? $rate;

        if (!is_numeric($rate) || !is_numeric($bidPrice) || !is_numeric($askPrice)) {
            throw new \Exception('Geçersiz kur değeri');
        }

        // Negatif değerleri kontrol et
        if ($rate < 0 || $bidPrice < 0 || $askPrice < 0) {
            throw new \Exception('Negatif kur değeri');
        }

        // Para birimlerini kontrol et
        if (empty($data['from']['code']) || empty($data['to']['code'])) {
            throw new \Exception('Para birimi belirtilmemiş');
        }

        return [
            'from' => [
                'code' => strtoupper($data['from']['code']),
                'name' => $data['from']['name'] ?? null
            ],
            'to' => [
                'code' => strtoupper($data['to']['code']),
                'name' => $data['to']['name'] ?? null
            ],
            'rate' => (float) $rate,
            'bid_price' => (float) $bidPrice,
            'ask_price' => (float) $askPrice,
            'last_updated' => $data['last_updated'] ?? now()->toIso8601String(),
            'timezone' => $data['timezone'] ?? 'UTC'
        ];
    }

    /**
     * AlphaVantage API'den gelen döviz kuru verisini temizler
     */
    public function cleanCurrencyData(array $rawData): array
    {
        try {
            $this->validateCurrencyData($rawData);

            return [
                'rate' => (float)$rawData['Realtime Currency Exchange Rate']['5. Exchange Rate'],
                'base_currency' => $rawData['Realtime Currency Exchange Rate']['1. From_Currency Code'],
                'target_currency' => $rawData['Realtime Currency Exchange Rate']['3. To_Currency Code'],
                'timestamp' => Carbon::parse($rawData['Realtime Currency Exchange Rate']['6. Last Refreshed']),
                'meta' => [
                    'bid_price' => (float)$rawData['Realtime Currency Exchange Rate']['8. Bid Price'],
                    'ask_price' => (float)$rawData['Realtime Currency Exchange Rate']['9. Ask Price'],
                    'volume' => $rawData['Realtime Currency Exchange Rate']['7. Volume'] ?? null,
                    'timezone' => $rawData['Realtime Currency Exchange Rate']['7. Time Zone'] ?? 'UTC',
                    'raw_response' => $rawData
                ]
            ];
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Veri temizleme hatası: " . $e->getMessage());
        }
    }

    /**
     * Gelen ham verinin gerekli alanları içerdiğini kontrol eder
     */
    private function validateCurrencyData(array $data): void
    {
        $required = [
            'Realtime Currency Exchange Rate' => [
                '1. From_Currency Code',
                '3. To_Currency Code',
                '5. Exchange Rate',
                '6. Last Refreshed',
                '8. Bid Price',
                '9. Ask Price'
            ]
        ];

        if (!isset($data['Realtime Currency Exchange Rate'])) {
            throw new InvalidArgumentException("Geçersiz veri formatı: 'Realtime Currency Exchange Rate' alanı bulunamadı");
        }

        foreach ($required['Realtime Currency Exchange Rate'] as $field) {
            if (!isset($data['Realtime Currency Exchange Rate'][$field])) {
                throw new InvalidArgumentException("Eksik alan: {$field}");
            }
        }

        // Rate değerinin sayısal olduğunu kontrol et
        if (!is_numeric($data['Realtime Currency Exchange Rate']['5. Exchange Rate'])) {
            throw new InvalidArgumentException("Geçersiz kur değeri");
        }

        // Para birimi kodlarının geçerli olduğunu kontrol et
        $this->validateCurrencyCode($data['Realtime Currency Exchange Rate']['1. From_Currency Code']);
        $this->validateCurrencyCode($data['Realtime Currency Exchange Rate']['3. To_Currency Code']);
    }

    /**
     * Para birimi kodunun geçerli olduğunu kontrol eder (ISO 4217)
     */
    private function validateCurrencyCode(string $code): void
    {
        if (!preg_match('/^[A-Z]{3}$/', $code)) {
            throw new InvalidArgumentException("Geçersiz para birimi kodu: {$code}");
        }
    }

    /**
     * Tarih/saat değerinin geçerli olduğunu kontrol eder
     */
   
    /**
     * Sayısal değerin geçerli olduğunu kontrol eder
     */
   
} 