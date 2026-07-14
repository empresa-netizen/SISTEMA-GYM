<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DietMeal extends Model
{
    protected $fillable = [
        'diet_menu_id',
        'name',
        'time_label',
        'order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function dietMenu(): BelongsTo
    {
        return $this->belongsTo(DietMenu::class);
    }

    public function mealFoods(): HasMany
    {
        return $this->hasMany(DietMealFood::class)->orderBy('order');
    }

    /**
     * @return array{calories: float, protein: float, carbs: float, fat: float}
     */
    public function computedMacros(): array
    {
        $this->loadMissing('mealFoods.dietFood');

        $totals = [
            'calories' => 0.0,
            'protein' => 0.0,
            'carbs' => 0.0,
            'fat' => 0.0,
        ];

        foreach ($this->mealFoods as $item) {
            $portion = $item->portionMacros();
            $totals['calories'] += $portion['calories'];
            $totals['protein'] += $portion['protein'];
            $totals['carbs'] += $portion['carbs'];
            $totals['fat'] += $portion['fat'];
        }

        return [
            'calories' => round($totals['calories'], 2),
            'protein' => round($totals['protein'], 2),
            'carbs' => round($totals['carbs'], 2),
            'fat' => round($totals['fat'], 2),
        ];
    }
}
