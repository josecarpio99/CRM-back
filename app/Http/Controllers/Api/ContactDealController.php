<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use Illuminate\Http\Request;
use App\Http\Requests\Deal\AttachDetachDealContactRequest;

class ContactDealController extends ApiController
{
    public function __invoke(AttachDetachDealContactRequest $request, Deal $deal)
    {
        if ($request->type == 'attach') {
            $deal->contacts()->attach($request->contact_id);
        } else {
            $deal->contacts()->detach($request->contact_id);
        }

    return $this->responseNoContent();
    }
}
