<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use Illuminate\Http\Request;
use App\Http\Requests\Deal\AttachDetachContactRequest;

class DealContactController extends ApiController
{
    public function __invoke(AttachDetachContactRequest $request, Deal $deal)
    {
        if ($request->type == 'attach') {
            $deal->associatedContacts()->attach($request->customer_id);
        } else {
            $deal->associatedContacts()->detach($request->customer_id);
        }

    return $this->responseNoContent();
    }
}
