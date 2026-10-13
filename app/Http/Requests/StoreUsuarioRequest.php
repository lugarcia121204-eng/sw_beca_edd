<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'curp' => ['required', 'string', 'max:18','min:18', 'unique:users'],
            'name' => ['required', 'string', 'max:255'],
            /* 'paternal_surname' => ['required', 'string', 'max:255'],
            'maternal_surname' => ['required', 'string', 'max:255'], */
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required'],
            

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
            'password' => 'Contraseña',
            
        ];
    }
}