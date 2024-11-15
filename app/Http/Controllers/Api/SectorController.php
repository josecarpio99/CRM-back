<?php

namespace App\Http\Controllers\Api;

use App\Models\Sector;
use Illuminate\Http\Request;

class SectorController extends ApiController
{
    public function __invoke()
    {
        return Sector::select(['id', 'name'])->orderBy('name', 'asc')->get();
    }

}
