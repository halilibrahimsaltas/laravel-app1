<?php

namespace App\Jobs;

use App\Events\FinancialDataProcessed;
use App\Events\FinancialDataProcessingFailed;
use App\Models\DataSource;
use App\Models\FinancialData;
use App\Services\DataCleanerService;
use Illuminate\Support\Carbon;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

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

    /**
     * Job oluşturulurken çalışır
     */
    public function __construct(DataSource $source, array $rawData)
    {
        $this->source = $source;
        $this->rawData = $rawData;
        $this->onQueue('financial-data');
    }

    /**
     * Job'ın ana işlem metodu
     */
    public function handle(DataCleanerService $cleaner)
    {
        try {
            Log::info("Finansal veri işleme başladı", [
                'source' => $this->source->name,
                'timestamp' => Carbon::now()
            ]);

            // Veriyi temizle ve doğrula
            $cleanData = $cleaner->cleanCurrencyData($this->rawData);

            // Veriyi kaydet
            $financialData = FinancialData::create([
                'source_id' => $this->source->id,
                ...$cleanData
            ]);

            // İşlem başarılı log kaydı
            Log::info("Finansal veri başarıyla işlendi", [
                'id' => $financialData->id,
                'source' => $this->source->name,
                'pair' => "{$cleanData['base_currency']}/{$cleanData['target_currency']}",
                'rate' => $cleanData['rate']
            ]);

            // Trend analizi için yeni job
            dispatch(new AnalyzeTrendJob($financialData))
                ->onQueue('analysis')
                ->delay(now()->addSeconds(5));

            // Veri değişim bildirimi
            event(new FinancialDataProcessed($financialData));

        } catch (Throwable $e) {
            $this->handleError($e);
        }
    }

    /**
     * Job başarısız olduğunda çalışır
     */
    public function failed(Throwable $e)
    {
        Log::error("Finansal veri işleme başarısız oldu", [
            'source' => $this->source->name,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        // Kritik hataları bildir
        if ($this->attempts() >= $this->tries) {
            // Slack, email veya diğer bildirim kanallarına gönder
            event(new FinancialDataProcessingFailed($this->source, $e));
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
            $this->source->id,
            $this->rawData['Realtime Currency Exchange Rate']['1. From_Currency Code'],
            $this->rawData['Realtime Currency Exchange Rate']['3. To_Currency Code'],
            $this->rawData['Realtime Currency Exchange Rate']['6. Last Refreshed']
        ]);
    }
} 