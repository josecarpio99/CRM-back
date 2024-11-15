<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use App\Models\Lead;
use App\Enums\RoleEnum;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalSearchController extends ApiController
{
    public function __invoke(Request $request)
    {
        $search = $request->search;
        $user = auth()->user();

        $customerQuery =  Customer::query()
            ->select(
                'id',
                'company_name as name',
                'owner_id',
                DB::raw('"customer" AS type')
            )
            ->where(function($query) use($search) {
                $query->where('name', 'like', "%$search%")
                ->orWhere('company_name', 'like', "%$search%");
            })
            ->when(! $user->isSuperAdminOrDirector() && $user->role != RoleEnum::LeadQualifier->value, function($query) use ($user) {
                $query->whereIn('owner_id', $user->getAssignedUsersIdByRole());
            });

        $leadQuery =  Lead::query()
            ->select(
                'id',
                'company_name as name',
                'owner_id',
                DB::raw('"lead" AS type')
            )
            ->where(function($query) use($search) {
                $query->where('name', 'like', "%$search%")
                ->orWhere('company_name', 'like', "%$search%");
            })
            ->when(! $user->isSuperAdminOrDirector() && $user->role != RoleEnum::LeadQualifier->value, function($query) use ($user) {
                $query->whereIn('owner_id', $user->getAssignedUsersIdByRole());
            });

        $dealQuery =  Deal::query()
            ->select(
                'id',
                'name',
                'owner_id',
                'type'
            )
            ->where(function($query) use($search) {
                $query->where('name', 'like', "%$search%");
            })
            ->when(! $user->isSuperAdminOrDirector() && $user->role != RoleEnum::LeadQualifier->value, function($query) use ($user) {
                $query->whereIn('owner_id', $user->getAssignedUsersIdByRole());
            });

        $finalQuery = $dealQuery->unionAll($customerQuery)->unionAll($leadQuery);

        return response()->json([
            'data' => $finalQuery->orderBy('name')->get(),
            'meta' => [
                'total' => $finalQuery->orderBy('name')->count()
            ]
        ]);

    }

}
