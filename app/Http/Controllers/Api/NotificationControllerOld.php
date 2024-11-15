<?php

namespace App\Http\Controllers\Api;

use App\Enums\DealTypeEnum;
use App\Models\Deal;
use Illuminate\Http\Request;

class NotificationControllerOld extends ApiController
{
    public function __invoke()
    {
        $user = auth()->user()->load('lastIncompletedTasks.taskable');

        $usersId = auth()->user()->getAssignedUsersIdByRole();

        $data = collect(
            $user->lastIncompletedTasks->map(function($item) {
                $item['sort_date'] = $item['due_at'];
                return $item;
            })
        );

        if ($usersId !== null) {
            $advisorsDeals = Deal::query()
                ->whereColumn('owner_id', '<>', 'created_by')
                ->whereIn('owner_id', $usersId)
                ->where('type', DealTypeEnum::Oportunidad->value)
                ->get()
                ;

            $data = $data->merge($advisorsDeals->map(function($item) {
                $item['sort_date'] = $item['created_at'];
                return $item;
            }));
        }

        return response()->json([
            'data' => $data->sortByDesc('sort_date')->values()->toArray()
        ]);
    }

}
