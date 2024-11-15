<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NoteResource extends JsonResource
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
                strrchr($this->noteable_type, "\\"),
                1
            )
        );

        return [
            'id' => $this->id,
            'content' => $this->content,
            'user_id' =>  $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'noteable_id' => $this->noteable_id,
            'noteable_type' => $classType,
            'created_at' => $this->created_at
        ];
    }
}
