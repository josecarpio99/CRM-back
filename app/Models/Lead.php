<?php

namespace App\Models;

use App\Traits\Searchable;
use App\Enums\LeadStatusEnum;
use App\Traits\FilterableByDates;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model implements HasMedia
{
    use HasFactory, Searchable, FilterableByDates, InteractsWithMedia, SoftDeletes;

    public $searchable = ['name'];

    protected $casts = [
        'appointment_at' => 'datetime'
    ];

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

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
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

    public function scopeAssigned($query)
    {
        return $query->where('status', LeadStatusEnum::Assigned->value);
    }

    public function scopeNew($query)
    {
        return $query->where('status', LeadStatusEnum::New->value);
    }

    public function scopeUnqualified($query)
    {
        return $query->where('status', LeadStatusEnum::Unqualified->value);
    }

    public function scopePaginateData($query, $limit)
    {
        if ($limit == 'all') {
            $limit = $query->count();
        }

        return $query->paginate($limit);
    }
}
