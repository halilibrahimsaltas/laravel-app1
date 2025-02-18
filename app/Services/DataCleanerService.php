<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use InvalidArgumentException;

class DataCleanerService
{
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
    private function validateTimestamp(string $timestamp): void
    {
        try {
            Carbon::parse($timestamp);
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Geçersiz tarih formatı: {$timestamp}");
        }
    }

    /**
     * Sayısal değerin geçerli olduğunu kontrol eder
     */
    private function validateNumericValue(string $value, string $fieldName): void
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException("Geçersiz {$fieldName} değeri: {$value}");
        }
    }
} 