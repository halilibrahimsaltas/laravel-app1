<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\CryptoController;

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
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});