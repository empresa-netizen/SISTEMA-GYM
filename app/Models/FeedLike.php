<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedLike extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'parent_id',
        'coach_feed_item_id',
        'user_id',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(CoachFeedItem::class, 'coach_feed_item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
