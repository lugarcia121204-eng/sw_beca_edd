<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Catalog\PriorityAreaResource;

class SubAreaResource extends JsonResource
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
            'priority_area_id' => $this->priority_area_id,
            'priority_area' => new PriorityAreaResource($this->whenLoaded('priorityArea')),
        ];
    }
}
