<?php

namespace App\Http\Resources;

use App\Traits\DateFormat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    use DateFormat;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'description'   => $this->description,
            'key'           => $this->key,
            'created_at'    => $this->textFormatDate($this->created_at),
        ];
    }
}
