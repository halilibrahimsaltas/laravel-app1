<?php

namespace App\Jobs;

use App\Events\FinancialDataProcessed;
use App\Events\FinancialDataProcessingFailed;
use App\Models\DataSource;
use App\Models\FinancialData;
use App\Services\DataCleanerService;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;
use App\Services\AlphaVantageService;
use App\Services\GoldApiService;
use App\Jobs\AnalyzeTrendJob;


class ProcessFinancialDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maksimum yeniden deneme sayısı
     */
    public $tries = 3;

    /**
     * Yeniden denemeler arasındaki bekleme süresi (saniye)
     */
    public $backoff = [10, 30, 60];

    /**
     * Job'ın zaman aşımı süresi (saniye)
     */
    public $timeout = 120;

    /**
     * Veri kaynağı
     */
    private DataSource $source;

    /**
     * Ham veri
     */
    private array $rawData;

    private string $type;
    private array $params;

    /**
     * Job oluşturulurken çalışır
     */
    public function __construct(string $type, array $params = [])
    {
        $this->type = $type;
        $this->params = $params;
    }

    /**
     * Job'ın ana işlem metodu
     */
    public function handle(DataCleanerService $cleaner, AlphaVantageService $alphaVantageService, GoldApiService $goldApiService): void
    {
        try {
            Log::info("Finansal veri işleme başladı", [
                'type' => $this->type,
                'params' => $this->params
            ]);

            $data = match ($this->type) {
                'forex' => $this->processForexData($alphaVantageService),
                'gold' => $this->processGoldData($goldApiService),
                default => throw new \Exception('Geçersiz veri tipi'),
            };

            Log::info("Ham veri alındı", [
                'type' => $this->type,
                'data' => $data
            ]);

            // Veriyi temizle
            $cleanData = $cleaner->cleanFinancialData($data);

            Log::info("Veri temizlendi", [
                'type' => $this->type,
                'clean_data' => $cleanData
            ]);

            // Veriyi kaydet
            $financialData = FinancialData::create([
                'type' => $this->type,
                'data' => $cleanData,
                'params' => $this->params,
                'status' => 'success',
                'data_source_id' => null,
                'timestamp' => now(),
                'from_code' => $cleanData['from']['code'] ?? null,
                'to_code' => $cleanData['to']['code'] ?? null,
                'rate' => $cleanData['rate'] ?? null,
                'bid_price' => $cleanData['bid_price'] ?? null,
                'ask_price' => $cleanData['ask_price'] ?? null
            ]);

            Log::info("Finansal veri başarıyla işlendi", [
                'id' => $financialData->id,
                'type' => $this->type,
                'params' => $this->params
            ]);

            // Trend analizi için yeni job
            dispatch(new AnalyzeTrendJob($financialData))
                ->onQueue('analysis')
                ->delay(now()->addSeconds(5));

            // Veri değişim bildirimi
            event(new FinancialDataProcessed($financialData));

        } catch (ConnectException $e) {
            Log::error("API bağlantı hatası", [
                'type' => $this->type,
                'params' => $this->params,
                'error' => $e->getMessage()
            ]);

            // Bağlantı hatalarında yeniden dene
            $this->release(30);

        } catch (\Exception $e) {
            Log::error("Finansal veri işleme hatası", [
                'type' => $this->type,
                'params' => $this->params,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            // Hatalı veriyi kaydet
            FinancialData::create([
                'type' => $this->type,
                'data' => null,
                'params' => $this->params,
                'status' => 'error',
                'error_message' => $e->getMessage(),
                'timestamp' => now()
            ]);

            // Kritik hataları bildir
            event(new FinancialDataProcessingFailed($this->type, $e));
        }
    }

    /**
     * Job başarısız olduğunda çalışır
     */
    public function failed(Throwable $e)
    {
        Log::error("Finansal veri işleme başarısız oldu", [
            'type' => $this->type,
            'params' => $this->params,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        // Kritik hataları bildir
        if ($this->attempts() >= $this->tries) {
            // Slack, email veya diğer bildirim kanallarına gönder
            event(new FinancialDataProcessingFailed($this->type, $e));
        }
    }

    /**
     * Hata yönetimi
     */
    private function handleError(Throwable $e)
    {
        // Belirli hata türlerine göre farklı davranışlar
        if ($e instanceof InvalidArgumentException) {
            // Veri doğrulama hatası - yeniden deneme yapma
            $this->fail($e);
        } elseif ($e instanceof ConnectException) {
            // Bağlantı hatası - yeniden dene
            if ($this->attempts() < $this->tries) {
                $this->release(
                    $this->backoff[$this->attempts() - 1] ?? 60
                );
            }
        } else {
            // Diğer hatalar için varsayılan davranış
            throw $e;
        }
    }

    /**
     * Job'ın unique ID'sini döndürür
     */
    public function uniqueId(): string
    {
        return implode(':', [
            $this->type,
            ...array_values($this->params)
        ]);
    }

    /**
     * Döviz verilerini işle
     */
    private function processForexData(AlphaVantageService $service): array
    {
        Log::info("Döviz verisi çekiliyor", [
            'from' => $this->params['from_currency'] ?? 'USD',
            'to' => $this->params['to_currency'] ?? 'TRY'
        ]);

        $fromCurrency = $this->params['from_currency'] ?? 'USD';
        $toCurrency = $this->params['to_currency'] ?? 'TRY';

        $response = $service->getExchangeRate($fromCurrency, $toCurrency);

        if ($response['status'] === 'error') {
            throw new \Exception($response['message']);
        }

        if (!isset($response['data']['Realtime Currency Exchange Rate'])) {
            throw new \Exception('API yanıtında döviz kuru verisi bulunamadı');
        }

        $exchangeData = $response['data']['Realtime Currency Exchange Rate'];
        
        return [
            'from' => [
                'code' => $exchangeData['1. From_Currency Code'],
                'name' => $exchangeData['2. From_Currency Name']
            ],
            'to' => [
                'code' => $exchangeData['3. To_Currency Code'],
                'name' => $exchangeData['4. To_Currency Name']
            ],
            'rate' => (float) $exchangeData['5. Exchange Rate'],
            'last_updated' => $exchangeData['6. Last Refreshed'],
            'timezone' => $exchangeData['7. Time Zone'],
            'bid_price' => (float) $exchangeData['8. Bid Price'],
            'ask_price' => (float) $exchangeData['9. Ask Price']
        ];
    }

    /**
     * Altın verilerini işle
     */
    private function processGoldData(GoldApiService $service): array
    {
        Log::info("Altın verisi çekiliyor", [
            'currency' => $this->params['currency'] ?? 'USD',
            'date' => $this->params['date'] ?? 'current'
        ]);

        $currency = $this->params['currency'] ?? 'USD';
        $date = $this->params['date'] ?? null;

        $response = $date 
            ? $service->getHistoricalPrice('XAU', $currency, $date)
            : $service->getRealTimePrice('XAU', $currency);

        if ($response['status'] === 'error') {
            throw new \Exception($response['message']);
        }

        $goldData = $response['data'];
        
        return [
            'from' => [
                'code' => 'XAU',
                'name' => 'Gold'
            ],
            'to' => [
                'code' => $currency,
                'name' => $this->getCurrencyName($currency)
            ],
            'rate' => $goldData['troy_ounce']['price'],
            'last_updated' => $goldData['last_updated'],
            'timezone' => 'UTC',
            'bid_price' => $goldData['troy_ounce']['price'],
            'ask_price' => $goldData['troy_ounce']['price']
        ];
    }

    /**
     * Para birimi koduna göre para birimi adını döndürür
     */
    private function getCurrencyName(string $code): string
    {
        return match($code) {
            'USD' => 'United States Dollar',
            'TRY' => 'Turkish Lira',
            'EUR' => 'Euro',
            'GBP' => 'British Pound',
            default => $code
        };
    }
} 