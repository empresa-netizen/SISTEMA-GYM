<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryWorkout extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'parent_id',
        'title',
        'focus',
        'duration_weeks',
        'sessions_per_week',
        'level',
        'status',
        'description',
        'notes',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
}
