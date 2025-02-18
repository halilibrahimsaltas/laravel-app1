<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnalysisResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency_pair',
        'daily_avg',
        'anomaly_detected',
        'deviation',
        'trend_direction',
        'volatility',
        'additional_metrics',
        'calculation_date'
    ];

    protected $casts = [
        'daily_avg' => 'decimal:4',
        'anomaly_detected' => 'boolean',
        'deviation' => 'decimal:4',
        'volatility' => 'decimal:4',
        'additional_metrics' => 'array',
        'calculation_date' => 'date'
    ];

    /**
     * Belirli bir para birimi çifti için son analiz sonucunu getirir
     */
    public static function latestForPair(string $currencyPair)
    {
        return static::where('currency_pair', $currencyPair)
                    ->latest('calculation_date')
                    ->first();
    }

    /**
     * Anomali tespit edilen sonuçları filtreler
     */
    public function scopeWithAnomalies($query)
    {
        return $query->where('anomaly_detected', true);
    }

    /**
     * Belirli bir trend yönüne sahip sonuçları filtreler
     */
    public function scopeWithTrend($query, string $direction)
    {
        return $query->where('trend_direction', $direction);
    }

    /**
     * Belirli bir volatilite eşiğinin üzerindeki sonuçları filtreler
     */
    public function scopeHighVolatility($query, float $threshold = 0.02)
    {
        return $query->where('volatility', '>', $threshold);
    }

    /**
     * Günlük bazda özet istatistikler getirir
     */
    public static function dailyStats(string $currencyPair, $date = null)
    {
        $date = $date ?? now()->toDateString();

        return static::where('currency_pair', $currencyPair)
                    ->whereDate('calculation_date', $date)
                    ->select([
                        'currency_pair',
                        'calculation_date',
                        'daily_avg',
                        'volatility',
                        'trend_direction',
                        \DB::raw('COUNT(CASE WHEN anomaly_detected = 1 THEN 1 END) as anomaly_count')
                    ])
                    ->groupBy('currency_pair', 'calculation_date', 'daily_avg', 'volatility', 'trend_direction')
                    ->first();
    }

    /**
     * Analiz sonucunun özetini döndürür
     */
    public function getSummaryAttribute(): array
    {
        return [
            'currency_pair' => $this->currency_pair,
            'date' => $this->calculation_date->format('Y-m-d'),
            'daily_avg' => $this->daily_avg,
            'status' => $this->anomaly_detected ? 'Anomali Tespit Edildi' : 'Normal',
            'trend' => ucfirst($this->trend_direction ?? 'belirsiz'),
            'volatility' => $this->volatility
        ];
    }
} 