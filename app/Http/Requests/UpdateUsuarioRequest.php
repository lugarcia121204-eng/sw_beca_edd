<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'curp' => ['required', 'string', 'max:18','min:18', Rule::unique('Users')->ignore($this->id)],
            'name' => ['required', 'string', 'max:255'],
            /* 'paternal_surname' => ['required', 'string', 'max:255'],
            'maternal_surname' => ['required', 'string', 'max:255'], */
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('Users')->ignore($this->id)],
            
        ];
    }

    public function attributes(): array
    {
        return [
            'curp' => 'CURP',
            'name' => 'Nombre',
            'paternal_name' =>  'Apellido Paterno',
            'maternal_name' =>  'Apellido Materno',
            'email' => 'Correo electrónico',
            
        ];
    }
}