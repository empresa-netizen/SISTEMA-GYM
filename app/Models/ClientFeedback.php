<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientFeedback extends Model
{
    use HasTenantScope;

    protected $table = 'client_feedbacks';

    protected $fillable = [
        'parent_id', 'member_id', 'status', 'message', 'photo_path', 'rating', 'context_type', 'context_id',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'context_id' => 'integer',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
