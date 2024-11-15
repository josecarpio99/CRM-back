<?php

namespace App\Http\Resources;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAuditReportResource extends JsonResource
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
            'warning_deals' => $this->warning_deals_from_publicity_count,
            'active_deals' => $this->active_deals_from_publicity_count,
        ];
    }
}
