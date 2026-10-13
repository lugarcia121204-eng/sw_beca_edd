<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermisosRequest extends FormRequest
{
    protected string $tableName = 'permissions';
    protected string $param     = 'permission';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Note: The route parameter name might depend on how the resource is defined in routes/web.php
        // We will assume it matches the previous project structure for now.
        $id = $this->route('permissions') ? $this->route('permissions')->id : null;
        
        return[
            'name'        => "required|string|max:50|unique:permissions,name," . $id,
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
