<?php

namespace App\Http\Controllers\Api;

use App\Models\Lead;
use App\Models\User;
use App\Models\Branch;
use App\Enums\RoleEnum;
use App\Mail\NewAAALead;
use App\Mail\NewLeadCreated;
use Illuminate\Http\Request;
use App\Mail\NewLeadAssigned;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\LeadResource;
use Spatie\QueryBuilder\AllowedSort;
use App\Http\Filters\FilterDateByStr;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Lead\StoreLeadRequest;
use App\Http\Requests\Lead\UpdateLeadRequest;

class LeadController extends ApiController
{
    public function __construct() {
        $this->middleware('can:update,lead')->only('update');
        $this->middleware('can:view,lead')->only('show');
        $this->middleware('can:delete,lead')->only('delete');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? $this->getDefaultPageLimit();;

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
                // AllowedFilter::exact('category', 'category_id'),
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
                    fn (Builder $query, $value) => $query->whereIn('status', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
                AllowedFilter::callback('created_at', function(Builder $query, $value) {
                    if (is_array($value)) {
                        $query->where(function($query) use($value) {
                            if ($value[0]) {
                                $query->where('leads.created_at', '>=', $value[0]);
                            }

                            if ($value[1]) {
                                $query->where(
                                    'leads.created_at',
                                    '<=',
                                     Carbon::parse($value[1])->endOfDay()
                                );
                            }
                        });
                    } else {
                        $query->filterDateByStr($value, 'leads.created_at');
                    }
                }),
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

        return LeadResource::collection(($leads->paginateData($limit)))
            ->additional(['meta' => [
                'totalAssigned' => ($leads->clone())->assigned()->count(),
                'totalNew' => ($leads->clone())->new()->count(),
                'totalUnqualified' => ($leads->clone())->unqualified()->count()
            ]]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLeadRequest $request)
    {
        $data = $request->validated();

        $data['created_by'] = auth()->user()->id;

        $leadData = [
            'company_name' => $data['company_name'] ?? null,
            'razon_social' => $data['razon_social'] ?? null,
            'city' => $data['city'] ?? null,
            'source_id' => $data['source_id'] ?? null,
            'owner_id' => $data['owner_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'created_by' => $data['created_by'] ?? null,
            'requirement' => $data['requirement'] ?? null,
        ];

        $contactData = [
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['mobile'] ?? null,
        ];

        $lead = Lead::create($leadData);

        $lead->contacts()->create($contactData);

        if (
            $lead->owner_id != $lead->created_by
        ) {
            try {
                \Mail::to($lead->owner)->send(new NewLeadAssigned($lead));
            } catch (\Throwable $th) {
                //TODO...
            }
        }

        if ($lead->category_id == 5) {
            try {
                \Mail::to(['manager@test.com', 'manager2@test.com'])->send(new NewAAALead($lead));

                $owner = $lead->owner;

                if ($owner->role == RoleEnum::Advisor->value) {
                    $branch = Branch::where('name', $owner->branch)->first();

                    $usersToNotify = User::query()
                        ->where('id', '<>', 44)
                        ->where('id', '<>', $owner->id)
                        ->where('id', '<>', $data['created_by'])
                        ->where(function($query) use($branch){
                            $query->where('role', RoleEnum::Admin->value)
                                ->where(function($query) use($branch) {
                                    $query->where('branch', $branch->name)
                                        ->orWhereHas('branches', function($query) use($branch) {
                                            $query->where('branch_id', $branch->id);
                                        });
                                });
                        })
                        ->orWhere(function($query) use($owner){
                            $query->where('role', RoleEnum::TeamLeader->value)
                                ->whereHas('assignedUsers', function($query) use($owner) {
                                    $query->where('user_id', $owner->id);
                                });
                        })
                        ->get();

                    if ($usersToNotify->count() > 0) {
                        \Mail::to($usersToNotify)->send(new NewAAALead($lead));
                    }
                }

            } catch (\Throwable $th) {
                //TODO...
            }
        }

        return new LeadResource($lead);
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead)
    {
        $lead->load(['sector', 'owner', 'source', 'category', 'media', 'creator', 'contacts']);

        $lead->load(['notes' => function($query) {
            $query->with('user');
        }]);

        $lead->load(['tasks' => function($query) {
            $query->with('owner', 'doneBy');
        }]);

        return new LeadResource($lead);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLeadRequest $request, Lead $lead)
    {
        $lead->update($request->validated());

        return new LeadResource($lead);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();

        $this->responseNoContent();
    }
}
