<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Any guest can register
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'evaluator_status' => 'prohibited',
            'evaluator_level' => 'prohibited',
            'evaluator_institution_id' => 'prohibited',
            'evaluator_state_id' => 'prohibited',
            'evaluator_authorized_by' => 'prohibited',
            'evaluator_authorized_at' => 'prohibited',
            'curp' => 'required|string|size:18',
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::notIn(['admin@gmail.com', 'superadmin@gmail.com']),
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'institution_id' => 'required|exists:institutions,id',
            'priority_area_id' => 'required|exists:priority_areas,id',
            'sub_area_id' => 'required|exists:sub_areas,id',
            'role_type' => 'sometimes|string|in:evaluador,docente', // Optional parameter to determine role
        ];
    }

    public function messages(): array
    {
        return [
            'email.not_in' => 'Este correo esta reservado para la administracion del sistema y no puede usarse en registro publico.',
        ];
    }
}
