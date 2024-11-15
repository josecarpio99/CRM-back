<?php

namespace App\Models;

use App\Traits\Searchable;
use App\Traits\FilterableByDates;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deal extends Model implements HasMedia
{
    use HasFactory, Searchable, FilterableByDates, InteractsWithMedia, SoftDeletes;

    public $searchable = ['name', 'customer_name'];

    protected $casts = [
        'estimated_close_date' => 'datetime',
        'added_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'stage_moved_at' => 'datetime',
        'monitoring_tasks' => 'array'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(DealPipeline::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(DealPipelineStage::class, 'deal_pipeline_stage_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function associatedContacts() : BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_deal', 'deal_id', 'customer_id');
    }

    public function contacts() : BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_deal', 'deal_id', 'contact_id');
    }

    public function mediaFiles(): MorphMany
    {
        return $this->media()->where('collection_name', 'files');
    }

    public function mediaProfitability(): MorphMany
    {
        return $this->media()->where('collection_name', 'profitability');
    }

    public function lastActivetask(): MorphOne
    {
        return $this->morphOne(Task::class, 'taskable')
            ->where('done', false)
            ->orderBy('due_at', 'ASC');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'deal_id');
    }

    public function scopePaginateData($query, $limit)
    {
        if ($limit == 'all') {
            $limit = $query->count();
        }

        return $query->paginate($limit);
    }
}
