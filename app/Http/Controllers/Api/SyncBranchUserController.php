<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\SyncBranchUserRequest;

class SyncBranchUserController extends ApiController
{
    public function __invoke(SyncBranchUserRequest $request, User $user)
    {
        abort_if(! auth()->user()->isSuperAdminOrDirector(), 401);

        $user->branches()->sync(
            $request->collect('branches')->pluck('id')->toArray()
        );

        return $this->responseNoContent();
    }

}
