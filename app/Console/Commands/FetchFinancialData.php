<?php

namespace App\Console\Commands;

use App\Jobs\ProcessFinancialDataJob;
use App\Models\FinancialData;
use Illuminate\Console\Command;

class FetchFinancialData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financial:fetch {type : Veri tipi (forex/gold)} {--debug : Debug modunda çalıştır}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finansal verileri çeker ve kaydeder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $type = $this->argument('type');
            $isDebug = $this->option('debug');

            $this->info("Veri çekme işlemi başlatılıyor...");
            $this->info("Tip: " . $type);

            // Test parametreleri
            $params = match ($type) {
                'forex' => [
                    'from_currency' => 'USD',
                    'to_currency' => 'TRY'
                ],
                'gold' => [
                    'currency' => 'USD'
                ],
                default => throw new \Exception('Geçersiz veri tipi'),
            };

            if ($isDebug) {
                $this->info("Debug modu aktif - Job direkt çalıştırılacak");
                $job = new ProcessFinancialDataJob($type, $params);
                $job->handle(
                    app()->make('App\Services\DataCleanerService'),
                    app()->make('App\Services\AlphaVantageService'),
                    app()->make('App\Services\GoldApiService')
                );
            } else {
                $this->info("Job kuyruğa ekleniyor...");
                dispatch(new ProcessFinancialDataJob($type, $params))
                    ->onQueue('default')
                    ->delay(now()->addSeconds(1));
                
                $this->info("Job başarıyla kuyruğa eklendi. Queue worker'ın çalıştığından emin olun:");
                $this->line("php artisan queue:work --queue=default");
            }

            // Son kaydı kontrol et
            $lastRecord = FinancialData::latest()->first();
            if ($lastRecord) {
                $this->info("Son kayıt:");
                $this->table(
                    ['ID', 'Tip', 'Durum', 'Oluşturulma'],
                    [[
                        $lastRecord->id,
                        $lastRecord->type,
                        $lastRecord->status,
                        $lastRecord->created_at
                    ]]
                );

                if ($isDebug) {
                    $this->info("Veri detayı:");
                    $this->line(json_encode($lastRecord->data, JSON_PRETTY_PRINT));
                }
            } else {
                $this->warn("Henüz kayıtlı veri yok!");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Hata: " . $e->getMessage());
            if ($isDebug) {
                $this->error($e->getTraceAsString());
            }
            return 1;
        }
    }
}