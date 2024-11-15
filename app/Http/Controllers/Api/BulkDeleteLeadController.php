<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Lead\BulkDeleteLeadRequest;
use App\Models\Lead;

class BulkDeleteLeadController extends ApiController
{
    public function __invoke(BulkDeleteLeadRequest $request, User $user)
    {
        // abort_if(! auth()->user()->isSuperAdminOrDirector(), 401);

        $ids = $request->collect('leads')->pluck('id')->toArray();

        Lead::whereIn('id', $ids)->delete();

        return $this->responseNoContent();
    }

}
