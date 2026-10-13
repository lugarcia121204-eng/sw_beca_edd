<?php

namespace App\Http\Requests\Catalogos;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRubricRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'questions' => ['nullable', 'array'],
            'questions.*.id' => ['nullable', 'integer', 'exists:rubric_questions,id'],
            'questions.*.text' => ['required', 'string'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*.id' => ['nullable', 'integer', 'exists:rubric_question_options,id'],
            'questions.*.options.*.text' => ['required', 'string'],
            'questions.*.options.*.score' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }
}
