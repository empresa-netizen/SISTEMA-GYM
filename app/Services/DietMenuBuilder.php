<?php

namespace App\Services;

use App\Models\DietMenu;
use Illuminate\Support\Facades\DB;

class DietMenuBuilder
{
    /**
     * @param  array{
     *   name: string,
     *   description?: ?string,
     *   meals_count?: ?int,
     *   total_calories?: ?float,
     *   status?: ?string,
     *   meals?: array<int, array{
     *     name: string,
     *     time_label?: ?string,
     *     notes?: ?string,
     *     foods?: array<int, array{diet_food_id: int, quantity_in_grams: numeric, notes?: ?string}>
     *   }>
     * }  $payload
     */
    public function createForTenant(int $parentId, array $payload): DietMenu
    {
        $meals = $payload['meals'] ?? [];

        return DB::transaction(function () use ($meals, $parentId, $payload) {
            $menu = DietMenu::create([
                'parent_id' => $parentId,
                'name' => $payload['name'],
                'description' => $payload['description'] ?? null,
                'status' => $payload['status'] ?? 'draft',
                'meals_count' => empty($meals) ? ($payload['meals_count'] ?? 0) : 0,
                'total_calories' => empty($meals) ? ($payload['total_calories'] ?? 0) : 0,
            ]);

            foreach (array_values($meals) as $mealIndex => $mealPayload) {
                $foods = $mealPayload['foods'] ?? [];

                $meal = $menu->meals()->create([
                    'name' => $mealPayload['name'],
                    'time_label' => $mealPayload['time_label'] ?? null,
                    'notes' => $mealPayload['notes'] ?? null,
                    'order' => $mealIndex,
                ]);

                foreach (array_values($foods) as $foodIndex => $foodPayload) {
                    $meal->mealFoods()->create([
                        'diet_food_id' => $foodPayload['diet_food_id'],
                        'quantity_in_grams' => $foodPayload['quantity_in_grams'],
                        'notes' => $foodPayload['notes'] ?? null,
                        'order' => $foodIndex,
                    ]);
                }
            }

            if (! empty($meals)) {
                $menu->syncAggregateCounters();
            }

            return $menu->load('meals.mealFoods.dietFood');
        });
    }
}
