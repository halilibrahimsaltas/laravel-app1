<?php

namespace App\Console\Commands;

use App\Models\FinancialData;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class FinancialCleanupCommand extends Command
{
    protected $signature = 'financial:cleanup {--days=30 : Kaç günden eski verilerin silineceği}';
    protected $description = 'Eski finansal verileri temizler';

    public function handle()
    {
        try {
            $days = $this->option('days');
            $date = Carbon::now()->subDays($days);

            $count = FinancialData::where('created_at', '<', $date)->delete();

            $this->info("{$count} adet eski veri başarıyla silindi.");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Veri temizleme hatası: " . $e->getMessage());
            
            return 1;
        }
    }
} 