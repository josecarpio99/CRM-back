<?php

namespace App\Http\Controllers\Api;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ExportLeadController extends ApiController
{

    public function __invoke(Request $request)
    {
        $leads = QueryBuilder::for(Lead::class)
            ->select(
                'leads.*'
            )
            ->with(['owner', 'source', 'category', 'creator', 'lastActiveTask.owner'])
            ->leftJoin('users', 'leads.owner_id', 'users.id')
            ->leftJoin('tasks', function ($join) {
                $join->on('tasks.id', '=', DB::raw(
                    '(SELECT tasks.id FROM tasks WHERE tasks.taskable_id = leads.id AND tasks.taskable_type = "App\\\\Models\\\\Lead" AND done = 0 ORDER BY due_at ASC LIMIT 1)'
                ));
            })
            ->allowedFilters([
                AllowedFilter::partial('company_name'),
                AllowedFilter::partial('name'),
                AllowedFilter::exact('category', 'category_id'),
                AllowedFilter::callback(
                    'source',
                    fn (Builder $query, $value) => $query->whereIn('source_id', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
                AllowedFilter::callback(
                    'status',
                    fn (Builder $query, $value) => $query->whereIn('status', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
                AllowedFilter::callback('created_at', fn (Builder $query, $value) => $query->filterDateByStr($value, 'leads.created_at')),
                AllowedFilter::callback(
                    'owner',
                    fn (Builder $query, $value) => $query->whereIn('leads.owner_id', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
                AllowedFilter::callback(
                    'creator',
                    fn (Builder $query, $value) => $query->whereIn('created_by', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
                AllowedFilter::callback(
                    'branch',
                    fn (Builder $query, $value) => $query->whereIn('users.branch', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
                AllowedFilter::callback('search', function(Builder $query, $value) {
                    $query->where(function($query) use($value) {
                        $query->where('leads.name', 'like', "%$value%")
                            ->orWhere('leads.company_name', 'like', "%$value%")
                            ->orWhere('leads.email', 'like', "%$value%")
                            ->orWhere('leads.phone', 'like', "%$value%")
                            ->orWhere('leads.mobile', 'like', "%$value%")
                            ->orWhere('users.branch', 'like', "%$value%")
                            ->orWhere('users.name', 'like', "%$value%") ;
                    });

                })
            ])
            ->allowedSorts([
                'company_name',
                'name',
                'email',
                'created_at',
                AllowedSort::field('owner', 'users.name'),
                AllowedSort::field('next_task', 'tasks.due_at'),
            ])
            ->defaultSort('-created_at')
            ->allowedIncludes(['category', 'notes']);

        $usersId = auth()->user()->getAssignedUsersIdByRole();

        if ($usersId !== null) {
            $leads->whereIn('leads.owner_id', $usersId);
        }

        $rows = [];

        $leadsArr = $leads->get()->toArray();

        foreach ($leadsArr as $lead) {
            if ($columns = $this->getColumns($request)) {
                $row = [];

                foreach ($columns as $column) {
                    $row[$this->getColumnTitle($column)] = $this->getColumnValue($column, $lead);
                }

                $rows[] = $row;
            } else {
                $rows[] = [
                    'ID' => $lead['id'],
                    'NOMBRE' => $lead['name'],
                ];
            }

        }

        return SimpleExcelWriter::streamDownload('prospectos_' . date('d-m-Y') . '.csv')
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
            'creator' => $data['creator']['name'] ?? null,
            'branch' => $data['owner']['branch'] ?? null,
            'category' => $data['category']['name'] ?? null,
            'source' => $data['source']['name'] ?? null,
            'next_task' => $data['last_active_task']['content'] ?? null,
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
            'creator' => 'CREADO POR',
            'branch' => 'SUCURSAL',
            'mobile' => 'TELÉFONO',
            'created_at' => 'AGREGADO EL',
            'category' => 'CLASIFICACIÓN',
            'source' => 'ORIGEN',
            'next_task' => 'PRÓXIMA TAREA'
        };
    }

}
