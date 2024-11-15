<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use App\Http\Requests\Deal\UpdateDealMonitoringTaskRequest;
use App\Http\Resources\DealResource;

class UpdateDealMonitoringTaskController extends ApiController
{
    public function __invoke(UpdateDealMonitoringTaskRequest $request, Deal $deal)
    {
        $deal->monitoring_tasks = $request->monitoring_tasks;
        $deal->save();

        return new DealResource($deal);
    }

}
