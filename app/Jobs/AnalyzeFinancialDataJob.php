<?php

namespace App\Jobs;

use App\Models\FinancialData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AnalyzeFinancialDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private FinancialData $financialData;
    private string $type;

    public function __construct(FinancialData $financialData)
    {
        $this->financialData = $financialData;
        $this->type = $financialData->type;
    }

    public function handle()
    {
        try {
            Log::info("Finansal veri analizi başladı", [
                'id' => $this->financialData->id,
                'type' => $this->type
            ]);

            // Günlük ortalama hesapla
            $dailyAverage = $this->calculateDailyAverage();
            
            // Volatilite hesapla
            $volatility = $this->calculateVolatility();
            
            // Trend analizi
            $trend = $this->analyzeTrend();

            // Sonuçları önbellekle (1 saat)
            $cacheKey = "analysis_{$this->type}_{$this->financialData->from_code}_{$this->financialData->to_code}";
            Cache::put($cacheKey, [
                'daily_average' => $dailyAverage,
                'volatility' => $volatility,
                'trend' => $trend,
                'updated_at' => now()
            ], now()->addHour());

            Log::info("Finansal veri analizi tamamlandı", [
                'id' => $this->financialData->id,
                'type' => $this->type,
                'results' => [
                    'daily_average' => $dailyAverage,
                    'volatility' => $volatility,
                    'trend' => $trend
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Finansal veri analizi hatası", [
                'id' => $this->financialData->id,
                'type' => $this->type,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    private function calculateDailyAverage(): float
    {
        $today = now()->startOfDay();
        
        return FinancialData::where('type', $this->type)
            ->where('from_code', $this->financialData->from_code)
            ->where('to_code', $this->financialData->to_code)
            ->where('created_at', '>=', $today)
            ->avg('rate') ?? 0.0;
    }

    private function calculateVolatility(): float
    {
        $lastWeek = now()->subWeek();
        
        $rates = FinancialData::where('type', $this->type)
            ->where('from_code', $this->financialData->from_code)
            ->where('to_code', $this->financialData->to_code)
            ->where('created_at', '>=', $lastWeek)
            ->pluck('rate')
            ->toArray();

        if (empty($rates)) {
            return 0.0;
        }

        $mean = array_sum($rates) / count($rates);
        $variance = array_reduce($rates, function ($carry, $rate) use ($mean) {
            return $carry + pow($rate - $mean, 2);
        }, 0) / count($rates);

        return sqrt($variance);
    }

    private function analyzeTrend(): string
    {
        $yesterday = now()->subDay();
        
        $yesterdayAvg = FinancialData::where('type', $this->type)
            ->where('from_code', $this->financialData->from_code)
            ->where('to_code', $this->financialData->to_code)
            ->where('created_at', '>=', $yesterday)
            ->where('created_at', '<', now()->startOfDay())
            ->avg('rate') ?? 0.0;

        $todayAvg = $this->calculateDailyAverage();

        if ($todayAvg > $yesterdayAvg) {
            return 'yükseliş';
        } elseif ($todayAvg < $yesterdayAvg) {
            return 'düşüş';
        } else {
            return 'yatay';
        }
    }
} 