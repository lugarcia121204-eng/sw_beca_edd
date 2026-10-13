<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalendarResource extends JsonResource
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
            'announcement_id' => $this->announcement_id,
            'announcement' => $this->whenLoaded('announcement'),
            'publication_start' => $this->publication_start?->format('Y-m-d'),
            'registration_start' => $this->registration_start?->format('Y-m-d'),
            'registration_end' => $this->registration_end?->format('Y-m-d'),
            'evaluation_start' => $this->evaluation_start?->format('Y-m-d'),
            'evaluation_end' => $this->evaluation_end?->format('Y-m-d'),
            'results_start' => $this->results_start?->format('Y-m-d'),
            'results_end' => $this->results_end?->format('Y-m-d'),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
