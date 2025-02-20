<?php

namespace App\Console;

use App\Jobs\ProcessFinancialDataJob;
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
                dispatch(new ProcessFinancialDataJob('forex', [
                    'from_currency' => $source->from_currency,
                    'to_currency' => $source->to_currency
                ]));
            }
        })->hourly();

        // Kripto para verilerini 5 dakikada bir güncelle
        $schedule->call(function () {
            $sources = DataSource::where('type', 'crypto')
                ->where('is_active', true)
                ->get();

            foreach ($sources as $source) {
                dispatch(new ProcessFinancialDataJob('crypto', [
                    'symbol' => $source->symbol
                ]));
            }
        })->everyFiveMinutes();

        // Altın verilerini saatlik güncelle
        $schedule->call(function () {
            $sources = DataSource::where('type', 'gold')
                ->where('is_active', true)
                ->get();

            foreach ($sources as $source) {
                dispatch(new ProcessFinancialDataJob('gold', [
                    'currency' => $source->currency
                ]));
            }
        })->hourly();

        // Her 5 dakikada bir USD/TRY döviz kuru
        $schedule->job(new ProcessFinancialDataJob('forex', [
            'from_currency' => 'USD',
            'to_currency' => 'TRY'
        ]))->everyFiveMinutes();

        // Her 5 dakikada bir EUR/TRY döviz kuru
        $schedule->job(new ProcessFinancialDataJob('forex', [
            'from_currency' => 'EUR',
            'to_currency' => 'TRY'
        ]))->everyFiveMinutes();

        // Her 5 dakikada bir USD cinsinden altın fiyatı
        $schedule->job(new ProcessFinancialDataJob('gold', [
            'currency' => 'USD'
        ]))->everyFiveMinutes();

        // Her 5 dakikada bir TRY cinsinden altın fiyatı
        $schedule->job(new ProcessFinancialDataJob('gold', [
            'currency' => 'TRY'
        ]))->everyFiveMinutes();

        // Veritabanı temizliği - 30 günden eski verileri sil
        $schedule->command('financial:cleanup --days=30')->daily();
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