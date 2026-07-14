<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutActivityLog extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'parent_id',
        'member_id',
        'workout_id',
        'workout_activity_id',
        'sets',
        'reps',
        'weight_kg',
        'is_completed',
        'notes',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'sets' => 'integer',
            'reps' => 'integer',
            'weight_kg' => 'decimal:2',
            'is_completed' => 'boolean',
            'logged_at' => 'datetime',
        ];
    }

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(WorkoutActivity::class, 'workout_activity_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
