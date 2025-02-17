<?php

namespace App\Http\Controllers;

use App\Services\CoinGeckoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CryptoController extends Controller
{
    private $coinGeckoService;

    public function __construct(CoinGeckoService $coinGeckoService)
    {
        $this->coinGeckoService = $coinGeckoService;
    }

    /**
     * Tek bir kripto para verisi getirir
     */
    public function getCryptoPrice(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'coin_id' => 'required|string|max:50',
                'currencies' => 'sometimes|array',
                'currencies.*' => 'string|max:5'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()
                ], 422);
            }

            $data = $this->coinGeckoService->fetchCryptoData(
                $request->coin_id,
                $request->currencies ?? ['usd', 'try']
            );

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Birden fazla kripto para verisi getirir
     */
    public function getMultipleCryptoPrices(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'coin_ids' => 'required|array|min:1',
                'coin_ids.*' => 'string|max:50',
                'currencies' => 'sometimes|array',
                'currencies.*' => 'string|max:5'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()
                ], 422);
            }

            $data = $this->coinGeckoService->fetchMultipleCryptoData(
                $request->coin_ids,
                $request->currencies ?? ['usd', 'try']
            );

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trend olan kripto paraları getirir
     */
    public function getTrendingCoins()
    {
        try {
            $data = $this->coinGeckoService->getTrendingCoins();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kripto para detaylarını getirir
     */
    public function getCoinDetails(string $coinId)
    {
        try {
            $data = $this->coinGeckoService->getCoinDetails($coinId);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 