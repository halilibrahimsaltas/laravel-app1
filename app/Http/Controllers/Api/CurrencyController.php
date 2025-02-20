<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FinancialDataService;  

class CurrencyController extends Controller
{
protected $financialDataService;

public function __construct(FinancialDataService $financialDataService)
{
$this->financialDataService = $financialDataService;
}

public function show($pair)
{
try {
$data = $this->financialDataService->getCurrencyData($pair);
return response()->json([
'status' => 'success',
'data' => $data
]);
} catch (\Exception $e) {
return response()->json([
'status' => 'error',
'message' => 'Döviz verisi alınırken hata oluştu',
'error' => $e->getMessage()
], 500);
}
}
}