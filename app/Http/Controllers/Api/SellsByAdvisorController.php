<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Enums\DealTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\SellsByAdvisorResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SellsByAdvisorController extends ApiController
{
    public function __invoke()
    {
        $deals = Deal::query()
            ->select(
                'owner_id',
                'users.name AS name',
                'users.branch AS branch',
                'users.id AS user_id',
                DB::raw("LOWER(DATE_FORMAT(deals.created_at, '%M')) AS month"),
                DB::raw("SUM(value) AS month_value"),
                DB::raw("COUNT(deals.id) AS count"),
                DB::raw("YEAR(deals.created_at) as year")
            )
            ->leftJoin('users', 'deals.owner_id', 'users.id')
            ->where('users.role', RoleEnum::Advisor->value)
            ->where('deals.type', DealTypeEnum::Cotizado->value)
            ->whereYear('deals.created_at', date('Y'))
            ->groupBy(
                'owner_id',
                DB::raw("MONTH(deals.created_at)"),
                DB::raw("YEAR(deals.created_at)")
            )
            ->orderBy(DB::raw("MONTH(deals.created_at)"), 'DESC')
            ;

        $data = $deals->get()->groupBy('owner_id')
            ->map(function ($item, $key) {
                $total = collect($item)->sum('month_value');
                $avg = $total / (int) date('m');
                return collect($item)->mapWithKeys(function ($item, $key) use ($total, $avg) {
                    return [
                        'name' => $item['name'],
                        'owner_id' => $item['owner_id'],
                        'branch' => $item['branch'],
                        'total' => $total,
                        'avg' => $avg,
                        $item['month'] => [
                            'sum' => $item['month_value'],
                            'count' => $item['count'],
                        ]
                    ];
                });
            })->sortByDesc('total')
            ;

        $userIds = $data->pluck('owner_id')->toArray();

        $missingUsers = User::query()
            ->select('id', 'name', 'branch')
            ->where('role', RoleEnum::Advisor->value)
            ->whereNotIn('id', $userIds)
            ->get();

        return SellsByAdvisorResource::collection($data->concat($missingUsers->toArray()));
    }

}
