<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\DietMeal */
class DietMealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $macros = $this->resource->computedMacros();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'time_label' => $this->time_label,
            'order' => $this->order,
            'notes' => $this->notes,
            'macros' => $macros,
            'foods' => $this->whenLoaded('mealFoods', function () {
                return $this->mealFoods->map(function ($item) {
                    $portion = $item->portionMacros();

                    return [
                        'id' => $item->id,
                        'diet_food_id' => $item->diet_food_id,
                        'name' => $item->dietFood?->name,
                        'food_group' => $item->dietFood?->food_group,
                        'quantity_in_grams' => (float) $item->quantity_in_grams,
                        'order' => $item->order,
                        'notes' => $item->notes,
                        'macros' => $portion,
                        'catalog_per_100g' => $item->dietFood ? [
                            'calories' => (float) $item->dietFood->calories,
                            'protein' => (float) $item->dietFood->protein,
                            'carbs' => (float) $item->dietFood->carbs,
                            'fat' => (float) $item->dietFood->fat,
                            'unit' => $item->dietFood->unit,
                        ] : null,
                    ];
                })->values();
            }),
        ];
    }
}
