<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FinancialDataService;

class GoldController extends Controller
{
protected $financialDataService;

public function __construct(FinancialDataService $financialDataService)
{
$this->financialDataService = $financialDataService;
}

public function show($currency)
{
try {
$data = $this->financialDataService->getGoldData($currency);
return response()->json([
'status' => 'success',
'data' => $data
]);
} catch (\Exception $e) {
return response()->json([
'status' => 'error',
'message' => 'Altın verisi alınırken hata oluştu',
'error' => $e->getMessage()
], 500);
}
}
}