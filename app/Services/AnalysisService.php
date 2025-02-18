<?php

namespace App\Services;

use App\Models\FinancialData;
use App\Models\AnalysisResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AnalysisService
{
    /**
     * Günlük ortalama kur hesaplar ve kaydeder
     */
    public function calculateDailyAverage(string $currencyPair): float
    {
        $cacheKey = "daily_avg:{$currencyPair}:" . today()->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($currencyPair) {
            return FinancialData::where('base_currency', substr($currencyPair, 0, 3))
                ->where('target_currency', substr($currencyPair, 4, 3))
                ->whereDate('timestamp', today())
                ->avg('rate') ?? 0.0;
        });
    }

    /**
     * Tüm analizleri çalıştırır ve sonuçları kaydeder
     */
    public function runCompleteAnalysis(string $currencyPair): AnalysisResult
    {
        // Analizleri çalıştır
        $anomalyResult = $this->detectAnomalies($currencyPair);
        $trendResult = $this->analyzeTrend($currencyPair);
        $dailyAvg = $this->calculateDailyAverage($currencyPair);

        // Sonuçları kaydet
        return AnalysisResult::create([
            'currency_pair' => $currencyPair,
            'daily_avg' => $dailyAvg,
            'anomaly_detected' => $anomalyResult['has_anomaly'],
            'deviation' => $anomalyResult['deviation'] ?? null,
            'trend_direction' => $trendResult['trend'],
            'volatility' => $trendResult['volatility'] ?? null,
            'calculation_date' => today(),
            'additional_metrics' => [
                'anomaly_details' => $anomalyResult,
                'trend_details' => $trendResult,
                'data_points' => $trendResult['data_points'] ?? 0,
                'change_percent' => $trendResult['change_percent'] ?? 0
            ]
        ]);
    }

    /**
     * Anormal kur değişimlerini tespit eder
     */
    public function detectAnomalies(string $currencyPair, float $threshold = 0.03): array
    {
        $latest = FinancialData::latestForPair($currencyPair);
        if (!$latest) {
            return [
                'has_anomaly' => false,
                'message' => 'Yeterli veri yok'
            ];
        }

        $average = $this->calculateDailyAverage($currencyPair);
        if ($average == 0) {
            return [
                'has_anomaly' => false,
                'message' => 'Günlük ortalama hesaplanamadı'
            ];
        }

        $deviation = abs(($latest->rate - $average) / $average);
        
        return [
            'has_anomaly' => $deviation > $threshold,
            'deviation' => $deviation,
            'threshold' => $threshold,
            'latest_rate' => $latest->rate,
            'average_rate' => $average,
            'timestamp' => $latest->timestamp,
            'message' => $deviation > $threshold 
                ? "Anormal değişim tespit edildi: %". round($deviation * 100, 2)
                : "Normal değişim aralığında"
        ];
    }

    /**
     * Belirli bir zaman aralığı için trend analizi yapar
     */
    public function analyzeTrend(string $currencyPair, int $days = 7): array
    {
        $data = FinancialData::where('base_currency', substr($currencyPair, 0, 3))
            ->where('target_currency', substr($currencyPair, 4, 3))
            ->where('timestamp', '>=', now()->subDays($days))
            ->orderBy('timestamp')
            ->get();

        if ($data->isEmpty()) {
            return [
                'trend' => 'unknown',
                'message' => 'Yeterli veri yok'
            ];
        }

        $firstRate = $data->first()->rate;
        $lastRate = $data->last()->rate;
        $changePercent = (($lastRate - $firstRate) / $firstRate) * 100;

        return [
            'trend' => $this->determineTrend($changePercent),
            'change_percent' => round($changePercent, 2),
            'start_rate' => $firstRate,
            'end_rate' => $lastRate,
            'data_points' => $data->count(),
            'period' => "{$days} gün",
            'volatility' => $this->calculateVolatility($data->pluck('rate')),
            'message' => $this->generateTrendMessage($changePercent)
        ];
    }

    /**
     * Trend yönünü belirler
     */
    private function determineTrend(float $changePercent): string
    {
        if ($changePercent > 1) return 'bullish';
        if ($changePercent < -1) return 'bearish';
        return 'stable';
    }

    /**
     * Volatilite hesaplar
     */
    private function calculateVolatility(Collection $rates): float
    {
        $mean = $rates->avg();
        $variance = $rates->map(function ($rate) use ($mean) {
            return pow($rate - $mean, 2);
        })->avg();

        return round(sqrt($variance), 4);
    }

    /**
     * Trend mesajı oluşturur
     */
    private function generateTrendMessage(float $changePercent): string
    {
        $direction = $changePercent > 0 ? 'yükseliş' : 'düşüş';
        $strength = abs($changePercent);

        if ($strength > 5) {
            return "Güçlü {$direction} trendi (%{$strength})";
        } elseif ($strength > 1) {
            return "Hafif {$direction} trendi (%{$strength})";
        } else {
            return "Yatay seyir (%{$strength})";
        }
    }
} 