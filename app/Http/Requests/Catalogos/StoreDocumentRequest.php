<?php

namespace App\Http\Requests\Catalogos;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
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
            'description' => 'required|string|max:1000',
            'via' => 'required|string|in:ambas,larga,corta',
            'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xlsx,xls|max:30720', // Input name from Vue
            'active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del documento es obligatorio.',
            'via.required' => 'La via de solicitud es obligatoria.',
            'via.in' => 'La via de solicitud seleccionada no es valida.',
            'archivo.file' => 'El archivo debe ser un archivo válido.',
            'archivo.mimes' => 'El archivo debe ser de tipo: PDF, JPG, JPEG, PNG, DOC o DOCX.',
            'archivo.max' => 'El archivo no puede exceder 30MB.',
        ];
    }
}
