<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalysisResult;
use App\Models\DataSource;
use App\Services\AnalysisService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AnalysisController extends Controller
{
    private AnalysisService $analysisService;

    public function __construct(AnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
    }

    /**
     * Günlük ortalama hesapla
     */
    public function getDailyAverage(string $currencyPair): JsonResponse
    {
        $average = $this->analysisService->calculateDailyAverage($currencyPair);

        return response()->json([
            'currency_pair' => $currencyPair,
            'daily_average' => $average,
            'calculation_date' => today()->format('Y-m-d')
        ]);
    }

    /**
     * Anomali kontrolü yap
     */
    public function checkAnomalies(string $currencyPair): JsonResponse
    {
        $result = $this->analysisService->detectAnomalies($currencyPair);

        return response()->json($result);
    }

    /**
     * Trend analizi yap
     */
    public function analyzeTrend(string $currencyPair, Request $request): JsonResponse
    {
        $days = $request->get('days', 7);
        $result = $this->analysisService->analyzeTrend($currencyPair, $days);

        return response()->json($result);
    }

    /**
     * Tam analiz çalıştır
     */
    public function runAnalysis(string $currencyPair): JsonResponse
    {
        $result = $this->analysisService->runCompleteAnalysis($currencyPair);

        return response()->json([
            'status' => 'success',
            'message' => 'Analiz tamamlandı',
            'result' => $result->summary
        ]);
    }

    /**
     * Analiz sonuçlarını getir
     */
    public function getResults(string $currencyPair): JsonResponse
    {
        $results = AnalysisResult::where('currency_pair', $currencyPair)
            ->latest('calculation_date')
            ->take(10)
            ->get()
            ->map(fn ($result) => $result->summary);

        return response()->json($results);
    }

    /**
     * Anomali listesini getir
     */
    public function listAnomalies(Request $request): JsonResponse
    {
        $query = AnalysisResult::withAnomalies();

        if ($request->has('currency_pair')) {
            $query->where('currency_pair', $request->currency_pair);
        }

        $anomalies = $query->latest('calculation_date')
            ->take(20)
            ->get()
            ->map(fn ($result) => [
                'currency_pair' => $result->currency_pair,
                'date' => $result->calculation_date->format('Y-m-d'),
                'deviation' => $result->deviation,
                'details' => $result->additional_metrics['anomaly_details'] ?? null
            ]);

        return response()->json($anomalies);
    }

    /**
     * Veri kaynaklarını listele
     */
    public function listDataSources(): JsonResponse
    {
        $sources = DataSource::all();
        return response()->json($sources);
    }

    /**
     * Yeni veri kaynağı oluştur
     */
    public function createDataSource(Request $request): JsonResponse
    {
        try {
            Log::info('Veri kaynağı oluşturma isteği:', [
                'request_data' => $request->all()
            ]);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string|in:api,webhook,file',
                'config' => 'required|array'
            ]);

            Log::info('Doğrulanmış veri:', [
                'validated_data' => $validated
            ]);

            $source = DataSource::create($validated);

            Log::info('Veri kaynağı oluşturuldu:', [
                'source_id' => $source->id,
                'source_data' => $source->toArray()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Veri kaynağı oluşturuldu',
                'source' => $source
            ], 201);

        } catch (\Exception $e) {
            Log::error('Veri kaynağı oluşturma hatası:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Veri kaynağı oluşturulamadı',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 