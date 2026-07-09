<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberAnamnesis extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'parent_id', 'member_id', 'goals', 'injuries', 'medications', 'lifestyle', 'notes', 'status',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
