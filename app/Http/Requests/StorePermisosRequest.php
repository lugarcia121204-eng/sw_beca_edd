<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePermisosRequest extends FormRequest
{
    protected string $tableName = 'permissions';
    protected string $param     = 'permissions';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return[
            'name' => 'required|string|max:50|unique:permissions',
            'guard_name'  => "required|string",
            'description' => "required|string",
            'module_key'  => "required|exists:modules,key",
        ];
    }
    public function attributes(): array
    {
        return [
            'name'        => "Nombre del Permiso",
            'description' => "Descripción",
            'module_key'  => "Nombre del módulo",
        ];
    }
}
