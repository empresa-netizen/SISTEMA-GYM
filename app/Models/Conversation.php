<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'parent_id', 'member_id', 'last_message', 'last_message_at', 'unread_by_coach',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'unread_by_coach' => 'boolean',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at');
    }
}
