<?php

namespace App\Console;

use App\Jobs\FetchFinancialDataJob;
use App\Models\DataSource;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Konsol uygulaması için komut listesi
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Uygulama için komut zamanlaması tanımla
     */
    protected function schedule(Schedule $schedule): void
    {
        // Forex verilerini saatlik güncelle
        $schedule->call(function () {
            $sources = DataSource::where('type', 'forex')
                ->where('is_active', true)
                ->get();

            foreach ($sources as $source) {
                dispatch(new FetchFinancialDataJob($source));
            }
        })->hourly();

        // Kripto para verilerini 5 dakikada bir güncelle
        $schedule->call(function () {
            $sources = DataSource::where('type', 'crypto')
                ->where('is_active', true)
                ->get();

            foreach ($sources as $source) {
                dispatch(new FetchFinancialDataJob($source));
            }
        })->everyFiveMinutes();

        // Altın verilerini saatlik güncelle
        $schedule->call(function () {
            $sources = DataSource::where('type', 'gold')
                ->where('is_active', true)
                ->get();

            foreach ($sources as $source) {
                dispatch(new FetchFinancialDataJob($source));
            }
        })->hourly();

        // Veritabanı temizliği (eski verileri sil)
        $schedule->command('model:prune')->daily();
    }

    /**
     * Komutları kaydet
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 