<?php

namespace App\Http\Resources;

use App\Traits\DateFormat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'evaluator_level' => $this->evaluator_level,
            'evaluator_status' => $this->evaluator_status,
            'evaluator_institution_id' => $this->evaluator_institution_id,
            'evaluator_state_id' => $this->evaluator_state_id,
            'evaluator_scope' => $this->evaluator_level === 'local' ? $this->evaluatorInstitution?->name : ($this->evaluator_level === 'state' ? $this->evaluatorState?->name : null),
            'email_verified' => $this->hasVerifiedEmail(),
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'role'          => $this->getPrimaryRole(),
            'roles'         => RoleResource::collection($this->whenLoaded('roles')),
            'roles_ids'     => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('id')->toArray();
            }),
            'is_active'     => $this->is_active,
            'created_at'    => $this->textFormatDate($this->created_at),
            'deleted_at'    => $this->deleted_at ? $this->textFormatDate($this->deleted_at) : null,
            'institution'   => $this->whenLoaded('institution'),
            'priority_area' => $this->whenLoaded('priorityArea'),
            'sub_area'      => $this->whenLoaded('subArea'),
        ];
    }
}
