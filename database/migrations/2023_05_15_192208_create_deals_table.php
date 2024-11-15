<?php

use App\Enums\DealStatusEnum;
use App\Enums\DealTypeEnum;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default(DealTypeEnum::Oportunidad->value);
            $table->string('name');
            $table->float('value', 12, 2)->nullable()->default(0);
            $table->smallInteger('win_probability')->nullable();
            $table->bigInteger('estimated_size')->nullable();
            $table->boolean('has_project_manager')->nullable();
            $table->boolean('created_by_lead_qualifier')->nullable();
            $table->string('status')->nullable()->default(DealStatusEnum::InProgress->value);
            $table->string('customer_responsiveness')->nullable();
            $table->text('requirement')->nullable();
            $table->string('city')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('source_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('deal_pipeline_id')->nullable()->constrained('deal_pipelines');
            $table->foreignId('deal_pipeline_stage_id')->nullable()->constrained('deal_pipeline_stages');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('estimated_close_date')->nullable();
            $table->string('estimated_close_date_range')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->dateTime('added_at')->nullable();
            $table->dateTime('stage_moved_at')->nullable();
            // $table->dateTime('converted_to_opportunity')->nullable();
            // $table->dateTime('converted_to_quote')->nullable();
            $table->dateTime('move_to_in_progress')->nullable();
            $table->decimal('discount', 4, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
