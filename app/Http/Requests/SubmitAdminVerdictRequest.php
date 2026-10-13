<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAdminVerdictRequest extends FormRequest
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
            'status' => 'required|in:approved,rejected',
            'comentario' => 'required_if:status,rejected|nullable|string|max:1000',
        ];
    }
    
    public function messages(): array
    {
        return [
            'status.required' => 'El estatus del veredicto es obligatorio.',
            'status.in' => 'El estatus seleccionado es inválido.',
            'comentario.required_if' => 'El comentario es requerido si se rechaza la solicitud.',
            'comentario.max' => 'El comentario no puede exceder los 1000 caracteres.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $applicationId = $this->route('id'); // Assuming the parameter is named 'id'
            if ($applicationId) {
                $application = \App\Models\Application::with('announcement.calendar')->find($applicationId);
                
                if ($application && $application->announcement) {
                    $stage = $application->announcement->current_stage;
                    if (!in_array($stage, ['evaluacion', 'resultados'])) {
                        $validator->errors()->add('status', 'Solo se puede emitir un veredicto definitivo durante las etapas de Evaluación o Resultados.');
                    }
                }
            }
        });
    }
}
