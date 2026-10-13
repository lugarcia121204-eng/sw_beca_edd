<?php

namespace App\Http\Requests\Security;

use App\Models\Module;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateModuleRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name'          => 'required|max:255',
            'description'   => 'required|max:255',
            'key'           => 'required|max:255|unique:modules,key,' . $this->id,
        ];
    }
    public function attributes(): array
    {
        return [
            'name'          => 'nombre',
            'description'   => 'descripción',
            'key'           => 'clave'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'El nombre es obligatorio',
            'name.max'              => 'El nombre no puede exceder 255 caracteres',
            'description.required'  => 'La descripción es obligatoria',
            'description.max'       => 'La descripción no puede exceder 255 caracteres',
            'key.required'          => 'La clave es obligatoria',
            'key.max'               => 'La clave no puede exceder 255 caracteres',
            'key.unique'            => 'Esta clave ya está en uso',
        ];
    }
}
