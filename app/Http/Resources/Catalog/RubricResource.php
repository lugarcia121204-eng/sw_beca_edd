<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RubricResource extends JsonResource
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
            'title' => $this->title,
            'is_active' => (bool) $this->is_active,
            'questions' => RubricQuestionResource::collection($this->whenLoaded('questions')),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
