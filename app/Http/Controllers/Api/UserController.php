<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? $this->getDefaultPageLimit();;

        $users = QueryBuilder::for(User::class)
            ->allowedFilters([
                AllowedFilter::exact('role'),
                AllowedFilter::callback('search', fn (Builder $query, $value) => $query->search($value))
            ])
            ->allowedSorts(['name', 'role', 'email', 'created_at'])
            ->defaultSort('name')
            ->where('id', '<>', auth()->user()->id);

        return UserResource::collection(($users->paginate($limit)));
    }

    public function list(Request $request)
    {
        $query = User::select(['id', 'name', 'branch', 'role']);

        $usersId = auth()->user()->getAssignedUsersIdByRole();

        if ($usersId !== null) {
            $query->whereIn('id', $usersId);
        }

        return $query->orderBy('name', 'asc')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['assignedUsers', 'branches']);

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        $this->responseNoContent();
    }
}
