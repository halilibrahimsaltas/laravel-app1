<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('data_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_data_id')->references('id')->on('financial_data')->onDelete('cascade');
            $table->string('validation_type');
            $table->json('validation_rules');
            $table->boolean('is_valid');
            $table->json('validation_errors')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_validations');
    }
}; 