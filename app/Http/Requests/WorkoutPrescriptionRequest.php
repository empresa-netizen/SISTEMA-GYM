<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkoutPrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'member_id' => [
                'nullable',
                'integer',
                Rule::exists('members', 'id')->where(fn ($query) => $query->where('parent_id', parentId())),
            ],
            'trainer_id' => [
                'nullable',
                'integer',
                Rule::exists('trainers', 'id')->where(fn ($query) => $query->where('parent_id', parentId())),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'workout_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string'],
            'activities' => ['nullable', 'array'],
            'activities.*.exercise_name' => ['required_with:activities', 'string', 'max:255'],
            'activities.*.description' => ['nullable', 'string'],
            'activities.*.sets' => ['nullable', 'integer', 'min:1'],
            'activities.*.reps' => ['nullable', 'integer', 'min:1'],
            'activities.*.duration_minutes' => ['nullable', 'integer', 'min:1'],
            'activities.*.rest_seconds' => ['nullable', 'integer', 'min:0'],
            'activities.*.weight_kg' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
