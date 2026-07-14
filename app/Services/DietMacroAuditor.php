<?php

namespace App\Services;

use App\Models\DietFood;
use App\Models\DietPrescription;

class DietMacroAuditor
{
    /**
     * @return array{
     *   menu_kcal: float,
     *   catalog: array{calories: float, protein: float, carbs: float, fat: float},
     *   prescribed: array{calories: float, protein: float, carbs: float, fat: float},
     *   foods: \Illuminate\Support\Collection,
     *   meals: \Illuminate\Support\Collection,
     *   meals_count: int,
     *   menu_name: ?string,
     *   menu_description: ?string
     * }
     */
    public function summarize(DietPrescription $prescription): array
    {
        $prescription->loadMissing('dietMenu.meals.mealFoods.dietFood');

        $menu = $prescription->dietMenu;
        $prescribed = $menu
            ? $menu->computedMacros()
            : ['calories' => 0.0, 'protein' => 0.0, 'carbs' => 0.0, 'fat' => 0.0, 'meals_count' => 0];

        $meals = collect();
        if ($menu) {
            $meals = $menu->meals->map(function ($meal) {
                $macros = $meal->computedMacros();

                return [
                    'id' => $meal->id,
                    'name' => $meal->name,
                    'time_label' => $meal->time_label,
                    'order' => $meal->order,
                    'notes' => $meal->notes,
                    'macros' => $macros,
                    'foods' => $meal->mealFoods->map(function ($item) {
                        $portion = $item->portionMacros();

                        return [
                            'id' => $item->id,
                            'diet_food_id' => $item->diet_food_id,
                            'name' => $item->dietFood?->name,
                            'food_group' => $item->dietFood?->food_group,
                            'quantity_in_grams' => (float) $item->quantity_in_grams,
                            'macros' => $portion,
                        ];
                    })->values(),
                ];
            })->values();
        }

        // Catálogo do tenant permanece como referência (não é a fonte da verdade da dieta).
        $foods = DietFood::query()
            ->where('parent_id', $prescription->parent_id)
            ->orderBy('name')
            ->get(['id', 'name', 'food_group', 'calories', 'protein', 'carbs', 'fat', 'unit']);

        $menuKcal = $prescribed['calories'] > 0
            ? (float) $prescribed['calories']
            : (float) ($menu?->total_calories ?? 0);

        return [
            'menu_kcal' => $menuKcal,
            'prescribed' => [
                'calories' => (float) $prescribed['calories'],
                'protein' => (float) $prescribed['protein'],
                'carbs' => (float) $prescribed['carbs'],
                'fat' => (float) $prescribed['fat'],
            ],
            'catalog' => [
                'calories' => (float) $foods->sum('calories'),
                'protein' => (float) $foods->sum('protein'),
                'carbs' => (float) $foods->sum('carbs'),
                'fat' => (float) $foods->sum('fat'),
            ],
            'foods' => $foods,
            'meals' => $meals,
            'meals_count' => (int) ($prescribed['meals_count'] ?: ($menu?->meals_count ?? 0)),
            'menu_name' => $menu?->name,
            'menu_description' => $menu?->description,
        ];
    }
}
