<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
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
            'company_name' => $this->company_name,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'requirement' => $this->requirement,
            'city' => $this->city,
            'razon_social' => $this->razon_social,
            'created_at' => $this->created_at,
            'source' => new SourceResource($this->whenLoaded('source')),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'notes' => NoteResource::collection($this->whenLoaded('notes')),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'lastActiveTask' => new TaskResource($this->whenLoaded('lastActiveTask')),
            'media' => DocumentResource::collection($this->whenLoaded('media')),
            'contacts' => ContactResource::collection($this->whenLoaded('contacts')),
        ];
    }
}
