<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardioPlan extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'parent_id',
        'member_id',
        'title',
        'modality',
        'duration_minutes',
        'intensity',
        'weekly_frequency',
        'notes',
        'status',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
