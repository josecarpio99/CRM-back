<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;

class DealSourceReportController extends ApiController
{

    public function __invoke(Request $request)
    {
        $deals = QueryBuilder::for(Deal::class)
            ->select(
                'deals.*',
                'customers.name as customer_name',
                'users.branch as branch',
                'tasks.due_at',
            )
            ->with(['customer', 'owner', 'creator', 'source', 'category', 'lastActiveTask.owner'])
            ->leftJoin('users', 'deals.owner_id', 'users.id')
            ->leftJoin('customers', 'deals.customer_id', 'customers.id')
            ->leftJoin('categories', 'customers.category_id', 'categories.id')
            ->allowedFilters([
                'name',
                AllowedFilter::exact('type'),
                AllowedFilter::exact('source', 'deals.source_id'),
                AllowedFilter::exact('category', 'customers.category_id'),
                AllowedFilter::partial('customer_name', 'customer.name'),
                AllowedFilter::callback(
                    'value',
                    function (Builder $query, $value) {
                        [$minValue, $maxValue] = $value;
                        return $query->whereBetween('value', [$minValue, $maxValue]);
                    }
                ),
                AllowedFilter::callback(
                    'source',
                    fn (Builder $query, $value) => $query->whereIn('deals.source_id', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
                AllowedFilter::callback(
                    'stage',
                    fn (Builder $query, $value) => $query->whereIn('deal_pipeline_stage_id', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
                AllowedFilter::callback(
                    'creator',
                    fn (Builder $query, $value) => $query->whereIn('deals.created_by', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
                AllowedFilter::callback(
                    'owner',
                    fn (Builder $query, $value) => $query->whereIn('deals.owner_id', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
                AllowedFilter::callback(
                    'branch',
                    fn (Builder $query, $value) => $query->whereIn('users.branch', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
                AllowedFilter::callback(
                    'status',
                    fn (Builder $query, $value) => $query->whereIn('deals.status', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
                AllowedFilter::callback('created_at', fn (Builder $query, $value) => $query->filterDateByStr($value, 'deals.created_at')),
                AllowedFilter::callback('closed_at', fn (Builder $query, $value) => $query->filterDateByStr($value, 'deals.stage_moved_at')),
            ]);

        $usersId = auth()->user()->getAssignedUsersIdByRole();

        if ($usersId !== null) {
            $deals->whereIn('deals.owner_id', $usersId);
        }

        $prospeccionSource = ($deals->clone())->where('deals.source_id', 1);
        $publicidadSource = ($deals->clone())->where('deals.source_id', 2);
        $recompraSource = ($deals->clone())->where('deals.source_id', 3);

        return response()->json([
            'source' => [
                'prospeccion' => [
                    'sum' => $prospeccionSource->sum('value'),
                    'count' => $prospeccionSource->count(),
                ],
                'publicidad' => [
                    'sum' => $publicidadSource->sum('value'),
                    'count' => $publicidadSource->count(),
                ],
                'recompra' => [
                    'sum' => $recompraSource->sum('value'),
                    'count' => $recompraSource->count(),
                ],
            ]
        ]);
    }
}
