<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model
{
    use HasFactory;

    public function contactable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopePaginateData($query, $limit)
    {
        if ($limit == 'all') {
            $limit = $query->count();
        }

        return $query->paginate($limit);
    }
}
