<?php

namespace App\Http\Requests\Security;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:50', Rule::unique('permissions', 'name')->ignore($this->permission->id),],
            'guard_name'  => "required|string",
            'description' => "required|string",
            'module_key'  => "required|exists:modules,key",
        ];
    }
    public function attributes(): array
    {
        return [
            'name'        => "nombre",
            'description' => "descripción",
            'module_key'  => "nombre del módulo",
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'El nombre es obligatorio',
            'name.max'              => 'El nombre no puede exceder 50 caracteres',
            'name.unique'           => 'Este nombre ya está en uso',
            'guard_name.required'   => 'El guard name es obligatorio',
            'description.required'  => 'La descripción es obligatoria',
            'module_key.required'   => 'Debes seleccionar un módulo',
            'module_key.exists'     => 'El módulo seleccionado no existe',
        ];
    }
}
