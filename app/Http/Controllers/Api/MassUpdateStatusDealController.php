<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use App\Models\User;
use App\Enums\DealTypeEnum;
use Illuminate\Http\Request;
use App\Http\Requests\Deal\MassUpdateStatusDealRequest;

class MassUpdateStatusDealController extends ApiController
{
    public function __invoke(MassUpdateStatusDealRequest $request, User $user)
    {
        // abort_if(! auth()->user()->isSuperAdminOrDirector(), 401);

        $ids = $request->collect('deals')->pluck('id')->toArray();

        Deal::whereIn('id', $ids)->update([
            'stage_moved_at' => now(),
            'status' => $request->status
        ]);

        return $this->responseNoContent();
    }

}
