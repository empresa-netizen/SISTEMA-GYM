<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberLogbook extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'parent_id', 'member_id', 'type', 'title', 'logged_at', 'rating', 'numeric_value', 'unit', 'metadata', 'comment',
    ];

    protected function casts(): array
    {
        return [
            'logged_at' => 'datetime',
            'numeric_value' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
