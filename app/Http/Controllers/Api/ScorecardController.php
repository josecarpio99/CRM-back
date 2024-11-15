<?php

namespace App\Http\Controllers\Api;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Resources\ScorecardResource;
use Illuminate\Database\Eloquent\Builder;

class ScorecardController extends ApiController
{
    public function __invoke()
    {
        $users = QueryBuilder::for(User::class)
            ->withSum('opportunities', 'value')
            ->withSum('quotations', 'value')
            ->withSum('deals', 'value')
            ->allowedFilters([
                'name',
                AllowedFilter::callback(
                    'branch',
                    fn (Builder $query, $value) => $query->whereIn('branch', gettype($value) == 'string' ? explode(',', $value) : $value)
                )
            ])
            ->allowedSorts([
                'name',
                AllowedSort::field('opportunities_sum_value'),
                AllowedSort::field('quotations_sum_value'),
                AllowedSort::field('deals_sum_value'),
            ])
            ->defaultSort('-deals_sum_value');

        $users->where('role', RoleEnum::Advisor->value);

        return ScorecardResource::collection(($users->get()));
    }

}
