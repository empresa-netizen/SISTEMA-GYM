<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberDietPrescriptionRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'diet_menu_id' => [
                'nullable',
                'integer',
                Rule::exists('diet_menus', 'id')->where(fn ($query) => $query->where('parent_id', parentId())),
            ],
            'notes' => ['nullable', 'string'],
            'scheduled_at' => ['nullable', 'date'],
            'menu_name' => ['nullable', 'string', 'max:255'],
            'menu_description' => ['nullable', 'string'],
            'meals' => ['nullable', 'array', 'max:12'],
            'meals.*.name' => ['required_with:meals', 'string', 'max:120'],
            'meals.*.time_label' => ['nullable', 'string', 'max:40'],
            'meals.*.notes' => ['nullable', 'string'],
            'meals.*.foods' => ['nullable', 'array', 'max:20'],
            'meals.*.foods.*.diet_food_id' => [
                'required',
                'integer',
                Rule::exists('diet_foods', 'id')->where(fn ($query) => $query->where('parent_id', parentId())),
            ],
            'meals.*.foods.*.quantity_in_grams' => ['required', 'numeric', 'min:1', 'max:10000'],
            'meals.*.foods.*.notes' => ['nullable', 'string'],
        ];
    }
}
