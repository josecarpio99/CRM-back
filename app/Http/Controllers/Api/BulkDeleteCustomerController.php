<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Customer\BulkDeleteCustomerRequest;
use App\Models\Customer;

class BulkDeleteCustomerController extends ApiController
{
    public function __invoke(BulkDeleteCustomerRequest $request, User $user)
    {
        // abort_if(! auth()->user()->isSuperAdminOrDirector(), 401);

        $ids = $request->collect('customers')->pluck('id')->toArray();

        Customer::whereIn('id', $ids)->delete();

        return $this->responseNoContent();
    }

}
