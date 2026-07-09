<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoachFeedItem extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'parent_id',
        'author_id',
        'member_id',
        'type',
        'title',
        'description',
        'meta',
        'image_path',
        'likes_count',
        'comments_count',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(FeedLike::class, 'coach_feed_item_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(FeedComment::class, 'coach_feed_item_id')->latest();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/'.$this->image_path) : null;
    }

    public function likedBy(?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return $this->likes()->where('user_id', $userId)->exists();
    }
}
