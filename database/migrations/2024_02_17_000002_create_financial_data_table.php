<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_source_id')->nullable()->constrained('data_sources')->nullOnDelete();
            $table->string('type'); // 'forex', 'gold' gibi
            $table->string('from_code')->nullable(); // USD, XAU gibi
            $table->string('to_code')->nullable(); // TRY, USD gibi
            $table->decimal('rate', 20, 8)->nullable(); // Kur veya fiyat
            $table->decimal('bid_price', 20, 8)->nullable(); // Alış
            $table->decimal('ask_price', 20, 8)->nullable(); // Satış
            $table->jsonb('data')->nullable();
            $table->jsonb('params')->nullable();
            $table->string('status'); // 'success', 'error'
            $table->text('error_message')->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->timestamps();

            // İndeksler
            $table->index('type');
            $table->index('status');
            $table->index('created_at');
            $table->index(['from_code', 'to_code']);
            $table->index('timestamp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_data');
    }
}; 