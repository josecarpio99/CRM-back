<?php

namespace App\Http\Resources;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'role' => $this->role,
            'branch' => $this->branch,
            'avatar_url' => $this->avatar_url,
            'assignedUsers' => UserResource::collection($this->whenLoaded('assignedUsers')),
            'branches' => BranchResource::collection($this->whenLoaded('branches')),
            'lastIncompletedTasks' => TaskResource::collection($this->whenLoaded('lastIncompletedTasks')),
            'notifications' => NotificationResource::collection($this->whenLoaded('notifications')),
        ];
    }
}
