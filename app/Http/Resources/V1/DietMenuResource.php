<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\DietMenu */
class DietMenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->resource->loadMissing('meals.mealFoods.dietFood');
        $macros = $this->resource->computedMacros();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'meals_count' => $macros['meals_count'] ?: (int) $this->meals_count,
            'total_calories' => $macros['calories'] > 0
                ? (float) $macros['calories']
                : ($this->total_calories !== null ? (float) $this->total_calories : null),
            'macros' => [
                'calories' => (float) $macros['calories'],
                'protein' => (float) $macros['protein'],
                'carbs' => (float) $macros['carbs'],
                'fat' => (float) $macros['fat'],
            ],
            'description' => $this->description,
            'meals' => DietMealResource::collection($this->meals),
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
