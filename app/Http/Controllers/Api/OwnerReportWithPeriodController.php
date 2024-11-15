<?php

namespace App\Http\Controllers\Api;

use App\Enums\DealStatusEnum;
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

class OwnerReportWithPeriodController extends ApiController
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

                $join->where(function($query) use($request) {
                    $query->where(function($query) use($request) {
                        if ($request->filter['since'] ?? false) {
                            $query->where('deals.created_at', '>=', $request->filter['since']);
                        }

                        if ($request->filter['until'] ?? false) {
                            $query->where('deals.created_at', '<=', $request->filter['until']);
                        }
                    });

                    $query->orWhere(function($query) use($request) {
                        $query->where(function($query) use($request) {
                            if ($request->filter['since'] ?? false) {
                                $query->where('deals.status', DealStatusEnum::Won->value)
                                    ->where('deals.stage_moved_at', '>=', $request->filter['since']);
                            }
                        });
                        $query->where(function($query) use($request) {
                            if ($request->filter['until'] ?? false) {
                                $query->where('deals.status', DealStatusEnum::Won->value)
                                    ->where('deals.stage_moved_at', '<=', $request->filter['until']);
                            }
                        });
                        // $query->where('deals.status', DealStatusEnum::Won->value);
                        // if ($request->filter['since'] ?? false) {
                        //     $query->where('deals.stage_moved_at', '>=', $request->filter['since']);
                        // }

                        // if ($request->filter['until'] ?? false) {
                        //     $query->where('deals.stage_moved_at', '<=', $request->filter['until']);
                        // }
                    });

                });

            })
            // ->where('deals.type', DealTypeEnum::Cotizado->value)
            ->whereNotNull('users.branch')
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

        // dd($deals->toSql());
        $response = $deals->toArray();

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
