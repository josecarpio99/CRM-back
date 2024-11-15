<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use App\Models\Branch;
use App\Models\Investment;
use App\Enums\DealTypeEnum;
use Illuminate\Http\Request;
use App\Enums\DealStatusEnum;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;

class BranchReportPeriodController extends ApiController
{
    public function __invoke(Request $request)
    {
        $deals = QueryBuilder::for(Deal::class)
            ->select(
                'users.branch as branch',
                DB::raw('COUNT(*) AS total'),
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
                )
            )
            ->leftJoin('users', 'deals.owner_id', 'users.id')
            // ->where('deals.type', DealTypeEnum::Cotizado->value)
            ->whereNotNull('users.branch')
            ->when($usersId = auth()->user()->getAssignedUsersIdByRole(), function($query) use($usersId){
                $query->whereIn('deals.owner_id', $usersId);
            })
            ->groupBy('users.branch')
            ->allowedFilters([
                AllowedFilter::callback(
                    'since',
                    function (Builder $query, $value) {
                        $query->where(function($query) use($value) {
                            $query->where('deals.created_at', '>=', $value);

                            $query->orWhere(function($query) use($value) {
                                $query->where('deals.status', DealStatusEnum::Won->value)
                                    ->where('deals.stage_moved_at', '>=', $value);
                            });
                        });
                    }
                ),
                AllowedFilter::callback(
                    'until',
                    function (Builder $query, $value) {
                        $query->where(function($query) use($value) {
                            $query->where('deals.created_at', '<=', $value);

                            $query->orWhere(function($query) use($value) {
                                $query->where('deals.status', DealStatusEnum::Won->value)
                                    ->where('deals.stage_moved_at', '<=', $value);
                            });
                        });
                    }
                ),
                AllowedFilter::callback(
                    'source',
                    fn (Builder $query, $value) => $query->whereIn('deals.source_id', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
            ])
            ->defaultSort('-venta_neta')
            ->get();

        foreach ($deals as $deal) {
            // $deal->hit_rate = ($deal->vivos + $deal->perdidos > 0 && $deal->ganados > 0)
            //     ? ($deal->vivos + $deal->perdidos) / $deal->ganados
            //     : 0;

            $deal->hit_rate = ($deal->total > 0 && $deal->ganados > 0)
                ? ($deal->ganados * 100) / $deal->total
                : 0;
        }

        $foundBranches = $deals->pluck('branch')->toArray();

        $response = $deals->whereNotNull('branch')->toArray();

        $sinceFilter = $request->filter['since'] ?? null;
        $beforeFilter = $request->filter['before'] ?? null;

        $investmentQuery = Investment::query()
            ->when($sinceFilter, function($query) use($sinceFilter) {
                $query->where('date', '>=', $sinceFilter);
            })
            ->when($beforeFilter, function($query) use($beforeFilter) {
                $query->where('date', '<=', $beforeFilter);
            });

        foreach (['NY', 'FL', 'TX', 'NC'] as $value) {

            $branch = Branch::where('name', $value)->first();

            if (! $branch) continue;

            $branchInvestmentSum = ($investmentQuery->clone())->where('branch_id', $branch->id)->sum('amount');

            if (! in_array($value, $foundBranches)) {
                $response[] = [
                    "branch" => $value,
                    "total" => 0,
                    "ganados" => 0,
                    "perdidos" => 0,
                    "vivos" => 0,
                    "venta_neta" => 0,
                    "hit_rate" => 0,
                    "roa" => 0,
                    "inversion" => $branchInvestmentSum,
                ];
            } else {
                foreach ($response as $key => $row) {
                    if ($row['branch'] == $value) {
                        $response[$key]['inversion'] = $branchInvestmentSum;

                        $response[$key]['roa'] = ($row['venta_neta'] > 0 && $branchInvestmentSum > 0)
                            ? $row['venta_neta'] / $branchInvestmentSum
                            : 0;
                        break;
                    }
                }
            }
        }

        return response()->json([
            'data' => $response,
            'meta' => [
                'total_venta_neta' => $deals->sum('venta_neta'),
                'total_inversion' => $investmentQuery->sum('amount'),
                'total_neto' => $deals->sum('venta_neta') - $investmentQuery->sum('amount'),
            ]
        ]);
    }

}
