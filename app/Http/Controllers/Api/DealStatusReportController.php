<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use Illuminate\Http\Request;
use App\Enums\DealStatusEnum;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;

class DealStatusReportController extends ApiController
{
    public function __invoke(Request $request)
    {
        $limit = $request->limit ?? $this->getDefaultPageLimit();;

        $deals = QueryBuilder::for(Deal::class)
            ->select(
                'deals.*',
                'customers.name as customer_name',
                'users.branch as branch'
            )
            ->leftJoin('users', 'deals.owner_id', 'users.id')
            ->allowedFilters([
                AllowedFilter::exact('type'),
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
            ])

            ;

        $usersId = auth()->user()->getAssignedUsersIdByRole();

        if ($usersId !== null) {
            $deals->whereIn('deals.owner_id', $usersId);
        }

        $wonStatus = ($deals->clone())->where('deals.status', DealStatusEnum::Won->value);
        $lostStatus = ($deals->clone())->where('deals.status', DealStatusEnum::Lost->value);
        $inProgressStatus = ($deals->clone())->where('deals.status', DealStatusEnum::InProgress->value);

        $wonStatusSum = $wonStatus->sum('value');
        $wonStatusCount = $wonStatus->count();
        $lostStatusSum = $lostStatus->sum('value');
        $lostStatusCount = $lostStatus->count();
        $inProgressStatusSum = $inProgressStatus->sum('value');
        $inProgressStatusCount = $inProgressStatus->count();

        $hitRateQuantity = ($inProgressStatusCount + $lostStatusCount > 0 && $wonStatusCount > 0)
            ? ($inProgressStatusCount + $lostStatusCount) / $wonStatusCount
            : 0;

        $hitRateAmount = ($wonStatusSum + $lostStatusSum > 0 && $wonStatusCount > 0)
            ? $wonStatusSum * 100 / ($wonStatusSum + $lostStatusSum)
            : 0;

        return response()->json([
            'data' => [
                'hit_rate' => [
                    'amount' => $hitRateAmount,
                    'count' => $hitRateQuantity,
                ],
                'won' => [
                    'sum' => $wonStatusSum,
                    'count' => $wonStatusCount,
                ],
                'lost' => [
                    'sum' => $lostStatusSum,
                    'count' => $lostStatusCount,
                ],
                'inProgress' => [
                    'sum' => $inProgressStatus->sum('value'),
                    'count' => $inProgressStatus->count(),
                ],
            ]
        ]);
    }

}
