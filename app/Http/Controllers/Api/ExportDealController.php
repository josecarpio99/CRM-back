<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ExportDealController extends ApiController
{

    protected $type = null;

    public function __invoke(Request $request)
    {
        $this->type = $request->filter['type'] ?? null;

        $deals = QueryBuilder::for(Deal::class)
            ->select(
                'deals.*',
                'customers.name as customer_name',
                'users.branch as branch',
                'tasks.due_at',
            )
            ->with(['customer.category', 'owner', 'creator', 'source', 'category', 'lastActiveTask.owner'])
            ->withCount(['quotes', 'mediaProfitability'])
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
                AllowedFilter::callback('search', function(Builder $query, $value) {
                    $query->where(function($query) use($value) {
                        $query->where('deals.name', 'like', "%$value%")
                            ->orWhere('deals.status', 'like', "%$value%")
                            ->orWhere('deals.value', 'like', "%$value%")
                            ->orWhere('users.branch', 'like', "%$value%")
                            ->orWhere('users.name', 'like', "%$value%")
                            ->orWhere('customers.name', 'like', "%$value%")
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

        $rows = [];

        $dealsArr = $deals->get()->toArray();

        foreach ($dealsArr as $deal) {
            if ($columns = $this->getColumns($request)) {
                $row = [];

                foreach ($columns as $column) {
                    $row[$this->getColumnTitle($column)] = $this->getColumnValue($column, $deal);
                }

                $rows[] = $row;
            } else {
                $rows[] = [
                    'ID' => $deal['id'],
                    'NOMBRE' => $deal['name'],
                ];
            }

        }

        $fileName = 'publicidad_';

        if (! empty($this->type)) {
            $fileName = $this->type == 'oportunidad' ? 'oportunidades_' : 'cotizaciones_';
        }

        return SimpleExcelWriter::streamDownload($fileName . date('d-m-Y') . '.csv')
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
            'value' => $data['value'],
            'status' => $data['status'],
            'estimated_close_date_range' => $data['estimated_close_date_range'],
            'created_at' => date('d-m-Y H:i', strtotime($data['created_at'])),
            'closed_at' => date('d-m-Y H:i', strtotime($data['stage_moved_at'])),
            'owner' => $data['owner']['name'] ?? null,
            'contact_name' => $data['customer']['name'] ?? null,
            'creator' => $data['creator']['name'] ?? null,
            'branch' => $data['owner']['branch'] ?? null,
            'source' => $data['source']['name'] ?? null,
            'category' => $data['customer']['category']['name'] ?? null,
            'next_task' => $data['last_active_task']['content'] ?? null,
            'quote' => $data['quotes_count'] > 0 ? 'Existe' : '',
            'profitability_format' => $data['media_profitability_count'] > 0 ? 'Existe' : '',
        };
    }

    protected function getColumnTitle($column)
    {
        return match ($column) {
            'id' => 'ID',
            'name' => 'NOMBRE',
            'value' => 'VALOR',
            'status' => 'STATUS',
            'estimated_close_date_range' => 'FECHA DE CIERRE PREVISTA',
            'contact_name' => 'NOMBRE DEL CONTACTO',
            'owner' => 'PROPIEDAD',
            'creator' => 'CREADO POR',
            'branch' => 'SUCURSAL',
            'closed_at' => 'FECHA DE CIERRE',
            'created_at' => 'AGREGADO EL',
            'category' => 'CLASIFICACIÓN',
            'source' => 'ORIGEN',
            'next_task' => 'PRÓXIMA TAREA',
            'quote' => 'COTIZACIÓN',
            'profitability_format' => 'FORMATO DE RENTABILIDAD',
        };
    }

}
