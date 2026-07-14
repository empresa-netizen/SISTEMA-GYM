<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryWorkoutActivity extends Model
{
    protected $fillable = [
        'library_workout_id',
        'exercise_name',
        'description',
        'sets',
        'reps',
        'duration_minutes',
        'rest_seconds',
        'weight_kg',
        'order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sets' => 'integer',
            'reps' => 'integer',
            'duration_minutes' => 'integer',
            'rest_seconds' => 'integer',
            'weight_kg' => 'decimal:2',
            'order' => 'integer',
        ];
    }

    public function libraryWorkout(): BelongsTo
    {
        return $this->belongsTo(LibraryWorkout::class);
    }
}
