<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_results', function (Blueprint $table) {
            $table->id();
            $table->string('currency_pair');
            $table->decimal('daily_avg', 12, 4);
            $table->boolean('anomaly_detected');
            $table->decimal('deviation', 8, 4)->nullable();
            $table->string('trend_direction')->nullable(); // bullish, bearish, stable
            $table->decimal('volatility', 8, 4)->nullable();
            $table->json('additional_metrics')->nullable();
            $table->date('calculation_date');
            $table->timestamps();

            // Performans için indeksler
            $table->index('currency_pair');
            $table->index('calculation_date');
            $table->index(['currency_pair', 'calculation_date']);
            
            // Anomali ve trend bazlı sorgular için
            $table->index(['anomaly_detected', 'calculation_date']);
            $table->index(['trend_direction', 'calculation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_results');
    }
}; 