<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_analysis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_data_id')->constrained('financial_data')->cascadeOnDelete();
            $table->string('type');
            $table->string('from_code');
            $table->string('to_code');
            $table->decimal('daily_average', 20, 8);
            $table->decimal('volatility', 10, 4);
            $table->string('trend');
            $table->json('additional_metrics')->nullable();
            $table->timestamp('analyzed_at');
            $table->timestamps();

            // İndeksler
            $table->index(['type', 'from_code', 'to_code']);
            $table->index('analyzed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_analysis');
    }
}; 