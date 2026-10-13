<?php

namespace App\Http\Requests\Security;

use Illuminate\Foundation\Http\FormRequest;

class ImportUsersRequest extends FormRequest
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
            'file' => 'required|mimes:xlsx,xls,csv',
        ];
    }
    
    public function messages(): array
    {
        return [
            'file.required' => 'Debe seleccionar un archivo para importar.',
            'file.mimes' => 'El archivo debe ser de tipo Excel o CSV.',
        ];
    }
}
