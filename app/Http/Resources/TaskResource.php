<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $classType = strtolower(
            substr(
                strrchr($this->taskable_type, "\\"),
                1
            )
        );

        return [
            'id' => $this->id,
            'content' => $this->content,
            'user_id' =>  $this->user_id,
            'owner_id' =>  $this->owner_id,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'done_by_user' => new UserResource($this->whenLoaded('doneBy')),
            'taskable_id' => $this->taskable_id,
            'taskable_type' => $classType,
            'done' => $this->done,
            'done_by' => $this->done_by,
            'due_at' => $this->due_at,
            'due_date' => $this->due_date,
            'created_at' => $this->created_at,
            // 'taskable' => $this->taskable,
            'resource' => $this->when($this->relationLoaded('taskable'), function () use($classType) {
                if (empty($this->taskable)) return [];
                if ($classType == 'deal') {
                    return [
                        'id' => $this->taskable->id,
                        'type' => $this->taskable->type,
                        'name' => $this->taskable->name,
                    ];
                }
                else {
                    return [
                        'id' => $this->taskable->id,
                        'type' => $classType,
                        'name' => $this->taskable->name,
                    ];
                }
            })
        ];
    }
}
