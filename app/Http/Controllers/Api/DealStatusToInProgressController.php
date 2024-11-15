<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use App\Enums\DealTypeEnum;
use App\Mail\NewAAACotizado;
use Illuminate\Http\Request;
use App\Enums\DealStatusEnum;
use App\Http\Resources\DealResource;
use App\Http\Requests\Deal\DealStatusToInProgressRequest;

class DealStatusToInProgressController extends ApiController
{
    public function __invoke(DealStatusToInProgressRequest $request, Deal $deal)
    {
        $this->authorize('convert', $deal);

       $deal->update([
            'status' => DealStatusEnum::InProgress->value,
            'stage_moved_at' => null,
            'move_to_in_progress' => now(),
            'value' => $request->value,
            'estimated_close_date_range' => $request->estimated_close_date_range,
            'discount' => $request->discount
        ]);

        // if ($deal->value >= 1_000_000) {
        //     try {
        //         \Mail::to(['manager@test.com', 'manager2@test.com'])->send(new NewAAACotizado($deal));
        //     } catch (\Throwable $th) {
        //         return response($th->getMessage(), 500);
        //         //TODO...
        //     }
        // }
        return new DealResource($deal);
    }

}
