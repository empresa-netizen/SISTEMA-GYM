<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DietMealFood extends Model
{
    protected $table = 'diet_meal_foods';

    protected $fillable = [
        'diet_meal_id',
        'diet_food_id',
        'quantity_in_grams',
        'order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_in_grams' => 'decimal:2',
            'order' => 'integer',
        ];
    }

    public function dietMeal(): BelongsTo
    {
        return $this->belongsTo(DietMeal::class);
    }

    public function dietFood(): BelongsTo
    {
        return $this->belongsTo(DietFood::class);
    }

    /**
     * Macros da porção: (macro_do_catalogo / 100) * quantity_in_grams.
     *
     * @return array{calories: float, protein: float, carbs: float, fat: float}
     */
    public function portionMacros(): array
    {
        $this->loadMissing('dietFood');
        $food = $this->dietFood;
        $factor = ((float) $this->quantity_in_grams) / 100;

        if (! $food) {
            return [
                'calories' => 0.0,
                'protein' => 0.0,
                'carbs' => 0.0,
                'fat' => 0.0,
            ];
        }

        return [
            'calories' => round(((float) $food->calories) * $factor, 2),
            'protein' => round(((float) $food->protein) * $factor, 2),
            'carbs' => round(((float) $food->carbs) * $factor, 2),
            'fat' => round(((float) $food->fat) * $factor, 2),
        ];
    }
}
