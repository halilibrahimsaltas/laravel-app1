<?php

namespace App\Http\Controllers;

use App\Models\FinancialData;
use Illuminate\Http\Request;


class ChartController extends Controller
{
    public function getChartData(Request $request)
    {
        $fromCode = $request->from_code;
        $toCode = $request->to_code;
        $days = $request->get('days', 7);

        $data = FinancialData::where('from_code', $fromCode)
            ->where('to_code', $toCode)
            ->where('timestamp', '>=', now()->subDays($days))
            ->orderBy('timestamp')
            ->get()
            ->map(function($item) {
                return [
                    'x' => $item->timestamp->format('Y-m-d H:i'),
                    'y' => $item->rate
                ];
            });

        return response()->json([
            'labels' => $data->pluck('x'),
            'datasets' => [
                [
                    'label' => "$fromCode/$toCode Rate",
                    'data' => $data->pluck('y')
                ]
            ]
        ]);
    }
} 