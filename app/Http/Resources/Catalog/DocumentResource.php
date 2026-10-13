<?php

namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
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
            'file_path' => $this->file_path,
            'file_name' => $this->file_name,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'active' => $this->active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            // Pivot data when loaded via announcement relationship
            'is_mandatory' => isset($this->pivot) ? (bool) $this->pivot->is_mandatory : true,
            'via' => isset($this->pivot) ? $this->pivot->via : ($this->via ?? 'ambas'),
            // Alias for frontend compatibility
            'is_required' => isset($this->pivot) ? (bool) $this->pivot->is_mandatory : true,
            // Template URL for download (if it has file)
            'template_url' => $this->file_path ? "/storage/{$this->file_path}" : null,
        ];
    }
}
