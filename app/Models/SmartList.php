<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartList extends Model
{
    use HasFactory;

    protected $table = 'smart_lists';

    protected $casts = [
        'definition' => 'array'
    ];

    public function scopePaginateData($query, $limit)
    {
        if ($limit == 'all') {
            $limit = $query->count();
        }

        return $query->paginate($limit);
    }
}
