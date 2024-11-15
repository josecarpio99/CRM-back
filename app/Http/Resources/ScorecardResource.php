<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScorecardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'branch' => $this->branch,
            'avatar_url' => $this->avatar_url,
            'opportunities_sum_value' => $this->opportunities_sum_value ?? 0,
            'quotations_sum_value' => $this->quotations_sum_value ?? 0,
            'deals_sum_value' => $this->deals_sum_value ?? 0,
        ];
    }
}
