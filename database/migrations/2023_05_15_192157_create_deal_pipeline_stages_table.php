<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deal_pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_pipeline_id')->constrained('deal_pipelines');
            $table->string('code');
            $table->string('name');
            $table->smallInteger('probability')->nullable();
            $table->tinyInteger('sort_order');
            $table->boolean('is_active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deal_pipeline_stages');
    }
};
