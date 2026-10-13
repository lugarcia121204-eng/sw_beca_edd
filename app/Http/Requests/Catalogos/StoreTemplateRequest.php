<?php

namespace App\Http\Requests\Catalogos;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'type' => 'required|in:recognition,acceptance',
            'file' => 'required|file|mimes:pdf|max:10240', // 10MB
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'type.required' => 'El tipo de plantilla es obligatorio.',
            'type.in' => 'El tipo de plantilla seleccionado es inválido.',
            'file.required' => 'El archivo de la plantilla es obligatorio.',
            'file.file' => 'Debe subir un archivo válido.',
            'file.mimes' => 'El archivo debe ser de formato PDF.',
            'file.max' => 'El archivo no debe pesar más de 10MB.',
        ];
    }
}
