<?php

namespace App\Http\Controllers\Api;

use App\Models\Source;
use Illuminate\Http\Request;

class SourceController extends ApiController
{
    public function __invoke()
    {
        return Source::select(['id', 'name'])->orderBy('name', 'asc')->get();
    }

}
