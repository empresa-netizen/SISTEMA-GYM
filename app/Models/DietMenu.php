<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;

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
}
