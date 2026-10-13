<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // We want users to be able to contact us
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
            'email' => 'required|email|max:255',
            'institution_id' => 'required|exists:institutions,id',
            'message' => 'required|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El correo electrónico debe ser válido',
            'institution_id.required' => 'Debes seleccionar una institución',
            'institution_id.exists' => 'La institución seleccionada no es válida',
            'message.required' => 'El mensaje es obligatorio',
            'message.max' => 'El mensaje no puede exceder 1000 caracteres',
        ];
    }
}
