<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;

class DietFood extends Model
{
    use HasTenantScope;

    protected $table = 'diet_foods';

    protected $fillable = [
        'parent_id', 'name', 'food_group', 'calories', 'protein', 'carbs', 'fat', 'unit',
    ];

    protected function casts(): array
    {
        return [
            'calories' => 'decimal:2',
            'protein' => 'decimal:2',
            'carbs' => 'decimal:2',
            'fat' => 'decimal:2',
        ];
    }
}
