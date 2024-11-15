<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use Illuminate\Http\Request;

class ConfirmDealController extends ApiController
{
    public function __invoke(Deal $deal)
    {
        $deal->confirmed_at = now();
        $deal->save();

        return $this->responseNoContent();
    }

}
