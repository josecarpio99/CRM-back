<?php

namespace App\Http\Controllers\Api;
use App\Models\Deal;
use Illuminate\Http\Request;

class UpdateDealStatusController extends ApiController
{
    public function __invoke(Deal $deal)
    {
        return $this->responseNoContent();
    }

}
