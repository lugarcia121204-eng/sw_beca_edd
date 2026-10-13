<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Docente');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'announcement_id' => 'required|exists:announcements,id',
            'position_type_id' => 'required|exists:position_types,id',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf|max:10240', // 10MB max
            'file_types' => 'nullable|array',
            'reused_documents' => 'nullable|array',
            'via' => 'required|in:larga,corta',
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'announcement_id.required' => 'La convocatoria es obligatoria.',
            'announcement_id.exists' => 'La convocatoria seleccionada no es válida.',
            'position_type_id.required' => 'El tipo de plaza es obligatorio.',
            'position_type_id.exists' => 'El tipo de plaza seleccionado no es válido.',
            'files.*.mimes' => 'Los archivos deben ser en formato PDF.',
            'files.*.max' => 'Los archivos no deben superar los 10MB.',
            'via.required' => 'La vía de solicitud es obligatoria.',
            'via.in' => 'La vía seleccionada no es válida.',
        ];
    }
}
