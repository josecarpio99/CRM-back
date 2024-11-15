<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Models\Customer;
use App\Enums\DealTypeEnum;
use App\Mail\NewAAACotizado;
use Illuminate\Http\Request;
use App\Enums\DealStatusEnum;
use App\Services\DealService;
use Illuminate\Support\Carbon;
use App\Mail\NewAAAOpportunity;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\DealResource;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Deal\StoreDealRequest;
use App\Http\Requests\Deal\UpdateDealRequest;

class DealController extends ApiController
{
    public function __construct() {
        $this->middleware('can:update,deal')->only('update');
        $this->middleware('can:view,deal')->only('show');
        $this->middleware('can:delete,deal')->only('delete');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? $this->getDefaultPageLimit();;

        $deals = QueryBuilder::for(Deal::class)
            ->select(
                'deals.*',
                'customers.company_name as customer_name',
                'users.branch as branch',
                'tasks.due_at',
            )
            ->with(['customer', 'owner', 'creator', 'source', 'category', 'lastActiveTask.owner', 'mediaProfitability'])
            ->leftJoin('users', 'deals.owner_id', 'users.id')
            ->leftJoin('customers', 'deals.customer_id', 'customers.id')
            ->leftJoin('categories', 'customers.category_id', 'categories.id')
            ->leftJoin('tasks', function ($join) {
                $join->on('tasks.id', '=', DB::raw(
                    '(SELECT tasks.id FROM tasks WHERE tasks.taskable_id = deals.id AND tasks.taskable_type = "App\\\\Models\\\\Deal" AND done = 0 ORDER BY due_at ASC LIMIT 1)'
                ));
            })
            ->allowedFilters([
                'name',
                AllowedFilter::exact('type'),
                AllowedFilter::exact('created_by_lead_qualifier'),
                AllowedFilter::exact('source', 'deals.source_id'),
                // AllowedFilter::exact('category', 'customers.category_id'),
                AllowedFilter::partial('customer_name', 'customer.name'),
                AllowedFilter::callback(
                    'value',
                    function (Builder $query, $value) {
                        [$minValue, $maxValue] = $value;
                        return $query->whereBetween('value', [$minValue, $maxValue]);
                    }
                ),
                AllowedFilter::callback(
                    'category',
                    fn (Builder $query, $value) => $query->whereIn('customers.category_id', gettype($value) == 'string' ? explode(',', $value) : $value)
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
                AllowedFilter::callback('created_at', function(Builder $query, $value) {
                    if (is_array($value)) {
                        $query->where(function($query) use($value) {
                            if ($value[0]) {
                                $query->where('deals.created_at', '>=', $value[0]);
                            }

                            if ($value[1]) {
                                $query->where(
                                    'deals.created_at',
                                    '<=',
                                     Carbon::parse($value[1])->endOfDay()
                                );
                            }
                        });
                    } else {
                        $query->filterDateByStr($value, 'deals.created_at');
                    }
                }),
                AllowedFilter::callback('closed_at', function(Builder $query, $value) {
                    if (is_array($value)) {
                        $query->where(function($query) use($value) {
                            if ($value[0]) {
                                $query->where('deals.stage_moved_at', '>=', $value[0]);
                            }

                            if ($value[1]) {
                                $query->where(
                                    'deals.stage_moved_at',
                                    '<=',
                                     Carbon::parse($value[1])->endOfDay()
                                );
                            }
                        });
                    } else {
                        $query->filterDateByStr($value, 'deals.stage_moved_at');
                    }
                }),
                AllowedFilter::callback('search', function(Builder $query, $value) {
                    $query->where(function($query) use($value) {
                        $query->where('deals.name', 'like', "%$value%")
                            ->orWhere('deals.status', 'like', "%$value%")
                            ->orWhere('deals.value', 'like', "%$value%")
                            ->orWhere('users.branch', 'like', "%$value%")
                            ->orWhere('users.name', 'like', "%$value%")
                            // ->orWhere('customers.name', 'like', "%$value%")
                            ->orWhere('customers.company_name', 'like', "%$value%");
                    });
                }),
                AllowedFilter::callback(
                    'next_task',
                    function(Builder $query, $value) {
                        $query->whereNotNull('tasks.id');
                    }
                )
            ])
            ->allowedSorts([
                'name',
                'customer_name',
                // AllowedSort::field('created_at', 'deals.created_at'),
                AllowedSort::field('category', 'categories.name'),
                AllowedSort::field('owner', 'users.name'),
                AllowedSort::field('closed_at', 'deals.stage_moved_at'),
                AllowedSort::field('next_task', 'tasks.due_at'),
                'created_at',
                'category_id',
                'estimated_size',
                'value'
            ])
            ->defaultSort('-created_at')
            ->allowedIncludes(['category', 'source', 'pipeline', 'notes']);

        $usersId = auth()->user()->getAssignedUsersIdByRole();

        if ($usersId !== null) {
            $deals->whereIn('deals.owner_id', $usersId);
        }


        // dd($deals->toSql());

        $branchCDMX = ($deals->clone())->where('branch','CDMX');
        $branchAGS = ($deals->clone())->where('branch','AGS');
        $branchMTY = ($deals->clone())->where('branch','MTY');
        $branchQRO = ($deals->clone())->where('branch','QRO');
        $prospeccionSource = ($deals->clone())->where('deals.source_id', 1);
        $publicidadSource = ($deals->clone())->where('deals.source_id', 2);
        $recompraSource = ($deals->clone())->where('deals.source_id', 3);

        // $oportunidades = ($deals->clone())->where('deals.type', DealTypeEnum::Oportunidad->value);
        // $cotizados = ($deals->clone())->where('deals.type', DealTypeEnum::Cotizado->value);

        $meta = [
            'valueAvg' => ($deals->clone())->avg('value'),
            'valueSum' => ($deals->clone())->sum('value'),
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
            ],
        ];

        if(auth()->user()->hasRole(RoleEnum::Director->value)) {
            $meta['branch'] = [
                'CDMX' => [
                    'sum' => $branchCDMX->sum('value'),
                    'count' => $branchCDMX->count(),
                ],
                'AGS' => [
                    'sum' => $branchAGS->sum('value'),
                    'count' => $branchAGS->count(),
                ],
                'MTY' => [
                    'sum' => $branchMTY->sum('value'),
                    'count' => $branchMTY->count(),
                ],
                'QRO' => [
                    'sum' => $branchQRO->sum('value'),
                    'count' => $branchQRO->count(),
                ],
            ];
        }

        return DealResource::collection(($deals->paginateData($limit)))
            ->additional(['meta' => $meta]);

        // return DealResource::collection(($deals->paginateData($limit)))
        // ->additional(['meta' => [
        //     'valueAvg' => ($deals->clone())->avg('value'),
        //     'valueSum' => ($deals->clone())->sum('value'),
        //     // 'AAA_customers' => ($deals->clone())->where('deals.category_id', 5)->count(),
        //     // 'estimatedSizeSum' => ($deals->clone())->sum('value'),
        //     'branch' => [
        //         'CDMX' => [
        //             'sum' => $branchCDMX->sum('value'),
        //             'count' => $branchCDMX->count(),
        //         ],
        //         'AGS' => [
        //             'sum' => $branchAGS->sum('value'),
        //             'count' => $branchAGS->count(),
        //         ],
        //         'MTY' => [
        //             'sum' => $branchMTY->sum('value'),
        //             'count' => $branchMTY->count(),
        //         ],
        //         'QRO' => [
        //             'sum' => $branchQRO->sum('value'),
        //             'count' => $branchQRO->count(),
        //         ],
        //     ],
        //     'source' => [
        //         'prospeccion' => [
        //             'sum' => $prospeccionSource->sum('value'),
        //             'count' => $prospeccionSource->count(),
        //         ],
        //         'publicidad' => [
        //             'sum' => $publicidadSource->sum('value'),
        //             'count' => $publicidadSource->count(),
        //         ],
        //         'recompra' => [
        //             'sum' => $recompraSource->sum('value'),
        //             'count' => $recompraSource->count(),
        //         ],
        //     ],
        //     // 'oportunidad' => [
        //     //     'avg' => $oportunidades->avg('value'),
        //     //     'sum' => $oportunidades->sum('value'),
        //     // ],
        //     // 'cotizado' => [
        //     //     'avg' => $cotizados->avg('value'),
        //     //     'sum' => $cotizados->sum('value'),
        //     // ],
        // ]]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDealRequest $request, DealService $dealService)
    {
        $data = $request->validated();

        $deal = $dealService->store($data, $request->user());

        // if ($deal->value >= 1_000_000) {
        //     if ($deal->type == DealTypeEnum::Oportunidad->value) {

        //         try {
        //             \Mail::to(['manager@test.com', 'manager2@test.com'])->send(new NewAAAOpportunity($deal));
        //         } catch (\Throwable $th) {
        //             //TODO...
        //         }
        //     } else {
        //         try {
        //             \Mail::to(['manager@test.com', 'manager2@test.com'])->send(new NewAAACotizado($deal));
        //         } catch (\Throwable $th) {
        //             //TODO...
        //         }
        //     }
        // }

        return new DealResource($deal);
    }

    /**
     * Display the specified resource.
     */
    public function show(Deal $deal)
    {
        $deal->load(['customer', 'owner', 'source', 'category', 'stage', 'creator', 'associatedContacts', 'media', 'contacts']);

        $deal->load(['notes' => function($query) {
            $query->with('user');
        }]);

        $deal->load(['tasks' => function($query) {
            $query->with('owner', 'doneBy');
        }]);

        return new DealResource($deal);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDealRequest $request, Deal $deal)
    {
        $data = $request->validated();

        if (
            $request->status == DealStatusEnum::Won->value ||
            $request->status == DealStatusEnum::Lost->value
        ) {
            $data['stage_moved_at'] = now();
        } else {
            $data['stage_moved_at'] = null;
        }

        $deal->update($data);

        return new DealResource($deal);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Deal $deal)
    {
        $deal->delete();

        $this->responseNoContent();
    }
}
