<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use App\Enums\DealTypeEnum;
use App\Mail\NewAAACotizado;
use Illuminate\Http\Request;
use App\Enums\DealStatusEnum;
use App\Http\Resources\DealResource;
use App\Http\Requests\Deal\ConvertOpportunityRequest;

class ConvertOpportunityController extends ApiController
{
    public function __invoke(ConvertOpportunityRequest $request, Deal $deal)
    {
        $this->authorize('convert', $deal);

        // $quotation = $deal->replicate()->fill([
        //     'type' => DealTypeEnum::Cotizado->value,
        //     'status' => DealStatusEnum::InProgress->value,
        //     'stage_moved_at' => null,
        //     'converted_to_quote' => now(),
        //     'value' => $request->value,
        //     'requirement' => $deal->requirement,
        //     'estimated_close_date_range' => $request->estimated_close_date_range
        // ]);

       $deal->update([
            'type' => DealTypeEnum::Cotizado->value,
            'status' => DealStatusEnum::InProgress->value,
            'stage_moved_at' => null,
            'converted_to_quote' => now(),
            'value' => $request->value,
            'estimated_close_date_range' => $request->estimated_close_date_range
        ]);

        if ($deal->value >= 1_000_000) {
            try {
                \Mail::to(['manager@test.com', 'manager2@test.com'])->send(new NewAAACotizado($deal));
            } catch (\Throwable $th) {
                return response($th->getMessage(), 500);
                //TODO...
            }
        }

        // $quotation->save();

        // $deal->delete();

        return new DealResource($deal);
    }

}
