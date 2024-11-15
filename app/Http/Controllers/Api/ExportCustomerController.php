<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ExportCustomerController extends ApiController
{

    public function __invoke(Request $request)
    {
        $daysSinceLastWonDealSelect = '(DATEDIFF(NOW(), last_won_deal.stage_moved_at))';

        $customers = QueryBuilder::for(Customer::class)
            ->select(
                'customers.*',
                DB::raw("$daysSinceLastWonDealSelect  as days_since_last_won_deal"),
            )
            ->with(['category', 'owner', 'source', 'lastActiveTask.owner', 'latestWonDeal'])
            ->withCount(['activeQuotes', 'activeOpportunities', 'activeDeals'])
            ->leftJoin('users', 'customers.owner_id', 'users.id')
            ->leftJoin('tasks', function ($join) {
                $join->on('tasks.id', '=', DB::raw(
                    '(SELECT tasks.id FROM tasks WHERE tasks.taskable_id = customers.id AND tasks.taskable_type = "App\\\\Models\\\\Customer" AND done = 0 ORDER BY due_at ASC LIMIT 1)'
                ));
            })
            ->leftJoin('deals as last_won_deal', function ($join) {
                $join->on('last_won_deal.id', '=', DB::raw(
                    '(SELECT deals.id FROM deals WHERE deals.customer_id = customers.id AND deals.status = "ganado" ORDER BY stage_moved_at DESC LIMIT 1)'
                ));
            })
            ->allowedFilters([
                'name',
                'company_name',
                AllowedFilter::exact('star'),
                // AllowedFilter::exact('category_id'),
                AllowedFilter::exact('customer_status'),
                AllowedFilter::callback(
                    'category',
                    fn (Builder $query, $value) => $query->whereIn('category_id', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
                AllowedFilter::callback(
                    'source',
                    fn (Builder $query, $value) => $query->whereIn('source_id', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
                AllowedFilter::callback(
                    'status',
                    fn (Builder $query, $value) => $query->whereIn('customer_status', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
                AllowedFilter::callback(
                    'owner',
                    fn (Builder $query, $value) => $query->whereIn('customers.owner_id', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
                AllowedFilter::callback(
                    'branch',
                    fn (Builder $query, $value) => $query->whereIn('users.branch', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
                AllowedFilter::callback('created_at', function(Builder $query, $value) {
                    if (is_array($value)) {
                        $query->where(function($query) use($value) {
                            if ($value[0]) {
                                $query->where('customers.created_at', '>=', $value[0]);
                            }

                            if ($value[1]) {
                                $query->where(
                                    'customers.created_at',
                                    '<=',
                                     Carbon::parse($value[1])->endOfDay()
                                );
                            }
                        });
                    } else {
                        $query->filterDateByStr($value, 'customers.created_at');
                    }
                }),
                AllowedFilter::callback('last_sell_at', function(Builder $query, $value) {
                    if (is_array($value)) {
                        $query->where(function($query) use($value) {
                            if ($value[0]) {
                                $query->where('last_won_deal.stage_moved_at', '>=', $value[0]);
                            }

                            if ($value[1]) {
                                $query->where(
                                    'last_won_deal.stage_moved_at',
                                    '<=',
                                     Carbon::parse($value[1])->endOfDay()
                                );
                            }
                        });
                    } else {
                        $query->filterDateByStr($value, 'last_won_deal.stage_moved_at');
                    }
                }),
                AllowedFilter::callback('search', function(Builder $query, $value) {
                    $query->where(function($query) use($value) {
                        $query->where('customers.name', 'like', "%$value%")
                        ->orWhere('customers.company_name', 'like', "%$value%")
                        ->orWhere('customers.email', 'like', "%$value%")
                        ->orWhere('customers.phone', 'like', "%$value%")
                        ->orWhere('customers.mobile', 'like', "%$value%")
                        ->orWhere('users.branch', 'like', "%$value%")
                        ->orWhere('users.name', 'like', "%$value%");

                    });
                }),
                AllowedFilter::callback(
                    'days_since_last_won_deal',
                    function(Builder $query, $value) use($daysSinceLastWonDealSelect) {
                        $query->whereRaw("$daysSinceLastWonDealSelect >= $value");
                    }
                ),
                AllowedFilter::callback(
                    'next_task',
                    function(Builder $query, $value) {
                        $query->whereNotNull('tasks.id');
                    }
                )
            ])
            ->allowedSorts([
                'company_name',
                'name',
                'email',
                'created_at',
                'active_opportunities_count',
                'active_quotes_count',
                AllowedSort::field('owner', 'users.name'),
                AllowedSort::field('next_task', 'tasks.due_at'),
                AllowedSort::field('last_sell_at', 'last_won_deal.stage_moved_at'),
                AllowedSort::field('days_since_last_won_deal'),
            ])
            ->defaultSort('-created_at')
            ->allowedIncludes(['potentialStatus', 'country', 'sector', 'notes']);

        $usersId = auth()->user()->getAssignedUsersIdByRole();

        if ($usersId !== null) {
            $customers->whereIn('customers.owner_id', $usersId);
        }

        $rows = [];

        $customersArr = $customers->get()->toArray();

        foreach ($customersArr as $customer) {
            if ($columns = $this->getColumns($request)) {
                $row = [];

                foreach ($columns as $column) {
                    $row[$this->getColumnTitle($column)] = $this->getColumnValue($column, $customer);
                }

                $rows[] = $row;
            } else {
                $rows[] = [
                    'ID' => $customer['id'],
                    'NOMBRE' => $customer['name'],
                ];
            }

        }

        return SimpleExcelWriter::streamDownload('cartera_' . date('d-m-Y') . '.csv')
            ->addRows($rows)
            ->toBrowser();
    }

    protected function getColumns(Request $request): array|null
    {
        if(! $request->columns) return null;

        return explode(',', $request->columns);
    }

    protected function getColumnValue(string $column, array $data)
    {
        return match ($column) {
            'id' => $data['id'],
            'name' => $data['name'],
            'company_name' => $data['company_name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'created_at' => date('d-m-Y H:i', strtotime($data['created_at'])),
            'owner' => $data['owner']['name'] ?? null,
            'branch' => $data['owner']['branch'] ?? null,
            'category' => $data['category']['name'] ?? null,
            'source' => $data['source']['name'] ?? null,
            'active_opportunities_count' => $data['active_opportunities_count'] ?? null,
            'active_quotes_count' => $data['active_quotes_count'] ?? null,
            'active_deals_count' => $data['active_deals_count'] ?? null,
            'next_task' => $data['last_active_task']['content'] ?? null,
            'last_sell_at' => Arr::get($data, 'latest_won_deal.stage_moved_at') ?
                date('d-m-Y H:i', strtotime(Arr::get($data, 'latest_won_deal.stage_moved_at')))
                : null,
            'days_since_last_won_deal' => $data['days_since_last_won_deal'] ?? null
        };
    }

    protected function getColumnTitle($column)
    {
        return match ($column) {
            'id' => 'ID',
            'name' => 'NOMBRE DEL CONTACTO',
            'company_name' => 'NOMBRE DE LA EMPRESA',
            'email' => 'CORREO',
            'owner' => 'PROPIEDAD',
            'branch' => 'SUCURSAL',
            'mobile' => 'TELÉFONO',
            'created_at' => 'AGREGADO EL',
            'category' => 'CLASIFICACIÓN',
            'source' => 'ORIGEN',
            'next_task' => 'PRÓXIMA TAREA',
            'active_opportunities_count' => 'OPORTUNIDADES ACTIVAS',
            'active_quotes_count' => 'COTIZACION ACTIVAS',
            'active_deals_count' => 'PROYECTOS ACTIVOS',
            'last_sell_at' => 'FECHA DE ÚLTIMA VENTA',
            'days_since_last_won_deal' => 'DÍAS DESDE LA ULTIMA VENTA'
        };
    }

}
