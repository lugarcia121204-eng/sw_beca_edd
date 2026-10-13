<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AnnouncementResource extends JsonResource
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
            'description' => $this->description,
            'status' => $this->status,
            'current_stage' => $this->current_stage,
            'image_path' => $this->image_path,
            'image_url' => $this->image_path && Storage::disk('public')->exists($this->image_path) ? asset('storage/' . $this->image_path) : null,
            'file_path' => $this->file_path,
            'file_url' => $this->file_path && Storage::disk('public')->exists($this->file_path) ? asset('storage/' . $this->file_path) : null,
            'file_name' => $this->file_name,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'calendar' => $this->relationLoaded('calendar') && $this->calendar ? new CalendarResource($this->calendar) : null, // Uses CalendarResource
            'registration_end' => $this->when(
                $this->relationLoaded('calendar'),
                fn() => $this->calendar?->registration_end ?? null
            ),
            'created_at' => $this->created_at->toDateTimeString(),
            'user_application' => $this->whenLoaded('applications', function () {
                $application = $this->applications->first();
                return $application ? [
                    'id' => $application->id,
                    'status' => $application->status,
                ] : null;
            }),
        ];
    }
}
