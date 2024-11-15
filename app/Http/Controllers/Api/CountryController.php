<?php

namespace App\Http\Controllers\Api;

use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends ApiController
{
    public function __invoke()
    {
        return Country::select(['id', 'name'])->orderBy('name', 'asc')->get();
    }

}
