<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutActivity extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'workout_id',
        'exercise_name',
        'description',
        'sets',
        'reps',
        'duration_minutes',
        'rest_seconds',
        'weight_kg',
        'order',
        'is_completed',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'sets' => 'integer',
            'reps' => 'integer',
            'duration_minutes' => 'integer',
            'rest_seconds' => 'integer',
            'weight_kg' => 'decimal:2',
            'order' => 'integer',
            'is_completed' => 'boolean',
        ];
    }

    /**
     * Get the workout
     */
    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WorkoutActivityLog::class)
            ->orderByDesc('logged_at')
            ->orderByDesc('id');
    }

    /**
     * Get formatted activity details
     */
    public function getDetailsAttribute(): string
    {
        $details = [];

        if ($this->sets && $this->reps) {
            $details[] = "{$this->sets} sets × {$this->reps} reps";
        }

        if ($this->duration_minutes) {
            $details[] = "{$this->duration_minutes} min";
        }

        if ($this->weight_kg) {
            $details[] = "{$this->weight_kg} kg";
        }

        return implode(' | ', $details);
    }
}
