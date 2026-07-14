<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkoutPrescriptionRequest extends FormRequest
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
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'workout_date' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(['active', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string'],
            'activities' => ['required', 'array', 'min:1'],
            'activities.*.exercise_name' => ['required', 'string', 'max:255'],
            'activities.*.description' => ['nullable', 'string'],
            'activities.*.sets' => ['nullable', 'integer', 'min:0', 'max:100'],
            'activities.*.reps' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'activities.*.duration_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'activities.*.rest_seconds' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'activities.*.weight_kg' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'activities.*.order' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'activities.*.notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'member_id.required' => 'Informe o aluno que recebera o treino.',
            'name.required' => 'Informe o nome do treino.',
            'activities.required' => 'Inclua ao menos um exercicio.',
            'activities.min' => 'Inclua ao menos um exercicio.',
            'activities.*.exercise_name.required' => 'Informe o nome de cada exercicio.',
        ];
    }
}
