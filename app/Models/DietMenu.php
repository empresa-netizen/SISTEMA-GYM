<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DietMenu extends Model
{
    use HasTenantScope;

    protected $table = 'diet_menus';

    protected $fillable = [
        'parent_id', 'name', 'status', 'meals_count', 'total_calories', 'description',
    ];

    protected function casts(): array
    {
        return ['total_calories' => 'decimal:2'];
    }

    public function meals(): HasMany
    {
        return $this->hasMany(DietMeal::class)->orderBy('order');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(DietPrescription::class);
    }

    /**
     * Soma real das refeições (porção × macros do catálogo).
     *
     * @return array{calories: float, protein: float, carbs: float, fat: float, meals_count: int}
     */
    public function computedMacros(): array
    {
        $this->loadMissing('meals.mealFoods.dietFood');

        $totals = [
            'calories' => 0.0,
            'protein' => 0.0,
            'carbs' => 0.0,
            'fat' => 0.0,
        ];

        foreach ($this->meals as $meal) {
            $mealMacros = $meal->computedMacros();
            $totals['calories'] += $mealMacros['calories'];
            $totals['protein'] += $mealMacros['protein'];
            $totals['carbs'] += $mealMacros['carbs'];
            $totals['fat'] += $mealMacros['fat'];
        }

        return [
            'calories' => round($totals['calories'], 2),
            'protein' => round($totals['protein'], 2),
            'carbs' => round($totals['carbs'], 2),
            'fat' => round($totals['fat'], 2),
            'meals_count' => $this->meals->count(),
        ];
    }

    /**
     * Mantém meals_count / total_calories alinhados ao cálculo real.
     */
    public function syncAggregateCounters(): void
    {
        $macros = $this->computedMacros();

        $this->forceFill([
            'meals_count' => $macros['meals_count'],
            'total_calories' => $macros['calories'],
        ])->save();
    }
}
