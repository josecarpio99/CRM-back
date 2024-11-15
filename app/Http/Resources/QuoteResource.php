<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $quoteUrl = config('app.cotizador_url') . 'pdf/' . $this->id;

        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'deal_id' => $this->deal_id,
            'code' => $this->code,
            'name' => $this->name,
            'total' => $this->total,
            'url' => $quoteUrl,
            'created_at' => $this->created_at
        ];
    }
}
