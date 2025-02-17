<?php

namespace App\Jobs;

use App\Models\DataSource;
use App\Services\AlphaVantageService;
use App\Services\CoinGeckoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchFinancialDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dataSource;

    /**
     * Job oluştur
     */
    public function __construct(DataSource $dataSource)
    {
        $this->dataSource = $dataSource;
    }

    /**
     * Job'ı çalıştır
     */
    public function handle(AlphaVantageService $alphaVantage, CoinGeckoService $coinGecko): void
    {
        try {
            $data = match ($this->dataSource->type) {
                'forex' => $this->fetchForexData($alphaVantage),
                'crypto' => $this->fetchCryptoData($coinGecko),
                'gold' => $this->fetchGoldData($alphaVantage),
                default => throw new \Exception('Desteklenmeyen veri kaynağı türü: ' . $this->dataSource->type),
            };

            // Veriyi kaydet
            $this->dataSource->data()->create([
                'raw_data' => $data,
                'processed_at' => now(),
            ]);

            Log::info('Finansal veri başarıyla çekildi', [
                'source_id' => $this->dataSource->id,
                'type' => $this->dataSource->type
            ]);

        } catch (\Exception $e) {
            Log::error('Finansal veri çekme hatası: ' . $e->getMessage(), [
                'source_id' => $this->dataSource->id,
                'type' => $this->dataSource->type
            ]);

            // Job'ı yeniden kuyruğa ekle (en fazla 3 deneme)
            if ($this->attempts() < 3) {
                $this->release(30); // 30 saniye sonra tekrar dene
            }
        }
    }

    /**
     * Forex verisi çek
     */
    protected function fetchForexData(AlphaVantageService $service): array
    {
        return $service->fetchData([
            'function' => 'CURRENCY_EXCHANGE_RATE',
            'from_currency' => $this->dataSource->config['from_currency'],
            'to_currency' => $this->dataSource->config['to_currency']
        ]);
    }

    /**
     * Kripto para verisi çek
     */
    protected function fetchCryptoData(CoinGeckoService $service): array
    {
        return $service->fetchCryptoData(
            $this->dataSource->config['coin_id'],
            $this->dataSource->config['currencies'] ?? ['usd', 'try']
        );
    }

    /**
     * Altın verisi çek
     */
    protected function fetchGoldData(AlphaVantageService $service): array
    {
        return $service->fetchData([
            'function' => 'CURRENCY_EXCHANGE_RATE',
            'from_currency' => 'XAU',
            'to_currency' => 'USD'
        ]);
    }
} 