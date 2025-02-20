<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class FinancialDataService
{
protected $alphaVantageKey;
protected $goldApiKey;

public function __construct()
{
$this->alphaVantageKey = config('services.alphavantage.key');
$this->goldApiKey = config('services.goldapi.key');
}

public function getDashboardData()
{
return Cache::remember('dashboard_data', 300, function () {
$usdTry = $this->getCurrencyData('USD/TRY');
$goldUsd = $this->getGoldData('USD');

return [
'currency' => [
'USD/TRY' => $usdTry['data'],
],
'gold' => [
'XAU/USD' => $goldUsd['data'],
],
'timestamp' => now(),
];
});
}

public function getCurrencyData($pair)
{
$cacheKey = "currency_data_{$pair}";

return Cache::remember($cacheKey, 300, function () use ($pair) {
[$from, $to] = explode('/', $pair);

$response = Http::get("https://www.alphavantage.co/query", [
'function' => 'CURRENCY_EXCHANGE_RATE',
'from_currency' => $from,
'to_currency' => $to,
'apikey' => $this->alphaVantageKey,
]);

if ($response->successful()) {
$data = $response->json();
return [
'status' => 'success',
'data' => [
'rate' => $data['Realtime Currency Exchange Rate']['5. Exchange Rate'] ?? null,
'timestamp' => $data['Realtime Currency Exchange Rate']['6. Last Refreshed'] ?? null,
'from' => $from,
'to' => $to,
]
];
}

throw new \Exception('Döviz kuru verisi alınamadı');
});
}

public function getGoldData($currency)
{
$cacheKey = "gold_data_{$currency}";

return Cache::remember($cacheKey, 300, function () use ($currency) {
$response = Http::withHeaders([
'x-access-token' => $this->goldApiKey,
])->get("https://www.goldapi.io/api/XAU/{$currency}");

if ($response->successful()) {
$data = $response->json();
return [
'status' => 'success',
'data' => [
'price' => $data['price'] ?? null,
'timestamp' => $data['timestamp'] ?? null,
'currency' => $currency,
]
];
}

throw new \Exception('Altın fiyat verisi alınamadı');
});
}
}