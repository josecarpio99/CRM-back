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

class OwnerReportController extends ApiController
{
    public function __invoke(Request $request)
    {
        $deals = QueryBuilder::for(User::class)
            ->select(
                'users.id as owner_id',
                'users.name as owner',
                'users.avatar_url as avatar_url',
                'users.branch as branch',
                DB::raw('COUNT(deals.owner_id) AS total'),
                DB::raw('(COUNT(deals.owner_id) * 450) AS inversion'),
                DB::raw('
                    SUM(
                        CASE
                            WHEN deals.status = "ganado"
                            THEN 1
                            ELSE 0
                        END
                    ) AS ganados'
                ),
                DB::raw('
                    SUM(
                        CASE
                            WHEN deals.status = "perdido"
                            THEN 1
                            ELSE 0
                        END
                    ) AS perdidos'
                ),
                DB::raw('
                    SUM(
                        CASE
                            WHEN deals.status = "en proceso"
                            THEN 1
                            ELSE 0
                        END
                    ) AS vivos'
                ),
                DB::raw('
                    SUM(
                        CASE
                            WHEN deals.status = "ganado"
                            THEN deals.value
                            ELSE 0
                        END
                    ) AS venta_neta'
                ),
                // DB::raw('
                //     (
                //         (
                //             SUM(
                //                 CASE
                //                 WHEN deals.status = "en proceso"
                //                 THEN 1
                //                 ELSE 0
                //                 END
                //             )
                //             +
                //             SUM(
                //                 CASE
                //                 WHEN deals.status = "perdido"
                //                 THEN 1
                //                 ELSE 0
                //                 END
                //             )
                //         )
                //         /
                //         SUM(
                //             CASE
                //             WHEN deals.status = "ganado"
                //             THEN 1
                //             ELSE 0
                //             END
                //         )
                //     ) AS hit_rate
                // '),
                DB::raw('
                    (
                        (
                            SUM(
                                CASE
                                WHEN deals.status = "ganado"
                                THEN 1
                                ELSE 0
                                END
                            )
                            *
                            100
                        )
                        /
                        COUNT(deals.owner_id)
                    ) AS hit_rate
                '),
                DB::raw('
                    (
                        SUM(
                            CASE
                                WHEN deals.status = "ganado"
                                THEN deals.value
                                ELSE 0
                            END
                        )
                        /
                        (COUNT(deals.owner_id) * 450)
                    ) AS roa

                '),
            )
            // ->leftJoin('users', 'deals.owner_id', 'users.id')
            // ->leftJoin('deals', 'deals.owner_id', 'users.id')
            ->leftJoin('deals', function($join) use ($request) {
                $join->on('deals.owner_id', '=', 'users.id');
                // $join->where('deals.type', '=', DealTypeEnum::Cotizado->value);
                $join->whereNull('deals.deleted_at');
                if ($request->filter['since'] ?? false) {
                    $join->where('deals.created_at', '>=', $request->filter['since']);
                }

                if ($request->filter['until'] ?? false) {
                    $join->where('deals.created_at', '<=', $request->filter['until']);
                }
            })
            // ->where('deals.type', DealTypeEnum::Cotizado->value)
            ->whereNotNull('users.branch')
            // ->where(DB::raw('COUNT(deals.owner_id)'), '>', 0)
            ->when($usersId = auth()->user()->getAssignedUsersIdByRole(), function($query) use($usersId){
                $query->whereIn('deals.owner_id', $usersId);
            })
            ->groupBy('users.id')
            ->havingRaw('COUNT(deals.owner_id) > ?', [0])
            ->allowedFilters([
                AllowedFilter::exact('branch', 'users.branch'),
                AllowedFilter::callback(
                    'since',
                    function (Builder $query, $value) {
                        return $query;

                        // $query->where('deals.created_at', '>=', $value);
                    }
                ),
                AllowedFilter::callback(
                    'until',
                    function (Builder $query, $value) {
                        return $query;

                        // $query->where('deals.created_at', '<=', $value);
                    }
                ),
                AllowedFilter::callback(
                    'source',
                    fn (Builder $query, $value) => $query->whereIn('deals.source_id', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
            ])
            ->allowedSorts([
                'hit_rate',
                'ganados',
                'perdidos',
                'vivos',
                'venta_neta',
                'total',
                'inversion',
                'roa',
                AllowedSort::field('name', 'users.name'),
                AllowedSort::field('owner', 'users.name'),
            ])
            ->defaultSort('-venta_neta')
            ->get();

        // foreach ($deals as $deal) {
        //     $deal->hit_rate = ($deal->vivos + $deal->perdidos > 0 && $deal->ganados > 0)
        //         ? ($deal->vivos + $deal->perdidos) / $deal->ganados
        //         : 0;
        // }

        // dd($deals->count());
        // $response = $deals->where('total', '>', 0)->toArray();
        $response = $deals->toArray();
        // $foundOwners = $deals->pluck('owner_id')->toArray();
        // $branchFilter = $request->filter['branch'] ?? null;

        // $users = User::query()
        //     ->when($branchFilter, function($query) use($branchFilter) {
        //         $query->where('branch', $branchFilter);
        //     })
        //     ->when($usersId = auth()->user()->getAssignedUsersIdByRole(), function($query) use($usersId){
        //         $query->whereIn('id', $usersId);
        //     })
        //     ->get();


        // foreach ($users as $user) {

        //     if (! in_array($user->id, $foundOwners)) {
        //         $response[] = [
        //             "branch" => $user->branch,
        //             "owner" => $user->name,
        //             "avatar_url" => $user->avatar_url,
        //             "total" => 0,
        //             "ganados" => 0,
        //             "perdidos" => 0,
        //             "vivos" => 0,
        //             "venta_neta" => 0,
        //             "hit_rate" => 0
        //         ];
        //     }
        // }

        return response()->json([
            'data' => $response,
            'meta' => [
                'total' => $deals->sum('total'),
                'total_venta_neta' => $deals->sum('venta_neta'),
                'total_ganados' => $deals->sum('ganados'),
                'total_perdidos' => $deals->sum('perdidos'),
                'total_vivos' => $deals->sum('vivos'),
            ]
        ]);
    }

}
