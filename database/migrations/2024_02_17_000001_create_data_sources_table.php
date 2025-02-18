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
        Schema::create('data_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // api, webhook, file
            $table->json('config');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_fetch_at')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Migration'ı geri al
     */
    public function down(): void
    {
        Schema::dropIfExists('data_sources');
    }
}; 