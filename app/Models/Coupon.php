<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'parent_id', 'code', 'discount_type', 'discount_value',
        'expires_at', 'uses_count', 'max_uses', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'expires_at' => 'date',
            'is_active' => 'boolean',
        ];
    }
}
