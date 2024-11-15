<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DealPipeline extends Model
{
    use HasFactory;

    protected $table = 'deal_pipelines';

    public function stages(): HasMany
    {
        return $this->hasMany(DealPipelineStage::class);
    }
}
