<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use App\Models\User;
use App\Models\Branch;
use App\Models\Investment;
use App\Enums\DealTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\UserAuditReportResource;

class UserAuditReportController extends ApiController
{
    public function __invoke(Request $request)
    {
        $users = QueryBuilder::for(User::class)
            ->select(
                'users.*'
            )
            // ->with(['warningDeals'])
            ->has('activeDealsFromPublicity')
            ->withCount(['warningDealsFromPublicity' => function($query) use($request) {
                $query->when($request->filter['since'] ?? null, function ($query) use($request) {
                    $query->where('created_at', '>=', $request->filter['since']);
                });

                $query->when($request->filter['until'] ?? null, function ($query) use($request) {
                    $query->where('created_at', '<=', $request->filter['until']);
                });

            }])
            ->withCount(['activeDealsFromPublicity' => function($query) use($request) {
                $query->when($request->filter['since'] ?? null, function ($query) use($request) {
                    $query->where('created_at', '>=', $request->filter['since']);
                });

                $query->when($request->filter['until'] ?? null, function ($query) use($request) {
                    $query->where('created_at', '<=', $request->filter['until']);
                });

            }])
            // ->leftJoin('users', 'deals.owner_id', 'users.id')
            // ->where('deals.type', DealTypeEnum::Cotizado->value)
            ->whereNotNull('users.branch')
            ->when($usersId = auth()->user()->getAssignedUsersIdByRole(), function($query) use($usersId){
                $query->whereIn('deals.owner_id', $usersId);
            })
            ->allowedFilters([
                AllowedFilter::exact('branch'),
                AllowedFilter::callback(
                    'since',
                    function (Builder $query, $value) {
                        return $query;
                    }
                ),
                AllowedFilter::callback(
                    'until',
                    function (Builder $query, $value) {
                        return $query;
                    }
                ),
            ])
            ->allowedSorts([
                'name',
                AllowedSort::field('owner', 'name'),
                AllowedSort::field('active_deals', 'active_deals_from_publicity_count'),
                AllowedSort::field('warning_deals', 'warning_deals_from_publicity_count'),
            ])
            ->defaultSort('name')
            ->get();

        return UserAuditReportResource::collection($users);
    }
}
