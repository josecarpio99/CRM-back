<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DealResource extends JsonResource
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
            'type' => $this->type,
            'name' => $this->name,
            'value' => $this->value,
            'estimated_size' => $this->estimated_size,
            'estimated_close_date' => $this->estimated_close_date,
            'estimated_close_date_range' => $this->estimated_close_date_range,
            'created_at' => $this->created_at,
            'confirmed_at' => $this->confirmed_at,
            'converted_to_quote' => $this->converted_to_quote,
            'converted_to_opportunity' => $this->converted_to_opportunity,
            'stage_moved_at' => $this->stage_moved_at,
            'move_to_in_progress' => $this->move_to_in_progress,
            'status' => $this->status,
            'requirement' => $this->requirement,
            'city' => $this->city,
            'created_by_lead_qualifier' => $this->created_by_lead_qualifier,
            'discount' => $this->discount,
            'monitoring_tasks' => $this->monitoring_tasks,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'pipeline' => new DealPipelineResource($this->whenLoaded('pipeline')),
            'stage' => new DealPipelineStageResource($this->whenLoaded('stage')),
            'source' => new SourceResource($this->whenLoaded('source')),
            'lastActiveTask' => new TaskResource($this->whenLoaded('lastActiveTask')),
            'associatedContacts' => CustomerResource::collection($this->whenLoaded('associatedContacts')),
            'notes' => NoteResource::collection($this->whenLoaded('notes')),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'media' => DocumentResource::collection($this->whenLoaded('media')),
            'media_files' => DocumentResource::collection($this->whenLoaded('mediaFiles')),
            'media_profitability' => DocumentResource::collection($this->whenLoaded('mediaProfitability')),
            'quotes' => QuoteResource::collection($this->whenLoaded('quotes')),
            'contacts' => ContactResource::collection($this->whenLoaded('contacts')),
        ];
    }
}
