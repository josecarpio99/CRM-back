<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\Request;

class ToggleCustomerStarController extends ApiController
{
    public function __invoke(Customer $customer)
    {
        $customer->star = ! $customer->star;
        $customer->save();

        return $this->responseNoContent();
    }

}
