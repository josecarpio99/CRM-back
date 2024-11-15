<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $result = [
            'id' => $this->id,
            'star' => $this->star,
            'is_company' => $this->is_company,
            'company_name' => $this->company_name,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'city' => $this->city,
            'razon_social' => $this->razon_social,
            'requirement' => $this->requirement,
            'created_at' => $this->created_at,
            'active_opportunities_count' => $this->active_opportunities_count,
            'active_quotes_count' => $this->active_quotes_count,
            'active_deals_count' => $this->active_deals_count,
            'days_since_last_won_deal' => $this->when(! is_null($this->days_since_last_won_deal), $this->days_since_last_won_deal),
            'activeOpportunities' => DealResource::collection($this->whenLoaded('activeOpportunities')),
            'activeQuotes' => DealResource::collection($this->whenLoaded('activeQuotes')),
            'activeDeals' => DealResource::collection($this->whenLoaded('activeDeals')),
            'source' => new SourceResource($this->whenLoaded('source')),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'notes' => NoteResource::collection($this->whenLoaded('notes')),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'lastActiveTask' => new TaskResource($this->whenLoaded('lastActiveTask')),
            'media' => DocumentResource::collection($this->whenLoaded('media')),
            'projects' => ProjectResource::collection($this->whenLoaded('projects')),
            'contacts' => ContactResource::collection($this->whenLoaded('contacts')),
            'latestWonDeal' => new DealResource($this->whenLoaded('latestWonDeal')),
            'wonDeals' => DealResource::collection($this->whenLoaded('wonDeals')),
        ];

        if ($this->logo) {
            $result['logo'] = [
                'img_name' => $this->logo,
                'img_url' => asset('storage/clients/' . $this->logo)
            ];
        }

        return $result;
    }
}
