<?php

namespace App\Http\Requests\Catalogos;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnouncementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['sometimes', 'in:activa,cerrada,pendiente'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:30720'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],

            'publication_start' => ['required', 'date'],
            'registration_start' => ['required', 'date', 'after_or_equal:publication_start'],
            'registration_end' => ['required', 'date', 'after_or_equal:registration_start'],
            'evaluation_start' => ['required', 'date', 'after_or_equal:registration_end'],
            'evaluation_end' => ['required', 'date', 'after_or_equal:evaluation_start'],
            'results_start' => ['required', 'date', 'after_or_equal:evaluation_end'],
            'results_end' => ['required', 'date', 'after_or_equal:results_start'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado debe ser activa, cerrada o pendiente.',
            'file.file' => 'El archivo debe ser un archivo válido.',
            'file.mimes' => 'El archivo debe ser de tipo: PDF, JPG, JPEG, PNG, DOC o DOCX.',
            'file.max' => 'El archivo no puede exceder 30MB.',
            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe ser de tipo: JPEG, PNG, JPG o GIF.',
            'image.max' => 'La imagen no puede exceder 2MB.',

            'publication_start.required' => 'La fecha de inicio de publicación es obligatoria.',
            'publication_start.date' => 'La fecha de inicio de publicación debe ser una fecha válida.',

            'registration_start.required' => 'La fecha de inicio de registro es obligatoria.',
            'registration_start.date' => 'La fecha de inicio de registro debe ser una fecha válida.',
            'registration_start.after_or_equal' => 'La fecha de inicio de registro debe ser posterior o igual a la fecha de publicación.',

            'registration_end.required' => 'La fecha de fin de registro es obligatoria.',
            'registration_end.date' => 'La fecha de fin de registro debe ser una fecha válida.',
            'registration_end.after_or_equal' => 'La fecha de fin de registro debe ser posterior o igual a la fecha de inicio de registro.',

            'evaluation_start.required' => 'La fecha de inicio de evaluación es obligatoria.',
            'evaluation_start.date' => 'La fecha de inicio de evaluación debe ser una fecha válida.',
            'evaluation_start.after_or_equal' => 'La fecha de inicio de evaluación debe ser posterior o igual a la fecha de fin de registro.',

            'evaluation_end.required' => 'La fecha de fin de evaluación es obligatoria.',
            'evaluation_end.date' => 'La fecha de fin de evaluación debe ser una fecha válida.',
            'evaluation_end.after_or_equal' => 'La fecha de fin de evaluación debe ser posterior o igual a la fecha de inicio de evaluación.',

            'results_start.required' => 'La fecha de inicio de resultados es obligatoria.',
            'results_start.date' => 'La fecha de inicio de resultados debe ser una fecha válida.',
            'results_start.after_or_equal' => 'La fecha de inicio de resultados debe ser posterior o igual a la fecha de fin de evaluación.',

            'results_end.required' => 'La fecha de fin de resultados es obligatoria.',
            'results_end.required' => 'La fecha de fin de resultados es obligatoria.',
            'results_end.date' => 'La fecha de fin de resultados debe ser una fecha válida.',
            'results_end.after_or_equal' => 'La fecha de fin de resultados debe ser posterior o igual a la fecha de inicio de resultados.',

            'registration_start.after_or_equal' => 'El registro no puede iniciar antes de la publicación.',

            'registration_end.after_or_equal' => 'El fin de registro debe ser posterior o igual al inicio.',

            'evaluation_start.after_or_equal' => 'La evaluación no puede iniciar antes de terminar el registro.',

            'evaluation_end.after_or_equal' => 'El fin de evaluación debe ser posterior o igual al inicio.',

            'results_start.after_or_equal' => 'Los resultados no pueden publicarse antes de terminar la evaluación.',

            'results_end.after_or_equal' => 'El fin de resultados debe ser posterior o igual al inicio.',
        ];
    }
}
