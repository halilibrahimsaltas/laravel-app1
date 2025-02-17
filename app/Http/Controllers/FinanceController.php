<?php

namespace App\Http\Controllers;

use App\Services\AlphaVantageService;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    private $alphaVantageService;

    public function __construct(AlphaVantageService $alphaVantageService)
    {
        $this->alphaVantageService = $alphaVantageService;
    }

    /**
     * Altın fiyatını getirir
     */
    public function getGoldPrice()
    {
        try {
            $data = $this->alphaVantageService->getGoldPrice();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Döviz kurunu getirir
     */
    public function getExchangeRate(Request $request)
    {
        try {
            $request->validate([
                'from_currency' => 'required|string|max:5',
                'to_currency' => 'required|string|max:5'
            ]);

            $data = $this->alphaVantageService->getExchangeRate(
                $request->from_currency,
                $request->to_currency
            );

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 