<?php

namespace App\Http\Requests;

use App\Models\Module;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateModuloRequest extends FormRequest
{
    protected string $tableName = 'module';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Adjusting to get ID properly if not present in request object directly but via route
        $id = $this->route('modulo') ? $this->route('modulo')->id : $this->id;

        return [
            'name' => ['required','max:255',Rule::unique(Module::class)->ignore($id)],
            'description' => 'required|max:255',
            'key' => ['required','max:255',Rule::unique(Module::class)->ignore($id)],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nombre del Módulo',
            'description' => 'Descripción',
            'key' => 'Clave'
        ];
    }
}
