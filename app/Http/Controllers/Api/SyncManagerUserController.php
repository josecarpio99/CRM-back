<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\SyncManagerUserRequest;

class SyncManagerUserController extends ApiController
{
    public function __invoke(SyncManagerUserRequest $request, User $user)
    {
        abort_if(! auth()->user()->isSuperAdminOrDirector(), 401);

        $user->assignedUsers()->sync(
            $request->collect('users')->pluck('id')->toArray()
        );

        return $this->responseNoContent();
    }

}
