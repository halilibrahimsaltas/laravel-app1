<?php

namespace App\Jobs;

use App\Models\FinancialData;
use App\Services\AnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeTrendJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private FinancialData $financialData;

    public function __construct(FinancialData $financialData)
    {
        $this->financialData = $financialData;
    }

    public function handle(AnalysisService $analysisService)
    {
        $currencyPair = "{$this->financialData->base_currency}/{$this->financialData->target_currency}";

        try {
            // Tüm analizleri çalıştır ve sonuçları kaydet
            $result = $analysisService->runCompleteAnalysis($currencyPair);

            // Sonuçları logla
            Log::info("Analiz tamamlandı", [
                'pair' => $currencyPair,
                'summary' => $result->summary
            ]);

            // Anomali varsa uyarı logla
            if ($result->anomaly_detected) {
                Log::warning("Anormal kur değişimi tespit edildi", [
                    'pair' => $currencyPair,
                    'details' => $result->additional_metrics['anomaly_details']
                ]);
            }

            // Meta veriyi güncelle
            $this->financialData->update([
                'meta' => array_merge($this->financialData->meta ?? [], [
                    'analysis' => [
                        'result_id' => $result->id,
                        'summary' => $result->summary,
                        'analyzed_at' => now()
                    ]
                ])
            ]);

        } catch (\Exception $e) {
            Log::error("Analiz başarısız oldu", [
                'pair' => $currencyPair,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
} 