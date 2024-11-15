<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Enums\DealTypeEnum;
use App\Http\Resources\DealResource;
use Illuminate\Http\Request;

class DealOpportunitiesAwaitingResponseController extends ApiController
{
    public function __invoke()
    {
        $deals = Deal::query()
            ->where('owner_id', auth()->user()->id)
            ->where('created_by_lead_qualifier', true)
            ->whereNull('confirmed_at')
            // ->where('type', DealTypeEnum::Oportunidad->value)
            // ->whereIn(
            //     'created_by',
            //     User::where('role', RoleEnum::LeadQualifier->value)->pluck('id')->toArray()
            // )
            ->get();

        return DealResource::collection($deals);
    }

}
