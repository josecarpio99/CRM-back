<?php

namespace App\Http\Controllers\Api;

use App\Models\Lead;
use App\Models\Customer;
use App\Enums\DealTypeEnum;
use Illuminate\Http\Request;
use App\Services\DealService;
use App\Http\Resources\CustomerResource;
use App\Enums\DealEstimatedCloseDateRangeEnum;
use App\Http\Requests\Lead\ConvertLeadRequest;
use App\Models\Deal;

class ConvertLeadController extends ApiController
{
    public function __invoke(ConvertLeadRequest $request, Lead $lead, DealService $dealService)
    {
        $this->authorize('convert', $lead);

        $deal = null;

        $customer = Customer::create([
            'company_name' => $request->company_name,
            'razon_social' => $request->razon_social ?? null,
            // 'name' => $request->name,
            // 'email' => $request->email,
            // 'mobile' => $request->mobile,
            'city' => $request->city,
            'owner_id' => $request->owner_id,
            'category_id' => $request->category_id,
            'requirement' => $request->requirement,
            'source_id' => $request->source_id
        ]);

        if ($request->create_opportunity) {
            $dealData = [
                'type' => DealTypeEnum::Oportunidad->value,
                'estimated_close_date_range' => DealEstimatedCloseDateRangeEnum::fromZeroToThreeMonths->value,
                'customer_id' => $customer->id,
                'value' => 0,
                'name' => $customer->company_name,
                'requirement' => $customer->requirement,
                'source_id' => $customer->source_id,
                'city' => $customer->city,
                'owner_id' => $customer->owner_id,
            ];

            $deal = $dealService->store($dealData, $request->user());
        }

        $notes = $lead->notes;

        $tasks = $lead->tasks;

        if ($notes->count() > 0) {
            $notes->each(function ($item) use($customer, $deal){
                $item->update([
                    'noteable_type' => Customer::class,
                    'noteable_id' => $customer->id,
                ]);

                if ($deal) {
                    $newNote = $item->replicate()->fill([
                        'noteable_type' => Deal::class,
                        'noteable_id' => $deal->id,
                    ]);

                    $newNote->save();
                }
            });
        }

        if ($tasks->count() > 0) {
            $tasks->each(function ($item) use($customer, $deal){
                $item->update([
                    'taskable_type' => Customer::class,
                    'taskable_id' => $customer->id,
                ]);

                if ($deal) {
                    $newTask = $item->replicate()->fill([
                        'taskable_type' => Deal::class,
                        'taskable_id' => $deal->id,
                    ]);

                    $newTask->save();
                }
            });
        }

        if ($lead->contacts->count() > 0) {
            $lead->contacts->each(function ($item) use($customer){
                $item->update([
                    'contactable_type' => Customer::class,
                    'contactable_id' => $customer->id,
                ]);
            });
        }

        $lead->delete();

        return new CustomerResource($customer);
    }

}
