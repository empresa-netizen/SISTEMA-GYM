<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'parent_id',
        'member_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'location',
        'max_participants',
        'registered_count',
        'status',
        'image',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Apply tenant scoping for data isolation
        static::addGlobalScope(new \App\Models\Scopes\TenantScope);
    }

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'max_participants' => 'integer',
            'registered_count' => 'integer',
        ];
    }

    /**
     * Get the owner/parent
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Check if event is full
     */
    public function isFull(): bool
    {
        return $this->max_participants && $this->registered_count >= $this->max_participants;
    }

    /**
     * Check if event is ongoing
     */
    public function isOngoing(): bool
    {
        return $this->start_time->isPast() && $this->end_time->isFuture();
    }

    /**
     * Get available spots
     */
    public function getAvailableSpotsAttribute(): ?int
    {
        if (! $this->max_participants) {
            return null;
        }

        return max(0, $this->max_participants - $this->registered_count);
    }

    /**
     * Get image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    /**
     * Scope for upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now())
            ->where('status', 'scheduled')
            ->orderBy('start_time', 'asc');
    }

    /**
     * Scope for ongoing events
     */
    public function scopeOngoing($query)
    {
        return $query->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->where('status', 'ongoing');
    }
}
