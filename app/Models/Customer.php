<?php

namespace App\Models;

use App\Traits\Searchable;
use App\Enums\DealTypeEnum;
use App\Enums\DealStatusEnum;
use App\Traits\FilterableByDates;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model implements HasMedia
{
    use HasFactory, Searchable, FilterableByDates, InteractsWithMedia, SoftDeletes;

    public $searchable = ['name', 'email'];

    protected $with = ['category'];

    protected $casts = [
        'star' => 'boolean'
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'customer_id');
    }

    public function activeDeals(): HasMany
    {
        return $this->deals()
            ->whereIn('status', [DealStatusEnum::InProgress->value, DealStatusEnum::New->value]);

        return $this->deals()
            ->where('status', DealStatusEnum::InProgress->value)
            ->orwhere('status', DealStatusEnum::New->value);
    }

    public function wonDeals(): HasMany
    {
        return $this->deals()->where('status', DealStatusEnum::Won->value);
    }

    public function latestWonDeal(): HasOne
    {
        return $this->hasOne(Deal::class, 'customer_id')
            ->where('status', DealStatusEnum::Won->value)
            ->latest('stage_moved_at');
    }

    public function opportunities(): HasMany
    {
        return $this->deals()->where('type', DealTypeEnum::Oportunidad->value);
    }

    public function activeOpportunities(): HasMany
    {
        return $this->opportunities()->where('status', DealStatusEnum::InProgress->value);
    }

    public function quotes(): HasMany
    {
        return $this->deals()->where('type', DealTypeEnum::Cotizado->value);
    }

    public function activeQuotes(): HasMany
    {
        return $this->quotes()->where('status', DealStatusEnum::InProgress->value);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(CustomerStatus::class, 'customer_status_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function potentialStatus(): BelongsTo
    {
        return $this->belongsTo(PotentialCustomerStatus::class, 'potential_customer_status_id');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function contacts(): MorphMany
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

    public function lastActivetask(): MorphOne
    {
        return $this->morphOne(Task::class, 'taskable')
            ->where('done', false)
            ->orderBy('due_at', 'ASC');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'id_cliente');
    }

    public function scopeClient($query)
    {
        return $query->where('customer_status', 'Cliente actual');
    }

    public function scopePotentialClient($query)
    {
        return $query->where('potential_customer_status', 'Cliente potencial actual');
    }

    public function scopePaginateData($query, $limit)
    {
        if ($limit == 'all') {
            $limit = $query->count();
        }

        return $query->paginate($limit);
    }
}
