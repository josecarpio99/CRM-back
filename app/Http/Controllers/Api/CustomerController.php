<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Branch;
use App\Enums\RoleEnum;
use App\Models\Customer;
use Illuminate\Support\Str;
use App\Mail\NewAAACustomer;
use Illuminate\Http\Request;
use App\Mail\NewCustomerCreated;
use App\Mail\NewCustomerAssigned;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\CustomerResource;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;

class CustomerController extends ApiController
{
    public function __construct() {
        $this->middleware('can:update,customer')->only('update');
        $this->middleware('can:view,customer')->only('show');
        $this->middleware('can:delete,customer')->only('delete');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? $this->getDefaultPageLimit();
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
                    fn (Builder $query, $value) => $query->whereIn('customers.category_id', gettype($value) == 'string' ? explode(',', $value) : $value)
                ),
                AllowedFilter::callback(
                    'source',
                    fn (Builder $query, $value) => $query->whereIn('customers.source_id', gettype($value) == 'string' ? explode(',', $value) : $value)
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
                'active_deals_count',
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
        // echo $customers->toSql();
        return CustomerResource::collection(($customers->paginateData($limit)))
            ->additional(['meta' => [
                'totalClients' => ($customers->clone())->client()->count(),
                'totalPotentialClients' => ($customers->clone())->potentialClient()->count(),
            ]]);
    }

    public function list(Request $request)
    {
        $query = Customer::select(['id', 'company_name as name']);

        if (auth()->user()->role == RoleEnum::Advisor->value) {
            $query->where('owner_id', auth()->user()->id);
        }

        if ($request->get('company')) {
            $query->where('is_company', 1);
        }

        return $query->orderBy('name', 'asc')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $data = $request->validated();

        $data['created_by'] = auth()->user()->id;

        $customerData = [
            'star' => $data['star'],
            'company_name' => $data['company_name'],
            'razon_social' => $data['razon_social'] ?? null,
            'city' => $data['city'],
            'source_id' => $data['source_id'],
            'owner_id' => $data['owner_id'],
            'category_id' => $data['category_id'],
            'created_by' => $data['created_by']
        ];

        $contactData = [
            'name' => $data['name'],
            'phone' => $data['mobile'],
            'email' => $data['email'],
        ];

        if ($request->logo) {
            $extension = $request->logo->getClientOriginalExtension();
            $originalName = $request->logo->getClientOriginalName();
            $name = explode('.', $originalName)[0];

            $fileName = $name . '-' . Str::random(20) . '.' . $extension;

            $request->logo->storeAs('public/clients', $fileName);
            $customerData['logo'] = $fileName;
        }

        $customer = Customer::create($customerData);

        $customer->contacts()->create($contactData);

        if (
            $customer->owner_id != $customer->created_by
        ) {
            try {
                \Mail::to($customer->owner)->send(new NewCustomerAssigned($customer));
            } catch (\Throwable $th) {
                //TODO:
            }
        }

        if ($customer->category_id == 5) {
            try {
                \Mail::to(['manager@test.com', 'manager2@test.com'])->send(new NewAAACustomer($customer));

                $owner = $customer->owner;

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
                        \Mail::to($usersToNotify)->send(new NewAAACustomer($customer));
                    }
                }
            } catch (\Throwable $th) {
                //TODO...
            }
        }


        return new CustomerResource($customer);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $customer->load('owner', 'media', 'source', 'creator', 'projects', 'contacts', 'activeDeals', 'wonDeals');

        $customer->load(['notes' => function($query) {
            $query->with('user');
        }]);

        $customer->load(['tasks' => function($query) {
            $query->with('owner', 'doneBy');
        }]);

        return new CustomerResource($customer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $data = $request->validated();

        if ($request->logo) {
            $extension = $request->logo->getClientOriginalExtension();
            $originalName = $request->logo->getClientOriginalName();
            $name = explode('.', $originalName)[0];

            $fileName = $name . '-' . Str::random(20) . '.' . $extension;

            $request->logo->storeAs('public/clients', $fileName);
            $data['logo'] = $fileName;

            if (Storage::exists('public/clients/' . $customer->logo)) {
                Storage::delete('public/clients/' . $customer->logo);
            }
        }

        $customer->update($data);

        return new CustomerResource($customer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        $this->responseNoContent();
    }
}
