<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration'ı çalıştır
     */
    public function up(): void
    {
        Schema::create('financial_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_source_id')->constrained()->cascadeOnDelete();
            $table->json('raw_data');
            $table->timestamp('processed_at');
            $table->timestamps();

            // İndeksler
            $table->index('processed_at');
            $table->index(['data_source_id', 'processed_at']);
        });
    }

    /**
     * Migration'ı geri al
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_data');
    }
}; 