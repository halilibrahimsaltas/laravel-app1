<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\CryptoController;
use App\Http\Controllers\Api\AnalysisController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Finans rotaları
    Route::get('/finance/gold-price', [FinanceController::class, 'getGoldPrice']);
    Route::get('/finance/exchange-rate', [FinanceController::class, 'getExchangeRate']);

    // Kripto para rotaları
    Route::prefix('crypto')->group(function () {
        Route::get('/price', [CryptoController::class, 'getCryptoPrice']);
        Route::get('/prices', [CryptoController::class, 'getMultipleCryptoPrices']);
        Route::get('/trending', [CryptoController::class, 'getTrendingCoins']);
        Route::get('/details/{coinId}', [CryptoController::class, 'getCoinDetails']);
    });

    // Analiz endpoint'leri
    Route::prefix('v1/analysis')->group(function () {
        // Günlük ortalama hesaplama
        Route::get('/daily-average/{currencyPair}', [AnalysisController::class, 'getDailyAverage']);
        
        // Anomali kontrolü
        Route::get('/anomalies/{currencyPair}', [AnalysisController::class, 'checkAnomalies']);
        
        // Trend analizi
        Route::get('/trend/{currencyPair}', [AnalysisController::class, 'analyzeTrend']);
        
        // Tam analiz çalıştır
        Route::post('/run/{currencyPair}', [AnalysisController::class, 'runAnalysis']);
        
        // Analiz sonuçlarını listele
        Route::get('/results/{currencyPair}', [AnalysisController::class, 'getResults']);
        
        // Anomali listesi
        Route::get('/anomalies', [AnalysisController::class, 'listAnomalies']);
    });

    // Veri kaynakları endpoint'leri
    Route::prefix('v1/data-sources')->group(function () {
        Route::get('/', [AnalysisController::class, 'listDataSources']);
        Route::post('/', [AnalysisController::class, 'createDataSource']);
    });
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});