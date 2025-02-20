<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FinancialDataService;


class DashboardController extends Controller
{
protected $financialDataService;

public function __construct(FinancialDataService $financialDataService)
{
$this->financialDataService = $financialDataService;
}

public function index()
{
try {
$data = $this->financialDataService->getDashboardData();
return response()->json([
'status' => 'success',
'data' => $data
]);
} catch (\Exception $e) {
return response()->json([
'status' => 'error',
'message' => 'Dashboard verisi alınırken hata oluştu',
'error' => $e->getMessage()
], 500);
}
}
}