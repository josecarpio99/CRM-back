<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Deal\BulkDeleteDealRequest;
use App\Models\Deal;

class BulkDeleteDealController extends ApiController
{
    public function __invoke(BulkDeleteDealRequest $request, User $user)
    {
        // abort_if(! auth()->user()->isSuperAdminOrDirector(), 401);

        $ids = $request->collect('deals')->pluck('id')->toArray();

        Deal::whereIn('id', $ids)->delete();

        return $this->responseNoContent();
    }

}
